<?php

/**
 * TechDivision\Import\Product\UrlRewrite\Observers\ProductMediaObserver
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

use TechDivision\Import\Product\UrlRewrite\Utils\ColumnKeys;
use TechDivision\Import\Product\Observers\AbstractProductImportObserver;

/**
 * Observer that extracts theproduct's media data from a CSV file to be added to media specifi CSV file.
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

        // initialize the array for the artefacts
        $this->artefacts = array();

        // process the images/additional images
        $this->processImages();
        $this->processAdditionalImages();

        // append the artefacts that has to be exported to the subject
        $this->addArtefacts($this->artefacts);
    }

    /**
     * Parses the column and exports the image data to a separate file.
     *
     * @return void
     */
    protected function processImages()
    {

        // load the store view code
        $storeViewCode = $this->getValue(ColumnKeys::STORE_VIEW_CODE);
        $attributeSetCode = $this->getValue(ColumnKeys::ATTRIBUTE_SET_CODE);

        // load the parent SKU from the row
        $parentSku = $this->getValue(ColumnKeys::SKU);

        // iterate over the available image fields
        foreach ($this->getImageTypes() as $imageColumnName => $labelColumnName) {
            // query whether or not, we've a base image
            if ($image = $this->getValue($imageColumnName)) {
                // initialize the label text
                $labelText = $this->getDefaultImageLabel();

                // query whether or not a custom label text has been passed
                if ($this->hasValue($labelColumnName)) {
                    $this->getValue($labelColumnName);
                }

                // prepare the new base image
                $artefact = $this->newArtefact(
                    array(
                        ColumnKeys::STORE_VIEW_CODE    => $storeViewCode,
                        ColumnKeys::ATTRIBUTE_SET_CODE => $attributeSetCode,
                        ColumnKeys::IMAGE_PARENT_SKU   => $parentSku,
                        ColumnKeys::IMAGE_PATH         => $image,
                        ColumnKeys::IMAGE_PATH_NEW     => $image,
                        ColumnKeys::IMAGE_LABEL        => $labelText
                    ),
                    array(
                        ColumnKeys::STORE_VIEW_CODE    => ColumnKeys::STORE_VIEW_CODE,
                        ColumnKeys::ATTRIBUTE_SET_CODE => ColumnKeys::ATTRIBUTE_SET_CODE,
                        ColumnKeys::IMAGE_PARENT_SKU   => ColumnKeys::SKU,
                        ColumnKeys::IMAGE_PATH         => $imageColumnName,
                        ColumnKeys::IMAGE_PATH_NEW     => $imageColumnName,
                        ColumnKeys::IMAGE_LABEL        => $labelColumnName
                    )
                );

                // append the base image to the artefacts
                $this->artefacts[] = $artefact;
            }
        }
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
