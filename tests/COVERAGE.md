# Coverage checkpoint

Baseline: `e3e13533ab0e67d13cdd16bedc0e377fa13910f0`; PHP 7.4.33; Xdebug 3.1.6. This checkpoint is incomplete and production regressions remain red.

Clover supplies executable lines for loose PHP and byte-identical archive measurement copies. Raw Xdebug lines preserve physical PHAR-stub execution. Actual Xdebug `out` / `out_hit` counters supply opcode exits; block hits alone are not branch proof. Synthetic return exits and zero-iteration edges over fixed nonempty arrays remain uncovered. Native subprocess graphs and parent graphs remain separate; missing data stays unknown. Xdebug 3.1.6 misattributes the conditional Bridge/EE constructor graph to its first declaration; Reflection detects mismatched runtime ranges, which retain unknown per-method branch metrics. Separate native contexts are recorded in `NATIVE_COVERAGE.md`.

All 27 shipped source units remain inventoried, including the PHAR stub, procedural files, archive views, and abstract declarations. Measurement copies are verified by SHA-256 before loading. Source is never excluded to improve the result. The raw collector installs an early filter and filters persisted JSON to the entire loose source tree, physical PHAR, and all PHAR members. A source-graph guard rejects an empty source measurement; unobserved inventory units remain visible.

| Source | Lines hit / executable | Parent procedural exits | Uncovered lines |
|---|---:|---:|---|
| `low_title/addon.setup.php` | 12/12 | 0/0 | none |
| `low_title/autoload.php` | 18/32 | 0/1 | 18, 19, 57, 63, 66, 74, 76, 77, 81, 82, 84, 90, 92, 93 |
| `low_title/low_title.phar#stub` | 55/154 | 0/45 | 13, 14, 16, 58, 59, 61, 62, 63, 64, 65, 67, 68, 69, 70, 71, 72, 74, 75, 76, 77, 78, 80, 81, 82, 83, 84, 85, 87, 88, 89, 90, 92, 93, 94, 96, 97, 98, 99, 115, 116, 117, 118, 121, 122, 123, 125, 126, 127, 129, 131, 132, 135, 136, 138, 139, 141, 145, 146, 148, 152, 154, 155, 156, 157, 158, 159, 161, 164, 165, 166, 167, 168, 170, 171, 172, 173, 175, 176, 177, 178, 180, 181, 183, 184, 189, 191, 192, 194, 199, 200, 202, 203, 205, 261, 262, 266, 280, 291, 292 |
| `low_title/low_title.phar/FluxCapacitor/Base/Ext.php` | 63/64 | not applicable | 152 |
| `low_title/low_title.phar/FluxCapacitor/Base/Ft.php` | 50/52 | 0/18 | 53, 54 |
| `low_title/low_title.phar/FluxCapacitor/Base/Mcp.php` | 73/82 | not applicable | 32, 104, 114, 135, 145, 184, 185, 187, 188 |
| `low_title/low_title.phar/FluxCapacitor/Base/Mod.php` | 8/8 | not applicable | none |
| `low_title/low_title.phar/FluxCapacitor/Base/Pi.php` | 2/9 | not applicable | 28, 29, 30, 31, 32, 33, 34 |
| `low_title/low_title.phar/FluxCapacitor/Base/Tab.php` | 20/22 | not applicable | 27, 28 |
| `low_title/low_title.phar/FluxCapacitor/Base/Upd.php` | 36/43 | not applicable | 62, 64, 66, 67, 68, 70, 73 |
| `low_title/low_title.phar/FluxCapacitor/Bridge/AbstractBridge.php` | 12/12 | not applicable | none |
| `low_title/low_title.phar/FluxCapacitor/Bridge/EE.php` | 9/16 | 0/23 | 20, 34, 44, 52, 60, 68, 76 |
| `low_title/low_title.phar/FluxCapacitor/Bridge/EE2.php` | 179/183 | not applicable | 49, 163, 164, 409 |
| `low_title/low_title.phar/FluxCapacitor/Bridge/EE3.php` | 174/174 | not applicable | none |
| `low_title/low_title.phar/FluxCapacitor/Bridge/EE4.php` | 174/174 | not applicable | none |
| `low_title/low_title.phar/FluxCapacitor/Bridge/EE5.php` | 174/174 | not applicable | none |
| `low_title/low_title.phar/FluxCapacitor/Bridge/EE6.php` | 0/0 | not applicable | none |
| `low_title/low_title.phar/FluxCapacitor/Conduit/GlobalFieldSettings.php` | 21/21 | not applicable | none |
| `low_title/low_title.phar/FluxCapacitor/Conduit/McpNav.php` | 0/80 | unknown | 7, 32, 34, 35, 37, 40, 41, 42, 45, 46, 50, 53, 54, 55, 65, 66, 69, 70, 71, 72, 73, 79, 80, 81, 82, 84, 87, 90, 91, 92, 93, 95, 98, 102, 103, 105, 106, 113, 116, 117, 120, 121, 129, 130, 131, 140, 143, 146, 150, 155, 156, 157, 161, 167, 168, 169, 175, 180, 185, 189, 214, 215, 218, 219, 221, 224, 229, 234, 239, 244, 245, 248, 249, 250, 251, 252, 253, 254, 255, 256 |
| `low_title/low_title.phar/FluxCapacitor/Conduit/PersistentCache.php` | 63/69 | not applicable | 197, 198, 199, 216, 217, 218 |
| `low_title/low_title.phar/FluxCapacitor/Conduit/StaticCache.php` | 15/15 | not applicable | none |
| `low_title/low_title.phar/FluxCapacitor/Conduit/Version.php` | 99/100 | not applicable | 180 |
| `low_title/low_title.phar/FluxCapacitor/FluxCapacitor.php` | 2/2 | not applicable | none |
| `low_title/low_title.phar/Views/license.php` | 239/239 | 0/8 | none |
| `low_title/low_title.phar/index.php` | 0/12 | 0/1 | 3, 5, 6, 9, 10, 14, 17, 18, 21, 24, 26, 28 |
| `low_title/pi.low_title.php` | 151/151 | 0/4 | none |
| `low_title/views/license.php` | 239/239 | 0/8 | none |

Procedural units are identified in `source-inventory.json`; class-only units have declarations rather than executable procedural regions. Captured parent procedural graphs are retained above, including zero-hit graphs. Native extraction/loading paths execute separately and are not merged into those graphs.

