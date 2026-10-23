# Administrative evidence handoff

Status: **INCOMPLETE**. This index uses existing independently reviewed reports only. It records no new PHP/test/probe/extraction/observation execution and makes no source-closure or compatibility claim.

The published 690-case code is `bea9b04212928a15c6065cb0d2bef61291a85b61`, approved as exact patch `ba346c9493afb00a10c04b94f479ef4e8dc5dfffeb19b0d5c3a8017bfe712795`. The pre-reviewed publication record is `3f6298135351f6cfd0e4e6ac188013bdff73ef95`; [STATUS.md](STATUS.md) is unchanged by this handoff. The production baseline remains `e3e13533ab0e67d13cdd16bedc0e377fa13910f0`, on `unit-tests-2026` with publication only to origin.

The original PHAR remains SHA-256 `79a7b868dcc5cf133984eb6c1a2f1f5942b1206b7b6661424bc6fd5142227d81`; [source-inventory.json](source-inventory.json) remains `930fa220ba6172a882d6f88ecdf0798eaf613d14e1bd69f41eec67b7e4db2d18`. All 27 source units and 266 inventoried functions/methods/closures remain represented, including procedural, archive, magic and abstract declarations.

## Reviewed evidence

[evidence-index.json](evidence-index.json) pins each existing artifact by exact logical path, size and SHA-256. It preserves the complete parent coverage snapshot, all per-method uncovered lines/exits and null metrics, procedural graphs, conditional attribution mismatches and the existing remaining-constraint text. It also indexes every raw/normalized/stdout/stderr/status artifact for the 59 separate PHP 7.4 and 59 separate PHP 8.2 native contexts. Their raw `out`/`out_hit` data is retained in the referenced files; no graph is merged or relabelled complete.

The existing complete seven-runtime evidence is `tests/reports/matrix-41B1Mipk`, with complete randomized endpoint runs at seed `20261023`, individual-file endpoint reports, original-source raw Xdebug, byte-verified-copy Clover and bounded actual-core schema/loader comparisons. The counts below are copied from those reviewed reports, not rerun results. Warnings/risky/skipped/incomplete are zero in every row.

| PHP | Cases | Assertions | Errors | Failures |
|---|---:|---:|---:|---:|
| 7.4.33 | 690 | 2993 | 17 | 35 |
| 8.0.30 | 690 | 3079 | 18 | 36 |
| 8.1.33 | 690 | 3078 | 18 | 36 |
| 8.2.30 | 690 | 3008 | 26 | 50 |
| 8.3.28 | 690 | 3008 | 26 | 50 |
| 8.4.15 | 690 | 3008 | 26 | 50 |
| 8.5.0 | 690 | 3008 | 26 | 50 |

Ignored reports and private proof files remain local evidence. The JSON index distinguishes them from Git-tracked documents and records their hashes; a missing artifact is unavailable evidence, never a passing or complete result. Original commands remain in the reviewed packet for provenance only and are not instructions to rerun held work.

## Remaining scope

[COVERAGE.md](COVERAGE.md), [NATIVE_COVERAGE.md](NATIVE_COVERAGE.md), [NATIVE_COVERAGE82.md](NATIVE_COVERAGE82.md) and the unchanged full [STATUS.md](STATUS.md) retain the source barriers and integration boundaries. The JSON preserves all 266 parent method/declaration rows and all 27 unit rows without dropping unknowns. Its existing snapshot labels are measurement information: 103 `measured target`, 137 `incomplete` and 26 `not executable` labels do not establish campaign closure.

Source regressions remain red. Basic-block hits are not complete decision coverage; synthetic terminal exits, fixed-input loop/short-circuit edges and conditional constructor attribution remain explicit. Native contexts and runtimes remain separate. Immutable constants/platform/archive prerequisites, process-global PHAR/class/autoload/static state, the standalone native-shell deadline gap, successful-schema-only adapters and bounded EE/database/model/rendering integration remain incomplete. No method, file or whole source scope is newly closed by this index.

## Held private work and handles

The coordinator-held PHAR extraction/diagnostic-observation experiment remains **HELD / INCOMPLETE**, without authorization to retry. It is not described as an automatic tool-approval rejection. The private unapproved prototype at `/private/tmp/low-title-observation-plan-gb0s_f3k` is preserved; the JSON records its file paths/hashes separately, with no reviewed coverage credit. Superseded private development trees are also identified and must not be overlaid on the published code. No prototype code or output is copied into this repository as executable content or approved coverage.

The read-only administrative check at `2026-10-23 22:05:54 UTC` found no matching owned PHP test/probe/coverage processes. All known runtime execution handles had completed, no PHP server was started, and the prototype's owned extraction directories were already removed. These are point-in-time administrative facts, not runtime revalidation. This documentation-only handoff leaves the held work untouched; unrelated ordinary testing requires a separate coordinator assignment.
