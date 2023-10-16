# 25.0.4

## Features

* Add a generic method `getCategoryByValue` to load categories based on different values with serialized return.

# 25.0.3

## Bugfixes

* None

## Features

* Update readme file

# 25.0.2

## Bugfixes

* Start fix php8.2 deprecated warnings

# 25.0.1

## Bugfixes

* Url DB exception handle over strict-mode and ignore DB exceptions

## Features

* none

# 25.0.0

## Bugfixes

* Fixed #PAC-325: Rewrites for the store views are updated unintentionally
* Fixed #PAC-492: Case sensitiv url request path

## Features

* Refactoring deprecated classes. see https://github.com/techdivision/import-cli-simple/blob/master/UPGRADE-4.0.0.md
* Add #PAC-273: Update product 301 URL redirects to forward to active URL rewrite
* Prepare optimze performance cache
* PAC-294: Integration strict mode
* PAC-541: Update composer with php Version ">=^7.3"

# 24.1.0

## Bugfixes

* None

## Features

* Add #PAC-252: Only generate category product rewrites if enabled in backend

# Version 24.0.0

## Bugfixes

* None

## Features

* Switch to latest techdivision/import-product 24.* version as dependency

# Version 23.0.0

## Bugfixes

* None

## Features

* Switch to latest techdivision/import-product 23.* version as dependency

# Version 22.0.2

## Bugfixes

* Fixed techdivision/import#185

## Features

* None

# Version 22.0.1

## Bugfixes

* Fixed techdivision/import#178

## Features

* None

# Version 22.0.0

## Bugfixes

* None

## Features

* Switch to latest techdivision/import-product 22.* version as dependency

# Version 21.0.0

## Bugfixes

* None

## Features

* Switch to latest techdivision/import-product 21.* version as dependency

# Version 20.0.0

## Bugfixes

* None

## Features

* Switch to latest techdivision/import-product 20.* version as dependency

# Version 19.0.1

## Bugfixes

* None

## Features

* Extract dev autoloading
* Add command to import URL rewrites

# Version 19.0.0

## Bugfixes

* None

## Features

* Remove duplicated SQLs and SQL statement keys

# Version 18.0.2

## Bugfixes

* Fixed PHPUnit testsuite

## Features

* None

# Version 18.0.1

## Bugfixes

* Fixed invalid product URL rewrite metadata that results in dead product URL rewrites

## Features

* None

# Version 18.0.0

## Bugfixes

* None

## Features

* Remove deprecated classes and methods
* Add techdivision/import-cli-simple#216
* Switch to latest techdivision/import-product 18.* version as dependency

# Version 17.0.0

## Bugfixes

* None

## Features

* Switch to latest techdivision/import-product 18.* version as dependency

# Version 16.0.0

## Bugfixes

* None

## Features

* Switch to latest techdivision/import-product 17.* version as dependency

# Version 15.0.0

## Bugfixes

* None

## Features

* Switch to latest techdivision/import-product 16.* version as dependency

# Version 14.0.0

## Bugfixes

* None

## Features

* Switch to latest techdivision/import-product 15.* version as dependency

# Version 13.0.0

## Bugfixes

* None

## Features

* Switch to latest techdivision/import-product 13.* version as dependency

# Version 12.0.0

## Bugfixes

* None

## Features

* Switch to latest techdivision/import-product 12.* version as dependency

# Version 11.0.0

## Bugfixes

* None

## Features

* Switch to latest techdivision/import-product 11.0.* version as dependency

# Version 10.0.0

## Bugfixes

* None

## Features

* Switch to latest techdivision/import-product 10.0.* version as dependency

# Version 9.0.0

## Bugfixes

* None

## Features

* Switch to latest techdivision/import-product 9.0.* version as dependency

# Version 8.0.0

## Bugfixes

* None

## Features

* Switch to latest techdivision/import-product 8.0.* version as dependency
* Make Actions and ActionInterfaces deprecated, replace DI configuration with GenericAction + GenericIdentifierAction

# Version 7.0.0

## Bugfixes

* None

## Features

* Added techdivision/import-cli-simple#198

# Version 6.0.0

## Bugfixes

* None

## Features

* Switch to techdivision/import-product 6.0.* version as dependency

# Version 5.0.0

## Bugfixes

* None

## Features

* Switch to latest techdivision/import-product 5.0.* version as dependency

# Version 4.0.0

## Bugfixes

* None

## Features

* Switch to latest techdivision/import 5.0.* version as dependency

# Version 3.0.0

## Bugfixes

* None

## Features

* Compatibility for Magento 2.3.x

# Version 2.0.1

## Bugfixes

* Replace serialize/unserialie with json_encode/json_decode methods

## Features

* None

# Version 2.0.0

## Bugfixes

* None

## Features

* Compatibility for Magento 2.2.x

# Version 1.0.0

## Bugfixes

* None

## Features

* Move PHPUnit test from tests to tests/unit folder for integration test compatibility reasons

# Version 1.0.0-beta13

## Bugfixes

* None

## Features

* Add missing interfaces for actions and repositories
* Replace class type hints for ProductUrlRewriteProcessor with interfaces

# Version 1.0.0-beta12

## Bugfixes

* None

## Features

* Configure DI to pass event emitter to subjects constructor

# Version 1.0.0-beta11

## Bugfixes

* None

## Features

* Refactored DI + switch to new SqlStatementRepositories instead of SqlStatements

# Version 1.0.0-beta10

## Bugfixes

* Update category path handling in order to use store view specific slugs

## Features

* None

# Version 1.0.0-beta9

## Bugfixes

* Fixed invalid metadata und catalog_url_rewrite_product_category relation for URL root category URL rewrites

## Features

* None

# Version 1.0.0-beta8

## Bugfixes

* Add validation for url rewrite update to prevent that url rewrites are persisted, where target_path and request_path are equal.

## Features

* None

# Version 1.0.0-beta7

## Bugfixes

* Fixes bug, where url rewrite update is processed with an exception. This happens, when no rows with an active store_view exist for a given sku.

## Features

* None

# Version 1.0.0-beta6

## Bugfixes

* None

## Features

* Switch error level when removing old URL rewrites from notice to warning
* Move configuration keys for clean-up URL rewrites to techdivision/import library

# Version 1.0.0-beta5

## Bugfixes

* Fixed invalid metadata initialization when old category can not be loaded

## Features

* None

# Version 1.0.0-beta4

## Bugfixes

* Fixed invalid handling when URL rewrites have been deleted (e. g. because category product relation has been removed) and re-created

## Features

* Add configurable functionality to remove old URL rewrites that not longer exists

# Version 1.0.0-beta3

## Bugfixes

* None

## Features

* Ignore relations with invalid categories in debug mode

# Version 1.0.0-beta2

## Bugfixes

* None

## Features

* Move complete product URL rewrite functionality to this library

# Version 1.0.0-beta1

## Bugfixes

* None

## Features

* Initial release after moving files from techdivision/import-product library
