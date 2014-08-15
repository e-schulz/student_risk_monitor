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

global $block_anxiety_teacher_block, $DB;

//$DB->delete_records('block_anxiety_teacher_course', array('blockid' => $block_anxiety_teacher_block->id));

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

$context = context_user::instance($userid);

//Set the page parameters
$blockname = get_string('pluginname', 'block_anxiety_teacher');
$header = get_string('settings', 'block_anxiety_teacher');

$PAGE->navbar->add($blockname);
$PAGE->navbar->add($header);

$PAGE->set_context($context);
$PAGE->set_title($blockname . ': '. $header);
$PAGE->set_heading($blockname . ': '.$header);
$PAGE->set_url('/blocks/anxiety_teacher/individual_settings.php?userid='.$userid);
$PAGE->set_pagetype($blockname);
$PAGE->set_pagelayout('standard');

///GETTING THE INFORMATION FROM THE DATABASE
//Here they can add or remove courses
//Need an array of unregistered courses, and an array of registered courses
$all_courses = block_anxiety_teacher_get_courses($USER->id);

//If there are registered courses, need two forms, otherwise one
if ($registered_courses = $DB->get_records('block_anxiety_teacher_course', array('blockid' => $block_anxiety_teacher_block->id))) {
    
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
    $mform1 = new individual_settings_form_add_course('individual_settings.php?userid='.$USER->id, array('courses' => $unregistered_courses));
    $mform2 = new individual_settings_form_remove_course('individual_settings.php?userid='.$USER->id, array('courses' => $registered_courses));
}
else {
    $mform1 = new individual_settings_form_add_course('individual_settings.php?userid='.$USER->id, array('courses' => $all_courses));
    $mform2 = new individual_settings_form_remove_course('individual_settings.php?userid='.$USER->id, array('courses' => array()));    
}

//Get the unregistered courses. need some fancy SQL

///RENDERING THE HTML

//Course added
if ($fromform = $mform1->get_data()) {
    
    //Create a new course instance
    $new_course = new object();
    $new_course->courseid = $fromform->add_course;
    $new_course->blockid = $block_anxiety_teacher_block->id;//????TO DO!
    $new_course->preamble_template = get_string('preamble-template', 'block_anxiety_teacher');
    $new_course->postamble_template = get_string('postamble-template', 'block_anxiety_teacher');

    //Set the full and short names
    foreach($all_courses as $single_course) {
        if($single_course->id === $fromform->add_course) {
            $new_course->fullname = $single_course->fullname;
            $new_course->shortname = $single_course->shortname;
        }
    }
    
    //add to DB
    if (!$DB->insert_record('block_anxiety_teacher_course', $new_course)) {
        echo get_string('errorinsertcourse', 'block_anxiety_teacher');
    }      
    
    //reload page
    redirect(new moodle_url('/blocks/anxiety_teacher/individual_settings.php', array('userid' => $USER->id)));

}
//Course removed
if ($fromform2 = $mform2->get_data()) {
    
    if ($DB->record_exists('block_anxiety_teacher_course', array('blockid' => $block_anxiety_teacher_block->id, 'courseid' => $fromform2->delete_course))) {
        $DB->delete_records('block_anxiety_teacher_course', array('blockid' => $block_anxiety_teacher_block->id, 'courseid' => $fromform2->delete_course));
    } 
    
    //reload page
    redirect(new moodle_url('/blocks/anxiety_teacher/individual_settings.php', array('userid' => $USER->id)));
}

//Render the HTML
echo $OUTPUT->header();
echo $OUTPUT->heading($blockname);

echo html_writer::start_tag('div', array('class' => 'no-overflow'));

//display the settings form
//echo block_anxiety_teacher_get_tabs_html($userid, true);
$currenttoptab = 'settings';
require('top_tabs.php');
$currentcoursetab = '';
require('settings_course_tabs.php');
echo html_writer::end_tag('div');
if ($message == 1) {
    echo '<div>Changes saved.</div><br>';
}
else {
    $mform1->display();       
    $mform2->display();
}

echo $OUTPUT->footer();