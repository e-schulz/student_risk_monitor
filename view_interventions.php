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

//Teacher must be logged in
require_login();

//Get the ID of the teacher
$userid = required_param('userid', PARAM_INT);
$courseid = required_param('courseid', PARAM_INT);
$interventionid = optional_param('interventionid', -1, PARAM_INT);

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
$PAGE->set_title($blockname . ': ' . $header);
$PAGE->set_heading($blockname . ': ' . $header);
$PAGE->set_url('/blocks/risk_monitor/view_interventions.php?userid=' . $USER->id . '&courseid=' . $courseid);
$PAGE->set_pagetype($blockname);
$PAGE->set_pagelayout('standard');

if($interventionid != -1) {
    
    if($DB->record_exists('block_risk_monitor_int_tmp', array('id' => $interventionid))) {
        $DB->delete_records('block_risk_monitor_int_tmp', array('id' => $interventionid));
    }
    
    //Delete all intervention instances assoc with this template
    if($DB->record_exists('block_risk_monitor_int_inst', array('interventiontemplateid' => $interventionid))) {
        $DB->delete_records('block_risk_monitor_int_inst', array('interventiontemplateid' => $interventionid));
    }
    
    redirect(new moodle_url('/blocks/risk_monitor/view_interventions.php?userid=' . $USER->id . '&courseid=' . $courseid));
}

$back_to_settings = html_writer::link(new moodle_url('individual_settings.php', array('userid' => $USER->id, 'courseid' => $courseid)), "Back to settings");

//$student_profile = new individual_settings_form_view_student('/blocks/risk_monitor/view_student.php?userid='.$USER->id.'&courseid='.$courseid.'&studentid='.$studentid, array('userid' => $userid, 'courseid' => $courseid, 'studentid' => $studentid));
$interventions_form = new individual_settings_form_view_interventions('/blocks/risk_monitor/view_interventions.php?userid=' . $USER->id . '&courseid=' . $courseid, array('userid' => $userid, 'courseid' => $courseid));

//Render the HTML
echo $OUTPUT->header();
echo $OUTPUT->heading($blockname);

//echo html_writer::start_tag('div', array('class' => 'no-overflow'));
//display the settings form
//echo block_risk_monitor_get_tabs_html($userid, true);
echo block_risk_monitor_get_top_tabs('settings', $courseid);
echo $OUTPUT->heading("View interventions");
echo $back_to_settings;

////MAIN CONTENT
//

$interventions_form->display();
echo $OUTPUT->footer();
