<?php
/**
 * Role profile widget for photos a user is tagged in
 *
 * @author Jeff Tilson
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU General Public License v2
 */

$page_owner = elgg_get_page_owner_guid();

if (!$page_owner) {
	$page_owner = $vars['user'];
}

$content = elgg_list_entities_from_relationship(array(
	'relationship' => 'phototag', 
	'relationship_guid' => $page_owner, 
	'inverse_relationship' => false,
	'types' => 'object', 
	'subtypes' => 'image', 
	'full_view'=> false,
	'limit' => 9,
	'list_type' => 'gallery',
)); 	

if (!$content) {
	$content = "<center><strong>" . elgg_echo('tidypics:photos:none') . "</strong></center>";
}

echo $content;