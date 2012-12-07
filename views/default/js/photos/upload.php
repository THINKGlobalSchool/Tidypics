<?php
/**
 * Tidypics Upload JS
 *
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU General Public License v2
 */

?>
//<script>
elgg.provide('elgg.tidypics.upload');

elgg.tidypics.upload.init = function() {
	// @todo
}

elgg.register_hook_handler('init', 'system', elgg.tidypics.upload.init);