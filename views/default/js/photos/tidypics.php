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
	// @todo move sort stuff
	$("#tidypics-sort").sortable({
		opacity: 0.7,
		revert: true,
		scroll: true
	});

	// Init sort submit
	$('.elgg-form-photos-album-sort').submit(function() {
		var tidypics_guids = [];
		$("#tidypics-sort li").each(function(index) {
			tidypics_guids.push($(this).attr('id'));
		});
		$('input[name="guids"]').val(tidypics_guids.toString());
	});

	elgg.tidypics.initLightboxes();
	elgg.tidypics.initFilterLinks();
	elgg.tidypics.initUpload();

	// Bind click handler for ajax comments
	$(document).delegate('form.elgg-form-comments-add input[type=submit]', 'click', elgg.tidypics.submitCommentClick);

	// Unbind elgg confirmation from delete links
	$('.elgg-requires-confirmation').die('click');

	// Bind click handler for ajax comment delete
	$(document).delegate('div.tidypics-lightbox-comments-container .elgg-menu-item-delete a', 'click', elgg.tidypics.deleteCommentClick);

	// Bind lightbox close
	$(document).delegate('a.tidypics-lightbox-close', 'click', function(event) {
		$.fancybox2.close();
		event.preventDefault();
	});
};

elgg.tidypics.initLightboxes = function() {
	// Init fancybox2 lightbox
	if ($(".tidypics-lightbox").length) {
		$(".tidypics-lightbox").attr('rel', 'tidypics-lightbox').fancybox2(elgg.tidypics.getFancyboxInit(null));
	}
}

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
		history.pushState({from: 'navigation'}, null, $(this).attr('href'));

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

			// Init lightboxes
			elgg.tidypics.initLightboxes();

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
	// Fix chrome/safari page load popstate (need to supply a state in each pushState for this to work)
	if (!event.state) {
		return;
	}

	if (location.href.indexOf('photos/image/') !== -1) {
		if (event.state) {
			$.fancybox2.jumpto(event.state.index);
			elgg.tidypics.doPushState = false;
		}
	} else {
		// Close fancybox if it's open, don't reload content
		if ($('.fancybox2-overlay').length) {
			$.fancybox2.close();
			return;
		}
		elgg.tidypics.loadTabContent(location.href);
		// Select the proper tab
		$('ul.elgg-menu-photos-filter > li').removeClass('elgg-state-selected');
		$('a[href="' + location.href + '"]').parent().addClass('elgg-state-selected');
	}
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

				// Init lightboxes
				elgg.tidypics.initLightboxes();
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

// Helper to add tinyMCE controls where necessary
elgg.tidypics.addTinyMCE = function() {
	// Init tinymce control for comments
	var id = $('.tidypics-lightbox-comments-container').find('.elgg-input-longtext').attr('id');
	if (typeof(tinyMCE) !== 'undefined' && id) {
		tinyMCE.EditorManager.execCommand('mceAddControl', false, id);
	}
}

// Helper to remove tinyMCE controls where necessary
elgg.tidypics.removeTinyMCE = function() {
	// Remove tinymce control for commments
	var id = $('.tidypics-lightbox-comments-container').find('.elgg-input-longtext').attr('id');
	if (typeof(tinyMCE) !== 'undefined' && id) {
		tinyMCE.EditorManager.execCommand('mceRemoveControl', false, id);
	}	
}

// Lightbox comments click handler
elgg.tidypics.submitCommentClick = function(event) {
	// Get the form
	var $form = $(this).closest('form.elgg-form');
	
	var $_original = $(this).clone();
	
	$(this).replaceWith("<div class='_tp-ajax-comment-loader elgg-ajax-loader'></div>");
	
	var $_this = $(this);

	// Get comment input id	
	var comment_id = $form.find('.elgg-input-longtext').attr('id');

	// Get entity guid
	var entity_guid = $form.find('input[name="entity_guid"]').val();
	
	// Get comment, may not be tinyMCE 
	if (typeof(tinyMCE) !== 'undefined') {
		try {
			var comment = tinyMCE.get(comment_id).getContent();
			$("#" + comment_id).val(comment);
		} catch (err) {
			var comment = $("#" + comment_id).val();
		}
	} else {
		var comment = $("#" + comment_id).val();
	}

	// Post comment with a regular elgg action
	elgg.action('comments/add', {
		data: {
			entity_guid: entity_guid, 
			generic_comment: comment,
		}, 
		success: function(data) {
			// Check for bad status 
			if (data.status == -1) {
				// Error
				$('._tp-ajax-comment-loader').replaceWith($_original);
			} else {
				// New comment html
				var load_url = elgg.get_site_url() + 'ajax/view/photos/ajax_comment?entity_guid=' + entity_guid;
				
				var $null_div = $(document.createElement('div'));
				$null_div.attr('id', 'null-div');

				$null_div.load(load_url, function(result) {
					elgg.tidypics.pushComment(result);
				}); 

				// Clear comment text field
				if (typeof(tinyMCE) !== 'undefined') {
					tinyMCE.get(comment_id).setContent('')
				} else {
					$('#' + comment_id).val('');
				}

				$('._tp-ajax-comment-loader').replaceWith($_original);
			}
		}
	});

	event.preventDefault();
}

