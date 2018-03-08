( function( $ ){
    var source  = _service + '?funcion=traerOdontologos',
        that    = this;

    table = $('#tablaOdontologos').DataTable({
        "ajax": {
            url: source,
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

            $(nRow).data('cod_usuario',aData.cod_usuario);

        },
        "aoColumns":    [
            {"mData": "nom_usuario",
                "mRender": function( data, type, row ){
                    return row.nom_usuario +' '+row.ape_usuario;
                }
            },
            {"mData": "dir_usuario"},
            {"mData": "tel_usuario",
                "mRender": function( data, type, row ){
                    return row.tel_usuario +' '+ row.cel_usuario;
                }
            },
            {"mData": "email_usuario" },
            {"mData": "usuario_usuario" },
            {"mData": "nom_perfil" },
            {"mData": "action",
                "mRender": function ( data, type, row ) {

                    var estado = row.cod_estado=='AAA'?'BBB':'AAA';

                    var template =
                        '<div class="btn-group">'+
                        '   <button type="button" class="btn btn-default btn-sm dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"> Acciones'+
                        '   </button>'+
                        '   <ul class="dropdown-menu dropdown-menu-right">'+
                        '       <li><a id="odontologo-edit"         data-infousuario='+encodeURIComponent(JSON.stringify(row))+' data-codusuario="'+row.cod_usuario+'"><i class="fa fa-refresh"></i> Editar</a></li>'+
                        '       <li><a id="odontologo-changestatus" data-codusuario="'+row.cod_usuario+'" data-estado="'+estado+'"><i class="fa fa-power-off"></i> Cambiar Estado </a></li>'+
                        '       <li><a id="odontologo-delete"   data-codusuario="'+row.cod_usuario+'"><i class="fa fa-trash"></i> Eliminar</a></li>'+
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

    $('#nuevoOdontologo').submit( function( e ) {
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

                var tr = $('#tablaOdontologos').find('[id="odontologo-edit"][data-codusuario="'+xhr_data.data[0].cod_usuario+'"]').closest('tr');

                if(tr.length)
                    table.row(tr).data(xhr_data.data[0]).draw();
                else
                    table.rows.add(xhr_data.data).draw();

                $.mostrarMensaje(1);

                $('#modalnuevoOdontologo').modal('hide');
                document.getElementById("nuevoOdontologo").reset();
            }else


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

    /*$(document).on('click', '#odontologo-delete', function(){
        var $this   = $(this),
            $data   = $this.data(),
            aData   = {};

        aData = {cod_usuario:$data.codusuario};

        swal({
            title: "Esta seguro?",
            text: "Esta accion eliminara el usuario y todas las acciones realizadas dentro de la plataforma",
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
                    url: _service + '?funcion=eliminaOdontologo',
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

                    var tr = $('#tablaOdontologos').find('[id="odontologo-delete"][data-codusuario="' + $data.codusuario + '"]').closest('tr');

                    if (tr.length)
                        tr.remove();

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

    });*/

}( jQuery ))