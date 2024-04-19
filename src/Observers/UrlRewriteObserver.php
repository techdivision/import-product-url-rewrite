<?php

/**
 * TechDivision\Import\Product\UrlRewrite\Observers\UrlRewriteObserver
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
use TechDivision\Import\Utils\RegistryKeys;
use TechDivision\Import\Utils\StoreViewCodes;
use TechDivision\Import\Subjects\SubjectInterface;
use TechDivision\Import\Observers\ObserverFactoryInterface;
use TechDivision\Import\Product\Utils\VisibilityKeys;
use TechDivision\Import\Product\Observers\AbstractProductImportObserver;
use TechDivision\Import\Product\UrlRewrite\Utils\ColumnKeys;
use TechDivision\Import\Product\UrlRewrite\Utils\MemberNames;
use TechDivision\Import\Product\UrlRewrite\Utils\CoreConfigDataKeys;
use TechDivision\Import\Product\UrlRewrite\Services\ProductUrlRewriteProcessorInterface;

/**
 * Observer that creates/updates the product's URL rewrites.
 *
 * @author    Tim Wagner <t.wagner@techdivision.com>
 * @copyright 2016 TechDivision GmbH <info@techdivision.com>
 * @license   https://opensource.org/licenses/MIT
 * @link      https://github.com/techdivision/import-product-url-rewrite
 * @link      http://www.techdivision.com
 */
class UrlRewriteObserver extends AbstractProductImportObserver implements ObserverFactoryInterface
{

    /**
     * The entity type to load the URL rewrites for.
     *
     * @var string
     */
    const ENTITY_TYPE = 'product';

    /**
     * The key for the category in the metadata.
     *
     * @var string
     */
    const CATEGORY_ID = 'category_id';

    /**
     * The URL key from the CSV file column that has to be processed by the observer.
     *
     * @var string
     */
    protected $urlKey;

    /**
     * The actual category ID to process.
     *
     * @var integer
     */
    protected $categoryId;

    /**
     * The actual entity ID to process.
     *
     * @var integer
     */
    protected $entityId;

    /**
     * The ID of the recently created URL rewrite.
     *
     * @var integer
     */
    protected $urlRewriteId;

    /**
     * The array with the URL rewrites that has to be created.
     *
     * @var array
     */
    protected $urlRewrites = array();

    /**
     * The array with the category IDs related with the product.
     *
     * @var array
     */
    protected $productCategoryIds = array();

    /**
     * The array with the root categories.
     *
     * @var array
     */
    protected $rootCategories = array();

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
     * @param \TechDivision\Import\Observers\StateDetectorInterface                                $stateDetector              The state detector instance
     */
    public function __construct(
        ProductUrlRewriteProcessorInterface $productUrlRewriteProcessor,
        StateDetectorInterface $stateDetector = null
    ) {
        $this->productUrlRewriteProcessor = $productUrlRewriteProcessor;

        // pass the state detector to the parent method
        parent::__construct($stateDetector);
    }

    /**
     * Will be invoked by the observer visitor when a factory has been defined to create the observer instance.
     *
     * @param \TechDivision\Import\Subjects\SubjectInterface $subject The subject instance
     *
     * @return \TechDivision\Import\Observers\ObserverInterface The observer instance
     */
    public function createObserver(SubjectInterface $subject)
    {

        // load the root categories
        $rootCategories = $subject->getRootCategories();

        // initialize the array with the root categories
        // by using the entity ID as index
        foreach ($rootCategories as $rootCategory) {
            $this->rootCategories[(int) $rootCategory[MemberNames::ENTITY_ID]] = $rootCategory;
        }

        // return the initialized instance
        return $this;
    }

    /**
     * Return's the product bunch processor instance.
     *
     * @return \TechDivision\Import\Product\UrlRewrite\Services\ProductUrlRewriteProcessorInterface The product bunch processor instance
     */
    protected function getProductUrlRewriteProcessor()
    {
        return $this->productUrlRewriteProcessor;
    }

