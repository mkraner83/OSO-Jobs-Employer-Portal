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
        
        // Simple Lightbox for Profile Photos
        $('.oso-photo-lightbox').on('click', function(e) {
            e.preventDefault();
            
            var $img = $(this).find('img');
            var imgSrc = $(this).attr('href');
            var imgAlt = $img.attr('alt');
            
            // Create lightbox HTML
            var lightboxHtml = '<div class="oso-lightbox-overlay">' +
                '<div class="oso-lightbox-content">' +
                '<span class="oso-lightbox-close">&times;</span>' +
                '<img src="' + imgSrc + '" alt="' + imgAlt + '">' +
                '</div>' +
                '</div>';
            
            // Append to body
            $('body').append(lightboxHtml);
            
            // Add CSS if not already present
            if ($('#oso-lightbox-styles').length === 0) {
                var styles = '<style id="oso-lightbox-styles">' +
                    '.oso-lightbox-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.9); z-index: 9999; display: flex; align-items: center; justify-content: center; }' +
                    '.oso-lightbox-content { position: relative; max-width: 90%; max-height: 90%; }' +
                    '.oso-lightbox-content img { max-width: 100%; max-height: 90vh; display: block; }' +
                    '.oso-lightbox-close { position: absolute; top: -40px; right: 0; color: white; font-size: 40px; cursor: pointer; font-weight: bold; }' +
                    '.oso-lightbox-close:hover { color: #ccc; }' +
                    '</style>';
                $('head').append(styles);
            }
            
            // Close on click
            $('.oso-lightbox-overlay').on('click', function(e) {
                if (e.target === this || $(e.target).hasClass('oso-lightbox-close')) {
                    $(this).fadeOut(200, function() {
                        $(this).remove();
                    });
                }
            });
            
            // Close on ESC key
            $(document).on('keyup.lightbox', function(e) {
                if (e.key === 'Escape') {
                    $('.oso-lightbox-overlay').fadeOut(200, function() {
                        $(this).remove();
                    });
                    $(document).off('keyup.lightbox');
                }
            });
        });
    });

})(jQuery);
