<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

///REQUIRES AND ERROR MESSAGES

require_once("../../config.php");
require_once("locallib.php");
require_once("student_risk_monitor_forms.php");

global $DB;

//Teacher must be logged in
require_login();

//Get the ID of the teacher
$userid = required_param('userid', PARAM_INT);
$courseid = required_param('courseid', PARAM_INT);

$custom_rule_id = optional_param('custruleid', -1, PARAM_INT);
$do_view = optional_param('view', -1, PARAM_INT);
$do_delete = optional_param('delete', -1, PARAM_INT);


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
$PAGE->set_url('/blocks/risk_monitor/view_custom_rules.php?userid='.$USER->id.'&courseid='.$courseid);
$PAGE->set_pagetype($blockname);
$PAGE->set_pagelayout('standard');


//Delete stuff
if($do_delete !== -1) {
    
    if($DB->record_exists('block_risk_monitor_cust_rule', array('id' => $custom_rule_id))) {
        $DB->delete_records('block_risk_monitor_cust_rule', array('id' => $custom_rule_id));
    }
    
    if($questions = $DB->get_records('block_risk_monitor_question', array('custruleid' => $custom_rule_id))) {
        
        foreach($questions as $question) {
             if($questions = $DB->get_records('block_risk_monitor_option', array('questionid' => $question->id))) {
                 $DB->delete_records('block_risk_monitor_option', array('questionid' => $question->id));
             }
        }
        $DB->delete_records('block_risk_monitor_question', array('custruleid' => $custom_rule_id));
    }
}

$viewruleid = -1;
if($do_view !== -1) {
      $viewruleid = $custom_rule_id;
}

//show descriptions

$back_to_categories = html_writer::link (new moodle_url('edit_categories_rules.php', array('userid' => $USER->id, 'courseid' => $courseid)), get_string('back_to_categories','block_risk_monitor'));

//Get all the categories and courses.
$custom_rules_form = new individual_settings_form_view_custom_rules('view_custom_rules.php?userid='.$USER->id.'&courseid='.$courseid, array('courseid' => $courseid, 'viewruleid' => $viewruleid)); 
 
//Render the HTML
echo $OUTPUT->header();
echo $OUTPUT->heading($blockname);


//echo html_writer::start_tag('div', array('class' => 'no-overflow'));

//display the settings form
//echo block_risk_monitor_get_tabs_html($userid, true);
echo block_risk_monitor_get_top_tabs('settings', $courseid);
echo $OUTPUT->heading("Custom rules");

$custom_rules_form->display();

echo $back_to_categories;
//echo $back_to_categories;
echo $OUTPUT->footer();