<?php
/**
 * Tidypics Helper Library
 *
 * @package TidypicsCommon
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU General Public License v2
 */

/** CONTENT FUNCTIONS **/

/**
 * Get content for photos/albums listing
 *
 * @param $type            what are we listing? either photos or albums
 * @param $page_type       page type (owner, all, group, etc)
 * @param $container_guid  container guid for photos/albums (optional)
 * @return array
 */
function tidypics_get_list_content($type, $page_type, $container_guid = NULL) {
	$params = array();

	$logged_in_user_guid = elgg_get_logged_in_user_guid();
	$owner = get_entity($container_guid);

	$options = array();

	if ($page_type == 'owner' && $container_guid)  {
		$params['title'] = elgg_echo("{$type}:owner", array($owner->name));

		if (elgg_instanceof($owner, 'group')) {
			$options['container_guid'] = $container_guid;
			elgg_push_breadcrumb($owner->name, "photos/group/$owner->guid/all");
		} else {
			$options['owner_guid'] = $container_guid;
			elgg_push_breadcrumb($owner->name, "photos/owner/$owner->username");
		}
		elgg_push_breadcrumb(elgg_echo("{$type}"));

	} else {
		$params['title'] = elgg_echo("{$type}:allsite");
		elgg_push_breadcrumb(elgg_echo("{$type}:all"));
	}

	if ($type == 'albums') {
		// Make sure we can create a new album here (check for group perms)
		if ($logged_in_user_guid && (!elgg_instanceof($owner, 'group') || $owner->canWriteToContainer($logged_in_user_guid))) {
			$options['enable_upload'] = TRUE;
		}
		$content = tidypics_view_album_list($options);
	} else {
		if ($logged_in_user_guid) {
			$options['enable_upload'] = TRUE;
		}
		$content = tidypics_view_photo_list($options);
 	}

	$params['content'] = $content;

	return $params;
}

/**
 * Build content to view an album
 *
 * @param int $album_guid Album guid
 * @return array
 */
function tidypics_get_view_album_content($album_guid) {
	$params['filter'] = ' ';

	// Get the album entity
	$album = get_entity($album_guid);
	if (!$album) {
		register_error(elgg_echo('noaccess'));
		$_SESSION['last_forward_from'] = current_page_url();
		forward('');
	}

	elgg_set_page_owner_guid($album->getContainerGUID());

	$owner = elgg_get_page_owner_entity();

	group_gatekeeper();

	$params['title'] = elgg_echo($album->getTitle());

	if (elgg_instanceof($owner, 'group')) {
		elgg_push_breadcrumb($owner->name, "photos/group/$owner->guid/all");
	} else {
		elgg_push_breadcrumb($owner->name, "photos/owner/$owner->username");
	}

	elgg_push_breadcrumb($album->getTitle());

	if (elgg_is_xhr()) {
		$options = array(
			'container_guid' => $album->guid,
		);

		if ($album->getContainerEntity()->canWriteToContainer(elgg_get_logged_in_user_guid())) {
			$options['enable_upload'] = TRUE;
		}

		$params['content'] = tidypics_view_photo_list($options);
		
	} else {
		$params['content'] = elgg_view_entity($album, array('full_view' => TRUE));
	}

	return $params;
}

/**
 * Build content to view an image
 *
 * @param int $photo_guid Photo guid
 * @return array
 */
function tidypics_get_view_image_contnet($photo_guid) {
	$params['filter'] = ' ';

	// Get the photo entity
	$photo = get_entity($photo_guid);
	if (!$photo) {
		register_error(elgg_echo('noaccess'));
		$_SESSION['last_forward_from'] = current_page_url();
		forward('');
	}

	// Add view annotation
	$photo->addView();

	// Load tagging @todo fix or update
	if (elgg_get_plugin_setting('tagging', 'tidypics')) {
		elgg_load_js('tidypics:tagging');
		elgg_load_js('jquery.imgareaselect');
	}

	// Set page owner based on owner of photo album (shouldn't this always be an album?)
	$album = $photo->getContainerEntity();
	if ($album) {
		elgg_set_page_owner_guid($album->getContainerGUID());
	}
	$owner = elgg_get_page_owner_entity();

	group_gatekeeper();

	$params['title'] = elgg_echo($photo->getTitle());

	if (elgg_instanceof($owner, 'group')) {
		elgg_push_breadcrumb($owner->name, "photos/group/$owner->guid/all");
	} else {
		elgg_push_breadcrumb($owner->name, "photos/owner/$owner->username");
	}

	elgg_push_breadcrumb($album->getTitle(), $album->getURL());
	elgg_push_breadcrumb($params['title']);

	if (elgg_get_plugin_setting('download_link', 'tidypics')) {
		// add download button to title menu
		elgg_register_menu_item('title', array(
			'name' => 'download',
			'href' => "photos/download/$photo_guid",
			'text' => elgg_echo('image:download'),
			'link_class' => 'elgg-button elgg-button-action',
		));
	}

	$params['content'] = elgg_view_entity($photo, array('full_view' => TRUE));

	return $params;
}

