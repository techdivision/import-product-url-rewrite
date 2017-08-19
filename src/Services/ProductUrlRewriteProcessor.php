<?php

/**
 * TechDivision\Import\Product\UrlRewrite\Services\ProductUrlRewriteProcessor
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

use TechDivision\Import\Actions\UrlRewriteAction;
use TechDivision\Import\Connection\ConnectionInterface;
use TechDivision\Import\Product\Repositories\ProductVarcharRepository;
use TechDivision\Import\Product\UrlRewrite\Actions\UrlRewriteProductCategoryAction;
use TechDivision\Import\Product\UrlRewrite\Repositories\UrlRewriteRepository;
use TechDivision\Import\Product\UrlRewrite\Repositories\UrlRewriteProductCategoryRepository;

/**
 * The product URL rewrite processor implementation.
 *
 * @author    Tim Wagner <t.wagner@techdivision.com>
 * @copyright 2016 TechDivision GmbH <info@techdivision.com>
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      https://github.com/techdivision/import-product-url-rewrite
 * @link      http://www.techdivision.com
 */
class ProductUrlRewriteProcessor implements ProductUrlRewriteProcessorInterface
{

    /**
     * A PDO connection initialized with the values from the Doctrine EntityManager.
     *
     * @var \TechDivision\Import\Connection\ConnectionInterface
     */
    protected $connection;

    /**
     * The action for URL rewrite CRUD methods.
     *
     * @var \TechDivision\Import\Actions\UrlRewriteAction
     */
    protected $urlRewriteAction;

    /**
     * The action for URL rewrite product category CRUD methods.
     *
     * @var \TechDivision\Import\Product\Actions\UrlRewriteProductCategoryAction
     */
    protected $urlRewriteProductCategoryAction;

    /**
     * The repository to load the URL rewrites with.
     *
     * @var \TechDivision\Import\Product\Repositories\UrlRewriteRepository
     */
    protected $urlRewriteRepository;

    /**
     * The repository to load the URL rewrite product category relations with.
     *
     * @var \TechDivision\Import\Product\Repositories\UrlRewriteProductCategoryRepository
     */
    protected $urlRewriteProductCategoryRepository;

    /**
     * Initialize the processor with the necessary assembler and repository instances.
     *
     * @param \TechDivision\Import\Connection\ConnectionInterface                           $connection                          The connection to use
     * @param \TechDivision\Import\Product\Repositories\ProductVarcharRepository            $productVarcharRepository            The product varchar repository to use
     * @param \TechDivision\Import\Product\Repositories\UrlRewriteRepository                $urlRewriteRepository                The URL rewrite repository to use
     * @param \TechDivision\Import\Product\Repositories\UrlRewriteProductCategoryRepository $urlRewriteProductCategoryRepository The URL rewrite product category repository to use
     * @param \TechDivision\Import\Actions\UrlRewriteAction                                 $urlRewriteAction                    The URL rewrite action to use
     * @param \TechDivision\Import\Product\Actions\UrlRewriteProductCategoryAction          $urlRewriteProductCategoryAction     The URL rewrite product category action to use
     */
    public function __construct(
        ConnectionInterface $connection,
        ProductVarcharRepository $productVarcharRepository,
        UrlRewriteRepository $urlRewriteRepository,
        UrlRewriteProductCategoryRepository $urlRewriteProductCategoryRepository,
        UrlRewriteAction $urlRewriteAction,
        UrlRewriteProductCategoryAction $urlRewriteProductCategoryAction
    ) {
        $this->setConnection($connection);
        $this->setProductVarcharRepository($productVarcharRepository);
        $this->setUrlRewriteRepository($urlRewriteRepository);
        $this->setUrlRewriteProductCategoryRepository($urlRewriteProductCategoryRepository);
        $this->setUrlRewriteAction($urlRewriteAction);
        $this->setUrlRewriteProductCategoryAction($urlRewriteProductCategoryAction);
    }

    /**
     * Set's the passed connection.
     *
     * @param \TechDivision\Import\Connection\ConnectionInterface $connection The connection to set
     *
     * @return void
     */
    public function setConnection(ConnectionInterface $connection)
    {
        $this->connection = $connection;
    }

    /**
     * Return's the connection.
     *
     * @return \TechDivision\Import\Connection\ConnectionInterface The connection instance
     */
    public function getConnection()
    {
        return $this->connection;
    }

