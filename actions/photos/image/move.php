<?php
/**
 * Move image action
 *
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU General Public License v2
 */

$entity_guid = get_input('entity_guid');
$album_guid = get_input('album_guid');

$photo = get_entity($entity_guid);
$album = get_entity($album_guid);

// Check for valid image
if (!elgg_instanceof($photo, 'object', 'image') || !$photo->canEdit()) {
	register_error(elgg_echo("image:invalid_image"));
	forward(REFERER);
}

// Check for valid album
if (!elgg_instanceof($album, 'object', 'album') || !$album->canEdit()) {
	register_error(elgg_echo("album:invalid_album"));
	forward(REFERER);
}

// Get original album
$original_album = $photo->getContainerEntity();

// Remove image from album image list
$original_album->removeImage($photo->guid);

// Add image to new album image list
$album->prependImageList(array($photo->guid));

// Change container to new album
$photo->container_guid = $album->guid;

// Save
if (!$photo->save()) {
	register_error(elgg_echo('image:movefailed', array($album->title)));
	forward(REFERER);
} else {
	system_message(elgg_echo('image:moved', array($album->title)));
	forward($photo->getURL());
}