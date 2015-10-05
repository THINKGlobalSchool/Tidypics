<?php
/**
 * Tidypics Photo Gallery plugin
 *
 * @author Jeff Tilson, Cash Costello
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU General Public License v2
 *
 * @todo
 * - Clean up old uploader (make sure it still works as a fallback
 * - TidypicsAlbum & TidypicsImage classes override the entity url handler?
 */

elgg_register_event_handler('init', 'system', 'tidypics_init');

/**
 * Tidypics plugin initialization
 */
function tidypics_init() {	

	// Register libraries
	$base_dir = elgg_get_plugins_path() . 'tidypics/lib';
	elgg_register_library('tidypics:core', "$base_dir/tidypics.php");
	elgg_register_library('tidypics:upload', "$base_dir/upload.php");
	elgg_register_library('tidypics:resize', "$base_dir/resize.php");
	elgg_register_library('tidypics:exif', "$base_dir/exif.php");
	elgg_load_library('tidypics:core');

	// Set up site menu
	elgg_register_menu_item('site', array(
		'name' => 'photos',
		'href' => 'photos/all',
		'text' => elgg_echo('photos'),
	));

	// Register a page handler
	elgg_register_page_handler('photos', 'tidypics_page_handler');

	// Extend CSS
	elgg_extend_view('css/elgg', 'css/photos/css');
	elgg_extend_view('css/admin', 'css/photos/css');

	// Extend Elgg JS
	elgg_extend_view('js/elgg', 'js/photos/config');

	// Register JS Libs
	$js = elgg_get_simplecache_url('js', 'photos/tidypics');
	elgg_register_js('tidypics', $js, 'footer');

	$js = elgg_get_simplecache_url('js', 'photos/tagging');
	elgg_register_js('tidypics:tagging', $js, 'footer');
	
	$js = elgg_get_simplecache_url('js', 'photos/upload');
	elgg_register_js('tidypics:upload', $js, 'footer');

	$js = elgg_get_simplecache_url('js', 'photos/lightbox');
	elgg_register_js('tidypics:lightbox', $js, 'footer');

	// Register jquery-waypoints js lib
	$js = elgg_get_simplecache_url('js', 'waypoints');
	elgg_register_js('jquery-waypoints', $js);

	// Load jquery-file-upload libs
	elgg_load_js('jquery.ui.widget');
	elgg_load_js('jquery-file-upload');
	elgg_load_js('jquery.iframe-transport');

	// Register jquery-fancybox2 js lib
	$js = elgg_get_simplecache_url('js', 'fancybox2');
	elgg_register_js('jquery-fancybox2', $js);

	// Register jquery-fancybox2 css
	$css = elgg_get_simplecache_url('css', 'fancybox2');
	elgg_register_css('jquery-fancybox2', $css);

	// Load lightbox JS/CSS
	elgg_load_js('lightbox');
	elgg_load_css('lightbox');

	// Add photos link to owner block/hover menus
	elgg_register_plugin_hook_handler('register', 'menu:owner_block', 'tidypics_owner_block_menu');

	// Add a tagged photos tab to the tabbed profile
	elgg_register_plugin_hook_handler('tabs', 'profile', 'tidypics_profile_tab_hander');

	// Add admin menu item
	elgg_register_admin_menu_item('configure', 'photos', 'settings');

	// Register for search
	elgg_register_entity_type('object', 'image');
	elgg_register_entity_type('object', 'album');

	// Register for the entity menu
	elgg_register_plugin_hook_handler('register', 'menu:entity', 'tidypics_entity_menu_setup', 100);

	// Register items for photo list filter
	elgg_register_plugin_hook_handler('register', 'menu:photos-listing-filter', 'tidypics_photo_list_filter_menu_setup');

	// Register items for photo list sort
	elgg_register_plugin_hook_handler('register', 'menu:photos-listing-sort', 'tidypics_photo_list_sort_menu_setup');

	// Register group option
	add_group_tool_option('photos', elgg_echo('tidypics:enablephotos'), true);
	elgg_extend_view('groups/tool_latest', 'photos/group_module');

	// Register widgets
	elgg_register_widget_type('album_view', elgg_echo("tidypics:widget:albums"), elgg_echo("tidypics:widget:album_descr"), array('profile'));
	elgg_register_widget_type('latest_photos', elgg_echo("tidypics:widget:latest"), elgg_echo("tidypics:widget:latest_descr"), array('profile'));

	if (elgg_is_active_plugin('roles')) {
		elgg_register_widget_type('profile_tagged', elgg_echo('tidypics:widget:tagged'), elgg_echo('tidypics:widget:tagged_desc'), array('roleprofilewidget'));
		elgg_register_widget_type('profile_albums', elgg_echo("tidypics:widget:albums"), elgg_echo("tidypics:widget:albums"), array('roleprofilewidget'));
		elgg_register_widget_type('profile_latest_photos', elgg_echo("tidypics:widget:latest"), elgg_echo("tidypics:widget:latest"), array('roleprofilewidget'));
		
		// Register ajax views for widget content
		elgg_register_ajax_view('widgets/profile_tagged/content');
		elgg_register_ajax_view('widgets/profile_albums/content');
		elgg_register_ajax_view('widgets/profile_latest_photos/content');
	}

	// RSS extensions for embedded media
	elgg_extend_view('extensions/xmlns', 'extensions/photos/xmlns');

	// allow group members add photos to group albums
	elgg_register_plugin_hook_handler('container_permissions_check', 'object', 'tidypics_group_permission_override');
	elgg_register_plugin_hook_handler('permissions_check:metadata', 'object', 'tidypics_group_permission_override');

	// Extend livesearch page handler
	elgg_register_plugin_hook_handler('route', 'livesearch', 'tidypics_route_livesearch_handler', 50);

	// Notifications
	elgg_register_notification_event('object', 'album', array('create'));
	elgg_register_plugin_hook_handler('prepare', 'notification:create:object:album', 'tidypics_prepare_notifications');

	// Role profile widget integration
	elgg_register_plugin_hook_handler('get_dynamic_handlers', 'role_widgets', 'tidypics_register_dynamic_widget_handlers');

	// Register pagesetup event handler for menu items
	elgg_register_event_handler('pagesetup', 'system', 'tidypics_pagesetup');

	// Register album create event handler
	elgg_register_event_handler('create', 'album', 'tidypics_album_create_handler');

	// Register album update event handler
	elgg_register_event_handler('update', 'album', 'tidypics_album_update_handler');

	// Register album delete event handler
	elgg_register_event_handler('delete', 'album', 'tidypics_album_delete_handler');

	// Register actions
	$base_dir = elgg_get_plugins_path() . 'tidypics/actions/photos';
	elgg_register_action("photos/delete", "$base_dir/delete.php");

	elgg_register_action("photos/album/save", "$base_dir/album/save.php");
	elgg_register_action("photos/album/sort", "$base_dir/album/sort.php");
	elgg_register_action("photos/album/set_cover", "$base_dir/album/set_cover.php");
	elgg_register_action("photos/image/save", "$base_dir/image/save.php");
	elgg_register_action("photos/image/update", "$base_dir/image/update.php");
	elgg_register_action("photos/image/move", "$base_dir/image/move.php");

	// Ajax upload actions
	elgg_register_action("photos/upload", "$base_dir/image/ajax_upload.php", 'logged_in');
	elgg_register_action("photos/uploads_complete", "$base_dir/image/ajax_upload_complete.php", 'logged_in');
	elgg_register_action("photos/album/create", "$base_dir/album/ajax_create.php", 'logged_in');

	elgg_register_action("photos/image/tag", "$base_dir/image/tag.php");
	elgg_register_action("photos/image/untag", "$base_dir/image/untag.php");

	elgg_register_action("photos/batch/edit", "$base_dir/batch/edit.php");

	elgg_register_action("photos/admin/settings", "$base_dir/admin/settings.php", 'admin');
	elgg_register_action("photos/admin/create_thumbnails", "$base_dir/admin/create_thumbnails.php", 'admin');
	elgg_register_action("photos/admin/upgrade", "$base_dir/admin/upgrade.php", 'admin');

	// Ajax whitelist
	elgg_register_ajax_view('photos/ajax_upload');
	elgg_register_ajax_view('photos/ajax_comment');
	elgg_register_ajax_view('photos/move_image');
	elgg_register_ajax_view('forms/photos/album/list');
	elgg_register_ajax_view('photos/tagging/tags');
	elgg_register_ajax_view('photos/album_photos_lightbox');


	/** BATCH COMMENTS/LIKING FIXES **/

	// Extend batch comment river view
	//elgg_extend_view('river/elements/layout', 'photos/batch_comment', 1);

	// Hook into annotation create event
	elgg_register_event_handler('annotate', 'object', 'tidypics_batch_create_annotate_handler');
	
	// Hook into annotations delete event
	elgg_register_event_handler('delete', 'annotations', 'tidypics_batch_delete_annotations_handler');


	// Set some additional input to handle batch 'liking' (should translate to the photo)
	elgg_register_plugin_hook_handler('permissions_check:annotate', 'object', 'tidypics_batch_annotation_permissions_handler');
}

