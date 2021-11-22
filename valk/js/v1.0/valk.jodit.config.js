$(document).ready(function() {
	$.ConfigJodit();
});

/**
 * Jodit.placeholder = "Escribe acá...";
 Jodit.showPlaceholder = false;
 * */

$.ConfigJodit = function(){
	if(typeof Jodit !== "undefined"){
		Jodit.defaultOptions.uploader = {"insertImageAsBase64URI": true};
		//Jodit.defaultOptions.enter = "BR";
		//Jodit.defaultOptions.language = "es";
		Jodit.defaultOptions.showCharsCounter = false;
		Jodit.defaultOptions.showWordsCounter = false;
		Jodit.defaultOptions.showXPathInStatusbar = false;
		Jodit.defaultOptions.toolbarAdaptive = false;
		Jodit.defaultOptions.toolbarSticky= false;
		Jodit.defaultOptions.buttons = "source,paragraph,bold,underline,italic,|,hr,copyformat,|,ul,ol,|,outdent,indent,align,|,fontsize,brush,|,image,video,table,link,|,undo,redo,|,fullsize";
	}
	else{
		setTimeout(function () {$.ConfigJodit()}, 100);
	}
};