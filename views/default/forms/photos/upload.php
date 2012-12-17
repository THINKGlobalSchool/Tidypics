<?php
/**
 * Tidypics upload form body
 *
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU General Public License v2
 */

// Get context and container
$context = elgg_extract('context', $vars);
$container_guid = elgg_extract('container_guid', $vars);

// Get container entity
$container = get_entity($container_guid);

// Default heading
$heading = elgg_echo("tidypics:upload:{$context}");

// Build heading based on context
if (elgg_instanceof($container, 'group')) {
	$heading .= elgg_echo('tidypics:upload:togroup', array($container->name));
} else if (elgg_instanceof($container, 'object', 'album')) {
	$heading .= elgg_echo('tidypics:upload:toalbum', array($container->title));
	if (elgg_instanceof($container->getContainerEntity(), 'group')) {
		$heading .= elgg_echo('tidypics:upload:togroup', array($container->getContainerEntity()->name));
	}
}

// New album label/input (Common across contexts)
$album_label = elgg_echo('tidypics:upload:albumname');
$album_input = elgg_view('input/text', array(
	'name' => '_tp_upload_new_album_title',
	'class' => 'tidypics-upload-new-album-title',
));

// 'or' label
$or_label = elgg_echo('tidypics:upload:or');

// Depending on context, show different album options
if ($context == 'addphotos') {
	$choose_album_input = elgg_view('input/button', array(
		'value' => elgg_echo('tidypics:upload:choosealbum'),
		'name' => '_tp_upload_choose_existing_album',
	));

	// Get list of existing albums
	$albums = elgg_get_entities(array(
		'type' => 'object',
		'subtype' => 'album',
		'limit' => 30, // @todo could have a ton of albums
		'owner_guid' => elgg_get_logged_in_user_guid(),
	));

	$album_options = array();

	foreach ($albums as $album) {
		$album_options[$album->guid] = $album->title;
	}

	// Album list input (hidden by default)
	$album_list = elgg_view('input/dropdown', array(
		'name' => '_tp_upload_select_existing_album',
		'options_values' => $album_options,
		'class' => 'hidden tidypics-upload-select-existing-album',
		'disabled' => 'DISABLED',
	));

	// Show album title input with button to switch to existing albums
	$album_menu = "$album_label $album_list $album_input $or_label $choose_album_input";
} else if ($context == 'addalbum') {
	// Regular new album input
	$album_menu = "$album_label $album_input";
}

// Drop zone label
$drop_label = elgg_echo('tidypics:upload:drophere');

// Manual select input
$select_input = elgg_view('input/submit', array(
	'name' => '_tp_upload_choose_submit',
	'value' => elgg_echo('tidypics:upload:browsephotos'),
));

// Upload/create input
$submit_input = elgg_view('input/submit', array(
	'name' => '_tp_upload_submit',
	'value' => elgg_echo('tidypics:upload'),
));

// Cancel input
$cancel_input = elgg_view('input/submit', array(
	'name' => '_tp_upload_cancel_submit',
	'value' => elgg_echo('tidypics:upload:cancel'),
	'class' => 'elgg-button elgg-button-cancel',
));

// Build form content
$content = <<<HTML
	<h2>$heading</h2>
	$album_menu
	<div id='tidypics-upload-dropzone'>
		<div id='tidypics-upload-dropzone-inner'>
			<h1>$drop_label</h1><br />
			<strong>- $or_label -</strong><br /><br />
			$select_input
		</div>
	</div>
	<div class='elgg-foot'>
	$submit_input $cancel_input
	</div>
HTML;

echo $content;