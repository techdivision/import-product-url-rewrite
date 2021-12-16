<?php

/**
 * TechDivision\Import\Product\UrlRewrite\Observers\UrlRewriteObserverTest
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

use PHPUnit\Framework\TestCase;
use ReflectionClass;
use TechDivision\Import\Dbal\Utils\EntityStatus;
use TechDivision\Import\Utils\StoreViewCodes;
use TechDivision\Import\Product\Utils\VisibilityKeys;
use TechDivision\Import\Product\UrlRewrite\Utils\ColumnKeys;
use TechDivision\Import\Product\UrlRewrite\Utils\MemberNames;
use TechDivision\Import\Adapter\SerializerAwareAdapterInterface;
use TechDivision\Import\Product\UrlRewrite\Utils\CoreConfigDataKeys;
use TechDivision\Import\Product\UrlRewrite\Subjects\UrlRewriteSubject;

/**
 * Test class for the product URL rewrite observer implementation.
 *
 * @author    Tim Wagner <t.wagner@techdivision.com>
 * @copyright 2016 TechDivision GmbH <info@techdivision.com>
 * @license   https://opensource.org/licenses/MIT
 * @link      https://github.com/techdivision/import-product-url-rewrite
 * @link      http://www.techdivision.com
 */
class UrlRewriteObserverTest extends TestCase
{

    /**
     * The observer we want to test.
     *
     * @var \TechDivision\Import\Product\UrlRewrite\Observers\UrlRewriteObserver
     */
    protected $observer;

    /**
     * A mock processor instance.
     *
     * @var \TechDivision\Import\Product\UrlRewrite\Services\ProductUrlRewriteProcessorInterface
     */
    protected $mockProductUrlRewriteProcessor;

    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     *
     * @return void
     * @see \PHPUnit\Framework\TestCase::setUp()
     */
    protected function setUp(): void
    {

        // mock the subject instance used to initialize the
        $mockSubject = $this->getMockBuilder(UrlRewriteSubject::class)->setMethods(get_class_methods(UrlRewriteSubject::class))->disableOriginalConstructor()->getMock();
        $mockSubject->expects($this->any())->method('getRootCategories')->willReturn(array(array(MemberNames::ENTITY_ID => 2)));

        // initialize a mock processor instance
        $this->mockProductUrlRewriteProcessor = $this->getMockBuilder('TechDivision\Import\Product\UrlRewrite\Services\ProductUrlRewriteProcessorInterface')
                                                     ->setMethods(get_class_methods('TechDivision\Import\Product\UrlRewrite\Services\ProductUrlRewriteProcessorInterface'))
                                                     ->getMock();

        // initialize the observer
        $this->observer = new UrlRewriteObserver($this->mockProductUrlRewriteProcessor);
        $this->observer = $this->observer->createObserver($mockSubject);
    }

    /**
     * Create's and invokes the UrlRewriteObserver instance.
     *
     * @param boolean $generateCategoryProductRewrites Whether or not the configuration to generare catagory/product URL rewrites has been activated
     * @param array   $productCategoryIds              The array with the category IDs the product has been related with
     * @param array   $category                        The array with the categories
     *
     * @return void
     */
    private function createAndInvokeObserver($generateCategoryProductRewrites = true, $productCategoryIds = [], $category = [])
    {

        // create a mock subject
        $mockSubject = $this->getMockBuilder(UrlRewriteSubject::class)
            ->setMethods(array('getCoreConfigData'))
            ->disableOriginalConstructor()
            ->getMock();

        // mock the method to load the Magento configuration data with
        $mockSubject->expects($this->exactly(1))
            ->method('getCoreConfigData')
            ->with(CoreConfigDataKeys::CATALOG_SEO_GENERATE_CATEGORY_PRODUCT_REWRITES)
            ->willReturn($generateCategoryProductRewrites);

        // mock the observer
        $observer = $this->getMockBuilder(UrlRewriteObserver::class)
            ->setConstructorArgs([$this->mockProductUrlRewriteProcessor])
            ->setMethods(['isRootCategory', 'getSubject'])
            ->getMock();

        // mock the subject
        $observer->expects($this->any())
            ->method('getSubject')
            ->willReturn($mockSubject);

        // mock the root category check
        $observer->expects($this->any())
            ->method('isRootCategory')
            ->willReturn(false);

        // prepare protected properties of observer
        $reflection = new ReflectionClass(UrlRewriteObserver::class);
        $property = $reflection->getProperty('productCategoryIds');
        $property->setAccessible(true);
        $property->setValue($observer, $productCategoryIds);

        // invoke the method to test
        $method = $reflection->getMethod('createProductCategoryRelation');
        $method->setAccessible(true);
        $method->invoke($observer, $category, true);

        // return category IDs the URL rewrites has been created for
        return $property->getValue($observer);
    }

