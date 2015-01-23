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

$options = array(
	'type' => 'object',
	'subtype' => 'album',
	'container_guid' => $page_owner,
	'limit' => 10,
	'full_view' => false,
	'list_type' => 'list'
);

$content = elgg_list_entities($options);

if (!$content) {
	$content = "<center><strong>" . elgg_echo('tidypics:none') . "</strong></center>";
}

echo $content;