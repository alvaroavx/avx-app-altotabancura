<?php
if(isset($Meta['Title'])) {
    echo '<title>' . $Meta['Title'] . '</title>'
	    .'<base href="'.constant('Root_Base').'">';
    foreach ($Meta['List'] as $li) {
        echo '<meta';
        foreach ($li as $lik => $liv) {
            echo ' ' . $lik . '="' . $liv . '"';
        }
        echo '>';
    }
    echo '<link rel="shortcut icon" href="' . $Meta['Favicon'] . '?v' . constant('Local_Favicon') . '">'
        . '<link rel="icon" href="' . $Meta['Favicon'] . '?v' . constant('Local_Favicon') . '">';
}