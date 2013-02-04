<?php
/**
 * View a string representation of tags for this image
 *
 * @uses $vars['entity']
 *
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU General Public License v2
 */

if ($vars['entity']) {
	$entity = $vars['entity'];
} else if ($vars['entity_guid']) {
	$entity = get_entity($vars['entity_guid']);
}

if (elgg_instanceof($entity, 'object', 'image')) {
	$tags = $entity->getPhotoTags(0);
	if ($count = count($tags)) {
		echo "<div class='tidypics-lightbox-other'>";
		echo elgg_echo('tidypics:taggedinphoto');

		foreach ($tags as $idx => $tag) {
			if ($tag->type == 'user') {
				$user = get_entity($tag->value);
				$label = elgg_view('output/url', array(
					'text' => $user->name,
					'href' => $user->getURL(),
					'id' => 'tag-link-' . $tag->annotation_id,
					'class' => '_tp-people-tag-link',
				));
			} else {
				$label = $tag->value;
			}

			echo $label;

			if (($count - 1) != $idx) {
				echo ', ';
			}
		}

		echo "</div>";
	}
}