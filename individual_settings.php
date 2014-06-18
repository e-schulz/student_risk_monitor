<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

///REQUIRES AND ERROR MESSAGES

require_once("../../config.php");
require_once("locallib.php");
require_once("individual_settings_form.php");

global $block_anxiety_teacher_config;

//Teacher must be logged in
require_login();

//Get the ID of the teacher
$userid = required_param('userid', PARAM_INT);
$message = optional_param('message', 0, PARAM_INT);

//Error- there is no user associated with the passed param
if (!$getuser = $DB->get_record('user', array('id' => $userid))) {
    print_error('no_user', 'block_anxiety_teacher', '', $userid);
}

//Error - the user trying to access this instance is the wrong one
if (!($USER->id == $userid)) {
    print_error('wrong_user', 'block_anxiety_teacher', '', $userid);
}


///GETTING THE INFORMATION FROM THE DATABASE
//From the DB we will need: all registered courses associated with this teacher, and the associated exam



///RENDERING THE HTML
//Set the page parameters
$blockname = get_string('pluginname', 'block_anxiety_teacher');
$header = get_string('settings', 'block_anxiety_teacher');

$PAGE->navbar->add($blockname);
$PAGE->navbar->add($header);

$PAGE->set_title($blockname . ': '. $header);
$PAGE->set_heading($blockname . ': '.$header);
$PAGE->set_url('/blocks/anxiety_teacher/individual_settings.php?userid='.$userid);
$PAGE->set_pagetype($blockname);
$PAGE->set_pagelayout('standard');

//Create the form (look at quickmail->email for this!)
$mform = new individual_settings_form('individual_settings.php?userid='.$USER->id);
if ($fromform = $mform->get_data()) {
    
    $message = get_string('changessaved','block_anxiety_teacher').'<br>';
    
    //save the changes to DB.
    //check if there is already a config saved and replace it
    if ($existing = $DB->get_record('block_anxiety_teacher_config', array('teacherid' => $USER->id))) {
        $changes = array('id' => $existing->id,
                         'timebeforeexam' => $fromform->numberdays*60*60*24,
                         'dateupdated' => time());
        $DB->update_record('block_anxiety_teacher_config', $changes);
    }
    else {      //this shouldn't really happen
        $newconfig = new object();
        $newconfig->teacherid = $USER->id;      
        $newconfig->timebeforeexam = $fromform->numberdays;
        $newconfig->dateupdated = time();
        $DB->insert_record('block_anxiety_teacher_config', $newconfig);
    }
    
    //redirect and show message "Changes saved"
    redirect(new moodle_url('/blocks/anxiety_teacher/individual_settings.php', array('userid' => $USER->id, 'message' => 1)));       

}

//Render the HTML
echo $OUTPUT->header();
echo $OUTPUT->heading($blockname);

echo html_writer::start_tag('div', array('class' => 'no-overflow'));

//display the settings form
//echo block_anxiety_teacher_get_tabs_html($userid, true);
$currenttoptab = 'settings';
require('top_tabs.php');
echo html_writer::end_tag('div');
if ($message == 1) {
    echo '<div>Changes saved.</div><br>';
}
else {
    $mform->display();
}
echo $OUTPUT->footer();