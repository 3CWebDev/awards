(function($){
    $('input[name="CheckoutAccountsPane[register][mail]"]').keypress(function (e) {
        if (e.which == 13) {
            $('#edit-checkoutaccountspane-register .button').focus().click();
            return false;
        }
    });
    $('input[name="CheckoutAccountsPane[register][password][pass1]"]').keypress(function (e) {
        if (e.which == 13) {
            $('#edit-checkoutaccountspane-register .button').focus().click();
            return false;
        }
    });
    $('input[name="CheckoutAccountsPane[register][password][pass2]"]').keypress(function (e) {
        if (e.which == 13) {
            $('#edit-checkoutaccountspane-register .button').focus().click();
            return false;
        }
    });
})(jQuery);
