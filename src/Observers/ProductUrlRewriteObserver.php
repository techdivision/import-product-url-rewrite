<?php

/**
 * TechDivision\Import\Product\UrlRewrite\Observers\ProductUrlRewriteObserver
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * PHP version 5
 *
 * @author    Tim Wagner <t.wagner@techdivision.com>
 * @copyright 2016 TechDivision GmbH <info@techdivision.com>
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      https://github.com/techdivision/import-product-url-rewrite
 * @link      http://www.techdivision.com
 */

namespace TechDivision\Import\Product\UrlRewrite\Observers;

use TechDivision\Import\Utils\StoreViewCodes;
use TechDivision\Import\Product\UrlRewrite\Utils\ColumnKeys;
use TechDivision\Import\Product\Observers\AbstractProductImportObserver;

/**
 * Observer that extracts the URL rewrite data to a specific CSV file.
 *
 * @author    Tim Wagner <t.wagner@techdivision.com>
 * @copyright 2016 TechDivision GmbH <info@techdivision.com>
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
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
     * Process the observer's business logic.
     *
     * @return array The processed row
     */
    protected function process()
    {

        // initialize the array for the artefacts and the store view codes
        $this->artefacts = array();
        $storeViewCodes = array();

        // load the SKU from the row
        $sku = $this->getValue(ColumnKeys::SKU);

        // prepare the store view code
        $this->getSubject()->prepareStoreViewCode();

        // try to load the store view code
        $storeViewCode = $this->getSubject()->getStoreViewCode(StoreViewCodes::ADMIN);

        // query whether or not we've a store view code
        if ($storeViewCode === StoreViewCodes::ADMIN) {
            // if not, load the websites the product is related with
            $websiteCodes = $this->getValue(ColumnKeys::PRODUCT_WEBSITES, array(), array($this, 'explode'));

            // load the store view codes of all websites
            foreach ($websiteCodes as $websiteCode) {
                $storeViewCodes = array_merge($storeViewCodes, $this->getStoreViewCodesByWebsiteCode($websiteCode));
            }
        } else {
            array_push($storeViewCodes, $storeViewCode);
        }

        // iterate over the available image fields
        foreach ($storeViewCodes as $storeViewCode) {
            // iterate over the store view codes and query if artefacts are already available
            if ($this->hasArtefactsByTypeAndEntityId(ProductUrlRewriteObserver::ARTEFACT_TYPE, $lastEntityId = $this->getSubject()->getLastEntityId())) {
                // if yes, load the artefacs
                $this->artefacts = $this->getArtefactsByTypeAndEntityId(ProductUrlRewriteObserver::ARTEFACT_TYPE, $lastEntityId);

                // override the existing data with the store view specific one
                for ($i = 0; $i < sizeof($this->artefacts); $i++) {
                    // query whether or not a URL key has be specfied and the store view codes are equal
                    if ($this->hasValue(ColumnKeys::URL_KEY) && $this->artefacts[$i][ColumnKeys::STORE_VIEW_CODE] === $storeViewCode) {
                        // update the URL key
                        $this->artefacts[$i][ColumnKeys::URL_KEY]    = $this->getValue(ColumnKeys::URL_KEY);
                        // update the visibility, if available
                        if ($this->hasValue(ColumnKeys::VISIBILITY)) {
                            $this->artefacts[$i][ColumnKeys::VISIBILITY] = $this->getValue(ColumnKeys::VISIBILITY);
                        }

                        // also update filename and line number
                        $this->artefacts[$i][ColumnKeys::ORIGINAL_DATA][ColumnKeys::ORIGINAL_FILENAME] = $this->getSubject()->getFilename();
                        $this->artefacts[$i][ColumnKeys::ORIGINAL_DATA][ColumnKeys::ORIGINAL_LINE_NUMBER] = $this->getSubject()->getLineNumber();
                    }
                }
            } else {
                // if no arefacts are available, append new data
                $artefact = $this->newArtefact(
                    array(
                        ColumnKeys::SKU                => $sku,
                        ColumnKeys::STORE_VIEW_CODE    => $storeViewCode,
                        ColumnKeys::CATEGORIES         => $this->getValue(ColumnKeys::CATEGORIES),
                        ColumnKeys::PRODUCT_WEBSITES   => $this->getValue(ColumnKeys::PRODUCT_WEBSITES),
                        ColumnKeys::VISIBILITY         => $this->getValue(ColumnKeys::VISIBILITY),
                        ColumnKeys::URL_KEY            => $this->getValue(ColumnKeys::URL_KEY)
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

                // append the base image to the artefacts
                $this->artefacts[] = $artefact;
            }
        }

        // append the artefacts that has to be exported to the subject
        $this->addArtefacts($this->artefacts);
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