/**
 * Build edit photo content
 *
 * @param $photo_guid Photo guid
 * @return array
 */
function tidypics_get_photo_edit_content($photo_guid) {
	gatekeeper();

	$params['filter'] = ' ';

	$guid = (int) get_input('guid');

	// Get the photo entity
	$photo = get_entity($photo_guid);
	if (!$photo) {
		register_error(elgg_echo('noaccess'));
		$_SESSION['last_forward_from'] = current_page_url();
		forward('');
	}

	// Make sure we can edit the photo
	if (!$photo->canEdit()) {
		register_error(elgg_echo('tidypics:nopermission'));
		forward($photo->getContainerEntity()->getURL());
	}

	$album = $photo->getContainerEntity();

	elgg_set_page_owner_guid($album->getContainerGUID());
	$owner = elgg_get_page_owner_entity();

	group_gatekeeper();

	$params['title'] = elgg_echo('image:edit');

	// Set up breadcrumbs
	elgg_push_breadcrumb(elgg_echo('photos'), "photos/all");
	if (elgg_instanceof($owner, 'user')) {
		elgg_push_breadcrumb($owner->name, "photos/owner/$owner->username");
	} else {
		elgg_push_breadcrumb($owner->name, "photos/group/$owner->guid/all");
	}
	elgg_push_breadcrumb($album->getTitle(), $album->getURL());
	elgg_push_breadcrumb($photo->getTitle(), $photo->getURL());
	elgg_push_breadcrumb($params['title']);

	$vars = tidypics_prepare_form_vars($photo);
	$params['content'] = elgg_view_form('photos/image/save', array('method' => 'post'), $vars);

	return $params;
}

/**
 * Build edit photo content
 *
 * @param $album_guid Album guid
 * @return array
 */
function tidypics_get_album_edit_content($album_guid) {
	gatekeeper(); 

	$params['filter'] = ' ';

	$guid = (int) get_input('guid');

	// Get the photo entity
	$album = get_entity($album_guid);
	if (!$album) {
		register_error(elgg_echo('noaccess'));
		$_SESSION['last_forward_from'] = current_page_url();
		forward('');
	}

	// Make sure we can edit the photo
	if (!$album->canEdit()) {
		register_error(elgg_echo('tidypics:nopermission'));
		forward($album->getURL());
	}

	elgg_set_page_owner_guid($album->getContainerGUID());
	$owner = elgg_get_page_owner_entity();

	group_gatekeeper();

	$params['title'] = elgg_echo('album:edit');

	// Set up breadcrumbs
	elgg_push_breadcrumb(elgg_echo('photos'), "photos/all");
	if (elgg_instanceof($owner, 'user')) {
		elgg_push_breadcrumb($owner->name, "photos/owner/$owner->username");
	} else {
		elgg_push_breadcrumb($owner->name, "photos/group/$owner->guid/all");
	}
	elgg_push_breadcrumb($album->getTitle(), $album->getURL());
	elgg_push_breadcrumb($params['title']);

	$vars = tidypics_prepare_form_vars($album);

	$params['content'] = elgg_view_form('photos/album/save', array('method' => 'post'), $vars);

	return $params;
}

/**
 * Build album sort content
 *
 * @param $album_guid
 * @return array
 */
