(function($) {
    $('.taxjar-sync-categories-btn').first().click(function(event) {
        Craft.postActionRequest('commerce-taxjar/categories/sync', {}, function(response) {
            console.log(response);
            if (response.success) {
                Craft.cp.displayNotice(Craft.t('commerce', 'Categories Updated. Reloading page.'));
                location.reload();
            } else {
                Craft.cp.displayError(Craft.t('commerce', 'Categories update failed. Make sure you are not in sandbox mode.'));
            }
        });
    });
})(jQuery);
