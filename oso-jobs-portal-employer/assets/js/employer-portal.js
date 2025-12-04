/**
 * OSO Employer Portal JavaScript
 */

(function($) {
    'use strict';

    $(document).ready(function() {
        
        // Advanced Filters Toggle
        $('#toggle-advanced').on('click', function() {
            var $btn = $(this);
            var $filters = $('#advanced-filters');
            var $text = $btn.find('.toggle-text');
            
            if ($filters.is(':visible')) {
                $filters.slideUp(300);
                $text.text($text.data('show-text') || 'Show Advanced Filters');
                $btn.removeClass('active');
            } else {
                $filters.slideDown(300);
                $text.text($text.data('hide-text') || 'Hide Advanced Filters');
                $btn.addClass('active');
            }
        });
        
        // Store text values for toggle
        var $toggleText = $('#toggle-advanced .toggle-text');
        $toggleText.data('show-text', 'Show Advanced Filters');
        $toggleText.data('hide-text', 'Hide Advanced Filters');
        
        // If any advanced filters are active, show the panel
        var hasActiveFilters = false;
        $('#advanced-filters input[type="checkbox"]').each(function() {
            if ($(this).is(':checked')) {
                hasActiveFilters = true;
                return false;
            }
        });
        
        if (hasActiveFilters) {
            $('#advanced-filters').show();
            $('#toggle-advanced').addClass('active').find('.toggle-text').text('Hide Advanced Filters');
        }
    });

})(jQuery);
