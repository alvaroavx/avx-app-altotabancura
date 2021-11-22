/**
 * JS Universidad
 * **/

/**
 * Muetra las Universidades en formato select
 * @constructor
 */
$.LoadSelectUniversidad = function() {
    $.ajax({
        data: $.RawData("LoadSelectUniversidad"),
        success: function (response) {
            $("#universidades").html(response);
        },
        complete: function(){}
    });
};

/** ADMINISTRACION UNIVERSIDADES **/
/**
 * Recarga el listado de Universidades en el Admin
 * @constructor
 */
$.LoadUniversidadAdmin = function(){
    $.ajax({
        data: $.RawData("LoadUniversidadAdmin"),
        success: function (response) {
            $("#univ_admin").html(response);
        },
        complete: function(){
            $('#universidadtable').DataTable({
                paging: true,
                "oLanguage": {
                    "sProcessing": "Procesando...",
                    "sLengthMenu": "Mostrar _MENU_ registros",
                    "sSearch": "Filtrar Universidad:",
                    "sInfo": "Mostrando _TOTAL_ universidades",
                    "sInfoEmpty": "Mostrando 0 universidades",
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
 * Muestra y oculta el formulario de nueva Universidad
 * @constructor
 */
$.LoadUniversidadForm = function() {
    var formulario = $("#universidadform");
    if(formulario.hasClass("open")) {
        formulario.empty();
        formulario.removeClass("open");
    }
    else {
        $.ajax({
            data: $.RawData("LoadUniversidadForm"),
            success: function (response) {
                formulario.html(response);
                formulario.addClass("open");
            },
            complete: function () {
                formulario.find('input').focus();
            }
        });
    }
};
/**
 * Guarda una Universidad en la BD y recarga listado
 * @param formId
 * @constructor
 */
$.SaveUniversidad = function(formId) {
    var form = $('#'+formId);
    var universidad = [];
    if($("#universidadform").hasClass("open"))
        $.LoadUniversidadForm();
    $.each(form.serializeArray(), function(i, field) {
        var input = $('input[name='+field.name+']');
        field.value = $.trim(field.value);
        switch(field.name){
            case "iduniversidad": universidad["iduniversidad"] = field.value; break;
            case "nombre": universidad["nombre"] = field.value; break;
            default:
                console.log("default: "+field.value);
                break;
        }
    });
    $.ajax({
        data: $.RawData('CreateModifyUniversidad', {
            'IdUniversidad': universidad["iduniversidad"],
            'Nombre': universidad["nombre"]
        }),
        success: function () {
            $.LoadUniversidadAdmin();
            $.LoadSedeAdmin();
        },
        complete: function(){}
    });
};
/**
 * Elimina una universidad
 * @constructor
 */
$.DeleteUniversidad = function(idUniversidad) {
    $.ajax({
        data: $.RawData('RemoveUniversidad', {
            'IdUniversidad': idUniversidad
        }),
        success: function () {
            $.LoadUniversidadAdmin();
            $.LoadSedeAdmin();
        },
        complete: function(){}
    });
};