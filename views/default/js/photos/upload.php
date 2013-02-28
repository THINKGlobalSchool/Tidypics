<?php
/**
 * Tidypics Upload JS
 *
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU General Public License v2
 */

$maxfilesize = (float)elgg_get_plugin_setting('maxfilesize', 'tidypics');

?>
//<script>
elgg.provide('elgg.tidypics.upload');

// Store some config values
elgg.tidypics.upload.maxfilesize = '<?php echo $maxfilesize; ?>';

elgg.tidypics.upload.init = function() {
	// Get uploader entity
	var $uploader = $('.elgg-module-tidypics-upload div._tp-uploader');

	// Make sure it exists
	if ($uploader.length) {
		var params = {};

		// Check for container_guid data
		if ($uploader.data('container_guid')) {
			params['container_guid'] = $uploader.data('container_guid');
		}

		// Check for group_guid data
		if ($uploader.data('group_guid')) {
			params['group_guid'] = $uploader.data('group_guid');
		}

		// Check for context data
		if ($uploader.data('context')) {
			params['context'] = $uploader.data('context');
		}

		// URL Encode params
		var encoded_params = $.param(params);

		// Init lightbox
		$('.elgg-module-tidypics-upload div._tp-uploader').fancybox({
			'hideOnOverlayClick': false,
			'hideOnContentClick': false,
			'href': elgg.get_site_url() + 'ajax/view/photos/ajax_upload?' + encoded_params,
			onComplete: function() {
				var params = {
					upload_container : $('#tidypics-upload-container'),
				};
				elgg.trigger_hook('uploadFormLoaded', 'tidypics', params, null);
			},
			onCleanup: function() {
				var params = {
					upload_container : $('#tidypics-upload-container'),
				};
				elgg.trigger_hook('uploadFormUnloaded', 'tidypics', params, null);
			}, 
		});
	}
}