| Function / method | Lines hit / executable | Blocks hit / total | Exits hit / total | Uncovered exit line -> target |
|---|---:|---:|---:|---|
| `low_title/autoload.php::{closure}:19` | 18/30 | 22/31 | 22/44 | 35->2147483645, 46->151, 48->66, 48->74, 70->80, 73->92, 73->94, 73->98, 73->98, 73->100, 76->109, 81->115, 81->121, 81->122, 81->126, 81->127, 81->131, 81->132, 82->142, 87->2147483645, 90->2147483645, 92->2147483645 |
| `low_title/low_title.phar#stub::go:113` | 0/50 | 0/35 | 0/53 | 121->27, 122->35, 122->40, 125->40, 127->50, 127->54, 127->54, 127->55, 127->27, 129->60, 129->68, 132->2147483645, 138->79, 138->87, 139->85, 139->87, 141->2147483645, 145->90, 145->98, 146->96, 146->98, 148->2147483645, 154->105, 154->110, 154->110, 154->111, 154->149, 156->122, 156->133, 158->133, 158->139, 158->144, 158->144, 158->145, 158->147, 159->2147483645, 164->149, 170->188, 170->286, 175->218, 175->285, 175->219, 175->285, 180->253, 180->263, 181->284, 175->284, 175->218, 189->286, 191->293, 191->295, 194->295, 194->2147483645 |
| `low_title/low_title.phar#stub::tmpdir:196` | 4/9 | 4/14 | 3/19 | 198->8, 199->13, 199->18, 199->22, 199->22, 199->24, 199->26, 200->2147483645, 202->31, 202->35, 202->35, 202->36, 202->41, 203->2147483645, 205->2147483645, 208->2147483645 |
| `low_title/low_title.phar#stub::_unpack:213` | 20/20 | 4/4 | 4/4 | none |
| `low_title/low_title.phar#stub::extractFile:239` | 15/18 | 12/14 | 15/19 | 260->59, 262->2147483645, 265->79, 266->2147483645 |
| `low_title/low_title.phar#stub::_removeTmpFiles:272` | 9/10 | 10/12 | 11/18 | 276->55, 277->54, 279->42, 279->46, 279->47, 276->54, 288->2147483645 |
| `low_title/low_title.phar/FluxCapacitor/Base/Ext.php::__construct:25` | 5/5 | 1/1 | 0/1 | 32->2147483645 |
| `low_title/low_title.phar/FluxCapacitor/Base/Ext.php::activateExtension:34` | 3/3 | 4/4 | 4/5 | 36->10 |
| `low_title/low_title.phar/FluxCapacitor/Base/Ext.php::updateExtension:44` | 2/2 | 1/1 | 0/0 | none |
| `low_title/low_title.phar/FluxCapacitor/Base/Ext.php::disableExtension:55` | 6/6 | 3/3 | 3/3 | none |
| `low_title/low_title.phar/FluxCapacitor/Base/Ext.php::updateVersion:70` | 10/10 | 1/1 | 0/0 | none |
| `low_title/low_title.phar/FluxCapacitor/Base/Ext.php::registerExtension:85` | 21/21 | 9/9 | 11/12 | 104->2147483645 |
| `low_title/low_title.phar/FluxCapacitor/Base/Ext.php::unregisterExtension:122` | 7/7 | 3/3 | 3/3 | none |
| `low_title/low_title.phar/FluxCapacitor/Base/Ext.php::cpCustomMenu:145` | 2/3 | 2/3 | 1/4 | 148->8, 149->2147483645, 153->2147483645 |
| `low_title/low_title.phar/FluxCapacitor/Base/Ext.php::disable_extension:163` | 1/1 | 1/1 | 0/0 | none |
| `low_title/low_title.phar/FluxCapacitor/Base/Ext.php::activate_extension:168` | 1/1 | 1/1 | 0/0 | none |
| `low_title/low_title.phar/FluxCapacitor/Base/Ext.php::update_extension:173` | 1/1 | 1/1 | 0/0 | none |
| `low_title/low_title.phar/FluxCapacitor/Base/Ext.php::update_version:178` | 1/1 | 1/1 | 0/0 | none |
| `low_title/low_title.phar/FluxCapacitor/Base/Ext.php::register_extension:183` | 1/1 | 1/1 | 0/0 | none |
| `low_title/low_title.phar/FluxCapacitor/Base/Ext.php::unregister_extension:188` | 1/1 | 1/1 | 0/0 | none |
| `low_title/low_title.phar/FluxCapacitor/Base/Ext.php::cp_custom_menu:193` | 1/1 | 1/1 | 0/0 | none |
| `low_title/low_title.phar/FluxCapacitor/Base/Ft.php::__construct:46` | 4/6 | 4/5 | 3/7 | 52->26, 52->27, 56->34, 56->2147483645 |
| `low_title/low_title.phar/FluxCapacitor/Base/Ft.php::update:58` | 1/1 | 1/1 | 0/0 | none |
| `low_title/low_title.phar/FluxCapacitor/Base/Ft.php::accepts_content_type:63` | 1/1 | 1/1 | 0/0 | none |
| `low_title/low_title.phar/FluxCapacitor/Base/Ft.php::getChannelId:68` | 13/13 | 13/13 | 17/18 | 72->2147483645 |
| `low_title/low_title.phar/FluxCapacitor/Base/Ft.php::displaySettingsRow:100` | 18/18 | 7/7 | 8/9 | 125->2147483645 |
| `low_title/low_title.phar/FluxCapacitor/Base/Ft.php::displaySettingsReturn:127` | 11/11 | 3/3 | 2/3 | 133->2147483645 |
| `low_title/low_title.phar/FluxCapacitor/Base/Ft.php::_display_settings_add_row:146` | 1/1 | 1/1 | 0/0 | none |
| `low_title/low_title.phar/FluxCapacitor/Base/Ft.php::_package_display_settings:152` | 1/1 | 1/1 | 0/0 | none |
| `low_title/low_title.phar/FluxCapacitor/Base/Mcp.php::__construct:25` | 3/4 | 2/3 | 1/4 | 31->17, 34->22, 34->2147483645 |
| `low_title/low_title.phar/FluxCapacitor/Base/Mcp.php::index:36` | 1/1 | 1/1 | 0/0 | none |
| `low_title/low_title.phar/FluxCapacitor/Base/Mcp.php::license:41` | 14/14 | 6/6 | 7/7 | none |
| `low_title/low_title.phar/FluxCapacitor/Base/Mcp.php::save_license:66` | 35/38 | 19/19 | 21/28 | 86->89, 98->148, 104->2147483645, 108->209, 114->2147483645, 122->223, 131->251 |
| `low_title/low_title.phar/FluxCapacitor/Base/Mcp.php::paginateModel:138` | 18/19 | 11/12 | 10/16 | 144->21, 145->33, 152->43, 152->47, 152->52, 152->62 |
| `low_title/low_title.phar/FluxCapacitor/Base/Mcp.php::canIncludeNav:181` | 2/6 | 4/8 | 3/11 | 183->11, 183->12, 184->19, 184->25, 187->25, 187->31, 187->33, 188->2147483645 |
| `low_title/low_title.phar/FluxCapacitor/Base/Mod.php::__construct:20` | 2/2 | 1/1 | 0/1 | 24->2147483645 |
| `low_title/low_title.phar/FluxCapacitor/Base/Mod.php::getParameter:34` | 6/6 | 5/5 | 5/6 | 44->2147483645 |
| `low_title/low_title.phar/FluxCapacitor/Base/Pi.php::__construct:20` | 2/2 | 1/1 | 0/1 | 24->2147483645 |
| `low_title/low_title.phar/FluxCapacitor/Base/Pi.php::info:26` | 0/7 | 0/1 | 0/1 | 35->2147483645 |
| `low_title/low_title.phar/FluxCapacitor/Base/Tab.php::__construct:21` | 3/5 | 4/5 | 3/7 | 26->21, 26->22, 30->29, 30->2147483645 |
| `low_title/low_title.phar/FluxCapacitor/Base/Tab.php::display:33` | 1/1 | 1/1 | 0/0 | none |
| `low_title/low_title.phar/FluxCapacitor/Base/Tab.php::publish_tabs:39` | 1/1 | 1/1 | 0/0 | none |
| `low_title/low_title.phar/FluxCapacitor/Base/Tab.php::save:45` | 1/1 | 1/1 | 0/0 | none |
| `low_title/low_title.phar/FluxCapacitor/Base/Tab.php::publish_data_db:51` | 1/1 | 1/1 | 0/0 | none |
| `low_title/low_title.phar/FluxCapacitor/Base/Tab.php::getEntryVariables:56` | 13/13 | 6/6 | 7/7 | none |
| `low_title/low_title.phar/FluxCapacitor/Base/Upd.php::__construct:24` | 5/5 | 1/1 | 0/1 | 32->2147483645 |
| `low_title/low_title.phar/FluxCapacitor/Base/Upd.php::install:34` | 11/11 | 3/3 | 2/3 | 38->2147483645 |
| `low_title/low_title.phar/FluxCapacitor/Base/Upd.php::update:54` | 2/9 | 6/9 | 5/12 | 57->2147483645, 66->33, 66->42, 68->42, 68->44, 70->44, 70->2147483645 |
| `low_title/low_title.phar/FluxCapacitor/Base/Upd.php::uninstall:76` | 18/18 | 5/6 | 5/7 | 93->45, 93->46 |
| `low_title/low_title.phar/FluxCapacitor/Bridge/AbstractBridge.php::getEEVersion:22` | 4/4 | 3/3 | 2/3 | 27->2147483645 |
| `low_title/low_title.phar/FluxCapacitor/Bridge/AbstractBridge.php::ver_lt:34` | 1/1 | 1/1 | 0/0 | none |
| `low_title/low_title.phar/FluxCapacitor/Bridge/AbstractBridge.php::ver_gt:39` | 1/1 | 1/1 | 0/0 | none |
| `low_title/low_title.phar/FluxCapacitor/Bridge/AbstractBridge.php::ver_lte:44` | 1/1 | 1/1 | 0/0 | none |
| `low_title/low_title.phar/FluxCapacitor/Bridge/AbstractBridge.php::ver_gte:49` | 1/1 | 1/1 | 0/0 | none |
| `low_title/low_title.phar/FluxCapacitor/Bridge/AbstractBridge.php::is_ee2:54` | 1/1 | 1/1 | 0/0 | none |
| `low_title/low_title.phar/FluxCapacitor/Bridge/AbstractBridge.php::is_ee3:59` | 1/1 | 1/1 | 0/0 | none |
| `low_title/low_title.phar/FluxCapacitor/Bridge/AbstractBridge.php::is_ee4:64` | 1/1 | 1/1 | 0/0 | none |
| `low_title/low_title.phar/FluxCapacitor/Bridge/AbstractBridge.php::is_ee5:69` | 1/1 | 1/1 | 0/0 | none |
| `low_title/low_title.phar/FluxCapacitor/Bridge/AbstractBridge.php::instantiate:75` | 0/0 | unknown | unknown | abstract declaration |
| `low_title/low_title.phar/FluxCapacitor/Bridge/AbstractBridge.php::getBaseURL:76` | 0/0 | unknown | unknown | abstract declaration |
| `low_title/low_title.phar/FluxCapacitor/Bridge/AbstractBridge.php::getNav:77` | 0/0 | unknown | unknown | abstract declaration |
| `low_title/low_title.phar/FluxCapacitor/Bridge/AbstractBridge.php::cpURL:78` | 0/0 | unknown | unknown | abstract declaration |
| `low_title/low_title.phar/FluxCapacitor/Bridge/AbstractBridge.php::moduleURL:79` | 0/0 | unknown | unknown | abstract declaration |
| `low_title/low_title.phar/FluxCapacitor/Bridge/AbstractBridge.php::view:80` | 0/0 | unknown | unknown | abstract declaration |
| `low_title/low_title.phar/FluxCapacitor/Bridge/AbstractBridge.php::getCurrentPage:81` | 0/0 | unknown | unknown | abstract declaration |
| `low_title/low_title.phar/FluxCapacitor/Bridge/AbstractBridge.php::getStartNum:82` | 0/0 | unknown | unknown | abstract declaration |
| `low_title/low_title.phar/FluxCapacitor/Bridge/AbstractBridge.php::pagination:83` | 0/0 | unknown | unknown | abstract declaration |
| `low_title/low_title.phar/FluxCapacitor/Bridge/AbstractBridge.php::getSettings:84` | 0/0 | unknown | unknown | abstract declaration |
| `low_title/low_title.phar/FluxCapacitor/Bridge/AbstractBridge.php::getConfig:85` | 0/0 | unknown | unknown | abstract declaration |
| `low_title/low_title.phar/FluxCapacitor/Bridge/AbstractBridge.php::setConfig:86` | 0/0 | unknown | unknown | abstract declaration |
| `low_title/low_title.phar/FluxCapacitor/Bridge/AbstractBridge.php::cache:87` | 0/0 | unknown | unknown | abstract declaration |
| `low_title/low_title.phar/FluxCapacitor/Bridge/AbstractBridge.php::flashData:88` | 0/0 | unknown | unknown | abstract declaration |
| `low_title/low_title.phar/FluxCapacitor/Bridge/AbstractBridge.php::getAddonThemesDir:89` | 0/0 | unknown | unknown | abstract declaration |
| `low_title/low_title.phar/FluxCapacitor/Bridge/AbstractBridge.php::overwriteEEClass:90` | 0/0 | unknown | unknown | abstract declaration |
| `low_title/low_title.phar/FluxCapacitor/Bridge/AbstractBridge.php::removeEEClass:91` | 0/0 | unknown | unknown | abstract declaration |
| `low_title/low_title.phar/FluxCapacitor/Bridge/AbstractBridge.php::xss_clean:92` | 0/0 | unknown | unknown | abstract declaration |
| `low_title/low_title.phar/FluxCapacitor/Bridge/AbstractBridge.php::getCurrentUrlInfo:93` | 0/0 | unknown | unknown | abstract declaration |
| `low_title/low_title.phar/FluxCapacitor/Bridge/AbstractBridge.php::getCachePath:94` | 0/0 | unknown | unknown | abstract declaration |
| `low_title/low_title.phar/FluxCapacitor/Bridge/AbstractBridge.php::getFooter:95` | 0/0 | unknown | unknown | abstract declaration |
| `low_title/low_title.phar/FluxCapacitor/Bridge/AbstractBridge.php::reduce_double_slashes:96` | 0/0 | unknown | unknown | abstract declaration |
| `low_title/low_title.phar/FluxCapacitor/Bridge/AbstractBridge.php::javascript_to_page:97` | 0/0 | unknown | unknown | abstract declaration |
| `low_title/low_title.phar/FluxCapacitor/Bridge/AbstractBridge.php::module_installed:98` | 0/0 | unknown | unknown | abstract declaration |
| `low_title/low_title.phar/FluxCapacitor/Bridge/AbstractBridge.php::assign_parameters:99` | 0/0 | unknown | unknown | abstract declaration |
| `low_title/low_title.phar/FluxCapacitor/Bridge/AbstractBridge.php::assign_variables:100` | 0/0 | unknown | unknown | abstract declaration |
| `low_title/low_title.phar/FluxCapacitor/Bridge/EE.php::ee:18` | 0/1 | 0/1 | 0/0 | none |
| `low_title/low_title.phar/FluxCapacitor/Bridge/EE.php::__construct:42` | 0/1 | unknown | unknown | unknown |
| `low_title/low_title.phar/FluxCapacitor/Bridge/EE.php::__construct:50` | 0/1 | unknown | unknown | unknown |
| `low_title/low_title.phar/FluxCapacitor/Bridge/EE.php::__construct:58` | 0/1 | unknown | unknown | unknown |
| `low_title/low_title.phar/FluxCapacitor/Bridge/EE.php::__construct:66` | 0/1 | unknown | unknown | unknown |
| `low_title/low_title.phar/FluxCapacitor/Bridge/EE.php::__construct:74` | 0/1 | unknown | unknown | unknown |
| `low_title/low_title.phar/FluxCapacitor/Bridge/EE.php::__construct:82` | 1/1 | unknown | unknown | unknown |
| `low_title/low_title.phar/FluxCapacitor/Bridge/EE2.php::__construct:26` | 2/2 | 1/1 | 0/1 | 30->2147483645 |
| `low_title/low_title.phar/FluxCapacitor/Bridge/EE2.php::instantiate:32` | 1/1 | 1/1 | 0/1 | 35->2147483645 |
| `low_title/low_title.phar/FluxCapacitor/Bridge/EE2.php::getBaseURL:37` | 21/22 | 25/26 | 32/39 | 47->46, 47->47, 49->52, 55->67, 55->78, 55->89, 67->114 |
| `low_title/low_title.phar/FluxCapacitor/Bridge/EE2.php::getNav:78` | 6/6 | 6/6 | 7/9 | 81->26, 90->2147483645 |
| `low_title/low_title.phar/FluxCapacitor/Bridge/EE2.php::cpURL:92` | 30/30 | 27/28 | 33/46 | 94->17, 94->32, 94->44, 94->51, 94->60, 94->64, 98->30, 106->37, 106->39, 106->42, 112->49, 119->58, 135->92 |
| `low_title/low_title.phar/FluxCapacitor/Bridge/EE2.php::moduleURL:142` | 6/6 | 6/6 | 7/8 | 150->29 |
| `low_title/low_title.phar/FluxCapacitor/Bridge/EE2.php::view:157` | 3/5 | 3/5 | 2/6 | 162->12, 162->18, 162->19, 170->28 |
| `low_title/low_title.phar/FluxCapacitor/Bridge/EE2.php::getCurrentPage:173` | 6/6 | 5/5 | 4/6 | 178->2147483645, 180->2147483645 |
| `low_title/low_title.phar/FluxCapacitor/Bridge/EE2.php::getStartNum:186` | 1/1 | 1/1 | 0/0 | none |
| `low_title/low_title.phar/FluxCapacitor/Bridge/EE2.php::pagination:191` | 5/5 | 3/3 | 3/3 | none |
| `low_title/low_title.phar/FluxCapacitor/Bridge/EE2.php::getSettings:203` | 4/4 | 3/3 | 2/3 | 208->2147483645 |
| `low_title/low_title.phar/FluxCapacitor/Bridge/EE2.php::getConfig:214` | 2/2 | 1/1 | 0/0 | none |
| `low_title/low_title.phar/FluxCapacitor/Bridge/EE2.php::setConfig:221` | 15/15 | 7/7 | 9/10 | 253->2147483645 |
| `low_title/low_title.phar/FluxCapacitor/Bridge/EE2.php::cache:255` | 19/19 | 26/26 | 30/42 | 262->34, 262->79, 262->116, 262->116, 262->138, 265->2147483645, 267->2147483645, 269->2147483645, 275->2147483645, 277->2147483645, 291->2147483645, 293->2147483645 |
| `low_title/low_title.phar/FluxCapacitor/Bridge/EE2.php::flashData:302` | 1/1 | 1/1 | 0/1 | 305->2147483645 |
| `low_title/low_title.phar/FluxCapacitor/Bridge/EE2.php::getAddonThemesDir:311` | 2/2 | 3/4 | 2/4 | 313->6, 313->17 |
| `low_title/low_title.phar/FluxCapacitor/Bridge/EE2.php::overwriteEEClass:324` | 1/1 | 1/1 | 0/1 | 327->2147483645 |
| `low_title/low_title.phar/FluxCapacitor/Bridge/EE2.php::removeEEClass:335` | 1/1 | 1/1 | 0/1 | 338->2147483645 |
| `low_title/low_title.phar/FluxCapacitor/Bridge/EE2.php::xss_clean:345` | 1/1 | 1/1 | 0/0 | none |
| `low_title/low_title.phar/FluxCapacitor/Bridge/EE2.php::url_title:355` | 1/1 | 1/1 | 0/0 | none |
| `low_title/low_title.phar/FluxCapacitor/Bridge/EE2.php::getCurrentUrlInfo:365` | 10/10 | 8/8 | 9/10 | 376->2147483645 |
| `low_title/low_title.phar/FluxCapacitor/Bridge/EE2.php::getCachePath:386` | 4/4 | 3/3 | 3/3 | none |
| `low_title/low_title.phar/FluxCapacitor/Bridge/EE2.php::getFooter:397` | 1/1 | 1/1 | 0/0 | none |
| `low_title/low_title.phar/FluxCapacitor/Bridge/EE2.php::reduce_double_slashes:406` | 3/4 | 2/3 | 1/3 | 408->10, 409->2147483645 |
| `low_title/low_title.phar/FluxCapacitor/Bridge/EE2.php::initiateSettings:420` | 25/25 | 17/17 | 22/24 | 427->2147483645, 444->67 |
| `low_title/low_title.phar/FluxCapacitor/Bridge/EE2.php::javascript_to_page:480` | 1/1 | 1/1 | 0/1 | 483->2147483645 |
| `low_title/low_title.phar/FluxCapacitor/Bridge/EE2.php::module_installed:485` | 5/5 | 3/3 | 2/3 | 491->2147483645 |
| `low_title/low_title.phar/FluxCapacitor/Bridge/EE2.php::assign_parameters:497` | 1/1 | 1/1 | 0/0 | none |
| `low_title/low_title.phar/FluxCapacitor/Bridge/EE2.php::assign_variables:507` | 1/1 | 1/1 | 0/0 | none |
| `low_title/low_title.phar/FluxCapacitor/Bridge/EE3.php::__construct:34` | 2/2 | 1/1 | 0/1 | 38->2147483645 |
| `low_title/low_title.phar/FluxCapacitor/Bridge/EE3.php::instantiate:40` | 1/1 | 1/1 | 0/1 | 43->2147483645 |
| `low_title/low_title.phar/FluxCapacitor/Bridge/EE3.php::getBaseURL:45` | 5/5 | 5/5 | 6/6 | none |
| `low_title/low_title.phar/FluxCapacitor/Bridge/EE3.php::getNav:62` | 23/23 | 26/26 | 36/39 | 69->95, 84->77, 104->2147483645 |
| `low_title/low_title.phar/FluxCapacitor/Bridge/EE3.php::cpURL:106` | 13/13 | 14/14 | 20/20 | none |
| `low_title/low_title.phar/FluxCapacitor/Bridge/EE3.php::moduleURL:131` | 2/2 | 1/1 | 0/0 | none |
| `low_title/low_title.phar/FluxCapacitor/Bridge/EE3.php::view:138` | 15/15 | 8/8 | 10/10 | none |
| `low_title/low_title.phar/FluxCapacitor/Bridge/EE3.php::getCurrentPage:166` | 5/5 | 5/5 | 4/6 | 170->2147483645, 172->2147483645 |
| `low_title/low_title.phar/FluxCapacitor/Bridge/EE3.php::getStartNum:178` | 1/1 | 1/1 | 0/0 | none |
| `low_title/low_title.phar/FluxCapacitor/Bridge/EE3.php::pagination:183` | 8/8 | 5/5 | 6/6 | none |
| `low_title/low_title.phar/FluxCapacitor/Bridge/EE3.php::getSettings:203` | 4/4 | 3/3 | 2/3 | 208->2147483645 |
| `low_title/low_title.phar/FluxCapacitor/Bridge/EE3.php::getConfig:214` | 2/2 | 1/1 | 0/0 | none |
| `low_title/low_title.phar/FluxCapacitor/Bridge/EE3.php::setConfig:221` | 15/15 | 7/7 | 9/10 | 254->2147483645 |
| `low_title/low_title.phar/FluxCapacitor/Bridge/EE3.php::cache:256` | 12/12 | 14/14 | 14/24 | 259->16, 259->45, 259->75, 259->75, 259->89, 262->2147483645, 264->2147483645, 270->2147483645, 272->2147483645, 278->2147483645 |
| `low_title/low_title.phar/FluxCapacitor/Bridge/EE3.php::flashData:293` | 13/13 | 13/13 | 17/19 | 314->55, 323->2147483645 |
| `low_title/low_title.phar/FluxCapacitor/Bridge/EE3.php::getAddonThemesDir:329` | 2/2 | 3/4 | 2/4 | 331->6, 331->17 |
| `low_title/low_title.phar/FluxCapacitor/Bridge/EE3.php::overwriteEEClass:342` | 1/1 | 1/1 | 0/1 | 345->2147483645 |
| `low_title/low_title.phar/FluxCapacitor/Bridge/EE3.php::removeEEClass:353` | 1/1 | 1/1 | 0/1 | 356->2147483645 |
| `low_title/low_title.phar/FluxCapacitor/Bridge/EE3.php::xss_clean:363` | 1/1 | 1/1 | 0/0 | none |
| `low_title/low_title.phar/FluxCapacitor/Bridge/EE3.php::url_title:373` | 1/1 | 1/1 | 0/0 | none |
| `low_title/low_title.phar/FluxCapacitor/Bridge/EE3.php::assign_variables:383` | 1/1 | 1/1 | 0/0 | none |
| `low_title/low_title.phar/FluxCapacitor/Bridge/EE3.php::prep_url:393` | 1/1 | 1/1 | 0/0 | none |
| `low_title/low_title.phar/FluxCapacitor/Bridge/EE3.php::getCurrentUrlInfo:403` | 10/10 | 8/8 | 9/10 | 416->2147483645 |
| `low_title/low_title.phar/FluxCapacitor/Bridge/EE3.php::getCachePath:426` | 4/4 | 3/3 | 3/3 | none |
| `low_title/low_title.phar/FluxCapacitor/Bridge/EE3.php::getFooter:437` | 1/1 | 1/1 | 0/0 | none |
| `low_title/low_title.phar/FluxCapacitor/Bridge/EE3.php::reduce_double_slashes:446` | 2/2 | 1/1 | 0/0 | none |
| `low_title/low_title.phar/FluxCapacitor/Bridge/EE3.php::initiateSettings:456` | 25/25 | 17/17 | 22/24 | 463->2147483645, 481->66 |
| `low_title/low_title.phar/FluxCapacitor/Bridge/EE3.php::javascript_to_page:517` | 1/1 | 1/1 | 0/1 | 520->2147483645 |
| `low_title/low_title.phar/FluxCapacitor/Bridge/EE3.php::module_installed:522` | 1/1 | 1/1 | 0/0 | none |
| `low_title/low_title.phar/FluxCapacitor/Bridge/EE3.php::assign_parameters:527` | 1/1 | 1/1 | 0/0 | none |
| `low_title/low_title.phar/FluxCapacitor/Bridge/EE4.php::__construct:34` | 2/2 | 1/1 | 0/1 | 38->2147483645 |
| `low_title/low_title.phar/FluxCapacitor/Bridge/EE4.php::instantiate:40` | 1/1 | 1/1 | 0/1 | 43->2147483645 |
| `low_title/low_title.phar/FluxCapacitor/Bridge/EE4.php::getBaseURL:45` | 5/5 | 5/5 | 6/6 | none |
| `low_title/low_title.phar/FluxCapacitor/Bridge/EE4.php::getNav:62` | 23/23 | 26/26 | 36/39 | 69->95, 84->77, 104->2147483645 |
| `low_title/low_title.phar/FluxCapacitor/Bridge/EE4.php::cpURL:106` | 13/13 | 14/14 | 20/20 | none |
| `low_title/low_title.phar/FluxCapacitor/Bridge/EE4.php::moduleURL:131` | 2/2 | 1/1 | 0/0 | none |
| `low_title/low_title.phar/FluxCapacitor/Bridge/EE4.php::view:138` | 15/15 | 8/8 | 10/10 | none |
| `low_title/low_title.phar/FluxCapacitor/Bridge/EE4.php::getCurrentPage:166` | 5/5 | 5/5 | 4/6 | 170->2147483645, 172->2147483645 |
| `low_title/low_title.phar/FluxCapacitor/Bridge/EE4.php::getStartNum:178` | 1/1 | 1/1 | 0/0 | none |
| `low_title/low_title.phar/FluxCapacitor/Bridge/EE4.php::pagination:183` | 8/8 | 5/5 | 6/6 | none |
| `low_title/low_title.phar/FluxCapacitor/Bridge/EE4.php::getSettings:203` | 4/4 | 3/3 | 2/3 | 208->2147483645 |
| `low_title/low_title.phar/FluxCapacitor/Bridge/EE4.php::getConfig:214` | 2/2 | 1/1 | 0/0 | none |
| `low_title/low_title.phar/FluxCapacitor/Bridge/EE4.php::setConfig:221` | 15/15 | 7/7 | 9/10 | 254->2147483645 |
| `low_title/low_title.phar/FluxCapacitor/Bridge/EE4.php::cache:256` | 12/12 | 14/14 | 14/24 | 259->16, 259->45, 259->75, 259->75, 259->89, 262->2147483645, 264->2147483645, 270->2147483645, 272->2147483645, 278->2147483645 |
| `low_title/low_title.phar/FluxCapacitor/Bridge/EE4.php::flashData:293` | 13/13 | 13/13 | 17/19 | 314->55, 323->2147483645 |
| `low_title/low_title.phar/FluxCapacitor/Bridge/EE4.php::getAddonThemesDir:329` | 2/2 | 3/4 | 2/4 | 331->6, 331->17 |
| `low_title/low_title.phar/FluxCapacitor/Bridge/EE4.php::overwriteEEClass:342` | 1/1 | 1/1 | 0/1 | 345->2147483645 |
| `low_title/low_title.phar/FluxCapacitor/Bridge/EE4.php::removeEEClass:353` | 1/1 | 1/1 | 0/1 | 356->2147483645 |
| `low_title/low_title.phar/FluxCapacitor/Bridge/EE4.php::xss_clean:363` | 1/1 | 1/1 | 0/0 | none |
| `low_title/low_title.phar/FluxCapacitor/Bridge/EE4.php::url_title:373` | 1/1 | 1/1 | 0/0 | none |
| `low_title/low_title.phar/FluxCapacitor/Bridge/EE4.php::assign_variables:383` | 1/1 | 1/1 | 0/0 | none |
| `low_title/low_title.phar/FluxCapacitor/Bridge/EE4.php::prep_url:393` | 1/1 | 1/1 | 0/0 | none |
| `low_title/low_title.phar/FluxCapacitor/Bridge/EE4.php::getCurrentUrlInfo:403` | 10/10 | 8/8 | 9/10 | 416->2147483645 |
| `low_title/low_title.phar/FluxCapacitor/Bridge/EE4.php::getCachePath:426` | 4/4 | 3/3 | 3/3 | none |
| `low_title/low_title.phar/FluxCapacitor/Bridge/EE4.php::getFooter:437` | 1/1 | 1/1 | 0/0 | none |
| `low_title/low_title.phar/FluxCapacitor/Bridge/EE4.php::reduce_double_slashes:446` | 2/2 | 1/1 | 0/0 | none |
| `low_title/low_title.phar/FluxCapacitor/Bridge/EE4.php::initiateSettings:456` | 25/25 | 17/17 | 22/24 | 463->2147483645, 481->66 |
| `low_title/low_title.phar/FluxCapacitor/Bridge/EE4.php::javascript_to_page:517` | 1/1 | 1/1 | 0/1 | 520->2147483645 |
| `low_title/low_title.phar/FluxCapacitor/Bridge/EE4.php::module_installed:522` | 1/1 | 1/1 | 0/0 | none |
| `low_title/low_title.phar/FluxCapacitor/Bridge/EE4.php::assign_parameters:527` | 1/1 | 1/1 | 0/0 | none |
| `low_title/low_title.phar/FluxCapacitor/Bridge/EE5.php::__construct:34` | 2/2 | 1/1 | 0/1 | 38->2147483645 |
| `low_title/low_title.phar/FluxCapacitor/Bridge/EE5.php::instantiate:40` | 1/1 | 1/1 | 0/1 | 43->2147483645 |
| `low_title/low_title.phar/FluxCapacitor/Bridge/EE5.php::getBaseURL:45` | 5/5 | 5/5 | 6/6 | none |
| `low_title/low_title.phar/FluxCapacitor/Bridge/EE5.php::getNav:62` | 23/23 | 26/26 | 36/39 | 69->95, 84->77, 104->2147483645 |
| `low_title/low_title.phar/FluxCapacitor/Bridge/EE5.php::cpURL:106` | 13/13 | 14/14 | 20/20 | none |
| `low_title/low_title.phar/FluxCapacitor/Bridge/EE5.php::moduleURL:131` | 2/2 | 1/1 | 0/0 | none |
| `low_title/low_title.phar/FluxCapacitor/Bridge/EE5.php::view:138` | 15/15 | 8/8 | 10/10 | none |
| `low_title/low_title.phar/FluxCapacitor/Bridge/EE5.php::getCurrentPage:166` | 5/5 | 5/5 | 4/6 | 170->2147483645, 172->2147483645 |
| `low_title/low_title.phar/FluxCapacitor/Bridge/EE5.php::getStartNum:178` | 1/1 | 1/1 | 0/0 | none |
| `low_title/low_title.phar/FluxCapacitor/Bridge/EE5.php::pagination:183` | 8/8 | 5/5 | 6/6 | none |
| `low_title/low_title.phar/FluxCapacitor/Bridge/EE5.php::getSettings:203` | 4/4 | 3/3 | 2/3 | 208->2147483645 |
| `low_title/low_title.phar/FluxCapacitor/Bridge/EE5.php::getConfig:214` | 2/2 | 1/1 | 0/0 | none |
| `low_title/low_title.phar/FluxCapacitor/Bridge/EE5.php::setConfig:221` | 15/15 | 7/7 | 9/10 | 254->2147483645 |
| `low_title/low_title.phar/FluxCapacitor/Bridge/EE5.php::cache:256` | 12/12 | 14/14 | 14/24 | 259->16, 259->45, 259->75, 259->75, 259->89, 262->2147483645, 264->2147483645, 270->2147483645, 272->2147483645, 278->2147483645 |
| `low_title/low_title.phar/FluxCapacitor/Bridge/EE5.php::flashData:293` | 13/13 | 13/13 | 17/19 | 314->55, 323->2147483645 |
| `low_title/low_title.phar/FluxCapacitor/Bridge/EE5.php::getAddonThemesDir:329` | 2/2 | 3/4 | 2/4 | 331->6, 331->17 |
| `low_title/low_title.phar/FluxCapacitor/Bridge/EE5.php::overwriteEEClass:342` | 1/1 | 1/1 | 0/1 | 345->2147483645 |
| `low_title/low_title.phar/FluxCapacitor/Bridge/EE5.php::removeEEClass:353` | 1/1 | 1/1 | 0/1 | 356->2147483645 |
| `low_title/low_title.phar/FluxCapacitor/Bridge/EE5.php::xss_clean:363` | 1/1 | 1/1 | 0/0 | none |
| `low_title/low_title.phar/FluxCapacitor/Bridge/EE5.php::url_title:373` | 1/1 | 1/1 | 0/0 | none |
| `low_title/low_title.phar/FluxCapacitor/Bridge/EE5.php::assign_variables:383` | 1/1 | 1/1 | 0/0 | none |
| `low_title/low_title.phar/FluxCapacitor/Bridge/EE5.php::prep_url:393` | 1/1 | 1/1 | 0/0 | none |
| `low_title/low_title.phar/FluxCapacitor/Bridge/EE5.php::getCurrentUrlInfo:403` | 10/10 | 8/8 | 9/10 | 416->2147483645 |
| `low_title/low_title.phar/FluxCapacitor/Bridge/EE5.php::getCachePath:426` | 4/4 | 3/3 | 3/3 | none |
| `low_title/low_title.phar/FluxCapacitor/Bridge/EE5.php::getFooter:437` | 1/1 | 1/1 | 0/0 | none |
| `low_title/low_title.phar/FluxCapacitor/Bridge/EE5.php::reduce_double_slashes:446` | 2/2 | 1/1 | 0/0 | none |
| `low_title/low_title.phar/FluxCapacitor/Bridge/EE5.php::initiateSettings:456` | 25/25 | 17/17 | 22/24 | 463->2147483645, 481->66 |
| `low_title/low_title.phar/FluxCapacitor/Bridge/EE5.php::javascript_to_page:517` | 1/1 | 1/1 | 0/1 | 520->2147483645 |
| `low_title/low_title.phar/FluxCapacitor/Bridge/EE5.php::module_installed:522` | 1/1 | 1/1 | 0/0 | none |
| `low_title/low_title.phar/FluxCapacitor/Bridge/EE5.php::assign_parameters:527` | 1/1 | 1/1 | 0/0 | none |
| `low_title/low_title.phar/FluxCapacitor/Conduit/GlobalFieldSettings.php::__construct:9` | 2/2 | 1/1 | 0/1 | 13->2147483645 |
| `low_title/low_title.phar/FluxCapacitor/Conduit/GlobalFieldSettings.php::getSetting:15` | 4/4 | 3/3 | 2/3 | 19->2147483645 |
| `low_title/low_title.phar/FluxCapacitor/Conduit/GlobalFieldSettings.php::getSettings:24` | 7/7 | 4/4 | 3/4 | 33->2147483645 |
| `low_title/low_title.phar/FluxCapacitor/Conduit/GlobalFieldSettings.php::updateSetting:39` | 8/8 | 4/4 | 3/4 | 50->2147483645 |
| `low_title/low_title.phar/FluxCapacitor/Conduit/McpNav.php::__construct:30` | 0/13 | unknown | unknown | unknown |
| `low_title/low_title.phar/FluxCapacitor/Conduit/McpNav.php::generateNav:63` | 0/7 | unknown | unknown | unknown |
| `low_title/low_title.phar/FluxCapacitor/Conduit/McpNav.php::formatItems:76` | 0/16 | unknown | unknown | unknown |
| `low_title/low_title.phar/FluxCapacitor/Conduit/McpNav.php::itemsToNav:110` | 0/5 | unknown | unknown | unknown |
| `low_title/low_title.phar/FluxCapacitor/Conduit/McpNav.php::buttonsToItems:126` | 0/3 | unknown | unknown | unknown |
| `low_title/low_title.phar/FluxCapacitor/Conduit/McpNav.php::setActive:136` | 0/8 | unknown | unknown | unknown |
| `low_title/low_title.phar/FluxCapacitor/Conduit/McpNav.php::setBreadcrumb:165` | 0/3 | unknown | unknown | unknown |
| `low_title/low_title.phar/FluxCapacitor/Conduit/McpNav.php::getActiveItem:173` | 0/1 | unknown | unknown | unknown |
| `low_title/low_title.phar/FluxCapacitor/Conduit/McpNav.php::deferGenerate:178` | 0/1 | unknown | unknown | unknown |
| `low_title/low_title.phar/FluxCapacitor/Conduit/McpNav.php::preGenerateNav:183` | 0/1 | unknown | unknown | unknown |
| `low_title/low_title.phar/FluxCapacitor/Conduit/McpNav.php::postGenerateNav:187` | 0/1 | unknown | unknown | unknown |
| `low_title/low_title.phar/FluxCapacitor/Conduit/McpNav.php::getUrl:212` | 0/6 | unknown | unknown | unknown |
| `low_title/low_title.phar/FluxCapacitor/Conduit/McpNav.php::defaultItems:227` | 0/1 | unknown | unknown | unknown |
| `low_title/low_title.phar/FluxCapacitor/Conduit/McpNav.php::defaultButtons:232` | 0/1 | unknown | unknown | unknown |
| `low_title/low_title.phar/FluxCapacitor/Conduit/McpNav.php::defaultActiveMap:237` | 0/1 | unknown | unknown | unknown |
| `low_title/low_title.phar/FluxCapacitor/Conduit/McpNav.php::setToolbarIcon:242` | 0/11 | unknown | unknown | unknown |
| `low_title/low_title.phar/FluxCapacitor/Conduit/PersistentCache.php::get:18` | 14/14 | 7/7 | 6/9 | 24->2147483645, 28->2147483645, 38->2147483645 |
| `low_title/low_title.phar/FluxCapacitor/Conduit/PersistentCache.php::set:55` | 12/12 | 5/5 | 4/6 | 58->2147483645, 70->2147483645 |
| `low_title/low_title.phar/FluxCapacitor/Conduit/PersistentCache.php::delete:88` | 7/7 | 5/5 | 5/6 | 91->2147483645 |
| `low_title/low_title.phar/FluxCapacitor/Conduit/PersistentCache.php::has:112` | 2/2 | 1/1 | 0/0 | none |
| `low_title/low_title.phar/FluxCapacitor/Conduit/PersistentCache.php::clear:122` | 7/7 | 8/8 | 9/11 | 125->2147483645, 128->38 |
| `low_title/low_title.phar/FluxCapacitor/Conduit/PersistentCache.php::createKey:154` | 3/3 | 3/3 | 3/3 | none |
| `low_title/low_title.phar/FluxCapacitor/Conduit/PersistentCache.php::prepCacheData:169` | 1/1 | 1/1 | 0/0 | none |
| `low_title/low_title.phar/FluxCapacitor/Conduit/PersistentCache.php::serveCacheData:179` | 1/1 | 1/1 | 0/0 | none |
| `low_title/low_title.phar/FluxCapacitor/Conduit/PersistentCache.php::checkPath:189` | 16/22 | 11/13 | 12/18 | 196->33, 199->2147483645, 210->2147483645, 215->109, 218->2147483645, 230->2147483645 |
| `low_title/low_title.phar/FluxCapacitor/Conduit/StaticCache.php::get:14` | 2/2 | 4/4 | 4/4 | none |
| `low_title/low_title.phar/FluxCapacitor/Conduit/StaticCache.php::set:28` | 1/1 | 1/1 | 0/1 | 31->2147483645 |
| `low_title/low_title.phar/FluxCapacitor/Conduit/StaticCache.php::put:40` | 1/1 | 1/1 | 0/1 | 43->2147483645 |
| `low_title/low_title.phar/FluxCapacitor/Conduit/StaticCache.php::delete:51` | 2/2 | 1/1 | 0/0 | none |
| `low_title/low_title.phar/FluxCapacitor/Conduit/StaticCache.php::has:64` | 1/1 | 1/1 | 0/0 | none |
| `low_title/low_title.phar/FluxCapacitor/Conduit/StaticCache.php::all:73` | 1/1 | 1/1 | 0/0 | none |
| `low_title/low_title.phar/FluxCapacitor/Conduit/StaticCache.php::clear:82` | 2/2 | 1/1 | 0/0 | none |
| `low_title/low_title.phar/FluxCapacitor/Conduit/StaticCache.php::createKey:96` | 5/5 | 5/5 | 5/6 | 99->2147483645 |
| `low_title/low_title.phar/FluxCapacitor/Conduit/Version.php::__construct:23` | 2/2 | 1/1 | 0/1 | 27->2147483645 |
| `low_title/low_title.phar/FluxCapacitor/Conduit/Version.php::keyExistsForAddon:29` | 7/7 | 3/3 | 2/3 | 32->2147483645 |
| `low_title/low_title.phar/FluxCapacitor/Conduit/Version.php::saveLicenseKey:46` | 8/8 | 10/10 | 12/13 | 63->2147483645 |
| `low_title/low_title.phar/FluxCapacitor/Conduit/Version.php::deleteLicenseKey:65` | 3/3 | 1/1 | 0/1 | 70->2147483645 |
| `low_title/low_title.phar/FluxCapacitor/Conduit/Version.php::verifyLicenseTable:72` | 29/29 | 14/14 | 18/20 | 75->2147483645, 80->25 |
| `low_title/low_title.phar/FluxCapacitor/Conduit/Version.php::check:128` | 29/30 | 14/16 | 16/23 | 166->137, 167->136, 169->2147483645, 166->125, 179->150, 182->155, 182->2147483645 |
| `low_title/low_title.phar/FluxCapacitor/Conduit/Version.php::getLicenseKey:184` | 8/8 | 3/3 | 3/3 | none |
| `low_title/low_title.phar/FluxCapacitor/Conduit/Version.php::getIgnoreSite:199` | 7/7 | 3/3 | 3/3 | none |
| `low_title/low_title.phar/FluxCapacitor/Conduit/Version.php::getOpenSSLPublicKey:213` | 1/1 | 1/1 | 0/0 | none |
| `low_title/low_title.phar/FluxCapacitor/Conduit/Version.php::spiderSafe:218` | 5/5 | 4/4 | 4/4 | none |
| `low_title/low_title.phar/FluxCapacitor/FluxCapacitor.php::__construct:20` | 2/2 | 1/1 | 0/1 | 24->2147483645 |
| `low_title/low_title.phar/index.php::{closure}:3` | 0/12 | 2/5 | 1/7 | 9->17, 10->2147483645, 24->45, 24->47, 28->47, 28->2147483645 |
| `low_title/pi.low_title.php::entry:29` | 53/53 | 37/37 | 51/54 | 53->21, 69->2147483645, 133->2147483645 |
| `low_title/pi.low_title.php::category:171` | 38/38 | 23/23 | 31/33 | 193->21, 210->2147483645 |
| `low_title/pi.low_title.php::channel:273` | 20/20 | 11/11 | 14/15 | 288->17 |
| `low_title/pi.low_title.php::site:340` | 20/20 | 11/11 | 14/15 | 355->17 |
| `low_title/pi.low_title.php::_format:407` | 4/4 | 5/5 | 5/7 | 410->2147483645, 416->2147483645 |
| `low_title/pi.low_title.php::_get_channel_field_id:427` | 8/8 | 3/3 | 3/3 | none |
| `low_title/pi.low_title.php::_get_category_field_id:449` | 8/8 | 3/3 | 3/3 | none |

