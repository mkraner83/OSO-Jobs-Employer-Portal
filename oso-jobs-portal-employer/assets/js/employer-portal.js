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
            e.stopPropagation();
            
            // Prevent duplicate lightbox if one already exists
            if ($('.oso-lightbox-overlay').length > 0) {
                return false;
            }
            
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
        // Edit Profile Form Handling
        $('#oso-edit-profile-form').on('submit', function(e) {
            e.preventDefault();
            
            var $form = $(this);
            var $message = $('#oso-form-message');
            var $submitBtn = $form.find('button[type="submit"]');
            
            // Disable submit button
            $submitBtn.prop('disabled', true);
            
            // Show loading message
            $message.removeClass('success error').addClass('loading')
                .text('Saving changes...').fadeIn();
            
            // Handle file uploads first
            var photoFile = $('#photo')[0].files[0];
            var resumeFile = $('#resume')[0].files[0];
            var uploadPromises = [];
            
            if (photoFile) {
                uploadPromises.push(uploadFile(photoFile, 'photo'));
            }
            
            if (resumeFile) {
                uploadPromises.push(uploadFile(resumeFile, 'resume'));
            }
            
            // Wait for file uploads to complete
            Promise.all(uploadPromises).then(function(results) {
                // Update hidden fields with uploaded URLs
                results.forEach(function(result) {
                    if (result.type === 'photo' && result.url) {
                        $('#photo_url').val(result.url);
                    } else if (result.type === 'resume' && result.url) {
                        $('#resume_url').val(result.url);
                    }
                });
                
                // Now submit the form data
                var formData = new FormData($form[0]);
                formData.append('action', 'oso_update_jobseeker_profile');
                formData.append('nonce', $('#oso_profile_nonce').val());
                formData.append('jobseeker_id', $form.data('jobseeker-id'));
                
                $.ajax({
                    url: osoEmployerPortal.ajaxUrl,
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response.success) {
                            $message.removeClass('loading error').addClass('success')
                                .text(response.data.message);
                            
                            // Redirect after 1 second
                            setTimeout(function() {
                                if (response.data.redirect_url) {
                                    window.location.href = response.data.redirect_url;
                                }
                            }, 1000);
                        } else {
                            $message.removeClass('loading success').addClass('error')
                                .text(response.data.message || 'An error occurred.');
                            $submitBtn.prop('disabled', false);
                        }
                    },
                    error: function() {
                        $message.removeClass('loading success').addClass('error')
                            .text('An error occurred. Please try again.');
                        $submitBtn.prop('disabled', false);
                    }
                });
            }).catch(function(error) {
                $message.removeClass('loading success').addClass('error')
                    .text(error || 'File upload failed. Please try again.');
                $submitBtn.prop('disabled', false);
            });
        });
        
        // File upload helper function
        function uploadFile(file, fileType) {
            return new Promise(function(resolve, reject) {
                var formData = new FormData();
                formData.append('file', file);
                formData.append('file_type', fileType);
                formData.append('action', 'oso_upload_profile_file');
                formData.append('nonce', wp.ajax.settings.nonce);
                
                $.ajax({
                    url: osoEmployerPortal.ajaxUrl,
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response.success) {
                            resolve({
                                type: fileType,
                                url: response.data.url
                            });
                        } else {
                            reject(response.data.message || 'Upload failed');
                        }
                    },
                    error: function() {
                        reject('Upload failed');
                    }
                });
            });
        }

    });

})(jQuery);
