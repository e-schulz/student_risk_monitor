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
$categoryid = required_param('categoryid', PARAM_INT);
$message = optional_param('message', -1, PARAM_INT);
$rule_id = optional_param('rule_id', -1, PARAM_INT);
$weighting_description = optional_param('weightingdesc', -1, PARAM_INT);

//Error- there is no user associated with the passed param
if (!$getuser = $DB->get_record('user', array('id' => $userid))) {
    print_error('no_user', 'block_risk_monitor', '', $userid);
}

//Error - the user trying to access this instance is the wrong one
if (!($USER->id == $userid)) {
    print_error('wrong_user', 'block_risk_monitor', '', $userid);
}
        
//Check that the category exists.
if(!$getcategory = $DB->get_record('block_risk_monitor_category', array('id' => $categoryid))) {
    print_error('no_category', 'block_risk_monitor', '', $categoryid);
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
$PAGE->set_url('/blocks/risk_monitor/new_rule.php?userid='.$userid.'&categoryid='.$categoryid);
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
        default:
            break;
    }
}

//Create the form
$new_rule_form = new individual_settings_form_new_rule('new_rule.php?userid='.$USER->id.'&categoryid='.$categoryid, array('rule_id' => $rule_id, 'categoryid' => $categoryid, 'weightingdesc' => $weighting_description));     

//On submit
if ($fromform = $new_rule_form->get_data()) {
    
    //If they want to view description
    if(isset($fromform->submit_get_rule_description)) {
        redirect(new moodle_url('new_rule.php', array('userid' => $USER->id, 'categoryid' => $categoryid, 'rule_id' => $fromform->rule_id)));
    }
    else if(isset($fromform->submit_get_weighting_description)) {
        redirect(new moodle_url('new_rule.php', array('userid' => $USER->id, 'categoryid' => $categoryid, 'weightingdesc' => 1)));
    }
    
    //Error checking
    //if weighting is not numeric, refresh with error
    if(!is_numeric($fromform->weighting_text)) {
        redirect(new moodle_url('new_rule.php', array('userid' => $USER->id, 'categoryid' => $categoryid, 'message' => 1)));
    }
    else if(intval($fromform->weighting_text < 0 || $fromform->weighting_text > 100)) {
        redirect(new moodle_url('new_rule.php', array('userid' => $USER->id, 'categoryid' => $categoryid, 'message' => 2)));        
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
    block_risk_monitor_adjust_weightings($categoryid, (100-floatval($weighting_value)));
    
    //Create the rule
    $new_rule = new object();
    $new_rule->name = DefaultRules::$default_rule_names[$fromform->rule_id];
    $new_rule->description = DefaultRules::$default_rule_descriptions[$fromform->rule_id];
    $new_rule->weighting = $weighting_value;
    $new_rule->enabled = 1;
    $new_rule->categoryid = $categoryid;
    $new_rule->timestamp = time();
    
    //add to DB
    if (!$DB->insert_record('block_risk_monitor_rule', $new_rule)) {
        echo get_string('errorinsertrule', 'block_risk_monitor');
    }     
    
    //Adjust the other weightings so they all add to 100%
    
    //Redirect to categories+rules
    redirect(new moodle_url('edit_categories_rules.php', array('userid' => $USER->id, 'courseid' => $getcategory->courseid)));
}

//Render the HTML
echo $OUTPUT->header();
echo $OUTPUT->heading($blockname);


//echo html_writer::start_tag('div', array('class' => 'no-overflow'));

//display the settings form
//echo block_risk_monitor_get_tabs_html($userid, true);
echo block_risk_monitor_get_top_tabs('settings');
echo $OUTPUT->heading("New Rule");
/*if($message) {
    echo $message;
}*/
echo $body;
$new_rule_form->display();
echo $OUTPUT->footer();