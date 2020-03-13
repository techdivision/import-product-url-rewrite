<?php

/**
 * TechDivision\Import\Product\UrlRewrite\Repositories\SqlStatementRepository
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

namespace TechDivision\Import\Product\UrlRewrite\Repositories;

use TechDivision\Import\Product\UrlRewrite\Utils\SqlStatementKeys;

/**
 * Repository class with the SQL statements to use.
 *
 * @author    Tim Wagner <t.wagner@techdivision.com>
 * @copyright 2016 TechDivision GmbH <info@techdivision.com>
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      https://github.com/techdivision/import-product-url-rewrite
 * @link      http://www.techdivision.com
 */
class SqlStatementRepository extends \TechDivision\Import\Product\Repositories\SqlStatementRepository
{

    /**
     * The SQL statements.
     *
     * @var array
     */
    private $statements = array(
        SqlStatementKeys::CREATE_URL_REWRITE_PRODUCT_CATEGORY =>
            'INSERT
               INTO catalog_url_rewrite_product_category
                    (url_rewrite_id,
                     category_id,
                     product_id)
             VALUES (:url_rewrite_id,
                     :category_id,
                     :product_id)',
        SqlStatementKeys::UPDATE_URL_REWRITE_PRODUCT_CATEGORY =>
            'UPDATE catalog_url_rewrite_product_category
                SET category_id = :category_id,
                    product_id = :product_id
              WHERE url_rewrite_id = :url_rewrite_id',
        SqlStatementKeys::DELETE_URL_REWRITE_PRODUCT_CATEGORY =>
            'DELETE
               FROM catalog_url_rewrite_product_category
              WHERE url_rewrite_id = :url_rewrite_id',
        SqlStatementKeys::URL_REWRITE_PRODUCT_CATEGORY =>
            'SELECT *
               FROM catalog_url_rewrite_product_category
              WHERE url_rewrite_id = :url_rewrite_id',
        SqlStatementKeys::URL_REWRITES_BY_SKU =>
            'SELECT t2.*
               FROM catalog_product_entity t1,
                    url_rewrite t2
              WHERE t1.sku = :sku
                AND t2.entity_id = t1.entity_id
                AND t2.entity_type = \'product\'',
        SqlStatementKeys::URL_REWRITE_PRODUCT_CATEGORIES_BY_SKU =>
            'SELECT t3.*
               FROM catalog_product_entity t1,
                    url_rewrite t2,
                    catalog_url_rewrite_product_category t3
              WHERE t1.sku = :sku
                AND t2.entity_id = t1.entity_id
                AND t2.entity_type = \'product\'
                AND t3.url_rewrite_id = t2.url_rewrite_id'
    );

    /**
     * Initialize the the SQL statements.
     */
    public function __construct()
    {

        // call the parent constructor
        parent::__construct();

        // merge the class statements
        foreach ($this->statements as $key => $statement) {
            $this->preparedStatements[$key] = $statement;
        }
    }
}