    /**
     * Will be invoked by the action on the events the listener has been registered for.
     *
     * @param \TechDivision\Import\Subjects\SubjectInterface $subject The subject instance
     *
     * @return array The modified row
     * @throws \Exception Is thrown, if the product is not available or no URL key has been specified
     * @see \TechDivision\Import\Observers\ObserverInterface::handle()
     */
    public function handle(SubjectInterface $subject)
    {

        // initialize the row
        $this->setSubject($subject);
        $this->setRow($subject->getRow());

        // try to load the entity ID for the product with the passed SKU
        if ($product = $this->loadProduct($sku = $this->getValue(ColumnKeys::SKU))) {
            $this->setLastEntityId($this->entityId = $product[MemberNames::ENTITY_ID]);
        } else {
            // prepare a log message
            $message = sprintf('Product with SKU "%s" can\'t be loaded to create URL rewrites', $sku);
            // query whether or not we're in debug mode
            if (!$this->getSubject()->isStrictMode()) {
                $this->getSubject()->getSystemLogger()->warning($message);
                $this->mergeStatus(
                    array(
                        RegistryKeys::NO_STRICT_VALIDATIONS => array(
                            basename($this->getFilename()) => array(
                                $this->getLineNumber() => array(
                                    ColumnKeys::SKU => $message
                                )
                            )
                        )
                    )
                );
                return $this->getRow();
            } else {
                throw new \Exception($this->appendExceptionSuffix($message));
            }
        }

        // try to load the URL key
        if ($this->hasValue(ColumnKeys::URL_KEY)) {
            $this->urlKey = $this->getValue(ColumnKeys::URL_KEY);
        } else {
            // prepare a log message
            $message = sprintf('Can\'t find a value in column "url_key" for product with SKU "%s"', $sku);
            // query whether or not we're in debug mode
            if (!$this->getSubject()->isStrictMode()) {
                $this->getSubject()->getSystemLogger()->warning($message);
                $this->mergeStatus(
                    array(
                        RegistryKeys::NO_STRICT_VALIDATIONS => array(
                            basename($this->getFilename()) => array(
                                $this->getLineNumber() => array(
                                    ColumnKeys::URL_KEY => $message
                                )
                            )
                        )
                    )
                );
                return $this->getRow();
            } else {
                throw new \Exception($this->appendExceptionSuffix($message));
            }
        }

        // initialize the store view code
        $this->getSubject()->prepareStoreViewCode();

        // load the store view - if no store view has been set we use the admin store view as default
        $storeViewCode = $this->getSubject()->getStoreViewCode(StoreViewCodes::ADMIN);

        // query whether or not the row has already been processed
        if ($this->storeViewHasBeenProcessed($sku, $storeViewCode)) {
            // log a message
            $this->getSubject()
                ->getSystemLogger()
                ->debug(
                    sprintf(
                        'URL rewrites for SKU "%s" + store view code "%s" has already been processed',
                        $sku,
                        $storeViewCode
                    )
                );

            // return without creating any rewrites
            return $this->getRow();
        };

        // stop processing as we don't want to create URL rewrites for the admin store view
        if ($storeViewCode === StoreViewCodes::ADMIN) {
            // log a message and return
            $this->getSubject()
                 ->getSystemLogger()
                 ->debug(
                     sprintf(
                         'Store with code "%s" is not active, no URL rewrites will be created for product with SKU "%s"',
                         $storeViewCode,
                         $sku
                     )
                 );

            // return without creating any rewrites
            return $this->getRow();
        }

        // stop processing if the store is NOT active
        if (!$this->getSubject()->storeIsActive($storeViewCode)) {
            // log a message and return
            $this->getSubject()
                 ->getSystemLogger()
                 ->debug(
                     sprintf(
                         'Store with code "%s" is not active, no URL rewrites will be created for product with SKU "%s"',
                         $storeViewCode,
                         $sku
                     )
                 );

            // return without creating any rewrites
            return $this->getRow();
        }

        // only map the visibility for the product row related to the default store view
        if (!$this->hasBeenProcessed($sku)) {
            $this->addEntityIdVisibilityIdMapping($this->getValue(ColumnKeys::VISIBILITY));
        }

        // process the functionality and return the row
        $this->process();

        // return the processed row
        return $this->getRow();
    }

