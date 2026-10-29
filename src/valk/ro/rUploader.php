<?php
require_once(constant('Root_Fisica').'valk/mu/cUploadHandler.php');
$options= array(
    'accept_file_types' => (($_REQUEST['file_type'])? '/\.('.$_REQUEST['file_type'].')/'
        : '/\.(pdf|txt|doc|docx|xls|xlsx|ppt|pptx|jpe?g|gif|png)/'),
    'max_file_size' => 50 * 1000000,
    'upload_dir' => '../../'.( ($_REQUEST['dir_url']) ? $_REQUEST['dir_url'] : constant('Root_Static')),
    'upload_url' => '../../'.(($_REQUEST['dir_url']) ? $_REQUEST['dir_url'] : constant('Root_Static')),
    'file_name' => $_REQUEST['file_name']
);
$upload_handler = new UploadHandler($options);
