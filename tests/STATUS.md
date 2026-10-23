# Low Title PHPUnit checkpoint

Status: **incomplete / source and instrumentation blockers remain**. The suite deliberately retains failing production regressions. Production code and the shipped PHAR are unchanged.

## Baseline and scope

- Origin: `https://github.com/EEHarbor/low_title.git`.
- Fresh `git fetch origin` and clean `git pull --ff-only origin develop` completed before branch creation. Baseline `e3e13533ab0e67d13cdd16bedc0e377fa13910f0` matched `origin/develop` at `0 0`.
- Branch: `unit-tests-2026`; this overrides the skill's branch/helper and global-runtime instructions.
- Inventory: 4 loose PHP files, 22 PHP members in `low_title.phar`, and its executable extraction stub: 27 source units and 266 functions/methods/closures including abstract declarations. See `source-inventory.json`.
- PHAR SHA-256: `79a7b868dcc5cf133984eb6c1a2f1f5942b1206b7b6661424bc6fd5142227d81`.
- No add-on runtime dependencies were introduced. PHPUnit tooling is add-on-local, pinned by version and SHA-256 in `.phpunit/versions.txt`; `.phpunit/install.sh` recreates ignored tool files.

## Harness and commands

Preflight is successful and resolves `./tests/.phpunit/run-phpunit.sh -c phpunit.xml`. The helper's optional `--help` pipeline emitted a non-blocking support-detection note, while actual Clover generation confirms support.

```sh
./tests/.phpunit/install.sh
"/Users/tomjaeger/Library/Application Support/Herd/bin/php74" tests/scripts/inventory.php
./tests/.phpunit/run-phpunit.sh -c "$PWD/phpunit.xml"
./tests/scripts/run-matrix.sh
```

The initial skill coverage command found no tests and could not produce baseline Clover (the unbootstrapped direct-access guard terminated uncovered-file processing). No baseline source coverage existed. The final harness defines EE constants before loading production and uses `includeUncoveredFiles=true` with `processUncoveredFiles=false` so coverage analysis does not execute context-dependent views outside their fixtures.

```sh
./tests/scripts/run-coverage.sh lines
./tests/scripts/run-coverage.sh branches
# Both print unique REPORT_DIR paths and verify the pinned PHAR before execution.
# Substitute those paths below.
python3 tests/scripts/summarize-branches.py <branch-report-dir>/raw.json tests/reports/branches.json
python3 tests/scripts/snapshot-coverage.py <line-report-dir>/clover.xml tests/reports/branches.json tests/reports/coverage-snapshot.json
python3 tests/scripts/render-coverage.py tests/reports/coverage-snapshot.json tests/COVERAGE.md

/Users/tomjaeger/.codex/skills/ee-addon-phpunit-coverage-writer/scripts/addon_method_coverage_report.py \
  --repo-root "$PWD" --source-root low_title --coverage-clover <line-report-dir>/clover.xml --reuse-clover --json
/Users/tomjaeger/.codex/skills/ee-addon-phpunit-coverage-writer/scripts/addon_method_coverage_report.py \
  --repo-root "$PWD" --source-root tests/.source --coverage-clover <line-report-dir>/clover.xml --reuse-clover --json
```

E_ALL diagnostics from shipped source and test support throw even under `@`. A fresh subprocess deliberately triggers an `@`-suppressed user deprecation to verify enforcement. PHPUnit's own suppressed filesystem probes retain their internal handling; unsuppressed tooling diagnostics still fail. Every native child uses its parent's explicit PHP binary and E_ALL; native children deliberately disable coverage and measurement-source substitution so shipped loading/compilation is tested separately.

Algorithm subjects declare missing source properties to permit independent behavior tests while native unmodified classes separately fail for their dynamic-property diagnostics. No source diagnostic is made an expected success. Reflection accessibility calls are limited to PHP < 8.1; PHP 8.5 does not execute the deprecated API.

EE collaborator shapes were checked against `/Users/tomjaeger/Sites/ee_repo_2`: `EE_DB_result::row()` including MySQL nulls and empty arrays, template missing-parameter defaults, fieldtype content IDs, terminating redirects, and Sidebar Header objects. Direct harness contract vectors verify numeric/current result rows, nulls, POST-first input, the strict XSS flag, template yes/no aliases with the url_title exception, and fresh database builders sharing backend fixture results. Legacy facade `set()` rejects loaded services, including null, while DI factories remain separate; direct harness vectors verify remove/reload of declared db/logger collaborators and coherent public access. Active Record recordings verify exact bound predicates, joins, limits, serialized payloads, and site identity; pagination verifies its total-row factory argument. Normal execution loads shipped files/archives; only coverage mode loads hash-verified archive-member copies.

## Full required matrix

All runs use PHPUnit **9.6.38**, explicit Herd binaries, fresh unique report files, and the complete configured suite. No global Herd runtime, ini file, or service changed. The matrix runner returns nonzero while regressions remain red. Launcher, matrix, and coverage entrypoints verify the selected PHAR hash before every execution; a tampered-tool fixture proves the runtime is not invoked after a mismatch.

| PHP | Cases | Assertions | Errors | Failures | Warnings | Risky | Skipped | Incomplete |
|---|---:|---:|---:|---:|---:|---:|---:|---:|
| 7.4.33 | 541 | 1613 | 15 | 22 | 0 | 0 | 0 | 0 |
| 8.0.30 | 541 | 1593 | 16 | 22 | 0 | 0 | 0 | 0 |
| 8.1.33 | 541 | 1593 | 16 | 22 | 0 | 0 | 0 | 0 |
| 8.2.30 | 541 | 1535 | 24 | 32 | 0 | 0 | 0 | 0 |
| 8.3.28 | 541 | 1535 | 24 | 32 | 0 | 0 | 0 | 0 |
| 8.4.15 | 541 | 1535 | 24 | 32 | 0 | 0 | 0 | 0 |
| 8.5.0 | 541 | 1535 | 24 | 32 | 0 | 0 | 0 | 0 |