    /**
     * Process the observer's business logic.
     *
     * @return void
     */
    protected function process()
    {

        // prepare the URL rewrites
        $this->prepareUrlRewrites();

        // iterate over the categories and create the URL rewrites
        foreach ($this->urlRewrites as $categoryId => $urlRewrite) {
            // initialize and persist the URL rewrite
            if ($urlRewrite = $this->initializeUrlRewrite($urlRewrite)) {
                // initialize URL rewrite and catagory ID
                $this->categoryId = $categoryId;

                try {
                    // persist the URL rewrite
                    if ($this->hasChanges($urlRewrite)) {
                        try {
                            $this->urlRewriteId = $this->persistUrlRewrite($urlRewrite);
                        } catch (\PDOException $pdoe) {
                            $this->handleDuplicateUrlKeyExceptionWithStrictMode($urlRewrite, $pdoe);
                        }
                    } else {
                        $this->urlRewriteId = $urlRewrite[MemberNames::URL_REWRITE_ID];
                    }

                    /*
                     * Attention! Stop processing, if this is a root category, because Magento needs explicitly
                     * NO URL rewrite product category relation to render canonical and meta og:url tag!
                     */
                    if ($this->isRootCategory($this->getCategory($categoryId))) {
                        continue;
                    }

                    // initialize and persist the URL rewrite product => category relation
                    $urlRewriteProductCategory = $this->initializeUrlRewriteProductCategory(
                        $this->prepareUrlRewriteProductCategoryAttributes()
                    );

                    // persist the URL rewrite product category relation
                    if ($this->hasChanges($urlRewriteProductCategory)) {
                        try {
                            $this->persistUrlRewriteProductCategory($urlRewriteProductCategory);
                        } catch (\PDOException $pdoe) {
                            $this->handleDuplicateUrlKeyExceptionWithStrictMode($urlRewriteProductCategory, $pdoe);
                        }
                    }
                } catch (\Exception $e) {
                    if (!$this->getSubject()->isStrictMode()) {
                        $message = sprintf('URL persist error for SKU "%s"! Detail: %s', $this->getValue(ColumnKeys::SKU), $e->getMessage());
                        $this->getSubject()
                            ->getSystemLogger()
                            ->warning($this->getSubject()->appendExceptionSuffix($message));
                        $this->mergeStatus(
                            array(
                                RegistryKeys::NO_STRICT_VALIDATIONS => array(
                                    basename($this->getFilename()) => array(
                                        $this->getLineNumber() => array(
                                            ColumnKeys::URL_KEY =>  $message
                                        )
                                    )
                                )
                            )
                        );
                    } else {
                        throw $e;
                    }
                }
            }
        }
    }

    /**
     * Initialize the category product with the passed attributes and returns an instance.
     *
     * @param array $attr The category product attributes
     *
     * @return array The initialized category product
     */
    protected function initializeUrlRewrite(array $attr)
    {
        return $attr;
    }

    /**
     * Initialize the URL rewrite product => category relation with the passed attributes
     * and returns an instance.
     *
     * @param array $attr The URL rewrite product => category relation attributes
     *
     * @return array The initialized URL rewrite product => category relation
     */
    protected function initializeUrlRewriteProductCategory($attr)
    {
        return $attr;
    }

