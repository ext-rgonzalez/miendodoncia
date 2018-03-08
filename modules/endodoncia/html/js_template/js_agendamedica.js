$( function( $ ) {
    var source  = _service + '?funcion=traerAgendaMedica',
        that    = this,
        agenda  = '';

    $( '#fec_ini').datetimepicker({
        format: "YYYY-MM-DD",
    });

    $( '#fec_hasta').datetimepicker({
        format: "YYYY-MM-DD"
    });

    agenda = $('#agendamedica').fullCalendar({
        monthNames: ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'],
        monthNamesShort: ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'],
        dayNames: ['Domingo', 'Lunes', 'Martes', 'Miercoles','Jueves', 'Viernes', 'Sábado'],
        dayNamesShort: ['Dom', 'Lun', 'Mar', 'Mie', 'Jue', 'Vie', 'Sab'],
        selectable: true,
        editable: true,
        minTime: '06:00:00',
        maxtime: '20:00:00',
        defaultView: 'agendaWeek',
        slotDuration: '00:20:00',
        firstHour:8,
        header: {
            left: 'prev,next today',
            center: 'title',
            right: 'month,agendaWeek,agendaDay,listWeek'
        },
        navLinks: true,
        eventLimit: true,
        select: function (start, end, allDay) {

            var _endtime    = $.fullCalendar.formatDate(end,'YYYY-MM-DD HH:mm:ss');
            var _starttime  = $.fullCalendar.formatDate(start,'YYYY-MM-DD HH:mm:ss');
            var _cuando     = _starttime + ' - ' + _endtime;

            document.getElementById("formAgendaMedica").reset();
            $('#cod_agenda').val('');

            $('#cuandoinfo').html('').html(_cuando);

            $('#start_evento_agenda').val(_starttime);
            $('#end_evento_agenda').val(_endtime);
            $('#eliminarAgenda').attr('disabled', true);
            $('#modalAgendaMedica').modal('show');

        },
        eventAfterRender: function (event, element, view) {

        },
        eventRender: function (event, element, icon) {
            var _event  = event;
            element.attr('href', 'javascript:void(0);');

            element.click(function() {
                var _endtime    = $.fullCalendar.formatDate(event.end,'YYYY-MM-DD HH:mm:ss');
                var _starttime  = $.fullCalendar.formatDate(event.start,'YYYY-MM-DD HH:mm:ss');
                var _cuando     = _starttime + ' - ' + _endtime;
                var result      = event.title.split(':');
                localStorage.setItem('eventId', event._id);
                document.getElementById("formAgendaMedica").reset();
                $('#cuandoinfo').html('').html(_cuando);
                $('#cod_agenda').val(event.cod_agenda);
                $('#nom_evento_agenda').val(result[0]);
                $('#des_evento_agenda').val(result[1]);
                $('#start_evento_agenda').val(_starttime);
                $('#end_evento_agenda').val(_endtime);
                $('#ind_confirmado').prop('checked', parseInt(event.ind_confirmado)?true:false);
                $('#eliminarAgenda').attr('disabled', false).data('cod_agenda', event.cod_agenda);
                $('#modalAgendaMedica').modal('show');

            });
        },
        eventResize: function(event, element, icon) {
            var nuevoEvento = {
                'agendamedica': {
                    'start_evento_agenda':  $.fullCalendar.formatDate(event.start, 'YYYY-MM-DD HH:mm:ss'),
                    'end_evento_agenda':    $.fullCalendar.formatDate(event.end, 'YYYY-MM-DD HH:mm:ss'),
                    'ind_confirmado':       event.ind_confirmado,
                    'cod_agenda':           event.cod_agenda
                }
            };

            $.ajax({
                url: _service + '?funcion=' + _metodo,
                dataType: "json",
                type: "GET",
                data: nuevoEvento,
            }).done(function (xhr_data) {
                $.mostrarMensaje(1)
            });

        },
        eventDrop: function (event, element, icon) {
            var nuevoEvento = {
                'agendamedica': {
                    'start_evento_agenda':  $.fullCalendar.formatDate(event.start, 'YYYY-MM-DD HH:mm:ss'),
                    'end_evento_agenda':    $.fullCalendar.formatDate(event.end, 'YYYY-MM-DD HH:mm:ss'),
                    'ind_confirmado':       event.ind_confirmado,
                    'cod_agenda':           event.cod_agenda
                }
            };

            $.ajax({
                url: _service + '?funcion=' + _metodo,
                dataType: "json",
                type: "GET",
                data: nuevoEvento,
            }).done(function (xhr_data) {
                $.mostrarMensaje(1)
            });
        }
    });

    $('#formAgendaMedica').submit( function( e ){
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
                if(localStorage.getItem('eventId'))
                    $('#agendamedica').fullCalendar('removeEvents', localStorage.getItem('eventId'));
            },
        }).done(function (xhr_data) {
            $('#agendamedica').fullCalendar('renderEvent',xhr_data[0],true);
            $('#modalAgendaMedica').modal('hide');
            $.mostrarMensaje(1);
            _button.attr('disabled',false);
            _button.find('i').removeClass('fa-spinner fa-spin').addClass('fa-floppy-o');
        }).fail(function (xhr_data){
            $.mostrarMensaje(2);
            _button.attr('disabled',false);
            _button.find('i').removeClass('fa-spinner fa-spin').addClass('fa-floppy-o');
        });

    });

    $(document).on('click', '#eliminarAgenda', function(){
        var $this = $(this),
            $data = $this.data();

        swal({
            title: "Esta seguro?",
            text: "Desea eliminar esta asignacion a la agenda medica",
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
                    url: _service + '?funcion=eliminarAgenda',
                    dataType: "json",
                    type: "GET",
                    data: {cod_agenda: $data.cod_agenda},
                }).done(function (xhr_data) {
                    $('#agendamedica').fullCalendar('removeEvents', localStorage.getItem('eventId'));
                    $('#modalAgendaMedica').modal('hide');
                    $.mostrarMensaje(1)
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

    $(document).on('click', '#cargaTodos', function(){

        var $this = $(this);

        $.ajax({
            url: _service + '?funcion=traerAgendaMedica',
            dataType: "json",
            type: "GET",
            beforeSend: function() {
                $this.attr('disabled',true);
                $this.find('i').removeClass('fa-list').addClass('fa-spinner fa-spin');
                $('#agendamedica').fullCalendar('removeEvents');
            },
        }).done(function (xhr_data) {
            $('#agendamedica').fullCalendar('renderEvents',xhr_data,true);
            $this.attr('disabled',false);
            $this.find('i').removeClass('fa-spinner fa-spin').addClass('fa-list');
        });
    });

    $(document).on('click', '#cargaConfirmados', function(){

        var $this = $(this);

        $.ajax({
            url: _service + '?funcion=traerAgendaMedica',
            dataType: "json",
            type: "GET",
            data: {indConfirmado: 1},
            beforeSend: function() {
                $this.attr('disabled',true);
                $this.find('i').removeClass('fa-check').addClass('fa-spinner fa-spin');
                $('#agendamedica').fullCalendar('removeEvents');
            },
        }).done(function (xhr_data) {
            $('#agendamedica').fullCalendar('renderEvents',xhr_data,true);
            $this.attr('disabled',false);
            $this.find('i').removeClass('fa-spinner fa-spin').addClass('fa-check');
        });
    });

    $(document).on('click', '#consultaAgenda', function(){

        var $this = $(this);

        document.getElementById("formConsultaAgendaMedica").reset();
        $('#modalConsultaAgendaMedica').modal('show');

    });

    $('#formConsultaAgendaMedica').submit( function( e ){
        e.preventDefault();

        var _button = $(this).find('button[type="submit"]'),
            _data   = $(this).serializefiles();

        $.ajax({
            url: _service + '?funcion=traerAgendaMedica',
            dataType: "json",
            type: "POST",
            data: _data,
            processData: false,
            contentType: false,
            beforeSend: function(){
                _button.attr('disabled',true);
                _button.find('i').removeClass('fa-calendar').addClass('fa-spinner fa-spin');
                $('#agendamedica').fullCalendar('removeEvents');
            },
        }).done(function (xhr_data) {
            if(xhr_data.length>0) {
                $('#agendamedica').fullCalendar('renderEvents',xhr_data,true);
                $('#modalConsultaAgendaMedica').modal('hide');

            }else
                swal({
                    title: "Mensaje!",
                    text: "No se encontraron registros en el rango de fechas consultado",
                    type:"info",
                    confirmButtonColor: "#039BE5"
                });

            _button.attr('disabled',false);
            _button.find('i').removeClass('fa-spinner fa-spin').addClass('fa-calendar');

        });
    });

    $('#modalAgendaMedica').on('hidden.bs.modal', function () {
        localStorage.removeItem('eventId');
    })

    $( function() {
        $.ajax({
            url: _service + '?funcion=traerAgendaMedica',
            dataType: "json",
            type: "POST",
            processData: false,
            contentType: false,
            beforeSend: function() {
                $('#agendamedica').fullCalendar('removeEvents');
            },
        }).done(function (xhr_data) {
            $('#agendamedica').fullCalendar('renderEvents',xhr_data,true);
        });
    });
});