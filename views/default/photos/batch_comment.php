<?php
/**
 * Tidypics batch comment view, prepends batch river views to replace the batch guid
 * with the album guid
 *
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU General Public License v2
 */

$entity = get_entity($vars['item']->object_guid);

if (elgg_instanceof($entity, 'object', 'tidypics_batch')
 	&& $vars['item']->action_type == 'comment'
 	&& $vars['item']->view == 'river/annotation/generic_comment/create') 
{
	$album = get_entity($entity->container_guid);
	$vars['item']->object_guid = $album->guid;
}