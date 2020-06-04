(function (Drupal, $, window) {
    Drupal.behaviors.basic = {
        attach: function (context, settings) {
            console.log('1');
            $(document).ready(function() {
console.log('2');
                var el = $('#edit-field-metal-colors').detach();

                $('.field--name-field-color').prepend(el);
            });

        }
    };

} (Drupal, jQuery, this));