## Remaining constraints

- Archived `Conduit/McpNav.php` cannot load under strict diagnostics: line 7 includes an absent loose `Conduit/McpNav.php` under `@`. Its methods remain unexecuted; source is not invented or suppressed to bypass this.
- `Base/Pi::info()` fails immediately by using `$this` statically. Its later lines require a source fix.
- `Base/Upd::update()` reaches the absent optional `ext.low_title.php`; the required diagnostic blocks subsequent success/catch paths.
- Cache creation emits an `@mkdir` warning before later logging/false-return lines when a file occupies a directory path. Both source regressions are retained.
- Modern `ee('db')` supplies independent builders with empty default metadata, or the initial `data_cache` explicitly configured on the connection. Both settings cache-flush outcomes now execute, with assertions that stale table metadata is removed before lookup while unrelated metadata survives. Facade mutations do not seed or replace the DI backend. Legacy settings retain the actual clone contract.
- CP-only constructors, alternate EE selection, archive index callbacks, and extraction have isolated native contexts where applicable; their graphs are not merged into parent coverage. Physical extraction-stub graphs are preserved: helper methods execute in the parent, while `go()` remains unexecuted there. Native integrity-error paths remain separate.
- PHP < 5.3.6 and Windows branches cannot execute on the required macOS Herd matrix. The shipped `FluxCapacitor::L` constant is `false`, so its true paths require source changes.
- EE2 inline views write only inside a disposable installation containing a hash-verified copy of the shipped PHAR. Production paths are never written.

The mandatory skill helper reports 7 loose methods and 201 archive methods. Its `at_target` labels treat missing Clover branch metrics as sufficient; those labels are line-progress information and never campaign-completion evidence.
