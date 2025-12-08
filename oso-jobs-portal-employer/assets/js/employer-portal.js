/**
 * OSO Employer Portal JavaScript
 * Version: 1.0.7
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
        // Jobseeker Edit Profile Form Handling
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
                formData.append('nonce', osoEmployerPortal.nonce);
                
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
        
        // Remove photo handler
        $(document).on('click', '.oso-remove-photo', function(e) {
            e.preventDefault();
            var $btn = $(this);
            var photoUrl = $btn.data('url');
            var $photosInput = $('#photos_urls');
            var currentPhotos = $photosInput.val().split('\n').filter(function(url) {
                return url.trim() !== '' && url.trim() !== photoUrl;
            });
            
            $photosInput.val(currentPhotos.join('\n'));
            $btn.closest('.oso-photo-item').fadeOut(300, function() {
                $(this).remove();
            });
        });
        
        // Employer Edit Profile Form Handling
        $('#oso-edit-employer-profile-form').on('submit', function(e) {
            e.preventDefault();
            
            var $form = $(this);
            var $message = $('#oso-employer-form-message');
            var $submitBtn = $form.find('button[type="submit"]');
            
            // Disable submit button
            $submitBtn.prop('disabled', true);
            
            // Show loading message
            $message.removeClass('success error').addClass('loading')
                .text('Saving changes...').fadeIn();
            
            // Auto-add https:// to website if needed
            var websiteInput = $('#website');
            if (websiteInput.length && websiteInput.val().trim() !== '') {
                var websiteValue = websiteInput.val().trim();
                if (!websiteValue.match(/^https?:\/\//i)) {
                    websiteInput.val('https://' + websiteValue);
                }
            }
            
            // Handle file uploads first
            var logoFile = $('#logo')[0].files[0];
            var photoFiles = $('#photos')[0].files;
            var uploadPromises = [];
            
            // Validate photo count
            var currentPhotosCount = $('#photos_urls').val().split('\n').filter(function(url) {
                return url.trim() !== '';
            }).length;
            var newPhotosCount = photoFiles ? photoFiles.length : 0;
            var totalPhotosCount = currentPhotosCount + newPhotosCount;
            
            if (totalPhotosCount > 6) {
                $message.removeClass('loading success').addClass('error')
                    .text('You can only upload up to 6 photos total.');
                $submitBtn.prop('disabled', false);
                return;
            }
            
            // Upload logo if selected
            if (logoFile) {
                // Validate logo file type
                var allowedLogoTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp', 'application/pdf'];
                if (!allowedLogoTypes.includes(logoFile.type.toLowerCase())) {
                    $message.removeClass('loading success').addClass('error')
                        .text('Logo must be JPG, JPEG, PNG, WEBP, or PDF format.');
                    $submitBtn.prop('disabled', false);
                    return;
                }
                
                if (logoFile.size > 6 * 1024 * 1024) {
                    $message.removeClass('loading success').addClass('error')
                        .text('Logo file size must be less than 6MB.');
                    $submitBtn.prop('disabled', false);
                    return;
                }
                uploadPromises.push(uploadFile(logoFile, 'logo'));
            }
            
            // Upload photos if selected
            if (photoFiles && photoFiles.length > 0) {
                // Validate file types
                var allowedTypes = ['image/jpeg', 'image/jpg', 'image/webp'];
                for (var i = 0; i < photoFiles.length; i++) {
                    if (!allowedTypes.includes(photoFiles[i].type.toLowerCase())) {
                        $message.removeClass('loading success').addClass('error')
                            .text('Only JPG, JPEG, and WEBP images are allowed.');
                        $submitBtn.prop('disabled', false);
                        return;
                    }
                }
                
                // Calculate total size of all photos
                var totalSize = 0;
                for (var i = 0; i < photoFiles.length; i++) {
                    totalSize += photoFiles[i].size;
                }
                
                if (totalSize > 20 * 1024 * 1024) {
                    $message.removeClass('loading success').addClass('error')
                        .text('Total size of all photos must be less than 20MB.');
                    $submitBtn.prop('disabled', false);
                    return;
                }
                
                for (var i = 0; i < photoFiles.length; i++) {
                    uploadPromises.push(uploadFile(photoFiles[i], 'photo'));
                }
            }
            
            // Wait for file uploads to complete
            Promise.all(uploadPromises).then(function(results) {
                // Update hidden fields with uploaded URLs
                results.forEach(function(result) {
                    if (result.type === 'logo' && result.url) {
                        $('#logo_url').val(result.url);
                    } else if (result.type === 'photo' && result.url) {
                        var currentPhotos = $('#photos_urls').val();
                        var newPhotos = currentPhotos ? currentPhotos + '\n' + result.url : result.url;
                        $('#photos_urls').val(newPhotos);
                    }
                });
                
                // Now submit the form data
                submitEmployerForm($form, $message, $submitBtn);
            }).catch(function(error) {
                $message.removeClass('loading success').addClass('error')
                    .text(error || 'File upload failed. Please try again.');
                $submitBtn.prop('disabled', false);
            });
        });
        
        // Helper function to submit employer form
        function submitEmployerForm($form, $message, $submitBtn) {
            var formData = new FormData($form[0]);
            formData.append('action', 'oso_update_employer_profile');
            formData.append('nonce', $('#oso_employer_profile_nonce').val());
            formData.append('employer_id', $form.data('employer-id'));
            
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
        }

        // Delete Job Posting
        $(document).on('click', '.oso-delete-job', function() {
            var $btn = $(this);
            var jobId = $btn.data('job-id');
            var jobTitle = $btn.data('job-title');
            
            if (!confirm('Are you sure you want to delete "' + jobTitle + '"? This action cannot be undone.')) {
                return;
            }
            
            // Disable button
            $btn.prop('disabled', true);
            var originalHtml = $btn.html();
            $btn.html('<span class="dashicons dashicons-update"></span> Deleting...');
            
            $.ajax({
                url: osoEmployerPortal.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'oso_delete_job_posting',
                    nonce: osoEmployerPortal.jobNonce,
                    job_id: jobId
                },
                success: function(response) {
                    if (response.success) {
                        // Fade out and remove the job card
                        $btn.closest('.oso-job-card').fadeOut(300, function() {
                            $(this).remove();
                            
                            // Check if there are any jobs left
                            if ($('.oso-job-card').length === 0) {
                                // Reload page to show "no jobs" state
                                location.reload();
                            }
                        });
                    } else {
                        alert(response.data.message || 'Failed to delete job posting.');
                        $btn.prop('disabled', false).html(originalHtml);
                    }
                },
                error: function() {
                    alert('Network error. Please try again.');
                    $btn.prop('disabled', false).html(originalHtml);
                }
            });
        });

    });

    // ============================================
    // Application Management
    // ============================================

    // View Cover Letter Modal
    $(document).on('click', '.oso-view-cover-letter', function() {
        var applicant = $(this).data('applicant');
        var job = $(this).data('job');
        var coverLetter = $(this).data('cover-letter');
        
        $('#oso-modal-title').text('Application from ' + applicant + ' for ' + job);
        $('#oso-modal-body').html('<strong>Message from Applicant:</strong><br><br>' + coverLetter);
        $('#oso-cover-letter-modal').fadeIn(300);
    });

    // Close Modal
    $('.oso-modal-close, .oso-modal-overlay').on('click', function() {
        $('#oso-cover-letter-modal').fadeOut(300);
    });

    // Approve Application
    $(document).on('click', '.oso-approve-application', function() {
        var $btn = $(this);
        var applicationId = $btn.data('application-id');
        var applicantName = $btn.closest('.oso-application-card-item').find('.oso-applicant-link').text().trim();
        
        var confirmMessage = '✅ Approve Application for ' + applicantName + '?\n\n' +
            '📧 The candidate will receive an approval email with your contact information.\n\n' +
            '📋 IMPORTANT - Next Steps:\n' +
            '• Please contact the candidate directly to discuss:\n' +
            '   - Start date and schedule details\n' +
            '   - Orientation and onboarding process\n' +
            '   - Required paperwork and documentation\n' +
            '   - Position-specific requirements\n\n' +
            'Click OK to approve and send notification email.';
        
        if (!confirm(confirmMessage)) {
            return;
        }
        
        $btn.prop('disabled', true).text('Approving...');
        
        $.ajax({
            url: osoEmployerPortal.ajaxUrl,
            type: 'POST',
            data: {
                action: 'oso_update_application_status',
                nonce: osoEmployerPortal.jobNonce,
                application_id: applicationId,
                status: 'approved'
            },
            success: function(response) {
                if (response.success) {
                    // Reload page to show updated status
                    location.reload();
                } else {
                    alert(response.data.message || 'Failed to approve application.');
                    $btn.prop('disabled', false).text('Approve');
                }
            },
            error: function() {
                alert('An error occurred. Please try again.');
                $btn.prop('disabled', false).text('Approve');
            }
        });
    });

    // Reject Application
    $(document).on('click', '.oso-reject-application', function() {
        var $btn = $(this);
        var applicationId = $btn.data('application-id');
        
        if (!confirm('Are you sure you want to reject this application?')) {
            return;
        }
        
        $btn.prop('disabled', true).text('Rejecting...');
        
        $.ajax({
            url: osoEmployerPortal.ajaxUrl,
            type: 'POST',
            data: {
                action: 'oso_update_application_status',
                nonce: osoEmployerPortal.jobNonce,
                application_id: applicationId,
                status: 'rejected'
            },
            success: function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert(response.data.message || 'Failed to reject application.');
                    $btn.prop('disabled', false).text('Reject');
                }
            },
            error: function() {
                alert('An error occurred. Please try again.');
                $btn.prop('disabled', false).text('Reject');
            }
        });
    });

    // Reset Application
    $(document).on('click', '.oso-reset-application', function() {
        var $btn = $(this);
        var applicationId = $btn.data('application-id');
        
        if (!confirm('Are you sure you want to reset this application to pending status?')) {
            return;
        }
        
        $btn.prop('disabled', true).text('Resetting...');
        
        $.ajax({
            url: osoEmployerPortal.ajaxUrl,
            type: 'POST',
            data: {
                action: 'oso_update_application_status',
                nonce: osoEmployerPortal.jobNonce,
                application_id: applicationId,
                status: 'pending'
            },
            success: function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert(response.data.message || 'Failed to reset application.');
                    $btn.prop('disabled', false).text('Reset');
                }
            },
            error: function() {
                alert('An error occurred. Please try again.');
                $btn.prop('disabled', false).text('Reset');
            }
        });
    });

    // Delete Employer Profile
    $(document).on('click', '.oso-delete-employer-profile', function() {
        var $btn = $(this);
        var employerId = $btn.data('employer-id');
        
        var confirmMessage = '⚠️ Delete Your Profile?\n\n' +
            'This will permanently delete your employer profile and all associated data:\n' +
            '• All job postings\n' +
            '• Job applications\n' +
            '• Profile information\n\n' +
            'This action CANNOT be undone!\n\n' +
            'Type "DELETE" to confirm:';
        
        var userInput = prompt(confirmMessage);
        
        if (userInput !== 'DELETE') {
            if (userInput !== null) {
                alert('Profile deletion cancelled. You must type "DELETE" to confirm.');
            }
            return;
        }
        
        $btn.prop('disabled', true).html('<span class="dashicons dashicons-update"></span> Deleting...');
        
        $.ajax({
            url: osoEmployerPortal.ajaxUrl,
            type: 'POST',
            data: {
                action: 'oso_delete_employer_profile',
                nonce: osoEmployerPortal.jobNonce,
                employer_id: employerId
            },
            success: function(response) {
                if (response.success) {
                    alert('Your profile has been deleted. You will be logged out.');
                    window.location.href = '/wp-login.php?action=logout';
                } else {
                    alert(response.data.message || 'Failed to delete profile.');
                    $btn.prop('disabled', false).html('<span class="dashicons dashicons-trash"></span> Delete Profile');
                }
            },
            error: function() {
                alert('Network error. Please try again.');
                $btn.prop('disabled', false).html('<span class="dashicons dashicons-trash"></span> Delete Profile');
            }
        });
    });

    // Delete Application
    $(document).on('click', '.oso-delete-application', function() {
        var $btn = $(this);
        var applicationId = $btn.data('application-id');
        var $card = $btn.closest('.oso-application-card-item');
        
        if (!confirm('Are you sure you want to permanently delete this rejected application?\n\nThis action cannot be undone.')) {
            return;
        }
        
        $btn.prop('disabled', true).html('<span class="dashicons dashicons-update"></span> Deleting...');
        
        $.ajax({
            url: osoEmployerPortal.ajaxUrl,
            type: 'POST',
            data: {
                action: 'oso_delete_application',
                nonce: osoEmployerPortal.jobNonce,
                application_id: applicationId
            },
            success: function(response) {
                if (response.success) {
                    $card.fadeOut(300, function() {
                        $(this).remove();
                        // Update stats if needed
                        var $rejectedCount = $('.oso-stat-card').eq(2).find('.oso-stat-number');
                        var currentCount = parseInt($rejectedCount.text()) || 0;
                        if (currentCount > 0) {
                            $rejectedCount.text(currentCount - 1);
                        }
                        var $totalCount = $('.oso-stat-card').eq(3).find('.oso-stat-number');
                        var totalCurrent = parseInt($totalCount.text()) || 0;
                        if (totalCurrent > 0) {
                            $totalCount.text(totalCurrent - 1);
                        }
                    });
                } else {
                    alert(response.data.message || 'Failed to delete application.');
                    $btn.prop('disabled', false).html('<span class="dashicons dashicons-trash"></span> Delete');
                }
            },
            error: function() {
                alert('Network error. Please try again.');
                $btn.prop('disabled', false).html('<span class="dashicons dashicons-trash"></span> Delete');
            }
        });
    });

})(jQuery);
