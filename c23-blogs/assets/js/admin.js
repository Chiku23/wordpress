/**
 * C23 Blogs - Admin JavaScript
 */
(function($) {
	'use strict';

	$(document).ready(function() {
		// Initialize Color Pickers
		if ($.fn.wpColorPicker) {
			$('.c23-color-picker').wpColorPicker();
		}

		// Tab Switching
		$('.c23-tab-btn').on('click', function(e) {
			e.preventDefault();
			var targetTab = $(this).data('tab');

			$('.c23-tab-btn').removeClass('active');
			$(this).addClass('active');

			$('.c23-tab-content').removeClass('active');
			$('#' + targetTab).addClass('active');
		});

		// Dynamic Toggle for Grid Columns when Layout View changes
		function toggleGridCols() {
			var currentView = $('#c23_layout_view').val();
			if (currentView === 'grid') {
				$('#row-grid-columns').show();
			} else {
				$('#row-grid-columns').hide();
			}
		}

		$('#c23_layout_view').on('change', toggleGridCols);
		toggleGridCols();
	});
})(jQuery);
