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

use TechDivision\Import\Product\Utils\VisibilityKeys;
use TechDivision\Import\Product\Subjects\AbstractProductSubject;
use TechDivision\Import\Product\UrlRewrite\Utils\MemberNames;

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
     * The mapping for the SKU => visibility.
     *
     * @var array
     */
    protected $entityIdVisibilityIdMapping = array();

    /**
     * The array with the available visibility keys.
     *
     * @var array
     */
    protected $availableVisibilities = array(
        'Not Visible Individually' => VisibilityKeys::VISIBILITY_NOT_VISIBLE,
        'Catalog'                  => VisibilityKeys::VISIBILITY_IN_CATALOG,
        'Search'                   => VisibilityKeys::VISIBILITY_IN_SEARCH,
        'Catalog, Search'          => VisibilityKeys::VISIBILITY_BOTH
    );

    /**
     * Return's the visibility key for the passed visibility string.
     *
     * @param string $visibility The visibility string to return the key for
     *
     * @return integer The requested visibility key
     * @throws \Exception Is thrown, if the requested visibility is not available
     */
    public function getVisibilityIdByValue($visibility)
    {

        // query whether or not, the requested visibility is available
        if (isset($this->availableVisibilities[$visibility])) {
            // load the visibility ID, add the mapping and return the ID
            return $this->availableVisibilities[$visibility];
        }

        // throw an exception, if not
        throw new \Exception(
            $this->appendExceptionSuffix(
                sprintf('Found invalid visibility %s', $visibility)
            )
        );
    }

    /**
     * Return's the visibility for the passed entity ID, if it already has been mapped. The mapping will be created
     * by calling <code>\TechDivision\Import\Product\Subjects\BunchSubject::getVisibilityIdByValue</code> which will
     * be done by the <code>\TechDivision\Import\Product\Callbacks\VisibilityCallback</code>.
     *
     * @return integer The visibility ID
     * @throws \Exception Is thrown, if the entity ID has not been mapped
     * @see \TechDivision\Import\Product\Subjects\BunchSubject::getVisibilityIdByValue()
     */
    public function getEntityIdVisibilityIdMapping()
    {

        // query whether or not the SKU has already been mapped to it's visibility
        if (isset($this->entityIdVisibilityIdMapping[$entityId = $this->getLastEntityId()])) {
            return $this->entityIdVisibilityIdMapping[$entityId];
        }

        // throw a new exception
        throw new \Exception(
            $this->appendExceptionSuffix(
                sprintf('Can\'t find visibility mapping for entity ID "%d"', $entityId)
            )
        );
    }

    /**
     * Add the entity ID => visibility mapping for the actual entity ID.
     *
     * @param string $visibility The visibility of the actual entity to map
     *
     * @return void
     */
    public function addEntityIdVisibilityIdMapping($visibility)
    {
        $this->entityIdVisibilityIdMapping[$this->getLastEntityId()] = $this->getVisibilityIdByValue($visibility);
    }

    /**
     * Return's TRUE if the store with the passed code is active, else FALSE.
     *
     * @param string $storeViewCode The store view code of the store to check for the active flag set to 1
     *
     * @return boolean TRUE if the store is active, else FALSE
     * @throws \Exception Is thrown, if the store with the actual code is not available
     */
    public function storeIsActive($storeViewCode)
    {

        // query whether or not, the requested store is available
        if (isset($this->stores[$storeViewCode])) {
            return 1 === (integer) $this->stores[$storeViewCode][MemberNames::IS_ACTIVE];
        }

        // throw an exception, if not
        throw new \Exception(
            $this->appendExceptionSuffix(
                sprintf('Found invalid store view code %s', $storeViewCode)
            )
        );
    }
}
