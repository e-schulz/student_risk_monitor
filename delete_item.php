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

//$DB->delete_records('block_risk_monitor_course', array('blockid' => $block_risk_monitor_block->id));

//Teacher must be logged in
require_login();

//Get the ID of the teacher
$userid = required_param('userid', PARAM_INT);
$courseid = required_param('courseid', PARAM_INT);
$categoryid = optional_param('categoryid', -1, PARAM_INT);
$ruleid = optional_param('ruleid', -1, PARAM_INT);
$interventionid = optional_param('interventionid', -1, PARAM_INT);
$studentinterventionid = optional_param('studentinterventionid', -1, PARAM_INT);

//Error- there is no user associated with the passed param
if (!$getuser = $DB->get_record('user', array('id' => $userid))) {
    print_error('no_user', 'block_risk_monitor', '', $userid);
}

//Error - the user trying to access this instance is the wrong one
if (!($USER->id == $userid)) {
    print_error('wrong_user', 'block_risk_monitor', '', $userid);
}

if ($categoryid == -1 && $ruleid == -1 && $interventionid == -1 && $studentinterventionid == -1) {
    print_error('nothing_to_delete', 'block_risk_monitor', '', $userid);
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
$PAGE->set_url('/blocks/risk_monitor/delete_item.php?userid='.$userid.'&courseid='.$courseid);
$PAGE->set_pagetype($blockname);
$PAGE->set_pagelayout('standard');

//On submit
$delete_form = new individual_settings_form_delete_item('delete_item.php?userid='.$USER->id.'&courseid='.$courseid);     

if($new_rule_form->is_cancelled()) {
    //redirect(new moodle_url('edit_categories_rules.php', array('userid' => $USER->id, 'courseid' => $courseid/*, 'courseid' => $getcategory->courseid*/)));    
}

if ($fromform = $new_rule_form->get_data()) {
    
    //Redirect to categories+rules
    //redirect(new moodle_url('edit_categories_rules.php', array('userid' => $USER->id, 'courseid' => $courseid)));
}

//Render the HTML
echo $OUTPUT->header();
echo $OUTPUT->heading($blockname);


//echo html_writer::start_tag('div', array('class' => 'no-overflow'));

//display the settings form
//echo block_risk_monitor_get_tabs_html($userid, true);
echo block_risk_monitor_get_top_tabs('settings', $courseid);
echo $OUTPUT->heading("Delete item");
echo $OUTPUT->box_start();
$delete_form->display();
echo $OUTPUT->box_end();
echo $OUTPUT->footer();