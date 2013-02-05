<?php
/**
 * Lightbox (full) view of an image
 *
 * @uses $vars['entity'] TidypicsImage
 *
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU General Public License v2
 */

// Get image info
$photo = $vars['entity'];

// Add view annotation
$photo->addView();

$img = elgg_view_entity_icon($photo, 'large', array(
	'href' => $photo->getIconURL('master'),
	'img_class' => 'tidypics-photo taggable',
	'link_class' => 'tidypics-master-photo',
));

$owner_link = elgg_view('output/url', array(
	'href' => "photos/owner/" . $photo->getOwnerEntity()->username,
	'text' => $photo->getOwnerEntity()->name,
));
$author_text = elgg_echo('byline', array($owner_link));

$owner_icon = elgg_view_entity_icon($photo->getOwnerEntity(), 'tiny');

$metadata = elgg_view_menu('entity', array(
	'entity' => $vars['entity'],
	'handler' => 'photos',
	'sort_by' => 'priority',
	'class' => 'elgg-menu-hz',
));

$title = $photo->getTitle();

// Set up inline editing
if ($photo->canEdit()) {
	$edit_title = $edit_description = $edit_tags = '_tp-can-edit tidypics-lightbox-can-edit'; 


	$edit_title_input = elgg_view('input/text', array(
		'name' => '_tp_edit_inline_title',
		'value' => $title,
		'class' => 'tidypics-lightbox-edit-title hidden',
	));

	// Emit a hook to modify inline title content
	$edit_title_input = elgg_trigger_plugin_hook('inline_edit_title', 'tidypics', array('image' => $photo), $edit_title_input);

	$edit_tags_input = elgg_view('input/tags', array(
			'name' => '_tp_edit_inline_tags',
			'value' => $photo->tags,
			'class' => 'tidypics-lightbox-edit-tags hidden',
	));

	// Emit a hook to modify inline tags content
	$edit_tags_input = elgg_trigger_plugin_hook('inline_edit_tags', 'tidypics', array('image' => $photo), $edit_tags_input);

	$edit_description_input = elgg_view('input/plaintext', array(
		'name' => '_tp_edit_inline_description',
		'value' => $photo->description,
		'class' => 'tidypics-lightbox-edit-description hidden',
	));

	// Emit a hook to modify inline description content
	$edit_description_input =  elgg_trigger_plugin_hook('inline_edit_description', 'tidypics', array('image' => $photo), $edit_description_input);

	$edit_link = elgg_view('output/url', array(
		'text' => elgg_echo('edit'),
		'href' => '#',
		'class' => '_tp-edit-inline',
	));

	$edit_overlay = "<div class='tidypics-lightbox-edit-overlay'>{$edit_link}</div>";

	$save_link = elgg_view('output/url', array(
		'text' => elgg_echo('save'),
		'href' => '#',
		'class' => 'elgg-button elgg-button-submit _tp-save-inline hidden',
		'data-entity_guid' => $photo->guid,
	));

	$cancel_link = elgg_view('output/url', array(
		'text' => elgg_echo('cancel'),
		'href' => '#',
		'class' => 'elgg-button elgg-button-cancel _tp-cancel-inline hidden',
	));

	if (!$photo->tags) {
		$photo_tags = "<span class='none'>No tags</span>";
	} else {
		$photo_tags =  elgg_view('output/tags', array('tags' => $photo->tags));
	}

	$photo_tags_content = "<span class='_tp-tags'>{$photo_tags}</span>";

	$tags = "<div class='tidypics-lightbox-photo-tags $edit_tags' data-field='tags'>$edit_overlay $photo_tags_content $edit_tags_input $save_link $cancel_link</div>";
} else {
	// Non-edit defaults
	$tags = null;
}



// Photo summary/info
$date = elgg_view_friendly_time($photo->time_created);

$subtitle = "$author_text $date $categories $comments_link";

$params = array(
	'entity' => $photo,
	'title' => false,
	'metadata' => '',
	'subtitle' => $subtitle,
	'tags' => $tags,
);

$list_body = elgg_view('object/elements/summary', $params);

$params = array('class' => 'mbl');
$summary = elgg_view_image_block($owner_icon, $list_body, $params);

// Set up description
if ($photo->description) {
	$description =  elgg_view('output/longtext', array(
		'value' => $photo->description,
		'class' => 'mbl',
	));
} else if ($photo->canEdit()) {
	$description = "<span class='none'>" . elgg_echo('tidypics:no_description') . "</span>";
}

$comments = elgg_view_comments($photo);

// People tagging
$people_tag_help = elgg_view('photos/tagging/help', $vars);
$people_tag_select = elgg_view('photos/tagging/select', $vars);
$people_tags = elgg_view('photos/tagging/tags', $vars);
$people_tags_string = elgg_view('photos/tagging/tags_string', $vars);

// Close lightbox button
$close_lightbox = elgg_view('output/url', array(
	'text' => "X",
	'href' => '#',
	'title' => elgg_echo('tidypics:close'),
	'class' => 'tidypics-lightbox-close',
));

// Views
if (elgg_get_plugin_setting('view_count', 'tidypics')) {
	$view_info = $photo->getViewInfo();
	$views_text = elgg_echo('tidypics:numviews', array((int)$view_info['total']));
}

// Build content
$content = <<<HTML
	<div class='tidypics-lightbox-container'>
		<div class='tidypics-lightbox-header'>
			<div class='tidypics-lightbox-header-metadata'>
				$close_lightbox
				$metadata
			</div>
		</div>
		<div class='tidypics-lightbox-middle'>
			<div class='tidypics-lightbox-middle-container'>
				<div class="tidypics-photo-wrapper center">
					$img
					$people_tag_help
					$people_tag_select
					<div class='_tp-tagging-container'>
						$people_tags
					</div>
				</div>
			</div>
			<div class='tidypics-lightbox-sidebar'>
				<div class='tidypics-lightbox-sidebar-content'>
					<div class='tidypics-lightbox-photo-title $edit_title' data-field='title'>
						<h2 class='_tp-title'>$title</h2>$edit_title_input
						$edit_overlay
						$save_link $cancel_link
					</div>
					$summary
					<div class='tidypics-lightbox-photo-description $edit_description' data-field='description'>
						<div class='_tp-description'>
							$description
						</div>
						$edit_description_input
						$edit_overlay
						$save_link $cancel_link
					</div>
					$edit_description_input
					<div class='_tp-people-tags-container'>
						$people_tags_string
					</div>
					<div class='tidypics-lightbox-other'>
						$views_text
					</div>
					<div class='tidypics-lightbox-comments-container'>
						$comments
					</div>
				</div>
			</div>
		</div>
		<div class='tidypics-lightbox-footer'></div>
	</div>
HTML;

echo $content;