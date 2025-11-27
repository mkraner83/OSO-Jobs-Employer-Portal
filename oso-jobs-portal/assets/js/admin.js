(function ($) {
    $(function () {
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
    });
})(jQuery);
