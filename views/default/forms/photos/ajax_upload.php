<?php
/**
 * Tidypics ajax upload form body
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

// Depending on context, show different album options
if ($context == 'addphotos' || $context == 'addalbum') {
	// 'addphotos' and 'addalbum' share some menu items

	// New album label/input (Common across contexts)
	$album_label = elgg_echo('tidypics:upload:newalbumname');
	$album_input = elgg_view('input/text', array(
		'name' => '_tp_upload_new_album_title',
		'class' => 'tidypics-upload-new-album-title _tp-upload-active-input',
		'value' => date('F j, Y'),
	));

	// 'or' label
	$or_label = elgg_echo('tidypics:upload:or');

	// Tags label 
	$album_tags_label = elgg_echo('tidypics:upload:newalbumtags');

	// Tags input
	$album_tags_input = elgg_view('input/tags', array(
		'name' => '_tp_upload_album_tags',
	));

	// Access label
	$album_access_label = elgg_echo('tidypics:upload:newalbumaccess');

	// Access input
	$album_access_input = elgg_view('input/access', array(
		'name' => '_tp_upload_album_access_id',
		'value' => ACCESS_DEFAULT,
	));

	if ($context == 'addphotos') { 	// Specific items for 'addphotos'
		$choose_album_input = elgg_view('input/button', array(
			'value' => elgg_echo('tidypics:upload:choosealbum'),
			'name' => '_tp_upload_choose_existing_album',
			'class' => 'elgg-button elgg-button-action',
		));

		// Get list of existing albums
		$albums = elgg_get_entities(array(
			'type' => 'object',
			'subtype' => 'album',
			'limit' => 30, // @todo could have a ton of albums, grabbing most 30 recent for now
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

		// Album list menu item
		$params = array(
			'name' => 'album-list',
			'text' => $album_list,
			'href' => FALSE,
			'priority' => 200,
		);
		elgg_register_menu_item('tidypics-upload-album', $params);

		// Switch menu item
		$params = array(
			'name' => 'album-switch',
			'text' => "<strong>- $or_label -</strong> $choose_album_input",
			'href' => FALSE,
			'priority' => 400,
		);
		elgg_register_menu_item('tidypics-upload-album', $params);

	}

	// Label menu item
	$params = array(
		'name' => 'album-label',
		'text' => "<span id='_tp-upload-album-label'>$album_label</span>",
		'href' => FALSE,
		'priority' => 100,
	);
	elgg_register_menu_item('tidypics-upload-album', $params);

	// Album input menu item
	$params = array(
		'name' => 'album-input',
		'text' => $album_input,
		'href' => FALSE,
		'priority' => 300,
	);
	elgg_register_menu_item('tidypics-upload-album', $params);

	// Album tags label menu item
	$params = array(
		'name' => 'album-tags-label',
		'text' => $album_tags_label,
		'href' => FALSE,
		'priority' => 100,
	);
	elgg_register_menu_item('tidypics-upload-album-metadata', $params);

	// Album tags input menu item
	$params = array(
		'name' => 'album-tags-input',
		'text' => $album_tags_input,
		'href' => FALSE,
		'priority' => 200,
	);
	elgg_register_menu_item('tidypics-upload-album-metadata', $params);


	// Album access label menu item
	$params = array(
		'name' => 'album-access-label',
		'text' => $album_access_label,
		'href' => FALSE,
		'priority' => 300,
	);
	elgg_register_menu_item('tidypics-upload-album-metadata', $params);

	// Album access input menu item
	$params = array(
		'name' => 'album-access-input',
		'text' => $album_access_input,
		'href' => FALSE,
		'priority' => 400,
	);
	elgg_register_menu_item('tidypics-upload-album-metadata', $params);

} else if ($context == 'addtoalbum') {
	$album_hidden = elgg_view('input/hidden', array(
		'name' => '_tp_upload_select_existing_album',
		'value' => $container_guid,
	));

	// Hidden album input menu item
	$params = array(
		'name' => 'album-hidden',
		'text' => $album_hidden,
		'href' => FALSE,
		'priority' => 100,
	);
	elgg_register_menu_item('tidypics-upload-album', $params);
}

// Get album menu
$album_menu = elgg_view_menu('tidypics-upload-album', array(
	'sort_by' => 'priority',
	'class' => 'elgg-menu-hz _tp-upload-album-menu',
));

// Get album metadata menu (can be disabled entirely when switching between new/existing album mode)
$album_menu_metadata = elgg_view_menu('tidypics-upload-album-metadata', array(
	'sort_by' => 'priority',
	'class' => 'elgg-menu-hz _tp-upload-album-metadata-menu',
));

// Drop zone label
$drop_label = elgg_echo('tidypics:upload:drophere');

// Manual select input
$select_input = elgg_view('input/submit', array(
	'name' => '_tp_upload_choose_submit',
	'value' => elgg_echo('tidypics:upload:browsephotos'),
));

$file_input = elgg_view('input/file', array(
	'name' => '_tp_upload_file_input',
	'id' => '_tp_upload-file-input',
	'class' => 'hidden',
	'multiple' => 'multiple',
));

// Batch input
$batch_input = elgg_view('input/hidden', array(
	'name' => '_tp-upload-batch',
	'value' => time(),
));

// Finish button
$finish_input = elgg_view('input/submit', array(
	'name' => '_tp-upload-finish',
	'value' => elgg_echo('tidypics:upload:finish'),
	'class' => 'elgg-button elgg-button-action right tidypics-upload-finish-input',
));

// Build form content
$content = <<<HTML
	<h2>$heading</h2>
	<div id='_tp-upload-album-menu'>$album_menu $album_menu_metadata</div>
	<div id='_tp-upload-dropzone' class='tidypics-upload-dropzone tidypics-upload-dropzone-droppable'>
		<div class='tidypics-upload-dropzone-inner'>
			<h1>$drop_label</h1><br />
			<strong>- $or_label -</strong><br /><br />
			$select_input
		</div>
	</div>
	<div class='elgg-foot'>
	<!-- Hidden inputs -->
	$file_input $batch_input
		<div class='tidypics-upload-status'><span></span>$finish_input</div>
	</div>
HTML;

echo $content;