    /**
     * Turns off autocommit mode. While autocommit mode is turned off, changes made to the database via the PDO
     * object instance are not committed until you end the transaction by calling ProductProcessor::commit().
     * Calling ProductProcessor::rollBack() will roll back all changes to the database and return the connection
     * to autocommit mode.
     *
     * @return boolean Returns TRUE on success or FALSE on failure
     * @link http://php.net/manual/en/pdo.begintransaction.php
     */
    public function beginTransaction()
    {
        return $this->connection->beginTransaction();
    }

    /**
     * Commits a transaction, returning the database connection to autocommit mode until the next call to
     * ProductProcessor::beginTransaction() starts a new transaction.
     *
     * @return boolean Returns TRUE on success or FALSE on failure
     * @link http://php.net/manual/en/pdo.commit.php
     */
    public function commit()
    {
        return $this->connection->commit();
    }

    /**
     * Rolls back the current transaction, as initiated by ProductProcessor::beginTransaction().
     *
     * If the database was set to autocommit mode, this function will restore autocommit mode after it has
     * rolled back the transaction.
     *
     * Some databases, including MySQL, automatically issue an implicit COMMIT when a database definition
     * language (DDL) statement such as DROP TABLE or CREATE TABLE is issued within a transaction. The implicit
     * COMMIT will prevent you from rolling back any other changes within the transaction boundary.
     *
     * @return boolean Returns TRUE on success or FALSE on failure
     * @link http://php.net/manual/en/pdo.rollback.php
     */
    public function rollBack()
    {
        return $this->connection->rollBack();
    }

    /**
     * Set's the action with the URL rewrite CRUD methods.
     *
     * @param \TechDivision\Import\Actions\UrlRewriteAction $urlRewriteAction The action with the URL rewrite CRUD methods
     *
     * @return void
     */
    public function setUrlRewriteAction($urlRewriteAction)
    {
        $this->urlRewriteAction = $urlRewriteAction;
    }

    /**
     * Return's the action with the URL rewrite CRUD methods.
     *
     * @return \TechDivision\Import\Actions\UrlRewriteAction The action instance
     */
    public function getUrlRewriteAction()
    {
        return $this->urlRewriteAction;
    }

    /**
     * Set's the action with the URL rewrite product category CRUD methods.
     *
     * @param \TechDivision\Import\Product\Actions\UrlRewriteProductCategoryAction $urlRewriteProductCategoryAction The action with the URL rewrite CRUD methods
     *
     * @return void
     */
    public function setUrlRewriteProductCategoryAction($urlRewriteProductCategoryAction)
    {
        $this->urlRewriteProductCategoryAction = $urlRewriteProductCategoryAction;
    }

    /**
     * Return's the action with the URL rewrite product category CRUD methods.
     *
     * @return \TechDivision\Import\Product\Actions\UrlRewriteProductCategoryAction The action instance
     */
    public function getUrlRewriteProductCategoryAction()
    {
        return $this->urlRewriteProductCategoryAction;
    }

    /**
     * Set's the repository to load the product varchar attribute with.
     *
     * @param \TechDivision\Import\Product\Repositories\ProductVarcharRepository $productVarcharRepository The repository instance
     *
     * @return void
     */
    public function setProductVarcharRepository($productVarcharRepository)
    {
        $this->productVarcharRepository = $productVarcharRepository;
    }

    /**
     * Return's the repository to load the product varchar attribute with.
     *
     * @return \TechDivision\Import\Product\Repositories\ProductVarcharRepository The repository instance
     */
    public function getProductVarcharRepository()
    {
        return $this->productVarcharRepository;
    }

    /**
     * Set's the repository to load the URL rewrites with.
     *
     * @param \TechDivision\Import\Product\Repositories\UrlRewriteRepository $urlRewriteRepository The repository instance
     *
     * @return void
     */
    public function setUrlRewriteRepository($urlRewriteRepository)
    {
        $this->urlRewriteRepository = $urlRewriteRepository;
    }

    /**
     * Return's the repository to load the URL rewrites with.
     *
     * @return \TechDivision\Import\Product\Repositories\UrlRewriteRepository The repository instance
     */
    public function getUrlRewriteRepository()
    {
        return $this->urlRewriteRepository;
    }

