<?php
/**
 * Elgg single upload action for flash/ajax uploaders
 */

elgg_load_library('tidypics:upload');

$batch = get_input('_tp-upload-batch');

$album_guid = get_input('_tp-upload-album-guid');

$album = get_entity($album_guid);

// Make sure we have a valid album
if (!elgg_instanceof($album, 'object', 'album')) {
	register_error(elgg_echo('tidypics:baduploadform'));
	forward(REFERER);
}

// Make sure we can write to the container (for groups)
if (!$album->getContainerEntity()->canWriteToContainer(elgg_get_logged_in_user_guid())) {
	register_error(elgg_echo('tidypics:nopermission'));
	forward(REFERER);
}

$errors = array();
$messages = array();

$group_guid = get_input('group_guid');

if ($group_guid) {
	$container_guid = $group_guid;
} else {
	$container_guid = get_input('container_guid', elgg_get_logged_in_user_guid());
}

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
$image->access_id = $album->access_id;
$image->setMimeType($mime);
$image->batch = $batch;
$image->tags = $album->tags;

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
	'album_guid' => $album_guid,
	'image_guid' => $image->guid,
	'batch' => $batch,
));

forward(REFERER);