    /**
     * Prepare's the URL rewrites that has to be created/updated.
     *
     * @return void
     */
    protected function prepareUrlRewrites()
    {

        // (re-)initialize the array for the URL rewrites and the product category IDs
        $this->urlRewrites = array();
        $this->productCategoryIds = array();

        // do NOT create new URL rewrites, if the product is NOT visible (any more), BUT
        // handle existing URL rewrites, e. g. to remove and clean up the URL rewrites
        if (!$this->isVisible()) {
             // log a message
             $this->getSubject()
                 ->getSystemLogger()
                 ->debug(
                     sprintf(
                         'Product with SKU "%s" is not visible, so no URL rewrites will be created',
                         $this->getValue(ColumnKeys::SKU)
                     )
                 );

             // return without creating any rewrites
             return;
        }

        // load the root category of the default store view (as we're in the
        // default row and does not have a store view code), because we need
        // that to create the default product URL rewrite
        $rootCategory = $this->getRootCategory();

        // at least, add the root category ID to the category => product relations
        $this->productCategoryIds[] = $rootCategory[MemberNames::ENTITY_ID];

        // load the store view code from the appropriate column
        $storeViewCode = $this->getValue(ColumnKeys::STORE_VIEW_CODE);

        // load the category paths from the import file
        $paths = $this->getValue(ColumnKeys::CATEGORIES, array(), array($this, 'explode'));

        // append the category => product relations found
        $iterator = 0;
        foreach ($paths as $path) {
            $iterator++;
            try {
                // try to load the category for the given path
                $category = $this->getCategoryByPath($path, $storeViewCode);
                // resolve the product's categories recursively
                $this->resolveCategoryIds($category[MemberNames::ENTITY_ID], true, $storeViewCode);
            } catch (\Exception $e) {
                // query whether or not strict mode has been enabled
                if (!$this->getSubject()->isStrictMode()) {
                    $message = sprintf("Error with category '%s'", $path);
                    $this->getSubject()
                         ->getSystemLogger()
                         ->warning($this->getSubject()->appendExceptionSuffix($message));
                    $this->mergeStatus(
                        array(
                            RegistryKeys::NO_STRICT_VALIDATIONS => array(
                                basename($this->getFilename()) => array(
                                    $this->getLineNumber() . '-cat-' . $iterator => array(
                                        ColumnKeys::CATEGORIES => $this->getSubject()->appendExceptionSuffix($message)
                                    )
                                )
                            )
                        )
                    );
                } else {
                    throw $e;
                }
            }
        }

        // initialize the member varialbe with the category ID
        // and prepare the attributes for each URL rewrite
        foreach ($this->productCategoryIds as $this->categoryId) {
            try {
                $preparedAttributes =  $this->prepareAttributes($storeViewCode);
                $this->urlRewrites[$this->categoryId] = $preparedAttributes;
            } catch (\Exception $e) {
                // query whether or not strict mode has been enabled
                if (!$this->getSubject()->isStrictMode()) {
                    $this->getSubject()
                        ->getSystemLogger()
                        ->warning($this->getSubject()->appendExceptionSuffix($e->getMessage()));
                    $this->mergeStatus(
                        array(
                            RegistryKeys::NO_STRICT_VALIDATIONS => array(
                                basename($this->getFilename()) => array(
                                    $this->getLineNumber() . '-cat-' . $iterator => array(
                                        ColumnKeys::CATEGORIES => $this->getSubject()->appendExceptionSuffix($e->getMessage())
                                    )
                                )
                            )
                        )
                    );
                } else {
                    throw $e;
                }
            }
        }
    }

    /**
     * Resolve's the parent categories of the category with the passed ID and relate's
     * it with the product with the passed ID, if the category is top level OR has the
     * anchor flag set.
     *
     * @param integer $categoryId    The ID of the category to resolve the parents
     * @param boolean $topLevel      TRUE if the passed category has top level, else FALSE
     * @param string  $storeViewCode The store view code to resolve the category IDs for
     *
     * @return void
     */
    protected function resolveCategoryIds($categoryId, $topLevel = false, $storeViewCode = StoreViewCodes::ADMIN)
    {

        // load the data of the category with the passed ID
        $category = $this->getCategory($categoryId, $storeViewCode);

        // return immediately if this is a root category
        if ($this->isRootCategory($category)) {
            return;
        }

        // create the product category relation for the current category
        $this->createProductCategoryRelation($category, $topLevel);

        // try to resolve the parent category IDs
        $this->resolveCategoryIds($category[MemberNames::PARENT_ID], false, $storeViewCode);
    }