// Click handler for comment delete click
elgg.tidypics.deleteCommentClick = function(event) {
	if (!$(this).hasClass('disabled') && confirm($(this).attr('rel'))) {
		$(this).addClass('disabled');
		$_this = $(this);

		// Extract annotation ID from the href
		var string = $(this).attr('href');

		var search = "annotation_id=";

		var annotation_id = string.substring(string.indexOf(search) + search.length);

		// Get the form
		var $form = $(this).closest('.todo-ajax-submission').find('form.elgg-form');

		// Get entity guid
		var entity_guid = $form.find('input[name="entity_guid"]').val();

		// Delete comment
		elgg.action('comments/delete', {
			data: {
				annotation_id: annotation_id,
			}, 
			success: function(data) {
				// Check for bad status 
				if (data.status == -1) {
					// Error
					$_this.removeClass('disabled');
				} else {
					// Remove the comment from the DOM
					$_this.closest('li.elgg-item').fadeOut(function(){
						$(this).remove();
					});
				}
			}
		});
	}
	event.preventDefault();
}

// Helper function to push a comment into an annotation list
elgg.tidypics.pushComment = function(content) {
	// Create an object from content param
	var $new_comment = $(content);
	$new_comment.hide(); // Hide it for special fx

	// Grab annotation
	var $annotation_list = $('.tidypics-lightbox-comments-container ul.elgg-annotation-list');
	
	// Check for the annotation list, if we dont have one, create it
	if ($annotation_list.length == 0) {
		// Create the annotation list
		var $ul = $(document.createElement('ul'));
		$ul.attr('class', 'elgg-list elgg-list-annotation elgg-annotation-list');

		// Create heading
		var $h3 = $(document.createElement('h3'));
		$h3.html(elgg.echo('comments'));

		// Prepend the container with the new contents
		$('.tidypics-lightbox-comments-container > .elgg-comments').prepend($ul);
		$('.tidypics-lightbox-comments-container > .elgg-comments').prepend($h3);
		
		var $annotation_list = $('.tidypics-lightbox-comments-container ul.elgg-annotation-list');
	}

	// Append to the annotation list
	$annotation_list.append($new_comment);

	// Slide it in
	$new_comment.slideDown();
}

elgg.tidypics.inlineEditClick = function(event) {
	// Hide edit link for now
	$(this).css('visibility', 'hidden');
	
	$_edit = $(this);

	// Get container
	var $container = $(this).closest('._tp-can-edit');

	// Determine which field we're editing
	var field = $container.data('field');

	// Get content display
	var $content = $container.find('._tp-' + field);

	// Get input
	var $input = $container.find('*[name=_tp_edit_inline_' + field + ']');

	// Get save button
	var $save = $container.find('._tp-save-inline');

	// Get cancel button
	var $cancel = $container.find('._tp-cancel-inline');

	$input.show();
	$input.focus();
	$save.show();
	$cancel.show();
	$content.hide();

	var resetInlineEdit = function() {
		$_edit.css('visibility', 'visible');
		$save.removeAttr('disabled');
		$save.hide();
		$cancel.hide();
		$input.hide();
		$content.show();
	}

	// Bind a click event for the save button
	$save.bind('click', function(event) {
		var value = $input.val();

		if (value && !$(this).attr('disabled')) {
			$(this).attr('disabled', 'DISABLED');
			$_save = $(this);
			
			var data = {};
			data[field] = value;
			data['entity_guid'] = $(this).data('entity_guid');

			elgg.action('photos/image/update', {
				data: data,
				success: function(data) {
					// Check for bad status 
					if (data.status == -1) {
						// Error
						$_save.removeAttr('disabled');
					} else {
						// Success
						$content.html(data.output);
						resetInlineEdit();
					}
				}
			});
		}
	})

	$cancel.bind('click', function(event) {
		resetInlineEdit();
	});

	event.preventDefault();
}

