<?php
/**
 * Remove photo tag action
 */

$annotation = elgg_get_annotation_from_id(get_input('annotation_id'));

if (!$annotation instanceof ElggAnnotation || $annotation->name != 'phototag') {
	register_error(elgg_echo("tidypics:phototagging:delete:error"));
	forward(REFERER);
}

if (!$annotation->canEdit()) {
	register_error(elgg_echo("tidypics:phototagging:delete:error"));
	forward(REFERER);
}

$tag = unserialize($annotation->value);

if ($annotation->delete()) {
	remove_entity_relationship($tag->value, 'phototag', $annotation->entity_guid);
	system_message(elgg_echo("tidypics:phototagging:delete:success"));
	echo elgg_view('photos/tagging/tags_string', array('entity_guid' => $annotation->entity_guid));
} else {
	system_message(elgg_echo("tidypics:phototagging:delete:error"));
}

forward(REFERER);