// Init upload events
elgg.tidypics.upload.initEvents = function(hook, type, params, options) {
	/** SET UP EVENTS FOR UPLOADER FORM **/
	// Get uploader container
	var $upload_container = params.upload_container;

	// Switch to existing album selection
	$upload_container.delegate('input[name="_tp_upload_choose_existing_album"]', 'click', function(event) {
		$upload_container
			.find('input[name="_tp_upload_new_album_title"]')
			.addClass('hidden')
			.removeClass('_tp-upload-active-input')
			.attr('disabled', 'DISABLED');

		$upload_container
			.find('select[name="_tp_upload_select_existing_album"]')
			.removeClass('hidden')
			.addClass('_tp-upload-active-input')
			.removeAttr('disabled');

		$(this)
			.attr('value', elgg.echo('tidypics:upload:addalbum'))
			.attr('name', '_tp_upload_create_new_album');

		$upload_container.find('#_tp-upload-album-label').html(elgg.echo('tidypics:upload:addtoexistingalbum'));
		
		$upload_container.find('._tp-upload-album-metadata-menu').hide();

		event.preventDefault();
	});

	// Switch to new album selection
	$upload_container.delegate('input[name="_tp_upload_create_new_album"]', 'click', function(event) {
		$upload_container
			.find('select[name="_tp_upload_select_existing_album"]')
			.addClass('hidden')
			.removeClass('_tp-upload-active-input')
			.attr('disabled', 'DISABLED');

		$upload_container
			.find('input[name="_tp_upload_new_album_title"]')
			.removeClass('hidden')
			.addClass('_tp-upload-active-input')
			.removeAttr('disabled');

		$(this)
			.attr('value', elgg.echo('tidypics:upload:choosealbum'))
			.attr('name', '_tp_upload_choose_existing_album');

		$upload_container.find('#_tp-upload-album-label').html(elgg.echo('tidypics:upload:newalbumname'));

		$upload_container.find('._tp-upload-album-metadata-menu').show();

		event.preventDefault();
	});

	// Finish uploading button
	$upload_container.delegate('input[name="_tp-upload-finish"]', 'click', function(event) {
		window.location = $(this).data('forward_url');
		event.preventDefault();
	});

	// Start uploading button
	$upload_container.delegate('input[name="_tp-upload-start"]', 'click', elgg.tidypics.upload.start);

	/** SET UP FILEUPLOAD **/
	$upload_container.delegate('input[name="_tp_upload_choose_submit"]', 'click', function(event) {
		$upload_container.find('#_tp-upload-file-input').trigger('click');
		event.preventDefault();
	});

	$('#_tp-upload-file-input').fileupload({
        dataType: 'json',
		dropZone: $('#_tp-upload-dropzone'),
		fileInput: $('#_tp-upload-file-input'),
		url: elgg.get_site_url() + "action/photos/upload",
		sequentialUploads: true,
		drop: function (e, data) {
			// // Remove drag class
			$('#_tp-upload-dropzone').removeClass('tidypics-upload-dropzone-drag');
		},
		add: function (e, data) {
			// Get max size in bytes
			var maxsizebytes = elgg.tidypics.upload.maxfilesize * 1024 * 1024;

			// Get file
			var file = data.files[0];

			// Check file size
			if (file.size > maxsizebytes) {
				elgg.register_error(elgg.echo('tidypics:exceed_filesize', [file.name, elgg.tidypics.upload.maxfilesize]));
			} else {
				// Get status container
				var $status_container = $(this).closest('form').find('div.tidypics-upload-status');

				// Show start/cancel button 
				$status_container.find('input[name="_tp-upload-start"]').show();
				$status_container.find('input[name="_tp-upload-cancel"]').show();

				// Get dropzone
				var $dropzone = $('#_tp-upload-dropzone');

				// Crear dropzone content
				if ($dropzone.hasClass('tidypics-upload-dropzone-droppable')) {
					$dropzone.data('original_content', $dropzone.html());
					$dropzone.html('');
					$dropzone.removeClass('tidypics-upload-dropzone-droppable');
				}

				// Create file upload container
				$dropzone.append(elgg.tidypics.upload.createImageElement(data, file));

				// Make sure fileInput and form are set
				if (!data.fileInput) {
					data.fileInput = $(this);
				}
				if (!data.form) {
					data.form = $('form.elgg-form-photos-upload');
				}

				var jqXHR = false;

				// Bind custom event to document
				$(document).bind('tpstartupload', function(event) {
					// Submit/upload this file
					if (data.files.length > 0) { // Make sure file exists
						jqXHR = data.submit()
							.success(function (result, textStatus, jqXHR) {
								// Successful post, check for errors/success
								if (result.status == 0) {
									// No errors uploading, display thumbnail in element context
									elgg.tidypics.upload.displayThumbnail(data.context, result.output.image_guid);
								} else {
									// There were errors
									if (result.system_messages.error.length) {
										for (e in result.system_messages.error) {
											// Display each error encountered
											elgg.tidypics.upload.displayError(data.context, result.system_messages.error[e]);
										}
									} else {
										// Not sure what happened here.. display unknown error
										elgg.tidypics.upload.displayError(data.context, elgg.echo('tidypics:unk_error'));
									}
								}
							})
							.error(function (jqXHR, textStatus, errorThrown) {
								// Error posting
								if (errorThrown != 'abort') {
									elgg.register_error(errorThrown);	
								}	
							})
							.complete(function (result, textStatus, jqXHR) {
								// Complete (always)
							});
					}

					event.preventDefault();
				});

				// Bind click handler to allow users to remove this image from the queue
				data.context.find('._tp-upload-image-remove').click(function(event) {
					event.preventDefault();
					
					// Zero out the files (will be just the current image)
					data.files.length = 0;

					// Remove upload image element from dropzone
					data.context.remove();

					// Check if we've removed the last element
					if (!$('._tp-upload-image-element ').length) {
						// Reset dropzone
						$dropzone.html($dropzone.data('original_content'));
						$dropzone.addClass('tidypics-upload-dropzone-droppable');

						// Hide buttons
						$status_container.find('.elgg-button').hide();
					}
				});

				// Cancel uploading button
				$('input[name="_tp-upload-cancel"]').click(function(event) {
					// If we've started uploading
					if (jqXHR) {
						jqXHR.abort();
						$status_container.addClass('tidypics-upload-status-error');
						$status_container.data('aborted', true);
						$status_container.find('span').html(elgg.echo(elgg.echo('tidypics:abort_cancelled')));
						$status_container.find('input[name="_tp-upload-finish"]').data('forward_url', window.location.href).show();
						$status_container.find('input[name="_tp-upload-cancel"]').hide();
						$status_container.removeClass('elgg-ajax-loader');
					} else {
						// Close the lightbox
						$.fancybox.close();
					}

					event.preventDefault();
				});

			}
		},
		dragover: function (e, data) {
			// Add dragover class
			$('#_tp-upload-dropzone').addClass('tidypics-upload-dropzone-drag');
		},
		start: function(e) {
			// Hide album menu inputs
			var $album_menu = $(this).closest('form').find('#_tp-upload-album-menu');

			// Get rid of remove links, need to cancel from here on in
			$('._tp-upload-image-remove').remove();

			// Hide close button
			$('a#fancybox-close').remove();

			// Disable escape			
			$(document).unbind('keydown.fb').bind('keydown', function(event){event.preventDefault()});

			var $active_input = $(this).closest('form').find('#_tp-upload-album-menu ._tp-upload-active-input');

			$album_menu.find('li').each(function() {
				if (!$(this).is('.elgg-menu-item-album-label')) {
					$(this).hide();
				} else {
					if ($active_input.is('select')) {
						$(this).append($active_input.find('option:selected').text());
					} else {
						$(this).append($active_input.val());
					}
				}
			});

			// Get status container
			var $status_container = $(this).closest('form').find('div.tidypics-upload-status');

			$status_container.addClass('elgg-ajax-loader');
			$status_container.find('span').html(elgg.echo('tidypics:upload:started'));
			$status_container.find('input[name="_tp-upload-start"]').hide();
		},
		stop: function (e) {
			// Get status container
			var $status_container = $(this).closest('form').find('div.tidypics-upload-status');

			// Get batch timestamp from upload form
			var batch = $(this).closest('form').find('input[name="_tp-upload-batch"]').val();

			var $album_guid_input = $('input[name="_tp-upload-album-guid"]');
			
			elgg.action('photos/uploads_complete', {
				data: {
					batch: batch,
					album_guid: $album_guid_input.val(),
				},
				success: function(response) {
					if (!$status_container.data('aborted')) {
						if (response.status == 0) {
							// No errors
							$status_container.find('span').html(elgg.echo('tidypics:upl_complete'));
							$status_container.find('input[name="_tp-upload-finish"]').data('forward_url', response.output.forward_url).show();
							$status_container.find('input[name="_tp-upload-cancel"]').hide();
							$status_container.removeClass('elgg-ajax-loader');
						} else {
							// There were errors
							$status_container.find('span').html(response.output);
							$status_container.addClass('tidypics-upload-status-error');
							$status_container.removeClass('elgg-ajax-loader');
						}
					}
				}
			});
	
		},
		progress: function (e, data) {
			// Update progress for context
			var progress = parseInt(data.loaded / data.total * 100, 10);
			data.context.find('.tidypics-upload-image-progress span').remove();
			data.context.find('.tidypics-upload-image-progress-bar').css(
				'width',
				progress + '%'
			);
    	},
    	done: function(e, data) {
    		// Set progress for the context to 100%
    		data.context.find('.tidypics-upload-image-progress-bar').css('width','100%');
    	}
	});
}

