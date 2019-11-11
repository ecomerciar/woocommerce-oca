; (function ($) {
    $('body').on('change', 'select[name="oca_sucursal"]', function (e) {
        var select = e.currentTarget;
        var optionSelected = select.options[select.selectedIndex];
        var selectValue = optionSelected.value;

        jQuery.post(
            ajaxObject.ajaxUrl, {
            'action': 'oca_set_sucursal_en_sesion',
            'data': selectValue,
            'security-nonce': ajaxObject.securityNonce
        }
        ).done(function (response) {
            if (!response.success) {
                alert('Hubo un error al seleccionar la sucursal de OCA. Por favor intenta nuevamente');
            }
            jQuery(document.body).trigger("update_checkout");
        });
    });
})(jQuery);