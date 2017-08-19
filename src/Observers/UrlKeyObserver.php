<?php

/**
 * TechDivision\Import\Product\UrlRewrite\Observers\UrlKeyObserver
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

use Zend\Filter\FilterInterface;
use TechDivision\Import\Utils\StoreViewCodes;
use TechDivision\Import\Utils\Filter\UrlKeyFilterTrait;
use TechDivision\Import\Product\UrlRewrite\Utils\ColumnKeys;
use TechDivision\Import\Product\UrlRewrite\Utils\MemberNames;
use TechDivision\Import\Product\Observers\AbstractProductImportObserver;
use TechDivision\Import\Product\Services\ProductBunchProcessorInterface;

/**
 * Observer that extracts the URL key from the product name and adds a two new columns
 * with the their values.
 *
 * @author    Tim Wagner <t.wagner@techdivision.com>
 * @copyright 2016 TechDivision GmbH <info@techdivision.com>
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      https://github.com/techdivision/import-product-url-rewrite
 * @link      http://www.techdivision.com
 */
class UrlKeyObserver extends AbstractProductImportObserver
{

    /**
     * The trait that provides string => URL key conversion functionality.
     *
     * @var \TechDivision\Import\Utils\Filter\UrlKeyFilterTrait
     */
    use UrlKeyFilterTrait;

    /**
     * The product bunch processor instance.
     *
     * @var \TechDivision\Import\Product\Services\ProductBunchProcessorInterface
     */
    protected $productBunchProcessor;

    /**
     * Initialize the observer with the passed product bunch processor instance.
     *
     * @param \TechDivision\Import\Product\Services\ProductBunchProcessorInterface $productBunchProcessor   The product bunch processor instance
     * @param \Zend\Filter\FilterInterface                                         $convertLiteralUrlFilter The URL filter instance
     */
    public function __construct(ProductBunchProcessorInterface $productBunchProcessor, FilterInterface $convertLiteralUrlFilter)
    {
        $this->convertLiteralUrlFilter = $convertLiteralUrlFilter;
        $this->productBunchProcessor = $productBunchProcessor;
    }

    /**
     * Return's the product bunch processor instance.
     *
     * @return \TechDivision\Import\Product\Services\ProductBunchProcessorInterface The product bunch processor instance
     */
    protected function getProductBunchProcessor()
    {
        return $this->productBunchProcessor;
    }

    /**
     * Process the observer's business logic.
     *
     * @return void
     */
    protected function process()
    {

        // query whether or not the URL key column has a value
        if ($this->hasValue(ColumnKeys::URL_KEY)) {
            return;
        }

        // query whether or not a product name is available
        if ($this->hasValue(ColumnKeys::NAME)) {
            $this->setValue(ColumnKeys::URL_KEY, $this->makeUrlKeyUnique($this->convertNameToUrlKey($this->getValue(ColumnKeys::NAME))));
            return;
        }

        // throw an exception, that the URL key can not be initialized
        $this->getSystemLogger()->debug(
            sprintf(
                'Can\'t initialize the URL key in CSV file %s on line %d',
                $this->getFilename(),
                $this->getLineNumber()
            )
        );
    }

    /**
     * Make's the passed URL key unique by adding the next number to the end.
     *
     * @param string $urlKey The URL key to make unique
     *
     * @return string The unique URL key
     */
    protected function makeUrlKeyUnique($urlKey)
    {

        // initialize the entity type ID
        $entityType = $this->getEntityType();
        $entityTypeId = (integer) $entityType[MemberNames::ENTITY_TYPE_ID];

        // initialize the store view ID, use the admin store view if no store view has
        // been set, because the default url_key value has been set in admin store view
        $storeId = $this->getSubject()->getRowStoreId(StoreViewCodes::ADMIN);

        // initialize the counter
        $counter = 0;

        // initialize the counters
        $matchingCounters = array();
        $notMatchingCounters = array();

        // pre-initialze the URL key to query for
        $value = $urlKey;

        do {
            // try to load the attribute
            $productVarcharAttribute = $this->getProductBunchProcessor()
                                            ->loadProductVarcharAttributeByAttributeCodeAndEntityTypeIdAndStoreIdAndValue(
                                                MemberNames::URL_KEY,
                                                $entityTypeId,
                                                $storeId,
                                                $value
                                            );

            // try to load the product's URL key
            if ($productVarcharAttribute) {
                // this IS the URL key of the passed entity
                if ($this->isUrlKeyOf($productVarcharAttribute)) {
                    $matchingCounters[] = $counter;
                } else {
                    $notMatchingCounters[] = $counter;
                }

                // prepare the next URL key to query for
                $value = sprintf('%s-%d', $urlKey, ++$counter);
            }

        } while ($productVarcharAttribute);

        // sort the array ascending according to the counter
        asort($matchingCounters);
        asort($notMatchingCounters);

        // this IS the URL key of the passed entity => we've an UPDATE
        if (sizeof($matchingCounters) > 0) {
            // load highest counter
            $counter = end($matchingCounters);
            // if the counter is > 0, we've to append it to the new URL key
            if ($counter > 0) {
                $urlKey = sprintf('%s-%d', $urlKey, $counter);
            }
        } elseif (sizeof($notMatchingCounters) > 0) {
            // create a new URL key by raising the counter
            $newCounter = end($notMatchingCounters);
            $urlKey = sprintf('%s-%d', $urlKey, ++$newCounter);
        }

        // return the passed URL key, if NOT
        return $urlKey;
    }
}
