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

// General tidypics init
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
};

// Init history for photos filter links
elgg.tidypics.initFilterLinks = function() {
	// Filter items
	$('ul.elgg-menu-photos-filter > li').each(function() {
		elgg.tidypics.initHistoryLink($(this));
	});
}

// Init history for breadcrumb links
elgg.tidypics.initBreadcrumbLinks = function() {
	// Breadcrumb items
	$('ul.elgg-menu.elgg-breadcrumbs > li').each(function() {
		elgg.tidypics.initHistoryLink($(this));
	});
}

// Init history pushstate for given link
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

// Main content loading
elgg.tidypics.loadTabContent = function(href) {
	var $loading = $("<div id='_tp-content-loader' class='elgg-ajax-loader'></div>");

	// Set ajax spinner
	$('#tidypics-content-container').html($loading);

	// Ajax get content at given href
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

			// Init list filtering
			elgg.tidypics.initListingFilters();

			// Let plugins perform any extra init tasks
			var params = {
				'href': href,
				'data': data,
			};

			elgg.trigger_hook('loadTabContentComplete', 'tidypics', params, null);

			// Update filter menu
			elgg.tidypics.loadFilterMenu($data.filter('#_tp-content-tab-filter'));
		}, 
		error: function(xhr, ajaxOptions, thrownError) {
			console.log(xrh.status);
			console.log(thrownError);
		}
	});
}

// Load content into sidebar
elgg.tidypics.loadSidebarContent = function($data) {
	$sidebar = $('div.elgg-layout.elgg-layout-one-sidebar > div.elgg-sidebar');
	$sidebar.replaceWith($data.children());
}

// Load content into breadcrumbs
elgg.tidypics.loadBreadcrumbsContent = function($data) {
	$breadcrumbs = $('div.elgg-main.elgg-body > ul.elgg-menu.elgg-breadcrumbs');
	$breadcrumbs.replaceWith($data.children());

	// Ajaxify breadcrumb links
	elgg.tidypics.initBreadcrumbLinks();
}

// Load filter menu into sidebar
elgg.tidypics.loadFilterMenu = function($data) {		
	$filter = $('div.elgg-main.elgg-body > .elgg-menu-photos-filter');
	$filter.replaceWith($data.children());
	elgg.tidypics.initFilterLinks();
}

// Set page title
elgg.tidypics.setPageTitles = function(title) {
	// Set document title
	document.title = elgg.config.sitename + ": " + title;

	// Set elgg heading titie
	$('div.elgg-head > h2.elgg-heading-main').html(title);
}

// History popstate event
elgg.tidypics.popState = function(event) {
	// Fix chrome/safari page load popstate (need to supply a state in each pushState for this to work)
	if (!event.state) {
		return;
	}

	// If going back/forward to an image lightbox, call the lightbox jumpto event
	if (location.href.indexOf('photos/image/') !== -1) {
		if (event.state) {
			$.fancybox2.jumpto(event.state.index);
			elgg.tidypics.doPushState = false; // @todo 
		}
	} else {
		// Close fancybox if it's open, don't reload content
		if ($('.fancybox2-overlay').length) {
			$.fancybox2.close();
			return;
		}
		// Load content at location
		elgg.tidypics.loadTabContent(location.href);

		// Select the proper tab
		$('ul.elgg-menu-photos-filter > li').removeClass('elgg-state-selected');
		$('a[href="' + location.href + '"]').parent().addClass('elgg-state-selected');
	}
}

// Init load more button for albums
elgg.tidypics.initLoadMore = function() { 
	// Ajax spinner
	var $loading = $("<div id='_tp-loader' class='elgg-ajax-loader'></div>");

	$(document).delegate('._tp-load-more', 'click', function(event) {
		event.preventDefault();

		// Show ajax spinner
		$(this).replaceWith($loading);

		// Get more content!
		elgg.get($(this).attr('href'), {
			data: {},
			success: function(data) {
				var $data = $(data);

				// Append new content
				$('#_tp-load-more-container').append($data.filter('#_tp-load-more-container').html());

				// Replace the next more link new content
				$('#_tp-loader').replaceWith($data.filter('._tp-load-more'));

				// Trigger a hook for extra tasks
				elgg.trigger_hook('infiniteWayPointLoaded', 'tidypics', null, null)
			}, 
			error: function(xhr, ajaxOptions, thrownError) {
				console.log(xrh.status);
				console.log(thrownError);
			}
		});

	});
}

