<?php
/**
 * Tidypics ajax comments view
 * 
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU General Public License v2
 * 
 */

// Get entity/user guids
$entity_guid = get_input('entity_guid');
$user_guid = elgg_get_logged_in_user_guid();

// Get user's last annotation on given entity
$annotations = elgg_get_annotations(array(
	'annotation_names' => array('generic_comment'),
	'annotation_owner_guid' => $user_guid,
	'guid' => $entity_guid,
	'reverse_order_by' => TRUE, // this is key
	'limit' => 1,
));

$anno_id = $annotations[0]->id;

// View annotation
$annotation_view = elgg_view_annotation($annotations[0]);
$annotation_content = <<<HTML
	<li id="item-annotation-$anno_id" class='elgg-item'>
	 	$annotation_view
	</li>
HTML;

echo $annotation_content;