<?php

/**
 * TechDivision\Import\Product\UrlRewrite\Observers\ProductUrlRewriteObserver
 *
 * PHP version 7
 *
 * @author    Tim Wagner <t.wagner@techdivision.com>
 * @copyright 2016 TechDivision GmbH <info@techdivision.com>
 * @license   https://opensource.org/licenses/MIT
 * @link      https://github.com/techdivision/import-product-url-rewrite
 * @link      http://www.techdivision.com
 */

namespace TechDivision\Import\Product\UrlRewrite\Observers;

use TechDivision\Import\Observers\StateDetectorInterface;
use TechDivision\Import\Product\UrlRewrite\Services\ProductUrlRewriteProcessorInterface;
use TechDivision\Import\Utils\MemberNames;
use TechDivision\Import\Utils\StoreViewCodes;
use TechDivision\Import\Product\UrlRewrite\Utils\ColumnKeys;
use TechDivision\Import\Product\Observers\AbstractProductImportObserver;

/**
 * Observer that extracts the URL rewrite data to a specific CSV file.
 *
 * @author    Tim Wagner <t.wagner@techdivision.com>
 * @copyright 2016 TechDivision GmbH <info@techdivision.com>
 * @license   https://opensource.org/licenses/MIT
 * @link      https://github.com/techdivision/import-product-url-rewrite
 * @link      http://www.techdivision.com
 */
class ProductUrlRewriteObserver extends AbstractProductImportObserver
{

    /**
     * The artefact type.
     *
     * @var string
     */
    const ARTEFACT_TYPE = 'url-rewrite';

    /**
     * The image artefacts that has to be exported.
     *
     * @var array
     */
    protected $artefacts = array();

    /**
     * The admin row for initialize artefacts that has to be exported.
     *
     * @var array
     */
    protected $adminRow = array();

    /**
     * The product bunch processor instance.
     *
     * @var \TechDivision\Import\Product\UrlRewrite\Services\ProductUrlRewriteProcessorInterface
     */
    protected $productUrlRewriteProcessor;

    /**
     * Initialize the observer with the passed product URL rewrite processor instance.
     *
     * @param \TechDivision\Import\Product\UrlRewrite\Services\ProductUrlRewriteProcessorInterface $productUrlRewriteProcessor The product URL rewrite processor instance
     * @param \TechDivision\Import\Observers\StateDetectorInterface|null                           $stateDetector              The state detector instance to use
     */
    public function __construct(
        ProductUrlRewriteProcessorInterface $productUrlRewriteProcessor,
        StateDetectorInterface $stateDetector = null
    ) {
        $this->productUrlRewriteProcessor = $productUrlRewriteProcessor;
        parent::__construct($stateDetector);
    }

    /**
     * Return's the product bunch processor instance.
     *
     * @return \TechDivision\Import\Product\UrlRewrite\Services\ProductUrlRewriteProcessorInterface The product URL rewrite processor instance
     */
    protected function getProductUrlRewriteProcessor()
    {
        return $this->productUrlRewriteProcessor;
    }

