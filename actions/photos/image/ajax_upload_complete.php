<?php
/**
 * Ajax batch upload complete
 */

$batch = get_input('batch');

$album_guid = get_input('album_guid', FALSE);

$img_river_view = elgg_get_plugin_setting('img_river_view', 'tidypics');

// Get the album
$album = get_entity($album_guid);
if (!elgg_instanceof($album, 'object', 'album')) {
	register_error('');
	echo elgg_echo('tidypics:baduploadform');
	forward(REFERER);
}

// Check permissions on album container (for groups)
if (!$album->getContainerEntity()->canWriteToContainer(elgg_get_logged_in_user_guid())) {
 	register_error(elgg_echo('tidypics:nopermission'));
 	forward(REFERER);
}

$params = array(
	'type'            => 'object',
	'subtype'         => 'image',
	'metadata_names'  => 'batch',
	'metadata_values' => $batch,
	'limit'           => 0
);

$images = elgg_get_entities_from_metadata($params);

if ($images) {	
	// Create a new batch object to contain these photos
	$batch = new ElggObject();
	$batch->subtype = "tidypics_batch";
	$batch->access_id = $album->access_id;
	$batch->container_guid = $album->guid;

	if ($batch->save()) {
		foreach ($images as $image) {
			// Add batch relationship
			add_entity_relationship($image->guid, "belongs_to_batch", $batch->getGUID());
		}
	}

} else {
	// No images uploaded! Display an error. Delete the album if it's brand new
	if ($album->new_album) {
		$album->delete();
	}
	register_error('');
	echo elgg_echo('tidypics:noimagesuploaded');
	forward(REFERER);
}

// "added images to album" river
if ($img_river_view == "batch" && $album->new_album == false) {
	elgg_create_river_item(array(
		'view' => 'river/object/tidypics_batch/create',
		'action_type' => 'create',
		'subject_guid' => $batch->getOwnerGUID(),
		'object_guid' => $batch->getGUID()
	));
}

// "created album" river
if ($album->new_album) {
	$album->new_album = false;
	$album->first_upload = true;

	elgg_create_river_item(array(
		'view' => 'river/object/album/create',
		'action_type' => 'create',
		'subject_guid' => $album->getOwnerGUID(),
		'object_guid' => $album->getGUID()
	));
} else {
	// "added image to album" notifications
	if ($album->first_upload) {
		$album->first_upload = false;
	}
}

elgg_trigger_event('create', 'object', $album);

echo json_encode(array(
	'batch_guid' => $batch->getGUID(),
	'forward_url' => $album->getURL(),
));

forward(REFERER);