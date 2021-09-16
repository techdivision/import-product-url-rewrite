<?php

/**
 * TechDivision\Import\Product\UrlRewrite\Repositories\UrlRewriteRepository
 *
 * PHP version 7
 *
 * @author    Tim Wagner <t.wagner@techdivision.com>
 * @copyright 2016 TechDivision GmbH <info@techdivision.com>
 * @license   https://opensource.org/licenses/MIT
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
 * @license   https://opensource.org/licenses/MIT
 * @link      https://github.com/techdivision/import-product-url-rewrite
 * @link      http://www.techdivision.com
 */
class UrlRewriteRepository extends \TechDivision\Import\Repositories\UrlRewriteRepository implements UrlRewriteRepositoryInterface
{
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
        $this->addFinder($this->finderFactory->createFinder($this, SqlStatementKeys::URL_REWRITES_BY_SKU));
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
        foreach ($this->getFinder(SqlStatementKeys::URL_REWRITES_BY_SKU)->find($params) as $result) {
            yield $result;
        }
    }
}
