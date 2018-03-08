$( function( $ ) {
    var source          = _service + '?funcion=traerAgendaMedica',
        that            = this,
        sourcePacientes = _service + '?funcion=traerPacientes';
        tablePacientes  = '';

    $('#ingresoscodhistoriaclinica').select2({placeholder: 'Seleccionar'});

    $( '#fec_prox_pago').datetimepicker({
        format: "YYYY-MM-DD"
    });

    tablePacientes = $( '#tablaPacientes' ).DataTable({
        "ajax": {
            url: sourcePacientes,
            type: 'POST'
        },
        "pageLength": 10,
        "serverSide": true,
        "bProcessing":  true,
        "bDeferRender": false,
        //"bFilter": false,
        "fnRowCallback": function( nRow, aData, iDisplayIndex ) {
            if ( aData['cod_estado']=='BBB' )
                $(nRow).addClass( 'danger' );

            $(nRow).data('info_paciente', encodeURIComponent(JSON.stringify(aData)));

        },
        "aoColumns":    [
            {"mData": "nom_paciente"},
            {"mData": "ced_paciente"},
            {"mData": "tel_paciente"},
            {"mData": "cel_paciente"},
            {"mData": "dir_paciente"},
            {"mData": "fec_paciente"},
            {"mData": "action",
                "mRender": function ( data, type, row ) {

                    var estado = row.cod_estado=='AAA'?'BBB':'AAA';

                    var template =
                        '<div class="btn-group">'+
                        '   <button type="button" id="seleccionPaciente" class="btn btn-default btn-sm"><i class="fa fa-check"></i> Seleccionar</button> '+
                        '   <button type="button" class="btn btn-default btn-sm dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">'+
                        '   </button>'+
                        '   <ul class="dropdown-menu dropdown-menu-right">'+
                        '       <li><a id="paciente-edit"         data-infopaciente='+encodeURIComponent(JSON.stringify(row))+' data-codpaciente="'+row.cod_paciente+'"><i class="fa fa-refresh"></i> Editar</a></li>'+
                        '       <li><a id="paciente-changestatus" data-codpaciente="'+row.cod_paciente+'" data-estado="'+estado+'"><i class="fa fa-power-off"></i> Cambiar Estado </a></li>'+
                        '       <li><a id="paciente-delete"   data-codpaciente="'+row.cod_paciente+'"><i class="fa fa-trash"></i> Eliminar</a></li>'+
                        '   </ul>'+
                        '</div>';

                    return template;
                },
                "bSortable": false
            }
        ],
        "initComplete": function ( settings, json) {

        }
    });

    $( document).on( 'click', '#seleccionarPaciente', function(){
        $('#panelTratamientos').fadeOut();
        $( '#modalPacientes' ).modal( 'show' );
    });

    $( document ).on('click', '#seleccionPaciente', function(){
        var $this       = $(this).closest('tr'),
            infoUsuario = JSON.parse( decodeURIComponent( $this.data( 'info_paciente' ) )),
            aData       = {};

        $('#panelTratamientos').fadeIn();
        resetFormIngreso();

        $( '#nom_paciente' ).val( '' ).val( infoUsuario.nom_paciente );
        $( '#cod_paciente' ).val( '' ).val( infoUsuario.cod_paciente );
        $( '#modalPacientes' ).modal( 'hide' );

        aData = {
            cod_paciente:infoUsuario.cod_paciente,
        };

        $.ajax({
            url: _service + '?funcion=traerTratamientosPacientes',
            dataType: "json",
            type: "GET",
            data: aData,
            beforeSend: function(){
                $('#ingresoscodhistoriaclinica').html('');
            },
        }).done(function (xhr_data) {

            if(xhr_data.tratamientos.length) {

                $.each(xhr_data.tratamientos, function(key,aTratamiento){
                    $('#ingresoscodhistoriaclinica').append($('<option>').attr('value',aTratamiento.cod_historia_clinica).html(aTratamiento.value));
                });

                $('#ingresoscodhistoriaclinica').trigger('change');

                if(xhr_data.tratamientos.length && xhr_data.tratamientos.length==1)
                    $('#agregar-fila').attr('disabled', true);

            }else {
                swal({
                    title: "Error",
                    text: "El paciente seleccionado no tiene historias clinicas en el sistema",
                    type: "error",
                    confirmButtonColor: "#039BE5"
                });
            }

        });
    });

    $(document).on('click', '#agregar-fila', function(){

        var $this       = $(this),
            $container  = '',
            _clone,
            _lastdiv;

        $('select[name="ingresos[ingresos][detalle][cod_historia_clinica][]"]').each(function(key, element){
            $(element).select2('destroy');
        });

        $('input[name="ingresos[ingresos][detalle][fec_prox_pago][]"]').each(function(key, element){
            $(element).data("DateTimePicker").destroy();
        });

        _clone  = $(this).closest('div#div-clone').clone( true),
        _lastdiv= $('div#div-clone').last();

        $('div#div-clone').last().after(_clone);
        $(_lastdiv).find('button#agregar-fila').hide();
        $(_lastdiv).find('button#eliminar-fila').show();
        $('div#div-clone').last().find('button#eliminar-fila').show();

        $container  = $('.div-cloner').last();

        $container.find('#ingresoscosto').val('');
        $container.find('#ingresoscancelado').val('');
        $container.find('#ingresosadeudado').val('');
        $container.find('#ingresosrecibido').val('');

        $container.find('select').find('option[value="'+_lastdiv.before().find('select').find(':selected').val()+'"]').remove();

        $('select[name="ingresos[ingresos][detalle][cod_historia_clinica][]"]').each(function(key, element){
            $(element).select2({'placeholder':'seleccionar'});
        });

        $('input[name="ingresos[ingresos][detalle][fec_prox_pago][]"]').each(function(key, element){
            $(element).datetimepicker({format: 'YYYY-MM-DD'});
        });

        if($container.find('select').find('option').length>0)
            $container.find('select').trigger('change');
        else
            $container.find('agregar-fila').attr('disabled',true);

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

    $(document).on('change', 'select[name="ingresos[ingresos][detalle][cod_historia_clinica][]"]', function(){
        var $this       = $(this),
            $container  = $this.closest('#div-clone');

        aData = {
            cod_historia_clinica:$this.find(':selected').val(),
            cod_paciente:$('#cod_paciente').val()
        };

        $.ajax({
            url: _service + '?funcion=getinfoIngreso',
            dataType: "json",
            type: "GET",
            data: aData,
        }).done(function (xhr_data) {

            $('#ingresoHistorial').html('').html(xhr_data.infoTratamiento.datosComprobanteFac.Historial.Historial);
            $('#saldoAdeudado').val('').val(xhr_data.infoTratamiento.datosHistoriaClinicaCliente.deuda.value);

            $container.find('#ingresoscosto').val('').val(xhr_data.infoTratamiento.datosComprobanteFac[0].imp_total_historia_clinica);
            $container.find('#ingresoscancelado').val('').val(xhr_data.infoTratamiento.datosComprobanteFac[0].imp_canc_historia_clinica);
            $container.find('#ingresosadeudado').val('').val(xhr_data.infoTratamiento.datosComprobanteFac[0].imp_adeu_historia_clinica);
            $container.find('#ingresosdiente').val('').val($this.find(':selected').text());

            var $dataInicial = {
                costo: xhr_data.infoTratamiento.datosComprobanteFac[0].imp_total_historia_clinica,
                adeudado: xhr_data.infoTratamiento.datosComprobanteFac[0].imp_adeu_historia_clinica,
                cancelado:xhr_data.infoTratamiento.datosComprobanteFac[0].imp_canc_historia_clinica
            };

            localStorage.setItem('saldoInicial_'+$this.find(':selected').val(), JSON.stringify($dataInicial));
        });
    });

    $(document).on('blur', '#ingresosrecibido', function(){
        var $this                   = $(this),
            $parent                 = $this.closest('#div-clone'),
            $cod_historia_clinica   = $parent.find('select').find(':selected').val(),
            $dataInicial            = JSON.parse(localStorage.getItem('saldoInicial_'+$cod_historia_clinica)),
            $valRecibido            = parseFloat($this.val(),2),
            $valAdeudado            = parseFloat($dataInicial.adeudado,2),
            $valTratamiento         = parseFloat($dataInicial.costo,2),
            $valCancelado           = parseFloat($dataInicial.cancelado,2);

        $valRecibido = isNaN($valRecibido)?0:$valRecibido;

        if($valRecibido>$valAdeudado) {
            swal({
                title: "Error de Ingreso",
                text: "El valor Recibido es mayor al adeudado",
                type: "error",
                confirmButtonColor: "#039BE5"
            });

            $('#guardarIngreso').attr('disabled',true);
        }else {

            $parent.find('#ingresoscancelado').val($valCancelado+$valRecibido);
            $parent.find('#ingresosadeudado').val($valAdeudado-$valRecibido);
            $('#guardarIngreso').attr('disabled',false);
        }

        if($valRecibido==0) {
            $parent.find('#ingresosadeudado').val($dataInicial.adeudado);
            $parent.find('#ingresoscosto').val($dataInicial.costo);
            $parent.find('#ingresoscancelado').val($dataInicial.cancelado);
        }

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
            confirmButtonText: "Si, Guardar Ingreso!",
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
                        $('#panelTratamientos').fadeOut();

                        $('#formIngreso').find('button').data({'codpago':xhr_data.status,'codpaciente':$('#cod_paciente').val()});
                        $('#generarIngresoPdfPaciente,#enviarIngresoPaciente').attr('disabled', false);
                    } else
                        $.mostrarMensaje(2);

                    _button.attr('disabled',false);
                    _button.find('i').removeClass('fa-spinner fa-spin').addClass('fa-floppy-o');

                }).fail( function(xhr_error) {
                    $.mostrarMensaje(2);
                    _button.attr('disabled',false);
                    _button.find('i').removeClass('fa-spinner fa-spin').addClass('fa-floppy-o');
                });
            }
        });
    });

    $(document).on('click', '#generarIngresoPdfPaciente', function(){
        var $this   = $(this),
            $data   = $this.data(),
            $app    = '?app='+$.base64.encode('endodoncia'),
            $met    = '&met='+$.base64.encode('GenerarComprobanteIngresoPDF'),
            $arg    = '&arg='+$.base64.encode($data.codpago);

        window.location.href = $app+$met+$arg;
    });

    $(document).on('click', '#enviarIngresoPaciente', function(){

        var $this           = $(this),
            $data           = $this.data(),
            aData           = {};

        aData = {
            cod_pago:$data.codpago,
            cod_paciente:$data.codpaciente,
            type:2
        };

        $.ajax({
            url: _service + '?funcion=GenerarIngresoPDF',
            dataType: "json",
            type: "GET",
            data: aData,
            beforeSend: function(){
                $this.attr('disabled',true);
                $this.find('i').removeClass('fa-send').addClass('fa-spinner fa-spin');
            },
        }).done(function (xhr_data) {

            if(xhr_data.status) {
                swal({
                    title: "Envio de Correo!",
                    text: "El correo ha sido enviado correctamente",
                    type:"success",
                    confirmButtonColor: "#039BE5"
                });

            }else
                $.mostrarMensaje(2);

            $this.attr('disabled',false);
            $this.find('i').removeClass('fa-spinner fa-spin').addClass('fa-send');
        }).fail(function(xhr_error){
            $.mostrarMensaje(2);
            $this.attr('disabled',false);
            $this.find('i').removeClass('fa-spinner fa-spin').addClass('fa-send');
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

        $('select[name="ingresos[ingresos][detalle][cod_historia_clinica][]"]').first().select2('destroy').html('').select2({'placeholder': 'seleccionar'});
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
                $('#ingresosNumComprobante').val('').val(xhr_data.numeracion.numComprobanteIngreso);
            }
        });
    }
});