/**
 * Start upload
 * 
 * If a new album is needed, create it. Then fire uploads
 */
elgg.tidypics.upload.start = function(event) {
	if (!$(this).attr('disabled')) {
		$(this).attr('disabled', 'DISABLED');

		// Determine if we're creating a new album or not
		var $new_album_input = $('input[name="_tp_upload_new_album_title"]');
		var $existing_album_input = $(':input[name="_tp_upload_select_existing_album"]');
		var $album_guid_input = $('input[name="_tp-upload-album-guid"]');

		// Existing album
		if (!$new_album_input.hasClass('_tp-upload-active-input')) {
			$album_guid_input.val($existing_album_input.val());

			// Trigger custom start upload event
			$(document).trigger('tpstartupload');
		} else {
			// New album, create it
			var $form = $(this).closest('form');

			elgg.action('photos/album/create', {
				data: {
					album_name: $form.find('input[name="_tp_upload_new_album_title"]').val(),
					album_tags: $form.find('input[name="_tp_upload_album_tags"]').val(),
					album_access: $form.find('select[name="_tp_upload_album_access_id"]').val(),
					group_guid: $form.find('input[name="group_guid"]').val(),
					container_guid: $form.find('input[name="container_guid"]').val()
				},
				success: function(response) {
					if (response.status == 0) {
						// Success
						$album_guid_input.val(response.output.album_guid);

						// Trigger custom start upload event
						$(document).trigger('tpstartupload');
					} else {
						// Errors
						$('input[name="_tp-upload-start"]').removeAttr('disabled');
					}
				}
			});
		}
	}
	event.preventDefault();
}

