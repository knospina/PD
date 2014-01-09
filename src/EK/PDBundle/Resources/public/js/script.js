jQuery(document).ready(function() {
    jQuery('#my-wishes tr .delete').click(function() {
        var tr = jQuery(this).parents('tr:first');
        jQuery.post(tr.data('delete-url'));
        tr.fadeOut();
    });
});