    /**
     * Invoke a test to make sure that all categories that
     * have been related to a product have been processed.
     *
     * @return void
     */
    public function testCreateProductCategoryRelationWithChildCategoryAndSettingEnabled()
    {

        // initialize method arguments
        $generateCategoryProductRewrites = true;
        $productCategoryIds = ['2'];
        $category = [
            'entity_id' => '10',
            'is_anchor' => '0'
        ];

        // create and invoke the partially mocked observer
        $productCategoryIds = $this->createAndInvokeObserver($generateCategoryProductRewrites, $productCategoryIds, $category);

        // assert that all categories have been processed
        $this->assertSame($productCategoryIds, ['2', '10']);
    }

    /**
     * Invoke a test to make sure that only the root category has been
     * processed, when the flag in the configuration has been activated.
     *
     * @return void
     */
    public function testCreateProductCategoryRelationWithChildCategoryAndSettingDisabled()
    {

        // initialize method arguments
        $generateCategoryProductRewrites = false;
        $productCategoryIds = ['2'];
        $category = [
            'entity_id' => '10',
            'is_anchor' => '0'
        ];

        // create and invoke the partially mocked observer
        $productCategoryIds = $this->createAndInvokeObserver($generateCategoryProductRewrites, $productCategoryIds, $category);

        // assert that only the root category ID has been processed
        $this->assertSame($productCategoryIds, ['2']);
    }

