# Native coverage contexts

Production baseline: `e3e13533ab0e67d13cdd16bedc0e377fa13910f0`. These fresh processes load the shipped PHAR with explicit EE versions, request contexts, and strict E_ALL diagnostics. Uninstall behavior uses a declared-property subject; unmodified constructors retain separate compatibility regressions. Each context remains independent of the parent suite and every other native graph. No counts below are added to parent coverage or used to close a source unit.

| Context | REQ / EE | PHP / Xdebug | Process exit | Method | Lines hit / executable | Blocks hit / total | Exits hit / total | Uncovered lines |
|---|---|---|---:|---|---:|---:|---:|---|
| `construct-Base-Mcp` | CP / 7.5.0 | 8.2.30 / 3.5.3 | 1 | `Base/Mcp::__construct` | 2/5 | 1/3 | 0/4 | 31, 32, 34 |
| `construct-Base-Mcp` | CP / 7.5.0 | 8.2.30 / 3.5.3 | 1 | `Base/Mcp::canIncludeNav` | 0/6 | 0/8 | 0/11 | 183, 184, 185, 187, 188, 193 |
| `construct-Base-Tab` | CP / 7.5.0 | 8.2.30 / 3.5.3 | 1 | `Base/Tab::__construct` | 2/6 | 1/5 | 0/7 | 26, 27, 28, 30 |
| `construct-Base-Tab` | CP / 7.5.0 | 8.2.30 / 3.5.3 | 1 | `Conduit/Version::check` | unknown | unknown | unknown | unknown |
| `construct-Base-Ft` | CP / 7.5.0 | 8.2.30 / 3.5.3 | 1 | `Base/Ft::__construct` | 2/7 | 1/5 | 0/7 | 50, 52, 53, 54, 56 |
| `construct-Base-Ft` | CP / 7.5.0 | 8.2.30 / 3.5.3 | 1 | `Conduit/Version::check` | unknown | unknown | unknown | unknown |
| `extension-menu` | CP / 7.5.0 | 8.2.30 / 3.5.3 | 0 | `Base/Ext::cpCustomMenu` | 3/4 | 2/3 | 1/4 | 149 |
| `archive-index` | PAGE / 7.5.0 | 8.2.30 / 3.5.3 | 0 | `@index::{closure}` | 12/12 | 5/5 | 5/7 | none |
| `updater-ee2` | PAGE / 2.5.0 | 8.2.30 / 3.5.3 | 0 | `Base/Upd::uninstall` | 16/18 | 4/6 | 3/7 | 120, 121 |
| `updater-ee5` | PAGE / 5.5.0 | 8.2.30 / 3.5.3 | 0 | `Base/Upd::uninstall` | 18/18 | 5/6 | 4/7 | none |
| `updater-ee6` | PAGE / 6.5.0 | 8.2.30 / 3.5.3 | 0 | `Base/Upd::uninstall` | 18/18 | 5/6 | 4/7 | none |
| `extract-checksum` | PAGE / 7.5.0 | 8.2.30 / 3.5.3 | 0 | `@stub::extractFile` | 11/18 | 9/14 | 9/19 | 249, 250, 255, 257, 261, 262, 269 |
| `extract-size` | PAGE / 7.5.0 | 8.2.30 / 3.5.3 | 1 | `@stub::extractFile` | 11/18 | 8/14 | 8/19 | 249, 250, 255, 257, 265, 266, 269 |
| `extract-truncated` | PAGE / 7.5.0 | 8.2.30 / 3.5.3 | 1 | `@stub::extractFile` | 11/18 | 8/14 | 8/19 | 249, 250, 255, 257, 265, 266, 269 |
| `bridge-ee2` | PAGE / 2.5.0 | 8.2.30 / 3.5.3 | 0 | `Bridge/EE::__construct` | 2/2 | 1/1 | 0/1 | none |
| `bridge-ee2` | PAGE / 2.5.0 | 8.2.30 / 3.5.3 | 0 | `Bridge/EE2::getAddonThemesDir` | 2/2 | 3/4 | 2/4 | none |
| `bridge-ee3` | PAGE / 3.5.0 | 8.2.30 / 3.5.3 | 0 | `Bridge/EE::__construct` | 2/2 | unknown | unknown | none |
| `bridge-ee3` | PAGE / 3.5.0 | 8.2.30 / 3.5.3 | 0 | `Bridge/EE3::getAddonThemesDir` | 2/2 | 3/4 | 2/4 | none |
| `bridge-ee4` | PAGE / 4.3.0 | 8.2.30 / 3.5.3 | 0 | `Bridge/EE::__construct` | 2/2 | unknown | unknown | none |
| `bridge-ee4` | PAGE / 4.3.0 | 8.2.30 / 3.5.3 | 0 | `Bridge/EE4::getAddonThemesDir` | 2/2 | 3/4 | 2/4 | none |
| `bridge-ee5` | PAGE / 5.5.0 | 8.2.30 / 3.5.3 | 0 | `Bridge/EE::__construct` | 2/2 | unknown | unknown | none |
| `bridge-ee5` | PAGE / 5.5.0 | 8.2.30 / 3.5.3 | 0 | `Bridge/EE5::getAddonThemesDir` | 2/2 | 3/4 | 2/4 | none |
| `bridge-ee6` | PAGE / 6.5.0 | 8.2.30 / 3.5.3 | 0 | `Bridge/EE::__construct` | 2/2 | unknown | unknown | none |
| `bridge-ee6` | PAGE / 6.5.0 | 8.2.30 / 3.5.3 | 0 | `Bridge/EE5::getAddonThemesDir` | 2/2 | 3/4 | 2/4 | none |
| `bridge-ee7` | PAGE / 7.5.0 | 8.2.30 / 3.5.3 | 0 | `Bridge/EE::__construct` | 2/2 | unknown | unknown | none |
| `bridge-ee7` | PAGE / 7.5.0 | 8.2.30 / 3.5.3 | 0 | `Bridge/EE5::getAddonThemesDir` | 2/2 | 3/4 | 2/4 | none |
| `loading-legacy-config` | PAGE / 2.5.0 | 8.2.30 / 3.5.3 | 0 | `Bridge/EE::ee` | 2/2 | 1/1 | 0/0 | none |
| `loading-legacy-config` | PAGE / 2.5.0 | 8.2.30 / 3.5.3 | 0 | `Bridge/EE::__construct` | 2/2 | 1/1 | 0/1 | none |
| `loading-legacy-defined` | PAGE / 2.5.0 | 8.2.30 / 3.5.3 | 0 | `Bridge/EE::ee` | 2/2 | 1/1 | 0/0 | none |
| `loading-legacy-defined` | PAGE / 2.5.0 | 8.2.30 / 3.5.3 | 0 | `Bridge/EE::__construct` | 2/2 | 1/1 | 0/1 | none |
| `loading-fieldtype-system` | PAGE / 7.5.0 | 8.2.30 / 3.5.3 | 0 | `Base/Ft::{main}` | 7/13 | 5/12 | 4/18 | 10, 11, 12, 13, 15, 16 |
| `loading-fieldtype-app` | PAGE / 7.5.0 | 8.2.30 / 3.5.3 | 0 | `Base/Ft::{main}` | 8/13 | 6/12 | 5/18 | 9, 12, 13, 15, 16 |
| `loading-fieldtype-priority` | PAGE / 7.5.0 | 8.2.30 / 3.5.3 | 0 | `Base/Ft::{main}` | 7/13 | 5/12 | 4/18 | 10, 11, 12, 13, 15, 16 |
| `loading-fieldtype-defined-version` | PAGE / 7.5.0 | 8.2.30 / 3.5.3 | 0 | `Base/Ft::{main}` | 6/13 | 5/12 | 4/18 | 9, 12, 13, 15, 16, 21, 22 |
| `loading-fieldtype-installer` | PAGE / 7.5.0 | 8.2.30 / 3.5.3 | 0 | `Base/Ft::{main}` | 11/13 | 10/12 | 9/18 | 9, 11 |
| `loading-fieldtype-installer-ancestor` | PAGE / 7.5.0 | 8.2.30 / 3.5.3 | 1 | `Base/Ft::{main}` | 9/13 | 9/12 | 8/18 | 9, 11, 16, 157 |
| `loading-fieldtype-predeclared` | PAGE / 7.5.0 | 8.2.30 / 3.5.3 | 0 | `Base/Ft::{main}` | 7/12 | 8/12 | 7/18 | 9, 11, 13, 15, 16 |
| `autoload-unrelated` | None / None | 8.2.30 / 3.5.3 | 0 | `@autoload::{closure}` | 9/30 | 2/31 | 1/44 | 39, 42, 43, 46, 48, 49, 51, 55, 57, 63, 66, 70, 73, 74, 76, 77, 81, 82, 84, 87, 90 |
| `autoload-sibling-namespace` | None / None | 8.2.30 / 3.5.3 | 1 | `@autoload::{closure}` | 19/30 | 9/31 | 8/44 | 35, 57, 63, 66, 74, 76, 77, 81, 82, 84, 90 |
| `autoload-missing-archive` | None / None | 8.2.30 / 3.5.3 | 0 | `@autoload::{closure}` | 13/30 | 3/31 | 2/44 | 35, 48, 49, 51, 55, 57, 63, 66, 70, 73, 74, 76, 77, 81, 82, 84, 87 |
| `autoload-missing-class` | None / None | 8.2.30 / 3.5.3 | 0 | `@autoload::{closure}` | 19/30 | 9/31 | 8/44 | 35, 57, 63, 66, 74, 76, 77, 81, 82, 84, 90 |
| `autoload-native` | None / None | 8.2.30 / 3.5.3 | 0 | `@autoload::{closure}` | 23/30 | 22/31 | 21/44 | 35, 57, 63, 66, 82, 87, 90 |
| `autoload-opcache` | None / None | 8.2.30 / 3.5.3 | 0 | `@autoload::{closure}` | 23/30 | 23/31 | 22/44 | 35, 57, 63, 66, 84, 87, 90 |
| `autoload-extraction` | None / None | 8.2.30 / 3.5.3 | 1 | `@autoload::{closure}` | 15/30 | 5/31 | 4/44 | 35, 49, 51, 55, 66, 70, 73, 74, 76, 77, 81, 82, 84, 87, 90 |
| `autoload-extraction` | None / None | 8.2.30 / 3.5.3 | 1 | `@stub::go` | 29/50 | 16/35 | 15/53 | 131, 132, 139, 141, 146, 148, 155, 156, 157, 158, 159, 161, 178, 180, 181, 183, 184, 189, 191, 192, 194 |
| `autoload-extraction` | None / None | 8.2.30 / 3.5.3 | 1 | `@stub::_unpack` | 20/20 | 4/4 | 4/4 | none |
| `autoload-extraction` | None / None | 8.2.30 / 3.5.3 | 1 | `@stub::tmpdir` | 3/9 | 3/14 | 2/19 | 199, 200, 202, 203, 205, 210 |
| `autoload-extraction` | None / None | 8.2.30 / 3.5.3 | 1 | `@stub::_removeTmpFiles` | 6/10 | 3/12 | 2/18 | 277, 278, 279, 280 |
| `autoload-no-opcache-api` | None / None | 8.2.30 / 3.5.3 | 0 | `@autoload::{closure}` | 22/30 | 20/31 | 19/44 | 35, 57, 63, 66, 77, 82, 87, 90 |
| `autoload-restricted-opcache-api` | None / None | 8.2.30 / 3.5.3 | 1 | `@autoload::{closure}` | 21/30 | 12/31 | 11/44 | 35, 57, 63, 66, 81, 82, 84, 87, 90 |
| `pagination-named-default` | PAGE / 7.5.0 | 8.2.30 / 3.5.3 | 0 | `Base/Mcp::paginateModel` | 17/18 | 11/12 | 10/16 | 145 |
| `pagination-named-default` | PAGE / 7.5.0 | 8.2.30 / 3.5.3 | 0 | `Bridge/EE5::moduleURL` | 2/2 | 1/1 | 0/0 | none |
| `pagination-named-default` | PAGE / 7.5.0 | 8.2.30 / 3.5.3 | 0 | `Bridge/EE5::pagination` | 8/8 | 5/5 | 4/6 | none |
| `pagination-global-explicit` | PAGE / 7.5.0 | 8.2.30 / 3.5.3 | 0 | `Base/Mcp::paginateModel` | 16/18 | 9/12 | 8/16 | 145, 153 |
| `pagination-global-explicit` | PAGE / 7.5.0 | 8.2.30 / 3.5.3 | 0 | `Bridge/EE5::moduleURL` | 0/2 | 0/1 | 0/0 | 133, 135 |
| `pagination-global-explicit` | PAGE / 7.5.0 | 8.2.30 / 3.5.3 | 0 | `Bridge/EE5::pagination` | 8/8 | 5/5 | 4/6 | none |
| `pagination-global-default` | PAGE / 7.5.0 | 8.2.30 / 3.5.3 | 1 | `Base/Mcp::paginateModel` | 15/18 | 9/12 | 8/16 | 145, 153, 172 |
| `pagination-global-default` | PAGE / 7.5.0 | 8.2.30 / 3.5.3 | 1 | `Bridge/EE5::moduleURL` | 0/2 | 0/1 | 0/0 | 133, 135 |
| `pagination-global-default` | PAGE / 7.5.0 | 8.2.30 / 3.5.3 | 1 | `Bridge/EE5::pagination` | 8/8 | 5/5 | 4/6 | none |
| `session-plain` | None / None | 8.2.30 / 3.5.3 | 1 | `@stub::go` | 33/50 | 21/35 | 20/53 | 131, 132, 139, 141, 146, 148, 157, 159, 178, 180, 181, 183, 184, 189, 191, 192, 194 |
| `session-plain` | None / None | 8.2.30 / 3.5.3 | 1 | `@stub::tmpdir` | 3/9 | 3/14 | 2/19 | 199, 200, 202, 203, 205, 210 |
| `session-depth` | None / None | 8.2.30 / 3.5.3 | 1 | `@stub::go` | 34/50 | 22/35 | 21/53 | 131, 132, 139, 141, 146, 148, 159, 178, 180, 181, 183, 184, 189, 191, 192, 194 |
| `session-depth` | None / None | 8.2.30 / 3.5.3 | 1 | `@stub::tmpdir` | 3/9 | 3/14 | 2/19 | 199, 200, 202, 203, 205, 210 |
| `session-mode` | None / None | 8.2.30 / 3.5.3 | 0 | `@stub::go` | 22/50 | 17/35 | 16/53 | 131, 132, 139, 141, 146, 148, 161, 164, 165, 166, 167, 168, 170, 171, 172, 173, 175, 176, 177, 178, 180, 181, 183, 184, 189, 191, 192, 194 |
| `session-mode` | None / None | 8.2.30 / 3.5.3 | 0 | `@stub::tmpdir` | 3/9 | 3/14 | 2/19 | 199, 200, 202, 203, 205, 210 |
| `session-tmp-file` | None / None | 8.2.30 / 3.5.3 | 1 | `@stub::go` | 21/50 | 13/35 | 12/53 | 131, 132, 139, 141, 146, 148, 155, 156, 157, 158, 159, 161, 168, 170, 171, 172, 173, 175, 176, 177, 178, 180, 181, 183, 184, 189, 191, 192, 194 |
| `session-tmp-file` | None / None | 8.2.30 / 3.5.3 | 1 | `@stub::tmpdir` | 3/9 | 3/14 | 2/19 | 199, 200, 202, 203, 205, 210 |
| `session-tmp-readonly` | None / None | 8.2.30 / 3.5.3 | 1 | `@stub::go` | 33/50 | 21/35 | 20/53 | 131, 132, 139, 141, 146, 148, 157, 159, 178, 180, 181, 183, 184, 189, 191, 192, 194 |
| `session-tmp-readonly` | None / None | 8.2.30 / 3.5.3 | 1 | `@stub::tmpdir` | 3/9 | 3/14 | 2/19 | 199, 200, 202, 203, 205, 210 |
| `session-missing` | None / None | 8.2.30 / 3.5.3 | 0 | `@stub::go` | 21/50 | 16/35 | 15/53 | 131, 132, 139, 141, 146, 148, 157, 161, 164, 165, 166, 167, 168, 170, 171, 172, 173, 175, 176, 177, 178, 180, 181, 183, 184, 189, 191, 192, 194 |
| `session-missing` | None / None | 8.2.30 / 3.5.3 | 0 | `@stub::tmpdir` | 3/9 | 3/14 | 2/19 | 199, 200, 202, 203, 205, 210 |
| `session-file` | None / None | 8.2.30 / 3.5.3 | 0 | `@stub::go` | 21/50 | 17/35 | 16/53 | 131, 132, 139, 141, 146, 148, 157, 161, 164, 165, 166, 167, 168, 170, 171, 172, 173, 175, 176, 177, 178, 180, 181, 183, 184, 189, 191, 192, 194 |
| `session-file` | None / None | 8.2.30 / 3.5.3 | 0 | `@stub::tmpdir` | 3/9 | 3/14 | 2/19 | 199, 200, 202, 203, 205, 210 |
| `session-empty` | None / None | 8.2.30 / 3.5.3 | 0 | `@stub::go` | 21/50 | 16/35 | 15/53 | 131, 132, 139, 141, 146, 148, 157, 161, 164, 165, 166, 167, 168, 170, 171, 172, 173, 175, 176, 177, 178, 180, 181, 183, 184, 189, 191, 192, 194 |
| `session-empty` | None / None | 8.2.30 / 3.5.3 | 0 | `@stub::tmpdir` | 3/9 | 3/14 | 2/19 | 199, 200, 202, 203, 205, 210 |

