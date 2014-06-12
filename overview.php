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
$header = get_string('overview', 'block_anxiety_teacher');

$PAGE->navbar->add($blockname);
$PAGE->navbar->add($header);

$PAGE->set_title($blockname . ': '. $header);
$PAGE->set_heading($blockname . ': '.$header);
$PAGE->set_url('/blocks/anxiety_teacher/overview.php');
$PAGE->set_pagetype($blockname);
$PAGE->set_pagelayout('standard');

//Initialise the table
$table = new html_table();
$table->attributes['class'] = 'overviewtable';

//Initialise the tabs - OVERVIEW | SETTINGS
$tabs = array();

$overviewtab = new html_table_cell();
$overviewtab->text = html_writer::tag('static',
    get_string('overview', 'block_anxiety_teacher'));
$tabs[] = $overviewtab;

$settingstab = new html_table_cell();
$settingstab->text = html_writer::link(
    new moodle_url('/blocks/anxiety_teacher/individual_settings.php', array('userid' => $USER->id)),
    get_string('settings', 'block_anxiety_teacher')
);
$tabs[] = $settingstab;

$table->data[] = new html_table_row($tabs);

//Next row of tabs: COURSES (but only if there is more than one course!)
$coursetabs = array();

//Get the context instances where the user is the teacher
$roleassigns = $DB->get_records('role_assignments', array('userid' => $USER->id, 'roleid' => 3), 'contextid');

$teachercourses = array();

foreach ($roleassigns as $roleassign) {
    
    //Get only the context instances where context = course 
    $contextinstances = $DB->get_records('context', array('contextlevel' => 50, 'id' => $roleassign->contextid));
    
    //add to the courses
    $teachercourses = array_merge($teachercourses, $contextinstances);
}

foreach($teachercourses as $teachercourse) {

    //Get the name of the course.
    $course = $DB->get_record('course', array('id' => $teachercourse->instanceid));
    
    $coursetab = new html_table_cell();
    $coursetab->text = html_writer::link(
        new moodle_url('/blocks/anxiety_teacher/course_page.php', array('courseid' => $course->id)),
        $course->shortname
    );
    $coursetabs[] = $coursetab;
}
$table->data[] = new html_table_row($coursetabs);


//Render the HTML
echo $OUTPUT->header();
echo $OUTPUT->heading($blockname);

echo html_writer::start_tag('div', array('class' => 'no-overflow'));

//html table goes here.
echo html_writer::table($table);

echo html_writer::end_tag('div');
echo $OUTPUT->footer();