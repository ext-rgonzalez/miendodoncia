( function( $ ){
    var source  = _service + '?funcion=traerHistoriaClinica&search='+_search,
        that    = this;

    $('#cod_odontologo').select2({placeholder: 'Seleccionar odontologo'});
    $('#cod_odontologoremision').select2({placeholder: 'Seleccionar odontologo'});

    $( '#fec_prox_pago').datetimepicker({
        format: "YYYY-MM-DD"
    });

    table = $('#tablaHistoriaClinica').DataTable({
        "ajax": {
            url: source,
            type: 'POST'
        },
        "pageLength": 20,
        "serverSide": true,
        "bProcessing":  true,
        "bDeferRender": true,
        "order": [[9, "desc"]],
        //"bFilter": false,
        "fnRowCallback": function( nRow, aData, iDisplayIndex ) {
            if ( aData['cod_estado']=='HCT' )
                $(nRow).addClass( 'info' );

            $(nRow).data('cod_historia_clinica',aData.cod_historia_clinica);

        },
        "aoColumns":    [
            {"mData": "nom_paciente"},
            {"mData": "ced_paciente"},
            {"mData": "diente_tratado"},
            {"mData": "motivo_historia_clinica" },
            {"mData": "que_historia_clinica" },
            {"mData": "como_historia_clinica" },
            {"mData": "cuando_historia_clinica" },
            {"mData": "donde_historia_clinica" },
            {"mData": "porque_historia_clinica" },
            {"mData": "fec_historia_clinica"},
            {"mData": "fec_mod_historia_clinica"},
            {"mData": "action",
                "mRender": function ( data, type, row ) {

                    var estado      = row.cod_estado=='HCT'?'AAA':'HCT';
                    var accionestado= row.cod_estado=='HCT'?'Abrir':'Cerrar';
                    var remision    = row.cod_estado=='HCT'?'<li><a id="historiaclinica-remision" data-codhistoria="'+row.cod_historia_clinica+'" data-codpaciente="'+row.cod_paciente+'"><i class="fa fa-address-card"></i> Remisiones</a></li>':'';
                    var edicion     = row.cod_estado!='HCT'?'<li><a id="historiaclinica-edit"     data-codhistoria="'+row.cod_historia_clinica+'" data-codpaciente="'+row.cod_paciente+'"><i class="fa fa-refresh"></i> Editar</a></li>':'<li><a id="historiaclinica-detalle"     data-codhistoria="'+row.cod_historia_clinica+'" data-codpaciente="'+row.cod_paciente+'"><i class="fa fa-eye"></i> Ver Detalle</a></li>';
                    var eliminar    = row.cod_estado!='HCT'?'<li><a id="historiaclinica-delete"   data-codhistoria="'+row.cod_historia_clinica+'" data-codpaciente="'+row.cod_paciente+'"><i class="fa fa-trash"></i> Eliminar</a></li>':'';

                    var template =
                        '<div class="btn-group">'+
                        '   <button type="button" class="btn btn-default btn-sm dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"> Acciones'+
                        '   </button>'+
                        '   <ul class="dropdown-menu dropdown-menu-right">'+
                                edicion+
                        '       <li><a id="historiaclinica-generapdf"        data-codhistoria="'+row.cod_historia_clinica+'" data-codpaciente="'+row.cod_paciente+'"><i class="fa fa-file-pdf-o"></i> Generar PDF </a></li>'+
                                remision+
                        '       <li><a id="historiaclinica-evoluciones"      data-codhistoria="'+row.cod_historia_clinica+'" data-codpaciente="'+row.cod_paciente+'" data-codconfigdental="'+row.cod_config_dental+'"><i class="fa fa-history"></i> Evoluciones </a></li>'+
                        '       <li><a id="historiaclinica-enviarodontologo" data-codhistoria="'+row.cod_historia_clinica+'" data-codpaciente="'+row.cod_paciente+'"><i class="fa fa-share"></i> Enviar a Odontologo </a></li>'+
                        '       <li><a id="historiaclinica-enviarpaciente"   data-codhistoria="'+row.cod_historia_clinica+'" data-codpaciente="'+row.cod_paciente+'"><i class="fa fa-share"></i> Enviar a Paciente </a></li>'+
                        '       <li><a id="historiaclinica-estadocuenta"     data-codhistoria="'+row.cod_historia_clinica+'" data-codpaciente="'+row.cod_paciente+'" data-codconfigdental="'+row.cod_config_dental+'"><i class="fa fa-money"></i> Estado de Cuenta </a></li>'+
                        '       <li><a id="historiaclinica-changestatus"     data-codhistoria="'+row.cod_historia_clinica+'" data-codpaciente="'+row.cod_paciente+'" data-estado="'+estado+'"><i class="fa fa-power-off"></i> '+accionestado+' Historia </a></li>'+
                                eliminar+
                        '   </ul>'+
                        '</div>';

                    return template;
                },
                "bSortable": false
            }
        ],
        "initComplete": function ( settings, json) {
            var button = '';

            if(!_accion.length) {
                switch (_accion.accion) {
                    case('ingreso'):
                        button = $('#tablaHistoriaClinica').find('[id="historiaclinica-estadocuenta"][data-codhistoria="' + _accion.codHistoria + '"]');
                        break;
                    case('enviaOdontologo'):
                        button = $('#tablaHistoriaClinica').find('[id="historiaclinica-enviarodontologo"][data-codhistoria="' + _accion.codHistoria + '"]');
                        break;
                    case('enviaPaciente'):
                        button = $('#tablaHistoriaClinica').find('[id="historiaclinica-enviarPaciente"][data-codhistoria="' + _accion.codHistoria + '"]');
                        break;
                    case('cerrarEnviar'):
                        button = $('#tablaHistoriaClinica').find('[id="historiaclinica-remision"][data-codhistoria="' + _accion.codHistoria + '"]');
                        break;
                }

                if(button.length)
                    $(button).trigger('click');

            }
        }
    });


    $(document).on('click', '#historiaclinica-edit', function(){
        var $this   = $(this),
            $data   = $this.data(),
            $app    = '?app='+$.base64.encode('endodoncia'),
            $met    = '&met='+$.base64.encode('CrearHistoriaClinica'),
            $arg    = '&arg='+$.base64.encode('1,2,HistoriaClinica*2,'+$data.codhistoria+','+$data.codpaciente);

        window.location.href = $app+$met+$arg;
    });

    $(document).on('click', '#historiaclinica-detalle', function(){
        var $this   = $(this),
            $data   = $this.data(),
            $app    = '?app='+$.base64.encode('endodoncia'),
            $met    = '&met='+$.base64.encode('CrearHistoriaClinica'),
            $arg    = '&arg='+$.base64.encode('1,2,HistoriaClinica*2,'+$data.codhistoria+','+$data.codpaciente+',true');

        window.location.href = $app+$met+$arg;
    });

    $(document).on('click', '#historiaclinica-generapdf', function(){
        var $this   = $(this),
            $data   = $this.data(),
            $app    = '?app='+$.base64.encode('endodoncia'),
            $met    = '&met='+$.base64.encode('GenerarHistoriaClinicaPDF'),
            $arg    = '&arg='+$.base64.encode($data.codhistoria);

        window.location.href = $app+$met+$arg;
    });

    $(document).on('click', '#historiaclinica-enviarodontologo', function(){
        var $this   = $(this),
            $data   = $this.data(),
            aData   = {};

        $('#cod_odontologo').val(0).trigger('change');
        $('#modalOdontologo').find('button').data({'codhistoria':$data.codhistoria,'codpaciente':$data.codpaciente});
        $('#modalOdontologo').modal('show');

    });

    $(document).on('click', '#historiaclinica-enviarpaciente', function(){
        var $this   = $(this),
            $data   = $this.data(),
            aData   = {};

        aData = {
            cod_historiaclinica:$data.codhistoria,
            cod_paciente:$data.codpaciente,
            type:2
        };

        $.ajax({
            url: _service + '?funcion=GenerarHistoriaClinicaPDF',
            dataType: "json",
            type: "GET",
            data: aData,
            beforeSend: function(){
                $this.attr('disabled',true);
                $this.find('i').removeClass('fa-share').addClass('fa-spinner fa-spin');
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
            $this.find('i').removeClass('fa-spinner fa-spin').addClass('fa-share');
        });
    });

    $(document).on('click', '#enviarHistoria', function(){
        var $this           = $(this),
            $data           = $this.data(),
            $cod_odontologos= {},
            aData           = {};

        if($('#cod_odontologo').find(':selected').length) {

            $cod_odontologos = $.map($('#cod_odontologo').serializeArray(), function(i,v){return i.value;}).join(',');

            aData = {
                cod_historiaclinica:$data.codhistoria,
                cod_paciente:$data.codpaciente,
                cod_odontologo: $cod_odontologos,
                type:1
            };


            $.ajax({
                url: _service + '?funcion=GenerarHistoriaClinicaPDF',
                dataType: "json",
                type: "GET",
                data: aData,
                beforeSend: function(){
                    $this.attr('disabled',true);
                    $this.find('i').removeClass('fa-send').addClass('fa-spinner fa-spin');
                },
            }).done(function (xhr_data) {

                if(xhr_data.status) {
                    $('#modalOdontologo').modal('hide');
                    $('#cod_odontologo').val(0).trigger('change');

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
            });
        }else
            $('#cod_odontologo').focus();


    });

    $(document).on('click', '#historiaclinica-changestatus', function(){
        var $this   = $(this),
            $data   = $this.data(),
            aData   = {};

        aData = {
            cod_historia_clinica:$data.codhistoria,
            estado: $data.estado
        };

        $.ajax({
            url: _service + '?funcion=cambiarEstadoHistoriaClinica',
            dataType: "json",
            type: "GET",
            data: aData,
        }).done(function (xhr_data) {
            if ( xhr_data.data.length>0 ) {

                var tr = $('#tablaHistoriaClinica').find('[id="historiaclinica-changestatus"][data-codhistoria="' + xhr_data.data[0].cod_historia_clinica+ '"]').closest('tr');

                if (tr.length)
                    table.row(tr).data(xhr_data.data[0]).draw();
            }
        });
    });

    $(document).on('click', '#historiaclinica-remision', function(){

        var $this           = $(this),
            $data           = $this.data(),
            aData           = {},
            select          = $('#selectimagenes');

        aData = {
            cod_historia_clinica:$data.codhistoria,
            cod_paciente: $data.codpaciente
        };

        if(select.hasClass('select2-hidden-accessible'))
            select.select2('destroy');

        select.html('').attr('disabled',true);

        $.ajax({
            url: _service + '?funcion=infoRemisiones',
            dataType: "json",
            type: "GET",
            data: aData,
        }).done(function (xhr_data) {
            if ( xhr_data.lista.length>0 )
                $('#imagenesRemision').html('').append(xhr_data.panel.imagenes);
            else
                panelRemision.html('<div class="alert alert-info">Esta historia Clinica no tiene imagenes adjuntas, debe abrir la historia, editarla, adjuntar las imagenes y cerrarla nuevamente para poder enviar la remision</div>');

            select.select2();

            $('#cod_odontologoremision').val(0).trigger('change');
            $('#modalRemisiones').find('button').data({'codhistoria':$data.codhistoria,'codpaciente':$data.codpaciente});
            $('#modalRemisiones').modal('show');
        });

    });

    $(document).on('click', 'div.superbox-list', function(){
        var $this   = $(this),
            $data   = $this.data(),
            select  = $('#selectimagenes'),
            $img    = [];

        $img = $data.codregistroimagenes.split(',');
        select.select2('destroy');

        if(select.find('option[value="'+$img[0]+'"]').length){
            select.find('option[value="'+$img[0]+'"]').remove();
            $this.removeClass('active');
        }else {
            select.append($('<option>').attr({'value':$img[0],'selected':true}).html($img[2]));
            $this.addClass('active');
        }

        select.select2();
    });

    $(document).on('click', '#generarRemision', function(){
        var $this   = $(this),
            $data   = $this.data(),
            $app    = '?app='+$.base64.encode('endodoncia'),
            $met    = '&met='+$.base64.encode('GenerarRemisionPDF');

        if($('#selectimagenes').find(':selected').length) {

            $cod_imagenes   = $.map($('#selectimagenes').attr('disabled',false).serializeArray(), function (i, v) {return i.value;}).join('|');
            $arg            = '&arg='+$.base64.encode($data.codhistoria+',0,'+$cod_imagenes);

            window.location.href = $app+$met+$arg;
        }else
            swal({
                title:"Remision sin Imagenes!",
                text:"Debe seleccionar al menos una imagen para adjuntar a al remision.",
                type:"error",
                confirmButtonText : 'Aceptar',
                confirmButtonColor: "#039BE5"
            });


    });

    $(document).on('click', '#enviarRemisionOdontologo', function(){
        var $this           = $(this),
            $data           = $this.data(),
            $cod_odontologos= {},
            aData           = {};

        if($('#selectimagenes').find(':selected').length) {

            if($('#cod_odontologoremision').find(':selected').length) {

                $cod_odontologos    = $.map($('#cod_odontologoremision').serializeArray(), function(i,v){return i.value;}).join(',');
                $cod_imagenes       = $.map($('#selectimagenes').attr('disabled',false).serializeArray(), function (i, v) {return i.value;}).join('|');

                aData = {
                    cod_historiaclinica:$data.codhistoria,
                    cod_paciente:$data.codpaciente,
                    cod_odontologo: $cod_odontologos,
                    cod_imagenes: $cod_imagenes,
                    type:1
                };


                $.ajax({
                    url: _service + '?funcion=GenerarRemisionPDF',
                    dataType: "json",
                    type: "GET",
                    data: aData,
                    beforeSend: function(){
                        $this.attr('disabled',true);
                        $this.find('i').removeClass('fa-send').addClass('fa-spinner fa-spin');
                    },
                }).done(function (xhr_data) {

                    if(xhr_data.status) {
                        $('#cod_odontologoremision').val(0).trigger('change');

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
                });
            }else
                $('#cod_odontologoremision').focus();
        }else
            swal({
                title:"Remision sin Imagenes!",
                text:"Debe seleccionar al menos una imagen para adjuntar a al remision.",
                type:"error",
                confirmButtonText : 'Aceptar',
                confirmButtonColor: "#039BE5"
            });

    });

    $(document).on('click', '#enviarRemisionPaciente', function(){

        var $this           = $(this),
            $data           = $this.data(),
            $cod_odontologos= {},
            aData           = {};

        if($('#selectimagenes').find(':selected').length) {


            $cod_imagenes       = $.map($('#selectimagenes').attr('disabled',false).serializeArray(), function (i, v) {return i.value;}).join('|');

            aData = {
                cod_historiaclinica:$data.codhistoria,
                cod_paciente:$data.codpaciente,
                cod_imagenes: $cod_imagenes,
                type:2
            };


            $.ajax({
                url: _service + '?funcion=GenerarRemisionPDF',
                dataType: "json",
                type: "GET",
                data: aData,
                beforeSend: function(){
                    $this.attr('disabled',true);
                    $this.find('i').removeClass('fa-send').addClass('fa-spinner fa-spin');
                },
            }).done(function (xhr_data) {

                if(xhr_data.status) {
                    $('#cod_odontologoremision').val(0).trigger('change');

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
            });
        }else
            swal({
                title:"Remision sin Imagenes!",
                text:"Debe seleccionar al menos una imagen para adjuntar a al remision.",
                type:"error",
                confirmButtonText : 'Aceptar',
                confirmButtonColor: "#039BE5"
            });


    });

    $(document).on('click', '#historiaclinica-evoluciones', function(){
        var $this           = $(this),
            $data           = $this.data(),
            aData           = {};

        aData = {
            cod_historia_clinica:$data.codhistoria,
            cod_paciente:$data.codpaciente,
            cod_config_dental: $data.codconfigdental,
            case: 1
        };

        $.ajax({
            url: _service + '?funcion=infoEvoluciones',
            dataType: "json",
            type: "GET",
            data: aData,
        }).done(function (xhr_data) {
            $('#infoConductos').html('').append(xhr_data.diagnostico.panel);
            $('#infoDesobturacion').html('').append(xhr_data.desobturacion.desobturacion);
            $('#historialEvolucion').find('textarea').html('').html(xhr_data.evoluciones.evoluciones);
            $('#evolucionesCodHistoria').val($data.codhistoria);
            $('#cod_paciente').val($data.codpaciente);
            $('#modalEvoluciones').modal('show');
        });
    });

    $(document).on('blur', '#nueva_evolucion', function(){

        var $this = $(this);

        if($this.val()=='')
            $('#nuevaEvolucion').fadeOut();
    });

    $(document).on('click', '#agregarEvolucion', function(e){
        e.preventDefault();
        $('#nuevaEvolucion').fadeIn().find('textarea').focus();
    });

    $('#formEvoluciones').submit( function(e){

        e.preventDefault();
        var _button = $(this).find('button[type="submit"]'),
            _data   = $(this).serializefiles();

        swal({
            title: "",
            text: "Desea guardar los cambios",
            type: "warning",
            showCancelButton: true,
            confirmButtonColor: "#039BE5",
            confirmButtonText: "Si, Guardar Evoluciones!",
            cancelButtonText: "No, Cancelar!",
            closeOnConfirm: false,
            closeOnCancel: true
        }, function (isConfirm) {
            if (isConfirm) {

                $.ajax({
                    url: _service + '?funcion=Evoluciones',
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
                    document.getElementById("formEvoluciones").reset();
                    $('#modalEvoluciones').modal('hide');

                    $.mostrarMensaje(1);

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

    $(document).on('click', '#historiaclinica-estadocuenta', function(){
        var $this           = $(this),
            $data           = $this.data(),
            aData           = {};

        aData = {
            cod_historia_clinica:$data.codhistoria,
            cod_paciente:$data.codpaciente,
            cod_config_dental: $data.codconfigdental,
        };

        $.ajax({
            url: _service + '?funcion=getinfoIngreso',
            dataType: "json",
            type: "GET",
            data: aData,
        }).done(function (xhr_data) {

            if(xhr_data.numeracion.numComprobanteEgreso!='') {

                $('#ingresosCodHistoria').val('').val($data.codhistoria);
                $('#cod_paciente_ingresos').val('').val($data.codpaciente);

                $('#ingresosNumComprobante').val('').val(xhr_data.numeracion.numComprobanteIngreso);
                $('#ingresoHistorial').html('').html(xhr_data.infoTratamiento.datosComprobanteFac.Historial.Historial);
                $('#saldoAdeudado').val('').val(xhr_data.infoTratamiento.datosHistoriaClinicaCliente.deuda.value);

                $('#ingresosdiente').val('').val(xhr_data.infoTratamiento.datosHistoriaClinicaCliente.tratamientos[0].value);
                $('#ingresoscosto').val('').val(xhr_data.infoTratamiento.datosComprobanteFac[0].imp_total_historia_clinica);
                $('#ingresoscancelado').val('').val(xhr_data.infoTratamiento.datosComprobanteFac[0].imp_canc_historia_clinica);
                $('#ingresosadeudado').val('').val(xhr_data.infoTratamiento.datosComprobanteFac[0].imp_adeu_historia_clinica);

                $('#generarIngresoPdfPaciente,#enviarIngresoPaciente').attr('disabled', true);

                $('#modalIngresos').modal('show');

                var $dataInicial = {
                    costo: xhr_data.infoTratamiento.datosComprobanteFac[0].imp_total_historia_clinica,
                    adeudado: xhr_data.infoTratamiento.datosComprobanteFac[0].imp_adeu_historia_clinica,
                    cancelado:xhr_data.infoTratamiento.datosComprobanteFac[0].imp_canc_historia_clinica
                };

                localStorage.setItem('saldoInicial', JSON.stringify($dataInicial));
            }else
                swal({
                    title:"Configuracion",
                    text:"El sistema no tiene configuracion activa para los comprobantes de ingreso y egreso.",
                    type:"error",
                    confirmButtonText : 'Aceptar',
                    confirmButtonColor: "#039BE5"
                });

        });
    });

    $('#formIngresos').submit( function(e){

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
                    url: _service + '?funcion=Ingresos',
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

                    _button.attr('disabled',false);
                    _button.find('i').removeClass('fa-spinner fa-spin').addClass('fa-floppy-o');
                    
                    document.getElementById("formIngresos").reset();
                    $('#modalIngresos').find('button').data({'codpago':xhr_data.status,'codpaciente':$('#cod_paciente_ingresos').val()});
                    $('#generarIngresoPdfPaciente,#enviarIngresoPaciente').attr('disabled', false);

                    $.mostrarMensaje(1);

                }).fail( function(){
                    swal({
                        title: "Cancelado",
                        text: "Ocurrio un error, si persiste comuniquese con el administrador",
                        type: "error",
                        confirmButtonColor: "#039BE5"
                    });

                    $('#generarIngresoPdfPaciente,#enviarIngresoPaciente').attr('disabled', true);

                    _button.attr('disabled',false);
                    _button.find('i').removeClass('fa-spinner fa-spin').addClass('fa-floppy-o');
                });
            }
        });
    });

    $(document).on('blur', '#ingresosrecibido', function(){
        var $this           = $(this),
            $dataInicial    = JSON.parse(localStorage.getItem('saldoInicial')),
            $valRecibido    = parseFloat($this.val(),2),
            $valAdeudado    = parseFloat($dataInicial.adeudado,2),
            $valTratamiento = parseFloat($dataInicial.costo,2),
            $valCancelado   = parseFloat($dataInicial.cancelado,2);

        $valRecibido = isNaN($valRecibido)?0:$valRecibido;

        if($valRecibido>$valAdeudado) {
            swal({
                title: "Error de Ingreso",
                text: "El valor Recibido es mayor al adeudado",
                type: "error",
                confirmButtonColor: "#039BE5"
            });
            $('#enviarIngreso').attr('disabled',true);
        }else {

            $('#ingresoscancelado').val($valCancelado+$valRecibido);
            $('#ingresosadeudado').val($valAdeudado-$valRecibido);
            $('#enviarIngreso').attr('disabled',false);
        }

        if($valRecibido==0) {
            $('#enviarIngreso').attr('disabled',true);
            $('#ingresosadeudado').val($dataInicial.adeudado);
            $('#ingresoscosto').val($dataInicial.costo);
            $('#ingresoscancelado').val($dataInicial.cancelado);
        }

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
        });
    });

    /*$(document).on('click', '#historiaclinica-delete', function(){
        var $this   = $(this),
            $data   = $this.data(),
            aData   = {};

        aData = {cod_historia_clinica:$data.codhistoria};

        swal({
            title: "Esta seguro?",
            text: "Esta accion eliminara el usuario y todas las acciones realizadas dentro de la plataforma",
            type: "warning",
            showCancelButton: true,
            confirmButtonColor: "#039BE5",
            confirmButtonText: "Si, Borrar!",
            cancelButtonText: "No, Cancelar!",
            closeOnConfirm: false,
            closeOnCancel: true
        }, function (isConfirm) {
            if (isConfirm) {

                $.ajax({
                    url: _service + '?funcion=eliminaHistoriaClinica',
                    dataType: "json",
                    type: "GET",
                    data: aData,
                }).done(function (xhr_data) {

                    swal({
                        title: "Borrado!",
                        text: "Transaccion relizada",
                        type:"success",
                        confirmButtonColor: "#039BE5"
                    });

                    var tr = $('#tablaHistoriaClinica').find('[id="historiaclinica-delete"][data-codhistoria="' + $data.codhistoria + '"]').closest('tr');

                    if (tr.length)
                        tr.remove();

                });
            }
        });
    });*/


}( jQuery ))