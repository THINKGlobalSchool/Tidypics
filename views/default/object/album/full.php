<?php
/**
 * Full view of an album
 *
 * @uses $vars['entity'] TidypicsAlbum
 *
 * @author Cash Costello
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU General Public License v2
 */

$load_lightbox = get_input('lb', 0);

$lightbox_image = get_entity($load_lightbox);

if (elgg_instanceof($lightbox_image, 'object', 'image')) {
	$lightbox_url = $lightbox_image->getURL();
}

$album = elgg_extract('entity', $vars);
$owner = $album->getOwnerEntity();

$owner_icon = elgg_view_entity_icon($owner, 'tiny');

$metadata = elgg_view_menu('entity', array(
	'entity' => $album,
	'handler' => 'photos',
	'sort_by' => 'priority',
	'class' => 'elgg-menu-hz',
));

$owner_link = elgg_view('output/url', array(
	'href' => "photos/owner/$owner->username",
	'text' => $owner->name,
	'is_trusted' => true,
));
$author_text = elgg_echo('byline', array($owner_link));
$date = elgg_view_friendly_time($album->time_created);
$categories = elgg_view('output/categories', $vars);

$subtitle = "$author_text $date $categories";

$params = array(
	'entity' => $album,
	'title' => false,
	'metadata' => $metadata,
	'subtitle' => $subtitle,
	'tags' => elgg_view('output/tags', array('tags' => $album->tags)),
);
$params = $params + $vars;
$summary = elgg_view('object/elements/summary', $params);

$body = '';
if ($album->description) {
	$body = elgg_view('output/longtext', array(
		'value' => $album->description,
		'class' => 'mbm',
	));
}

$options = array(
	'container_guid' => $album->guid,
);

if ($album->getContainerEntity()->canWriteToContainer(elgg_get_logged_in_user_guid())) {
	$options['enable_upload'] = TRUE;
}

$body .= tidypics_view_photo_list($options);

echo elgg_view('object/elements/full', array(
	'entity' => $album,
	'icon' => $owner_icon,
	'summary' => $summary,
	'body' => $body,
));

echo elgg_view_comments($album);

$js = <<<JAVASCRIPT
<script type='text/javascript'>
	$(document).ready(function() {
		var load_lightbox = $load_lightbox;

		var tidypics_init_full_album = function() {
			// Init infinite scroll
			elgg.tidypics.initInfiniteScroll();

			// Implement popstate
			window.addEventListener("popstate", function(e) {
				elgg.tidypics.popState(e);
			});

			if (load_lightbox) {
				var lightbox_url = '$lightbox_url';
				$.fancybox2(elgg.tidypics.lightbox.getFancyboxInit(lightbox_url));
			}
		}

		elgg.register_hook_handler('ready', 'system', tidypics_init_full_album);
	});
</script>
JAVASCRIPT;

echo $js;