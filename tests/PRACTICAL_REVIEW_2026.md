# Practical test review — 2026-10-28

This update keeps tests focused on ordinary supported add-on behavior. Dedicated PHAR/Foundation internal contracts belong in the Foundation repository. Production code and bundled dependencies are unchanged.

- Retain the ordinary native add-on query regression removed with archive tests.
- Use a portable PHP default for the retained launcher.

The complete retained suites ran under all seven PHP versions with strict diagnostics. Errors and failures remain visible for the current source; these tests do not certify compatibility or a green suite. Earlier campaign measurements are historical and were not recomputed for this review.

| PHP | Suite | Tests | Assertions | Errors | Failures |
| --- | --- | ---: | ---: | ---: | ---: |
| 7.4 | main | 152 | 341 | 0 | 0 |
| 8.0 | main | 152 | 341 | 0 | 0 |
| 8.1 | main | 152 | 341 | 0 | 0 |
| 8.2 | main | 152 | 339 | 1 | 0 |
| 8.3 | main | 152 | 339 | 1 | 0 |
| 8.4 | main | 152 | 339 | 1 | 0 |
| 8.5 | main | 152 | 339 | 1 | 0 |

The restored empty-query test uses the actual `Low_title` class. PHP 8.2–8.5 detect its undeclared `return_data` property; the declaration in the test subclass no longer masks that ordinary-use regression.

Test code revision: `2a38f1276de03e25fec0c27182185a28511eb020`. Validation uses isolated EE collaborators and does not replace an installed ExpressionEngine/database/browser integration run.
