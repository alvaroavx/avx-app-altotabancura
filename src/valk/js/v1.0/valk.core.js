if (window.frameElement){
    top.location.href = location.href;
}
$.ajaxSetup({
    url:   "g",
    type: "POST"
});
window.onpopstate = function(e) {
    /*console.log(e.state);*/
    var constructor = $("#constructor");
    if($.IsSet(e.state.tipo)){
        constructor.attr("data-load",e.state.tipo);
        constructor.attr("data-idload",e.state.valor);
    }
    else{
        constructor.attr("data-load","");
        constructor.attr("data-idload","");
    }
    $.LoadMidBlock(2);
};
/**
 * @return {string}
 */
$.RawData = function(valk,raw={},edward=1){
    var constructor = $("#constructor");
    raw = $.IsNull(raw, {});
    raw['valk'] = valk;
    raw['edward'] = edward;
    raw['load'] = constructor.attr("data-load");
    raw['idload'] = constructor.attr("data-idload");
    raw['media'] = $.Media();
    return "rawdata=" + $.base64.encode(encodeURIComponent($.RawCode(raw)));
};
/**
 * @return {string}
 */
$.RawestData = function(valk,raw={},edward=1){
    raw['valk'] = valk;
    raw['edward'] = edward;
    raw['load'] = constructor.attr("data-load");
    raw['idload'] = constructor.attr("data-idload");
    raw['media'] = $.Media();
    return "rawestdata=" + encodeURIComponent($.RawCode(raw));
};
/**
 * @return {string}
 */
$.RawCode = function(a){
    var text = "";
    $.each(a,function(key, value){
        text += "<njong>" + key + "<alvax>" + value + "";
    });
    return text;
};
/**
 * @return {string}
 */
$.Decode = function(text){
    return decodeURIComponent($.base64.decode(text));
};
/**
 * @arg obj: elemento a hacer scroll
 * @arg top: posicion a la que debe ir
 */
$.GoUp = function(obj, top) {
    if (typeof obj == 'undefined') { obj = $("#desktoprow"); }
    if (typeof top == 'undefined') { top = 0; }
    obj.animate({scrollTop: top}, 300);
};
$.BodyFixed = function(bool){
    if(bool===1){
        $("body").addClass( "bodyfixed" );
    }
    else{
        $("body").removeClass( "bodyfixed" );
    }
};
/**
 * @return {boolean}
 */
$.IsSet= function(object){
    return (typeof object !=="undefined" && typeof object !== typeof undefined && object.length > 0);
};
$.IsNull= function(object,respuesta){
    return (typeof object !=="undefined" && object != null)? object: respuesta;
};
$.Media = function(){
    return $("#fondo").css("width").replace("px","");
};
$.HideBeta = function(){
    $(".bannerbeta").css("display","none");
    $(".textobannerbeta").css("display","none");
};
$.FormatoRut = function(elem){
    var rut;
    rut = $.IsNull(elem.val(),'').replace('-', '').replace(/\./g, '').replace(/\s+/g, '');
    if (rut.length===8){
        rut = rut.substring(0,1)+"."+rut.substring(1,4)+"."+rut.substring(4,7)+"-"+rut.substring(7,8);
    }
    else if (rut.length===9){
        rut = rut.substring(0,2)+"."+rut.substring(2,5)+"."+rut.substring(5,8)+"-"+rut.substring(8,9);
    }
    else if (rut.length >=6){
        rut = rut.substring(0,2)+"."+rut.substring(2,5)+"."+rut.substring(5,8);
    }
    else if (rut.length >=3){
        rut = rut.substring(0,2)+"."+rut.substring(2,5);
    }
    else if (rut.length >=0){
        rut = rut.substring(0,2);
    }
    elem.val(rut);
};
$.CleanModal = function(){
    $("#optionalmodal").css("display","none");
    $("#shadowmodal").css("display","none");
    $("#loadmodal").css("display","none");
    $("#blockmodal").css("display","none");
    $("#blockclose").css("display","none");
    $("#blockcontenedor").empty();
    $("#1optionalcontenedor").empty();
    $.BodyFixed(0);
};
$.OptionalModal = function(bool){
    if(bool===1){
        $("#optionalmodal").css("display","block");
        $.BodyFixed(1);
    }
    else{
        $.CleanModal();
    }
};
$.BlockModal = function(bool, closer){
    closer = (closer === undefined) ? 0: closer;
    if(bool===1){
        if(closer ===1){
            $("#blockclose").css("display","block");
        }
        $("#blockmodal").css("display","block");
        $.BodyFixed(1);
    }
    else{
        $.CleanModal();
    }
};
$.ShadowModal = function(bool){
    if(bool===1){
        $("#shadowmodal").css("display","block");
        $.BodyFixed(1);
    }
    else{
        $.CleanModal();
    }
};
$.LoadModal= function(bool){
    if(bool===1){
        $("#loadmodal").css("display","block");
        $.BodyFixed(1);
    }
    else{
        $.CleanModal();
    }
};
$.CleanCache = function(){
    $.ajax({
        data: $.RawData("CleanCache")
    });
};
$.TerminosCondiciones = function(){
    $.ajax({
        data: $.RawData("TerminosCondiciones"),
        success: function(response){
            $("#blockcontenedor").html(response);
            $.BlockModal(1,0);
        }
    });
};
$.Uploader = function(elem){
    var url = $.IsNull($(elem).attr("data-url"),"0"),
        filename = $.IsNull($(elem).attr("data-name"),""),
        filetype = $.IsNull($(elem).attr("data-type"),"0");

    $(elem).find(".input_fileupload").fileupload({
        dataType: "json",
        url: "valk/ro/rUploader.php",
        dropZone: $(elem),
        formData: {
            dir_url: url,
            file_name: filename,
            file_type: filetype
        },
        done: function (e, data){
            $.each(data.result.files, function (index, file){
                if(file.error){
                    $(elem).find(".error_fileupload").html(file.error).css("display","block");
                }
                else{
                    $(elem).find(".error_fileupload").css("display","none");
                }
            });
        },
        progressall: function (e, data){
            $(elem).find(".progress_fileupload").css("display", "block");
            var progress = parseInt(data.loaded / data.total * 100, 10);
            $(elem).find(".progress_fileupload").css("width", progress + "%");
        }
    });
};
$(document).ready(function(){
    var body = $("body");
    body.click(function(e){
        if (e.target.id !== "modalview"
            && !($(e.target).parents("#modalview").length)
            && !($(e.target).hasClass("modalup"))
            && $("#optionalmodal").css("display") === "block"){
            $.PopUpModal();
        }
    });
    $(window).scroll(function () {
        if ($(this).scrollTop() > 150) {
            $("#goup").fadeIn();
        } else {
            $("#goup").fadeOut();
        }
    });

});