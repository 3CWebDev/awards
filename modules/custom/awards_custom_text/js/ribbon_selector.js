(function($){

    $('#edit-ribbon input').change(function() {
        var id = $('#edit-ribbon input[name=ribbon]:checked').val();
        var image = $('#id-' + id).html();

        //ribbon-sample
        $('.ribbon-sample img').remove();
        $('.ribbon-sample').append(image);
    })

})(jQuery);