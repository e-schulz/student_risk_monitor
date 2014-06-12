<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

///REQUIRES AND ERROR MESSAGES

require_once("../../config.php");
//Teacher must be logged in
require_login();

//Get the ID of the teacher
$userid = required_param('userid', PARAM_INT);

//Error- there is no user associated with the passed param
if (!$getuser = $DB->get_record('user', array('id' => $userid))) {
    print_error('no_user', 'block_test_anxiety_teacher', '', $userid);
}

//Error - the user trying to access this instance is the wrong one
if (!($USER->id == $userid)) {
    print_error('wrong_user', 'block_test_anxiety_teacher', '', $userid);
}


///GETTING THE INFORMATION FROM THE DATABASE
//From the DB we will need: all registered courses associated with this teacher, and the associated exam



///RENDERING THE HTML
//Set the page parameters
$blockname = get_string('pluginname', 'block_test_anxiety_teacher');
$header = get_string('settings', 'block_test_anxiety_teacher');

$PAGE->navbar->add($blockname);
$PAGE->navbar->add($header);

$PAGE->set_title($blockname . ': '. $header);
$PAGE->set_heading($blockname . ': '.$header);
$PAGE->set_url('/blocks/test_anxiety_teacher/settings.php');
$PAGE->set_pagetype($blockname);
$PAGE->set_pagelayout('standard');

//Create the form (look at quickmail->email for this!)
//
//Render the HTML
echo $OUTPUT->header();
echo $OUTPUT->heading($blockname);

echo html_writer::start_tag('div', array('class' => 'no-overflow'));

//display the settings form
//$form->display();

echo html_writer::end_tag('div');
echo $OUTPUT->footer();