function tidypics_get_album_sort_content($album_guid) {
	gatekeeper(); 

	$params['filter'] = ' ';

	$guid = (int) get_input('guid');

	// Get the photo entity
	$album = get_entity($album_guid);
	if (!$album) {
		register_error(elgg_echo('noaccess'));
		$_SESSION['last_forward_from'] = current_page_url();
		forward('');
	}

	// Make sure we can edit the photo
	if (!$album->canEdit()) {
		register_error(elgg_echo('tidypics:nopermission'));
		forward($album->getURL());
	}

	elgg_set_page_owner_guid($album->getContainerGUID());
	$owner = elgg_get_page_owner_entity();

	group_gatekeeper();

	$params['title'] = elgg_echo('tidypics:sort', array($album->getTitle()));

	// set up breadcrumbs
	elgg_push_breadcrumb(elgg_echo('photos'), 'photos/all');
	if (elgg_instanceof($owner, 'group')) {
		elgg_push_breadcrumb($owner->name, "photos/group/$owner->guid/all");
	} else {
		elgg_push_breadcrumb($owner->name, "photos/owner/$owner->username");
	}
	elgg_push_breadcrumb($album->getTitle(), $album->getURL());
	elgg_push_breadcrumb(elgg_echo('album:sort'));

	if ($album->getSize()) {
		$params['content'] = elgg_view_form('photos/album/sort', array(), array('album' => $album));
	} else {
		$params['content'] = elgg_echo('tidypics:sort:no_images');
	}

	return $params;
}

/**
 * Tidypics view album list
 * 
 * @param $options Entity getter options
 */
function tidypics_view_album_list(array $options = array()) {
	// Entity options
	$defaults = array(
		'type' => 'object',
		'subtype' => 'album',
		'limit' => 24,
		'offset' => get_input('offset', 0),
	);

	if ($options['enable_upload'] && !$defaults['offset']) {
		$defaults['limit'] = 23;
	}

	$options = array_merge($defaults, (array)$options);

	$options['count'] = TRUE;

	$count = elgg_get_entities($options);

	unset($options['count']);

	$albums = elgg_get_entities($options);

	$options['items'] = $albums;
	$options['count'] = $count;

	return elgg_view('photos/album_list', $options);
}

/**
 * Tidypics view photo list
 * 
 * @param $options Entity getter options
 */
function tidypics_view_photo_list(array $options = array()) {
	// Entity options
	$defaults = array(
		'type' => 'object',
		'subtype' => 'image',
		'limit' => 24,
		'offset' => get_input('offset', 0),
	);

	if ($options['enable_upload'] && !$defaults['offset']) {
		$defaults['limit'] = 23;
	}

	$options = array_merge($defaults, (array)$options);

	// If we have an album container, get its sorted list of images
	if ($options['container_guid'] && elgg_instanceof($album = get_entity($options['container_guid']), 'object', 'album')) {
		$count = $album->getSize();
		$photos = $album->getImages($options['limit'], $options['offset']);
	} else {
		$options['count'] = TRUE;
		$count = elgg_get_entities($options);
		unset($options['count']);
		$photos = elgg_get_entities($options);
	}

	$options['items'] = $photos;
	$options['count'] = $count;

	return elgg_view('photos/photo_list', $options);
}

/**
 * Prepare vars for a form, pulling from an entity or sticky forms.
 * 
 * @param type $entity
 * @return type
 */
function tidypics_prepare_form_vars($entity = null) {

	// input names => defaults
	$values = array(
		'title' => '',
		'description' => '',
		'access_id' => ACCESS_DEFAULT,
		'tags' => '',
		'container_guid' => elgg_get_page_owner_guid(),
		'guid' => null,
		'entity' => $entity,
	);

	if ($entity) {
		foreach (array_keys($values) as $field) {
			if (isset($entity->$field)) {
				$values[$field] = $entity->$field;
			}
		}
	}

	if (elgg_is_sticky_form('tidypics')) {
		$sticky_values = elgg_get_sticky_values('tidypics');
		foreach ($sticky_values as $key => $value) {
			$values[$key] = $value;
		}
	}

	elgg_clear_sticky_form('tidypics');

	return $values;
}

/**
 * Returns available image libraries.
 * 
 * @return string
 */
