$( function( $ ) {

    var source          = _service + '?funcion=traerAgendaMedica',
        that            = this,
        agenda          = '',
        tablaCartera    = '',
        tablaControl    = '';

    agenda = $('#agendamedica').fullCalendar({
        monthNames: ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'],
        monthNamesShort: ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'],
        dayNames: ['Domingo', 'Lunes', 'Martes', 'Miercoles','Jueves', 'Viernes', 'Sábado'],
        dayNamesShort: ['Dom', 'Lun', 'Mar', 'Mie', 'Jue', 'Vie', 'Sab'],
        minTime: '06:00:00',
        maxtime: '20:00:00',
        defaultView: 'listDay',
        slotDuration: '00:20:00',
        firstHour:12,
        header: {
            left: '',//'prev,next today',
            center: 'title',
            right: '',//'month,agendaWeek,agendaDay,listWeek'
        },
        navLinks: true,
        eventLimit: true
    });

    tablaCartera = $( '#tablaCartera' ).DataTable({
        "ajax": {
            url: 'modules/endodoncia/controller/endodonciaJsonController.php?funcion=traerCartera',
            type: 'POST'
        },
        "pageLength": 10,
        "serverSide": true,
        "bProcessing":  true,
        "bDeferRender": false,
        "bFilter": false,
        "lengthChange": false,
        "fnRowCallback": function( nRow, aData, iDisplayIndex ) {
            if ( parseInt(aData['vencido']) )
                $(nRow).addClass( 'danger' );
        },
        "aoColumns":    [
            {"mData": "nom_paciente"},
            {"mData": "nom_config_dental"},
            {"mData": "imp_adeu_historia_clinica",
                "mRender": function( data, type, row ){
                    return  '<span class="label label-primary"><strong>Total: </strong>'+row.imp_total_historia_clinica+'</span>'+
                            '<span class="label label-warning"><strong>Adeudado: </strong>'+row.imp_adeu_historia_clinica+'</span>'+
                            '<span class="label label-success"><strong>Cancelado: </strong>'+row.imp_canc_historia_clinica+'</span>';
                }
            },
            {"mData": "fec_ult_pago"},
            {"mData": "fec_prox_pago"}
        ]
    });

    tablaControl= $( '#tablaControler' ).DataTable({
        "ajax": {
            url: 'modules/endodoncia/controller/endodonciaJsonController.php?funcion=traerControles',
            type: 'POST'
        },
        "pageLength": 10,
        "serverSide": true,
        "bProcessing":  true,
        "bDeferRender": false,
        "bFilter": false,
        "lengthChange": false,
        "fnRowCallback": function( nRow, aData, iDisplayIndex ) {
            if ( parseInt(aData['vencido']) )
                $(nRow).addClass( 'danger' );
        },
        "aoColumns":    [
            {"mData": "nom_paciente"},
            {"mData": "nom_config_dental"},
            {"mData": "fec_control"},
            {"mData": "fec_historia_clinica"}
        ]
    });

    $(function(){
        $.ajax({
            url: _service + '?funcion=infoGraficosDashboard',
            dataType: "json",
            type: "GET",
        }).done(function (xhr_data) {
            ArmaGraficodashboard(xhr_data);
        }).fail(function (xhr) {});
    });

    function ArmaGraficodashboard(dataChart){

        Morris.Bar({
            element: 'grafica-tratamientos',
            data: dataChart.barChart,
            xkey: 'y',
            ykeys: ['c'],
            labels: ['cantidad'],
            barColors: ["#F26C4F"]
        });

        Morris.Donut({
            element: 'tratamientos-mes',
            data: dataChart.donutChart,
            colors: ["#9CC4E4", "#18a689", "#F26C4F", "#4584d1"]
        });

        Morris.Donut({
            element: 'citas-mes',
            data: dataChart.donutChart_1,
            colors: ['#27a9e3',"#9CC4E4", "#F26C4F", "#4584d1"]
        });
    }

    $( function() {
        $.ajax({
            url: 'modules/endodoncia/controller/endodonciaJsonController.php?funcion=traerAgendaMedica',
            dataType: "json",
            type: "GET",
            data: {dsshboard: 1},
            beforeSend: function() {
                $('#agendamedica').fullCalendar('removeEvents');
            },
        }).done(function (xhr_data) {
            $('#agendamedica').fullCalendar('renderEvents',xhr_data,true);
        });
    });

});