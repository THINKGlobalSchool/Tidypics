<?php
/**
 * Tidypics General JS
 *
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU General Public License v2
 * @todo comments
 */

?>
//<script>
elgg.provide('elgg.tidypics');

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
			'href': elgg.get_site_url() + 'ajax/view/photos/upload?' + encoded_params,
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
			.attr('disabled', 'DISABLED');

		$upload_container
			.find('select[name="_tp_upload_select_existing_album"]')
			.removeClass('hidden')
			.removeAttr('disabled');

		$(this)
			.attr('value', elgg.echo('tidypics:upload:addalbum'))
			.attr('name', '_tp_upload_create_new_album');

		event.preventDefault();
	});

	// Switch to new album selection
	$upload_container.delegate('input[name="_tp_upload_create_new_album"]', 'click', function(event) {
		$upload_container
			.find('select[name="_tp_upload_select_existing_album"]')
			.addClass('hidden')
			.attr('disabled', 'DISABLED');

		$upload_container
			.find('input[name="_tp_upload_new_album_title"]')
			.removeClass('hidden')
			.removeAttr('disabled');

		$(this)
			.attr('value', elgg.echo('tidypics:upload:choosealbum'))
			.attr('name', '_tp_upload_choose_existing_album');

		event.preventDefault();
	});
}

elgg.register_hook_handler('init', 'system', elgg.tidypics.init);
elgg.register_hook_handler('upload_form_loaded', 'tidypics', elgg.tidypics.initUploadEvents);
