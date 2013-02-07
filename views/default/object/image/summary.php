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

$body = elgg_view_entity_icon($image, 'small', array('link_class' => 'tidypics-lightbox'));

// Let plugins customize module params
$params = elgg_trigger_plugin_hook('photo_summary_params', 'tidypics', array('entity' => $image), NULL);

echo elgg_view_module('tidypics-image', '', $body, $params);