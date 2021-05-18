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

use TechDivision\Import\Actions\ActionInterface;
use TechDivision\Import\Connection\ConnectionInterface;
use TechDivision\Import\Product\Repositories\ProductRepositoryInterface;
use TechDivision\Import\Product\Repositories\ProductVarcharRepositoryInterface;
use TechDivision\Import\Product\UrlRewrite\Repositories\UrlRewriteRepositoryInterface;
use TechDivision\Import\Product\UrlRewrite\Repositories\UrlRewriteProductCategoryRepositoryInterface;
use TechDivision\Import\Utils\PrimaryKeyUtilInterface;

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
     * The primary key util instance.
     *
     * @var \TechDivision\Import\Utils\PrimaryKeyUtilInterface
     */
    protected $primaryKeyUtil;

    /**
     * The action for URL rewrite CRUD methods.
     *
     * @var \TechDivision\Import\Actions\ActionInterface
     */
    protected $urlRewriteAction;

    /**
     * The action for URL rewrite product category CRUD methods.
     *
     * @var \TechDivision\Import\Actions\ActionInterface
     */
    protected $urlRewriteProductCategoryAction;

    /**
     * The repository to load the products with.
     *
     * @var \TechDivision\Import\Product\Repositories\ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * The repository to load the URL rewrites with.
     *
     * @var \TechDivision\Import\Product\UrlRewrite\Repositories\UrlRewriteRepositoryInterface
     */
    protected $urlRewriteRepository;

    /**
     * The repository to load the URL rewrite product category relations with.
     *
     * @var \TechDivision\Import\Product\UrlRewrite\Repositories\UrlRewriteProductCategoryRepositoryInterface
     */
    protected $urlRewriteProductCategoryRepository;

    /**
     * Initialize the processor with the necessary assembler and repository instances.
     *
     * @param \TechDivision\Import\Connection\ConnectionInterface                                               $connection                          The connection to use
     * @param \TechDivision\Import\Product\Repositories\ProductRepositoryInterface                              $productRepository                   The product repository to use
     * @param \TechDivision\Import\Product\Repositories\ProductVarcharRepositoryInterface                       $productVarcharRepository            The product varchar repository to use
     * @param \TechDivision\Import\Product\UrlRewrite\Repositories\UrlRewriteRepositoryInterface                $urlRewriteRepository                The URL rewrite repository to use
     * @param \TechDivision\Import\Product\UrlRewrite\Repositories\UrlRewriteProductCategoryRepositoryInterface $urlRewriteProductCategoryRepository The URL rewrite product category repository to use
     * @param \TechDivision\Import\Actions\ActionInterface                                                      $urlRewriteAction                    The URL rewrite action to use
     * @param \TechDivision\Import\Actions\ActionInterface                                                      $urlRewriteProductCategoryAction     The URL rewrite product category action to use
     * @param \TechDivision\Import\Utils\PrimaryKeyUtilInterface                                                $primaryKeyUtil                      The primary key util
     */
    public function __construct(
        ConnectionInterface $connection,
        ProductRepositoryInterface $productRepository,
        ProductVarcharRepositoryInterface $productVarcharRepository,
        UrlRewriteRepositoryInterface $urlRewriteRepository,
        UrlRewriteProductCategoryRepositoryInterface $urlRewriteProductCategoryRepository,
        ActionInterface $urlRewriteAction,
        ActionInterface $urlRewriteProductCategoryAction,
        PrimaryKeyUtilInterface $primaryKeyUtil
    ) {
        $this->setConnection($connection);
        $this->setPrimaryKeyUtil($primaryKeyUtil);
        $this->setProductRepository($productRepository);
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
     * @param \TechDivision\Import\Actions\ActionInterface $urlRewriteAction The action with the URL rewrite CRUD methods
     *
     * @return void
     */
    public function setUrlRewriteAction(ActionInterface $urlRewriteAction)
    {
        $this->urlRewriteAction = $urlRewriteAction;
    }

    /**
     * Return's the action with the URL rewrite CRUD methods.
     *
     * @return \TechDivision\Import\Actions\ActionInterface The action instance
     */
    public function getUrlRewriteAction()
    {
        return $this->urlRewriteAction;
    }

    /**
     * Set's the action with the URL rewrite product category CRUD methods.
     *
     * @param \TechDivision\Import\Actions\ActionInterface $urlRewriteProductCategoryAction The action with the URL rewrite CRUD methods
     *
     * @return void
     */
    public function setUrlRewriteProductCategoryAction(ActionInterface $urlRewriteProductCategoryAction)
    {
        $this->urlRewriteProductCategoryAction = $urlRewriteProductCategoryAction;
    }

    /**
     * Return's the action with the URL rewrite product category CRUD methods.
     *
     * @return \TechDivision\Import\Actions\ActionInterface The action instance
     */
    public function getUrlRewriteProductCategoryAction()
    {
        return $this->urlRewriteProductCategoryAction;
    }

    /**
     * Set's the repository to load the product varchar attribute with.
     *
     * @param \TechDivision\Import\Product\Repositories\ProductVarcharRepositoryInterface $productVarcharRepository The repository instance
     *
     * @return void
     */
    public function setProductVarcharRepository(ProductVarcharRepositoryInterface $productVarcharRepository)
    {
        $this->productVarcharRepository = $productVarcharRepository;
    }

    /**
     * Return's the repository to load the product varchar attribute with.
     *
     * @return \TechDivision\Import\Product\Repositories\ProductVarcharRepositoryInterface The repository instance
     */
    public function getProductVarcharRepository()
    {
        return $this->productVarcharRepository;
    }

    /**
     * Set's the repository to load the URL rewrites with.
     *
     * @param \TechDivision\Import\Product\UrlRewrite\Repositories\UrlRewriteRepositoryInterface $urlRewriteRepository The repository instance
     *
     * @return void
     */
    public function setUrlRewriteRepository(UrlRewriteRepositoryInterface $urlRewriteRepository)
    {
        $this->urlRewriteRepository = $urlRewriteRepository;
    }

    /**
     * Return's the repository to load the URL rewrites with.
     *
     * @return \TechDivision\Import\Product\UrlRewrite\Repositories\UrlRewriteRepositoryInterface The repository instance
     */
    public function getUrlRewriteRepository()
    {
        return $this->urlRewriteRepository;
    }

    /**
     * Set's the repository to load the URL rewrite product category relations with.
     *
     * @param \TechDivision\Import\Product\UrlRewrite\Repositories\UrlRewriteProductCategoryRepositoryInterface $urlRewriteProductCategoryRepository The repository instance
     *
     * @return void
     */
    public function setUrlRewriteProductCategoryRepository(UrlRewriteProductCategoryRepositoryInterface $urlRewriteProductCategoryRepository)
    {
        $this->urlRewriteProductCategoryRepository = $urlRewriteProductCategoryRepository;
    }

    /**
     * Return's the repository to load the URL rewrite product category relations with.
     *
     * @return \TechDivision\Import\Product\UrlRewrite\Repositories\UrlRewriteProductCategoryRepositoryInterface The repository instance
     */
    public function getUrlRewriteProductCategoryRepository()
    {
        return $this->urlRewriteProductCategoryRepository;
    }

    /**
     * Set's the repository to load the products with.
     *
     * @param \TechDivision\Import\Product\Repositories\ProductRepositoryInterface $productRepository The repository instance
     *
     * @return void
     */
    public function setProductRepository(ProductRepositoryInterface $productRepository)
    {
        $this->productRepository = $productRepository;
    }

    /**
     * Return's the repository to load the products with.
     *
     * @return \TechDivision\Import\Product\Repositories\ProductRepositoryInterface The repository instance
     */
    public function getProductRepository()
    {
        return $this->productRepository;
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
     * Load's and return's the product with the passed SKU.
     *
     * @param string $sku The SKU of the product to load
     *
     * @return array The product
     */
    public function loadProduct($sku)
    {
        return $this->getProductRepository()->findOneBySku($sku);
    }

    /**
     * Load's and return's the varchar attribute with the passed params.
     *
     * @param integer $attributeCode The attribute code of the varchar attribute
     * @param integer $entityTypeId  The entity type ID of the varchar attribute
     * @param integer $storeId       The store ID of the varchar attribute
     * @param string  $pk            The primary key of the product
     *
     * @return array|null The varchar attribute
     */
    public function loadProductVarcharAttributeByAttributeCodeAndEntityTypeIdAndStoreIdAndPK($attributeCode, $entityTypeId, $storeId, $pk)
    {
        return $this->getProductVarcharRepository()->findOneByAttributeCodeAndEntityTypeIdAndStoreIdAndPk($attributeCode, $entityTypeId, $storeId, $pk);
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

    /**
     * Sets the passed primary key util instance.
     *
     * @param \TechDivision\Import\Utils\PrimaryKeyUtilInterface $primaryKeyUtil The primary key util instance
     *
     * @return void
     */
    public function setPrimaryKeyUtil(PrimaryKeyUtilInterface $primaryKeyUtil)
    {
        $this->primaryKeyUtil = $primaryKeyUtil;
    }

    /**
     * Returns the primary key util instance.
     *
     * @return \TechDivision\Import\Utils\PrimaryKeyUtilInterface The primary key util instance
     */
    public function getPrimaryKeyUtil()
    {
        return $this->primaryKeyUtil;
    }

    /**
     * Returns the primary key member name for the actual Magento edition.
     *
     * @return string The primary key member name
     * @see \TechDivision\Import\Utils\PrimaryKeyUtilInterface::getPrimaryKeyMemberName()
     */
    public function getPrimaryKeyMemberName()
    {
        return $this->getPrimaryKeyUtil()->getPrimaryKeyMemberName();
    }
}
