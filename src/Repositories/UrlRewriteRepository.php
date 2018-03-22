<?php

/**
 * TechDivision\Import\Product\UrlRewrite\Repositories\UrlRewriteRepository
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

use TechDivision\Import\Product\UrlRewrite\Utils\MemberNames;
use TechDivision\Import\Product\UrlRewrite\Utils\SqlStatementKeys;

/**
 * Repository implementation to load URL rewrite data.
 *
 * @author    Tim Wagner <t.wagner@techdivision.com>
 * @copyright 2016 TechDivision GmbH <info@techdivision.com>
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      https://github.com/techdivision/import-product-url-rewrite
 * @link      http://www.techdivision.com
 */
class UrlRewriteRepository extends \TechDivision\Import\Repositories\UrlRewriteRepository implements UrlRewriteRepositoryInterface
{

    /**
     * The prepared statement to load the existing URL rewrites by a SKU.
     *
     * @var \PDOStatement
     */
    protected $urlRewritesBySkuStmt;

    /**
     * Initializes the repository's prepared statements.
     *
     * @return void
     */
    public function init()
    {

        // invoke the parent instance
        parent::init();

        // initialize the prepared statements
        $this->urlRewritesBySkuStmt = $this->getConnection()->prepare($this->loadStatement(SqlStatementKeys::URL_REWRITES_BY_SKU));
    }

    /**
     * Return's an array with the URL rewrites for the passed SKU.
     *
     * @param string $sku The SKU to load the URL rewrites for
     *
     * @return array The URL rewrites
     */
    public function findAllBySku($sku)
    {

        // initialize the params
        $params = array(MemberNames::SKU => $sku);

        // load and return the URL rewrites for the passed SKU
        $this->urlRewritesBySkuStmt->execute($params);
        return $this->urlRewritesBySkuStmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}
