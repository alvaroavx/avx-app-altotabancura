/**
 * JS Vacuna
 * **/

/**
 * Muestra las Vacunas en formato select
 * @constructor
 */
$.LoadSelectVacuna = function() {
    $.ajax({
        data: $.RawData("LoadSelectSede"),
        success: function (response) {
            $("#sedes").html(response);
        },
        complete: function(){}
    });
};

/** ADMINISTRACION VACUNAS **/
/**
 * Recarga el listado de Vacunas en el Admin
 * @constructor
 */
$.LoadVacunaAdmin = function(){
    $.ajax({
        data: $.RawData("LoadVacunaAdmin"),
        success: function (response) {
            $("#vacu_admin").html(response);
        },
        complete: function(){
            $('#vacunastable').DataTable({
                paging: true,
                "oLanguage": {
                    "sProcessing": "Procesando...",
                    "sLengthMenu": "Mostrar _MENU_ registros",
                    "sSearch": "Filtrar Vacuna:",
                    "sInfo": "Mostrando _TOTAL_ vacunas",
                    "sInfoEmpty": "Mostrando 0 vacunas",
                    "sZeroRecords": "No se encontraron resultados",
                    "sInfoFiltered": "(filtrado de un total de _MAX_)",
                    "oPaginate": {
                        "sFirst": "Primero",
                        "sLast": "Ultimo",
                        "sNext": "Siguiente",
                        "sPrevious": "Anterior"
                    },
                }
            });
        }
    });
};
/**
 * Muestra y oculta el formulario de nueva Vacuna
 * @constructor
 */
$.LoadVacunaForm = function() {
    var formulario = $("#vacunaform");
    if(formulario.hasClass("open")) {
        formulario.empty();
        formulario.removeClass("open");
    }
    else {
        $.ajax({
            data: $.RawData("LoadVacunaForm"),
            success: function (response) {
                formulario.html(response);
                formulario.addClass("open");
            },
            complete: function () {
                formulario.find('input').first().focus();
            }
        });
    }
};

$.SaveVacuna = function(elem) {
    var form = $(elem).closest('.formulario');
    var vacuna = [];
    var empty = false;

    vacuna["idvacuna"] = form.find('input[name=idvacuna]').val();
    if(form.find('input[name=vacuna]').val() === "") {
        form.find('input[name=vacuna]').css("border-color",  "red");
        empty = true;
    }
    else {
        vacuna["vacuna"] = form.find('input[name=vacuna]').val();
    }
    if(form.find('input[name=folio]').val() === "") {
        form.find('input[name=folio]').css("border-color",  "red");
        empty = true;
    }
    else {
        vacuna["folio"] = form.find('input[name=folio]').val();
    }
    console.log(vacuna);
    if(empty) {

    }
    else {
        if($("#vacunaform").hasClass("open"))
            $.LoadVacunaForm();
        $.ajax({
            data: $.RawData('CreateModifyVacuna', {
                'IdVacuna': vacuna["idvacuna"],
                'Vacuna': vacuna["vacuna"],
                'Lote': vacuna["folio"]
            }),
            success: function () {
                $.LoadVacunaAdmin();
            },
            complete: function(){}
        });
    }
};

$.DeleteVacuna = function(idVacuna) {
    $.ajax({
        data: $.RawData('RemoveVacuna', {
            'IdVacuna': idVacuna
        }),
        success: function () {
            $.LoadVacunaAdmin();
        },
        complete: function(){}
    });
};

/** FORMULARIO DE USUARIO Y VACUNAS **/
$.AddVaccine = function(idUsuario) {
    $.ajax({
        data: $.RawData('UserVaccine', {
            'IdUsuarioVacuna': 0,
            'IdVacuna': 0,
            'IdUsuario': idUsuario,
            'Numero': 0,
            'Fecha': '',
        }),
        success: function (response) {
            $.ajax({
                data: $.RawData('AddVaccine', {
                    'IdUsuario': idUsuario,
                }),
                success: function (response) {
                    $('#vacunasformulario').append(response);
                    $.UpdateMessageForm("newvac");
                },
            });
        },
        complete: function(){}
    });
};
$.ShowVaccines = function(idUsuario) {
    $.LoadUsuario(idUsuario);
    var checkExist = setInterval(function() {
        if ($('#editvaccines').length) {
            $('#editvaccines').click();
            clearInterval(checkExist);
        }
    }, 100); // check every 100ms

};
$.RemoveVaccine = function(idUsuarioVacuna) {
    $.ajax({
        data: $.RawData('DetachVaccine', {
            'IdUsuarioVacuna': idUsuarioVacuna,
        }),
        success: function (response) {
            $("div.vacunarow[data-idusuariovacuna='"+idUsuarioVacuna+"']").remove();
            $.UpdateMessageForm("delvac");
        },
    });
};
$.EditVaccines = function(elem) {
    var form = $('#vacunasformulario');
    if(form.hasClass('open')) {
        form.removeClass('open');
        $(elem).html('Editar Vacunas <div class="icon small" onclick="" title="Vacunas"><div class="syringe3 right"></div></div>');
    }
    else {
        form.addClass('open');
        $(elem).html('Ocultar Vacunas <div class="icon small" onclick="" title="Ocultar Vacunas"><div class="cancel right"></div></div>');
    }
};
$.SaveVaccines = function () {
    var vacunas = $('#vacunasformulario').find('div.vacunarow[data-edited=true]');
    $.each(vacunas, function () {
        var vacuna = [];
        vacuna["idusuariovacuna"] = $(this).attr('data-idusuariovacuna');
        vacuna["idvacuna"] = $(this).find('select[name=vacuna]').val();
        vacuna["idusuario"] = $('#usuarioform').find('div.formulario').attr('data-idusuario');
        vacuna["numero"] = $(this).find('input[name=numero]').val();
        vacuna["fecha"] = $(this).find('input[name=fecha]').val();
        var ready = true;
        if (vacuna["idvacuna"]==="") {
            $(this).find('select[name=vacuna]').css("border-color","red");
            ready = false;
        }
        if (vacuna["numero"]==="") {
            $(this).find('input[name=numero]').css("border-color","red");
            ready = false;
        }
        if (vacuna["fecha"]==="") {
            $(this).find('input[name=fecha]').css("border-color","red");
            ready = false;
        }
        if (ready) {
            $.ajax({
                data: $.RawData('UserVaccine', {
                    'IdUsuarioVacuna': vacuna["idusuariovacuna"],
                    'IdVacuna': vacuna["idvacuna"],
                    'IdUsuario': vacuna["idusuario"],
                    'Numero': vacuna["numero"],
                    'Fecha': vacuna["fecha"]
                }),
                success: function () {},
            });
        }
    });
};
/**
 * Actualiza el estado de la vacuna para ser guardada despues
 * @param elem
 * @constructor
 */
$.UpdateVaccine = function (elem) {
    $(elem).closest('div.vacunarow').attr('data-edited', 'true');
};