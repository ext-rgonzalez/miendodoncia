var _modules    = 'modules/';
var _services   = _modules + 'sistema/controller/sistemaJsonController.php';

(function($) {

    "use strict";

    $.fn.serializefiles = function() {
        var obj = $(this);
        /* ADD FILE TO PARAM AJAX */
        var formData = new FormData();
        $.each($(obj).find("input[type='file']"), function(i, tag) {
            $.each($(tag)[0].files, function(i, file) {
                formData.append(tag.name, file);
            });
        });
        var params = $(obj).serializeArray();
        $.each(params, function (i, val) {
            formData.append(val.name, val.value);
        });
        return formData;
    };

    $.fn.escapeJson = function() {
        var str = JSON.stringify($(this)[0]);
         str.replace(/\\'/g, "\\'")
            .replace(/\\"/g, '\\"')
            .replace(/\\&/g, "\\&")
            .replace(/\\r/g, "\\r")
            .replace(/\\t/g, "\\t")
            .replace(/\\b/g, "\\b")
            .replace(/\\f/g, "\\f");

        return str;
    };

    $(function($) {
        $('[data-toggle="tooltip"]').tooltip()
    });

    $(function($) {
        $('[data-toggle="popover"]').popover()
    });

    $(function($){
         setTimeout( function(){ notificacionesSistema();},40000)
    });

    function notificacionesSistema(){
        $.ajax({
            url: _services + '?funcion=traerNotificacionesSistema',
            dataType: "json",
            type: "GET",
        }).done(function (xhr_data) {

            if(xhr_data.config!=null){

                var $template           = '',
                    $modalNotificacion  = $('#modalNotificaciones'),
                    $badgetNumNotify    = $('#panelnotificacion'),
                    $numNotify          = !isNaN(parseInt($('#panelnotificacion').find('span').html()))?parseInt($badgetNumNotify.find('span').html()):0,
                    $dondeAgregar       = $modalNotificacion.find('[data-dismiss="alert"]').length>0?1: 0,
                    $addTemplate        = !$dondeAgregar?$modalNotificacion:$modalNotificacion.find('[data-dismiss="alert"]:first').parent('div');

                $.each( xhr_data.config, function(key,notify){
                    $template =  '<div class="alert alert-info alert-dismissible" role="alert">'
                                +'<button type="button" class="close" data-codNotify="'+notify.cod_notificacion+'" id="borrarNotificacion" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>'
                                +notify.des_notificacion
                                +'<p><strong style="font-size: 9px">'+notify.fecha_notificacion+'</strong></p></p>'
                                +'</div>';

                    if($dondeAgregar)
                        $addTemplate.before($template);
                    else
                        $addTemplate.append($template);

                });

                $badgetNumNotify.find('span').html($numNotify+xhr_data.config.length);

                setTimeout( function(){
                    notificacionesSistema();
                },60000)
            }else{
                setTimeout( function(){
                    notificacionesSistema();
                },60000)
            }

        }).fail(function (xhr) {});
    };

    $(document).on('click', '#panelnotificacion', function(e){
        e.preventDefault();

        var $this               = $(this),
            $modalNotificacion  = $('#modalNotificaciones'),
            numnotificacion     = parseInt($modalNotificacion.find('[data-dismiss="alert"]').length);

        if(numnotificacion) {
            $this.find('span').html('');
            $modalNotificacion.modal('show');
        }else
            $.notify('No Tiene notificaciones pendientes en el sistema.', {
                allow_dismiss: true,
                placement: {
                    from: 'top',
                    align: 'right'
                }
            });
    });

    $('#modalNotificaciones').on('hidden.bs.modal', function () {
        var $this               = $(this),
            $modalNotificacion  = $('#modalNotificaciones'),
            $badgetNumNotifica  = $('#panelnotificacion'),
            numnotificacion     = parseInt($modalNotificacion.find('[data-dismiss="alert"]').length);

        $badgetNumNotifica.find('span').html('');
        $modalNotificacion.find('[data-dismiss="alert"]').parent('div').each( function(k,t){
            $(t).removeClass('alert-info');
        });
    });

    $(document).on('click', '#borrarNotificacion', function(){});

    $(document).on('click', '#borrarNotificaciones', function(){});

    $.mostrarMensaje = function (tipo) {
        switch (tipo) {
            case (1):
                swal({
                    title:"Confirmacion!",
                    text:"La transaccion se realizo correctamente",
                    type:"success",
                    confirmButtonText : 'Aceptar',
                    confirmButtonColor: "#039BE5"
                });
                break;
            case (2):
                swal({
                    title:"Error!",
                    text:"Ocurrio un error, si persiste pongase en contacto con el administrador",
                    type:"error",
                    confirmButtonText : 'Aceptar',
                    confirmButtonColor: "#039BE5"
                });
                break;
        }
    }
})(jQuery);
