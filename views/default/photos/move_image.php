<?php
/**
 * Tidypics move image to album view
 *
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU General Public License v2
 */

$heading = elgg_view_title(elgg_echo('photo:move_to_album'));
$form =  elgg_view_form('photos/image/move');

echo $heading;
echo $form;