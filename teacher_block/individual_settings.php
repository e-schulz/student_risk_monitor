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

//create some student data.
/*$data = new object();
$data->userid = 3;
$data->categoryid = 7;
$data->value = 40;
$data->timestamp = time();
$DB->insert_record('block_risk_monitor_cat_risk', $data);*/
//$DB->delete_records('block_risk_monitor_int_tmp');

//Teacher must be logged in
require_login();

//Get the ID of the teacher
$userid = required_param('userid', PARAM_INT);
$courseid = required_param('courseid', PARAM_INT);

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
$header = get_string('settings', 'block_risk_monitor'); $action = new moodle_url('individual_settings.php', array('userid' => $USER->id, 'courseid' => $courseid));

$PAGE->navbar->add($blockname, new moodle_url('overview.php', array('userid' => $USER->id, 'courseid' => $courseid))); 
$PAGE->navbar->add($header, $action); 

$PAGE->set_context($context);
$PAGE->set_title($blockname . ': '. $header);
$PAGE->set_heading($blockname . ': '.$header);
$PAGE->set_url('/blocks/risk_monitor/teacher_block/individual_settings.php?userid='.$userid."&courseid=".$courseid.'&courseid='.$courseid);
$PAGE->set_pagetype($blockname);
$PAGE->set_pagelayout('standard');

//Create the body
$body = '';

//Link to edit categories and rules
$body .= html_writer::link (new moodle_url('edit_categories_rules.php', array('userid' => $USER->id, 'courseid' => $courseid)), get_string('edit_categories_rules','block_risk_monitor')).'<br><br>';
            
//Description for add or delete
$body .= html_writer::tag('div', get_string('edit_categories_rules_description','block_risk_monitor').'<br><br>');

//Link to edit interventions
$body .= html_writer::link (new moodle_url('edit_intervention_templates.php', array('userid' => $USER->id, 'courseid' => $courseid)), get_string('edit_interventions','block_risk_monitor')).'<br><br>';
            
//Description for add or delete
$body .= html_writer::tag('div', get_string('edit_interventions_description','block_risk_monitor').'<br><br>');

///RENDERING THE HTML

//Render the HTML
echo $OUTPUT->header();
echo $OUTPUT->heading($blockname);

//echo html_writer::start_tag('div', array('class' => 'no-overflow'));

//display the settings form
//echo block_risk_monitor_get_tabs_html($userid, true);
echo block_risk_monitor_get_top_tabs('settings', $courseid);

echo $body;

echo $OUTPUT->footer();