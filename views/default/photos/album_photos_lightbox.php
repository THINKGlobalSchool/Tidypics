<?php
/**
 * Tidypics get list of album photos suitable for loading a lightbox (json)
 * @todo This should probably be it's own viewtype, and be more flexible
 *
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU General Public License v2
 *
 * @uses $vars['container_guid']
 */

$album_guid = $vars['container_guid'];

$album = get_entity($album_guid);

$url = elgg_get_site_url();

if (elgg_instanceof($album, 'object', 'album')) {
	$image_guids = $album->getImageList();

	$image_list = array();

	foreach ($image_guids as $guid) {
		$image_list[] = array(
			'href' => $url . 'photos/image/' . $guid,
			'type' => 'ajax',
			'thumbSource' => $url . "photos/thumbnail/{$guid}/small",
			'isDom' => 0
		);
	}

	echo json_encode($image_list);
}