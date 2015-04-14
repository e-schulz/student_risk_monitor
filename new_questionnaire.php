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
$categoryid = required_param('categoryid', PARAM_INT);
$page = optional_param('page', -1, PARAM_INT);
$questionnaireid = optional_param('questionnaireid', -1, PARAM_INT);
$scoring_method = optional_param('scoringmethod', -1, PARAM_INT);

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


//Add new or existing links.

$PAGE->navbar->add($blockname, new moodle_url('overview.php', array('userid' => $USER->id, 'courseid' => $courseid)));
$PAGE->navbar->add($header, $action); 

$PAGE->set_context($context);
$PAGE->set_title($blockname . ': '. $header);
$PAGE->set_heading($blockname . ': '.$header);
$PAGE->set_url('/blocks/risk_monitor/new_questionnaire.php?userid='.$userid.'&courseid='.$courseid);
$PAGE->set_pagetype($blockname);
$PAGE->set_pagelayout('standard');

if($page == -1 || $page == 1) {
   $questionnaire_form =  new individual_settings_form_create_questionnaire_general_page('new_questionnaire.php?userid='.$userid.'&courseid='.$courseid."&categoryid=".$categoryid."&page=".$page."&questionnaireid=".$questionnaireid."&scoring_method=".$scoring_method);
}
else if($page == 2) {
    $questionnaire_form =  new individual_settings_form_create_questionnaire_question_page('new_questionnaire.php?userid='.$userid.'&courseid='.$courseid."&categoryid=".$categoryid."&scoringmethod=".$scoring_method."&questionnaireid=".$questionnaireid."&page=".$page, array('scoringmethod' => $scoring_method));
}
else if($page == 3) {
    $all_questions = $DB->get_records('block_risk_monitor_question', array('custruleid' => $questionnaireid));
    $min_total = $max_total = $total_questions = 0;
    foreach($all_questions as $question) {
        $total_questions++;
        $options = $DB->get_records('block_risk_monitor_option', array('questionid' => $question->id));
        $values = array();
        foreach($options as $option) {
            $values[] = $option->value;
        }
        $min_total += min($values);
        $max_total += max($values);
    }    
    $new_rule = new object();
    $new_rule->id = $questionnaireid;
    $new_rule->min_score = $min_total;
    $new_rule->max_score = $max_total;    
    $DB->update_record('block_risk_monitor_cust_rule', $new_rule);  
    $questionnaire_form =  new individual_settings_form_create_questionnaire_final_page('new_questionnaire.php?userid='.$userid.'&courseid='.$courseid."&categoryid=".$categoryid."&scoringmethod=".$scoring_method."&questionnaireid=".$questionnaireid."&page=".$page, array('minscore' => $min_total, 'maxscore' => $max_total, 'totalquestions' => $total_questions));
}
$heading = "New questionnaire";


