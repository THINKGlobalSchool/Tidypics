<?php
/**
 * Image view
 *
 * @uses $vars['entity'] TidypicsImage
 *
 * @author Cash Costello
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU General Public License v2
 */


$full_view = elgg_extract('full_view', $vars, false);
$lightbox = elgg_extract('lightbox', $vars, false);

if ($full_view) {
	echo elgg_view('object/image/full', $vars);
} else if ($lightbox) {
	echo elgg_view('object/image/lightbox', $vars);
} else {
	echo elgg_view('object/image/summary', $vars);
}