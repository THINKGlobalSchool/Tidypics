<?php
/**
 * Tidypics General JS
 *
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU General Public License v2
 * @todo comments
 */

$maxfilesize = (float)elgg_get_plugin_setting('maxfilesize', 'tidypics');

?>
//<script>
elgg.provide('elgg.tidypics');

// Store some config values
elgg.tidypics.maxfilesize = '<?php echo $maxfilesize; ?>';

elgg.tidypics.init = function() {

	if ($(".tidypics-lightbox").length) {
		$(".tidypics-lightbox").fancybox({'type': 'image'});
	}

	$("#tidypics-sort").sortable({
		opacity: 0.7,
		revert: true,
		scroll: true
	});

	$('.elgg-form-photos-album-sort').submit(function() {
		var tidypics_guids = [];
		$("#tidypics-sort li").each(function(index) {
			tidypics_guids.push($(this).attr('id'));
		});
		$('input[name="guids"]').val(tidypics_guids.toString());
	});

	elgg.tidypics.initFilterLinks();
	elgg.tidypics.initUpload();
};

elgg.tidypics.initFilterLinks = function() {
	// Filter items
	$('ul.elgg-menu-photos-filter > li').each(function() {
		elgg.tidypics.initHistoryLink($(this));
	});
}

elgg.tidypics.initBreadcrumbLinks = function() {
	// Breadcrumb items
	$('ul.elgg-menu.elgg-breadcrumbs > li').each(function() {
		elgg.tidypics.initHistoryLink($(this));
	});
}

elgg.tidypics.initHistoryLink = function($parent) {
	$parent.delegate('a', 'click', function(event) {
		history.pushState(null, null, $(this).attr('href'));

		$('ul.elgg-menu-photos-filter > li').removeClass('elgg-state-selected');

		$('ul.elgg-menu-photos-filter > li > a[href^="' + $(this).attr('href') + '"]')
			.parent().addClass('elgg-state-selected');

		elgg.tidypics.loadTabContent($(this).attr('href'));

		event.preventDefault();
	});
}

elgg.tidypics.loadTabContent = function(href) {
	var $loading = $("<div id='_tp-content-loader' class='elgg-ajax-loader'></div>");

	$('#tidypics-content-container').html($loading);

	elgg.get(href, {
		data: {},
		success: function(data) {
			var $data = $(data);
			// Load main content
			$('#tidypics-content-container').html(data);

			// Load in breadcrumbs
			elgg.tidypics.loadBreadcrumbsContent($data.filter('#_tp-content-tab-breadcrumbs'));

			// Load in sidebar
			elgg.tidypics.loadSidebarContent($data.filter('#_tp-content-tab-sidebar'));

			// Set titles
			elgg.tidypics.setPageTitles($data.filter('#_tp-content-tab-page-title').html());

			// Init infinite scroll
			elgg.tidypics.initInfiniteScroll();

			// Init any uploaders
			elgg.tidypics.initUpload();


		}, 
		error: function(xhr, ajaxOptions, thrownError) {
			console.log(xrh.status);
			console.log(thrownError);
		}
	});
}

elgg.tidypics.loadSidebarContent = function($data) {
	$sidebar = $('div.elgg-layout.elgg-layout-one-sidebar > div.elgg-sidebar');
	$sidebar.replaceWith($data.children());
}

elgg.tidypics.loadBreadcrumbsContent = function($data) {
	$breadcrumbs = $('div.elgg-main.elgg-body > ul.elgg-menu.elgg-breadcrumbs');
	$breadcrumbs.replaceWith($data.children());

	// Ajaxify breadcrumb links
	elgg.tidypics.initBreadcrumbLinks();
}

elgg.tidypics.setPageTitles = function(title) {
	// Set document title
	document.title = elgg.config.sitename + ": " + title;

	// Set elgg heading titie
	$('div.elgg-head > h2.elgg-heading-main').html(title);
}