/**
 * Photos page routing handler:
 *
 * All photos:    photos/all
 * All albums:    photos/albums/all
 * My Photos:     photos/owner
 * My Albums:     photos/albums/owner
 * 
 * @todo 
 * - What about friends.. we don't use it but it's a standard elgg pattern
 * - From posts (similar to G+): photos/posts (maybe?) 
 *
 * @param array $page
 * @return bool
 */
function tidypics_page_handler($page) {

	$page_type = $page[0];
	$page_base = elgg_get_plugins_path() . 'tidypics/pages/photos';

	elgg_push_breadcrumb(elgg_echo('photos'), 'photos/all');

	// Load content for XHR requests
	if (elgg_is_xhr()) {
		switch ($page_type) {
			// All/default
			case 'all':
			case 'world':
			default:
				$params = tidypics_get_list_content('photos', $page_type);
				break;
			// Albums
			case 'albums':
				switch ($page[1]) {
					case 'owner':
						$user = get_user_by_username($page[2]);
						elgg_set_page_owner_guid($user->guid);
						$params = tidypics_get_list_content('albums', $page[1], $user->guid);
						break;
					case 'all':
					default:
						$params = tidypics_get_list_content('albums', $page[1]);
						break;
				}
				break;
			case 'album':
				$params = tidypics_get_view_album_content($page[1]);
				break;
			// Groups
			case 'group':
				if (isset($page[2]) && $page[2] == 'all') {
					$type = 'photos';
				} else {
					$type = 'albums';
				}
				$group = elgg_get_page_owner_entity();
				$params = tidypics_get_list_content($type, 'owner', $group->guid);
				break;
			// Photos
			case 'owner':
				$user = elgg_get_page_owner_entity();
				$params = tidypics_get_list_content('photos', $page_type, $user->guid);
				break;
			// Photo lightbox/view
			case "image":
			case "view":
				$params = tidypics_get_view_image_ajax_content($page[1]);
				echo $params['content'];
				return TRUE;
				break;
		}

		// Output special ajax view
		echo elgg_view('photos/ajax_content', $params);

		return TRUE;
	} else {
		// Load tidypics related JS
		elgg_load_js('tidypics');
		elgg_load_js('tidypics:upload');
		elgg_load_js('tidypics:tagging');
		elgg_load_js('tidypics:lightbox');

		// Load other JS
		elgg_load_js('jquery-waypoints');
		elgg_load_js('jquery-fancybox2');
		elgg_load_css('jquery-fancybox2');
		elgg_load_js('jquery.imgareaselect');
		elgg_load_js('elgg.autocomplete');
		elgg_load_js('jquery.ui.autocomplete.html');
    	elgg_load_js('elgg.autocomplete');
		elgg_load_js('jquery.ui.autocomplete.html');

		switch ($page_type) {
			case 'album':
				$params = tidypics_get_view_album_content($page[1]);
				break;
			case "image":
			case "view":
				if (get_input('classic', FALSE)) {
					$params = tidypics_get_view_image_content($page[1]);	
				} else {
					// Get image and album entities
					$image = get_entity($page[1]);
					$album = get_entity($image->container_guid);

					// Make sure we can access them
					if (!$image || !$album) {
						register_error(elgg_echo('noaccess'));
						$_SESSION['last_forward_from'] = current_page_url();
						forward('');
					}

					// Forward to albums to display lightbox
					forward('photos/album/' . $album->guid . '?lb=' . $image->guid);
				}
				break;
			// Editing
			case "edit": //edit image or album
				$entity = get_entity($page[1]);
				if (elgg_instanceof($entity, 'object', 'album')) {
					$params = tidypics_get_album_edit_content($page[1]);
				} else if (elgg_instanceof($entity, 'object', 'image')) {
					$params = tidypics_get_photo_edit_content($page[1]);
				} else {
					forward('photos');
				}		
				break;
			case "sort": // sort a photo album
				$params = tidypics_get_album_sort_content($page[1]);
				break;
			// Other pages
			case "thumbnail": // tidypics thumbnail
				set_input('guid', $page[1]);
				set_input('size', elgg_extract(2, $page, 'small'));
				require "$page_base/image/thumbnail.php";
				return TRUE;
				break;
			case "download": // download an image
				set_input('guid', $page[1]);
				set_input('disposition', elgg_extract(2, $page, 'attachment'));
				include "$page_base/image/download.php";
				break;
			default:
				$params['content'] = elgg_view('photos/content', array('page' => $page));
				break; 
		}

		// Photos sidebar
		$params['sidebar'] = ($params['sidebar'] ? $params['sidebar'] : elgg_view('photos/sidebar', array('page' => $page_type)));

		// Photos filter menu
		if (!$params['filter']) {
			$params['filter'] = elgg_view_menu('photos-filter', array(
				'class' => 'elgg-menu-hz elgg-menu-filter elgg-menu-filter-default',
				'sort_by' => 'priority',
			));
		}

		$body = elgg_view_layout($params['layout'] ? $params['layout'] : 'content', $params);

		echo elgg_view_page($params['title'], $body);
	}
	return TRUE;
}

