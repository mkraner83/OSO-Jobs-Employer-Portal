(function ($) {
    $(function () {
        // Placeholder for future frontend enhancements such as filters.
        $('.oso-jobseeker-file-input').on('change', function () {
            var $input = $(this);
            var file = this.files[0];
            if (!file) {
                return;
            }

            var field = $input.data('field');
            var limits = {
                resume_url: {size: OSOJobsProfile.maxResume, types: ['application/pdf']},
                photo_url: {size: OSOJobsProfile.maxPhoto, types: ['image/jpeg', 'image/jpg']}
            };

            if (!limits[field]) {
                return;
            }

            if (file.size > limits[field].size) {
                alert('File is too large.');
                $input.val('');
                return;
            }

            if (limits[field].types.indexOf(file.type) === -1) {
                alert('Invalid file type.');
                $input.val('');
                return;
            }

            var formData = new FormData();
            formData.append('action', 'oso_jobs_upload_file');
            formData.append('nonce', OSOJobsProfile.nonce);
            formData.append('field', field);
            formData.append('file', file);

            $.ajax({
                url: OSOJobsProfile.ajaxUrl,
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function (response) {
                    if (response.success) {
                        var $fieldWrapper = $input.closest('.oso-jobseeker-field');
                        var $hidden = $fieldWrapper.find('.oso-jobseeker-file-url');
                        $hidden.val(response.data);
                        var $preview = $fieldWrapper.find('.oso-jobseeker-preview');
                        if (!$preview.length) {
                            $preview = $('<div class="oso-jobseeker-preview"></div>').prependTo($fieldWrapper);
                        }

                        if (field === 'photo_url') {
                            var $img = $preview.find('img');
                            if (!$img.length) {
                                $img = $('<img alt="">').appendTo($preview);
                            }
                            $img.attr('src', response.data);
                        } else {
                            var $link = $preview.find('a');
                            if (!$link.length) {
                                $link = $('<a target="_blank" rel="noopener noreferrer"></a>').appendTo($preview);
                            }
                            $link.attr('href', response.data).text(OSOJobsProfile.resumeText);
                        }

                        $input.val('');
                    } else {
                        alert(response.data || 'Upload failed.');
                    }
                },
                error: function () {
                    alert('Upload failed.');
                }
            });
        });

        $('.oso-jobseeker-delete-file').on('click', function (e) {
            e.preventDefault();
            var $btn = $(this);
            var field = $btn.data('field');
            var $urlField = $btn.closest('.oso-jobseeker-file').find('.oso-jobseeker-file-url');

            $.post(
                OSOJobsProfile.ajaxUrl,
                {
                    action: 'oso_jobs_delete_file',
                    nonce: OSOJobsProfile.nonce,
                    field: field
                },
                function (response) {
                    if (response.success) {
                        $urlField.val('');
                        var $fieldWrapper = $btn.closest('.oso-jobseeker-field');
                        $fieldWrapper.find('.oso-jobseeker-preview').empty();
                    }
                }
            );
        });
    });
})(jQuery);