    /**
     * Adds the entity product relation if necessary.
     *
     * @param array   $category The category to create the relation for
     * @param boolean $topLevel Whether or not the category has top level
     *
     * @return void
     */
    protected function createProductCategoryRelation($category, $topLevel)
    {

        // query whether or not the product has already been related
        if (in_array($category[MemberNames::ENTITY_ID], $this->productCategoryIds)) {
            return;
        }

        // load the backend configuration value for whether or not the catalog product rewrites should be generated
        $generateCategoryRewrites = $this->getGenerateCategoryProductRewritesOptionValue();

        // abort if generating product categories is disabled and category is not root
        if ($generateCategoryRewrites === false && $this->isRootCategory($category) === false) {
            return;
        }

        // create relation if the category is top level or has the anchor flag set
        if ($topLevel || (integer) $category[MemberNames::IS_ANCHOR] === 1) {
            $this->productCategoryIds[] = $category[MemberNames::ENTITY_ID];
            return;
        }

        // log a debug messsage that the URL rewrite has not
        // been created because of the missing anchor flag
        $this->getSubject()
            ->getSystemLogger()
            ->debug(
                sprintf(
                    'Don\'t create URL rewrite for category "%s" because of missing anchor flag',
                    $category[MemberNames::PATH]
                )
            );
    }

    /**
     * Returns the option value for whether or not to generate product catalog rewrites as well.
     *
     * @return bool
     */
    protected function getGenerateCategoryProductRewritesOptionValue()
    {
        return (bool) $this->getSubject()->getCoreConfigData(
            CoreConfigDataKeys::CATALOG_SEO_GENERATE_CATEGORY_PRODUCT_REWRITES,
            true
        );
    }

    /**
     * Prepare the attributes of the entity that has to be persisted.
     *
     * @param string $storeViewCode The store view code to prepare the attributes for
     *
     * @return array The prepared attributes
     */
    protected function prepareAttributes($storeViewCode)
    {

        // load the store ID to use
        $storeId = $this->getSubject()->getRowStoreId();

        // load the category to create the URL rewrite for
        $category = $this->getCategory($this->categoryId, $storeViewCode);

        // initialize the values
        $metadata = $this->prepareMetadata($category);
        $targetPath = $this->prepareTargetPath($category);
        $requestPath = $this->prepareRequestPath($category);

        // return the prepared URL rewrite
        return $this->initializeEntity(
            array(
                MemberNames::ENTITY_TYPE      => UrlRewriteObserver::ENTITY_TYPE,
                MemberNames::ENTITY_ID        => $this->entityId,
                MemberNames::REQUEST_PATH     => $requestPath,
                MemberNames::TARGET_PATH      => $targetPath,
                MemberNames::REDIRECT_TYPE    => 0,
                MemberNames::STORE_ID         => $storeId,
                MemberNames::DESCRIPTION      => null,
                MemberNames::IS_AUTOGENERATED => 1,
                MemberNames::METADATA         => $metadata ? json_encode($metadata) : null
            )
        );
    }

    /**
     * Prepare's the URL rewrite product => category relation attributes.
     *
     * @return array The prepared attributes
     */
    protected function prepareUrlRewriteProductCategoryAttributes()
    {

        // return the prepared product
        return $this->initializeEntity(
            array(
                MemberNames::PRODUCT_ID     => $this->entityId,
                MemberNames::CATEGORY_ID    => $this->categoryId,
                MemberNames::URL_REWRITE_ID => $this->urlRewriteId
            )
        );
    }