elgg.tidypics.popState = function(event) {
	elgg.tidypics.loadTabContent(location.href);

	// Select the proper tab
	$('ul.elgg-menu-photos-filter > li').removeClass('elgg-state-selected');
	$('a[href="' + location.href + '"]').parent().addClass('elgg-state-selected');
}

elgg.tidypics.initInfiniteScroll = function() {
	var $loading = $("<div id='_tp-waypoint-loader' class='elgg-ajax-loader'></div>"),

	$infinite_waypoint = $('#_tp-waypoint-container');
	opts = {
		offset: '100%'
	};
	
	$infinite_waypoint.waypoint(function(event, direction) {
		$infinite_waypoint.waypoint('remove');
		$infinite_waypoint.append($loading);

		elgg.get($('._tp-waypoint-more').attr('href'), {
			data: {},
			success: function(data) {
				var $data = $(data);

				$('#_tp-infinite-list-container').append($data.filter('#_tp-infinite-list-container').html());

				$loading.detach();

				$('._tp-waypoint-more').replaceWith($data.filter('#_tp-waypoint-container').find('._tp-waypoint-more'));

				if ($data.filter('#_tp-waypoint-container').length) {
					$infinite_waypoint.waypoint(opts);
				}
			}, 
			error: function(xhr, ajaxOptions, thrownError) {
				console.log(xrh.status);
				console.log(thrownError);
			}
		});
	}, opts);
}

elgg.tidypics.initUpload = function() {
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
				elgg.trigger_hook('upload_form_loaded', 'tidypics', params, null);
			},
			onCleanup: function() {
				var params = {
					upload_container : $('#tidypics-upload-container'),
				};
				elgg.trigger_hook('upload_form_unloaded', 'tidypics', params, null);
			}, 
		});
	}
}

elgg.tidypics.initUploadEvents = function(hook, type, params, options) {
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

		event.preventDefault();
	});

	$upload_container.delegate('input[name="_tp-upload-finish"]', 'click', function(event) {
		window.location.reload();
		event.preventDefault();
	});

	/** SET UP FILEUPLOAD (move this) **/
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
			var maxsizebytes = elgg.tidypics.maxfilesize * 1024 * 1024;

			var file = data.files[0];

			// Check file size
			if (file.size > maxsizebytes) {
				elgg.register_error(elgg.echo('tidypics:exceed_filesize', [file.name, elgg.tidypics.maxfilesize]));
			} else {
				// Crear dropzone content
				if ($dropzone.hasClass('tidypics-upload-dropzone-droppable')) {
					$dropzone.html('');
					$dropzone.removeClass('tidypics-upload-dropzone-droppable');
				}

				// Create file upload container
				$dropzone.append(elgg.tidypics.createUploadImageElement(data, file));

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
							elgg.tidypics.displayUploadThumbnail(data.context, result.output.image_guid);
						} else {
							// There were errors
							if (result.system_messages.error.length) {
								for (e in result.system_messages.error) {
									// Display each error encountered
									elgg.tidypics.displayUploadError(data.context, result.system_messages.error[e]);
								}
							} else {
								// Not sure what happened here.. display unknown error
								elgg.tidypics.displayUploadError(data.context, elgg.echo('tidypics:unk_error'));
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

			$album_menu.children().each(function() {
				if (!$(this).is('span#_tp-upload-album-label')) {
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
elgg.tidypics.createUploadImageElement = function(data, file) {
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
elgg.tidypics.displayUploadError = function(context, error) {
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
elgg.tidypics.displayUploadThumbnail = function(context, guid) {
	var $img = $(document.createElement('img'));
	$img.attr('src', elgg.get_site_url() + 'photos/thumbnail/' + guid + '/small/')
	$img.addClass('tidypics-upload-image-thumbnail');
	$img.addClass('elgg-photo');
	context.html($img);
}

elgg.register_hook_handler('init', 'system', elgg.tidypics.init);
elgg.register_hook_handler('upload_form_loaded', 'tidypics', elgg.tidypics.initUploadEvents);
