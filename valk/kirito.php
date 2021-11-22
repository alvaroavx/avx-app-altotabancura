<?php
if(constant('Entorno_Developer')){
	echo '<div class="bannerbeta selectDisable" onclick="$.HideBeta();"></div>'
		.'<div class="textobannerbeta" onclick="$.HideBeta();">MODO DEVELOPER</div>';
}