<?php

/**
 * TechDivision\Import\Product\UrlRewrite\Observers\ClearUrlRewriteObserver
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

use TechDivision\Import\Product\Utils\ColumnKeys;
use TechDivision\Import\Product\Observers\AbstractProductImportObserver;
use TechDivision\Import\Product\UrlRewrite\Utils\SqlStatementKeys;
use TechDivision\Import\Product\UrlRewrite\Services\ProductUrlRewriteProcessorInterface;

/**
 * Observer that removes the product URL rewrite with the SKU found in the CSV file.
 *
 * @author    Tim Wagner <t.wagner@techdivision.com>
 * @copyright 2016 TechDivision GmbH <info@techdivision.com>
 * @license   https://opensource.org/licenses/MIT
 * @link      https://github.com/techdivision/import-product-url-rewrite
 * @link      http://www.techdivision.com
 */
class ClearUrlRewriteObserver extends AbstractProductImportObserver
{

    /**
     * The product URL rewrite processor instance.
     *
     * @var \TechDivision\Import\Product\UrlRewrite\Services\ProductUrlRewriteProcessorInterface
     */
    protected $productUrlRewriteProcessor;

    /**
     * Initialize the observer with the passed product URL rewrite processor instance.
     *
     * @param \TechDivision\Import\Product\Services\ProductBunchProcessorInterface $productUrlRewriteProcessor The product URL rewrite processor instance
     */
    public function __construct(ProductUrlRewriteProcessorInterface $productUrlRewriteProcessor)
    {
        $this->productUrlRewriteProcessor = $productUrlRewriteProcessor;
    }

    /**
     * Return's the product URL rewrite processor instance.
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

        // query whether or not, we've found a new SKU => means we've found a new product
        if ($this->isLastSku($sku = $this->getValue(ColumnKeys::SKU))) {
            return;
        }

        // elete the URL rewrites of the product with the passed SKU
        $this->deleteUrlRewrite(array(ColumnKeys::SKU => $sku), SqlStatementKeys::DELETE_URL_REWRITE_BY_SKU);
    }

    /**
     * Delete's the URL rewrite(s) with the passed attributes.
     *
     * @param array       $row  The attributes of the entity to delete
     * @param string|null $name The name of the prepared statement that has to be executed
     *
     * @return void
     */
    protected function deleteUrlRewrite($row, $name = null)
    {
        $this->getProductUrlRewriteProcessor()->deleteUrlRewrite($row, $name);
    }
}
