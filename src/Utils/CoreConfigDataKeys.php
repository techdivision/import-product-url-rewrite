<?php

/**
 * TechDivision\Import\Product\UrlRewrite\Utils\CoreConfigDataKeys
 *
 * @author    Marcus Döllerer <m.doellerer@techdivision.com>
 * @copyright 2020 TechDivision GmbH <info@techdivision.com>
 * @link      https://www.techdivision.com
 */

namespace TechDivision\Import\Product\UrlRewrite\Utils;

/**
 * Utility class containing the keys Magento uses to persist values in the "core_config_data table".
 *
 * @author    Marcus Döllerer <m.doellerer@techdivision.com>
 * @copyright 2020 TechDivision GmbH <info@techdivision.com>
 * @license   https://opensource.org/licenses/MIT
 * @link      https://github.com/techdivision/import-product-url-rewrite
 * @link      https://www.techdivision.com
 */
class CoreConfigDataKeys extends \TechDivision\Import\Product\Utils\CoreConfigDataKeys
{

    /**
     * Name for the column 'catalog/seo/generate_category_product_rewrites'.
     *
     * @var string
     */
    const CATALOG_SEO_GENERATE_CATEGORY_PRODUCT_REWRITES = 'catalog/seo/generate_category_product_rewrites';
}