/**
 * Add a menu item to an ownerblock
 */
function tidypics_owner_block_menu($hook, $type, $return, $params) {
	if (elgg_instanceof($params['entity'], 'user')) {
		$url = "photos/owner/{$params['entity']->username}";
		$item = new ElggMenuItem('photos', elgg_echo('photos'), $url);
		$return[] = $item;
	} else {
		if ($params['entity']->photos_enable != "no") {
			$url = "photos/group/{$params['entity']->guid}/all";
			$item = new ElggMenuItem('photos', elgg_echo('photos:group'), $url);
			$return[] = $item;
		}
	}

	return $return;
}

/**
 * Handler to add a photo tab to the tabbed profile
 */
function tidypics_profile_tab_hander($hook, $type, $value, $params) {
	if (elgg_is_logged_in()) {
		$value[] = 'photos_tagged';
	}
	return $value;
}

/**
 * Add Tidypics links/info to entity menu
 */
function tidypics_entity_menu_setup($hook, $type, $return, $params) {
	if (elgg_in_context('widgets')) {
		return $return;
	}

	$entity = $params['entity'];
	$handler = elgg_extract('handler', $params, false);
	if ($handler != 'photos') {
		return $return;
	}

	if (elgg_instanceof($entity, 'object', 'image')) {
		// Move to album link
		if ($entity->canEdit()) {
			$options = array(
				'name' => 'move_to_album',
				'text' => elgg_echo('photo:move_to_album'),
				'href' => elgg_get_site_url() . 'ajax/view/photos/move_image?entity_guid=' . $entity->guid,
				'link_class' => 'tidypics-move-lightbox',
				'priority' => 70,
			);
			$return[] = ElggMenuItem::factory($options);
		}

		// Set cover
		$album = $entity->getContainerEntity();
		$cover_guid = $album->getCoverImageGuid();
		if ($cover_guid != $entity->getGUID() && $album->canEdit()) {
			$url = 'action/photos/album/set_cover'
				. '?image_guid=' . $entity->getGUID()
				. '&album_guid=' . $album->getGUID();

			$params = array(
				'href' => $url,
				'text' => elgg_echo('album:cover_link'),
				'is_action' => true,
				'is_trusted' => true,
				'confirm' => elgg_echo('album:cover')
			);
			$text = elgg_view('output/url', $params);

			$options = array(
				'name' => 'set_cover',
				'text' => $text,
				'href' => false,
				'priority' => 80,
			);
			$return[] = ElggMenuItem::factory($options);
		}

		// Person tagging
		if (elgg_get_plugin_setting('tagging', 'tidypics')) {
			$options = array(
				'name' => 'tagging',
				'text' => elgg_echo('tidypics:actiontag'),
				'href' => '#',
				'title' => elgg_echo('tidypics:tagthisphoto'),
				'rel' => 'photo-tagging',
				'priority' => 80,
			);
			$return[] = ElggMenuItem::factory($options);
		}

		// Add album info to entity menu
		$album_label = elgg_echo('tidypics:inalbum');

		$options = array(
			'name' => 'album-info',
			'text' => $album_label . elgg_get_excerpt($album->getTitle(), 150),
			'href' =>  $album->getURL(),
			'priority' => 1,
		);
		$return[] = ElggMenuItem::factory($options);
	}

	// only show these options if there are images
	if (elgg_instanceof($entity, 'object', 'album') && $entity->getSize() > 0) {
		// @todo slideshow

		if ($entity->canEdit()) {
			$options = array(
				'name' => 'sort',
				'text' => elgg_echo('album:sort'),
				'href' => "photos/sort/" . $entity->getGUID(),
				'priority' => 90,
			);
			$return[] = ElggMenuItem::factory($options);
		}
	}

	return $return;
}

