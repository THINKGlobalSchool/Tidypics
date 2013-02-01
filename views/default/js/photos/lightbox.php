<?php
/**
 * Tidypics Lightbox JS
 *
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU General Public License v2
 */

?>
//<script>
elgg.provide('elgg.tidypics.lightbox');

elgg.tidypics.lightbox.init = function() {
	// Init fancybox2 lightbox
	if ($(".tidypics-lightbox").length) {
		$(".tidypics-lightbox").attr('rel', 'tidypics-lightbox').fancybox2(elgg.tidypics.lightbox.getFancyboxInit(null));
	}


	// Bind click handler for ajax comments
	$(document).delegate('form.elgg-form-comments-add input[type=submit]', 'click', elgg.tidypics.lightbox.submitCommentClick);

	// Unbind elgg confirmation from delete links
	$('.elgg-requires-confirmation').die('click');

	// Bind click handler for ajax comment delete
	$(document).delegate('div.tidypics-lightbox-comments-container .elgg-menu-item-delete a', 'click', elgg.tidypics.lightbox.deleteCommentClick);

	// Bind lightbox close
	$(document).delegate('a.tidypics-lightbox-close', 'click', function(event) {
		$.fancybox2.close();
		event.preventDefault();
	});

	// Ajaxify likes in lightboxes
	$(document).delegate('body.fancybox2-lock li.elgg-menu-item-likes a', 'click', elgg.tidypics.lightbox.likeClick);
}

elgg.tidypics.lightbox.getFancyboxInit = function(href) {
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

			$('._tp-edit-inline').bind('click', elgg.tidypics.lightbox.inlineEditClick);
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

// Lightbox comments click handler
elgg.tidypics.lightbox.submitCommentClick = function(event) {
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
					elgg.tidypics.lightbox.pushComment(result);
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
elgg.tidypics.lightbox.deleteCommentClick = function(event) {
	if (!$(this).hasClass('disabled') && confirm($(this).attr('rel'))) {
		$(this).addClass('disabled');
		$_this = $(this);

		// Extract annotation ID from the href
		var string = $(this).attr('href');

		var search = "annotation_id=";

		var annotation_id = string.substring(string.indexOf(search) + search.length);

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
elgg.tidypics.lightbox.pushComment = function(content) {
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

elgg.tidypics.lightbox.inlineEditClick = function(event) {
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

	var params = {
		'input': $input,
	}

	// Trigger a hook for special inputs
	elgg.trigger_hook('photoLightboxInlineEditInputShow', 'tidypics', params, null);

	$('.tidypics-lightbox-edit-overlay').attr('style', 'display: none;'); // hide the overlay if it's still showing

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
		$('.tidypics-lightbox-edit-overlay').removeAttr('style');
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

// Ajax post like clicks
elgg.tidypics.lightbox.likeClick = function(event) {
	elgg.action($(this).attr('href'), {data: {},
		success: function(data) {
			if (data.status == -1) {
				// Error
			} else {
				// Success
			}
		}
	});
	event.preventDefault();
}

elgg.tidypics.lightbox.peopleTagAdded = function(hook, type, params, value) {
	if ($('.tidypics-tagging-container').length) {
		var tags_href = elgg.get_site_url() + 'ajax/view/photos/tagging/tags?entity_guid=' + params.guid;
		$('.tidypics-tagging-container').load(tags_href, function() {
			elgg.tidypics.tagging.destroy();
			elgg.tidypics.tagging.init();
		});

		// Update tag string
		$('.tidypics-lightbox-people-tags-container').html(params.output);
		return false;
	}
	return value;
}

elgg.tidypics.lightbox.peopleTagRemoved = function(hook, type, params, value) {
	if ($('.tidypics-lightbox-people-tags-container').length) {
		// Update tag string
		$('.tidypics-lightbox-people-tags-container').html(params.output);
		return false;
	}
	return value;
}

// Helper to add tinyMCE controls where necessary
elgg.tidypics.lightbox.addTinyMCE = function() {
	// Init tinymce control for comments
	var id = $('.tidypics-lightbox-comments-container').find('.elgg-input-longtext').attr('id');
	if (typeof(tinyMCE) !== 'undefined' && id) {
		tinyMCE.EditorManager.execCommand('mceAddControl', false, id);
	}
}

// Helper to remove tinyMCE controls where necessary
elgg.tidypics.lightbox.removeTinyMCE = function() {
	// Remove tinymce control for commments
	var id = $('.tidypics-lightbox-comments-container').find('.elgg-input-longtext').attr('id');
	if (typeof(tinyMCE) !== 'undefined' && id) {
		tinyMCE.EditorManager.execCommand('mceRemoveControl', false, id);
	}	
}

elgg.register_hook_handler('init', 'system', elgg.tidypics.lightbox.init);
elgg.register_hook_handler('loadTabContentComplete', 'tidypics', elgg.tidypics.lightbox.init);
elgg.register_hook_handler('infiniteWayPointLoaded', 'tidypics', elgg.tidypics.lightbox.init);
elgg.register_hook_handler('peopleTagAdded', 'tidypics', elgg.tidypics.lightbox.peopleTagAdded);
elgg.register_hook_handler('peopleTagRemoved', 'tidypics', elgg.tidypics.lightbox.peopleTagRemoved);
elgg.register_hook_handler('photoLightboxAfterShow', 'tidypics', elgg.tidypics.lightbox.addTinyMCE);
elgg.register_hook_handler('photoLightboxBeforeShow', 'tidypics', elgg.tidypics.lightbox.removeTinyMCE);
elgg.register_hook_handler('photoLightboxBeforeClose', 'tidypics', elgg.tidypics.lightbox.removeTinyMCE);