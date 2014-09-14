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
$categoryid = required_param('categoryid', PARAM_INT);
$message = optional_param('message', -1, PARAM_INT);
$rule_id = optional_param('rule_id', -1, PARAM_INT);

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
$PAGE->set_url('/blocks/risk_monitor/new_rule.php?userid='.$userid.'&categoryid='.$categoryid.'&courseid='.$courseid);
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

/*if(intval($custom_rule) == -1) {
    $default_rule_link = get_string('default_rule', 'block_risk_monitor');
    $custom_rule_link = html_writer::link(new moodle_url('new_rule.php', array('userid' => $USER->id, 'categoryid' => $categoryid, 'custom' => 1, 'courseid' => $courseid)), get_string('custom_rule', 'block_risk_monitor'));
    $new_rule_form = new individual_settings_form_new_default_rule('new_rule.php?userid='.$USER->id.'&categoryid='.$categoryid.'&courseid='.$courseid, array('rule_id' => $rule_id, 'categoryid' => $categoryid, 'weightingdesc' => $weighting_description));     
}
else{
    $default_rule_link = html_writer::link(new moodle_url('new_rule.php', array('userid' => $USER->id, 'categoryid' => $categoryid, 'courseid' => $courseid)), get_string('default_rule', 'block_risk_monitor'));
    $custom_rule_link = get_string('custom_rule', 'block_risk_monitor');
    $new_rule_form = new individual_settings_form_new_custom_rule('new_rule.php?userid='.$USER->id.'&courseid='.$courseid.'&categoryid='.$categoryid.'&custom=1', array('rule_id' => $rule_id, 'categoryid' => $categoryid, 'weightingdesc' => $weighting_description));     
}

$rule_type_links = $default_rule_link."&nbsp;|&nbsp;".$custom_rule_link."<br><br>";*/

//On submit
    $new_rule_form = new individual_settings_form_new_default_rule('new_rule.php?userid='.$USER->id.'&categoryid='.$categoryid.'&courseid='.$courseid, array('ruleid' => $rule_id, 'categoryid' => $categoryid, 'weightingdesc' => -1));     

if($new_rule_form->is_cancelled()) {
    redirect(new moodle_url('edit_categories_rules.php', array('userid' => $USER->id, 'courseid' => $courseid/*, 'courseid' => $getcategory->courseid*/)));    
}

if ($fromform = $new_rule_form->get_data()) {
    
    if(!isset($fromform->add_rule)) {
        redirect(new moodle_url('new_rule.php', array('userid' => $USER->id, 'categoryid' => $categoryid, 'rule_id' => $fromform->rule_id, 'courseid' => $courseid)));
    }
    
    $new_rule = new object();
    
    //Error checking
    //if weighting is not numeric, refresh with error
    if(!is_numeric($fromform->weighting)) {
        redirect(new moodle_url('new_rule.php', array('userid' => $USER->id, 'categoryid' => $categoryid, 'message' => 1, 'ruleid' => $fromform->rule_id, 'courseid' => $courseid)));
    }
    else if(intval($fromform->weighting < 0 || $fromform->weighting > 100)) {
        redirect(new moodle_url('new_rule.php', array('userid' => $USER->id, 'categoryid' => $categoryid, 'message' => 2, 'ruleid' => $fromform->rule_id, 'courseid' => $courseid)));        
    }
    else {
        if(empty($fromform->weighting)) {
            $fromform->weighting = 0;
        }   
        block_risk_monitor_adjust_weightings_rule_added($categoryid, (100-floatval($fromform->weighting)));   
        $new_rule->weighting = $fromform->weighting;        
    }
    
    
    //Error checking: value
    if(DefaultRules::$default_rule_value_required[$fromform->rule_id] == 1) {
        
        if(!is_numeric($fromform->value)) {
            
            redirect(new moodle_url('new_rule.php', array('userid' => $USER->id, 'categoryid' => $categoryid, 'message' => 3, 'ruleid' => $fromform->rule_id, 'courseid' => $courseid)));
        }
        else if(intval($fromform->value < 0)) {
            redirect(new moodle_url('new_rule.php', array('userid' => $USER->id, 'categoryid' => $categoryid, 'message' => 4, 'ruleid' => $fromform->rule_id, 'courseid' => $courseid)));        
        }

        if(empty($fromform->value)) {
            redirect(new moodle_url('new_rule.php', array('userid' => $USER->id, 'categoryid' => $categoryid, 'message' => 5, 'ruleid' => $fromform->rule_id, 'courseid' => $courseid)));
        }
        $new_rule->value = $fromform->value;        
        $rule_name = DefaultRules::$default_rule_names[$fromform->rule_id];
        $rule_name = str_replace("a number of", $fromform->value, $rule_name);
        $rule_name = str_replace("certain cutoff", $fromform->value."%", $rule_name);
        $rule_name = str_replace("number of", $fromform->value, $rule_name);
        $new_rule->name = $rule_name;
    }
    else {
        $new_rule->name = DefaultRules::$default_rule_names[$fromform->rule_id];
    }
    
    $new_rule->description = DefaultRules::$default_rule_descriptions[$fromform->rule_id];//$rule_type->description;
    $new_rule->categoryid = $categoryid;
    $new_rule->timestamp = time();
    $new_rule->ruletype = 1;
    $new_rule->defaultruleid = $fromform->rule_id;
    
    //add to DB
    if (!$DB->insert_record('block_risk_monitor_rule_inst', $new_rule)) {
        echo get_string('errorinsertrule', 'block_risk_monitor');
    }     
    
    //Edit the category timestamp, to show a new rule has been added.
    $edited_category = new object();
    $edited_category->id = $categoryid;
    $edited_category->timestamp = time();
    $DB->update_record('block_risk_monitor_category', $edited_category);
        
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
echo $OUTPUT->heading("New Rule");
echo $body;
$new_rule_form->display();
echo $OUTPUT->footer();