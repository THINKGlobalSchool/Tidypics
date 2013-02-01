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

	elgg.tidypics.initFilterLinks();
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

				elgg.trigger_hook('infiniteWayPointLoaded', 'tidypics', null, null)
			}, 
			error: function(xhr, ajaxOptions, thrownError) {
				console.log(xrh.status);
				console.log(thrownError);
			}
		});
	}, opts);
}

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
}

elgg.tidypics.filterListings = function() {
	var params = {};
	$('.elgg-menu-photos-listing-filter input, .elgg-menu-photos-listing-filter select').each(function() {
		params[$(this).attr('name')] = $(this).val();
	});

	var query_string = $.param(params);

	elgg.tidypics.loadTabContent("?" + query_string);
}

elgg.register_hook_handler('init', 'system', elgg.tidypics.init);