/**
 * Set up photo listing filter menu
 */
function tidypics_photo_list_filter_menu_setup($hook, $type, $return, $params) {
	$container_guid = $params['container_guid'];
	$type = $params['type'];

	$logged_in = elgg_get_logged_in_user_guid();

	$tag_label = elgg_echo('tidypics:tag');
	$tag_input = elgg_view('input/autocomplete', array(
		'name' => 'tag',
		'data-match_on' => 'tags',
		'value' => get_input('tag'),
	));

	$filter_label = elgg_echo('tidypics:filterby', array($type));

	// Filter by label
	$options = array(
		'name' => 'photos-listing-filterby-label',
		'text' => "<label>{$filter_label}</label>",
		'href' => false,
		'priority' => 1,
	);
	$return[] = ElggMenuItem::factory($options);

	// Search by tag label
	$options = array(
		'name' => 'photos-listing-tag-label',
		'text' => "<label>{$tag_label}:</label>",
		'href' => false,
		'priority' => 100,
	);
	$return[] = ElggMenuItem::factory($options);

	// Search by tag input
	$options = array(
		'name' => 'photos-listing-tag-input',
		'text' => $tag_input,
		'href' => false,
		'priority' => 101,
	);
	$return[] = ElggMenuItem::factory($options);

	// Don't show people tag when listing albums
	if ($type != 'albums') {
		$people_tag_label = elgg_echo('tidypics:peopletag');
		$people_tag_input = elgg_view('input/autocomplete', array(
			'name' => 'people_tag',
			'data-match_on' => 'users',
			'value' => get_input('people_tag'),
		));

		// Search by people tag label
		$options = array(
			'name' => 'photos-listing-people-tag-label',
			'text' => "<label>{$people_tag_label}:</label>",
			'href' => false,
			'priority' => 400,
		);
		$return[] = ElggMenuItem::factory($options);

		// Search by people tag input
		$options = array(
			'name' => 'photos-listing-people-tag-input',
			'text' => $people_tag_input,
			'href' => false,
			'priority' => 401,
		);
		$return[] = ElggMenuItem::factory($options);
	}

	// Only show owner and role inputs when not viewing a container
	if (!$container_guid) {

		$owner_label = elgg_echo('tidypics:owner');
		$owner_input = elgg_view('input/autocomplete', array(
			'name' => 'owner',
			'data-match_on' => 'users',
			'value' => get_input('owner'),
		));

		// Search by owner label
		$options = array(
			'name' => 'photos-listing-owner-label',
			'text' => "<label>{$owner_label}:</label>",
			'href' => false,
			'priority' => 200,
		);
		$return[] = ElggMenuItem::factory($options);

		// Search by owner input
		$options = array(
			'name' => 'photos-listing-owner-input',
			'text' => $owner_input,
			'href' => false,
			'priority' => 201,
		);
		$return[] = ElggMenuItem::factory($options);
	}

	return $return;
}

