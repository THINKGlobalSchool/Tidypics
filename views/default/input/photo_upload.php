<?php
/**
 * Tidypics Photo Upload Input
 *
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU General Public License v2
 * 
 * @uses $vars['text']
 * @uses $vars['id']
 * @uses $vars['class']
 * @uses $vars['container_guid']
 */

$text = elgg_extract('text', $vars);
$class = elgg_extract('class', $vars);
$id = elgg_extract('id', $vars);

$attributes = array();

if ($container_guid = elgg_extract('container_guid', $vars)) {
	$attributes['data-container_guid'] = $container_guid;
}

if ($context = elgg_extract('context', $vars)) {
	$attributes['data-context'] = $context;
}

$attributes = elgg_format_attributes($attributes);

if ($class) {
	$class = "elgg-module-tidypics-upload $class";
} else {
	$class = 'elgg-module-tidypics-upload';
}

$content = "<div class='_tp-uploader' $attributes>$text</div>";

$params = array(
		'class' => $class,
		'id' => $id,
);

echo elgg_view_module('tidypics-album', '', $content, $params);