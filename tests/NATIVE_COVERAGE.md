# Native coverage contexts

Production baseline: `e3e13533ab0e67d13cdd16bedc0e377fa13910f0`. These fresh processes load the shipped PHAR with explicit EE versions, request contexts, and strict E_ALL diagnostics. Uninstall behavior uses a declared-property subject; unmodified constructors retain separate compatibility regressions. Each context remains independent of the parent suite and every other native graph. No counts below are added to parent coverage or used to close a source unit.

| Context | REQ / EE | PHP / Xdebug | Process exit | Method | Lines hit / executable | Blocks hit / total | Exits hit / total | Uncovered lines |
|---|---|---|---:|---|---:|---:|---:|---|
| `construct-Base-Mcp` | CP / 7.5.0 | 7.4.33 / 3.1.6 | 0 | `Base/Mcp::__construct` | 4/5 | 2/3 | 1/4 | 32 |
| `construct-Base-Mcp` | CP / 7.5.0 | 7.4.33 / 3.1.6 | 0 | `Base/Mcp::canIncludeNav` | 4/6 | 6/8 | 5/11 | 185, 188 |
| `construct-Base-Tab` | CP / 7.5.0 | 7.4.33 / 3.1.6 | 0 | `Base/Tab::__construct` | 6/6 | 5/5 | 4/7 | none |
| `construct-Base-Tab` | CP / 7.5.0 | 7.4.33 / 3.1.6 | 0 | `Conduit/Version::check` | 25/29 | 9/16 | 8/23 | 131, 167, 169, 180 |
| `construct-Base-Ft` | CP / 7.5.0 | 7.4.33 / 3.1.6 | 0 | `Base/Ft::__construct` | 7/7 | 5/5 | 4/7 | none |
| `construct-Base-Ft` | CP / 7.5.0 | 7.4.33 / 3.1.6 | 0 | `Conduit/Version::check` | 25/29 | 9/16 | 8/23 | 131, 167, 169, 180 |
| `extension-menu` | CP / 7.5.0 | 7.4.33 / 3.1.6 | 0 | `Base/Ext::cpCustomMenu` | 3/4 | 2/3 | 1/4 | 149 |
| `archive-index` | PAGE / 7.5.0 | 7.4.33 / 3.1.6 | 0 | `@index::{closure}` | 12/12 | 5/5 | 5/7 | none |
| `updater-ee2` | PAGE / 2.5.0 | 7.4.33 / 3.1.6 | 0 | `Base/Upd::uninstall` | 16/18 | 4/6 | 3/7 | 120, 121 |
| `updater-ee5` | PAGE / 5.5.0 | 7.4.33 / 3.1.6 | 0 | `Base/Upd::uninstall` | 18/18 | 5/6 | 4/7 | none |
| `updater-ee6` | PAGE / 6.5.0 | 7.4.33 / 3.1.6 | 0 | `Base/Upd::uninstall` | 18/18 | 5/6 | 4/7 | none |
| `extract-checksum` | PAGE / 7.5.0 | 7.4.33 / 3.1.6 | 0 | `@stub::extractFile` | 11/18 | 9/14 | 9/19 | 249, 250, 255, 257, 261, 262, 269 |
| `extract-size` | PAGE / 7.5.0 | 7.4.33 / 3.1.6 | 1 | `@stub::extractFile` | 11/18 | 8/14 | 8/19 | 249, 250, 255, 257, 265, 266, 269 |
| `extract-truncated` | PAGE / 7.5.0 | 7.4.33 / 3.1.6 | 1 | `@stub::extractFile` | 11/18 | 8/14 | 8/19 | 249, 250, 255, 257, 265, 266, 269 |
| `bridge-ee2` | PAGE / 2.5.0 | 7.4.33 / 3.1.6 | 0 | `Bridge/EE::__construct` | 2/2 | 1/1 | 0/1 | none |
| `bridge-ee2` | PAGE / 2.5.0 | 7.4.33 / 3.1.6 | 0 | `Bridge/EE2::getAddonThemesDir` | 2/2 | 3/4 | 2/4 | none |
| `bridge-ee3` | PAGE / 3.5.0 | 7.4.33 / 3.1.6 | 0 | `Bridge/EE::__construct` | 2/2 | unknown | unknown | none |
| `bridge-ee3` | PAGE / 3.5.0 | 7.4.33 / 3.1.6 | 0 | `Bridge/EE3::getAddonThemesDir` | 2/2 | 3/4 | 2/4 | none |
| `bridge-ee4` | PAGE / 4.3.0 | 7.4.33 / 3.1.6 | 0 | `Bridge/EE::__construct` | 2/2 | unknown | unknown | none |
| `bridge-ee4` | PAGE / 4.3.0 | 7.4.33 / 3.1.6 | 0 | `Bridge/EE4::getAddonThemesDir` | 2/2 | 3/4 | 2/4 | none |
| `bridge-ee5` | PAGE / 5.5.0 | 7.4.33 / 3.1.6 | 0 | `Bridge/EE::__construct` | 2/2 | unknown | unknown | none |
| `bridge-ee5` | PAGE / 5.5.0 | 7.4.33 / 3.1.6 | 0 | `Bridge/EE5::getAddonThemesDir` | 2/2 | 3/4 | 2/4 | none |
| `bridge-ee6` | PAGE / 6.5.0 | 7.4.33 / 3.1.6 | 0 | `Bridge/EE::__construct` | 2/2 | unknown | unknown | none |
| `bridge-ee6` | PAGE / 6.5.0 | 7.4.33 / 3.1.6 | 0 | `Bridge/EE5::getAddonThemesDir` | 2/2 | 3/4 | 2/4 | none |
| `bridge-ee7` | PAGE / 7.5.0 | 7.4.33 / 3.1.6 | 0 | `Bridge/EE::__construct` | 2/2 | unknown | unknown | none |
| `bridge-ee7` | PAGE / 7.5.0 | 7.4.33 / 3.1.6 | 0 | `Bridge/EE5::getAddonThemesDir` | 2/2 | 3/4 | 2/4 | none |
| `loading-legacy-config` | PAGE / 2.5.0 | 7.4.33 / 3.1.6 | 0 | `Bridge/EE::ee` | 2/2 | 1/1 | 0/0 | none |
| `loading-legacy-config` | PAGE / 2.5.0 | 7.4.33 / 3.1.6 | 0 | `Bridge/EE::__construct` | 2/2 | 1/1 | 0/1 | none |
| `loading-legacy-defined` | PAGE / 2.5.0 | 7.4.33 / 3.1.6 | 0 | `Bridge/EE::ee` | 2/2 | 1/1 | 0/0 | none |
| `loading-legacy-defined` | PAGE / 2.5.0 | 7.4.33 / 3.1.6 | 0 | `Bridge/EE::__construct` | 2/2 | 1/1 | 0/1 | none |
| `loading-fieldtype-system` | PAGE / 7.5.0 | 7.4.33 / 3.1.6 | 0 | `Base/Ft::{main}` | 7/13 | 5/12 | 4/18 | 10, 11, 12, 13, 15, 16 |
| `loading-fieldtype-app` | PAGE / 7.5.0 | 7.4.33 / 3.1.6 | 0 | `Base/Ft::{main}` | 8/13 | 6/12 | 5/18 | 9, 12, 13, 15, 16 |
| `loading-fieldtype-priority` | PAGE / 7.5.0 | 7.4.33 / 3.1.6 | 0 | `Base/Ft::{main}` | 7/13 | 5/12 | 4/18 | 10, 11, 12, 13, 15, 16 |
| `loading-fieldtype-defined-version` | PAGE / 7.5.0 | 7.4.33 / 3.1.6 | 0 | `Base/Ft::{main}` | 6/13 | 5/12 | 4/18 | 9, 12, 13, 15, 16, 21, 22 |
| `loading-fieldtype-installer` | PAGE / 7.5.0 | 7.4.33 / 3.1.6 | 0 | `Base/Ft::{main}` | 11/13 | 10/12 | 9/18 | 9, 11 |
| `loading-fieldtype-installer-ancestor` | PAGE / 7.5.0 | 7.4.33 / 3.1.6 | 1 | `Base/Ft::{main}` | 9/13 | 9/12 | 8/18 | 9, 11, 16, 157 |
| `loading-fieldtype-predeclared` | PAGE / 7.5.0 | 7.4.33 / 3.1.6 | 0 | `Base/Ft::{main}` | 7/12 | 8/12 | 7/18 | 9, 11, 13, 15, 16 |

