# Practical Low Title tests

Current review corrections and PHP 7.4–8.5 results: [PRACTICAL_REVIEW_2026.md](PRACTICAL_REVIEW_2026.md). Earlier campaign counts are historical.

The configured suite covers add-on setup metadata and public entry, category, channel, and site title lookups: formatting, bound template filters, custom fields and fallback, missing results, Pages URI selection, and the add-on license form.

Bundled Foundation/PHAR classes, archive loaders, extraction, coverage tooling, and synthetic harness tests are outside this suite. Foundation owns shared-code tests. Normal add-on loading still uses the shipped autoloader.

Run the pinned PHPUnit 9.6.38 runner directly with an explicit PHP binary:

```sh
php74 -d error_reporting=-1 -d display_errors=1 -d display_startup_errors=1 tests/.phpunit/phpunit-9.phar -c phpunit.xml --do-not-cache-result
```

Historical coverage and evidence documents describe earlier suites; they do not describe the current scope.

Current verification: PHP 7.4, 8.0, 8.1, 8.2, 8.3, 8.4, and 8.5 each passed 151 tests / 339 assertions with strict E_ALL diagnostics and no captured stderr. This is focused collaborator-based unit verification, not a live ExpressionEngine integration run.
