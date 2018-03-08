( function( $ ){

    var tablePacientes          = '',
        tableMedicamentos       = '',
        sourcePacientes         = _service + '?funcion=traerPacientes';

    if(_codImagen)
        $('#content_tooths').find('div[data-coddiente="'+_codImagen+'"]').addClass('active1');

    $( '#cod_config_control' ).select2({'placeholder':'Seleccione',allowClear: true});
    $( '#no_cod_ant_fam' ).select2({'placeholder':'Seleccione',allowClear: true});
    $( '#no_cod_ant_odo' ).select2({'placeholder':'Seleccione',allowClear: true});
    $( '#no_cod_ant_per' ).select2({'placeholder':'Seleccione',allowClear: true});
    $( '#no_cod_ale' ).select2({'placeholder':'Seleccione',allowClear: true});

    $( '#no_cod_tej_bla' ).select2({'placeholder':'Seleccione',allowClear: true});
    $( '#no_cod_tej_den' ).select2({'placeholder':'Seleccione',allowClear: true});
    $( '#no_cod_tej_per' ).select2({'placeholder':'Seleccione',allowClear: true});
    $( '#no_cod_tej_peri' ).select2({'placeholder':'Seleccione',allowClear: true});
    $( '#no_cod_tej_pul' ).select2({'placeholder':'Seleccione',allowClear: true});
    $( '#no_cod_diagnostico' ).select2({'placeholder':'Seleccione',allowClear: true});
    $( '#no_cod_anarad' ).select2({'placeholder':'Seleccione',allowClear: true});

    $( '#no_cod_med' ).select2({
        minimumInputLength: 3,
        placeholder: 'Buscar',
        escapeMarkup: function (e) {return e; },
        formatResult: function(e){return e.text; },
        formatSelection: function(e){return e.text; },
        ajax: {
            url: _service + '?funcion=consultaMedicamentos',
            dataType: 'json',
            type: "GET",
            cache: false,
            data: function (term) {return {term: term.term};},
            processResults: function (data,page) {
                return {results: $.map(data, function (item) {return {text: item.result, id: item.codigo}})};
            }
        }
    });

    $( '#cod_ciudad' ).select2({
        minimumInputLength: 3,
        placeholder: 'Buscar',
        escapeMarkup: function (e) {return e; },
        formatResult: function(e){return e.text; },
        formatSelection: function(e){return e.text; },
        ajax: {
            url: _service + '?funcion=consultaCiudad',
            dataType: 'json',
            type: "GET",
            cache: false,
            data: function (term) {return {term: term.term};},
            processResults: function (data,page) {
                return {results: $.map(data, function (item) {return {text: item.result, id: item.codigo}})};
            }
        }
    });

    $( '#fec_prox_pago').datetimepicker({
        format: "YYYY-MM-DD"
    });

    $( '#fec_nacimiento_paciente' ).datetimepicker({
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
        $( '#modalPacientes' ).modal( 'show' );
    });

    $( document).on( 'click', '#seleccionarMedicamentos', function(){
        document.getElementById("crearMedicamentoCsv").reset();
        tableMedicamentos.clear().draw();
        $( '#modalMedicamentos' ).modal( 'show' );

    });

    $( document ).on('click', '#seleccionPaciente', function(){
        var $this       = $(this).closest('tr'),
            infoUsuario = JSON.parse( decodeURIComponent( $this.data( 'info_paciente' ) ) );

        $( '#nom_paciente' ).val( '' ).val( infoUsuario.nom_paciente );
        $( '#cod_paciente' ).val( '' ).val( infoUsuario.cod_paciente );
        $( '#seleccionarDiente' ).attr( 'disabled', false );
        $( '#modalPacientes' ).modal( 'hide' );

    });

    $( document ).on( 'click', '#seleccionarDiente', function(){
        $( '#modalOdontograma' ).modal( 'show' );
    });

    $( document ).on( 'click', '#content_tooths div[data-ref="cod_imagen_dental"]',function(){
        var $this               = $(this),
            $contentTooths      = $this.closest( 'div#content_tooths'),
            $cod_config_dental  = $this.data('coddiente'),
            $cod_paciente       = $( '#cod_paciente' ).val(),
            aData               = {},
            infoDesobturacion;

        $( 'div[data-ref="cod_imagen_dental"]' ).removeClass( "active1" );
        $this.addClass( "active1" );

        aData = {
            cod_config_dental: $cod_config_dental,
            cod_paciente: $cod_paciente
        };

        $.ajax({
            url: _service + '?funcion=consultaDienteHistoriaDatos',
            dataType: "json",
            type: "GET",
            data: aData,
        }).done(function (xhr_data) {

            if(xhr_data.codConfigDental.cod_config_dental){
                $( '#nom_config_dental' ).val(xhr_data.codConfigDental.nom_config_dental);
                $( '#cod_config_dental' ).val(xhr_data.codConfigDental.cod_config_dental);
                $('#ind_retratamiento').prop('checked',false).attr('disabled',false);

                if(parseInt(xhr_data.codConfigDental.ind_temporales))
                    $('#ind_temporales').prop('checked',true).attr('disabled',true);
                else
                    $('#ind_temporales').prop('checked',false).attr('disabled',true);

                $('#infoConductos').html('').append(xhr_data.panelDiagnostico.panel);

                $('#infoDesobturacion').html('').append(xhr_data.desobturacion.desobturacion);

                infoDesobturacion = $('#infoDesobturacion').find('div.panel');

                if( !$('#ind_desobturacion').is('checked') )
                    infoDesobturacion.fadeOut();
                else
                    infoDesobturacion.fadeIn();

                $( '#registroRadiograficoBD' ).remove();

                if(parseInt(xhr_data.retratamiento.retratamiento)) {
                    swal({
                        title: "",
                        text: "Este diente ya ha sido tratado anteriormente, desea marcarlo como retratamiento por fracaso en el procedimiento ?",
                        type: "warning",
                        showCancelButton: true,
                        confirmButtonColor: "#039BE5",
                        confirmButtonText: "Si, Marcar como retratamiento",
                        cancelButtonText: "No, Ignorar!",
                        closeOnConfirm: true,
                        closeOnCancel: true
                    }, function (isConfirm) {
                        if (isConfirm) {
                            $('#ind_retratamiento').prop('checked',true).attr('disabled',true);
                            $('#historialEvolucion').fadeIn();
                            $('#historialEvolucion').find('textarea').html(xhr_data.evolucionesDiente.evoluciones_diente);
                            $('#registroRadiografico').append(xhr_data.imagenesDiente.imagenes_diente).parent('div').fadeIn();
                            //$('#superbox').SuperBox();
                        }else {
                            $('#ind_retratamiento').prop('checked',false).attr('disabled',true);
                            $('#historialEvolucion').fadeOut();
                            $('#historialEvolucion').find('textarea').html('');
                            $('#registroRadiografico').html('').parent('div').fadeOut();
                        }
                    });
                }

            }else {
                swal({
                    title:"Diente sin Configuracion!",
                    text:"El diente seleccionado no tiene ninguna configuracion.",
                    type:"error",
                    confirmButtonText : 'Aceptar',
                    confirmButtonColor: "#039BE5"
                });
            }

            $( '#modalOdontograma' ).modal( 'hide' );
        });
    });

    $( document).on( 'change', '#no_cod_tej_bla', function(){
        var $this       = $(this);
            container   = $this.closest('fieldset');

        renderOtroTejidos( $this, container );
    });

    $( document).on( 'change', '#no_cod_tej_den', function(){
        var $this       = $(this);
        container   = $this.closest('fieldset');

        renderOtroTejidos( $this, container );
    });

    $( document).on( 'change', '#no_cod_tej_per', function(){
        var $this       = $(this);
        container   = $this.closest('fieldset');

        renderOtroTejidos( $this, container );
    });

    $( document).on( 'change', '#no_cod_tej_peri', function(){
        var $this       = $(this);
        container   = $this.closest('fieldset');

        renderOtroTejidos( $this, container );
    });

    $( document).on( 'change', '#no_cod_tej_pul', function(){
        var $this       = $(this);
        container   = $this.closest('fieldset');

        renderOtroTejidos( $this, container );
    });

    $( document).on('change', '#no_cod_diagnostico', function(){

        var $this   = $(this),
            $select = $('#no_cod_subdiagnostico');

        $.ajax({
            url: _service + '?funcion=traerSubDiagnosticos',
            dataType: "json",
            type: "GET",
            data: {coddiagnosticos: $this.find(':selected').text()},
        }).done(function (xhr_data) {

            if($select.hasClass('select2-hidden-accessible'))
                $select.select2('destroy');

            $select.html('');

            $.each( xhr_data, function(k,v){
                $('#no_cod_subdiagnostico').append($('<option>').attr({'value': v.cod_config_diagnosticos}).html(v.des_config_diagnosticos));
            });

            $select.select2({placeholder: 'Seleccione',allowClear: true});
        });
    });

    $( document).on( 'click', '#ind_desobturacion', function(){
        var $this = $(this),
            checked = $this.prop('checked');

        if(checked)
            $('#infoDesobturacion').find('div.panel').fadeIn();
        else
            $('#infoDesobturacion').find('div.panel').fadeOut();

    });

    $('#nuevoPaciente').submit( function( e ) {
        e.preventDefault();

        var _button = $(this).find('button[type="submit"]'),
            _data   = $(this).serializefiles();

        $.ajax({
            url: _service + '?funcion=Pacientes',
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

            if ( xhr_data.data.length>0 ){

                var tr = $('#tablaPacientes').find('[id="paciente-edit"][data-codpaciente="' + xhr_data.data[0].cod_paciente + '"]').closest('tr');

                if (tr.length)
                    tablePacientes.row(tr).data(xhr_data.data[0]).draw();

                $( '#nom_paciente' ).val( '' ).val(  xhr_data.data[0].nom_paciente );
                $( '#cod_paciente' ).val( '' ).val(  xhr_data.data[0].cod_paciente );
                $( '#seleccionarDiente' ).attr( 'disabled', false );

                $.mostrarMensaje(1);

                $('#modalPacientes').modal('hide');

                document.getElementById("nuevoPaciente").reset();
            }else{
                $.mostrarMensaje(2);
            }


            _button.attr('disabled',false);
            _button.find('i').removeClass('fa-spinner fa-spin').addClass('fa-floppy-o');ç

        }).fail( function(){
            $.mostrarMensaje(2);
            _button.attr('disabled',false);
            _button.find('i').removeClass('fa-spinner fa-spin').addClass('fa-floppy-o');ç
        });
    });

    $(document).on('click', '#paciente-edit', function(){
        var $this    = $(this),
            $panel   = $('div#nuevoPacientes'),
            $data    = $this.data(),
            $infoUser= JSON.parse(decodeURIComponent($data.infopaciente));

        $.each($infoUser, function(key,val){
            var isselect2 = false;

            $panel.find('input[name="paciente['+key+']"]').val(val);
            $panel.find('input[name="paciente['+key+']"]').val(val);
            $panel.find('textarea[name="paciente['+key+']"]').html(val);
            $panel.find('input[name="paciente['+key+']"]').prop('checked',val=='Si');


            if($panel.find('select[name="paciente['+key+']"]').hasClass('select2-hidden-accessible')){
                $panel.find('select[name="paciente['+key+']"]').select2('destroy');
                isselect2 = true;
            }

            $panel.find('select[name="paciente['+key+']"] option[value="'+val+'"]').prop('selected','selected');

            if(isselect2)
                $panel.find('select[name="paciente['+key+']"]').select2({allowClear: true});

            if(key=='cod_ciudad') {
                $panel.find('select[name="paciente['+key+']"]').select2('destroy');
                $panel.find('select[name="paciente['+key+']"]').html('');
                $panel.find('select[name="paciente['+key+']"]').append($('<option>').attr('value',val).html($infoUser.nom_ciudad).prop('selected', true));
                $panel.find('select[name="paciente['+key+']"]').select2({allowClear: true});
            }

        });

        $('a[href="#nuevoPacientes"]').trigger('click');
    });

    $(document).on('click', '#paciente-changestatus', function(){
        var $this   = $(this),
            $data   = $this.data(),
            aData   = {};

        aData = {
            cod_paciente:$data.codpaciente,
            estado: $data.estado
        };

        $.ajax({
            url: _service + '?funcion=cambiarEstadoPaciente',
            dataType: "json",
            type: "GET",
            data: aData,
        }).done(function (xhr_data) {
            if ( xhr_data.data.length>0 ) {

                var tr = $('#tablaPacientes').find('[id="paciente-changestatus"][data-codpaciente="' + xhr_data.data[0].cod_paciente + '"]').closest('tr');

                if (tr.length)
                    tablePacientes.row(tr).data(xhr_data.data[0]).draw();
            }
        });
    });

    $(document).on('click', '#paciente-delete', function(){
        var $this   = $(this),
            $data   = $this.data(),
            aData   = {};

        aData = {cod_paciente:$data.codpaciente};

        swal({
            title: "Esta seguro?",
            text: "Esta accion eliminara todos los datos paciente, incluyendo sus historial en la plataforma",
            type: "warning",
            showCancelButton: true,
            confirmButtonColor: "#039BE5",
            confirmButtonText: "Si, Borrar!",
            cancelButtonText: "No, Cancelar!",
            closeOnConfirm: false,
            closeOnCancel: false
        }, function (isConfirm) {
            if (isConfirm) {

                $.ajax({
                    url: _service + '?funcion=eliminaPaciente',
                    dataType: "json",
                    type: "GET",
                    data: aData,
                }).done(function (xhr_data) {

                    swal({
                        title: "Borrado!",
                        text: "Transaccion realizada correctamente",
                        type:"success",
                        confirmButtonColor: "#039BE5"
                    });

                    var tr = $('#tablaPacientes').find('[id="paciente-delete"][data-codpaciente="' + $data.codpaciente + '"]').closest('tr');

                    if (tr.length)
                        tr.remove();

                }).fail( function(xhr_error){
                    $.mostrarMensaje(2);
                });

            }
            else {
                swal({
                    title: "Cancelado",
                    text: "Transaccion cancelada",
                    type: "error",
                    confirmButtonColor: "#039BE5"
                });
            }
        });
    });

    tableMedicamentos = $( '#tablaMedicamentos' ).DataTable({
        "pageLength": 20,
        'aaData': [],
        "serverSide": false,
        "bProcessing":  false,
        "bDeferRender": false,
        "bFilter": true,
        "aoColumns":    [
            {"mData": "cod_unico_config_medicamentos"},
            {"mData": "nom_config_medicamentos"},
            {"mData": "des_config_medicamentos"},
            {"mData": "forma_farma_config_medicamentos"},
        ]
    });

    $('#crearMedicamento').submit(function (e) {
        e.preventDefault();
        var _button = $(this).find('button[type="submit"]'),
            _data   = $(this).serializefiles();

        $.ajax({
            url: _service + '?funcion=Medicamentos',
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
            $('#no_cod_med').select2('destroy').html('');
            $('#no_cod_med').append($('<option>').attr({value:xhr_data.data[0].cod_config_medicamentos}).html(xhr_data.data[0].nom_config_medicamentos).prop('selected',true));

            $( '#no_cod_med' ).select2({
                minimumInputLength: 3,
                placeholder: 'Buscar',
                escapeMarkup: function (e) {return e; },
                formatResult: function(e){return e.text; },
                formatSelection: function(e){return e.text; },
                ajax: {
                    url: _service + '?funcion=consultaMedicamentos',
                    dataType: 'json',
                    type: "GET",
                    cache: true,
                    data: function (term) {return {term: term.term};},
                    processResults: function (data,page) {
                        return {results: $.map(data, function (item) {return {text: item.result, id: item.codigo}})};
                    }
                }
            });

            document.getElementById("crearMedicamento").reset();
            $('#modalMedicamentos').modal('hide');
            _button.attr('disabled',false);
            _button.find('i').removeClass('fa-spinner fa-spin').addClass('fa-floppy-o');
        }).fail( function(xhr_error){
            $.mostrarMensaje(2);
        });
    });

    $('#crearMedicamentoCsv').submit(function (e) {
        e.preventDefault();
        var _data = $(this).serializefiles();
        $.ajax({
            url: _service + '?funcion=PrecacargarCsvMedicamentos',
            dataType: "json",
            type: "POST",
            data: _data,
            processData: false,
            contentType: false,
            beforeSend: function(){ tableMedicamentos.clear().draw();},
        }).done(function (xhr_data) {
            tableMedicamentos.rows.add(xhr_data).draw();
            $('#guardarMedicamentoCsv').attr('disabled',false);
        }).fail( function(xhr_error){
            $('#guardarMedicamentoCsv').attr('disabled',true);
        });
    });

    $(document).on('click', '#guardarMedicamentoCsv', function(){
        var $this   = $(this),
            $data   = JSON.stringify(tableMedicamentos.rows().data());

        $.ajax({
            url: _service + '?funcion=MedicamentosCSv',
            dataType: "json",
            type: "POST",
            data: {data: $data},
            beforeSend: function(){
                $this.attr('disabled',true);
                $this.find('i').removeClass('fa-floppy-o').addClass('fa-spinner fa-spin');
            },
        }).done(function (xhr_data) {
            $.mostrarMensaje(1);
            tableMedicamentos.clear().draw();
            $this.find('i').removeClass('fa-spinner fa-spin').addClass('fa-floppy-o');
            document.getElementById("crearMedicamentoCsv").reset();
            $('#modalMedicamentos').modal('hide');
        }).fail( function(xhr_error){
            $.mostrarMensaje(2);
        });
    });

    $(document).on('click', '#agregarEvolucion', function(e){
        e.preventDefault();
        $('#nuevaEvolucion').fadeIn().find('textarea').focus();
    });

    $(document).on('blur', '#nueva_evolucion', function(){

        var $this = $(this);

        if($this.val()=='')
            $('#nuevaEvolucion').fadeOut();
    });

    $('#historiaClinica').submit( function(e){

        e.preventDefault();
        var _button = $(this).find('button[type="submit"]'),
            _data   = $(this).serializefiles();

        swal({
            title: "",
            text: "Desea guardar esta historia clinica",
            type: "warning",
            showCancelButton: true,
            confirmButtonColor: "#039BE5",
            confirmButtonText: "Si, Guardar Historia Clinica!",
            cancelButtonText: "No, Cancelar!",
            closeOnConfirm: false,
            closeOnCancel: true
        }, function (isConfirm) {
            if (isConfirm) {

                $.ajax({
                    url: _service + '?funcion=HistoriaClinica',
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

                    console.log(xhr_data);

                    _button.attr('disabled',false);
                    _button.find('i').removeClass('fa-spinner fa-spin').addClass('fa-floppy-o');
                    document.getElementById("historiaClinica").reset();

                    $.mostrarMensaje(1);

                    $('#accionesPosteriores').find('button').data({'codhistoria':xhr_data.cod_historia_clinica,'cedPaciente':xhr_data.ced_paciente});
                    $('#accionesPosteriores').modal('show');

                }).fail( function(){
                    swal({
                        title: "Cancelado",
                        text: "Ocurrio un error, si persiste comuniquese con el administrador",
                        type: "error",
                        confirmButtonColor: "#039BE5"
                    });

                    _button.attr('disabled',false);
                    _button.find('i').removeClass('fa-spinner fa-spin').addClass('fa-floppy-o');
                });
            }
        });
    });

    $(document).on('click', '#agregarIngresos', function(){
        var $this   = $(this),
            $data   = $this.data(),
            $app    = '?app='+$.base64.encode('endodoncia'),
            $met    = '&met='+$.base64.encode('HistoriaClinica'),
            $arg    = '&arg='+$.base64.encode('1,2,HistoriaClinica*2,'+$data.codhistoria+',ingreso,'+$data.cedPaciente);

        window.location.href = $app+$met+$arg;
    });

    $(document).on('click', '#enviarOdontologo', function(){
        var $this   = $(this),
            $data   = $this.data(),
            $app    = '?app='+$.base64.encode('endodoncia'),
            $met    = '&met='+$.base64.encode('HistoriaClinica'),
            $arg    = '&arg='+$.base64.encode('1,2,HistoriaClinica*2,'+$data.codhistoria+',enviaOdontologo');

        window.location.href = $app+$met+$arg;
    });

    $(document).on('click', '#enviarPaciente', function(){
        var $this   = $(this),
            $data   = $this.data(),
            $app    = '?app='+$.base64.encode('endodoncia'),
            $met    = '&met='+$.base64.encode('HistoriaClinica'),
            $arg    = '&arg='+$.base64.encode('1,2,HistoriaClinica*2,'+$data.codhistoria+',enviaPaciente');

        window.location.href = $app+$met+$arg;
    });

    $(document).on('click', '#cerraryenviar', function(){
        var $this   = $(this),
            $data   = $this.data(),
            $app    = '?app='+$.base64.encode('endodoncia'),
            $met    = '&met='+$.base64.encode('HistoriaClinica'),
            $arg    = '&arg='+$.base64.encode('1,2,HistoriaClinica*2,'+$data.codhistoria+',cerrarEnviar');

        window.location.href = $app+$met+$arg;
    });

    $(document).on('click', '#limpiarPaciente', function(){
        document.getElementById("nuevoPaciente").reset();
        $('input[name="paciente[cod_paciente]"]').val('');
    });

    function renderOtroTejidos( $this, container ) {

        if(!$this.find(':selected').length)
            container.find('input[id="otro_'+$this.attr('id')+'"]').val('').parent('div').fadeOut();

        $this.find(':selected').each( function(key, option){
            if( $(option).text()=='Otro')
                container.find('input[id="otro_'+$this.attr('id')+'"]').val('').focus().parent('div').fadeIn();
            else
                container.find('input[id="otro_'+$this.attr('id')+'"]').val('').parent('div').fadeOut();
        });
    }

}( jQuery ))