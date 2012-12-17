<?php
/**
 * Tidypics Content View
 *
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU General Public License v2
 */

$content = <<<HTML
	<div id='tidypics-content-container'>
	</div>
HTML;

$script = <<<JAVASCRIPT
<script type='text/javascript'>
	tidypics_init_content = function() {
		elgg.tidypics.loadTabContent(window.location.href);

		// Implement popstate
		window.addEventListener("popstate", function(e) {
			elgg.tidypics.popState(e);
		});
	};
	// Need to init AFTER elgg is initted
	elgg.register_hook_handler('ready', 'system', tidypics_init_content);

</script>
JAVASCRIPT;

echo $content . $script;