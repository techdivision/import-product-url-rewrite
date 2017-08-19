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
            $websiteCodes = $this->getValue(ColumnKeys::PRODUCT_WEBSITES, array(), array($this, 'extract'));

            // load the store view codes of all websites
            foreach ($websiteCodes as $websiteCode) {
                $storeViewCodes = array_merge($storeViewCodes, $this->getStoreViewCodesByWebsiteCode($websiteCode));
            }

        } else {
            array_push($storeViewCodes, $storeViewCode);
        }

        // iterate over the available image fields
        foreach ($storeViewCodes as $storeViewCode) {
            // prepare the new base image
            $artefact = $this->newArtefact(
                array(
                    ColumnKeys::SKU                => $sku,
                    ColumnKeys::STORE_VIEW_CODE    => $storeViewCode
                ),
                array(
                    ColumnKeys::SKU                => ColumnKeys::SKU,
                    ColumnKeys::STORE_VIEW_CODE    => ColumnKeys::STORE_VIEW_CODE
                )
            );

            // append the base image to the artefacts
            $this->artefacts[] = $artefact;
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