/**
 * Create the image upload element to display progress and set data context
 *
 * @param object data
 * @param object file
 * @return object
 */
elgg.tidypics.upload.createImageElement = function(data, file) {
	var $div = $(document.createElement('div'));
	$div.addClass('_tp-upload-image-element tidypics-upload-image-element');
	$div.data('name', file.name);

	var $remove = $(document.createElement('div'));
	$remove.addClass('tidypics-upload-image-remove');
	$remove.addClass('_tp-upload-image-remove');

	var $remove_icon = $(document.createElement('span'));
	$remove_icon.addClass('elgg-icon');
	$remove_icon.addClass('elgg-icon-delete');

	$remove.append($remove_icon);

	$div.append($remove);
	
	var $name = $(document.createElement('div'));
	$name.addClass('tidypics-upload-image-name');
	$name.html(file.name);

	$div.append($name);

	var $progress = $(document.createElement('div'));
	$progress.html("<span>" + elgg.echo('tidypics:upload:waiting') + "</span>");
	$progress.addClass('tidypics-upload-image-progress');

	var $bar = $(document.createElement('div'));
	$bar.addClass('tidypics-upload-image-progress-bar');

	$progress.append($bar);

	$div.append($progress);

	// Set context for file upload data
	data.context = $div;

	return $div;
}

/**
 * Display errors in the upload image element
 *
 * @param object context The context (element)
 * @param string error
 */
elgg.tidypics.upload.displayError = function(context, error) {
	// Register an elgg error
	//elgg.register_error(error);

	var $error_container = context.find('.tidypics-upload-image-errors');

	if (!$error_container.length) {
		$error_container = $('<div class="tidypics-upload-image-errors"></div>');
		context.html($('<div class="tidypics-upload-image-error-header">' + elgg.echo('tidypics:upload_error') + '</div>'));
		context.append($error_container);
	}

	$error_container.append(error + "<br />");
}

/**
 * Display the image thumbnail in image element
 *
 * @param object context The context (element)
 * @param string guid
 */
elgg.tidypics.upload.displayThumbnail = function(context, guid) {
	var $img = $(document.createElement('img'));
	$img.attr('src', elgg.get_site_url() + 'photos/thumbnail/' + guid + '/small/')
	$img.addClass('tidypics-upload-image-thumbnail');
	$img.addClass('elgg-photo');
	context.html($img);
}

elgg.register_hook_handler('init', 'system', elgg.tidypics.upload.init);
elgg.register_hook_handler('uploadFormLoaded', 'tidypics', elgg.tidypics.upload.initEvents);
elgg.register_hook_handler('loadTabContentComplete', 'tidypics', elgg.tidypics.upload.init);