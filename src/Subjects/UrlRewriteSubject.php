<?php

/**
 * TechDivision\Import\Product\UrlRewrite\Subjects\UrlRewriteSubject
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

namespace TechDivision\Import\Product\UrlRewrite\Subjects;

use TechDivision\Import\Product\UrlRewrite\Utils\MemberNames;
use TechDivision\Import\Product\Subjects\AbstractProductSubject;

/**
 * The subject implementation that handles the business logic to persist URL rewrites.
 *
 * @author    Tim Wagner <t.wagner@techdivision.com>
 * @copyright 2016 TechDivision GmbH <info@techdivision.com>
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      https://github.com/techdivision/import-product-url-rewrite
 * @link      http://www.techdivision.com
 */
class UrlRewriteSubject extends AbstractProductSubject
{

    /**
     * Return's TRUE, if the passed URL key varchar value IS related with the actual PK.
     *
     * @param array $productVarcharAttribute The varchar value to check
     *
     * @return boolean TRUE if the URL key is related, else FALSE
     */
    public function isUrlKeyOf(array $productVarcharAttribute)
    {
        return ($productVarcharAttribute[MemberNames::ENTITY_ID] === $this->getLastEntityId()) && ((integer) $productVarcharAttribute[MemberNames::STORE_ID] === $this->getRowStoreId());
    }
}
