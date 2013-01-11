<?php
/**
 * View the tags for this image
 *
 * @uses $vars['entity']
 *
 * @author Cash Costello
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU General Public License v2
 */

if ($vars['entity']) {
	$entity = $vars['entity'];
} else if ($vars['entity_guid']) {
	$entity = get_entity($vars['entity_guid']);
}

if (elgg_instanceof($entity, 'object', 'image')) {
	$tags = $entity->getPhotoTags();
	foreach ($tags as $tag) {
		echo elgg_view('photos/tagging/tag', array('tag' => $tag));
	}
}