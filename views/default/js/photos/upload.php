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

		// Check for context data
		if ($uploader.data('context')) {
			params['context'] = $uploader.data('context');
		}

		// URL Encode params
		var encoded_params = $.param(params);

		// Init lightbox
		$('.elgg-module-tidypics-upload div._tp-uploader').fancybox({
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

	$upload_container.delegate('input[name="_tp-upload-finish"]', 'click', function(event) {
		window.location = window.location.href;
		event.preventDefault();
	});

	/** SET UP FILEUPLOAD **/
	$upload_container.delegate('input[name="_tp_upload_choose_submit"]', 'click', function(event) {
		$upload_container.find('#_tp_upload-file-input').trigger('click');
		event.preventDefault();
	});

	$('#_tp_upload-file-input').fileupload({
        dataType: 'json',
		dropZone: $('#_tp-upload-dropzone'),
		fileInput: $('#_tp_upload-file-input'),
		url: elgg.get_site_url() + "action/photos/upload",
		sequentialUploads: true,
		drop: function (e, data) {
			// // Remove drag class
			$('#_tp-upload-dropzone').removeClass('tidypics-upload-dropzone-drag');
		},
		add: function (e, data) {
			var $dropzone = $('#_tp-upload-dropzone');

			// Get max size in bytes
			var maxsizebytes = elgg.tidypics.upload.maxfilesize * 1024 * 1024;

			var file = data.files[0];

			// Check file size
			if (file.size > maxsizebytes) {
				elgg.register_error(elgg.echo('tidypics:exceed_filesize', [file.name, elgg.tidypics.upload.maxfilesize]));
			} else {
				// Crear dropzone content
				if ($dropzone.hasClass('tidypics-upload-dropzone-droppable')) {
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

				// Submit/upload this file
				var jqXHR = data.submit()
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
						elgg.register_error(errorThrown);
					})
					.complete(function (result, textStatus, jqXHR) {
						// Complete
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
		},
		stop: function (e) {
			// Get status container
			var $status_container = $(this).closest('form').find('div.tidypics-upload-status');

			// Get batch timestamp from upload form
			var batch = $(this).closest('form').find('input[name="_tp-upload-batch"]').val();

			elgg.action('photos/uploads_complete', {
				data: {
					batch: batch,
				},
				success: function(response) {
					if (response.status == 0) {
						// No errors
						$status_container.find('span').html(elgg.echo('tidypics:upl_complete'));
						$status_container.find('input[name="_tp-upload-finish"]').show();
						$status_container.removeClass('elgg-ajax-loader');
					} else {
						// There were errors
						$status_container.find('span').html(response.output);
						$status_container.addClass('tidypics-upload-status-error');
						$status_container.removeClass('elgg-ajax-loader');
					}
				}
			});
		},
		progress: function (e, data) {
			// Update progress for context
			var progress = parseInt(data.loaded / data.total * 100, 10);
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
	
	var $name = $(document.createElement('div'));
	$name.addClass('tidypics-upload-image-name');
	$name.html(file.name);

	$div.append($name);

	var $progress = $(document.createElement('div'));
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