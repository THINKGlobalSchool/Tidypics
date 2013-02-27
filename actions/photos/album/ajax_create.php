<?php
/**
 * Elgg ajax create album action
 */

$album_name = get_input('album_name');
$album_tags = get_input('album_tags');
$album_access = get_input('album_access', ACCESS_DEFAULT);
$group_guid = get_input('group_guid');

if ($group_guid) {
	$container_guid = $group_guid;
} else {
	$container_guid = get_input('container_guid', elgg_get_logged_in_user_guid());
}

$container = get_entity($container_guid);

// Check permissions on container (for groups)
if (!$container->canWriteToContainer(elgg_get_logged_in_user_guid())) {
	register_error(elgg_echo('tidypics:nopermission'));
 	forward(REFERER);
}

// Get tags
$tags = string_to_tag_array($album_tags);

$album = new TidypicsAlbum();
$album->container_guid = $container_guid;
$album->owner_guid = elgg_get_logged_in_user_guid();
$album->access_id = $album_access;
$album->title = $album_name;
$album->tags = $tags;

if ($album->save()) {
	echo json_encode(array(
		'album_guid' => $album->guid,
	));
} else {
	register_error(elgg_echo('album:savefailed'));
}

forward(REFERER);