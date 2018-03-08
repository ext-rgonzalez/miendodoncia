( function( $ ){
    var source                  = _service + '?funcion=traerConsentimientos',
        tablePacientes          = '',
        tableMedicamentos       = '',
        sourcePacientes         = _service + '?funcion=traerPacientes';
        that    = this;

    $('#codconsentimiento').select2();
    $('#codhistoriaClinica').select2();

    table = $('#tablaConsentimientos').DataTable({
        "ajax": {
            url: source,
            type: 'POST'
        },
        "pageLength": 10,
        "serverSide": true,
        "bProcessing":  true,
        "bDeferRender": false,
        "order": [[4, "desc"]],
        //"bFilter": false,
        "fnRowCallback": function( nRow, aData, iDisplayIndex ) {
            if ( aData['cod_estado']=='BBB' )
                $(nRow).addClass( 'danger' );

            $(nRow).data('cod_paciente',aData.cod_paciente);

        },
        "aoColumns":    [
            {"mData": "nom_paciente"},
            {"mData": "num_config_dental"},
            {"mData": "img_paciente_consentimiento",
                "mRender": function( data, type, row ){
                    return '<img src="modules/endodoncia/adjuntos/'+data+'" style="max-width:40px;" class="image">';
                }
            },
            {"mData": "nom_config_consentimiento" },
            {"mData": "fec_paciente_consentimiento" },
            {"mData": "action",
                "mRender": function ( data, type, row ) {

                    var estado = row.cod_estado=='AAA'?'BBB':'AAA';

                    var template =
                        '<div class="btn-group">'+
                        '   <button type="button" class="btn btn-default btn-sm dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"> Acciones'+
                        '   </button>'+
                        '   <ul class="dropdown-menu dropdown-menu-right">'+
                        '       <li><a id="consentimiento-generapdf"    data-codconsentimiento="'+row.cod_paciente_consentimiento+'"><i class="fa fa-file-pdf-o"></i> Generar Pdf</a></li>'+
                        '       <li><a id="consentimiento-changestatus" data-codconsentimiento="'+row.cod_paciente_consentimiento+'" data-estado="'+estado+'"><i class="fa fa-power-off"></i> Cambiar Estado </a></li>'+
                        '       <li><a id="consentimiento-delete"       data-codconsentimiento="'+row.cod_paciente_consentimiento+'"><i class="fa fa-trash"></i> Eliminar</a></li>'+
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

    $('#formConsentimiento').submit( function( e ) {
        e.preventDefault();

        var _button = $(this).find('button[type="submit"]'),
            _data   = $(this).serializefiles();

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

            if ( xhr_data.data.length>0 ){

                var tr = $('#tablaConsentimientos').find('[id="consentimiento-generapdf"][data-codconsentimiento="'+xhr_data.data[0].cod_paciente_consentimiento+'"]').closest('tr');

                if(tr.length)
                    table.row(tr).data(xhr_data.data[0]).draw();
                else
                    table.rows.add(xhr_data.data).draw();

                $.mostrarMensaje(1);

                $('#modalConsentimiento').modal('hide');
                document.getElementById("formConsentimiento").reset();

                var $app    = '?app='+$.base64.encode('endodoncia'),
                    $met    = '&met='+$.base64.encode('GenerarConsentimientoPDF'),
                    $arg    = '&arg='+$.base64.encode(xhr_data.data[0].cod_paciente_consentimiento);

                window.location.href = $app+$met+$arg;

            }else
                $.mostrarMensaje(2);

            _button.attr('disabled',false);
            _button.find('i').removeClass('fa-spinner fa-spin').addClass('fa-floppy-o');

        });
    });

    $(document).on('click', '#crearOdontologo', function(e){
        e.preventDefault();

        document.getElementById("nuevoOdontologo").reset();
        $('#modalnuevoOdontologo').modal('show');
    });

    $(document).on('click', '#odontologo-edit', function(){
        var $this    = $(this),
            $modal   = $('#modalnuevoOdontologo'),
            $data    = $this.data(),
            $infoUser= JSON.parse(decodeURIComponent($data.infousuario));

        $.each($infoUser, function(key,val){
            $modal.find('input[name="'+key+'"]').val(val);
            $modal.find('textarea[name="'+key+'"]').html(val);
            $modal.find('select[name="'+key+'"] option[value="'+val+'"]').prop('selected','selected');
        });

        $modal.modal('show');
    });

    $(document).on('click', '#odontologo-changestatus', function(){
        var $this   = $(this),
            $data   = $this.data(),
            aData   = {};

        aData = {
            cod_usuario:$data.codusuario,
            estado: $data.estado
        };

        $.ajax({
            url: _service + '?funcion=cambiarEstadoOdontologo',
            dataType: "json",
            type: "GET",
            data: aData,
        }).done(function (xhr_data) {
            if ( xhr_data.data.length>0 ) {

                var tr = $('#tablaOdontologos').find('[id="odontologo-changestatus"][data-codusuario="' + xhr_data.data[0].cod_usuario + '"]').closest('tr');

                if (tr.length)
                    table.row(tr).data(xhr_data.data[0]).draw();
            }
        });
    });

    $(document).on('click', '#crearConsentimiento', function(){
        document.getElementById("formConsentimiento").reset();
        $('#modalConsentimiento').modal('show');
    });

    $( document).on( 'click', '#seleccionarPaciente', function(){
        $( '#modalPacientes' ).modal( 'show' );
    });

    $( document ).on('click', '#seleccionPaciente', function(){
        var $this       = $(this).closest('tr'),
            infoUsuario = JSON.parse( decodeURIComponent( $this.data( 'info_paciente' ) )),
            aData       = {};

        $( '#nom_paciente' ).val( '' ).val( infoUsuario.nom_paciente );
        $( '#cod_paciente' ).val( '' ).val( infoUsuario.cod_paciente );
        $( '#seleccionarDiente' ).attr( 'disabled', false );
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
                $('#codhistoriaClinica').select2('destroy');
                $('#codhistoriaClinica').html('');
            },
        }).done(function (xhr_data) {

            if(xhr_data.tratamientos.length) {

                $.each(xhr_data.tratamientos, function(key,aTratamiento){
                    $('#codhistoriaClinica').append($('<option>').attr('value',aTratamiento.cod_historia_clinica).html(aTratamiento.value));
                });

                $('#capturarHuella').attr('disabled',false);

            }else {
                swal({
                    title: "Error",
                    text: "El paciente seleccionado no tiene historias clinicas en el sistema",
                    type: "error",
                    confirmButtonColor: "#039BE5"
                });

                $('#capturarHuella').attr('disabled',true);
                $('#enviarIngreso').attr('disabled',true);
            }

            $('#codhistoriaClinica').select2();

        });
    });

    $(document).on('click','#consentimiento-generapdf', function(){
        var $this   = $(this),
            $data   = $this.data(),
            $app    = '?app='+$.base64.encode('endodoncia'),
            $met    = '&met='+$.base64.encode('GenerarConsentimientoPDF'),
            $arg    = '&arg='+$.base64.encode($data.codconsentimiento);

        window.location.href = $app+$met+$arg;
    });

}( jQuery ))