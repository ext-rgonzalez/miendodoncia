$( function( $ ) {
    var source          = _service + '?funcion=Gasto',
        that            = this;

    $(document).on('click', '#agregar-fila', function(){

        var _clone  = $(this).closest('div#div-clone').clone( true),
            _lastdiv= $('div#div-clone').last();

        $('div#div-clone').last().after(_clone);
        $(_lastdiv).find('button#agregar-fila').hide();
        $(_lastdiv).find('button#eliminar-fila').show();
        $('div#div-clone').last().find('button#eliminar-fila').show();

    });

    $(document).on('click', '#eliminar-fila', function(){
        $(this).closest('div#div-clone').remove();
        if($('div#div-clone').length>1)
            $('div#div-clone').last().find('button#agregar-fila').show();
        else{
            $('div#div-clone').last().find('button#agregar-fila').show();
            $('div#div-clone').last().find('button#eliminar-fila').hide();
        }
    });

    $(document).on('click', '#limpiarIngreso', function(){
        resetFormIngreso();
        getNumeracion();
    });

    $('#formIngreso').submit( function( e ) {
        e.preventDefault();

        var _button = $(this).find('button[type="submit"]'),
            _data   = $(this).serializefiles();

        swal({
            title: "",
            text: "Desea guardar los cambios",
            type: "warning",
            showCancelButton: true,
            confirmButtonColor: "#039BE5",
            confirmButtonText: "Si, Guardar Gasto!",
            cancelButtonText: "No, Cancelar!",
            closeOnConfirm: false,
            closeOnCancel: true
        }, function (isConfirm) {
            if (isConfirm) {

                $.ajax({
                    url: _service + '?funcion=' + _metodo,
                    dataType: "json",
                    type: "POST",
                    data: _data,
                    processData: false,
                    contentType: false,
                    beforeSend: function(){
                        _button.attr('disabled',true);
                        _button.find('i').removeClass('fa-floppy-o').addClass('fa-spinner fa-spin');
                    },
                }).done(function (xhr_data) {

                    if(xhr_data.status){
                        $.mostrarMensaje(1);
                        resetFormIngreso();

                    } else
                        $.mostrarMensaje(2);

                    _button.attr('disabled',false);
                    _button.find('i').removeClass('fa-spinner fa-spin').addClass('fa-floppy-o');

                }).fail( function( xhr_error){
                    $.mostrarMensaje(2);
                    _button.attr('disabled',false);
                    _button.find('i').removeClass('fa-spinner fa-spin').addClass('fa-floppy-o');
                });
            }
        });
    });

    $( function() {
        getNumeracion();
    });

    function resetFormIngreso() {
        document.getElementById("formIngreso").reset();

        if($('div#div-clone').length>1) {
            $('div#div-clone').each( function(key, element){
                if($('div#div-clone').length>1)
                    $('div#div-clone').last().find('#eliminar-fila').trigger('click');
            })
        }

        $('#agregar-fila').attr('disabled', false);

        getNumeracion();
    }

    function getNumeracion() {
        $.ajax({
            url: _service + '?funcion=getNumComprobantes',
            dataType: "json",
            type: "POST",
            processData: false,
            contentType: false,
        }).done(function (xhr_data) {
            if (xhr_data.numeracion.numComprobanteEgreso != '') {
                $('#ingresosNumComprobante').val('').val(xhr_data.numeracion.numComprobanteEgreso);
            }
        });
    }
});