/**
 * Set up photo listing filter menu
 */
function tidypics_photo_list_sort_menu_setup($hook, $type, $return, $params) {
	$container_guid = $params['container_guid'];
	$type = $params['type'];

	$logged_in = elgg_get_logged_in_user_guid();

	$order_label = elgg_echo('tidypics:order');

	$options =  array(
		'date' => elgg_echo('tidypics:date'),
		'recentcomments' => elgg_echo('tidypics:recentcomments'),
		'numcomments' => elgg_echo('tidypics:numcomments'),
	);

	if ($type != 'albums') {
		$options['views'] = elgg_echo('tidypics:views');
	}

	$order_input = elgg_view('input/dropdown', array(
		'id' => 'tidypics-list-order-input',
		'name' => 'order_by',
		'value' => get_input('order_by'),
		'options_values' => $options,
	));

	// Order by label
	$options = array(
		'name' => 'tidypics-list-order-label',
		'text' => "<label>{$order_label}:</label>",
		'href' => false,
		'priority' => 100,
	);
	$return[] = ElggMenuItem::factory($options);

	// Order by input
	$options = array(
		'name' => 'tidypics-list-order-input',
		'text' => $order_input,
		'href' => false,
		'priority' => 101,
	);
	$return[] = ElggMenuItem::factory($options);

	// Sort order input
	$sort_order  = get_input('sort_order', 'desc'); // Get sort order, default is desc
	$options = array(
		'data-sort_order' => $sort_order == 'asc' ? 'desc' : 'asc' ,
		'data-current_value' => $sort_order,
		'name' => 'tidypics-list-sort-order',
		'text' => "<label>" . ($sort_order == 'asc' ? elgg_echo('tidypics:desc') : elgg_echo('tidypics:asc')) . "</label>",
		'href' => '#',
		'priority' => 200,
	);
	$return[] = ElggMenuItem::factory($options);

	return $return;
}

