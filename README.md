# Pacemaker - Product URL Rewrite Import

[![Latest Stable Version](https://img.shields.io/packagist/v/techdivision/import-product-url-rewrite.svg?style=flat-square)](https://packagist.org/packages/techdivision/import-product-url-rewrite) 
 [![Total Downloads](https://img.shields.io/packagist/dt/techdivision/import-product-url-rewrite.svg?style=flat-square)](https://packagist.org/packages/techdivision/import-product-url-rewrite)
 [![License](https://img.shields.io/packagist/l/techdivision/import-product-url-rewrite.svg?style=flat-square)](https://packagist.org/packages/techdivision/import-product-url-rewrite)
 [![Build Status](https://img.shields.io/travis/techdivision/import-product-url-rewrite/master.svg?style=flat-square)](http://travis-ci.org/techdivision/import-product-url-rewrite)
 [![Scrutinizer Code Quality](https://img.shields.io/scrutinizer/g/techdivision/import-product-url-rewrite/master.svg?style=flat-square)](https://scrutinizer-ci.com/g/techdivision/import-product-url-rewrite/?branch=master) [![Code Coverage](https://img.shields.io/scrutinizer/coverage/g/techdivision/import-product-url-rewrite/master.svg?style=flat-square)](https://scrutinizer-ci.com/g/techdivision/import-product-url-rewrite/?branch=master)

Please visit the Pacemaker [website](https://pacemaker.techdivision.com) or our [documentation](https://docs.met.tdintern.de/pacemaker/1.3/) for additional information

## Multistore URL Rewrite Import
* Importer supports multistore url_key, it is only to be noted that if the file for a new product contains only default store row, only one entry for defualt store is stored in the table **catalog_product_entity_varchar** and the **url_rewrite** table for all stores a key is deposited.
* When updating this product with a new key for this product, all stores will be changed.
* To avoid this problem, the default line can be omitted and only the specific store imported.

### Speziall Fall
In some cases the default row must be included in the product file, in which case, if there are no entries in the varchar for the stores already, the url_rewrite for all stores is updated based on what is in the default column.

### Lösung
* To solve the problem only the default store and the specific store should be imported and so the product will only be imported for the store and the specific store.
* import should always include all stores in a new product, so each store has an entry in the **catalog_product_entity_varchar** table for the **url_key** attribute.
* If the category is only to be updated in a store, the default row must be supplied with the specific store, then the url_rewrtie and url_key are updated in the Varchar table.