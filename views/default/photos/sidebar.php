<?php
/**
 * Sidebar view
 */

$page = elgg_extract('page', $vars);
$type = elgg_extract('type', $vars);

$owner = elgg_get_page_owner_entity();

$image = elgg_extract('image', $vars);

if ($image && $page == 'image') {
	if (elgg_get_plugin_setting('exif', 'tidypics')) {
		echo elgg_view('photos/sidebar/exif', $vars);
	}
} else {
	if ($type == 'photos') {
		$subtype = 'image';
	} else if ($type = 'albums') {
		$subtype = 'album';
	}

	if ($owner) {
		echo elgg_view('page/elements/comments_block', array(
			'subtypes' => $subtype,
			'owner_guid' => $owner->guid,
		));
	} else {
		echo elgg_view('page/elements/comments_block', array(
			'subtypes' => $subtype,
		));
	}
}

if ($page == 'upload') {
	if (elgg_get_plugin_setting('quota', 'tidypics')) {
		echo elgg_view('photos/sidebar/quota', $vars);
	}
}