Raw JSON, retained out/out_hit graphs, stdout/stderr, exit status, and the generated summary are stored in the unique native report directory. The checksum rejection intentionally terminates with its diagnostic and legacy exit status 0. Size and truncated-stream contexts exit 1 because the shipped size-error expression reads undefined $stat at stub line 262; strict diagnostics remain visible. The measured Xdebug 3.1.6 and 3.5.3 contexts attribute conditional Bridge/EE constructor graphs to the first declaration; Reflection identifies the actual runtime method. Mismatched graph ranges remain preserved in the raw/normalized report and have unknown per-method branch metrics above. Uninstrumented native contexts remain unknown. The absent optional add-on navigation class keeps its successful-inclusion paths unavailable; source diagnostics still stop native constructors on PHP >= 8.2 in the complete matrix.

Loading probes omit bootstrap-owned `ee()` and fieldtype parent declarations. The legacy contexts use EE2.5 and verify the shipped `get_instance()` fallback plus configured/predefined version precedence. Fieldtype paths receive a byte-identical copy of the existing bounded `Support/Fieldtype.php` contract; these are native add-on loader checks, not full EE-core integration. Their procedural rows select source lines outside inventoried method ranges. Xdebug zero-hit procedural graphs alongside executed lines remain unknown branch metrics, with raw counters preserved. The installer-ancestor case exits 1 because global replacement at Ft line 13 also changes the ancestor directory, preventing the parent load at line 35. Every context removes its unique fixture files before reporting.

