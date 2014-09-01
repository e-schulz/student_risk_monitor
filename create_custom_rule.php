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
$num_questions = optional_param('numquestions', -1, PARAM_INT);
$num_options = optional_param('numoptions', -1, PARAM_INT);
$scoring_method = optional_param('scoringmethod', -1, PARAM_INT);
$rule_id = optional_param('ruleid', -1, PARAM_INT);

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
$PAGE->set_url('/blocks/risk_monitor/create_custom_rule.php?userid='.$userid.'&courseid='.$courseid);
$PAGE->set_pagetype($blockname);
$PAGE->set_pagelayout('standard');

/*for($i=5; $i<15; $i++) {
    $new_record = new object();
    $new_record->id = $i;
    $new_record->custruleid = 4;
    $DB->update_record('block_risk_monitor_question', $new_record);
}*/

        $new_rule = new object();
        $new_rule->id = 4;
        $new_rule->min_score = 0;
        $new_rule->max_score = 30;
        $new_rule->low_mod_risk_cutoff = 22;
        $new_rule->mod_high_risk_cutoff = 11;
        $DB->update_record('block_risk_monitor_cust_rule', $new_rule);


if($num_questions !== -1) {
    $heading = "Questions";
}
else {
    $heading = "New custom rule";
}
//Get all the categories and courses.
$new_custom_rule_form = new individual_settings_form_create_custom_rule('create_custom_rule.php?userid='.$userid.'&courseid='.$courseid.'&numquestions='.$num_questions.'&numoptions='.$num_options.'&scoringmethod='.$scoring_method.'&ruleid='.$rule_id, array('courseid' => $courseid, 'numquestions' => $num_questions, 'numoptions' => $num_options, 'scoringmethod' => $scoring_method, 'finalpage' => $final_page)); 
 
if($new_custom_rule_form->is_cancelled()) {
    if($rule_id !== -1) {
        $DB->delete_record('block_risk_monitor_cust_rule', array('id' => $rule_id));
    }
    redirect(new moodle_url('view_custom_rules.php', array('userid' => $USER->id, 'courseid' => $courseid))); 
}
else if ($fromform = $new_custom_rule_form->get_data()) {
    
    if(isset($fromform->submit_rule1)) {
        //Create the rule
        $new_rule = new object();
        $new_rule->name = $fromform->rule_name_text;
        $new_rule->description = $fromform->rule_description_text;
        $new_rule->userid = $userid;        
        $new_rule->min_score = 0;
        $new_rule->max_score = 100;
        $new_rule->low_mod_risk_cutoff = MODERATE_RISK;
        $new_rule->mod_high_risk_cutoff = HIGH_RISK;       
        $new_rule->timestamp = time();
        $new_rule_id = $DB->insert_record('block_risk_monitor_cust_rule', $new_rule);
            
        redirect(new moodle_url('create_custom_rule.php', array('userid' => $USER->id, 'courseid' => $courseid, 'numquestions' => $fromform->number_questions, 'numoptions' => $fromform->number_options, 'scoringmethod' => $fromform->scoring_method, 'ruleid' => $new_rule_id))); 
    }
    else {

    if($rule_id !== -1 && $scoring_method == 1) {
        $new_rule = new object();
        $new_rule->id = $rule_id;
        $new_rule->min_score = $fromform->min_score;
        $new_rule->max_score = $fromform->max_score;
        $new_rule->low_mod_risk_cutoff = $fromform->medrangebegin;
        $new_rule->mod_high_risk_cutoff = $fromform->highrangebegin;
        $DB->update_record('block_risk_monitor_cust_rule', $new_rule);
    }
            
        for($i=0; $i<$num_questions; $i++) {

            $question_identifier = 'question_text'.$i;
            //Create the question
            $new_question = new object();
            $new_question->question = $fromform->$question_identifier;
            $new_question->custruleid = $rule_id;
            $new_question_id = $DB->insert_record('block_risk_monitor_question', $new_question);

            //Create the options
            for($j=0; $j<$num_options; $j++) {
                $text_identifier = 'option_text'.$i.$j;
                $value_identifier = 'option_value'.$i.$j;
                $new_option1 = new object();
                $new_option1->label = $fromform->$text_identifier;
                $new_option1->value = $fromform->$value_identifier;
                $new_option1->questionid = $new_question_id;
                $DB->insert_record('block_risk_monitor_option', $new_option1);
            }
        }

        redirect(new moodle_url('view_custom_rules.php', array('userid' => $USER->id, 'courseid' => $courseid))); 
    }
}


echo $OUTPUT->header();
echo $OUTPUT->heading($blockname);

echo block_risk_monitor_get_top_tabs('settings', $courseid);
echo $OUTPUT->heading($heading);

$new_custom_rule_form->display();
    
//echo $back_to_categories;
echo $OUTPUT->footer();