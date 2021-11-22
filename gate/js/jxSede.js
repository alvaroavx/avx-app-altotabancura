/**
 * JS Sede
 * **/

/**
 * Muestra las Sedes en formato select
 * @constructor
 */
$.LoadSelectSede = function() {
    $.ajax({
        data: $.RawData("LoadSelectSede"),
        success: function (response) {
            $("#sedes").html(response);
        },
        complete: function(){}
    });
};

/** ADMINISTRACION SEDES **/
/**
 * Recarga el listado de Sedes en el Admin
 * @constructor
 */
$.LoadSedeAdmin = function(){
    $.ajax({
        data: $.RawData("LoadSedeAdmin"),
        success: function (response) {
            $("#sede_admin").html(response);
        },
        complete: function(){
            $('#sedetable').DataTable({
                paging: true,
                "oLanguage": {
                    "sProcessing": "Procesando...",
                    "sLengthMenu": "Mostrar _MENU_ registros",
                    "sSearch": "Filtrar Sede:",
                    "sInfo": "Mostrando _TOTAL_ sedes",
                    "sInfoEmpty": "Mostrando 0 sedes",
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
 * Muestra u oculta el formulario de edición de una Sede
 * @constructor
 */
$.LoadSedeForm = function() {
    var formulario = $("#sedeform");
    if(formulario.hasClass("open")) {
        formulario.empty();
        formulario.removeClass("open");
    }
    else {
        $.ajax({
            data: $.RawData("LoadSedeForm"),
            success: function (response) {
                formulario.html(response);
                formulario.addClass("open");
            },
            complete: function () {
                formulario.find('select').focus();
            }
        });
    }
};

$.SaveSede = function(elem) {
    var form = $(elem).closest('.formulario');
    var sede = [];
    var empty = false;

    sede["idsede"] = form.find('input[name=idsede]').val();
    if(form.find('option:selected').val() === "") {
        form.find('#universidades').css("border-color",  "red");
        empty = true;
    }
    else {
        sede["iduniversidad"] = form.find('option:selected').val();
    }
    if(form.find('input[name=nombre]').val() === "") {
        form.find('input[name=nombre]').css("border-color",  "red");
        empty = true;
    }
    else {
        sede["nombre"] = form.find('input[name=nombre]').val();
    }
    console.log(sede);
    if(empty) {

    }
    else {
        if($("#sedeform").hasClass("open"))
            $.LoadSedeForm();
        $.ajax({
            data: $.RawData('CreateModifySede', {
                'IdSede': sede["idsede"],
                'IdUniversidad': sede["iduniversidad"],
                'Nombre': sede["nombre"]
            }),
            success: function () {
                $.LoadSedeAdmin();
            },
            complete: function(){}
        });
    }
};

$.DeleteSede = function(idSede) {
    $.ajax({
        data: $.RawData('RemoveSede', {
            'IdSede': idSede
        }),
        success: function () {
            $.LoadSedeAdmin();
        },
        complete: function(){}
    });
};