    /**
     * Process the observer's business logic.
     *
     * @return array The processed row
     */
    protected function process()
    {

        // initialize the product
        $product = null;

        // initialize the array for the artefacts and the store view codes
        $this->artefacts = array();
        $storeViewCodes = array();

        // load the SKU from the row
        $sku = $this->getValue(ColumnKeys::SKU);

        // prepare the store view code
        $this->getSubject()->prepareStoreViewCode();

        // try to load the store view code
        $storeViewCodeValue = $this->getSubject()->getStoreViewCode(StoreViewCodes::ADMIN);

        // query whether or not we've a store view code
        if ($storeViewCodeValue === StoreViewCodes::ADMIN) {
            // load product to see if already exist or new
            $product = $this->loadProduct($this->getValue(ColumnKeys::SKU));

            // init admin row for memory overload
            $this->adminRow = array();

            // remember the admin row on SKU to be safe on later process
            $this->adminRow[$sku] = array(
                ColumnKeys::CATEGORIES       => $this->getValue(ColumnKeys::CATEGORIES),
                ColumnKeys::PRODUCT_WEBSITES => $this->getValue(ColumnKeys::PRODUCT_WEBSITES),
                ColumnKeys::VISIBILITY       => $this->getValue(ColumnKeys::VISIBILITY),
                ColumnKeys::URL_KEY          => $this->getValue(ColumnKeys::URL_KEY)
            );

            // if not, load the websites the product is related with
            $websiteCodes = $this->getValue(ColumnKeys::PRODUCT_WEBSITES, array(), array($this, 'explode'));

            // load the store view codes of all websites
            foreach ($websiteCodes as $websiteCode) {
                $storeViewCodes = array_merge($storeViewCodes, $this->getStoreViewCodesByWebsiteCode($websiteCode));
            }
        } else {
            array_push($storeViewCodes, $storeViewCodeValue);
        }

        // iterate over the available image fields
        foreach ($storeViewCodes as $storeViewCode) {
            // iterate over the store view codes and query if artefacts are already available
            if ($this->hasArtefactsByTypeAndEntityId(ProductUrlRewriteObserver::ARTEFACT_TYPE, $lastEntityId = $this->getSubject()->getLastEntityId())) {
                // if yes, load the artefacs
                $this->artefacts = $this->getArtefactsByTypeAndEntityId(ProductUrlRewriteObserver::ARTEFACT_TYPE, $lastEntityId);

                // initialize the flag that shows whether or not an artefact has already been available
                $foundArtefactToUpdate = false;
                // override the existing data with the store view specific one
                for ($i = 0; $i < sizeof($this->artefacts); $i++) {
                    // query whether or not a URL key has be specfied and the store view codes are equal
                    if ($this->artefacts[$i][ColumnKeys::STORE_VIEW_CODE] === $storeViewCode) {
                        // set the flag to mark we've already found an attribute
                        $foundArtefactToUpdate = true;

                        // update the URL key, if available
                        if ($this->hasValue(ColumnKeys::URL_KEY)) {
                            $this->artefacts[$i][ColumnKeys::URL_KEY] = $this->getValue(ColumnKeys::URL_KEY);
                        }

                        // update the visibility, if available
                        if ($this->hasValue(ColumnKeys::VISIBILITY)) {
                            $this->artefacts[$i][ColumnKeys::VISIBILITY] = $this->getValue(ColumnKeys::VISIBILITY);
                        }

                        // also update filename and line number
                        $this->artefacts[$i][ColumnKeys::ORIGINAL_DATA][ColumnKeys::ORIGINAL_FILENAME] = $this->getSubject()->getFilename();
                        $this->artefacts[$i][ColumnKeys::ORIGINAL_DATA][ColumnKeys::ORIGINAL_LINE_NUMBER] = $this->getSubject()->getLineNumber();
                    }
                }

                if (!$foundArtefactToUpdate) {
                    // if no arefacts are available, append new data
                    $this->createArtefact($sku, $storeViewCode);
                }
            } else {
                // on admin row and existing product check if url_key in database
                if ($storeViewCodeValue === StoreViewCodes::ADMIN && $product) {
                    // initialize store ID from store code
                    $storeId = $this->getSubject()->getRowStoreId($storeViewCode);
                    // load the url_key attribute
                    $urlKey = $this->loadExistingUrlKey($product, $storeViewCode);
                    // if url_key attribute found and same store as searched
                    if ($urlKey && $urlKey[MemberNames::STORE_ID] == $storeId) {
                        // skip for artefact as default entry
                        continue;
                    }
                }

                // if no arefacts are available, append new data
                $this->createArtefact($sku, $storeViewCode);
            }
        }

        // append the artefacts that has to be exported to the subject
        $this->addArtefacts($this->artefacts);
    }

    /**
     * Tries to load the URL key for the passed product and store view code and return's it.
     *
     * @param array  $product       The product to return the URL key for
     * @param string $storeViewCode The store view code of the URL key
     *
     * @return array|null The array with the URL key attribute data
     */
    protected function loadExistingUrlKey(array $product, string $storeViewCode)
    {

        // initialize last entity as primary key
        $pk = $this->getPrimaryKeyId($product);

        // initialize the entity type ID
        $entityType = $this->getSubject()->getEntityType();
        $entityTypeId = (integer) $entityType[MemberNames::ENTITY_TYPE_ID];

        // initialize store ID from store code
        $storeId = $this->getSubject()->getRowStoreId($storeViewCode);

        // take a look if url_key already exist
        return $this->getProductUrlRewriteProcessor()->loadVarcharAttributeByAttributeCodeAndEntityTypeIdAndStoreIdAndPrimaryKey(
            ColumnKeys::URL_KEY,
            $entityTypeId,
            $storeId,
            $pk
        );
    }

    /**
     * @param array $product From loadProduct
     * @return mixed
     */
    protected function getPrimaryKeyId(array $product)
    {
        return $product[$this->getProductUrlRewriteProcessor()->getPrimaryKeyMemberName()];
    }

