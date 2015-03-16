<?php
/**
 * Instructions on how to peform photo tagging
 *
 * @author Cash Costello
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU General Public License v2
 */

$cancel_button = elgg_view('output/url', array(
	'text' => elgg_echo('tidypics:finish_tagging'),
	'href' => '#',
	'id' => 'tidypics-tagging-quit'
));

$instructions = elgg_echo('tidypics:taginstruct');

$content = <<<HTML
	<div id="tidypics-tagging-help" class="	tidypics-tagging-help pam hidden">
		$instructions $cancel_button
	</div>
HTML;

echo $content;