/**
 * Override permissions for group albums
 *
 * 1. To write to a container (album) you must be able to write to the owner of the container (odd)
 * 2. We also need to change metadata on the album
 *
 * @param string $hook
 * @param string $type
 * @param bool   $result
 * @param array  $params
 * @return mixed
 */
function tidypics_group_permission_override($hook, $type, $result, $params) {	
	if (isset($params['container'])) {
		$album = $params['container'];
	} else {
		$album = $params['entity'];
	}

	if (elgg_instanceof($album, 'object', 'album')) {
		return $album->getContainerEntity()->canWriteToContainer();
	}

	return $return;
}

/**
 * Extend livesearch page handler to include tags
 *
 * @param string $hook
 * @param string $type
 * @param bool   $return
 * @param array  $params
 * @return mixed
 */
function tidypics_route_livesearch_handler($hook, $type, $return, $params) {
	$match_on = get_input('match_on', 'all');
	
	// Prevent metadata menu from appearing in livesearch
	elgg_push_context('owner_block');

	if ($match_on == 'tags') {
		$term = get_input('term');

		// Only grab tags similar to the input
		$wheres[] = "msv.string like '%$term%'";	

		// Get site tags
		$site_tags = elgg_get_tags(array(
			'threshold' => 1, 
			'limit' => 15,
			'wheres' => $wheres,
		));

		$dbprefix = elgg_get_config('dbprefix');

		$tags_array = array();

		foreach ($site_tags as $site_tag) {
			$tag = array();
			$tags_array[] = array(
				'type' => 'tag',
				'name' => $site_tag->tag,
				'value' => $site_tag->tag,
				'label' => "<div style='width: 100%; cursor: pointer;'>{$site_tag->tag}</div>",
			);
		}
		
		header("Content-Type: application/json");
		echo json_encode(array_values($tags_array));
		return FALSE;
	}

	return $return;
}

/**
 * Prepare notification message(s) for tidypics entities
 *
 * @param string                          $hook         Hook name
 * @param string                          $type         Hook type
 * @param Elgg_Notifications_Notification $notification The notification to prepare
 * @param array                           $params       Hook parameters
 * @return Elgg_Notifications_Notification
 */
