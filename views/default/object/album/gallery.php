<?php
/**
 * Display an album in a gallery
 *
 * @uses $vars['entity'] TidypicsAlbum
 *
 * @author Cash Costello
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU General Public License v2
 */

$album = elgg_extract('entity', $vars);
$enable_lightbox = elgg_extract('enable_lightbox', $vars);

$icon_options = array('href' => $album->getURL());

if ($enable_lightbox) {
	elgg_push_context('tidypics_view_album');
	$icon_options['link_class'] = 'tidypics-album-lightbox';
}

$album_cover = elgg_view_entity_icon($album, 'small', $icon_options);

// Album title beneath
$footer = elgg_view('output/url', array(
	'text' => elgg_get_excerpt($album->getTitle(), 19),
	'href' => $album->getURL(),
	'is_trusted' => true,
	'class' => 'tidypics-heading',
));

$footer .= '<div class="elgg-subtext">' . elgg_echo('album:num', array($album->getSize())) . '</div>';

$params = array(
	'footer' => $footer,
);

// Let plugins customize module params
$params = elgg_trigger_plugin_hook('album_summary_params', 'tidypics', array('entity' => $album), $params);

echo elgg_view_module('tidypics-album', $header, $album_cover, $params);
