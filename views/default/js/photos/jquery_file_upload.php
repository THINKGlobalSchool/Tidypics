<?php
/**
 * Simplecache view for jquery-file-upload
 *
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU General Public License v2
 */

$js_path = elgg_get_config('path');

$widget_path = "{$js_path}mod/tidypics/vendors/jquery-file-upload/jquery.ui.widget.js";
$transport_path = "{$js_path}mod/tidypics/vendors/jquery-file-upload/jquery.iframe-transport.js";
$fileupload_path = "{$js_path}mod/tidypics/vendors/jquery-file-upload/jquery.fileupload.js";

include $widget_path;
include $transport_path;
include $fileupload_path;