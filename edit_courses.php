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

global $block_risk_monitor_block, $DB;

//$DB->delete_records('block_risk_monitor_course', array('blockid' => $block_risk_monitor_block->id));

//Teacher must be logged in
require_login();

//Get the ID of the teacher
$userid = required_param('userid', PARAM_INT);
//$message = optional_param('message', 0, PARAM_INT);
$message = optional_param('message', '', PARAM_RAW);

//Error- there is no user associated with the passed param
if (!$getuser = $DB->get_record('user', array('id' => $userid))) {
    print_error('no_user', 'block_risk_monitor', '', $userid);
}

//Error - the user trying to access this instance is the wrong one
if (!($USER->id == $userid)) {
    print_error('wrong_user', 'block_risk_monitor', '', $userid);
}

$context = context_user::instance($userid);

//Set the page parameters
$blockname = get_string('pluginname', 'block_risk_monitor');
$header = get_string('settings', 'block_risk_monitor');

$PAGE->navbar->add($blockname);
$PAGE->navbar->add($header);

$PAGE->set_context($context);
$PAGE->set_title($blockname . ': '. $header);
$PAGE->set_heading($blockname . ': '.$header);
$PAGE->set_url('/blocks/risk_monitor/edit_courses.php?userid='.$userid);
$PAGE->set_pagetype($blockname);
$PAGE->set_pagelayout('standard');


$body = '';
if($message == 'course-deleted') {
    $body .= "Course removed.";
}
else if ($message == 'course-added'){
    $body .= "Course added.";
}
else {
    
}
//Add or delete course

$all_courses = block_risk_monitor_get_courses($USER->id);

//If there are registered courses, need two forms, otherwise one
if ($registered_courses = $DB->get_records('block_risk_monitor_course', array('blockid' => $block_risk_monitor_block->id))) {

    //Create a new array containing only unregistered courses
    $unregistered_courses = array();
     //Loop thru all the courses and add only those that aren't already registered
    foreach($all_courses as $single_course) {
        $registered = false;
        foreach($registered_courses as $registered_course) {
            if($single_course->id === $registered_course->courseid) {
                $registered = true;
                break;
            }
        }
        if ($registered == false) {
            $unregistered_courses[] = $single_course;
        }
     }
}
else {
    $unregistered_courses = $all_courses; 
    $registered_courses = array();
}

//The add/delete form
$add_form = new individual_settings_form_add_course('edit_courses.php?userid='.$USER->id, array('courses' => $unregistered_courses)); 
$delete_form = new individual_settings_form_remove_course('edit_courses.php?userid='.$USER->id, array('courses' => $registered_courses));
//$add_delete_form = new individual_settings_form_add_remove_courses('edit_courses.php?userid='.$USER->id, array('courses_to_add' => $unregistered_courses, 'courses_to_delete' => $registered_courses));    
$back_to_settings = html_writer::link (new moodle_url('individual_settings.php', array('userid' => $USER->id)), get_string('back_to_settings','block_risk_monitor'));
    

///RENDERING THE HTML

//from add/delete form
if ($fromform1 = $add_form->get_data()) {
        
    //If add create course, supply message saying course added and redirect
    $new_course = new object();
    $new_course->courseid = $fromform1->add_course;
    $new_course->blockid = $block_risk_monitor_block->id;
    foreach($all_courses as $single_course) {
        if($single_course->id === $fromform1->add_course) {
            $new_course->fullname = $single_course->fullname;
            $new_course->shortname = $single_course->shortname;
        }
    }
    
    //add to DB
    if (!$DB->insert_record('block_risk_monitor_course', $new_course)) {
        echo get_string('errorinsertcourse', 'block_risk_monitor');
    }      
    
    redirect(new moodle_url('edit_courses.php', array('userid' => $USER->id, 'message' => 'course-added')));
    
}

if ($fromform2 = $delete_form->get_data()) {
            
    if ($DB->record_exists('block_risk_monitor_course', array('blockid' => $block_risk_monitor_block->id, 'courseid' => $fromform2->delete_course))) {
        $DB->delete_records('block_risk_monitor_course', array('blockid' => $block_risk_monitor_block->id, 'courseid' => $fromform2->delete_course));
    } 
    else {
        echo get_string('errorcoursenotexist', 'block_risk_monitor');
    }
    
    //If delete
    redirect(new moodle_url('edit_courses.php', array('userid' => $USER->id, 'message' => 'course-deleted')));
}

//Render the HTML
echo $OUTPUT->header();
echo $OUTPUT->heading($blockname);

//echo html_writer::start_tag('div', array('class' => 'no-overflow'));

//display the settings form
//echo block_risk_monitor_get_tabs_html($userid, true);
echo block_risk_monitor_get_top_tabs('settings');

echo $body;

//Display forms
$add_form->display();

$delete_form->display();

//Display button
echo $back_to_settings;

echo $OUTPUT->footer();