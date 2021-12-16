<?php

/**
 * TechDivision\Import\Product\UrlRewrite\Repositories\UrlRewriteProductCategoryRepository
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

use TechDivision\Import\Dbal\Collection\Repositories\AbstractFinderRepository;
use TechDivision\Import\Product\UrlRewrite\Utils\CacheKeys;
use TechDivision\Import\Product\UrlRewrite\Utils\MemberNames;
use TechDivision\Import\Product\UrlRewrite\Utils\SqlStatementKeys;
use TechDivision\Import\Dbal\Repositories\FinderAwareEntityRepositoryInterface;

/**
 * Repository implementation to load URL rewrite product category relation data.
 *
 * @author    Tim Wagner <t.wagner@techdivision.com>
 * @copyright 2016 TechDivision GmbH <info@techdivision.com>
 * @license   https://opensource.org/licenses/MIT
 * @link      https://github.com/techdivision/import-product-url-rewrite
 * @link      http://www.techdivision.com
 */
class UrlRewriteProductCategoryRepository extends AbstractFinderRepository implements UrlRewriteProductCategoryRepositoryInterface, FinderAwareEntityRepositoryInterface
{

    /**
     * Initializes the repository's prepared statements.
     *
     * @return void
     */
    public function init()
    {

        // initialize the prepared statements
        $this->addFinder($this->finderFactory->createFinder($this, SqlStatementKeys::URL_REWRITE_PRODUCT_CATEGORY));
        $this->addFinder($this->finderFactory->createFinder($this, SqlStatementKeys::URL_REWRITE_PRODUCT_CATEGORIES_BY_SKU));
        $this->addFinder($this->finderFactory->createFinder($this, SqlStatementKeys::URL_REWRITE_PRODUCT_CATEGORIES));
    }

    /**
     * Return's the URL rewrite product category relation for the passed
     * URL rewrite ID.
     *
     * @param integer $urlRewriteId The URL rewrite ID to load the URL rewrite product category relation for
     *
     * @return array|false The URL rewrite product category relation
     */
    public function load($urlRewriteId)
    {

        return $this->getFinder(SqlStatementKeys::URL_REWRITE_PRODUCT_CATEGORY)
            ->find(array(MemberNames::URL_REWRITE_ID => $urlRewriteId));
    }

    /**
     * Return's an array with the URL rewrite product category relations for the passed SKU.
     *
     * @param string $sku The SKU to load the URL rewrite product category relations for
     *
     * @return array The URL rewrite product category relations
     * @deprecated since 24.0.0
     */
    public function findAllBySku($sku)
    {

        foreach ($this->getFinder(SqlStatementKeys::URL_REWRITE_PRODUCT_CATEGORIES_BY_SKU)->find(array(MemberNames::SKU => $sku)) as $result) {
            yield $result;
        }
    }

    /**
     * @return array|null The product categorie relation data
     */
    public function findAll()
    {
        foreach ($this->getFinder(SqlStatementKeys::URL_REWRITE_PRODUCT_CATEGORIES)->find() as $result) {
            yield $result;
        }
    }

    /**
     * Return's the primary key name of the entity.
     *
     * @return string The name of the entity's primary key
     */
    public function getPrimaryKeyName()
    {
        return MemberNames::URL_REWRITE_ID;
    }

    /**
     * Return's the finder's entity name.
     *
     * @return string The finder's entity name
     */
    public function getEntityName()
    {
        return CacheKeys::URL_REWRITE_PRODUCT_CATEGORY;
    }

    /**
     * Return's the entity unique key name.
     *
     * @return string The name of the entity's unique key
     */
    public function getUniqueKeyName()
    {
        return $this->getPrimaryKeyName();
    }
}
