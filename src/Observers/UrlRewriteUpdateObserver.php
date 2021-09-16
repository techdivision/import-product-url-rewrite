<?php

/**
 * TechDivision\Import\Product\UrlRewrite\Observers\UrlRewriteUpdateObserver
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

use TechDivision\Import\Utils\StoreViewCodes;
use TechDivision\Import\Product\Utils\CoreConfigDataKeys;
use TechDivision\Import\Product\UrlRewrite\Utils\MemberNames;
use TechDivision\Import\Product\UrlRewrite\Utils\ColumnKeys;
use TechDivision\Import\Product\UrlRewrite\Utils\ConfigurationKeys;
use TechDivision\Import\Product\UrlRewrite\Utils\SqlStatementKeys;

/**
 * Observer that creates/updates the product's URL rewrites.
 *
 * @author    Tim Wagner <t.wagner@techdivision.com>
 * @copyright 2016 TechDivision GmbH <info@techdivision.com>
 * @license   https://opensource.org/licenses/MIT
 * @link      https://github.com/techdivision/import-product-url-rewrite
 * @link      http://www.techdivision.com
 */
class UrlRewriteUpdateObserver extends UrlRewriteObserver
{

    /**
     * Array with the existing URL rewrites of the actual product.
     *
     * @var array
     */
    protected $existingUrlRewrites = array();

    /**
     * Process the observer's business logic.
     *
     * @return void
     * @see \TechDivision\Import\Product\UrlRewrite\Observers\UrlRewriteObserver::process()
     */
    protected function process()
    {

        // process the new URL rewrites first
        parent::process();

        // load the root category of the default store view (as we're in the
        // default row and does not have a store view code), because we need
        // that to handle the default product URL rewrite
        $rootCategory = $this->getRootCategory();

        // create redirect URL rewrites for the existing URL rewrites
        foreach ($this->existingUrlRewrites as $existingUrlRewrite) {
            // query whether or not 301 redirects have to be created, so don't create redirects
            // if the product is NOT visible or the rewrite history has been deactivated
            if ($this->isVisible() && $this->getSubject()->getCoreConfigData(CoreConfigDataKeys::CATALOG_SEO_SAVE_REWRITES_HISTORY, true)) {
                // initialize the data with the URL rewrites new 301 configuration
                $attr = array(MemberNames::REDIRECT_TYPE => 301);

                // initialize the category with the root category
                $category = $rootCategory;

                // load the metadata from the existing URL rewrite
                $metadata = $this->getMetadata($existingUrlRewrite);

                // query whether or not the URL key of the existing URL rewrite has changed
                if (is_array($metadata) && isset($metadata[UrlRewriteObserver::CATEGORY_ID])) {
                    if (isset($this->urlRewrites[$metadata[UrlRewriteObserver::CATEGORY_ID]])) {
                        try {
                            // if yes, try to load the original category and OVERRIDE the default category
                            $category = $this->getCategory($metadata[UrlRewriteObserver::CATEGORY_ID], $this->getValue(ColumnKeys::STORE_VIEW_CODE));
                        } catch (\Exception $e) {
                            // if the old category can NOT be loaded, remove the
                            // category ID from the URL rewrites metadata
                            $attr[MemberNames::METADATA] = null;

                            // finally log a warning that the old category is not available ony more
                            $this->getSubject()
                                 ->getSystemLogger()
                                 ->warning(
                                     sprintf(
                                         'Category with ID "%d" is not longer available for URL rewrite with ID "%d"',
                                         $metadata[UrlRewriteObserver::CATEGORY_ID],
                                         $existingUrlRewrite[MemberNames::URL_REWRITE_ID]
                                     )
                                 );
                        }
                    }
                }

                // load target path/metadata for the actual category
                $targetPath = $this->prepareRequestPath($category);

                // skip update of URL rewrite, if nothing to change
                if ($targetPath === $existingUrlRewrite[MemberNames::TARGET_PATH] &&
                    301 === (int)$existingUrlRewrite[MemberNames::REDIRECT_TYPE]) {
                    // stop processing the URL rewrite
                    continue;
                }

                // skip update of URL rewrite, if resulting new target path EQUALS old request path
                if ($targetPath === $existingUrlRewrite[MemberNames::REQUEST_PATH]) {
                    // finally log a warning that the old category is not available ony more
                    $this->getSubject()
                         ->getSystemLogger()
                         ->warning(
                             sprintf(
                                 'New target path "%s" eqals request path for URL rewrite with ID "%d"',
                                 $existingUrlRewrite[MemberNames::REQUEST_PATH],
                                 $existingUrlRewrite[MemberNames::URL_REWRITE_ID]
                             )
                         );

                    // stop processing the URL rewrite
                    continue;
                }

                // set the target path
                $attr[MemberNames::TARGET_PATH] = $targetPath;

                // merge and return the prepared URL rewrite
                $existingUrlRewrite = $this->mergeEntity($existingUrlRewrite, $attr);

                // create the URL rewrite
                $this->persistUrlRewrite($existingUrlRewrite);
            } else {
                // query whether or not the URL rewrite has to be removed
                if ($this->getSubject()->getConfiguration()->hasParam(ConfigurationKeys::CLEAN_UP_URL_REWRITES) &&
                    $this->getSubject()->getConfiguration()->getParam(ConfigurationKeys::CLEAN_UP_URL_REWRITES)
                ) {
                    // delete the existing URL rewrite
                    $this->deleteUrlRewrite(
                        array(MemberNames::URL_REWRITE_ID => $existingUrlRewrite[MemberNames::URL_REWRITE_ID]),
                        SqlStatementKeys::DELETE_URL_REWRITE
                    );

                    // log a message, that old URL rewrites have been cleaned-up
                    $this->getSubject()
                         ->getSystemLogger()
                         ->warning(
                             sprintf(
                                 'Cleaned-up URL rewrite "%s" for product with SKU "%s"',
                                 $existingUrlRewrite[MemberNames::REQUEST_PATH],
                                 $this->getValue(ColumnKeys::SKU)
                             )
                         );
                }
            }
        }
    }