Autoloader probes report the actual availability of opcache_get_configuration. The configured PHP 7.4 runtime has no opcode-cache API, so its opcache flag case exercises the supported fallback; PHP 8.2 separately measures the available API and validate_permission query-string branch. The disabled-API case uses only command-local configuration. A restricted API raises the source warning at autoload line 77 on runtimes where the API exists. Full extraction still stops on its strict mkdir diagnostic. Pagination probes join the declared-property behavior subject to the actual bridge and typed bounded CP/Pagination collaborator. A global call with its default null URL forwards an empty string, violating the real render(Url) contract; its type-boundary error retains the shipped caller trace. Controller/default and global/explicit URL controls pass. Every raw context remains separate, including different runtimes.

Session fallback probes first verify that the actual PHP files handler accepts each valid configured path and creates its owned mode-0600 session file. The three-part N;MODE;/path form is valid PHP configuration, but this stub strips only the first separator and terminates at line 159. An empty session.save_path also accepts the owned sys_temp_dir through the real files handler, yet the stub rejects it instead of using that default. Its legacy exit status 0 and verbatim output remain in the JSON alongside returned=false; the ordinary valid-path test remains red. Plain/depth and read-only-temp fallback reach the existing mkdir warning. A writable file as TMPDIR reaches a distinct line-167 Not a directory diagnostic despite a valid session fallback. Missing and file-valued session locations intentionally reject with the preserved diagnostic. The valid empty-default case remains an ordinary red regression with its native default-directory proof. Shutdown cleanup captures output and restores owned directory permissions even after source die(); parent cleanup remains independent. No session native graph is merged with other contexts.
