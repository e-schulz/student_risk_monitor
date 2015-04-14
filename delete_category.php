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

//$DB->delete_records('block_risk_monitor_course', array('blockid' => $block_risk_monitor_block->id));

//Teacher must be logged in
require_login();

//Get the ID of the teacher
$userid = required_param('userid', PARAM_INT);
$courseid = required_param('courseid', PARAM_INT);
$categoryid = required_param('categoryid', PARAM_INT);

//Error- there is no user associated with the passed param
if (!$getuser = $DB->get_record('user', array('id' => $userid))) {
    print_error('no_user', 'block_risk_monitor', '', $userid);
}

//Error - the user trying to access this instance is the wrong one
if (!($USER->id == $userid)) {
    print_error('wrong_user', 'block_risk_monitor', '', $userid);
}

if ($categoryid == -1) {
    print_error('nothing_to_delete', 'block_risk_monitor', '', $userid);
}

$context = context_user::instance($userid);
$getcategory = $DB->get_record('block_risk_monitor_category', array('id'=>$categoryid));

//Set the page parameters
$blockname = get_string('pluginname', 'block_risk_monitor');
$header = get_string('settings', 'block_risk_monitor'); $action = new moodle_url('individual_settings.php', array('userid' => $USER->id, 'courseid' => $courseid));

$PAGE->navbar->add($blockname, new moodle_url('overview.php', array('userid' => $USER->id, 'courseid' => $courseid))); 
$PAGE->navbar->add($header, $action); 

$PAGE->set_context($context);
$PAGE->set_title($blockname . ': '. $header);
$PAGE->set_heading($blockname . ': '.$header);
$PAGE->set_url('/blocks/risk_monitor/delete_item.php?userid='.$userid.'&courseid='.$courseid);
$PAGE->set_pagetype($blockname);
$PAGE->set_pagelayout('standard');

$delete_form = new individual_settings_form_delete_item('delete_category.php?userid='.$USER->id.'&courseid='.$courseid.'&categoryid='.$categoryid);     

if($delete_form->is_cancelled()) {
    redirect(new moodle_url('edit_categories_rules.php', array('userid' => $USER->id, 'courseid' => $courseid)));    
}

if ($fromform = $delete_form->get_data()) {
    
    //Delete category
    if($DB->record_exists('block_risk_monitor_category', array('id' => $categoryid))) {
        $DB->delete_records('block_risk_monitor_category', array('id' => $categoryid));
    }    
    
    //Delete all rules associated with a category
    if($DB->record_exists('block_risk_monitor_rule_inst', array('categoryid' => $categoryid))) {
        $rule_insts = $DB->get_records('block_risk_monitor_rule_inst', array('categoryid' => $categoryid));
        foreach($rule_insts as $rule_inst) {
            
           if($DB->record_exists('block_risk_monitor_rule_risk', array('ruleid' => $rule_inst->id))) {
                $DB->delete_records('block_risk_monitor_rule_risk', array('ruleid' => $rule_inst->id)); 
            }
        }
        $DB->delete_records('block_risk_monitor_rule_inst', array('categoryid' => $categoryid));        
    }

    //Delete all risks for this category
    if($DB->record_exists('block_risk_monitor_cat_risk', array('categoryid' => $categoryid))) {
        $DB->delete_records('block_risk_monitor_cat_risk', array('categoryid' => $categoryid)); 
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
echo $OUTPUT->heading("Delete ".$getcategory->name);
echo $OUTPUT->box_start();
echo "<b>Are you sure you want to delete this category?</b><br><br>";
echo "All student risk data and intervention templates for this category will also be erased.";
$delete_form->display();
echo $OUTPUT->box_end();
echo $OUTPUT->footer();