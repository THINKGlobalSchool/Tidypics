<?php
/**
 * Save album action
 *
 * @author Cash Costello
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU General Public License v2
 */

// Get input data
$title = get_input('title');
$description = get_input('description');
$tags = get_input('tags');
$access_id = get_input('access_id');
$container_guid = get_input('container_guid', elgg_get_logged_in_user_guid());
$guid = get_input('guid');

elgg_make_sticky_form('tidypics');

if (empty($title)) {
	register_error(elgg_echo("album:blank"));
	forward(REFERER);
}

if ($guid) {
	$album = get_entity($guid);
	$message = elgg_echo("album:saved");
} else {
	$album = new TidypicsAlbum();
	$message = elgg_echo("album:created");
}

// Store old access id
$old_access_id = $album->access_id;

// Store old tags
$old_tags = $album->tags;

if (!is_array($old_tags)) {
	$old_tags = array($old_tags);
}

$album->container_guid = $container_guid;
$album->owner_guid = elgg_get_logged_in_user_guid();
$album->access_id = $access_id;
$album->title = $title;
$album->description = $description;


if ($tags) {
	$album->tags = string_to_tag_array($tags);
}

// If we've updated the access_id for the album, push update images context
if ($old_access_id != $access_id) {
	elgg_push_context('tidypics_update_images_access');
}

// If we've updated the album tags push update tags context
if (serialize($old_tags) != serialize(string_to_tag_array($tags))) {
	elgg_push_context('tidypics_update_images_tags');
}

if (!$album->save()) {
	register_error(elgg_echo("album:savefailed"));

	// Revert image updates
	elgg_push_context('tidypics_update_images_access');
	elgg_push_context('tidypics_update_images_tags');

	forward(REFERER);
}

elgg_clear_sticky_form('tidypics');

system_message($message);

forward($album->getURL());