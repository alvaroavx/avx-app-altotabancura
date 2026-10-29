/**
 * JS Admin
 * **/

/** OPCIONES BARRA LATERAL **/
$.IsAdminUp = function() {
    var admin = $('#adminrow');
    var ret;
    if(admin.css('display')==="flex")
        ret = true;
    else
        ret = false;
    return ret;
};
$.SidebarFocus = function(seccion) {
    if(!$.IsAdminUp()) {
        $.LoadAdmin("focus", seccion);
    }
    else {
        $('.admincol').removeClass("active");
        if(seccion==="universidad")
            $('#universidadesrow').addClass("active");
        else if (seccion==="sede")
            $('#sedesrow').addClass("active");
        else if(seccion==="carrera")
            $('#carrerasrow').addClass("active");
        else if(seccion==="vacuna")
            $('#vacunasrow').addClass("active");
        else if(seccion==="docente")
            $('#docentesrow').addClass("active");
    }
};
$.SidebarNew = function (seccion) {
    if(!$.IsAdminUp()) {
        $.LoadAdmin("new", seccion);
    }
    else {
        if(seccion==="universidad")
            $.LoadUniversidadForm();
        else if(seccion==="sede")
            $.LoadSedeForm();
        else if(seccion==="carrera")
            $.LoadCarreraForm();
        else if(seccion==="vacuna")
            $.LoadVacunaForm();
    }
};
$.AddAlumno = function () {
    if(!$.IsAdminUp()) {
        $.LoadAdmin("focus", "alumno");
    }
    else {
        $.LoadAlumnoForm();
    }
};
$.EditAlumno = function () {
    $.LoadResultados();
    $('#adminbutton').click();
};
$.AddDocente = function () {
    if(!$.IsAdminUp()) {
        $.LoadAdmin("focus", "docente");
    }
    else {
        $.LoadDocenteForm();
    }
};
$.EditDocente = function () {
    /*$.LoadResultados();
    $('#adminbutton').click();*/
    console.log('MOSTRAR DOCENTES');
};
$.ShowSidebar = function () {
    var sidebar = $("#sidebar");
    if(sidebar.css("display")==="block") {
        sidebar.css("display","none")
    }
    else {
        sidebar.css("display","block")
    }
};

/**
 * Carga la vista COMPLETA para los administradores
 * @constructor
 */
$.VistaAdmin = function(){
    $.FootPrint('administracion');
    $.ajax({
        data: $.RawData("VistaAdmin"),
        success: function (response) {
            $("#midblock").html(response);
        },
        complete: function(){
        }
    });
};

/**
 * Carga contenido del Admin
 * @constructor
 */
$.LoadAdmin = function(action=null, seccion=null){
    $.FootPrint('administracion');
    $.ajax({
        data: $.RawData("LoadAdmin"),
        success: function (response) {
            $("#resultsrow").html(response);
        },
        complete: function(){
            if(action==="new") {
                if (seccion === "universidad")
                    $.LoadUniversidadForm();
                else if (seccion === "sede")
                    $.LoadSedeForm();
                else if (seccion === "carrera")
                    $.LoadCarreraForm();
                else if (seccion === "vacuna")
                    $.LoadVacunaForm();
            }
            else if (action==="focus") {
                if(seccion==="universidad")
                    $('#universidadesrow').addClass("active");
                else if (seccion==="sede")
                    $('#sedesrow').addClass("active");
                else if(seccion==="carrera")
                    $('#carrerasrow').addClass("active");
                else if(seccion==="vacuna")
                    $('#vacunasrow').addClass("active");
                else if(seccion==="alumno")
                    $.LoadAlumnoForm();
                else if(seccion==="docente")
                    $.LoadDocenteForm();
            }
        }
    });
};

/**
 * Muestra u oculta el formulario de edición del elemento enviado
 * @param elem
 * @constructor
 */
$.EditThis = function(elem) {
    var tr = $(elem).closest("tr");
    if(tr.find(".view").hasClass("hidden")) {
        tr.find(".view").removeClass("hidden");
        tr.find(".rework").addClass("hidden");
    }
    else {
        tr.find(".view").addClass("hidden");
        tr.find(".rework").removeClass("hidden");
        tr.find('input').focus();
    }
};

