<?php
/**
 * Tidypics Photo Gallery plugin
 *
 * @author Cash Costello
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU General Public License v2
 *
 * @todo
 * - River entries/annotating (handler 'batch' comments etc)
 * - Clean languages
 * - Upgrades need to stay.. need to check against our old fork
 * - Fix plugin ordering issue (jquery file upload)
 * - Need to sort out 'actions' menu
 * - Move lightbox js to own library?
 * - Add more documentation (JS mostly)
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
	elgg_register_simplecache_view('js/photos/tidypics');
	elgg_register_js('tidypics', $js, 'footer');

	$js = elgg_get_simplecache_url('js', 'photos/tagging');
	elgg_register_simplecache_view('js/photos/tagging');
	elgg_register_js('tidypics:tagging', $js, 'footer');
	
	$js = elgg_get_simplecache_url('js', 'photos/uploading');
	elgg_register_simplecache_view('js/photos/uploading');
	elgg_register_js('tidypics:uploading', $js, 'footer');

	// Register jquery-waypoints js lib
	$js = elgg_get_simplecache_url('js', 'waypoints');
	elgg_register_simplecache_view('js/waypoints');
	elgg_register_js('jquery-waypoints', $js);

	// Register jquery-file-upload js lib
	$js = elgg_get_simplecache_url('js', 'jquery_file_upload');
	elgg_register_simplecache_view('js/jquery_file_upload');
	elgg_register_js('jquery-file-upload', $js);
	elgg_load_js('jquery-file-upload');

	// Register jquery-fancybox2 js lib
	$js = elgg_get_simplecache_url('js', 'fancybox2');
	elgg_register_simplecache_view('js/fancybox2');
	elgg_register_js('jquery-fancybox2', $js);

	// Register jquery-fancybox2 css
	$css = elgg_get_simplecache_url('css', 'fancybox2');
	elgg_register_simplecache_view('css/fancybox2');
	elgg_register_css('jquery-fancybox2', $css);

	// Add photos link to owner block/hover menus
	elgg_register_plugin_hook_handler('register', 'menu:owner_block', 'tidypics_owner_block_menu');

	// Add admin menu item
	elgg_register_admin_menu_item('configure', 'photos', 'settings');

	// Register for search
	elgg_register_entity_type('object', 'image');
	elgg_register_entity_type('object', 'album');

	// Register for the entity menu
	elgg_register_plugin_hook_handler('register', 'menu:entity', 'tidypics_entity_menu_setup');

	// Register group option
	add_group_tool_option('photos', elgg_echo('tidypics:enablephotos'), true);
	elgg_extend_view('groups/tool_latest', 'photos/group_module');

	// Register widgets
	elgg_register_widget_type('album_view', elgg_echo("tidypics:widget:albums"), elgg_echo("tidypics:widget:album_descr"), 'profile');
	elgg_register_widget_type('latest_photos', elgg_echo("tidypics:widget:latest"), elgg_echo("tidypics:widget:latest_descr"), 'profile');

	// RSS extensions for embedded media
	elgg_extend_view('extensions/xmlns', 'extensions/photos/xmlns');

	// allow group members add photos to group albums
	elgg_register_plugin_hook_handler('container_permissions_check', 'object', 'tidypics_group_permission_override');
	elgg_register_plugin_hook_handler('permissions_check:metadata', 'object', 'tidypics_group_permission_override');

	// @todo notifications
	register_notification_object('object', 'album', elgg_echo('tidypics:newalbum_subject'));
	elgg_register_plugin_hook_handler('notify:entity:message', 'object', 'tidypics_notify_message');

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

	// Ajax upload actions
	elgg_register_action("photos/upload", "$base_dir/image/ajax_upload.php", 'logged_in');
	elgg_register_action("photos/uploads_complete", "$base_dir/image/ajax_upload_complete.php", 'logged_in');

	elgg_register_action("photos/image/tag", "$base_dir/image/tag.php");
	elgg_register_action("photos/image/untag", "$base_dir/image/untag.php");

	elgg_register_action("photos/batch/edit", "$base_dir/batch/edit.php");

	elgg_register_action("photos/admin/settings", "$base_dir/admin/settings.php", 'admin');
	elgg_register_action("photos/admin/create_thumbnails", "$base_dir/admin/create_thumbnails.php", 'admin');
	elgg_register_action("photos/admin/upgrade", "$base_dir/admin/upgrade.php", 'admin');

	// Ajax whitelist
	elgg_register_ajax_view('photos/ajax_upload');
	elgg_register_ajax_view('photos/ajax_comment');
	elgg_register_ajax_view('photos/tagging/tags');
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

	elgg_push_breadcrumb('photos', 'photos/all');

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
				$group = elgg_get_page_owner_entity();
				$params = tidypics_get_list_content('albums', 'owner', $group->guid);
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
		// Photos sidebar
		$params['sidebar'] = elgg_view('photos/sidebar');

		// Output special ajax view
		echo elgg_view('photos/ajax_content', $params);

		return TRUE;
	} else {

		elgg_load_js('tidypics');
		elgg_load_js('jquery-waypoints');
		elgg_load_js('jquery-fancybox2');
		elgg_load_css('jquery-fancybox2');
		elgg_load_js('tidypics:tagging');
		elgg_load_js('jquery.imgareaselect');
		elgg_load_js('elgg.autocomplete');
		elgg_load_js('jquery.ui.autocomplete.html');
		elgg_load_js('tinymce');
    	elgg_load_js('elgg.tinymce');

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
		$params['sidebar'] = elgg_view('photos/sidebar');

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
			$text = elgg_view('output/confirmlink', $params);

			$options = array(
				'name' => 'set_cover',
				'text' => $text,
				'priority' => 80,
			);
			$return[] = ElggMenuItem::factory($options);
		}

		if (elgg_get_plugin_setting('view_count', 'tidypics')) {
			$view_info = $entity->getViewInfo();
			$text = elgg_echo('tidypics:views', array((int)$view_info['total']));
			$options = array(
				'name' => 'views',
				'text' => "<span>$text</span>",
				'href' => false,
				'priority' => 90,
			);
			$return[] = ElggMenuItem::factory($options);
		}

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
			'text' => $album_label . $album->getTitle(),
			'href' =>  $album->getURL(),
			'section' => 'info',
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
	if (get_input('action') == 'photos/upload' || get_input('action') == 'photos/uploads_complete') {
		if (isset($params['container'])) {
			$album = $params['container'];
		} else {
			$album = $params['entity'];
		}

		if (elgg_instanceof($album, 'object', 'album')) {
			return $album->getContainerEntity()->canWriteToContainer();
		}
	}
}

/**
 * Create the body of the notification message
 *
 * Does not run if a new album without photos
 * 
 * @param string $hook
 * @param string $type
 * @param bool   $result
 * @param array  $params
 * @return mixed
 */
function tidypics_notify_message($hook, $type, $result, $params) {
	$entity = $params['entity'];
	$to_entity = $params['to_entity'];
	$method = $params['method'];
	
	if (elgg_instanceof($entity, 'object', 'album')) {
		if ($entity->new_album) {
			// stops notification from being sent
			return false;
		}
		
		if ($entity->first_upload) {
			$descr = $entity->description;
			$title = $entity->getTitle();
			$owner = $entity->getOwnerEntity();
			return elgg_echo('tidypics:newalbum', array($owner->name))
					. ': ' . $title . "\n\n" . $descr . "\n\n" . $entity->getURL();
		} else {
			if ($entity->shouldNotify()) {
				$descr = $entity->description;
				$title = $entity->getTitle();
				$owner = $entity->getOwnerEntity();

				return elgg_echo('tidypics:updatealbum', array($owner->name, $title)) . ': ' . $entity->getURL();
			}
		}
	}
	
	return null;
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
	$user = elgg_get_logged_in_user_entity();

	// Set up menu items for photos context
	if (elgg_in_context('photos')) {

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