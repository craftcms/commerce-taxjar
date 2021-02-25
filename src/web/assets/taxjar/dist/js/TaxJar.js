(function($) {
    $('a#taxjar-sync-categories-btn').click(function(event) {
        event.preventDefault();
        $('#taxjar-sync-spinner').toggleClass('hidden');
        Craft.postActionRequest('commerce-taxjar/categories/sync', {}, function(response) {

            if (response.success) {
                Craft.cp.displayNotice(Craft.t('commerce', 'Categories Updated. Reloading page.'));
                location.reload();
            } else {
                Craft.cp.displayError(Craft.t('commerce', 'Categories update failed. Make sure you are not in sandbox mode.'));
            }

            $('#taxjar-sync-spinner').toggleClass('hidden');
        });
    });
})(jQuery);
