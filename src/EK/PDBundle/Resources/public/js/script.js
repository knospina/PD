jQuery(document).ready(function(){

   console.log('piesledzu');
   jQuery('div#my-wishes .delete').click(function() {
   var li = jQuery(this).parents('li:first');
   jQuery.post($li.data('delete-url'));
   li.fadeOut();
});

});