if($questionnaire_form->is_cancelled()) {
    if($questionnaireid != -1) {
        if($DB->record_exists('block_risk_monitor_cust_rule', array('id' => $questionnaireid))) {
            $DB->delete_records('block_risk_monitor_cust_rule', array('id' => $questionnaireid));
        }
        
        if($questions = $DB->get_records('block_risk_monitor_question', array('custruleid' => $questionnaireid))) {
            foreach($questions as $question) {
                if($DB->record_exists('block_risk_monitor_option', array('questionid' => $question->id))) {
                    $DB->delete_records('block_risk_monitor_option', array('questionid' => $question->id));
                }
                $DB->delete_records('block_risk_monitor_question', array('custruleid' => $questionnaireid));
            }
        }
    }
    redirect(new moodle_url('edit_categories_rules.php', array('userid' => $USER->id, 'courseid' => $courseid))); 
}
else if ($fromform = $questionnaire_form->get_data()) {
    
    if($page == -1 || $page == 1) {
     $course_context = context_course::instance($courseid);
       
        /*$new_rule = new object();
        $new_rule->name = $fromform->rule_name_text;
        $new_rule->description = $fromform->rule_description_text;*/
        $scoring_method = $fromform->scoring_method;
        unset($fromform->scoring_method);
        $fromform->userid = $userid;        
        $fromform->instructionsformat = FORMAT_HTML;
        $fromform->min_score = 0;
        $fromform->max_score = 100;
        $fromform->low_risk_floor = 0;
        $fromform->low_risk_ceiling = MODERATE_RISK-1;
        $fromform->med_risk_floor = MODERATE_RISK;
        $fromform->med_risk_ceiling = HIGH_RISK-1;
        $fromform->high_risk_floor = HIGH_RISK;
        $fromform->high_risk_ceiling = 100; 
        $fromform->timestamp = time();
        
                $fromform = file_postupdate_standard_editor($fromform, 'instructions', array(), $course_context,
                                        'block_risk_monitor', 'intervention_instructions');   
        $new_rule_id = $DB->insert_record('block_risk_monitor_cust_rule', $fromform);    
             
        redirect(new moodle_url('new_questionnaire.php', array('userid' => $USER->id, 'courseid' => $courseid, 'page' => 2, 'categoryid' => $categoryid, 'questionnaireid' => $new_rule_id, 'scoringmethod' => $scoring_method))); 
    }
    else if($page == 2) {
            //Create the question
            $new_question = new object();
            if($fromform->question_text != '') {
                $new_question->question = $fromform->question_text;
                $new_question->custruleid = $questionnaireid;
                $new_question_id = $DB->insert_record('block_risk_monitor_question', $new_question);

                //Create the options
                for($j=0; $j<5; $j++) {
                    $text_identifier = 'option_text'.$j;
                    $value_identifier = 'option_value'.$j;
                    if($fromform->$text_identifier != "") {
                        $new_option1 = new object();
                        $new_option1->label = $fromform->$text_identifier;
                        if($scoring_method == 0) {
                           if($fromform->$value_identifier == 0) {
                                $value = 0;
                            }
                            else if($fromform->$value_identifier == 1) {
                                $value = MODERATE_RISK;
                            }
                            else if ($fromform->$value_identifier == 2) {
                                $value = 100;
                            }
                            $new_option1->value = $value;
                        }
                        else {
                            $new_option1->value = $fromform->$value_identifier;
                        }
                        $new_option1->questionid = $new_question_id;
                        $DB->insert_record('block_risk_monitor_option', $new_option1);
                    }
                }
            }
            
            if(isset($fromform->submit_another)) {
                redirect(new moodle_url('new_questionnaire.php', array('userid' => $USER->id, 'courseid' => $courseid, 'page' => 2, 'categoryid' => $categoryid, 'questionnaireid' => $questionnaireid, 'scoringmethod' => $scoring_method)));            
            }
            else if(isset($fromform->submit_save) && $scoring_method == 1) {
                redirect(new moodle_url('new_questionnaire.php', array('userid' => $USER->id, 'courseid' => $courseid, 'page' => 3, 'categoryid' => $categoryid, 'questionnaireid' => $questionnaireid, 'scoringmethod' => $scoring_method)));                            
            }
            else if(isset($fromform->submit_save)) {
                $custom_rule = $DB->get_record('block_risk_monitor_cust_rule', array('id' => $questionnaireid));
                $all_questions = $DB->get_records('block_risk_monitor_question', array('custruleid' => $questionnaireid));
                $total_questions = count($all_questions);
                $min_total = $max_total = 0;
                foreach($all_questions as $question) {
                    $options = $DB->get_records('block_risk_monitor_option', array('questionid' => $question->id));
                    $values = array();
                    foreach($options as $option) {
                        $values[] = $option->value;
                    }
                    $min_total += min($values);
                    $max_total += max($values);
                }    
                $new_rule = new object();
                $new_rule->id = $questionnaireid;
                $new_rule->min_score = $min_total;
                $new_rule->max_score = $max_total;        
                $new_rule->low_risk_floor = 0;
                $new_rule->low_risk_ceiling = (MODERATE_RISK*$total_questions)-1;
                $new_rule->med_risk_floor = MODERATE_RISK*$total_questions;
                $new_rule->med_risk_ceiling = (HIGH_RISK*$total_questions)-1;
                $new_rule->high_risk_floor = HIGH_RISK*$total_questions;
                $new_rule->high_risk_ceiling = 100*$total_questions;
                $DB->update_record('block_risk_monitor_cust_rule', $new_rule); 
                $total_rules = count(block_risk_monitor_get_rules(intval($categoryid)))+1;
                $weighting_default = 100/intval($total_rules);
                block_risk_monitor_adjust_weightings_rule_added($categoryid, (100-floatval($weighting_default)));
                
                $rule_inst = new object();
                $rule_inst->name = $custom_rule->name;
                $rule_inst->description = $custom_rule->description;
                $rule_inst->weighting = $weighting_default;        
                $rule_inst->timestamp = time();
                $rule_inst->categoryid = $categoryid;
                $rule_inst->ruletype = 2;
                $rule_inst->custruleid = $custom_rule->id;       
                $new_rule_id = $DB->insert_record('block_risk_monitor_rule_inst', $rule_inst);                   
                redirect(new moodle_url('edit_categories_rules.php', array('userid' => $USER->id, 'courseid' => $courseid)));                 
            }
    }
    else if($page == 3) {
        $new_rule = new object();
        $new_rule->id = $questionnaireid;
        $new_rule->low_risk_floor = $fromform->lowrangebegin;
        $new_rule->low_risk_ceiling = $fromform->lowrangeend;
        $new_rule->med_risk_floor = $fromform->medrangebegin;
        $new_rule->med_risk_ceiling = $fromform->medrangeend;
        $new_rule->high_risk_floor = $fromform->highrangebegin;
        $new_rule->high_risk_ceiling = $fromform->highrangeend;
        $DB->update_record('block_risk_monitor_cust_rule', $new_rule);  
        $custom_rule = $DB->get_record('block_risk_monitor_cust_rule', array('id' => $questionnaireid));
        
        $total_rules = count(block_risk_monitor_get_rules(intval($categoryid)))+1;
        $weighting_default = 100/intval($total_rules);
        block_risk_monitor_adjust_weightings_rule_added($categoryid, (100-floatval($weighting_default)));
                
        $rule_inst = new object();
        $rule_inst->name = $custom_rule->name;
        $rule_inst->description = $custom_rule->description;
        $rule_inst->weighting = $weighting_default;        
        $rule_inst->timestamp = time();
        $rule_inst->categoryid = $categoryid;
        $rule_inst->ruletype = 2;
        $rule_inst->custruleid = $custom_rule->id;       
        $DB->insert_record('block_risk_monitor_rule_inst', $rule_inst);                           
        redirect(new moodle_url('edit_categories_rules.php', array('userid' => $USER->id, 'courseid' => $courseid)));      
    }
}


echo $OUTPUT->header();
echo $OUTPUT->heading($blockname);

echo block_risk_monitor_get_top_tabs('settings', $courseid);
echo $OUTPUT->heading($heading);

$questionnaire_form->display();

if($page == 2 || $page == 3) {
    //Display a preview.
    echo $OUTPUT->heading("Questionnaire preview");
    if($questions = $DB->get_records('block_risk_monitor_question', array('custruleid' => $questionnaireid))) {
        foreach($questions as $question) {
            echo $OUTPUT->box_start();
            echo $question->question."<br>";
            $options = $DB->get_records('block_risk_monitor_option', array('questionid' => $question->id));
            foreach($options as $option) {
                echo "<li>".$option->label."</li>";
            }
            echo "<br>";
            echo $OUTPUT->box_end();
        }
    }
}
//echo $back_to_categories;
echo $OUTPUT->footer();