    /**
     * Creates a new artefact, pre-initialized with the values from the admin row.
     *
     * @param string $sku           The sku for the new url_key
     * @param string $storeViewCode The Storeview code
     *
     * @return void
     */
    protected function createArtefact(string $sku, string $storeViewCode) : void
    {

        // create the new artefact and return it
        $artefact = $this->newArtefact(
            array(
                ColumnKeys::SKU                => $sku,
                ColumnKeys::STORE_VIEW_CODE    => $storeViewCode,
                ColumnKeys::CATEGORIES         => $this->getValue(ColumnKeys::CATEGORIES, isset($this->adminRow[$sku][ColumnKeys::CATEGORIES]) ? $this->adminRow[$sku][ColumnKeys::CATEGORIES]: null),
                ColumnKeys::PRODUCT_WEBSITES   => $this->getValue(ColumnKeys::PRODUCT_WEBSITES, isset($this->adminRow[$sku][ColumnKeys::PRODUCT_WEBSITES]) ? $this->adminRow[$sku][ColumnKeys::PRODUCT_WEBSITES] : null),
                ColumnKeys::VISIBILITY         => $this->getValue(ColumnKeys::VISIBILITY, isset($this->adminRow[$sku][ColumnKeys::VISIBILITY]) ? $this->adminRow[$sku][ColumnKeys::VISIBILITY] : null),
                ColumnKeys::URL_KEY            => $this->getValue(ColumnKeys::URL_KEY, isset($this->adminRow[$sku][ColumnKeys::URL_KEY]) ? $this->adminRow[$sku][ColumnKeys::URL_KEY] : null)
            ),
            array(
                ColumnKeys::SKU                => ColumnKeys::SKU,
                ColumnKeys::STORE_VIEW_CODE    => ColumnKeys::STORE_VIEW_CODE,
                ColumnKeys::CATEGORIES         => ColumnKeys::CATEGORIES,
                ColumnKeys::PRODUCT_WEBSITES   => ColumnKeys::PRODUCT_WEBSITES,
                ColumnKeys::VISIBILITY         => ColumnKeys::VISIBILITY,
                ColumnKeys::URL_KEY            => ColumnKeys::URL_KEY,
            )
        );

        // append the artefact to the artefacts
        $this->artefacts[] = $artefact;
    }

    /**
     * Load's and return's the product with the passed SKU.
     *
     * @param string $sku The SKU of the product to load
     *
     * @return array The product
     */
    protected function loadProduct($sku)
    {
        return $this->getProductUrlRewriteProcessor()->loadProduct($sku);
    }

    /**
     * Returns an array with the codes of the store views related with the passed website code.
     *
     * @param string $websiteCode The code of the website to return the store view codes for
     *
     * @return array The array with the matching store view codes
     */
    protected function getStoreViewCodesByWebsiteCode($websiteCode)
    {
        return $this->getSubject()->getStoreViewCodesByWebsiteCode($websiteCode);
    }

    /**
     * Queries whether or not artefacts for the passed type and entity ID are available.
     *
     * @param string $type     The artefact type, e. g. configurable
     * @param string $entityId The entity ID to return the artefacts for
     *
     * @return boolean TRUE if artefacts are available, else FALSE
     */
    protected function hasArtefactsByTypeAndEntityId($type, $entityId)
    {
        return $this->getSubject()->hasArtefactsByTypeAndEntityId($type, $entityId);
    }

    /**
     * Return the artefacts for the passed type and entity ID.
     *
     * @param string $type     The artefact type, e. g. configurable
     * @param string $entityId The entity ID to return the artefacts for
     *
     * @return array The array with the artefacts
     * @throws \Exception Is thrown, if no artefacts are available
     */
    protected function getArtefactsByTypeAndEntityId($type, $entityId)
    {
        return $this->getSubject()->getArtefactsByTypeAndEntityId($type, $entityId);
    }

    /**
     * Create's and return's a new empty artefact entity.
     *
     * @param array $columns             The array with the column data
     * @param array $originalColumnNames The array with a mapping from the old to the new column names
     *
     * @return array The new artefact entity
     */
    protected function newArtefact(array $columns, array $originalColumnNames)
    {
        return $this->getSubject()->newArtefact($columns, $originalColumnNames);
    }

    /**
     * Add the passed product type artefacts to the product with the
     * last entity ID.
     *
     * @param array $artefacts The product type artefacts
     *
     * @return void
     * @uses \TechDivision\Import\Product\Media\Subjects\MediaSubject::getLastEntityId()
     */
    protected function addArtefacts(array $artefacts)
    {
        $this->getSubject()->addArtefacts(ProductUrlRewriteObserver::ARTEFACT_TYPE, $artefacts);
    }
}