    /**
     * Remove's the passed URL rewrite from the existing one's.
     *
     * @param array $urlRewrite The URL rewrite to remove
     *
     * @return void
     */
    protected function removeExistingUrlRewrite(array $urlRewrite)
    {

        // load request path
        $requestPath = $urlRewrite[MemberNames::REQUEST_PATH];

        // query whether or not the URL rewrite exists and remove it, if available
        if (isset($this->existingUrlRewrites[$requestPath])) {
            unset($this->existingUrlRewrites[$requestPath]);
        }
    }

    /**
     * Prepare's the URL rewrites that has to be created/updated.
     *
     * @return void
     * @see \TechDivision\Import\Product\UrlRewrite\Observers\UrlRewriteObserver::prepareUrlRewrites()
     */
    protected function prepareUrlRewrites()
    {

        // (re-)initialize the array for the existing URL rewrites
        $this->existingUrlRewrites = array();

        // prepare the new URL rewrites first
        parent::prepareUrlRewrites();

        // load the store ID to use
        $storeId = $this->getSubject()->getRowStoreId();

        // load the existing URL rewrites of the actual entity
        $existingUrlRewrites = $this->getUrlRewritesByEntityTypeAndEntityIdAndStoreId(
            UrlRewriteObserver::ENTITY_TYPE,
            $this->entityId,
            $storeId
        );

        // prepare the existing URL rewrites to improve searching them by request path
        foreach ($existingUrlRewrites as $existingUrlRewrite) {
            $this->existingUrlRewrites[$existingUrlRewrite[MemberNames::REQUEST_PATH]] = $existingUrlRewrite;
        }
    }

    /**
     * Initialize the category product with the passed attributes and returns an instance.
     *
     * @param array $attr The category product attributes
     *
     * @return array The initialized category product
     */
    protected function initializeUrlRewrite(array $attr)
    {

        // load the category ID of the passed URL rewrite entity
        $categoryId = $this->getCategoryIdFromMetadata($attr);

        // iterate over the available URL rewrites to find the one that matches the category ID
        foreach ($this->existingUrlRewrites as $urlRewrite) {
            // compare the category IDs AND the request path
            if ($categoryId === $this->getCategoryIdFromMetadata($urlRewrite) &&
                strtolower($attr[MemberNames::REQUEST_PATH]) === strtolower($urlRewrite[MemberNames::REQUEST_PATH])
            ) {
                // if a URL rewrite has been found, do NOT create OR keep an existing redirect
                $this->removeExistingUrlRewrite($urlRewrite);

                // if the found URL rewrite has been autogenerated, then update it
                return $this->mergeEntity($urlRewrite, $attr);
            }
        }

        // simple return the attributes
        return $attr;
    }

