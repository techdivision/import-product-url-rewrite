<?php

/**
 * TechDivision\Import\Product\UrlRewrite\Utils\SqlStatementKeys
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

namespace TechDivision\Import\Product\UrlRewrite\Utils;

/**
 * Utility class with the SQL statements to use.
 *
 * @author    Tim Wagner <t.wagner@techdivision.com>
 * @copyright 2016 TechDivision GmbH <info@techdivision.com>
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      https://github.com/techdivision/import-product-url-rewrite
 * @link      http://www.techdivision.com
 */
class SqlStatementKeys extends \TechDivision\Import\Product\Utils\SqlStatementKeys
{

    /**
     * The SQL statement to create new URL rewrite product category relations.
     *
     * @var string
     */
    const CREATE_URL_REWRITE_PRODUCT_CATEGORY = 'create.url_rewrite_product_category';

    /**
     * The SQL statement to update an existing URL rewrite product category relation.
     *
     * @var string
     */
    const UPDATE_URL_REWRITE_PRODUCT_CATEGORY = 'update.url_rewrite_product_category';

    /**
     * The SQL statement to remove a existing URL rewrite product category relation.
     *
     * @var string
     */
    const DELETE_URL_REWRITE_PRODUCT_CATEGORY = 'delete.url_rewrite_product_category';

    /**
     * The SQL statement to load the URL rewrite product category relation with the passed ID.
     *
     * @var string
     */
    const URL_REWRITE_PRODUCT_CATEGORY = 'ur_rewrite_product_category';

    /**
     * The SQL statement to load the URL rewrites by a SKU.
     *
     * @var string
     */
    const URL_REWRITES_BY_SKU = 'ur_rewrites.by.sku';

    /**
     * The SQL statement to load the URL rewrite product category relations for the passed SKU.
     *
     * @var string
     */
    const URL_REWRITE_PRODUCT_CATEGORIES_BY_SKU = 'ur_rewrite_product_categories.by.sku';
}
