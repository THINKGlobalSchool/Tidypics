<?php
/**
 * Summary of an image for lists/galleries
 *
 * @uses $vars['entity'] TidypicsImage
 *
 * @author Cash Costello
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU General Public License v2
 */

$image = elgg_extract('entity', $vars);

$img = elgg_view_entity_icon($image, 'small');

$body = elgg_view('output/url', array(
	'text' => $img,
	'href' => $image->getURL(),
	'encode_text' => false,
	'is_trusted' => true,
));

$params = array(
	//'footer' => $footer,
);
echo elgg_view_module('tidypics-image', '', $body, $params);