// Init infinite scrolling with waypoint JS
elgg.tidypics.initInfiniteScroll = function() {
	// Ajax spinner
	var $loading = $("<div id='_tp-waypoint-loader' class='elgg-ajax-loader'></div>");

	// Get the waypoint container
	$infinite_waypoint = $('#_tp-waypoint-container');
	opts = {
		offset: '100%'
	};
	
	// Bind waypoint
	$infinite_waypoint.waypoint(function(event, direction) {
		// Unbind waypoint
		$infinite_waypoint.waypoint('remove');

		// Show ajax spinner
		$infinite_waypoint.append($loading);

		// Get more content!
		elgg.get($('._tp-waypoint-more').attr('href'), {
			data: {},
			success: function(data) {
				var $data = $(data);

				// Append new content
				$('#_tp-infinite-list-container').append($data.filter('#_tp-infinite-list-container').html());

				// Remove spinner
				$loading.detach();

				// Replace the next more link new content
				$('._tp-waypoint-more').replaceWith($data.filter('#_tp-waypoint-container').find('._tp-waypoint-more'));

				// Re-init waypoint
				if ($data.filter('#_tp-waypoint-container').length) {
					$infinite_waypoint.waypoint(opts);
				}

				// Trigger a hook for extra tasks
				elgg.trigger_hook('infiniteWayPointLoaded', 'tidypics', null, null)
			}, 
			error: function(xhr, ajaxOptions, thrownError) {
				console.log(xrh.status);
				console.log(thrownError);
			}
		});
	}, opts);
}

// Init filter options in photo/album listings
elgg.tidypics.initListingFilters = function() {
	// Let plugins perform any extra init tasks
	var params = {};
	elgg.trigger_hook('initListingFilters', 'tidypics', params, null);

	// Keyup for tags
	$('.elgg-menu-photos-listing-filter input').keyup(function(event) {
		// Match for enter
		var code = event.which;
		if (code == 13) {
			elgg.tidypics.filterListings();
		}
		event.preventDefault();
	});

	// init any autocompletes, with a custom select and source url
	$('.elgg-input-autocomplete').each(function() {
		var source_url = elgg.get_site_url() + 'livesearch?match_on=' + $(this).data('match_on');
		$(this).autocomplete({
			source: source_url,
			minLength: 2,
			html: "html",
			select: function(event, ui) {
				$(this).val(ui.item.value);
				elgg.tidypics.filterListings();
			},
		});
	});

	// init order by input
	$('select#tidypics-list-order-input').change(function(event) {
		elgg.tidypics.filterListings();
		event.preventDefault();
	});

	// init sort order link
	$('li.elgg-menu-item-tidypics-list-sort-order a').click(function(event) {
		$(this).data('current_value', $(this).data('sort_order'));
		elgg.tidypics.filterListings();
		event.preventDefault();
	});
}

// Load new content based on supplied filters
elgg.tidypics.filterListings = function() {
	var params = {};

	// Get filtering values
	$('.elgg-menu-photos-listing-filter input, .elgg-menu-photos-listing-filter select').each(function() {
		params[$(this).attr('name')] = $(this).val();
	});

	// Get ordering values
	$('.elgg-menu-photos-listing-sort input, .elgg-menu-photos-listing-sort select').each(function() {
		params[$(this).attr('name')] = $(this).val();
	});

	// Get sort order value
	params['sort_order'] = $('li.elgg-menu-item-tidypics-list-sort-order a').data('current_value');

	var query_string = $.param(params);

	elgg.tidypics.loadTabContent("?" + query_string);
}

elgg.register_hook_handler('init', 'system', elgg.tidypics.init);