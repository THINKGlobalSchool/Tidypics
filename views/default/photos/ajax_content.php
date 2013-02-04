<?php
/**
 * Tidypics Ajax Content View
 *
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU General Public License v2
 * @uses $vars['content']
 * @uses $vars['title']
 * @uses $vars['sidebar'] ?
 */

$tab_content = elgg_extract('content', $vars);
$title = elgg_extract('title', $vars);
$sidebar = elgg_view('page/elements/sidebar', $vars);
$breadcrumbs = elgg_view('navigation/breadcrumbs');
$filter_menu = elgg_view_menu('photos-filter', array(
	'class' => 'elgg-menu-hz elgg-menu-filter elgg-menu-filter-default',
	'sort_by' => 'priority',
));

$content = <<<HTML
	<span id='_tp-content-tab-page-title' class='hidden'>$title</span>
	<span id='_tp-content-tab-breadcrumbs' class='hidden'>$breadcrumbs</span>
	<span id='_tp-content-tab-filter' class='hidden'>$filter_menu</span>
	<span id='_tp-content-tab-sidebar' class='hidden'>
		<div class="elgg-sidebar">
			$sidebar			
		</div>
	</span>
HTML;

echo $content . $menu . $tab_content;