    /**
     * Set's the repository to load the URL rewrite product category relations with.
     *
     * @param \TechDivision\Import\Product\Repositories\UrlRewriteProductCategoryRepository $urlRewriteProductCategoryRepository The repository instance
     *
     * @return void
     */
    public function setUrlRewriteProductCategoryRepository($urlRewriteProductCategoryRepository)
    {
        $this->urlRewriteProductCategoryRepository = $urlRewriteProductCategoryRepository;
    }

    /**
     * Return's the repository to load the URL rewrite product category relations with.
     *
     * @return \TechDivision\Import\Product\Repositories\UrlRewriteProductCategoryRepository The repository instance
     */
    public function getUrlRewriteProductCategoryRepository()
    {
        return $this->urlRewriteProductCategoryRepository;
    }

    /**
     * Return's the URL rewrites for the passed URL entity type and ID.
     *
     * @param string  $entityType The entity type to load the URL rewrites for
     * @param integer $entityId   The entity ID to laod the rewrites for
     *
     * @return array The URL rewrites
     */
    public function getUrlRewritesByEntityTypeAndEntityId($entityType, $entityId)
    {
        return $this->getUrlRewriteRepository()->findAllByEntityTypeAndEntityId($entityType, $entityId);
    }

    /**
     * Return's the URL rewrites for the passed URL entity type and ID.
     *
     * @param string  $entityType The entity type to load the URL rewrites for
     * @param integer $entityId   The entity ID to load the URL rewrites for
     * @param integer $storeId    The store ID to load the URL rewrites for
     *
     * @return array The URL rewrites
     */
    public function getUrlRewritesByEntityTypeAndEntityIdAndStoreId($entityType, $entityId, $storeId)
    {
        return $this->getUrlRewriteRepository()->findAllByEntityTypeAndEntityIdAndStoreId($entityType, $entityId, $storeId);
    }

    /**
     * Return's an array with the URL rewrites for the passed SKU.
     *
     * @param string $sku The SKU to load the URL rewrites for
     *
     * @return array The URL rewrites
     */
    public function getUrlRewritesBySku($sku)
    {
        return $this->getUrlRewriteRepository()->findAllBySku($sku);
    }

    /**
     * Return's an array with the URL rewrite product category relations for the passed SKU.
     *
     * @param string $sku The SKU to load the URL rewrite product category relations for
     *
     * @return array The URL rewrite product category relations
     */
    public function getUrlRewriteProductCategoriesBySku($sku)
    {
        return $this->getUrlRewriteProductCategoryRepository()->findAllBySku($sku);
    }

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
    public function loadProductVarcharAttributeByAttributeCodeAndEntityTypeIdAndStoreIdAndValue($attributeCode, $entityTypeId, $storeId, $value)
    {
        return $this->getProductVarcharRepository()->findOneByAttributeCodeAndEntityTypeIdAndStoreIdAndValue($attributeCode, $entityTypeId, $storeId, $value);
    }

    /**
     * Return's the URL rewrite product category relation for the passed
     * URL rewrite ID.
     *
     * @param integer $urlRewriteId The URL rewrite ID to load the URL rewrite product category relation for
     *
     * @return array|false The URL rewrite product category relation
     */
    public function loadUrlRewriteProductCategory($urlRewriteId)
    {
        return $this->getUrlRewriteProductCategoryRepository()->load($urlRewriteId);
    }

    /**
     * Persist's the URL write with the passed data.
     *
     * @param array       $row  The URL rewrite to persist
     * @param string|null $name The name of the prepared statement that has to be executed
     *
     * @return string The ID of the persisted entity
     */
    public function persistUrlRewrite($row, $name = null)
    {
        return $this->getUrlRewriteAction()->persist($row, $name);
    }

    /**
     * Persist's the URL rewrite product => category relation with the passed data.
     *
     * @param array       $row  The URL rewrite product => category relation to persist
     * @param string|null $name The name of the prepared statement that has to be executed
     *
     * @return void
     */
    public function persistUrlRewriteProductCategory($row, $name = null)
    {
        $this->getUrlRewriteProductCategoryAction()->persist($row, $name);
    }

    /**
     * Delete's the URL rewrite with the passed attributes.
     *
     * @param array       $row  The attributes of the entity to delete
     * @param string|null $name The name of the prepared statement that has to be executed
     *
     * @return void
     */
    public function deleteUrlRewrite($row, $name = null)
    {
        $this->getUrlRewriteAction()->delete($row, $name);
    }
}