function tidypics_get_image_libraries() {
	$options = array();
	if (extension_loaded('gd')) {
		$options['GD'] = 'GD';
	}

	if (extension_loaded('imagick')) {
		$options['ImageMagickPHP'] = 'imagick PHP extension';
	}

	$disablefunc = explode(',', ini_get('disable_functions'));
	if (is_callable('exec') && !in_array('exec', $disablefunc)) {
		$options['ImageMagick'] = 'ImageMagick executable';
	}

	return $options;
}

/**
 * Are there upgrade scripts to be run?
 *
 * @return bool 
 */
function tidypics_is_upgrade_available() {
	// sets $version based on code
	require_once elgg_get_plugins_path() . "tidypics/version.php";

	$local_version = elgg_get_plugin_setting('version', 'tidypics');
	if ($local_version === false) {
		// no version set so either new install or really old one
		if (!get_subtype_class('object', 'image') || !get_subtype_class('object', 'album')) {
			$local_version = 0;
		} else {
			// set initial version for new install
			elgg_set_plugin_setting('version', $version, 'tidypics');
			$local_version = $version;
		}
	} elseif ($local_version === '1.62') {
		// special work around to handle old upgrade system
		$local_version = 2010010101;
		elgg_set_plugin_setting('version', $local_version, 'tidypics');
	}

	if ($local_version == $version) {
		return false;
	} else {
		return true;
	}
}

/**
 * Merge tags from album to image
 * 
 * @param TidypicsAlbum $album
 * @param TidypicsImage $image
 * @return bool
 */
function tidypics_merge_album_image_tags($album, $image) {
	// Make sure the forum entity has tags
	if ($album_tags = $album->tags) {

		// Make sure forum tags is an array
		if (!is_array($album->tags)) {
			$album_tags = array($album_tags);
		}

		$image_tags = $image->tags;

		// Make sure topic tags is an array
		if (!is_array($image->tags)) {
			if (!$image_tags) {
				$image_tags = array();
			} else {
				$image_tags = array($image_tags);
			}
		}

		// Merge album and image tags
		$new_tags = array_merge($album_tags, $image_tags);

		// Remove dupes
		$new_tags = array_unique($new_tags);

		// Update image tags
		$image->tags = $new_tags;

		return TRUE;
	}

	return FALSE;
}

/**
 * Returns just a guid from a database $row. Used in elgg_get_entities()'s callback.
 *
 * @param stdClass $row
 * @return type
 */
function tp_guid_callback($row) {
	return ($row->guid) ? $row->guid : false;
}

/**
 * Get image directory path
 *
 * Each album gets a subdirectory based on its container id
 *
 * @return string	path to image directory
 */
function tp_get_img_dir() {
	$file = new ElggFile();
	return $file->getFilenameOnFilestore() . 'image/';
}

/**
 * Is the request from a known browser
 *
 * @return true/false
 */
function tp_is_person() {
	$known = array('msie', 'mozilla', 'firefox', 'safari', 'webkit', 'opera', 'netscape', 'konqueror', 'gecko');

	$agent = strtolower($_SERVER['HTTP_USER_AGENT']);

	foreach ($known as $browser) {
		if (strpos($agent, $browser) !== false) {
			return true;
		}
	}

	return false;
}

/**
 * Auto orient image using im command line
 *
 * @param ElggFile holds the image that was uploaded
 * @return bool    TRUE on success
 */
function tp_auto_orient_im_cmdline($file) {
	// Get im path
	$im_path = elgg_get_plugin_setting('im_path', 'tidypics');
	if (!$im_path) {
		$im_path = "/usr/bin/";
	}
	if (substr($im_path, strlen($im_path)-1, 1) != "/") {
		$im_path .= "/";
	}

	// Auto-orient the image
	$command = $im_path . "convert \"" . $file->getFilenameOnFilestore() . "\" -auto-orient \"" . $file->getFilenameOnFilestore() . "\"";
	$output = array();
	$ret = 0;
	exec($command, $output, $ret);
	if ($ret == 127) {
		trigger_error('Tidypics warning: Image Magick convert is not found', E_USER_WARNING);
		return FALSE;
	} else if ($ret > 0) {
		trigger_error('Tidypics warning: Image Magick convert failed', E_USER_WARNING);
		return FALSE;
	}
	
	return TRUE;
}