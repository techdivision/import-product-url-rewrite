<?php

/**
 * TechDivision\Import\Product\UrlRewrite\Observers\UrlRewriteUpdateObserverTest
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

/**
 * Test class for the product URL rewrite update observer implementation.
 *
 * @author    Tim Wagner <t.wagner@techdivision.com>
 * @copyright 2016 TechDivision GmbH <info@techdivision.com>
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      https://github.com/techdivision/import-product-url-rewrite
 * @link      http://www.techdivision.com
 */
class UrlRewriteUpdateObserverTest extends \PHPUnit_Framework_TestCase
{

    /**
     * The observer we want to test.
     *
     * @var \TechDivision\Import\Product\UrlRewrite\Observers\UrlRewriteUpdateObserver
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
        $this->observer = new UrlRewriteUpdateObserver($this->mockProductUrlRewriteProcessor);
    }

    /**
     * Test's the handle() method with a successfull URL rewrite persist when using different categories.
     *
     * @return void
     */
    public function testHandleWithSuccessfullUpdateAndDifferentCategories()
    {

        // initialize the entity ID to use
        $entityId = 61413;

        // create a dummy CSV file row
        $headers = array(
            'sku'             => 0,
            'url_key'         => 1,
            'categories'      => 2,
            'store_view_code' => 3,
            'visibility'      => 4
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
             $path7 = 'Default Category/Collections/Old'                => array(MemberNames::ENTITY_ID => 8, MemberNames::PARENT_ID => 6, MemberNames::IS_ANCHOR => null, MemberNames::URL_PATH => 'collections/old', MemberNames::PATH => $path7),
             $path8 = 'Default Category/Men/Old'                        => array(MemberNames::ENTITY_ID => 9, MemberNames::PARENT_ID => 3, MemberNames::IS_ANCHOR => null, MemberNames::URL_PATH => 'men/old', MemberNames::PATH => $path8),
        );

        // create a mock configuration
        $mockSubjectConfiguration = $this->getMockBuilder('TechDivision\Import\Configuration\SubjectConfigurationInterface')
                                         ->setMethods(get_class_methods('TechDivision\Import\Configuration\SubjectConfigurationInterface'))
                                         ->getMock();

        // mock the methods
        $mockSubjectConfiguration->expects($this->once())
                                 ->method('getDelimiter')
                                 ->willReturn(',');

        // mock the system logger
        $mockSystemLogger = $this->getMockBuilder('Psr\Log\LoggerInterface')
                                 ->setMethods(get_class_methods('Psr\Log\LoggerInterface'))
                                 ->getMock();

        // the found URL rewrites
        $urlRewrites = array(
            array(
                MemberNames::URL_REWRITE_ID   => 744,
                MemberNames::ENTITY_TYPE      => 'product',
                MemberNames::ENTITY_ID        => $entityId,
                MemberNames::REQUEST_PATH     => sprintf('%s-old.html', $row[$headers[ColumnKeys::URL_KEY]]),
                MemberNames::TARGET_PATH      => sprintf('catalog/product/view/id/%s', $entityId),
                MemberNames::REDIRECT_TYPE    => 0,
                MemberNames::STORE_ID         => 1,
                MemberNames::DESCRIPTION      => null,
                MemberNames::IS_AUTOGENERATED => 1,
                MemberNames::METADATA         => serialize(array())
            ),
            array(
                MemberNames::URL_REWRITE_ID   => 745,
                MemberNames::ENTITY_TYPE      => 'product',
                MemberNames::ENTITY_ID        => $entityId,
                MemberNames::REQUEST_PATH     => sprintf('old/tops-old/hoodies/%s-old.html', $row[$headers[ColumnKeys::URL_KEY]]),
                MemberNames::TARGET_PATH      => sprintf('catalog/product/view/id/%s/category/8', $entityId),
                MemberNames::REDIRECT_TYPE    => 0,
                MemberNames::STORE_ID         => 1,
                MemberNames::DESCRIPTION      => null,
                MemberNames::IS_AUTOGENERATED => 1,
                MemberNames::METADATA         => serialize(array('category_id' => 8))
            ),
            array(
                MemberNames::URL_REWRITE_ID   => 746,
                MemberNames::ENTITY_TYPE      => 'product',
                MemberNames::ENTITY_ID        => $entityId,
                MemberNames::REQUEST_PATH     => sprintf('collections-old/eco-friendly/%s-old.html', $row[$headers[ColumnKeys::URL_KEY]]),
                MemberNames::TARGET_PATH      => sprintf('catalog/product/view/id/%s/category/9', $entityId),
                MemberNames::REDIRECT_TYPE    => 0,
                MemberNames::STORE_ID         => 1,
                MemberNames::DESCRIPTION      => null,
                MemberNames::IS_AUTOGENERATED => 1,
                MemberNames::METADATA         => serialize(array('category_id' => 9))
            )
        );

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
                                        'getSystemLogger'
                                    )
                                )
                                ->disableOriginalConstructor()
                                ->getMock();

        // set the mock configuration on the subject
        $mockSubject->setConfiguration($mockSubjectConfiguration);

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
                        array(ColumnKeys::CATEGORIES),
                        array(ColumnKeys::STORE_VIEW_CODE)
                    )
                    ->willReturnOnConsecutiveCalls(0, 1, 1, 3, 4, 2, 2);
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
        $mockSubject->expects($this->exactly(11))
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
                        array($categories[$path1][MemberNames::ENTITY_ID])
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
                        $categories[$path1]
                    );
        $mockSubject->expects($this->any())
                    ->method('getRootCategory')
                    ->willReturn(array(MemberNames::ENTITY_ID =>  2, MemberNames::PARENT_ID => 1, MemberNames::IS_ANCHOR => null, MemberNames::URL_PATH => null));
        $mockSubject->expects($this->once())
                    ->method('getStoreViewCode')
                    ->with(StoreViewCodes::ADMIN)
                    ->willReturn($storeViewCode);
        $mockSubject->expects($this->once())
                    ->method('storeIsActive')
                    ->with($storeViewCode)
                    ->willReturn(true);
        $mockSubject->expects($this->exactly(4))
                    ->method('getEntityIdVisibilityIdMapping')
                    ->willReturn(VisibilityKeys::VISIBILITY_BOTH);
        $mockSubject->expects($this->any())
                    ->method('getRowStoreId')
                    ->willReturn($storeId = 1);
        $mockSubject->expects($this->exactly(10))
                    ->method('getCoreConfigData')
                    ->withConsecutive(
                        array(CoreConfigDataKeys::CATALOG_SEO_PRODUCT_USE_CATEGORIES, false),
                        array(CoreConfigDataKeys::CATALOG_SEO_PRODUCT_URL_SUFFIX, '.html'),
                        array(CoreConfigDataKeys::CATALOG_SEO_PRODUCT_URL_SUFFIX, '.html'),
                        array(CoreConfigDataKeys::CATALOG_SEO_PRODUCT_URL_SUFFIX, '.html'),
                        array(CoreConfigDataKeys::CATALOG_SEO_SAVE_REWRITES_HISTORY, true),
                        array(CoreConfigDataKeys::CATALOG_SEO_PRODUCT_URL_SUFFIX, '.html'),
                        array(CoreConfigDataKeys::CATALOG_SEO_SAVE_REWRITES_HISTORY, true),
                        array(CoreConfigDataKeys::CATALOG_SEO_PRODUCT_URL_SUFFIX, '.html'),
                        array(CoreConfigDataKeys::CATALOG_SEO_SAVE_REWRITES_HISTORY, true),
                        array(CoreConfigDataKeys::CATALOG_SEO_PRODUCT_URL_SUFFIX, '.html')
                    )
                    ->willReturnOnConsecutiveCalls(true, '.html', '.html', '.html', true, '.html', true, '.html', true, '.html', '.html');

        // mock the processor methods
        $this->mockProductUrlRewriteProcessor->expects($this->once())
                    ->method('loadProduct')
                    ->with($sku)
                    ->willReturn(array(MemberNames::ENTITY_ID => $entityId));
        $this->mockProductUrlRewriteProcessor->expects($this->any())
                    ->method('loadUrlRewriteProductCategory')
                    ->willReturn(array());
        $this->mockProductUrlRewriteProcessor->expects($this->once())
                    ->method('getUrlRewritesByEntityTypeAndEntityIdAndStoreId')
                    ->with(UrlRewriteObserver::ENTITY_TYPE, $entityId, $storeId)
                    ->willReturn($urlRewrites);
       $this->mockProductUrlRewriteProcessor->expects($this->exactly(6))
                    ->method('persistUrlRewrite')
                    ->withConsecutive(
                        array(
                            array(
                                EntityStatus::MEMBER_NAME     => EntityStatus::STATUS_CREATE,
                                MemberNames::ENTITY_TYPE      => UrlRewriteObserver::ENTITY_TYPE,
                                MemberNames::ENTITY_ID        => $entityId,
                                MemberNames::REQUEST_PATH     => sprintf('%s.html', $row[$headers[ColumnKeys::URL_KEY]]),
                                MemberNames::TARGET_PATH      => sprintf('catalog/product/view/id/%s', $entityId),
                                MemberNames::REDIRECT_TYPE    => 0,
                                MemberNames::STORE_ID         => $storeId,
                                MemberNames::DESCRIPTION      => null,
                                MemberNames::IS_AUTOGENERATED => 1,
                                MemberNames::METADATA         => serialize(array())
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
                                MemberNames::METADATA         => serialize(array('category_id' => 5)),
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
                                MemberNames::METADATA         => serialize(array('category_id' => 7)),
                                EntityStatus::MEMBER_NAME     => EntityStatus::STATUS_CREATE
                            )
                        ),
                        array(
                            array(
                                EntityStatus::MEMBER_NAME     => EntityStatus::STATUS_UPDATE,
                                MemberNames::ENTITY_TYPE      => UrlRewriteObserver::ENTITY_TYPE,
                                MemberNames::URL_REWRITE_ID   => 744,
                                MemberNames::ENTITY_ID        => $entityId,
                                MemberNames::REQUEST_PATH     => sprintf('%s-old.html', $row[$headers[ColumnKeys::URL_KEY]]),
                                MemberNames::TARGET_PATH      => sprintf('%s.html', $row[$headers[ColumnKeys::URL_KEY]]),
                                MemberNames::REDIRECT_TYPE    => 301,
                                MemberNames::STORE_ID         => $storeId,
                                MemberNames::DESCRIPTION      => null,
                                MemberNames::IS_AUTOGENERATED => 1,
                                MemberNames::METADATA         => serialize(array())
                            )
                        ),
                        array(
                            array(
                                EntityStatus::MEMBER_NAME     => EntityStatus::STATUS_UPDATE,
                                MemberNames::ENTITY_TYPE      => UrlRewriteObserver::ENTITY_TYPE,
                                MemberNames::URL_REWRITE_ID   => 745,
                                MemberNames::ENTITY_ID        => $entityId,
                                MemberNames::REQUEST_PATH     => sprintf('old/tops-old/hoodies/%s-old.html', $row[$headers[ColumnKeys::URL_KEY]]),
                                MemberNames::TARGET_PATH      => sprintf('%s.html', $row[$headers[ColumnKeys::URL_KEY]]),
                                MemberNames::REDIRECT_TYPE    => 301,
                                MemberNames::STORE_ID         => $storeId,
                                MemberNames::DESCRIPTION      => null,
                                MemberNames::IS_AUTOGENERATED => 1,
                                MemberNames::METADATA         => serialize(array('category_id' => 8))
                            )
                        ),
                        array(
                            array(
                                EntityStatus::MEMBER_NAME     => EntityStatus::STATUS_UPDATE,
                                MemberNames::ENTITY_TYPE      => UrlRewriteObserver::ENTITY_TYPE,
                                MemberNames::URL_REWRITE_ID   => 746,
                                MemberNames::ENTITY_ID        => $entityId,
                                MemberNames::REQUEST_PATH     => sprintf('collections-old/eco-friendly/%s-old.html', $row[$headers[ColumnKeys::URL_KEY]]),
                                MemberNames::TARGET_PATH      => sprintf('%s.html', $row[$headers[ColumnKeys::URL_KEY]]),
                                MemberNames::REDIRECT_TYPE    => 301,
                                MemberNames::STORE_ID         => $storeId,
                                MemberNames::DESCRIPTION      => null,
                                MemberNames::IS_AUTOGENERATED => 1,
                                MemberNames::METADATA         => serialize(array('category_id' => 9))
                            )
                        )
                    )
                    ->willReturnOnConsecutiveCalls(1000, 1001, 1002, 1003, 1004, 1005);
        $this->mockProductUrlRewriteProcessor->expects($this->exactly(3))
                    ->method('persistUrlRewriteProductCategory')
                    ->withConsecutive(
                        array(
                            array(
                                MemberNames::URL_REWRITE_ID   => 1000,
                                MemberNames::PRODUCT_ID       => $entityId,
                                MemberNames::CATEGORY_ID      => 2,
                                EntityStatus::MEMBER_NAME     => EntityStatus::STATUS_CREATE
                            )
                        ),
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
