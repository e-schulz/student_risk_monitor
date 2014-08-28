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

global $block_risk_monitor_block, $DB;

//$DB->delete_records('block_risk_monitor_course', array('blockid' => $block_risk_monitor_block->id));

//Teacher must be logged in
require_login();

//Get the ID of the teacher
$userid = required_param('userid', PARAM_INT);
$ruleid = required_param('ruleid', PARAM_INT);
$message = optional_param('message', -1, PARAM_INT);
$weighting_description = optional_param('weightingdesc', -1, PARAM_INT);
$custom_rule = optional_param('custom', -1, PARAM_INT);

//Error- there is no user associated with the passed param
if (!$getuser = $DB->get_record('user', array('id' => $userid))) {
    print_error('no_user', 'block_risk_monitor', '', $userid);
}

//Error - the user trying to access this instance is the wrong one
if (!($USER->id == $userid)) {
    print_error('wrong_user', 'block_risk_monitor', '', $userid);
}
        
//Check that the rule exists.
if(!$getrule = $DB->get_record('block_risk_monitor_rule_inst', array('id' => $ruleid))) {
    print_error('no_rule', 'block_risk_monitor', '', $ruleid);
}

$getcategory = $DB->get_record('block_risk_monitor_category', array('id' => $getrule->categoryid));
$context = context_user::instance($userid);

//Set the page parameters
$blockname = get_string('pluginname', 'block_risk_monitor');
$header = get_string('settings', 'block_risk_monitor');

$PAGE->navbar->add($blockname);
$PAGE->navbar->add($header);

$PAGE->set_context($context);
$PAGE->set_title($blockname . ': '. $header);
$PAGE->set_heading($blockname . ': '.$header);
$PAGE->set_url('/blocks/risk_monitor/edit_rule.php?userid='.$userid.'&ruleid='.$ruleid);
$PAGE->set_pagetype($blockname);
$PAGE->set_pagelayout('standard');

//Create the body
$body = '';
if($message != -1) {
    switch($message){
        CASE 1:
            $body .= get_string('errweightingnotnumeric','block_risk_monitor');
            break;
        CASE 2: 
            $body .= get_string('errweightingnotinrange','block_risk_monitor');
            break;
        CASE 3:
            $body .= "Error: value must be numeric";
            break;
        CASE 4: 
            $body .= "Error: value must be a positive number.";
            break;
        CASE 5:
            $body .= "Error: must insert a value";
            break;
        default:
            break;
    }
}

//Create the form
 $edit_rule_form = new individual_settings_form_edit_rule('edit_rule.php?userid='.$USER->id.'&ruleid='.$ruleid.'&custom='.$custom_rule, array('ruleid' => $ruleid, 'weightingdesc' => $weighting_description, 'categoryname' => $getcategory->name, 'custom' => $custom_rule));    

//On submit
if ($fromform = $edit_rule_form->get_data()) {
    
    //If they want to view weighting description
    if(isset($fromform->submit_get_weighting_description)) {
        redirect(new moodle_url('edit_rule.php', array('userid' => $USER->id, 'ruleid' => $ruleid, 'weightingdesc' => 1)));
    }
    
    //Error checking
    //if weighting is not numeric, refresh with error
    if(!is_numeric($fromform->weighting_text)) {
        redirect(new moodle_url('edit_rule.php', array('userid' => $USER->id, 'ruleid' => $ruleid, 'message' => 1)));
    }
    else if(intval($fromform->weighting_text < 0 || $fromform->weighting_text > 100)) {
        redirect(new moodle_url('edit_rule.php', array('userid' => $USER->id, 'ruleid' => $ruleid, 'message' => 2)));        
    }
    
    if($custom_rule == -1) {
        if(!is_numeric($fromform->value_text)) {
            redirect(new moodle_url('edit_rule.php', array('userid' => $USER->id, 'ruleid' => $ruleid, 'message' => 3)));
        }
        else if(intval($fromform->value_text < 0)) {
            redirect(new moodle_url('edit_rule.php', array('userid' => $USER->id, 'ruleid' => $ruleid, 'message' => 4)));        
        }

        if(empty($fromform->value_text)) {
            redirect(new moodle_url('edit_rule.php', array('userid' => $USER->id, 'ruleid' => $ruleid, 'message' => 5)));
        }
    }
    
    $weighting_value = $fromform->weighting_text;
   //if no weighing given, default to 0
    if(empty($fromform->weighting_text)) {
        $weighting_value = 0;
    }
    else {
       $weighting_value = $fromform->weighting_text;      
    }
    
    //Adjust the current weightings
    block_risk_monitor_adjust_weightings_rule_added($getcategory->id, (100-floatval($weighting_value)), $ruleid);
    
    //Edit the rule
    $edited_rule = new object();
    $edited_rule->id = $ruleid;
    $edited_rule->weighting = $weighting_value;
    if($custom_rule == -1) {
        $edited_rule->value = $fromform->value_text;
    }
    $edited_rule->timestamp = time();
    
    //add to DB
    $DB->update_record('block_risk_monitor_rule_inst', $edited_rule);
        
    //Redirect to categories+rules
    redirect(new moodle_url('edit_categories_rules.php', array('userid' => $USER->id)));
}

//Render the HTML
echo $OUTPUT->header();
echo $OUTPUT->heading($blockname);


//echo html_writer::start_tag('div', array('class' => 'no-overflow'));

//display the settings form
//echo block_risk_monitor_get_tabs_html($userid, true);
echo block_risk_monitor_get_top_tabs('settings');
echo $OUTPUT->heading("Edit Rule");
/*if($message) {
    echo $message;
}*/
    
echo $body;
$edit_rule_form->display();
echo $OUTPUT->footer();