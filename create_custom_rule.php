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

//Teacher must be logged in
require_login();

//Get the ID of the teacher
$userid = required_param('userid', PARAM_INT);

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
$header = get_string('settings', 'block_risk_monitor');

$PAGE->navbar->add($blockname);
$PAGE->navbar->add($header);

$PAGE->set_context($context);
$PAGE->set_title($blockname . ': '. $header);
$PAGE->set_heading($blockname . ': '.$header);
$PAGE->set_url('/blocks/risk_monitor/create_custom_rule.php?userid='.$userid);
$PAGE->set_pagetype($blockname);
$PAGE->set_pagelayout('standard');

//Get all the categories and courses.
$new_custom_rule_form = new individual_settings_form_create_custom_rule('create_custom_rule.php?userid='.$userid); 
 
if($new_custom_rule_form->is_cancelled()) {
    redirect(new moodle_url('view_custom_rules.php', array('userid' => $USER->id))); 
}
else if ($fromform = $new_custom_rule_form->get_data()) {
    
    //Create the rule
    $new_rule = new object();
    $new_rule->name = $fromform->rule_name_text;
    $new_rule->description = $fromform->rule_description_text;
    $new_rule->userid = $userid;
    $new_rule->timestamp = time();
    $new_rule_id = $DB->insert_record('block_risk_monitor_cust_rule', $new_rule);
    
    //Create the question
    $new_question = new object();
    $new_question->question = $fromform->question_text;
    $new_question->custruleid = $new_rule_id;
    $new_question_id = $DB->insert_record('block_risk_monitor_question', $new_question);
    
    //Create the options
    $new_option1 = new object();
    $new_option1->label = $fromform->option1_text;
    $new_option1->value = $fromform->option1_value;
    $new_option1->questionid = $new_question_id;
    $DB->insert_record('block_risk_monitor_option', $new_option1);
    
    $new_option2 = new object();
    $new_option2->label = $fromform->option2_text;
    $new_option2->value = $fromform->option2_value;
    $new_option2->questionid = $new_question_id;
    $DB->insert_record('block_risk_monitor_option', $new_option2);
    
    if(!empty($fromform->option3_text)) {
        $new_option3 = new object();
        $new_option3->label = $fromform->option3_text;
        $new_option3->value = $fromform->option3_value;
        $new_option3->questionid = $new_question_id;
        $DB->insert_record('block_risk_monitor_option', $new_option3);
    }
    
    redirect(new moodle_url('view_custom_rules.php', array('userid' => $USER->id))); 
}


echo $OUTPUT->header();
echo $OUTPUT->heading($blockname);

echo block_risk_monitor_get_top_tabs('settings');
echo $OUTPUT->heading("New custom rule");

$new_custom_rule_form->display();
    
//echo $back_to_categories;
echo $OUTPUT->footer();