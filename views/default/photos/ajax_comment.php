<?php
/**
 * Tidypics ajax comment view
 * 
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU General Public License v2
 * 
 */

// Get entity/user guids
$entity_guid = get_input('entity_guid');
$user_guid = elgg_get_logged_in_user_guid();

// Get user's last comment
$comments = elgg_get_entities(array(
	'type' => 'object',
	'subtype' => 'comment',
	'container_guid' => $entity_guid,
	'owner_guid' => $user_guid,
	'reverse_order_by' => FALSE,
	'full_view' => TRUE,
	'limit' => 1,
	'preload_owners' => TRUE,
	'distinct' => FALSE,
));

$comment_id = $comments[0]->guid;

// View comment
$comment_view = elgg_view_entity($comments[0]);
$comment_content = <<<HTML
	<li class="elgg-item elgg-item-object elgg-item-object-comment" id="elgg-object-$comment_id">
	 	$comment_view
	</li>
HTML;

echo $comment_content;

