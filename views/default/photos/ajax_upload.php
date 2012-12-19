<?php
/**
 * Tidypics upload view
 *
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU General Public License v2
 */

$form = elgg_view_form('photos/ajax_upload', array('class' => 'elgg-form-alt'), $vars);

$content = <<<HTML
	<div id='tidypics-upload-container'>
	 	$form
	</div>
HTML;

echo $content;