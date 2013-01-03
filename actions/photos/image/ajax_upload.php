<?php
/**
 * Elgg single upload action for flash/ajax uploaders
 */

elgg_load_library('tidypics:upload');

if ($_SESSION['_tp_new_album_guid']) {
	$album_guid = $_SESSION['_tp_new_album_guid'];
} else {
	$album_guid = get_input('_tp_upload_select_existing_album');
	$new_album = get_input('_tp_upload_new_album_title', NULL);
}

$batch = get_input('_tp-upload-batch');
$container_guid = get_input('container_guid', elgg_get_logged_in_user_guid());

$errors = array();
$messages = array();

// Existing album
if ($album_guid) {
	$album = get_entity($album_guid);
} else if ($new_album) { // New album
	// Get tags
	$tags = string_to_tag_array(get_input('_tp_upload_album_tags'));

	// Get access id
	$access_id = get_input('_tp_upload_album_access_id', ACCESS_DEFAULT);

	$album = new TidypicsAlbum();
	$album->container_guid = $container_guid;
	$album->owner_guid = elgg_get_logged_in_user_guid();
	$album->access_id = $access_id;
	$album->title = $new_album;
	$album->tags = $tags;
	$album->save();

	$_SESSION['_tp_new_album_guid'] = $album->guid;
}

if (!elgg_instanceof($album, 'object', 'album')) {
	register_error(elgg_echo('tidypics:baduploadform'));
	forward(REFERER);
}

if (!$album->getContainerEntity()->canWriteToContainer(elgg_get_logged_in_user_guid())) {
	register_error(elgg_echo('tidypics:nopermission'));
	forward(REFERER);
}


// Set album guid in session for upload complete action
$_SESSION['_tp_album_guid'] = $album->guid;

// probably POST limit exceeded
if (empty($_FILES)) {
	trigger_error('Tidypics warning: user exceeded post limit on image upload', E_USER_WARNING);
	register_error(elgg_echo('tidypics:exceedpostlimit'));
	forward(REFERER);
}

$file = $_FILES['_tp_upload_file_input'];

$mime = tp_upload_get_mimetype($file['name']);
if ($mime == 'unknown') {
	register_error(elgg_echo('tidypics:not_image'));
	forward(REFERER);
}

$image = new TidypicsImage();
$image->container_guid = $album->guid;
$image->setMimeType($mime);
$image->access_id = $album->access_id;
$image->tags = $album->tags; // Set image tags from album tags
$image->batch = $batch;

try {
	$image->save($file);
	$album->prependImageList(array($image->guid));

	if (elgg_get_plugin_setting('img_river_view', 'tidypics') === "all") {
		add_to_river('river/object/image/create', 'create', $image->getOwnerGUID(), $image->getGUID());
	}

	system_message(elgg_echo('success'));
} catch (Exception $e) {
	register_error($e->getMessage());
	forward(REFERER);
}

echo json_encode(array(
	'album_guid' => $album->guid,
	'image_guid' => $image->guid,
	'batch' => $batch,
));

forward(REFERER);