Latest full-matrix reports and exact per-case diagnostic output are under `tests/reports/matrix-DRVDOuNE` with an ignored `tests/reports/matrix-results.json` summary. No warnings, risky tests, skipped tests, or incomplete selections were hidden. PHP 7.4 and PHP 8.5 each execute every configured case.

## Production regressions and strict-diagnostic failures

- `autoload.php:33`: the namespace-prefix guard also handles sibling `FluxCapacitorExtra` classes and throws for them. The PHAR extraction stub's line 177 emits `@mkdir(): File exists` under strict diagnostics.
- `pi.low_title.php:122`: a site's pages array without `uris` warns. Line 409 passes nullable custom-field titles to `strlen()`; native null outputs also violate the string return contract on older PHP. Line 153 creates undeclared `return_data` on native instances (PHP >= 8.2).
- Archived `Base/Pi.php:29`: static `info()` uses `$this` and throws. Base and conduit native constructors create undeclared properties on PHP >= 8.2.
- Archived `Base/Ext.php:183`: the legacy registration wrapper resets the caller's hook, priority, and enabled arguments. `Base/Upd.php:62` includes an absent optional extension under `@`.
- Archived EE2 `getBaseURL():47` supplies invalid operator `=<` to `version_compare()`; it fails the 2.6 session path on PHP 7.4 and throws on PHP 8.0+. Archived EE3/4/5 `getNav():96` stores a Sidebar Header object instead of a page-title string when `active_map` is used; EE6 inherits EE5.
- Archived EE3/4/5 `overwriteEEClass():344` calls facade `set()` without removing the loaded service. EE6 inherits EE5; replacing loaded strings or null produces the facade's `RuntimeException`. The fixture matches `Legacy/Facade.php:87–97,145`; eight red regressions retain the defect. EE2 direct replacement succeeds.
- Archived `GlobalFieldSettings.php:19,31`: unknown keys and malformed serialization warn under `@`. Archived `Conduit/McpNav.php:7` includes an absent loose file under `@`, blocking native loading and its methods.
- Archived `StaticCache.php:18`: empty values are returned as `false`, losing 0, "0", empty strings/arrays, and null. Persistent cache reads pass composite arrays as already-processed offsets, fail to reuse the request cache, can return another key's value when the caller's key equals its SHA-1, and ignore the documented processed-key flag in `has()`.
- Archived `PersistentCache.php:181,196,215`: malformed cache serialization and cache paths occupied by files emit diagnostics instead of clean misses/failures. Required strict diagnostics stop before some subsequent logger branches.

EE2 persistent cache delete/clear removes session data without deleting the persistent store; stateful retrieval assertions keep both defects red.

Source failures remain assertions/errors, with no skipped source defects, expected deprecations, source patches, or diagnostic suppression. PHP >= 8.2 also fails the MCP save cases because the concrete Version constructor creates an undeclared property before performing the save.

## Isolation and remaining harness limits

Each test restores the caller EE global, request arrays, form-helper global, error reporting/handler, include path, and autoload stack; a nested-context contract test verifies restoration. Class declarations, method-local static maps, and PHAR's process-wide `interceptFileFuncs()` effect cannot be unloaded or reversed in the parent suite. Bootstrap/discovery loads are documented process-level state. Native loading/diagnostic cases run in fresh PHP processes, and algorithm subjects use unique cache keys. Full 541-case randomized runs on PHP 7.4 and 8.5 with seed `20261023` reproduce the ordered counts exactly; logs are `tests/reports/v4-random74.txt` and `v4-random85.txt`.

All native fixture children set both `sys_temp_dir` and `TMPDIR` to their unique owned directory. Extraction probes assert the actual `Extract_low_title::$temp` path and verify cleanup, including repeated runs. Review caught earlier probes falling back to shared `/tmp/pharextract/low_title`; that existing shared tree is preserved because ownership is not established.

Native subprocess readers are sequential and currently have no timeout; controlled probes have small outputs and finite shipped inputs. A nonblocking shared process helper remains a robustness gap, not a source failure or coverage completion claim. The raw collector initially exhausted its command-local 1 GB limit while serializing tooling graphs; the final runner installs an early filter and filters the persisted JSON to the complete shipped loose tree, physical PHAR, and all PHAR members. A guard rejects runs without an executed source function graph. The latest raw JSON contains 26 source files and no test/tooling paths; the declaration-only EE6 member remains in the 27-unit inventory. That failed artifact is not reused.

## Coverage, review, and publication

See `COVERAGE.md` for every source unit, every inventoried function/method, line measurements, actual block/exit measurements, unknown procedural metrics, and exact remaining line/exit locations. The seven loose plugin methods reach 100% executable lines; only the private field-map methods currently reach every reported opcode exit. Fixed nonempty loops, synthetic exits, native-context graphs, unreachable constants/platforms, and source exceptions prevent a full branch-completion claim.

Independent exact-diff review: first review identified five harness/reporting issues; follow-up review identified TMPDIR isolation, raw-scope reporting, successful extraction-probe metadata, and loaded-facade contract issues, now corrected; revised exact-diff approval is **pending**. Commit/push: **pending review**. No completed-file coverage milestone or full campaign completion is claimed. After review, publish the honest regression checkpoint to `origin/unit-tests-2026` and verify exact remote HEAD parity. Remaining work requires source fixes or additional compatible native-context instrumentation; production changes remain outside authorization.
