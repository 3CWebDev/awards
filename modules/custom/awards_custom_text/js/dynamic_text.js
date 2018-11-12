(function($){

    $(document).ready(function($) {
        customTextPreview();
    });

    $( ".custom-text-group .form-text" ).keyup(function (index) {
        // Is this line's copy chexkbox enabled?
        var line_num = $(this).attr("line");
        if ($("input.form-checkbox[line=" + line_num + "]").is(':checked')) {
            var line_text = $("input[item=1][line=" + line_num + "]").val();
            i = 1;
            do{
                i += 1;
                $("input[item=" + i + "][line=" + line_num + "]").val(line_text);
            }while(i < qty);
        }
        // Update preview
        customTextPreview();
    });

    $('input.form-checkbox').change(function() {
        if(this.checked) {
            // Copy this line to all other products
            var qty = drupalSettings.awards_custom.awards_custom.qty;
            var line_num = $(this).attr("line");
            var line_text = $("input[item=1][line=" + line_num + "]").val();

            i = 1;
            do{
                i += 1;
                $("input[item=" + i + "][line=" + line_num + "]").val(line_text);
            }while(i < qty);
            customTextPreview();

        }
    })

    $('select[name=template]').change(function() {
        tid = this.value;

        // Start ajax request
        $.ajax({
            url: "/ajax/" + tid,
            dataType: "json",
            success: function(data) {

                var size = Object.keys(data).length;

                // Clear any existing filled in fields?
                $("input[item=1]").val('');
                for (i = 0; i < size; i++) {
                    // Copy text to first product
                    $("input[item=1][line=" + (i+1) + "]").val(data[i]);
                }
                customTextPreview();
            }
        });
    })



    function customTextPreview(){
        var lines = new Array();

        $(".custom-text-group input.form-text").each(function (index) {

            var line_text = $(this).val();
            var item_num = $(this).attr("item");
            var line_num = $(this).attr("line");

            lines[line_num] = line_text;

            var text = lines.join("<br />");

            $('#text_preview' + item_num + ' div').html(text);

        });
    }

})(jQuery);
