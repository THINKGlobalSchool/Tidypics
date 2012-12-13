<?php
/**
 * Tidypics General JS
 *
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU General Public License v2
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

	elgg.tidypics.initAjaxLinks();
	elgg.tidypics.initInfiniteScroll();
};

elgg.tidypics.initAjaxLinks = function() {
	$('ul.elgg-menu-photos-filter > li').each(function() {
		$(this).delegate('a', 'click', function(event) {

			history.pushState(null, null, $(this).attr('href'));

			$('ul.elgg-menu-photos-filter > li').removeClass('elgg-state-selected');

			$(this).parent().addClass('elgg-state-selected');

			elgg.tidypics.getPageContent($(this).attr('href'));

			event.preventDefault();
		});
	});
}

elgg.tidypics.getPageContent = function(href) {
	elgg.get(href, {
		data: {},
		success: function(data) {
			$('#tidypics-content-container').html(data);
			elgg.tidypics.initInfiniteScroll();
		}, 
		error: function(xhr, ajaxOptions, thrownError) {
			console.log(xrh.status);
			console.log(thrownError);
		}
	});
}

elgg.tidypics.popState = function(event) {
	elgg.tidypics.getPageContent(location.href);

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

elgg.register_hook_handler('init', 'system', elgg.tidypics.init);
