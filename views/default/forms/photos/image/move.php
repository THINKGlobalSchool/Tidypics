<?php
/**
 * Tidypics move image to album form
 *
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU General Public License v2
 */

$entity_guid = get_input('entity_guid');

$photo = get_entity($entity_guid);

if (!elgg_instanceof($photo, 'object', 'image')) {
	return false;
}

$select_label = elgg_echo('tidypics:select_album');

$albums = elgg_view('forms/photos/album/list', array('limit' => 5));

$entity_input = elgg_view('input/hidden', array('name' => 'entity_guid', 'value' => $entity_guid));

$album_input = elgg_view('input/hidden', array('name' => 'album_guid'));

$submit = elgg_view('input/submit', array(
	'value' => elgg_echo('tidypics:move'),
	'id' => '_tp-move-to-album-submit',
));

$content = <<<HTML
	<div id='tidypics-move-to-album-lightbox'>
		<div id='_tp-move-to-album-list'>
			$albums
		</div>
		<div class='elgg-foot'>
			$entity_input
			$album_input
			$submit
		</div>
	</div>
HTML;

echo $content;