    /**
     * Prepare's the target path for a URL rewrite.
     *
     * @param array $category The categroy with the URL path
     *
     * @return string The target path
     */
    protected function prepareTargetPath(array $category)
    {

        // query whether or not, the category is the root category
        if ($this->isRootCategory($category)) {
            $targetPath = sprintf('catalog/product/view/id/%d', $this->entityId);
        } else {
            $targetPath = sprintf('catalog/product/view/id/%d/category/%d', $this->entityId, $category[MemberNames::ENTITY_ID]);
        }

        // return the target path
        return $targetPath;
    }

    /**
     * Prepare's the request path for a URL rewrite or the target path for a 301 redirect.
     *
     * @param array $category The categroy with the URL path
     *
     * @return string The request path
     * @throws \RuntimeException Is thrown, if the passed category has no or an empty value for attribute "url_path"
     */
    protected function prepareRequestPath(array $category)
    {

        // load the product URL suffix to use
        $urlSuffix = $this->getSubject()->getCoreConfigData(CoreConfigDataKeys::CATALOG_SEO_PRODUCT_URL_SUFFIX, '.html');

        // query whether or not, the category is the root category
        if ($this->isRootCategory($category)) {
            return sprintf('%s%s', $this->urlKey, $urlSuffix);
        } else {
            // query whether or not the category's "url_path" attribute, necessary to create a valid "request_path", is available
            if (isset($category[MemberNames::URL_PATH]) && $category[MemberNames::URL_PATH]) {
                return sprintf('%s/%s%s', $category[MemberNames::URL_PATH], $this->urlKey, $urlSuffix);
            }
        }

        // throw an exception if the category's "url_path" attribute is NOT available
        throw new \RuntimeException(
            $this->appendExceptionSuffix(
                sprintf(
                    'Can\'t find mandatory attribute "%s" for category ID "%d", necessary to build a valid "request_path"',
                    MemberNames::URL_PATH,
                    $category[MemberNames::ENTITY_ID]
                )
            )
        );
    }

    /**
     * Prepare's the URL rewrite's metadata with the passed category values.
     *
     * @param array $category The category used for preparation
     *
     * @return array|null The metadata
     */
    protected function prepareMetadata(array $category)
    {

        // initialize the metadata
        $metadata = array();

        // query whether or not, the passed category IS the root category
        if ($this->isRootCategory($category)) {
            return;
        }

        // if not, set the category ID in the metadata
        $metadata[UrlRewriteObserver::CATEGORY_ID] = (string) $category[MemberNames::ENTITY_ID];

        // return the metadata
        return $metadata;
    }

    /**
     * Query whether or not the actual entity is visible.
     *
     * @return boolean TRUE if the entity is visible, else FALSE
     */
    protected function isVisible()
    {
        return $this->getEntityIdVisibilityIdMapping() !== VisibilityKeys::VISIBILITY_NOT_VISIBLE;
    }

    /**
     * Return's the visibility for the passed entity ID, if it already has been mapped. The mapping will be created
     * by calling <code>\TechDivision\Import\Product\Subjects\BunchSubject::getVisibilityIdByValue</code> which will
     * be done by the <code>\TechDivision\Import\Product\Callbacks\VisibilityCallback</code>.
     *
     * @return integer The visibility ID
     * @throws \Exception Is thrown, if the entity ID has not been mapped
     * @see \TechDivision\Import\Product\Subjects\BunchSubject::getVisibilityIdByValue()
     */
    protected function getEntityIdVisibilityIdMapping()
    {
        return $this->getSubject()->getEntityIdVisibilityIdMapping();
    }

    /**
     * Return's the root category for the actual view store.
     *
     * @return array The store's root category
     * @throws \Exception Is thrown if the root category for the passed store code is NOT available
     */
    protected function getRootCategory()
    {
        return $this->getSubject()->getRootCategory();
    }

    /**
     * Return's TRUE if the passed category IS the root category, else FALSE.
     *
     * @param array $category The category to query
     *
     * @return boolean TRUE if the passed category IS the root category
     */
    protected function isRootCategory(array $category)
    {
        return isset($this->rootCategories[$category[MemberNames::ENTITY_ID]]);
    }

