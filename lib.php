<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

function block_risk_monitor_pluginfile($course, $record, $context, $filearea, $args, $forcedownload) {
    
    $fs = get_file_storage();
    global $DB;

    list($itemid, $filename) = $args;
    
    $fs = get_file_storage();
    $files = $fs->get_area_files($context->id, 'block_risk_monitor', 'intervention_files', $itemid, 'sortorder', false); // TODO: this is not very efficient!!
    
    //Check filename exists.
    foreach($files as $file) {

        //$filename_size = strlen($file->get_filename());
        //$filename_from_args = substr($args, $filename_size);
        if($file->get_filename() === $filename) {
           send_stored_file($file);
        }
    }
}