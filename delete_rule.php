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
$ruleid = required_param('ruleid', PARAM_INT);

//Error- there is no user associated with the passed param
if (!$getuser = $DB->get_record('user', array('id' => $userid))) {
    print_error('no_user', 'block_risk_monitor', '', $userid);
}

//Error - the user trying to access this instance is the wrong one
if (!($USER->id == $userid)) {
    print_error('wrong_user', 'block_risk_monitor', '', $userid);
}

if ($ruleid == -1) {
    print_error('nothing_to_delete', 'block_risk_monitor', '', $userid);
}

$context = context_user::instance($userid);
$getrule = $DB->get_record('block_risk_monitor_rule_inst', array('id'=>$ruleid));

//Set the page parameters
$blockname = get_string('pluginname', 'block_risk_monitor');
$header = get_string('settings', 'block_risk_monitor'); $action = new moodle_url('individual_settings.php', array('userid' => $USER->id, 'courseid' => $courseid));

$PAGE->navbar->add($blockname, new moodle_url('overview.php', array('userid' => $USER->id, 'courseid' => $courseid))); 
$PAGE->navbar->add($header, $action); 

$PAGE->set_context($context);
$PAGE->set_title($blockname . ': '. $header);
$PAGE->set_heading($blockname . ': '.$header);
$PAGE->set_url('/blocks/risk_monitor/delete_rule.php?userid='.$userid.'&courseid='.$courseid);
$PAGE->set_pagetype($blockname);
$PAGE->set_pagelayout('standard');

$delete_form = new individual_settings_form_delete_item('delete_rule.php?userid='.$USER->id.'&courseid='.$courseid.'&ruleid='.$ruleid);     

if($delete_form->is_cancelled()) {
    redirect(new moodle_url('edit_categories_rules.php', array('userid' => $USER->id, 'courseid' => $courseid)));    
}

if ($fromform = $delete_form->get_data()) {
    
    //Delete record and readjust weightings.
    if($rule_to_delete = $DB->get_record('block_risk_monitor_rule_inst', array('id' => $ruleid))) {
        $rules = block_risk_monitor_get_rules($rule_to_delete->categoryid);
        $old_sum = 0;
        foreach($rules as $rule) {
            $old_sum += $rule->weighting;
        }
        $old_sum = $old_sum - intval($rule_to_delete->weighting);
        $DB->delete_records('block_risk_monitor_rule_inst', array('id' => $ruleid));
        block_risk_monitor_adjust_weightings_rule_deleted($rule_to_delete->categoryid, $old_sum);    

        if($DB->record_exists('block_risk_monitor_rule_risk', array('ruleid' => $rule_to_delete->id))) {
            $DB->delete_records('block_risk_monitor_rule_risk', array('ruleid' => $rule_to_delete->id));        
        }          
    }
    
    //Redirect to categories+rules
    redirect(new moodle_url('edit_categories_rules.php', array('userid' => $USER->id, 'courseid' => $courseid)));
}

//Render the HTML
echo $OUTPUT->header();
echo $OUTPUT->heading($blockname);


//echo html_writer::start_tag('div', array('class' => 'no-overflow'));

//display the settings form
//echo block_risk_monitor_get_tabs_html($userid, true);
echo block_risk_monitor_get_top_tabs('settings', $courseid);
echo $OUTPUT->heading("Delete ".$getrule->name);
echo $OUTPUT->box_start();
echo "<b>Are you sure you want to delete this rule?</b><br><br>";
echo "All student risk data for this rule will also be erased.";
$delete_form->display();
echo $OUTPUT->box_end();
echo $OUTPUT->footer();