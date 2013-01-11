<?php
/**
 * Update image field action
 *
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU General Public License v2
 */

$title = get_input('title', NULL);
$description = get_input('description', NULL);
$entity_guid = get_input('entity_guid', NULL);

$image = get_entity($entity_guid);

if (!elgg_instanceof($image, 'object', 'image') || !$image->canEdit()) {
	register_error(elgg_echo('image:invalid_image'));
	forward(REFERER);
}

if (!$title && !$description) {
	register_error(elgg_echo('image:error'));
}

$updated = FALSE;

if ($title && !empty($title)) {
	$image->title = $title;
	$updated = $title;
}

if ($description) {
	$image->description = $description;
	$updated = $description;
}

// If we updated a field, save and return the value
if ($updated) {
	if ($image->save()) {
		echo $updated;
	} else {
		// Failed to save
		register_error('image:error');
	}
} else {
	register_error('image:no_update');
}

forward(REFERER);