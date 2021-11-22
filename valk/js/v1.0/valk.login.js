$.Autentificar = function(){
	$("#error_login").html("");
	$.ajax({
		data: $.RawData("Autentificar",{
			"usuario": $("#user_login").val(),
			"pass": $("#pass_login").val(),
			"redsocial" : "1"
		}),
		success: function(response){
			$("#error_login").html(response).css("display","inline-block");
		}
	});
};
$.Registro = function(){
	$("#error_login").html("");
	$.ajax({
		data: $.RawData("Registrar",{
			"usuario": $("#user_login").val(),
			"nombre": $("#name_login").val(),
			"apellido": $("#surname_login").val(),
			"pass": $("#pass_login").val()
		}),
		success: function(response){
			$("#error_login").html(response).css("display","inline-block");
		}
	});
};
$.RecoverPass = function(){
	$("#error_login").html("");
	if ($.LoadWaitRecover){
		$.LoadWaitRecover();
	}
	$.ajax({
		data: $.RawData("RecoverPass",{
			"usuario": $("#user_login").val()
		})
	});
};
$.ChangePass = function(){
	$("#error_login").html("");
	$.ajax({
		data: $.RawData("ChangePass",{
			"pass": $("#pass_login").val(),
			"newpass": $("#newpass_login").val()
		}),
		success: function(response){
			$("#error_login").html(response).css("display","inline-block");
		}
	});
};
$.RecoverPassPush = function(){
	$("#error_login").html("");
	$.ajax({
		data: $.RawData("RecoverPassPush",{
			"pass": $("#pass_login").val()
		}),
		success: function(response){
			$("#error_login").html(response).css("display","inline-block");
		}
	});
};
$.DeleteSesion = function(){
	$.ajax({
		data: $.RawData("DeleteSesion"),
		success: function (response) {
			$("#midblock").html(response);
		}
	});
};
$.LoadCoreRecoverPass = function(){
	$.ajax({
		data: $.RawData("LoadCoreRecoverPass"),
		success: function (response) {
			$("#midblock").html(response);
		}
	});
};
$.LoadCoreRegistro = function(){
	$.ajax({
		data: $.RawData("LoadCoreRegistro"),
		success: function (response) {
			$("#midblock").html(response);
		}
	});
};
$.LoadWaitRecover = function(){
	$.ajax({
		data: $.RawData("LoadWaitRecover"),
		success: function (response) {
			$("#midblock").html(response);
		}
	});
};