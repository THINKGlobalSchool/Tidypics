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
 * @uses $vars['container_guid']
 */

$items = elgg_extract('items', $vars);
$offset = elgg_extract('offset', $vars);
$limit = elgg_extract('limit', $vars);
$count = elgg_extract('count', $vars);
$enable_upload = elgg_extract('enable_upload', $vars);
$container_guid = elgg_extract('container_guid', $vars);
$group_guid = elgg_extract('group_guid', $vars);

foreach ($items as $item) {
	$photos_content .= elgg_view_entity($item, array(
		'full_view' => FALSE
	));
}

// Determine if we're showing the upload/create box
if ($enable_upload && !$offset) {

	$upload_params = array(
		'text' => elgg_echo('album:addpix'),
	);

	if ($container_guid) {
		$upload_params['container_guid'] = $container_guid;
		$upload_params['context'] = 'addtoalbum';
	} else {
		$upload_params['context'] = 'addphotos';
		if ($group_guid) {
			$upload_params['group_guid'] = $group_guid;
		}
	}

	$upload_content = elgg_view('input/photo_upload', $upload_params);
}

if (!$items) {
	// No results!
	$none = elgg_view_module('tidypics-image', '', elgg_echo('photos:none'), array('class' => 'tidypics-none'));
}

$id = $container_guid ? "_tp-load-more-container" : "_tp-infinite-list-container";

$content = <<<HTML
	<div class='tidypics-photos-list-container' id='$id'>
		$upload_content
		$none
		$photos_content
	</div>
HTML;

$next_offset = $limit + $offset;

echo $content;

// If we have items, show the next link (for infinite scroll)
if (count($items) && $next_offset < $count) {
	$next_offset = (int)$limit + (int)$offset;
	$next_link = elgg_http_add_url_query_elements(current_page_url(), array('offset' => $next_offset));

	// Only infinite scroll when not viewing an album
	if (!$container_guid) {
		echo "<div id='_tp-waypoint-container'><a class='_tp-waypoint-more' href='" . $next_link  . "'></a></div>";
	} else {
		$next_label = elgg_echo('tidypics:loadmore');
		echo "<a class='_tp-load-more tidypics-load-more elgg-button elgg-button-action' href='{$next_link}'>{$next_label}</a>";
	}
}