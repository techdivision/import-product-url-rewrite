<?php

/**
 * TechDivision\Import\Product\UrlRewrit\Services\ProductUrlRewriteProcessorInterface
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

namespace TechDivision\Import\Product\UrlRewrite\Services;

use TechDivision\Import\Product\Services\ProductProcessorInterface;

/**
 * Interface for a product URL rewrite processor.
 *
 * @author    Tim Wagner <t.wagner@techdivision.com>
 * @copyright 2016 TechDivision GmbH <info@techdivision.com>
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      https://github.com/techdivision/import-product-url-rewrite
 * @link      http://www.techdivision.com
 */
interface ProductUrlRewriteProcessorInterface extends ProductProcessorInterface
{

    /**
     * Return's the action with the URL rewrite CRUD methods.
     *
     * @return \TechDivision\Import\Dbal\Actions\ActionInterface The action instance
     */
    public function getUrlRewriteAction();

    /**
     * Return's the action with the URL rewrite product category CRUD methods.
     *
     * @return \TechDivision\Import\Dbal\Actions\ActionInterface The action instance
     */
    public function getUrlRewriteProductCategoryAction();

    /**
     * Return's the repository to load the product varchar attribute with.
     *
     * @return \TechDivision\Import\Product\Repositories\ProductVarcharRepositoryInterface The repository instance
     */
    public function getProductVarcharRepository();

    /**
     * Return's the repository to load the URL rewrites with.
     *
     * @return \TechDivision\Import\Product\UrlRewrite\Repositories\UrlRewriteRepositoryInterface The repository instance
     */
    public function getUrlRewriteRepository();

    /**
     * Return's the repository to load the URL rewrite product category relations with.
     *
     * @return \TechDivision\Import\Product\UrlRewrite\Repositories\UrlRewriteProductCategoryRepositoryInterface The repository instance
     */
    public function getUrlRewriteProductCategoryRepository();

    /**
     * Return's the repository to load the products with.
     *
     * @return \TechDivision\Import\Product\Repositories\ProductRepositoryInterface The repository instance
     */
    public function getProductRepository();

    /**
     * Return's the URL rewrites for the passed URL entity type and ID.
     *
     * @param string  $entityType The entity type to load the URL rewrites for
     * @param integer $entityId   The entity ID to laod the rewrites for
     *
     * @return array The URL rewrites
     */
    public function getUrlRewritesByEntityTypeAndEntityId($entityType, $entityId);

    /**
     * Return's the URL rewrites for the passed URL entity type and ID.
     *
     * @param string  $entityType The entity type to load the URL rewrites for
     * @param integer $entityId   The entity ID to load the URL rewrites for
     * @param integer $storeId    The store ID to load the URL rewrites for
     *
     * @return array The URL rewrites
     */
    public function getUrlRewritesByEntityTypeAndEntityIdAndStoreId($entityType, $entityId, $storeId);

    /**
     * Return's an array with the URL rewrites for the passed SKU.
     *
     * @param string $sku The SKU to load the URL rewrites for
     *
     * @return array The URL rewrites
     */
    public function getUrlRewritesBySku($sku);

    /**
     * Return's an array with the URL rewrite product category relations for the passed SKU.
     *
     * @param string $sku The SKU to load the URL rewrite product category relations for
     *
     * @return array The URL rewrite product category relations
     */
    public function getUrlRewriteProductCategoriesBySku($sku);

    /**
     * Load's and return's the product with the passed SKU.
     *
     * @param string $sku The SKU of the product to load
     *
     * @return array The product
     */
    public function loadProduct($sku);

    /**
     * Load's and return's the varchar attribute with the passed params.
     *
     * @param integer $attributeCode The attribute code of the varchar attribute
     * @param integer $entityTypeId  The entity type ID of the varchar attribute
     * @param integer $storeId       The store ID of the varchar attribute
     * @param string  $value         The value of the varchar attribute
     *
     * @return array|null The varchar attribute
     */
    public function loadProductVarcharAttributeByAttributeCodeAndEntityTypeIdAndStoreIdAndValue($attributeCode, $entityTypeId, $storeId, $value);

    /**
     * Return's the URL rewrite product category relation for the passed
     * URL rewrite ID.
     *
     * @param integer $urlRewriteId The URL rewrite ID to load the URL rewrite product category relation for
     *
     * @return array|false The URL rewrite product category relation
     */
    public function loadUrlRewriteProductCategory($urlRewriteId);

    /**
     * Persist's the URL write with the passed data.
     *
     * @param array       $row  The URL rewrite to persist
     * @param string|null $name The name of the prepared statement that has to be executed
     *
     * @return string The ID of the persisted entity
     */
    public function persistUrlRewrite($row, $name = null);

    /**
     * Persist's the URL rewrite product => category relation with the passed data.
     *
     * @param array       $row  The URL rewrite product => category relation to persist
     * @param string|null $name The name of the prepared statement that has to be executed
     *
     * @return void
     */
    public function persistUrlRewriteProductCategory($row, $name = null);

    /**
     * Delete's the URL rewrite with the passed attributes.
     *
     * @param array       $row  The attributes of the entity to delete
     * @param string|null $name The name of the prepared statement that has to be executed
     *
     * @return void
     */
    public function deleteUrlRewrite($row, $name = null);
}
