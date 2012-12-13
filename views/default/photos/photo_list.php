<?php
/**
 * Tidypics View Photo List
 *
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU General Public License v2
 *
 * @uses $vars['items']
 * @uses $vars['offset']
 * @uses $vars['limit']
 * @uses $vars['enable_upload']
 * @uses $vars['count']
 */

$items = elgg_extract('items', $vars);
$offset = elgg_extract('offset', $vars);
$limit = elgg_extract('limit', $vars);
$count = elgg_extract('count', $vars);
$enable_upload = elgg_extract('enable_upload', $vars);

foreach ($items as $item) {
	$photos_content .= elgg_view_entity($item, array('full_view' => FALSE));
}

// Determine if we're showing the upload/create box
if ($enable_upload && !$offset) {
	$params = array(
		'class' => 'elgg-module-tidypics-upload',
	);

	$upload_content = elgg_view_module('tidypics-upload', '', elgg_echo('album:addpix'), $params);
}

$content = <<<HTML
	<div class='tidypics-photos-list-container' id='_tp-infinite-list-container'>
		$upload_content
		$photos_content
	</div>
HTML;

$next_offset = $limit + $offset;

echo $content;

// If we have items, show the next link (for infinite scroll)
if (count($items) && $next_offset < $count) {
	$next_offset = (int)$limit + (int)$offset;
	$next_link = elgg_http_add_url_query_elements(current_page_url(), array('offset' => $next_offset));
	echo "<div id='_tp-waypoint-container'><a class='_tp-waypoint-more' href='" . $next_link  . "'></a></div>";
}