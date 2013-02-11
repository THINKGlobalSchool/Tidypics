<?php
/**
 * Tidypics list album form
 *
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU General Public License v2
 */

$offset = elgg_extract('offset', $vars, get_input('offset', 0));
$limit = elgg_extract('limit', $vars, get_input('limit', 10));

$options = array(
	'type' => 'object',
	'subtype' => 'album',
	'owner_guid' => elgg_get_logged_in_user_guid(),
	'limit' => $limit,
	'offset' => $offset,
	'count' => TRUE,
);

$count = elgg_get_entities($options);

unset($options['count']);

$albums = elgg_get_entities($options);

foreach ($albums as $album) {
	$icon = elgg_view_entity_icon($album, 'tiny');

	$input = "<input type='radio' name='select_album_guid' value='{$album->guid}'/>";

	echo elgg_view_image_block($input, elgg_get_excerpt($album->title, 50), array('image_alt' => $icon));
}

$nav = elgg_view('navigation/pagination', array(
	'offset' => $offset,
	'count' => $count,
	'limit' => $limit,
	'offset_key' => 'offset',
	'base_url' => elgg_get_site_url() . 'ajax/view/forms/photos/album/list?limit=' . $limit,
));

echo $nav;