function tidypics_prepare_notifications($hook, $type, $notification, $params) {
	$entity = $params['event']->getObject();
	$owner = $params['event']->getActor();
	$recipient = $params['recipient'];
	$language = $params['language'];
	$method = $params['method'];

	// Album notifications
	if (elgg_instanceof($object, 'object', 'album')) {
		// First upload
		if ($entity->first_upload) {
			$notification->subject = elgg_echo('tidypics:newalbum_subject');

			$notification->body = elgg_echo('tidypics:newalbum', array(
				$owner->name, 
				$entity->title,
				$entity->description,
				$entity->getURL()
			), $language);

			$notification->summary = elgg_echo('tidypics:newalbum_subject', array($entity->title), $language);

			return $notification;
		} else if ($entity->shouldNotify()) { // Album update (if needed)
			$notification->subject = elgg_echo('tidypics:updatealbum_subject');

			$notification->body = elgg_echo('tidypics:updatealbum', array(
				$owner->name, 
				$entity->title,
				$entity->getURL()
			), $language);

			$notification->summary = elgg_echo('tidypics:updatealbum_subject', array($entity->title), $language);

			return $notification;
		}
	}
	return FALSE;
}

/**
 * Register roleprofilewidgets as dynamic handlers to provide individual titles
 * for user widgets
 *
 * @param string $hook
 * @param string $type
 * @param bool   $return
 * @param array  $params
 * @return mixed
 */
function tidypics_register_dynamic_widget_handlers($hook, $type, $return, $params) {
	$user = $params['user'];
	$return['profile_tagged'] = elgg_echo('tidypics:photosof', array($user->name));
	return $return;
}


/**
 * Tidypics pagesetup event handler
 *
 * @param string $event  Event name
 * @param string $type   Object type
 * @param mixed  $object Object
 *
 * @return bool
 */
function tidypics_pagesetup($event, $type, $object) {
	// Set up menu items for photos context
	if (elgg_in_context('photos')) {

		$user = elgg_get_logged_in_user_entity();

		$page_owner = elgg_get_page_owner_entity();

		// Create non-group items
		if (!elgg_instanceof($page_owner, 'group')) {
			// All site photos filter item
			$params = array(
				'name' => 'allphotos',
				'text' => elgg_echo('photos:all'),
				'href' => 'photos/all',
				'priority' => 100,
			);
			elgg_register_menu_item('photos-filter', $params);

			// All site albums filter item
			$params = array(
				'name' => 'allalbums',
				'text' => elgg_echo('albums:all'),
				'href' => 'photos/albums/all',
				'priority' => 200,
			);
			elgg_register_menu_item('photos-filter', $params);

			// My photos/albums only for logged in
			if ($user) {
				// My photos filter item
				$params = array(
					'name' => 'myphotos',
					'text' => elgg_echo('photos:mine'),
					'href' => "photos/owner/{$user->username}",
					'priority' => 300,
				);
				elgg_register_menu_item('photos-filter', $params);

				// My albums filter item
				$params = array(
					'name' => 'myalbums',
					'text' => elgg_echo('albums:mine'),
					'href' => "photos/albums/owner/{$user->username}",
					'priority' => 400,
				);
				elgg_register_menu_item('photos-filter', $params);
			} 
		} else {
			// My photos filter item
			$params = array(
				'name' => 'groupphotos',
				'text' => elgg_echo('photos:group'),
				'href' => "photos/group/{$page_owner->guid}/all",
				'priority' => 100,
			);
			elgg_register_menu_item('photos-filter', $params);

			// My albums filter item
			$params = array(
				'name' => 'groupalbums',
				'text' => elgg_echo('albums:group'),
				'href' => "photos/group/{$page_owner->guid}/albums/all",
				'priority' => 200,
			);
			elgg_register_menu_item('photos-filter', $params);
		}
	}
}

/**
 * Tidypics Album Create Event Handler
 *
 * @param string $event  Event name
 * @param string $type   Object type
 * @param mixed  $object Object
 *
 * @return bool
 */
function tidypics_album_create_handler($event, $type, $object) {
	//
}

/**
 * Tidypics Album Update Event Handler
 *
 * @param string $event  Event name
 * @param string $type   Object type
 * @param mixed  $object Object
 *
 * @return bool
 */
