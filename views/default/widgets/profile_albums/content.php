<?php
/**
 * Role profile widget for albums
 *
 * @author Jeff Tilson
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU General Public License v2
 */

$page_owner = $vars['user'];

$options = array(
	'type' => 'object',
	'subtype' => 'album',
	'container_guid' => $page_owner->guid,
	'limit' => 10,
	'full_view' => false,
	'list_type' => 'list'
);

echo elgg_list_entities($options);