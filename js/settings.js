jQuery(document).ready(function () {

    jQuery(".add-operativa").on('click', function () {
        var newRow = jQuery('<tr>');
        var cols = "";
        var i = jQuery('#operativas-table tbody tr').length;

        cols += '<td style="text-align:center"><input name="operativas[' + i + '][active]" type="hidden" value="0"/><input type="checkbox" name="operativas[' + i + '][activo]"></td>';
        cols += '<td style="text-align:center"><input type="text" name="operativas[' + i + '][name]" placeholder="Nombre en Checkout"></td>';
        cols += '<td><select name="operativas[' + i + '][type]">';
        cols += '<option value="pap">Puerta a Puerta</option>';
        cols += '<option value="pas">Puerta a Sucursal</option>';
        cols += '<option value="sap">Sucursal a Puerta</option>';
        cols += '<option value="sas">Sucursal a Sucursal</option>';
        cols += '</select></td>';
        cols += '<td style="text-align:center"><input type="text" name="operativas[' + i + '][code]"></td>';
        cols += '<td style="text-align:center"><input name="operativas[' + i + '][contrareembolso]" type="hidden" value="0"/><input type="checkbox" name="operativas[' + i + '][contrareembolso]"></td>';
        cols += '<td style="text-align:center"><a class="button delete-site">Eliminar</a></td>';

        newRow.append(cols);
        jQuery('#operativas-table').append(newRow);

        jQuery('.delete-site').filter(':last').click(function (event) {
            jQuery(this).closest("tr").remove();
        });
    });

    jQuery('.delete-site').click(function (event) {
        jQuery(this).closest("tr").remove();
    });

});