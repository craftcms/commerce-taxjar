(function($) {
    $('.taxjar-sync-categories-btn').first().click(function(event) {
        Craft.postActionRequest('commerce-taxjar/categories/sync', {}, function(response) {
            console.log(response);
        });
    });
})(jQuery);
