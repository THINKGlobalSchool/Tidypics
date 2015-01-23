<?php
/**
 * Role profile widget for albums
 *
 * @author Jeff Tilson
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU General Public License v2
 */

$page_owner = $vars['user'];

echo elgg_list_entities(array(
	'type' => 'object',
	'subtype' => 'image',
	'limit' => 9,
	'owner_guid' => $page_owner->guid,
	'full_view' => false,
	'list_type' => 'gallery',
	'list_type_toggle' => false
));