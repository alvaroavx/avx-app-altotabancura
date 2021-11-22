<?php
/**NJONG 05.09.18: Clases del Gate**/
$Root = constant('Root_Fisica').constant('Root_Class').constant('Prefix_Class');
foreach($Construct as $co) {
    if(file_exists($Root.$co.'.php')) {
        require_once($Root.$co.'.php');
    }
}