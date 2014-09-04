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
$studentid = required_param('studentid', PARAM_INT);
$categoryid = required_param('categoryid', PARAM_INT);

//Error- there is no user associated with the passed param
if (!$getuser = $DB->get_record('user', array('id' => $userid))) {
    print_error('no_user', 'block_risk_monitor', '', $userid);
}

//Error - the user trying to access this instance is the wrong one
if (!($USER->id == $userid)) {
    print_error('wrong_user', 'block_risk_monitor', '', $userid);
}
//The student.
$student = $DB->get_record('user', array('id' => $studentid));
$category = $DB->get_record('block_risk_monitor_category', array('id' => $categoryid));
$context = context_user::instance($userid);

//Set the page parameters
$blockname = get_string('pluginname', 'block_risk_monitor');
$header = get_string('settings', 'block_risk_monitor');

$PAGE->navbar->add($blockname);
$PAGE->navbar->add($header);

$PAGE->set_context($context);
$PAGE->set_title($blockname . ': '. $header);
$PAGE->set_heading($blockname . ': '.$header);
$PAGE->set_url('/blocks/risk_monitor/view_category.php?userid='.$USER->id.'&courseid='.$courseid.'&studentid='.$studentid.'&categoryid='.$categoryid);
$PAGE->set_pagetype($blockname);
$PAGE->set_pagelayout('standard');

$back_to_overview = html_writer::link (new moodle_url('overview.php', array('userid' => $USER->id, 'courseid' => $courseid)), "Back to overview");

$body = '';

$category_profile = new individual_settings_form_view_category('/blocks/risk_monitor/view_category.php?userid='.$USER->id.'&courseid='.$courseid.'&studentid='.$studentid.'&categoryid='.$categoryid, array('userid' => $userid, 'courseid' => $courseid, 'studentid' => $studentid, 'categoryid' => $categoryid));
//prepopulate with data.

if($fromform = $category_profile->get_data()) {
    //Get which checkboxes are checked
    if ($intervention_templates = $DB->get_records('block_risk_monitor_int_tmp', array('categoryid' => $categoryid))) {
        foreach($intervention_templates as $intervention_template) {
            $form_identifier = 'intervention'.$intervention_template->id;
            if($fromform->$form_identifier == 1) {
                //Checked, create intervention instance.
                if(!$DB->record_exists('block_risk_monitor_int_inst', array('studentid' => $studentid, 'interventiontemplateid' => $intervention_template->id))) {
                    $intervention_instance = new object();
                    $intervention_instance->studentid = $studentid;
                    $intervention_instance->timestamp = time();
                    $intervention_instance->interventiontemplateid = $intervention_template->id;
                    $intervention_instance->viewed = 0;

                    $DB->insert_record('block_risk_monitor_int_inst', $intervention_instance);
                }
            }
        }
    }
    //Create intervention instances.
}

//Render the HTML
echo $OUTPUT->header();
echo $OUTPUT->heading($blockname);


//echo html_writer::start_tag('div', array('class' => 'no-overflow'));

//display the settings form
//echo block_risk_monitor_get_tabs_html($userid, true);
echo block_risk_monitor_get_top_tabs('overview', $courseid);
echo $OUTPUT->heading($category->name." risk: ".$student->firstname."&nbsp;".$student->lastname);

$category_profile->display();
echo $body;
echo $back_to_overview;
echo $OUTPUT->footer();