function tidypics_album_update_handler($event, $type, $object) {
	// Update images if appropriate
	if (elgg_in_context('tidypics_update_images_access') ||
		elgg_in_context('tidypics_update_images_tags')) {

		// Get album images
		$images = elgg_get_entities(array(
			'type' => 'object',
			'subtype' => 'image',
			'container_guids' => $object->guid,
			'limit' => 0
		));

		foreach ($images as $image) {
			// Update access
			if (elgg_in_context('tidypics_update_images_access')) {
				$image->access_id = $object->access_id;
			}

			// Update tags
			if (elgg_in_context('tidypics_update_images_tags')) {
				// Merging album and image tags, this method retains the original album tags (do we want that?)
				tidypics_merge_album_image_tags($object, $image);
			}

			$image->save();
		}
	}
}

/**
 * Tidypics Album Delete Event Handler
 *
 * @param string $event  Event name
 * @param string $type   Object type
 * @param mixed  $object Object
 *
 * @return bool
 */
function tidypics_album_delete_handler($event, $type, $object) {
	//
}


/**
 * Hook into the annotate permissions to sort out batch liking
 * 
 * @param string $hook
 * @param string $type
 * @param bool   $result
 * @param array  $params
 * @return mixed
 */
function tidypics_batch_annotation_permissions_handler($hook, $type, $result, $params) {
	if (elgg_instanceof($params['entity'], 'object', 'tidypics_batch') && $params['annotation_name'] == 'likes' && $result) {
		// Set a flag here so we know we're liking a batch
		set_input('like_batch', 'like_batch');
	}
	return $result;	
}


/**
 * Tidypics batch create annotation handler
 * - to keep likes and comments in sync with batches
 *
 * @param string $event  Event name
 * @param string $type   Object type
 * @param mixed  $params Params
 *
 * @return bool
 */
function tidypics_batch_create_annotate_handler($event, $type, $params) {
	if (elgg_instanceof($params, 'object', 'tidypics_batch')) {
		$user = elgg_get_logged_in_user_entity();

		// Batch like
		if (get_input('like_batch') == 'like_batch') {
			// Get batch images
			$images = elgg_get_entities_from_relationship(array(
				'relationship' => 'belongs_to_batch',
				'relationship_guid' => $params->getGUID(),
				'inverse_relationship' => true,
				'types' => array('object'),
				'subtypes' => array('image'),
				'limit' => 0,
				'offset' => 0,
				'count' => false,
			));

			$user = elgg_get_logged_in_user_entity();

			// Like each image in the batch
			foreach ($images as $image) {
				// Make sure we haven't liked already
				if (!elgg_annotation_exists($image->guid, 'likes')) {
					$annotation = create_annotation(
						$image->guid,
						'likes',
						"likes",
						"",
						$user->guid,
						$image->access_id
					);
				}
			}		
		} else { // Batch comment
			$album = get_entity($params->container_guid);

			$comment_text = get_input('generic_comment');

			$annotation = create_annotation($album->guid,
				'generic_comment',
				$comment_text,
				"",
				$user->guid,
				$album->access_id
			);
		}	
	}
	return TRUE;
}

/**
 * Tidypics batch delete annotation handler
 * - to keep likes and comments in sync with batches
 *
 * @param string $event  Event name
 * @param string $type   Object type
 * @param mixed  $params Params
 *
 * @return bool
 */
function tidypics_batch_delete_annotations_handler($event, $type, $params) {
	// Get entity
	$entity = get_entity($params->entity_guid);

	if (elgg_instanceof($entity, 'object', 'tidypics_batch')) {
		if ($params->name == 'likes') {
			// Get batch images
			$images = elgg_get_entities_from_relationship(array(
				'relationship' => 'belongs_to_batch',
				'relationship_guid' => $entity->getGUID(),
				'inverse_relationship' => true,
				'types' => array('object'),
				'subtypes' => array('image'),
				'limit' => 0,
				'offset' => 0,
				'count' => false,
			));

			$user = elgg_get_logged_in_user_entity();

			// Delete like for each image in batch
			foreach ($images as $image) {
				$image_guids[] = $image->guid;
			}

			if (count($image_guids)) {
				// Delete likes
				elgg_delete_annotations(array(
					'guids' => $image_guids,
					'annotation_owner_guid' => $user->guid,
					'annotation_name' => 'likes',
				));
			}
		}
	}
	return TRUE;
}