<?php
/**
 * Tagged photos for tabbed profile
 * 
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU General Public License v2
 */

// Get user
$page_owner = elgg_get_page_owner_entity();

$content .= elgg_view_title(elgg_echo('tidypics:usertag', array($page_owner->name)));

$content .= elgg_list_entities_from_relationship(array(
	'relationship' => 'phototag', 
	'relationship_guid' => $page_owner->guid, 
	'inverse_relationship' => false,
	'types' => 'object', 
	'subtypes' => 'image', 
	'full_view'=> false,
	'list_type' => 'gallery',
)); 	

echo $content;