    /**
     * Test's the handle() method with a successful URL rewrite persist.
     *
     * @return void
     */
    public function testHandleWithSuccessfullCreateWithoutCategories()
    {

        // create a dummy CSV file header
        $headers = array(
            'sku'             => 0,
            'url_key'         => 1,
            'store_view_code' => 2,
            'visibility'      => 3,
            'categories'      => 4
        );

        // create a dummy CSV file row
        $row = array(
            0 => $sku = 'TEST-01',
            1 => 'bruno-compete-hoodie-test',
            2 => $storeViewCode = 'default',
            3 => 'Catalog, Search',
            4 => null
        );

        // initialize category and entity ID
        $categoryId = 2;
        $entityId = 61413;

        // mock the system logger
        $mockSystemLogger = $this->getMockBuilder('Psr\Log\LoggerInterface')
            ->setMethods(get_class_methods('Psr\Log\LoggerInterface'))
            ->getMock();

        // create a mock subject
        $mockSubject = $this->getMockBuilder('TechDivision\Import\Product\UrlRewrite\Subjects\UrlRewriteSubject')
                            ->setMethods(
                                array(
                                    'hasHeader',
                                    'getHeader',
                                    'getHeaders',
                                    'getRootCategory',
                                    'getCategory',
                                    'getCoreConfigData',
                                    'getRowStoreId',
                                    'getRow',
                                    'hasBeenProcessed',
                                    'addEntityIdVisibilityIdMapping',
                                    'getEntityIdVisibilityIdMapping',
                                    'getStoreViewCode',
                                    'isDebugMode',
                                    'storeIsActive',
                                    'getSystemLogger',
                                )
                            )
                            ->disableOriginalConstructor()
                            ->getMock();

        // mock the methods
        $mockSubject->expects($this->any())
            ->method('getSystemLogger')
            ->willReturn($mockSystemLogger);
        $mockSubject->expects($this->any())
                    ->method('isDebugMode')
                    ->willReturn(false);
        $mockSubject->expects($this->any())
                    ->method('getHeaders')
                    ->willReturn($headers);
        $mockSubject->expects($this->any())
                    ->method('getRow')
                    ->willReturn($row);
        $mockSubject->expects($this->any())
                    ->method('hasHeader')
                    ->willReturn(true);
        $mockSubject->expects($this->any())
                    ->method('getHeader')
                    ->withConsecutive(
                        array(ColumnKeys::SKU),
                        array(ColumnKeys::URL_KEY),
                        array(ColumnKeys::URL_KEY),
                        array(ColumnKeys::STORE_VIEW_CODE),
                        array(ColumnKeys::VISIBILITY),
                        array(ColumnKeys::STORE_VIEW_CODE),
                        array(ColumnKeys::CATEGORIES),
                        array(ColumnKeys::STORE_VIEW_CODE)
                    )
                    ->willReturnOnConsecutiveCalls(0, 1, 1, 2, 3, 2, 4, 2);
        $mockSubject->expects($this->once())
                    ->method('hasBeenProcessed')
                    ->willReturn(false);
        $mockSubject->expects($this->once())
                    ->method('getEntityIdVisibilityIdMapping')
                    ->willReturn(VisibilityKeys::VISIBILITY_BOTH);
        $mockSubject->expects($this->exactly(1))
                    ->method('getRowStoreId')
                    ->willReturn($storeId = 1);
        $mockSubject->expects($this->exactly(2))
                    ->method('getCategory')
                    ->with($categoryId)
                    ->willReturn($category = array(MemberNames::ENTITY_ID => $categoryId, MemberNames::PARENT_ID => 1, MemberNames::IS_ANCHOR => null, MemberNames::URL_PATH => null));
        $mockSubject->expects($this->exactly(1))
                    ->method('getRootCategory')
                    ->willReturn($category);
        $mockSubject->expects($this->once())
                    ->method('getStoreViewCode')
                    ->with(StoreViewCodes::ADMIN)
                    ->willReturn($storeViewCode);
        $mockSubject->expects($this->once())
                    ->method('storeIsActive')
                    ->with($storeViewCode)
                    ->willReturn(true);
        $mockSubject->expects($this->exactly(1))
                    ->method('getCoreConfigData')
                    ->with(CoreConfigDataKeys::CATALOG_SEO_PRODUCT_URL_SUFFIX, '.html')
                    ->willReturn('.html');

        // mock the processor methods
        $this->mockProductUrlRewriteProcessor->expects($this->once())
                    ->method('loadProduct')
                    ->with($sku)
                    ->willReturn(array(MemberNames::ENTITY_ID => $entityId));
        $this->mockProductUrlRewriteProcessor->expects($this->once())
                    ->method('persistUrlRewrite')
                    ->with(
                        array(
                            MemberNames::ENTITY_TYPE      => UrlRewriteObserver::ENTITY_TYPE,
                            MemberNames::ENTITY_ID        => $entityId,
                            MemberNames::REQUEST_PATH     => sprintf('%s.html', $row[$headers[ColumnKeys::URL_KEY]]),
                            MemberNames::TARGET_PATH      => sprintf('catalog/product/view/id/%s', $entityId),
                            MemberNames::REDIRECT_TYPE    => 0,
                            MemberNames::STORE_ID         => $storeId,
                            MemberNames::DESCRIPTION      => null,
                            MemberNames::IS_AUTOGENERATED => 1,
                            MemberNames::METADATA         => null,
                            EntityStatus::MEMBER_NAME     => EntityStatus::STATUS_CREATE
                        )
                    )
                    ->willReturn(1000);

        // invoke the handle() method
        $this->assertSame($row, $this->observer->handle($mockSubject));
    }