    /**
     * Return's the category with the passed path.
     *
     * @param string $path          The path of the category to return
     * @param string $storeViewCode The store view code of the category to return
     *
     * @return array The category
     */
    protected function getCategoryByPath($path, $storeViewCode = StoreViewCodes::ADMIN)
    {
        return $this->getSubject()->getCategoryByPath($path, $storeViewCode);
    }

    /**
     * Return's the category with the passed ID.
     *
     * @param integer $categoryId    The ID of the category to return
     * @param string  $storeViewCode The store view code of category to return
     *
     * @return array The category data
     */
    protected function getCategory($categoryId, $storeViewCode = StoreViewCodes::ADMIN)
    {
        return $this->getSubject()->getCategory($categoryId, $storeViewCode);
    }

    /**
     * Persist's the URL rewrite with the passed data.
     *
     * @param array $row The URL rewrite to persist
     *
     * @return string The ID of the persisted entity
     */
    protected function persistUrlRewrite($row)
    {
        return $this->getProductUrlRewriteProcessor()->persistUrlRewrite($row);
    }

    /**
     * Persist's the URL rewrite product => category relation with the passed data.
     *
     * @param array $row The URL rewrite product => category relation to persist
     *
     * @return void
     */
    protected function persistUrlRewriteProductCategory($row)
    {
        return $this->getProductUrlRewriteProcessor()->persistUrlRewriteProductCategory($row);
    }

    /**
     * Queries whether or not the passed SKU and store view code has already been processed.
     *
     * @param string $sku           The SKU to check been processed
     * @param string $storeViewCode The store view code to check been processed
     *
     * @return boolean TRUE if the SKU and store view code has been processed, else FALSE
     */
    protected function storeViewHasBeenProcessed($sku, $storeViewCode)
    {
        return $this->getSubject()->storeViewHasBeenProcessed($sku, $storeViewCode);
    }

    /**
     * Add the entity ID => visibility mapping for the actual entity ID.
     *
     * @param string $visibility The visibility of the actual entity to map
     *
     * @return void
     */
    protected function addEntityIdVisibilityIdMapping($visibility)
    {
        $this->getSubject()->addEntityIdVisibilityIdMapping($visibility);
    }

    /**
     * Set's the ID of the product that has been created recently.
     *
     * @param string $lastEntityId The entity ID
     *
     * @return void
     */
    protected function setLastEntityId($lastEntityId)
    {
        $this->getSubject()->setLastEntityId($lastEntityId);
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
     * @param array         $urlRewriteData data of urlrewrite
     * @param \PDOException $pdoe pdo exception
     * @return void
     * @throws \PDOException
     */
    public function handleDuplicateUrlKeyExceptionWithStrictMode(array $urlRewriteData, \PDOException $pdoe): void
    {
        $message = sprintf(
            'Is a "Duplicate entry" PDO exception is thrown: with Urlrewrite Data \\n
                            ("entity_id": "%s" && "request_path": "%s" && "target_path": "%s" && "entity_type": "%s" && "redirect_type": "%s" && "store_id": "%s")',
            $urlRewriteData[MemberNames::ENTITY_ID],
            $urlRewriteData[MemberNames::REQUEST_PATH],
            $urlRewriteData[MemberNames::TARGET_PATH],
            $urlRewriteData[MemberNames::ENTITY_TYPE],
            $urlRewriteData[MemberNames::REDIRECT_TYPE],
            $urlRewriteData[MemberNames::STORE_ID]
        );
        if (!$this->getSubject()->isStrictMode()) {
            $this->getSubject()
                ->getSystemLogger()
                ->warning($this->getSubject()->appendExceptionSuffix($message));
            $this->mergeStatus(
                array(
                    RegistryKeys::NO_STRICT_VALIDATIONS => array(
                        basename($this->getFilename()) => array(
                            $this->getLineNumber() => array(
                                ColumnKeys::URL_KEY => $message
                            )
                        )
                    )
                )
            );
        } else {
            throw new \PDOException($pdoe);
        }
    }
}
