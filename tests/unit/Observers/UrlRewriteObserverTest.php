<?php

/**
 * TechDivision\Import\Product\UrlRewrite\Observers\UrlRewriteObserverTest
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

namespace TechDivision\Import\Product\UrlRewrite\Observers;

use TechDivision\Import\Utils\EntityStatus;
use TechDivision\Import\Utils\StoreViewCodes;
use TechDivision\Import\Product\Utils\VisibilityKeys;
use TechDivision\Import\Product\Utils\CoreConfigDataKeys;
use TechDivision\Import\Product\UrlRewrite\Utils\ColumnKeys;
use TechDivision\Import\Product\UrlRewrite\Utils\MemberNames;
use TechDivision\Import\Adapter\SerializerAwareAdapterInterface;

/**
 * Test class for the product URL rewrite observer implementation.
 *
 * @author    Tim Wagner <t.wagner@techdivision.com>
 * @copyright 2016 TechDivision GmbH <info@techdivision.com>
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      https://github.com/techdivision/import-product-url-rewrite
 * @link      http://www.techdivision.com
 */
class UrlRewriteObserverTest extends \PHPUnit_Framework_TestCase
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
     * @see \PHPUnit_Framework_TestCase::setUp()
     */
    protected function setUp()
    {

        // initialize a mock processor instance
        $this->mockProductUrlRewriteProcessor = $this->getMockBuilder('TechDivision\Import\Product\UrlRewrite\Services\ProductUrlRewriteProcessorInterface')
                                                     ->setMethods(get_class_methods('TechDivision\Import\Product\UrlRewrite\Services\ProductUrlRewriteProcessorInterface'))
                                                     ->getMock();

        // initialize the observer
        $this->observer = new UrlRewriteObserver($this->mockProductUrlRewriteProcessor);
    }

    /**
     * Test's the handle() method with a successfull URL rewrite persist.
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
                                    'storeIsActive'
                                )
                            )
                            ->disableOriginalConstructor()
                            ->getMock();

        // mock the methods
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
        $mockSubject->expects($this->exactly(5))
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
     * Test's the handle() method with a successfull URL rewrite persist when using the same categories.
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
        $mockImportAdapter->expects($this->once())
            ->method('explode')
            ->with($row[2])
            ->willReturn(array('Default Category/Men/Tops/Hoodies & Sweatshirts', 'Default Category/Collections/Eco Friendly', 'Default Category'));

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
        $mockSubject->expects($this->exactly(13))
                    ->method('getCategory')
                    ->withConsecutive(
                        array($categories[$path4][MemberNames::ENTITY_ID]),
                        array($categories[$path3][MemberNames::ENTITY_ID]),
                        array($categories[$path2][MemberNames::ENTITY_ID]),
                        array($categories[$path6][MemberNames::ENTITY_ID]),
                        array($categories[$path5][MemberNames::ENTITY_ID]),
                        array($categories[$path2][MemberNames::ENTITY_ID]),
                        array($categories[$path1][MemberNames::ENTITY_ID]),
                        array($categories[$path1][MemberNames::ENTITY_ID]),
                        array($categories[$path4][MemberNames::ENTITY_ID]),
                        array($categories[$path6][MemberNames::ENTITY_ID]),
                        array($categories[$path1][MemberNames::ENTITY_ID]),
                        array($categories[$path4][MemberNames::ENTITY_ID]),
                        array($categories[$path6][MemberNames::ENTITY_ID])
                    )
                    ->willReturnOnConsecutiveCalls(
                        $categories[$path4],
                        $categories[$path3],
                        $categories[$path2],
                        $categories[$path6],
                        $categories[$path5],
                        $categories[$path2],
                        $categories[$path1],
                        $categories[$path1],
                        $categories[$path4],
                        $categories[$path6],
                        $categories[$path1],
                        $categories[$path4],
                        $categories[$path6]
                    );
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
        $mockSubject->expects($this->exactly(3))
                    ->method('getCoreConfigData')
                    ->withConsecutive(
                        array(CoreConfigDataKeys::CATALOG_SEO_PRODUCT_URL_SUFFIX, '.html'),
                        array(CoreConfigDataKeys::CATALOG_SEO_PRODUCT_URL_SUFFIX, '.html'),
                        array(CoreConfigDataKeys::CATALOG_SEO_PRODUCT_URL_SUFFIX, '.html')
                    )
                    ->willReturnOnConsecutiveCalls('.html', '.html', '.html');
       $mockSubject->expects(($this->once()))
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
