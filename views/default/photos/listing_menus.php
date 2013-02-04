<?php
/**
 * Tidypics Listing Menus
 *
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU General Public License v2
 *
 * @uses $vars['type']
 * @uses $vars['container_guid']
 */

$type = elgg_extract('type', $vars);
$container_guid = elgg_extract('container_guid', $vars);

//$filter_label = elgg_echo('tidypics:filterby', array($type));
//$content = "<label class='tidypics-filterby-label'>{$filter_label}</label>";

$content .= elgg_view_menu('photos-listing-filter', array(
	'class' => 'elgg-menu-hz',
	'sort_by' => 'priority',
	'container_guid' => $container_guid,
	'type' => $type,
));

$content .= elgg_view_menu('photos-listing-sort', array(
	'class' => 'elgg-menu-hz',
	'sort_by' => 'priority',
	'container_guid' => $container_guid,
	'type' => $type,
));

echo $content;