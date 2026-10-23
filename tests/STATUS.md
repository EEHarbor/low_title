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
./tests/scripts/run-native-coverage.sh
# Coverage commands print unique REPORT_DIR paths.
# PHPUnit line/branch runs verify their pinned tool PHAR before execution.
# Substitute those paths below.
python3 tests/scripts/summarize-branches.py <branch-report-dir>/raw.json tests/reports/branches.json
python3 tests/scripts/snapshot-coverage.py <line-report-dir>/clover.xml tests/reports/branches.json tests/reports/coverage-snapshot.json
python3 tests/scripts/render-coverage.py tests/reports/coverage-snapshot.json tests/COVERAGE.md
python3 tests/scripts/render-native-coverage.py <native-report-dir> tests/NATIVE_COVERAGE.md

/Users/tomjaeger/.codex/skills/ee-addon-phpunit-coverage-writer/scripts/addon_method_coverage_report.py \
  --repo-root "$PWD" --source-root low_title --coverage-clover <line-report-dir>/clover.xml --reuse-clover --json
/Users/tomjaeger/.codex/skills/ee-addon-phpunit-coverage-writer/scripts/addon_method_coverage_report.py \
  --repo-root "$PWD" --source-root tests/.source --coverage-clover <line-report-dir>/clover.xml --reuse-clover --json
