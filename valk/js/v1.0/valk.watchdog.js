/**
 * watchdog: registro de errores, debug
 */
var HeartBeat;
$.Watchdog_test = function(){
    $.ajax({
        url:  rootajax['watchdog'],
        data: $.RawData({
            "valk": "WDTest"
        }),
    });
};
$.HeartBeat = function(){
    $.ajax({
        data: $.RawData("HeartBeat",null,0),
        success: function (response) {
            /*if(response === "" || response == null){
                HeartBeat = setTimeout(function () {
                    $.HeartBeat();
                }, 5000);
            }
            else{
                $("#midblock").html(response);
            }*/
        }
    });
};
/* errores de documento */
/*
window.onerror = function(message, url, lineNumber) {
    console.log(message);
    console.log(url);
    console.log(lineNumber);
    return true;
};
*/
/* errores en AJAX */
/*
$.ajaxSetup({
    error: function (xhr, ajaxOptions, thrownError){
        console.log(xhr.status);
        console.log(xhr.statusText);
        console.log(ajaxOptions);
        console.log(thrownError);
        //$.Watchdog_test();
    }
});
*/
$.FootPrint = function(load,idload="0",url=1){
    var constructor = $("#constructor");
    load = String(load).toLowerCase();
    idload = String(idload).toLowerCase();
    if(url===1&&(constructor.attr("data-load") !== load || constructor.attr("data-idload") !== idload)){
        window.history.pushState({"tipo": load, "valor": idload}, "", load + ((idload!=="0")? "/" + idload: ""));
    }
    if(url===0){
        window.history.pushState("", "", "");
    }
    $.ShadowMark(load, idload);
    $.Snitch(load, idload);
    $.LifeNote(load, idload);
};
$.ShadowMark = function(load, idload="0"){
    var constructor = $("#constructor");
    constructor.attr({"data-load":load, "data-idload":idload});
};
$.Snitch = function(tipo, valor, funnel = 0){
    if(plugin["gtag"]){
        gtag("event",valor,{'event_category' : tipo});
    }
    if(plugin["fbpixel"]) {
        if(funnel){
            fbq("track", tipo, {content_ids: valor});
        }
        else{
            fbq("trackCustom", tipo, {content_ids: valor});
        }
    }
};