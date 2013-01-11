<?php
/**
 * Add photo tag action
 *
 * @author Cash Costello
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU General Public License v2
 */


$coordinates_str = get_input('coordinates');
$username = get_input('username');
$image_guid = get_input('guid');

if ($image_guid == 0) {
	register_error(elgg_echo("tidypics:phototagging:error"));
	forward(REFERER);
}

$image = get_entity($image_guid);
if (!$image) {
	register_error(elgg_echo("tidypics:phototagging:error"));
	forward(REFERER);
}

if (empty($username)) {
	register_error(elgg_echo("tidypics:phototagging:error"));
	forward(REFERER);
}

$user = get_user_by_username($username);
if (!$user) {
	register_error(elgg_echo("tidypics:phototagging:nouser"));
	forward(REFERER);
	// plain tag
	//$relationships_type = 'word';
	//$value = $username;
} else {
	$relationships_type = 'user';
	$value = $user->guid;
}

$tag = new stdClass();
$tag->coords = $coordinates_str;
$tag->type = $relationships_type;
$tag->value = $value;
$access_id = $image->getAccessID();

$annotation_id = $image->annotate('phototag', serialize($tag), $access_id);
if ($annotation_id) {
	// if tag is a user id, add relationship for searching (find all images with user x)
	if ($tag->type === 'user') {
		if (!check_entity_relationship($tag->value, 'phototag', $image_guid)) {
			add_entity_relationship($tag->value, 'phototag', $image_guid);
/*
			// also add this to the river - subject is image, object is the tagged user
			add_to_river('river/object/image/tag', 'tag', $tagger->guid, $user_id, $access_id, 0, $annotation_id);
 * 
 */
			// notify user of tagging as long as not self
			if ($owner_id != $user_id) {
				notify_user(
						$user_id,
						$owner_id,
						elgg_echo('tidypics:tag:subject'),
						sprintf(
							elgg_echo('tidypics:tag:body'),
							$image->getTitle(),
							$tagger->name,
							$image->getURL()
						)
				);
			}

		}
	}

	system_message(elgg_echo("tidypics:phototagging:success"));
}

forward(REFERER);