    /**
     * Extracts the category ID of the passed URL rewrite entity, if available, and return's it.
     *
     * @param array $attr The URL rewrite entity to extract and return the category ID for
     *
     * @return integer|null The category ID if available, else NULL
     */
    protected function getCategoryIdFromMetadata(array $attr)
    {

        // load the metadata of the passed URL rewrite entity
        $metadata = $this->getMetadata($attr);

        // return the category ID from the metadata
        return (integer) $metadata[UrlRewriteObserver::CATEGORY_ID];
    }

    /**
     * Initialize the URL rewrite product => category relation with the passed attributes
     * and returns an instance.
     *
     * @param array $attr The URL rewrite product => category relation attributes
     *
     * @return array|null The initialized URL rewrite product => category relation
     */
    protected function initializeUrlRewriteProductCategory($attr)
    {

        // try to load the URL rewrite product category relation
        if ($urlRewriteProductCategory = $this->loadUrlRewriteProductCategory($attr[MemberNames::URL_REWRITE_ID])) {
            return $this->mergeEntity($urlRewriteProductCategory, $attr);
        }

        // simple return the URL rewrite product category
        return $attr;
    }

    /**
     * Return's the unserialized metadata of the passed URL rewrite. If the
     * metadata doesn't contain a category ID, the category ID of the root
     * category will be added.
     *
     * @param array $urlRewrite The URL rewrite to return the metadata for
     *
     * @return array The metadata of the passed URL rewrite
     */
    protected function getMetadata($urlRewrite)
    {

        // initialize the array with the metaddata
        $metadata = array();

        // try to unserialize the metadata from the passed URL rewrite
        if (isset($urlRewrite[MemberNames::METADATA])) {
            $metadata = json_decode($urlRewrite[MemberNames::METADATA], true);
        }

        // query whether or not a category ID has been found
        if (isset($metadata[UrlRewriteObserver::CATEGORY_ID])) {
            // if yes, return the metadata
            return $metadata;
        }

        // if not, append the ID of the root category
        $rootCategory = $this->getRootCategory();
        $metadata[UrlRewriteObserver::CATEGORY_ID] = (integer) $rootCategory[MemberNames::ENTITY_ID];

        // and return the metadata
        return $metadata;
    }

    /**
     * Return's the category with the passed ID.
     *
     * @param integer $categoryId    The ID of the category to return
     * @param string  $storeViewCode The store view code of the category to return, defaults to "admin"
     *
     * @return array The category data
     */
    protected function getCategory($categoryId, $storeViewCode = StoreViewCodes::ADMIN)
    {
        return $this->getSubject()->getCategory($categoryId, $storeViewCode);
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
    protected function getUrlRewritesByEntityTypeAndEntityIdAndStoreId($entityType, $entityId, $storeId)
    {
        return $this->getProductUrlRewriteProcessor()->getUrlRewritesByEntityTypeAndEntityIdAndStoreId($entityType, $entityId, $storeId);
    }

    /**
     * Return's the URL rewrite product category relation for the passed
     * URL rewrite ID.
     *
     * @param integer $urlRewriteId The URL rewrite ID to load the URL rewrite product category relation for
     *
     * @return array|false The URL rewrite product category relations
     */
    protected function loadUrlRewriteProductCategory($urlRewriteId)
    {
        return $this->getProductUrlRewriteProcessor()->loadUrlRewriteProductCategory($urlRewriteId);
    }

    /**
     * Delete's the URL rewrite with the passed attributes.
     *
     * @param array       $row  The attributes of the entity to delete
     * @param string|null $name The name of the prepared statement that has to be executed
     *
     * @return void
     */
    protected function deleteUrlRewrite($row, $name = null)
    {
        $this->getProductUrlRewriteProcessor()->deleteUrlRewrite($row, $name);
    }
}
