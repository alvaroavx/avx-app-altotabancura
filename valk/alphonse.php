<?php
/**
 * CASOS:
 * MASCARA
 * cache  js css
 * DIRECTO
 * res
 * fonts
 * DEVELOPER js css
 * NOMBRE CODIFICADO con extension
 * static      /s/qwerty.jpg      jpg, svg, png, ico, gif
 * MASCARA CON PARAMETRO CODIFICADO sin extension
 * out         /o/qwerty     Nombre.xlsx ..... multiples extensiones
 * endpoints   /e/qwerty    shortcut.php
 * MASCARA SIN PARAMETRO
 * gatekeeper  /g
 * uploads     /u rUploader.php
 * VARIABLES
 * alphonse    /param
 * alphonse    /param/val
 */
$DebugAlphonse = 0;

$Url = preg_replace( '/\?.*/','',str_replace(constant('Prefix_Fisico'),'',str_replace('/'.strtolower(constant('Gate')),'',$_SERVER['REQUEST_URI'])));
$Ruta = '';
/**********************************************************************************************************************/
if($Url == '/test.php' ){
	include(constant('Root_Fisica').$Url);
	exit();
}
/** MASCARA cache .js, .css*/
if (preg_match('/'. '^\/'.constant('Prefix_Cache').'\/.+\.(js|css)(?!.+)' . '/', $Url)) {
	$Ruta = constant('Root_Fisica').str_replace('/'.constant('Prefix_Cache').'/', constant('Root_Cache'),$Url);
	echo (($DebugAlphonse)? '<br>CACHE JS, CSS':'');
}
/** DIRECTO  res/nombre.ext .png, .jpeg, .jpg, .svg, .ico, .bm, .bmp, .gif, .mjpg*/
else if (preg_match('/'. '\/res\/.+\.(png|bmp*|jpe*g|svg|ico|gif|mjpg)(?!.+)' . '/', $Url)) {
	$Ruta = constant('Root_Fisica').$Url;
	echo (($DebugAlphonse)? '<br>RES .png, .jpeg, .jpg, .svg, .ico, .bm, .bmp, .gif, .mjpg':'');
}
/*TODO FALTA FONTS*/
/** DIRECTO  FONTS*/
else if (preg_match('/'. '\/fonts\/.+\.(PENDIENTE)(?!.+)' . '/', $Url)) {
	$Ruta = constant('Root_Fisica').$Url;
	echo (($DebugAlphonse)? '<br>FONTS':'');
}
/** DIRECTO  DEVELOPER js css VALK*/
else if (preg_match('/'. '\/valk\/(js|css)\/.+\.(js|css)(?!.+)' . '/', $Url)) {
	$Ruta = constant('Root_Fisica').$Url;
	echo (($DebugAlphonse)? '<br>DEVELOPER .js, .css valk':'');
}
/** DIRECTO  DEVELOPER js css VENDOR*/
else if (preg_match('/'. '\/vendor\/'.'.+\.(js|css)(?!.+)' . '/', $Url)) {
	$Ruta = constant('Root_Fisica').$Url;
	echo (($DebugAlphonse)? '<br>DEVELOPER .js, .css vendor':'');
}
/** DIRECTO  DEVELOPER js css*/
else if (preg_match('/'. '\/gate\/(js|css)\/.+\.(js|css)(?!.+)' . '/', $Url)) {
	$Ruta = constant('Root_Fisica').$Url;
	echo (($DebugAlphonse)? '<br>DEVELOPER .js, .css':'');
}
/** NOMBRE CODIFICADO con extension  /s/qwerty.ext  .png, .jpeg, .jpg, .svg, .ico, .bm, .bmp, .gif, .mjpg*/
else if (preg_match('/'. '\/static\/.+\.(png|bmp*|jpe*g|svg|ico|gif|mjpg)(?!.+)' . '/', $Url)) {
	$Ruta = constant('Root_Fisica').$Url;
	echo (($DebugAlphonse)? '<br>STATIC .png, .jpeg, .jpg, .svg, .ico, .bm, .bmp, .gif, .mjpg':'');
}
/** MASCARA CON PARAMETRO CODIFICADO sin extension  /o/qwerty     Nombre.xlsx ..... multiples extensiones*/
else if (preg_match('/'. '\/'.constant('Prefix_Out').'\/.+(?!\..+)' . '/', $Url)) {
    $Url = str_replace('/'.constant('Prefix_Out').'/', '',$Url);
    $Ruta = constant('Root_Fisica').constant('Root_Out').$Url;
    echo (($DebugAlphonse)? '<br>OUT /o/qwerty':'');
}
/*else if (preg_match('/'. '\/'.constant('Root_Out').'\/.+(?!\..+)' . '/', $Url)) {
    $Url = str_replace('/'.constant('Root_Out').'/', '',$Url);
    $Ruta = constant('Root_Fisica').constant('Root_Out').$Url;
    echo (($DebugAlphonse)? '<br>OUT /out/qwerty':'');
}*/
/** MASCARA CON PARAMETRO CODIFICADO sin extension  endpoints /e/qwerty */
else if (preg_match('/'. '\/'.constant('Prefix_Endpoint').'\/.+(?!\..+)' . '/', $Url)) {
	$Codigo = str_replace('/'.constant('Prefix_Endpoint').'/', '',$Url);
	if($Codigo = urldecode(encrypt_decrypt('decrypt',$Codigo))){
		$Ruta = constant('Root_Fisica').constant('File_Endpoint');
	}
	echo (($DebugAlphonse)? '<br>ENDPOINT /e/qwerty':'');
}
/** MASCARA SIN PARAMETRO sin extension gatekeeper  /g */
else if (preg_match('/'. '^\/'.constant('Prefix_Gate').'(?!.+)' . '/', $Url)) {
	$Ruta = constant('Root_Fisica').constant('File_Gatekeeper');
	echo (($DebugAlphonse)? '<br>GATEKEEPER':'');
}
/*TODO FALTA UPLOADS*/
/** MASCARA SIN PARAMETROS sin extension  upload /u */
else if (preg_match('/'. '\/'.constant('Prefix_Upload').'\/.+(?!\..+)' . '/', $Url)) {

	echo (($DebugAlphonse)? '<br>UPLOAD /u':'');
}
$Info = new SplFileInfo($Url);
$Extension = $Info->getExtension();
$Extension = (($Extension != '0')? $Extension : '');
echo (($DebugAlphonse)? '<br>'.$Url.'<br>'.$Extension.'<br>'.$Ruta:'');
$MimeType = [
	'bm'    => 'image/bmp',
	'bmp'   => 'image/bmp',
	'css'   => 'text/css',
	'gif'   => 'image/gif',
	'jpeg'  => 'image/jpeg',
	'jpg'   => 'image/jpeg',
	'mjpg'  => 'video/x-motion-jpeg',
	'png'   => 'image/png',
	'pdf'   => 'application/pdf',
	'js'    => 'application/x-javascript',
	'ico'   => 'image/x-icon',
	'svg'   => 'image/svg+xml',
	'xlsx'  => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
];
if($Ruta != '' && file_exists($Ruta)){
	switch($Extension){
		case 'js':
		case 'css':
		case 'png':
		case 'jpg':
		case 'mjpg':
		case 'jpeg':
		case 'gif':
		case 'ico':
		case 'bm':
		case 'bmp':
		case 'svg':
			$mtime = filemtime($Ruta);
			$time = gmdate('r', $mtime);
			$etag = md5($mtime.$Ruta);
			header("Last-Modified: $time");
			header('Cache-Control: public, max-age=31536000');
			header("Etag: $etag");
			header("Pragma:");
			header('Content-type: '.$MimeType[$Extension]);
			break;
		case 'xlsx':
			header('Content-disposition: attachment; filename="'.$Url.'"');
			header('Content-type: '.$MimeType[$Extension]);
			header('Content-Transfer-Encoding: binary');
			break;
        case 'pdf':
            header('Content-type:application/pdf');
            header('Content-Disposition:attachment;filename="'.$Url.'"');
            // The PDF source is in original.pdf
            readfile($Url);
            break;
	}
	if($DebugAlphonse){
		exit();
	}
	include($Ruta);
	exit();
}
/** VARIABLES sin extension  shortcut  /param_1/val_1, /param_1/param_2/val_2, /param_1/val_1/param_2/val_2, /param  */
$Shorts = explode('/', ltrim($Url, '/'));
$Load = constant('Default_Load');
$IdLoad = constant('Default_IdLoad');
/**********************/
foreach(array_values($Shorts) as $shor => $ter){
	if(isset($Shortcut[$ter])){
		$Load = $ter;
		$IdLoad = (isset($Shorts[$shor+1])? $Shorts[$shor+1] : $Shortcut[$ter]);
	}
	break;
}
echo (($DebugAlphonse)? '<br>VARIABLES shortcut '.$Load.'-'.$IdLoad:'');
if($DebugAlphonse){
	exit();
}
header("location:");