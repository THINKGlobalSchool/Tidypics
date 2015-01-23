<?php
/**
 * Role profile widget for albums
 *
 * @author Jeff Tilson
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU General Public License v2
 */

$page_owner = elgg_get_page_owner_guid();

if (!$page_owner) {
	$page_owner = $vars['user'];
}

$content = elgg_list_entities(array(
	'type' => 'object',
	'subtype' => 'image',
	'limit' => 9,
	'owner_guid' => $page_owner,
	'full_view' => false,
	'list_type' => 'gallery',
	'list_type_toggle' => false
));

if (!$content) {
	$content = "<center><strong>" . elgg_echo('tidypics:photos:none') . "</strong></center>";
}

echo $content;