Raw JSON, retained out/out_hit graphs, stdout/stderr, exit status, and the generated summary are stored in the unique native report directory. The checksum rejection intentionally terminates with its diagnostic and legacy exit status 0. Size and truncated-stream contexts exit 1 because the shipped size-error expression reads undefined $stat at stub line 262; strict diagnostics remain visible. Xdebug 3.1.6 attributes every conditional Bridge/EE constructor graph to its first declaration; Reflection identifies the actual runtime method. Mismatched graph ranges remain preserved in the raw/normalized report and have unknown per-method branch metrics above. Uninstrumented native contexts remain unknown. The absent optional add-on navigation class keeps its successful-inclusion paths unavailable; source diagnostics still stop native constructors on PHP >= 8.2 in the complete matrix.

Loading probes omit bootstrap-owned `ee()` and fieldtype parent declarations. The legacy contexts use EE2.5 and verify the shipped `get_instance()` fallback plus configured/predefined version precedence. Fieldtype paths receive a byte-identical copy of the existing bounded `Support/Fieldtype.php` contract; these are native add-on loader checks, not full EE-core integration. Their procedural rows select source lines outside inventoried method ranges. Xdebug zero-hit procedural graphs alongside executed lines remain unknown branch metrics, with raw counters preserved. The installer-ancestor case exits 1 because global replacement at Ft line 13 also changes the ancestor directory, preventing the parent load at line 35. Every context removes its unique fixture files before reporting.
