<?php
function Edward($Valk, $Variables) {
	echo('<script>console.log("DEBUG: '.$Valk.'('.str_replace('"','', json_encode($Variables)).')");</script>');
}