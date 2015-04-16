<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

///REQUIRES AND ERROR MESSAGES

require_once("../../../config.php");
require_once("../locallib.php");
require_once("../student_risk_monitor_forms.php");

global $DB;

//Teacher must be logged in
require_login();

//Get the ID of the teacher
$userid = required_param('userid', PARAM_INT);
$courseid = required_param('courseid', PARAM_INT);
$interventionid = required_param('interventionid', PARAM_INT);
$from_overview = optional_param('from_overview', -1, PARAM_INT);
$from_studentid = optional_param('from_studentid', -1, PARAM_INT);
$from_categoryid = optional_param('from_categoryid', -1, PARAM_INT);

//Error- there is no user associated with the passed param
if (!$getuser = $DB->get_record('user', array('id' => $userid))) {
    print_error('no_user', 'block_risk_monitor', '', $userid);
}

//Error - the user trying to access this instance is the wrong one
if (!($USER->id == $userid)) {
    print_error('wrong_user', 'block_risk_monitor', '', $userid);
}

$intervention_template = $DB->get_record('block_risk_monitor_int_tmp', array('id' => $interventionid));
$context = context_user::instance($userid);

//Set the page parameters
$blockname = get_string('pluginname', 'block_risk_monitor');
$header = get_string('overview', 'block_risk_monitor'); $action = new moodle_url('overview.php', array('userid' => $USER->id, 'courseid' => $courseid));

$PAGE->navbar->add($blockname, new moodle_url('overview.php', array('userid' => $USER->id, 'courseid' => $courseid))); 
$PAGE->navbar->add($header, $action); 

$PAGE->set_context($context);
$PAGE->set_title($blockname . ': ' . $header);
$PAGE->set_heading($blockname . ': ' . $header);
$PAGE->set_url('/blocks/risk_monitor/teacher_block/view_intervention.php?userid=' . $USER->id . '&courseid=' . $courseid.'&interventionid='.$interventionid);
$PAGE->set_pagetype($blockname);
$PAGE->set_pagelayout('standard');

$has_resources = false;
if($intervention_template->has_files == 1 || ($intervention_template->url != '' && $intervention_template->url != 'http://')) {
    $has_resources = true;
}

if($from_overview != -1) {
    $back_link = html_writer::link(new moodle_url('view_category_risk.php', array('userid' => $USER->id, 'courseid' => $courseid, 'studentid' => $from_studentid, 'categoryid' => $from_categoryid)), "Back to student overview");
}
else {
    $back_link = html_writer::link(new moodle_url('edit_intervention_templates.php', array('userid' => $USER->id, 'courseid' => $courseid)), "Back to interventions")." | ".
            html_writer::link(new moodle_url('edit_intervention.php', array('userid' => $USER->id, 'courseid' => $courseid, 'interventionid' => $interventionid)), "Edit template")." | ".
            html_writer::link(new moodle_url('edit_intervention_templates.php', array('userid' => $USER->id, 'courseid' => $courseid, 'templateid' => $interventionid)), "Delete template");
    
    
}

$body = '';

//$student_profile = new individual_settings_form_view_student('/blocks/risk_monitor/view_student.php?userid='.$USER->id.'&courseid='.$courseid.'&studentid='.$studentid, array('userid' => $userid, 'courseid' => $courseid, 'studentid' => $studentid));
$intervention_form = new individual_settings_form_view_intervention('/blocks/risk_monitor/teacher_block/view_intervention.php?userid=' . $USER->id . '&courseid=' . $courseid."&interventionid=".$interventionid, array('userid' => $userid, 'courseid' => $courseid, 'interventionid' => $interventionid));
$instructions_form = new individual_settings_form_view_intervention_instructions('/blocks/risk_monitor/teacher_block/view_intervention.php?userid=' . $USER->id . '&courseid=' . $courseid."&interventionid=".$interventionid, array('interventionid' => $interventionid, 'studentid' => $from_studentid));

//Render the HTML
echo $OUTPUT->header();
echo $OUTPUT->heading($blockname);


//echo html_writer::start_tag('div', array('class' => 'no-overflow'));
//display the settings form
//echo block_risk_monitor_get_tabs_html($userid, true);
if($from_overview != -1) {
    echo block_risk_monitor_get_top_tabs('overview', $courseid); 
    echo $OUTPUT->heading("Intervention preview: ".$intervention_template->title);

}
else {
    echo block_risk_monitor_get_top_tabs('settings', $courseid);
    echo $OUTPUT->heading($intervention_template->title);

}
echo $back_link."<br><br>";
echo $OUTPUT->box_start();
echo "<b>Message</b>";
$instructions_form->display();
echo $OUTPUT->box_end();
if($has_resources == true) {
    echo $OUTPUT->box_start();
    echo "<b>Resources</b>";
    $intervention_form->display();
    echo $OUTPUT->box_end();
}

echo $OUTPUT->footer();
