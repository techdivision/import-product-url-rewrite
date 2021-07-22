<?php

/**
 * TechDivision\Import\Product\Fpt\Utils\CacheKeys
 *
 * Copyright (c) 2021 TechDivision GmbH.
 *
 * All rights reserved.
 *
 * This product includes proprietary software developed at TechDivision GmbH, Germany.
 * For more information see http://www.techdivision.com/.
 *
 * To obtain a valid license for using this software please contact us at
 * license@techdivision.com.
 */

namespace TechDivision\Import\Product\UrlRewrite\Utils;

/**
 * A utility class that contains the cache keys.
 *
 * @link      http://www.techdivision.com/
 * @author    Martin Eisenführer <m.eisenfuehrer@techdivision.com>
 * @copyright Copyright (c) 2021 TechDivision GmbH (http://www.techdivision.com)
 */
class CacheKeys extends \TechDivision\Import\Product\Utils\CacheKeys
{

    /**
     * Name for the table 'url_rewrite'.
     *
     * @var string
     */
    const URL_REWRITE_PRODUCT_CATEGORY = 'catalog_url_rewrite_product_category';

    /**
     * Initializes the instance with the passed cache key.
     *
     * @param string $cacheKey  The cache key use
     * @param array  $cacheKeys Additional cache keys
     */
    public function __construct($cacheKey, array $cacheKeys = array())
    {

        // merge the passed cache keys with the one from this class
        $mergedCacheKeys = array_merge(
            array(
                CacheKeys::URL_REWRITE_PRODUCT_CATEGORY,
            ),
            $cacheKeys
        );

        // pass them to the parent instance
        parent::__construct($cacheKey, $mergedCacheKeys);
    }
}