elgg.tidypics.getFancyboxInit = function(href) {
	var tagging = false;

	// Is tagging loaded?
	if (elgg.tidypics.tagging != undefined) {
		tagging = true;
	}

	var initial_state = window.location.href;

	elgg.tidypics.doPushState = true;

	var fancyboxInit = {
		live: false, // Don't use jQuery.live to bind fancyboxes
		href: href, // Optional, will use elements href if not provided
		type: 'ajax',
		closeBtn: false,
		arrows: false,
		wrapCSS: 'tidypics-lightbox-wrap',
		scrolling: 'no',
		beforeShow: function() {
			//console.log('beforeShow');
			var new_state = this.href;

			if (elgg.tidypics.doPushState) {
				history.pushState({index: this.index}, null, new_state);
			} else {
				elgg.tidypics.doPushState = true;
			}

			if (tagging) {
				// Destroy tagging events
				elgg.tidypics.tagging.destroy();	
			}

			// Get photo and sidebar
			var $photo = this.inner.find('img.tidypics-photo');
			var $sidebar = this.inner.find('.tidypics-lightbox-sidebar-content');

			// Trigger before show event
			var params = {
				'photo': $photo,
				'sidebar': $sidebar,
				'lightbox': this,
			}
			elgg.trigger_hook('photoLightboxBeforeShow', 'tidypics', params, null);

			// Offsets for auto-resizing photo/sidebar
			var photo_offset = 180;
			var sidebar_offset = 155;

			// Store original photo height/width
			$photo.attr('data-original_height', $photo.attr('height'));
			$photo.attr('data-original_width', $photo.attr('width'));

			// Set height and show photo
			if ($photo.attr('height') > $(window).height() - photo_offset) {
				$photo.attr('height', $(window).height() - photo_offset);
				$photo.removeAttr('width');
				if (tagging) {
					elgg.tidypics.tagging.scale = $photo.data('original_height') / $photo.height();
				}
			} else {
				elgg.tidypics.tagging.scale = 1;
			}

			$photo.show();

			// Set sidebar height
			$sidebar.css('height', $(window).height() - sidebar_offset);

			// Bind namespaced resize events
			$(window).bind('resize.tpLightboxResize', function () {
				var window_height = $(window).height();

				$sidebar.css('height', window_height - sidebar_offset);

				var new_height = window_height - photo_offset;

				// Don't blow up the image
				if (new_height > $photo.data('original_height')) {
					new_height = $photo.data('original_height');
				}

				$photo.attr('height', new_height);
				$photo.removeAttr('width');
				
				if (tagging) {
					elgg.tidypics.tagging.scale = $photo.data('original_height') / $photo.height();
					$('.tidypics-tag').each(elgg.tidypics.tagging.position);
				}
			});

			// Clicking a photo opens the master image in a new window
			$photo.parent().bind('click', function(event) {
				window.open($(this).attr('href'));
				event.preventDefault();
			});

			$('._tp-edit-inline').bind('click', elgg.tidypics.inlineEditClick);
		},
		afterShow: function() {
			//console.log('afterShow');
			var params = {
				'lightbox': this,
			}
			elgg.trigger_hook('photoLightboxAfterShow', 'tidypics', params, null);

			// Init tagging when everything is loaded
			if (tagging) {
				elgg.autocomplete.init();
				elgg.tidypics.tagging.init();
			}
		},
		beforeLoad: function() {
			//console.log('beforeLoad');
			// Unbind namespaced resize events (otherwise we'll have a million resize events)
			$(window).unbind('resize.tpLightboxResize');
			var params = {
				'lightbox': this,
			}
			elgg.trigger_hook('photoLightboxBeforeLoad', 'tidypics', params, null);
		},
		afterLoad: function(current, previous) {
			//console.log('afterLoad');
			var params = {
				'current': current,
				'previous': previous,
				'lightbox': this,
			}
			elgg.trigger_hook('photoLightboxAfterLoad', 'tidypics', params, null);
		},
		beforeClose: function() {
			//console.log('beforeClose');
			history.pushState({from: 'closeLightbox'}, null, initial_state);
			var params = {
				'lightbox': this,
			}
			elgg.trigger_hook('photoLightboxBeforeClose', 'tidypics', params, null);
		},
		afterClose: function() {
			//console.log('afterClose');
			var params = {
				'lightbox': this,
			}
			elgg.trigger_hook('photoLightboxAfterClose', 'tidypics', params, null);
		},
		helpers: {
			overlay: {
				closeClick: false,
			},			
			thumbs	: {
				width	: 50,
				height	: 50,
				appendTo: '.tidypics-lightbox-footer'
			},
			buttons	: {
				skipSingle: true,
				appendTo: '.tidypics-lightbox-header',
				tpl: '<div id="fancybox2-buttons"><ul><li><a class="btnPrev" title="Previous" href="javascript:;"></a></li><li><a class="btnPlay" title="Start slideshow" href="javascript:;"></a></li><li><a class="btnNext" title="Next" href="javascript:;"></a></li></ul></div>',
			}
		},
		keys: {
			next : {
				39 : 'left', // right arrow
			},
			prev : {
				37 : 'right',  // left arrow
			},
			close  : [27], // escape key
			//play   : [32], // space - start/stop slideshow
			play   : false,
		}
	}
	return fancyboxInit;
}

elgg.register_hook_handler('init', 'system', elgg.tidypics.init);
elgg.register_hook_handler('uploadFormLoaded', 'tidypics', elgg.tidypics.initUploadEvents);
elgg.register_hook_handler('photoLightboxAfterShow', 'tidypics', elgg.tidypics.addTinyMCE);
elgg.register_hook_handler('photoLightboxBeforeShow', 'tidypics', elgg.tidypics.removeTinyMCE);
elgg.register_hook_handler('photoLightboxBeforeClose', 'tidypics', elgg.tidypics.removeTinyMCE);