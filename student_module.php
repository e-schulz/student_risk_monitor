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

global $DB;

require_login();

//Get the ID of the teacher
$userid = required_param('userid', PARAM_INT);
$courseid = required_param('courseid', PARAM_INT);
$interventionid = required_param('interventionid', PARAM_INT);
       
//Error- there is no user associated with the passed param
if (!$getuser = $DB->get_record('user', array('id' => $userid))) {
    print_error('no_user', 'block_risk_monitor', '', $userid);
}

//Error - the user trying to access this instance is the wrong one
if (!($USER->id == $userid)) {
    print_error('wrong_user', 'block_risk_monitor', '', $userid);
}
$intervention_instance = $DB->get_record('block_risk_monitor_int_inst', array('studentid' => $userid, 'interventiontemplateid' => $interventionid));
$updated_instance = new object();
$updated_instance->id = $intervention_instance->id;
$updated_instance->viewed = 1;
$DB->update_record('block_risk_monitor_int_inst', $updated_instance);

$intervention_template = $DB->get_record('block_risk_monitor_int_tmp', array('id' => $interventionid));
$context = context_user::instance($userid);

//Set the page parameters
$blockname = get_string('studentpluginname', 'block_risk_monitor');
$header = $intervention_template->title;

$PAGE->navbar->add($blockname);
$PAGE->navbar->add($header);

$PAGE->set_context($context);
$PAGE->set_title($blockname . ': '. $header);
$PAGE->set_heading($blockname . ': '.$header);
$PAGE->set_url('/blocks/risk_monitor/student_module.php?userid='.$userid.'&courseid='.$courseid);
$PAGE->set_pagetype($blockname);
$PAGE->set_pagelayout('standard');

//Create the body
$body = '';

$back_to_course = html_writer::link (new moodle_url('/course/view.php?id='.$courseid), "Back to course page");

//Create the form
$intervention_form = new individual_settings_form_view_intervention('student_module.php?userid='.$USER->id.'&courseid='.$courseid.'&interventionid='.$interventionid, array('interventionid' => $interventionid, 'courseid' => $courseid, 'userid' => $userid)); 
$intervention_instructions = new individual_settings_form_view_intervention_instructions('student_module.php?userid='.$USER->id.'&courseid='.$courseid.'&interventionid='.$interventionid, array('interventionid' => $interventionid)); 

//Render the HTML
echo $OUTPUT->header();
echo $OUTPUT->heading($blockname);

echo $OUTPUT->heading($intervention_template->title);
echo $OUTPUT->box_start();
$intervention_instructions->display();
echo $OUTPUT->box_end();
echo $OUTPUT->box_start();
$intervention_form->display();
echo $OUTPUT->box_end();
echo $back_to_course;
echo $OUTPUT->footer();