```

E_ALL diagnostics from shipped source and test support throw even under `@`. A fresh subprocess deliberately triggers an `@`-suppressed user deprecation to verify enforcement. PHPUnit's own suppressed filesystem probes retain their internal handling; unsuppressed tooling diagnostics still fail. Every PHPUnit native child uses its parent's explicit PHP binary and E_ALL, with coverage and measurement-source substitution disabled so shipped loading/compilation is tested separately. The standalone native-coverage command instruments its separate source contexts explicitly.

Algorithm subjects declare missing source properties to permit independent behavior tests while native unmodified classes separately fail for their dynamic-property diagnostics. No source diagnostic is made an expected success. Reflection accessibility calls are limited to PHP < 8.1; PHP 8.5 does not execute the deprecated API.

EE collaborator shapes were checked against `/Users/tomjaeger/Sites/ee_repo_2`: `EE_DB_result::row()` including MySQL nulls and empty arrays, template missing-parameter defaults, fieldtype content IDs, terminating redirects, and Sidebar Header objects. Direct harness contract vectors verify numeric/current result rows, nulls, POST-first input, the strict XSS flag, template yes/no aliases with the url_title exception, and fresh database builders sharing backend fixture results. Legacy facade `set()` rejects loaded services, including null, while DI factories remain separate; direct harness vectors verify remove/reload of declared db/logger collaborators and coherent public access. Active Record recordings verify exact bound predicates, joins, limits, serialized payloads, and site identity; pagination verifies its total-row factory argument and requires a CP URL object at render time. The bounded typed collaborator matches `CP/Pagination::render(Url)`; direct read-only probes of actual EE 3.5, 4.1, 5.3, 6, and current implementations on all seven runtimes reject strings, null, arrays, and unrelated objects before rendering. Those 35 probes establish the argument contract, not full EE pagination integration. Normal execution loads shipped files/archives; only coverage mode loads hash-verified archive-member copies.

## Full required matrix

All runs use PHPUnit **9.6.38**, explicit Herd binaries, fresh unique report files, and the complete configured suite. No global Herd runtime, ini file, or service changed. The matrix runner returns nonzero while regressions remain red. Launcher, matrix, and coverage entrypoints verify the selected PHAR hash before every execution; a tampered-tool fixture proves the runtime is not invoked after a mismatch.

| PHP | Cases | Assertions | Errors | Failures | Warnings | Risky | Skipped | Incomplete |
|---|---:|---:|---:|---:|---:|---:|---:|---:|
| 7.4.33 | 625 | 2032 | 17 | 24 | 0 | 0 | 0 | 0 |
| 8.0.30 | 625 | 2012 | 18 | 24 | 0 | 0 | 0 | 0 |
| 8.1.33 | 625 | 2012 | 18 | 24 | 0 | 0 | 0 | 0 |
| 8.2.30 | 625 | 1945 | 26 | 37 | 0 | 0 | 0 | 0 |
| 8.3.28 | 625 | 1945 | 26 | 37 | 0 | 0 | 0 | 0 |
| 8.4.15 | 625 | 1945 | 26 | 37 | 0 | 0 | 0 | 0 |
| 8.5.0 | 625 | 1945 | 26 | 37 | 0 | 0 | 0 | 0 |

Latest full-matrix reports and exact per-case diagnostic output are under `tests/reports/matrix-LVv6nZLA` with an ignored `tests/reports/matrix-results.json` summary. No warnings, risky tests, skipped tests, or incomplete selections were hidden. PHP 7.4 and PHP 8.5 each execute every configured case.

## Production regressions and strict-diagnostic failures

- `autoload.php:33`: the namespace-prefix guard also handles sibling `FluxCapacitorExtra` classes and throws for them. The PHAR extraction stub's line 177 emits `@mkdir(): File exists` under strict diagnostics. Its size-error message reads undefined `$stat` at line 262; nested cleanup attempts `@rmdir()` before recursion at line 278, and hidden files skipped by `glob('*')` make the final line-285 removal warn.
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

Each test restores the caller EE global, request arrays, form-helper global, error reporting/handler, include path, and autoload stack; a nested-context contract test verifies restoration. Class declarations, method-local static maps, and PHAR's process-wide `interceptFileFuncs()` effect cannot be unloaded or reversed in the parent suite. Bootstrap/discovery loads are documented process-level state. Native loading/diagnostic cases run in fresh PHP processes, and algorithm subjects use unique cache keys. Full 625-case randomized runs on PHP 7.4 and 8.5 with seed `20261023` reproduce the ordered counts exactly; logs are `tests/reports/v6-random74.txt` and `v6-random85.txt`.

All native fixture children set both `sys_temp_dir` and `TMPDIR` to their unique owned directory. Extraction probes assert the actual `Extract_low_title::$temp` path and verify cleanup, including repeated runs. Review caught earlier probes falling back to shared `/tmp/pharextract/low_title`; that existing shared tree is preserved because ownership is not established.

PHPUnit native, autoload, installation, and launcher probes use `Support/Process.php`: independent file-backed stdout/stderr, a default two-second monotonic `hrtime()` deadline, a file-confirmed owned process group, TERM/KILL escalation, child reaping, and finally-closed owned temporary streams. Controls retain one MiB on each output stream and bound seven-second stalls, including SIGTERM-ignoring children, while checking that the owned PID is gone. POSIX/pcntl support was verified on all seven explicit Herd runtimes. The standalone native-coverage shell command runs its finite controlled contexts directly. The raw collector initially exhausted its command-local 1 GB limit while serializing tooling graphs; the final runner installs an early filter and filters the persisted JSON to the complete shipped loose tree, physical PHAR, and all PHAR members. A guard rejects runs without an executed source function graph. The latest raw JSON contains 26 source files and no test/tooling paths; the declaration-only EE6 member remains in the 27-unit inventory. That failed artifact is not reused.

## Coverage, review, and publication

See `COVERAGE.md` for every source unit, every inventoried function/method, line measurements, actual block/exit measurements, unknown procedural metrics, and exact remaining line/exit locations. The seven loose plugin methods reach 100% executable lines; only the private field-map methods currently reach every reported opcode exit. Fixed nonempty loops, synthetic exits, native-context graphs, unreachable constants/platforms, and source exceptions prevent a full branch-completion claim.

Independent exact-diff review approved the first 541-case source-red checkpoint with no remaining actionable findings. The approved full-index patch SHA-256 is `ea7f45de0007dfcf0a51f4fbf816b149e4fcc322fc7dc4a35ae39ed1c1700f31`. Review independently reproduced all seven full suites, randomized PHP 7.4/8.5 runs with seed `20261026`, repeated owned-directory extraction cleanup, native EE-facade replacement failure, and every saved per-method line/block/exit count across all 27 inventory units. Initial and follow-up findings about fixture fidelity, persistent deletion, stub graph mapping, tool verification, caller restoration, TMPDIR isolation, raw-scope filtering, successful probe metadata, and loaded-facade contracts were corrected before approval. The first checkpoint documented subprocess timeout, irreversible process state, integration, and branch gaps. The current follow-up resolves the PHPUnit native timeout/output issue; process-state, integration, and branch gaps remain.

Test checkpoint `11a745b8cb4eceed87ad85cfab1a58a9fdb10c7c` was committed and pushed to `origin/unit-tests-2026`. Fresh `git ls-remote` matched that exact SHA, local/remote divergence was `0 0`, and the checkout was clean. Production diff against the pinned baseline was empty and the PHAR hash remained unchanged. This status record is a documentation-only follow-up on the same branch. No completed-file coverage milestone or full campaign completion is claimed. Further feasible work includes the legacy get_instance fallback and fieldtype system/app/installer loading paths, alongside source and instrumentation blockers. Production changes remain outside authorization.

## Reviewed follow-up checkpoint

The 589-case follow-up adds empty extension hooks, inactive/empty navigation, default alert modifiers, empty legacy settings/cache state, persistent-cache empty and subdirectory-preservation outcomes, development update-pixel endpoints, optional Pro/site-license values, and EE2.5 slash/navigation contracts. Native probes exercise CP constructors/menu output, all EE2–7 bridge selections and configured theme URLs, the archive fallback loader, and old/new updater membership tables. Three additional PHP >= 8.2 failures are native CP constructor dynamic-property diagnostics; those source regressions remain red.

Separate native measurements are in `NATIVE_COVERAGE.md`, generated from `tests/reports/native-coverage-YV551OPP`. All 14 PHP 7.4 contexts exit successfully. Their raw graphs remain separate from parent Clover and one another. Tab and fieldtype CP constructor lines and all archive fallback-loader lines execute in these contexts, with uncovered exits still reported. Parent `Version::check()` improves to 29/30 lines and 16/23 exits; its immutable license-constant path remains unexecuted. Persistent `clear()` reaches all eight blocks and 9/11 exits, while synthetic exits remain uncovered.

The raw collector now records Reflection constructor source ranges. Xdebug 3.1.6 incorrectly assigns all conditional `Bridge/EE::__construct` graphs to its first declaration (42–45), even when the runtime method is at 50/58/66/74/82. The raw and normalized graphs are preserved, but source-range mismatches have unknown per-method block/exit metrics. The parent and native renderers do not assign those counters to a different declaration.

Root review of the first follow-up packet identified two harness defects before publication: blocking sequential native output and the DI database factory depending on the facade property. The replacement packet uses the bounded shared runner above. The database factory captures its original backend independently; direct controls verify that facade removal/replacement leaves fresh DI builders attached to that original backend. The updater native oracle now checks the entire ordered mutation sequence and ownership predicates. These are harness fixes; source regressions remain visible.

Root independently approved the exact 23-path replacement patch SHA-256 `5cae23a3225d16a56d6ff0eff47f6ed9e07bead230437f8623a5358eeb6bb392`, based on `80e115b456723ff5afb94409fe156200fd4f53f3`, with no remaining actionable findings. Independent replays matched all seven 589-case suites and all 14 native contexts, source inventory, bounded flood/stall controls, independent database factories, and updater predicates. Commit `6f66f2754f5522f7e66b3f155d75e28538850e3b` contains that exact reviewed patch and was pushed to `origin/unit-tests-2026`; fresh live origin verification matched the SHA, divergence was `0 0`, and production diff remained empty. This is an incomplete source-red checkpoint. Feasible legacy/fieldtype loading paths and PHAR extraction helper coverage remain for subsequent reviewed work; inherited pagination collaborator fidelity and monotonic process deadlines will also be checked separately.


## Reviewed PHAR helper checkpoint

The 625-case checkpoint adds direct shipped-stub coverage for the complete real manifest and all 22 member payloads, one-member and metadata/alias manifests, compression-mask accumulation, plain/gzip/bzip extraction at lengths 0, 1, 8191, 8192, 8193, 16384, and 16385, exact stream consumption, Unix TMPDIR preference/fallback, and empty/flat/nested/hidden cleanup. Owned cleanup runs in `finally`, restores the caller directory and environment, and verifies the unique tree is gone even when source raises a diagnostic. The source is unchanged. Four newly red cases expose two cleanup diagnostics and the two malformed-size/truncated-stream diagnostics above; all previous issue identities remain present.

Parent physical-stub lines improve from 7/154 to 55/154. `_unpack()` measures 20/20 executable lines, 4/4 blocks, and 4/4 actual exits. `extractFile()` reaches 15/18 lines and 15/19 exits; `_removeTmpFiles()` reaches 9/10 lines and 11/18 exits. The Unix temporary-directory paths execute, while Windows-only paths remain unavailable. Full extraction `go()` remains unexecuted in the parent and blocked by the separate native extraction regression; source warnings prevent the recursive-cleanup call. This is neither a completed source-file milestone nor full campaign completion.

Fresh parent line and raw captures are `tests/reports/coverage-qSGCBBeM/clover.xml` and `tests/reports/coverage-cXoiBiI7/raw.json`; normalized reports are `v6-branches74.json` and `v6-coverage-snapshot.json`. The raw collector retains 26 shipped source files; the normalized inventory report retains all 27 units. Native coverage at `tests/reports/native-coverage-rmoIaG0l` retains 17 independent contexts: the prior 14 and checksum rejection exit 0; malformed size and truncated input exit 1 with the source diagnostic. The checksum's intentional diagnostic with legacy exit 0 is asserted directly. Native exit/error branches are never merged into parent coverage. Thirty PHP support/test/probe files lint on PHP 7.4/8.5, Python/Bash syntax checks pass, and full randomized 7.4/8.5 runs retain the ordered counts and issue identities.

This checkpoint also corrects inherited pagination test inputs to CP URL objects, adds four direct invalid-type collaborator controls, and switches runner/deadline-control clocks to monotonic `hrtime()`. Independent review approved exact 12-path patch SHA-256 `b2622080bd61859ea5d5fb24944c792a0f14642ebd94e98aa54f77217da588f8` based on `77f10fd10af62bd9165fd86c94851dcde8f6e62a`, after correcting a stale generated coverage constraint. The reviewer independently reproduced all seven complete suites and exact issue identities, all 17 native contexts, all 245 measured method graphs and all 27 units of line data, and all 35 actual pagination typeguard probes. No actionable bounded-diff findings remain. Commit `d8f90959caeb8463ee265b18c32bac5785738d35` contains that reviewed patch and was pushed to `origin/unit-tests-2026`; fresh live origin matched the SHA, divergence was `0 0`, and production diff remained empty. Legacy `get_instance()` fallback and native fieldtype system/app/installer layouts remain feasible subsequent work; private probes have already identified an installer-ancestor path replacement regression. No production repair is authorized.
