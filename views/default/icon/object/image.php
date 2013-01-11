<?php
/**
 * Image icon view
 *
 * @uses $vars['entity']     The entity the icon represents - uses getIconURL() method
 * @uses $vars['size']       tiny, small (default), large, master
 * @uses $vars['href']       Optional override for link
 * @uses $vars['img_class']  Optional CSS class added to img
 * @uses $vars['link_class'] Optional CSS class added to link
 * @uses $vars['title']      Optional title override
 *
 * @author Cash Costello
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU General Public License v2
 */

$entity = $vars['entity'];

$sizes = array('master', 'large', 'small', 'thumb');

// Get size
if (!in_array($vars['size'], $sizes)) {
	$vars['size'] = 'small';
}

switch ($vars['size']) {
	case 'thumb':
		$thumb_name = $entity->thumbnail;
		break;
	case 'small':
		$thumb_name = $entity->smallthumb;
		break;
	case 'large':
		$thumb_name = $entity->largethumb;
		break;
	default:
		$thumb_name = $entity->getFilename();
		break;
}

$icon = new ElggFile();
$icon->owner_guid = $entity->getOwnerGUID();
$icon->setFilename($thumb_name);

$thumbinfo = getimagesize($icon->getFilenameOnFilestore());
$width = $thumbinfo[0];
$height = $thumbinfo[1];

if (!isset($vars['title'])) {
	$title = $entity->getTitle();
} else {
	$title = $vars['title'];
}

$url = $entity->getURL();
if (isset($vars['href'])) {
	$url = $vars['href'];
}

$class = '';
if (isset($vars['img_class'])) {
	$class = $vars['img_class'];
}
$class = "elgg-photo $class";

$img_src = $entity->getIconURL($vars['size']);
$img_src = elgg_format_url($img_src);
$img = elgg_view('output/img', array(
	'src' => $img_src,
	'class' => $class,
	'title' => $title,
	'alt' => $title,
	'width' => $width,
	'height' => $height,
));

if ($url) {
	$params = array(
		'href' => $url,
		'text' => $img,
		'is_trusted' => true,
	);

	if (elgg_in_context('tidypics_view_album')) {
		$params['data-album_guid'] = $entity->container_guid;
	}

	if (isset($vars['link_class'])) {
		$params['class'] = $vars['link_class'];
	}
	echo elgg_view('output/url', $params);
} else {
	echo $img;
}
