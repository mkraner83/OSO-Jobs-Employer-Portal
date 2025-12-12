(function ($) {
    $(function () {
        // Image lightbox
        $(document).on('click', '.oso-jobseeker-preview img, .oso-jobseeker-thumb img', function (e) {
            e.preventDefault();
            var $img = $(this);
            var src = $img.attr('src');
            var $modal = $('<div class="oso-lightbox"><div class="oso-lightbox__backdrop"></div><div class="oso-lightbox__content"><img src="' + src + '" alt="" /></div></div>');

            $('body').append($modal);
            $modal.on('click', '.oso-lightbox__backdrop, img', function () {
                $modal.remove();
            });
        });

        // Inline approval toggle
        $(document).on('click', '.oso-toggle-approval', function (e) {
            e.preventDefault();
            
            var $link = $(this);
            var postId = $link.data('post-id');
            var nonce = $link.data('nonce');
            var currentStatus = $link.data('current');
            
            // Disable link during request
            $link.css('opacity', '0.5').css('pointer-events', 'none');
            
            $.ajax({
                url: OSOJobsAdmin.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'oso_toggle_jobseeker_approval',
                    post_id: postId,
                    nonce: nonce
                },
                success: function (response) {
                    if (response.success) {
                        var newStatus = response.data.approved;
                        
                        // Update link appearance
                        if (newStatus === '1') {
                            $link
                                .css('color', '#28a745')
                                .html('✓ ' + OSOJobsAdmin.approveText)
                                .data('current', '1');
                        } else {
                            $link
                                .css('color', '#dc3545')
                                .html('✗ ' + OSOJobsAdmin.pendingText)
                                .data('current', '0');
                        }
                        
                        // Update nonce for next toggle
                        $.post(OSOJobsAdmin.ajaxUrl, {
                            action: 'oso_refresh_approval_nonce',
                            post_id: postId
                        }, function (nonceResponse) {
                            if (nonceResponse.success) {
                                $link.data('nonce', nonceResponse.data.nonce);
                            }
                        });
                    } else {
                        alert(response.data.message || 'Error updating approval status');
                    }
                },
                error: function () {
                    alert('An error occurred. Please try again.');
                },
                complete: function () {
                    // Re-enable link
                    $link.css('opacity', '1').css('pointer-events', 'auto');
                }
            });
        });
    });
})(jQuery);