    /**
     * Test's the handle() method with a successful URL rewrite persist when using the same categories.
     *
     * @return void
     */
    public function testHandleWithSuccessfullUpdateAndSameCategories()
    {

        // initialize the entity ID to use
        $entityId = 61413;

        // create a dummy CSV file row
        $headers = array(
            'sku'                => 0,
            'url_key'            => 1,
            'categories'         => 2,
            'store_view_code'    => 3,
            'visibility'         => 4
        );

        // create a dummy CSV file header
        $row = array(
            0 => $sku = 'TEST-01',
            1 => 'bruno-compete-hoodie',
            2 => 'Default Category/Men/Tops/Hoodies & Sweatshirts,Default Category/Collections/Eco Friendly,Default Category',
            3 => $storeViewCode = 'default',
            4 => 'Catalog, Search'
        );

        // initialize the categories
        $categories = array(
             $path1 = 'Default Category'                                => array(MemberNames::ENTITY_ID => 2, MemberNames::PARENT_ID => 1, MemberNames::IS_ANCHOR => null, MemberNames::URL_PATH => null, MemberNames::PATH => $path1),
             $path2 = 'Default Category/Men'                            => array(MemberNames::ENTITY_ID => 3, MemberNames::PARENT_ID => 2, MemberNames::IS_ANCHOR => null, MemberNames::URL_PATH => 'men', MemberNames::PATH => $path2),
             $path3 = 'Default Category/Men/Tops'                       => array(MemberNames::ENTITY_ID => 4, MemberNames::PARENT_ID => 3, MemberNames::IS_ANCHOR => null, MemberNames::URL_PATH => 'men/tops-men', MemberNames::PATH => $path3),
             $path4 = 'Default Category/Men/Tops/Hoodies & Sweatshirts' => array(MemberNames::ENTITY_ID => 5, MemberNames::PARENT_ID => 4, MemberNames::IS_ANCHOR => null, MemberNames::URL_PATH => 'men/tops-men/hoodies-and-sweatshirts-men', MemberNames::PATH => $path4),
             $path5 = 'Default Category/Collections'                    => array(MemberNames::ENTITY_ID => 6, MemberNames::PARENT_ID => 3, MemberNames::IS_ANCHOR => null, MemberNames::URL_PATH => 'collections', MemberNames::PATH => $path5),
             $path6 = 'Default Category/Collections/Eco Friendly'       => array(MemberNames::ENTITY_ID => 7, MemberNames::PARENT_ID => 6, MemberNames::IS_ANCHOR => null, MemberNames::URL_PATH => 'collections/eco-friendly', MemberNames::PATH => $path6),
        );

        // initialize a mock import adapter instance
        $mockImportAdapter = $this->getMockBuilder(SerializerAwareAdapterInterface::class)->getMock();
        $mockImportAdapter->expects($this->exactly(1))
            ->method('explode')
            ->withConsecutive(
                array($row[2]),
                array('Default Category/Men/Tops/Hoodies & Sweatshirts'),
                array('Default Category/Collections/Eco Friendly'),
                array('Default Category')
            )
            ->willReturnOnConsecutiveCalls(
                array('Default Category/Men/Tops/Hoodies & Sweatshirts', 'Default Category/Collections/Eco Friendly', 'Default Category'),
                array('Default Category', 'Men', 'Tops', 'Hoodies & Sweatshirts'),
                array('Default Category', 'Collections', 'Eco Friendly'),
                array('Default Category')
            );

        // mock the system logger
        $mockSystemLogger = $this->getMockBuilder('Psr\Log\LoggerInterface')
                                 ->setMethods(get_class_methods('Psr\Log\LoggerInterface'))
                                 ->getMock();

        // create a mock subject
        $mockSubject = $this->getMockBuilder('TechDivision\Import\Product\UrlRewrite\Subjects\UrlRewriteSubject')
                            ->setMethods(
                                array(
                                    'hasHeader',
                                    'getHeader',
                                    'getHeaders',
                                    'getRootCategory',
                                    'getRowStoreId',
                                    'getCategory',
                                    'getCoreConfigData',
                                    'getRow',
                                    'hasBeenProcessed',
                                    'addEntityIdVisibilityIdMapping',
                                    'getEntityIdVisibilityIdMapping',
                                    'getStoreViewCode',
                                    'isDebugMode',
                                    'storeIsActive',
                                    'getCategoryByPath',
                                    'getSystemLogger',
                                    'getImportAdapter'
                                )
                            )
                            ->disableOriginalConstructor()
                            ->getMock();

        // mock the methods
        $mockSubject->expects($this->any())
                    ->method('getSystemLogger')
                    ->willReturn($mockSystemLogger);
        $mockSubject->expects($this->any())
                    ->method('isDebugMode')
                    ->willReturn(false);
        $mockSubject->expects($this->any())
                    ->method('getHeaders')
                    ->willReturn($headers);
        $mockSubject->expects($this->any())
                    ->method('getRow')
                    ->willReturn($row);
        $mockSubject->expects($this->any())
                    ->method('hasHeader')
                    ->willReturn(true);
        $mockSubject->expects($this->any())
                    ->method('getSystemLogger')
                    ->willReturn($mockSystemLogger);
        $mockSubject->expects($this->any())
                    ->method('getHeader')
                    ->withConsecutive(
                        array(ColumnKeys::SKU),
                        array(ColumnKeys::URL_KEY),
                        array(ColumnKeys::URL_KEY),
                        array(ColumnKeys::STORE_VIEW_CODE),
                        array(ColumnKeys::VISIBILITY),
                        array(ColumnKeys::STORE_VIEW_CODE),
                        array(ColumnKeys::CATEGORIES),
                        array(ColumnKeys::STORE_VIEW_CODE)
                    )
                    ->willReturnOnConsecutiveCalls(0, 1, 1, 3, 4, 3, 2, 3);
        $mockSubject->expects($this->once())
                    ->method('hasBeenProcessed')
                    ->willReturn(false);
        $mockSubject->expects($this->exactly(3))
                    ->method('getCategoryByPath')
                    ->withConsecutive(
                        array($path4 /* Default Category/Men/Tops/Hoodies & Sweatshirts */),
                        array($path6 /* Default Category/Collections/Eco Friendly */),
                        array($path1 /* Default Category */)
                    )
                    ->willReturnOnConsecutiveCalls(
                        $categories[$path4],
                        $categories[$path6],
                        $categories[$path1]
                    );
        $mockSubject->expects($this->any())
                    ->method('getCategory')
                    ->will(
                        $this->returnCallback(function ($categoryId, $storeViewCode) use ($categories) {
                            foreach ($categories as $category) {
                                if ((int) $category[MemberNames::ENTITY_ID] === (int) $categoryId) {
                                    return $category;
                                }
                            }
                        }));
        $mockSubject->expects($this->any())
                    ->method('getRootCategory')
                    ->willReturn($categories[$path1]);
        $mockSubject->expects($this->once())
                    ->method('getStoreViewCode')
                    ->with(StoreViewCodes::ADMIN)
                    ->willReturn($storeViewCode);
        $mockSubject->expects($this->once())
                    ->method('storeIsActive')
                    ->with($storeViewCode)
                    ->willReturn(true);
        $mockSubject->expects($this->once())
                    ->method('getEntityIdVisibilityIdMapping')
                    ->willReturn(VisibilityKeys::VISIBILITY_BOTH);
        $mockSubject->expects($this->any())
                    ->method('getRowStoreId')
                    ->willReturn($storeId = 1);
        $mockSubject->expects($this->any())
                    ->method('getCoreConfigData')
                    ->will(
                        $this->returnCallback(function ($arg1, $arg2) {
                            return $arg2;
                        })
                    );
       $mockSubject->expects(($this->exactly(1)))
                    ->method('getImportAdapter')
                    ->willReturn($mockImportAdapter);

        // mock the processor methods
        $this->mockProductUrlRewriteProcessor->expects($this->once())
                    ->method('loadProduct')
                    ->with($sku)
                    ->willReturn(array(MemberNames::ENTITY_ID => $entityId));
        $this->mockProductUrlRewriteProcessor->expects($this->exactly(3))
                    ->method('persistUrlRewrite')
                    ->withConsecutive(
                        array(
                            array(
                                MemberNames::ENTITY_TYPE      => UrlRewriteObserver::ENTITY_TYPE,
                                MemberNames::ENTITY_ID        => $entityId,
                                MemberNames::REQUEST_PATH     => sprintf('%s.html', $row[$headers[ColumnKeys::URL_KEY]]),
                                MemberNames::TARGET_PATH      => sprintf('catalog/product/view/id/%s', $entityId),
                                MemberNames::REDIRECT_TYPE    => 0,
                                MemberNames::STORE_ID         => $storeId,
                                MemberNames::DESCRIPTION      => null,
                                MemberNames::IS_AUTOGENERATED => 1,
                                MemberNames::METADATA         => null,
                                EntityStatus::MEMBER_NAME     => EntityStatus::STATUS_CREATE
                            )
                        ),
                        array(
                            array(
                                MemberNames::ENTITY_TYPE      => UrlRewriteObserver::ENTITY_TYPE,
                                MemberNames::ENTITY_ID        => $entityId,
                                MemberNames::REQUEST_PATH     => sprintf('men/tops-men/hoodies-and-sweatshirts-men/%s.html', $row[$headers[ColumnKeys::URL_KEY]]),
                                MemberNames::TARGET_PATH      => sprintf('catalog/product/view/id/%s/category/5', $entityId),
                                MemberNames::REDIRECT_TYPE    => 0,
                                MemberNames::STORE_ID         => $storeId,
                                MemberNames::DESCRIPTION      => null,
                                MemberNames::IS_AUTOGENERATED => 1,
                                MemberNames::METADATA         => json_encode(array('category_id' => "5")),
                                EntityStatus::MEMBER_NAME     => EntityStatus::STATUS_CREATE
                            )
                        ),
                        array(
                            array(
                                MemberNames::ENTITY_TYPE      => UrlRewriteObserver::ENTITY_TYPE,
                                MemberNames::ENTITY_ID        => $entityId,
                                MemberNames::REQUEST_PATH     => sprintf('collections/eco-friendly/%s.html', $row[$headers[ColumnKeys::URL_KEY]]),
                                MemberNames::TARGET_PATH      => sprintf('catalog/product/view/id/%s/category/7', $entityId),
                                MemberNames::REDIRECT_TYPE    => 0,
                                MemberNames::STORE_ID         => $storeId,
                                MemberNames::DESCRIPTION      => null,
                                MemberNames::IS_AUTOGENERATED => 1,
                                MemberNames::METADATA         => json_encode(array('category_id' => "7")),
                                EntityStatus::MEMBER_NAME     => EntityStatus::STATUS_CREATE
                            )
                        )
                    )
                    ->willReturnOnConsecutiveCalls(1000, 1001, 1002);
        $this->mockProductUrlRewriteProcessor->expects($this->exactly(2))
                    ->method('persistUrlRewriteProductCategory')
                    ->withConsecutive(
                        array(
                            array(
                                MemberNames::URL_REWRITE_ID   => 1001,
                                MemberNames::PRODUCT_ID       => $entityId,
                                MemberNames::CATEGORY_ID      => 5,
                                EntityStatus::MEMBER_NAME     => EntityStatus::STATUS_CREATE
                            )
                        ),
                        array(
                            array(
                                MemberNames::URL_REWRITE_ID   => 1002,
                                MemberNames::PRODUCT_ID       => $entityId,
                                MemberNames::CATEGORY_ID      => 7,
                                EntityStatus::MEMBER_NAME     => EntityStatus::STATUS_CREATE
                            )
                        )
                    );

        // invoke the handle() method
        $this->assertSame($row, $this->observer->handle($mockSubject));
    }
}
