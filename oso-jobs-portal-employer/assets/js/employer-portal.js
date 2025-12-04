/**
 * OSO Employer Portal JavaScript
 */

(function($) {
    'use strict';
    
    $(document).ready(function() {
        
        // Toggle advanced filters
        $('.oso-toggle-filters').on('click', function(e) {
            e.preventDefault();
            var $advanced = $('.oso-filter-advanced');
            $advanced.slideToggle(300);
            
            // Remember state
            var isVisible = $advanced.is(':visible');
            localStorage.setItem('oso_filters_visible', isVisible ? '1' : '0');
            
            // Update button text
            $(this).text(isVisible ? 'Hide Filters' : 'Advanced Filters');
        });
        
        // Restore filter state on page load
        if (localStorage.getItem('oso_filters_visible') === '1') {
            $('.oso-filter-advanced').show();
            $('.oso-toggle-filters').text('Hide Filters');
        }
        
    });
    
})(jQuery);
