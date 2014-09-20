<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

defined('MOODLE_INTERNAL') || die();
global $CFG;
require_once($CFG->dirroot."/config.php");
require_once("rules.php");
require_once("riskslib.php");
require_once("rulelib.php");

global $DB, $USER, $COURSE;

//this method will check any questions that need to be answered.
function block_risk_monitor_generate_student_view($userid, $courseid) {
    
    global $CFG, $USER, $COURSE, $DB;
    
    if(count($questionnaires = block_risk_monitor_get_questionnaires($userid, $courseid)) !== 0) {
        $content = '<b>Questionnaires:</b><br>';
        foreach($questionnaires as $questionnaire) {
            if($questionnaire->title != null) {
                $title = $questionnaire->title;
            }
            else {
                $title = "Questionnaire";
            }
            $content .= html_writer::link(new moodle_url('/blocks/risk_monitor/student_questions.php', array('userid' => $USER->id, 'courseid' => $COURSE->id, 'questionnaireid' => $questionnaire->id)), $title."<br>");
        }
        $content .= "<br>";
    }
    
    if(count($interventions = block_risk_monitor_get_interventions($userid, $courseid)) !== 0) {
        $content .= '<b>Helpful resources:</b><br>';
        foreach($interventions as $intervention) {
            $intervention_template = $DB->get_record('block_risk_monitor_int_tmp', array('id' => $intervention->interventiontemplateid));
            $content .= html_writer::link(new moodle_url('/blocks/risk_monitor/student_module.php', array('userid' => $USER->id, 'courseid' => $COURSE->id, 'interventionid' => $intervention_template->id)), $intervention_template->title."<br>");
        }
    }
    
    return $content;
}

function block_risk_monitor_get_interventions($userid, $courseid) {
    global $DB;
    $interventions_to_return = array(); 
    
    if($categories = $DB->get_records('block_risk_monitor_category', array('courseid' => $courseid))) {
        foreach($categories as $category) {
            
            if($interventions = $DB->get_records('block_risk_monitor_int_inst', array('studentid' => $userid))) {
                
                foreach($interventions as $intervention) {
                    if($DB->record_exists('block_risk_monitor_int_tmp', array('categoryid' => $category->id, 'id' => $intervention->interventiontemplateid))) {
                        $interventions_to_return[] = $intervention;
                    }
                }
            }
        }
    }
    return $interventions_to_return;
}

function block_risk_monitor_get_questionnaires($userid, $courseid) {
    global $DB;
    $questionnaires_to_return = array();
    
    //Get the categories for this course
    if($categories = $DB->get_records('block_risk_monitor_category', array('courseid' => $courseid))) {
        foreach($categories as $category) {
            
            //Get the questionnaires for this category.
            if($rules = $DB->get_records('block_risk_monitor_rule_inst', array('categoryid' => $category->id, 'ruletype' => 2))) {
                
                //Questionnaires combined weighting
                $questionnaires_weighting = 0;
                foreach($rules as $rule) {
                    $questionnaires_weighting += $rule->weighting;
                }
                
                //Only show the questionnaires if other rules have been broken
                //get the other rules
                $other_rules = $DB->get_records('block_risk_monitor_rule_inst', array('categoryid' => $category->id, 'ruletype' => 1));
                //calculate the risk rating.
                $total_risk = 0;
                $other_rules_weighting = 100 - $questionnaires_weighting;
                foreach($other_rules as $other_rule) {
                    if($rule_risk = $DB->get_record('block_risk_monitor_rule_risk', array('ruleid' => $other_rule->id, 'userid' => $userid))) {
                        $total_risk += ($other_rule->weighting/$other_rules_weighting)*floatval($rule_risk->value);
                    }
                }
                
                //If risk is above moderate, show the questionnaires.
                if(count($other_rules) == 0 || $total_risk > QUESTIONNAIRE_RISK) {
                    //avoid doubles - in case same questionnaire is added to multiple categories
                    foreach($rules as $rule) {

                        $add_questionnaire = false;
                        if($questions = $DB->get_records('block_risk_monitor_question', array('custruleid' => $rule->custruleid))) {
                            foreach($questions as $question) {

                                if(!($DB->record_exists('block_risk_monitor_answer', array('questionid' => $question->id,  'userid' => $userid)))) {
                                    $add_questionnaire = true;
                                }
                            }
                        }

                        if(!array_key_exists($rule->custruleid, $questionnaires_to_return) && $add_questionnaire == true) {
                            $questionnaires_to_return[$rule->custruleid] = $DB->get_record('block_risk_monitor_cust_rule', array('id' => $rule->custruleid));
                        }
                    }
                }
            }
        }
    }
    
    return $questionnaires_to_return;
}

function block_risk_monitor_get_questions($questionnaireid, $userid) {
    
    global $DB;
    $questions_to_return = array();
    $question_total = 0;
    
    if($cust_rule = $DB->get_record('block_risk_monitor_cust_rule', array('id' => $questionnaireid))) {
                            
        if($questions = $DB->get_records('block_risk_monitor_question', array('custruleid' => $cust_rule->id))) {
            foreach($questions as $question) {
                                    
                if(!($DB->record_exists('block_risk_monitor_answer', array('questionid' => $question->id,  'userid' => $userid)))) {
                    $questions_to_return[] = $question;
                    $question_total++;
                }
            }
        }
    }
    
    return $questions_to_return;
}

function block_risk_monitor_get_top_tabs($currenttoptab, $courseid) {
    global $OUTPUT, $USER;
    
    $row = array();
    $row[] = new tabobject('overview',
                           new moodle_url('/blocks/risk_monitor/overview.php', array('userid' => $USER->id, 'courseid' => $courseid)),
                            get_string('overview', 'block_risk_monitor'));

    $row[] = new tabobject('settings',
                           new moodle_url('/blocks/risk_monitor/individual_settings.php', array('userid' => $USER->id, 'courseid' => $courseid)),
                           get_string('settings', 'block_risk_monitor'));

    return '<div class="topdisplay">'.$OUTPUT->tabtree($row, $currenttoptab).'</div>';
}

//Get all rules for a given category.
function block_risk_monitor_get_rules($categoryid) {
    
    global $DB;
    $rules = $DB->get_records('block_risk_monitor_rule_inst', array('categoryid' => $categoryid));
    return $rules;
}

//Get default rules for a given category.
function block_risk_monitor_get_default_rules($categoryid, $namesonly = false) {
    
    global $DB;
    $rules = $DB->get_records('block_risk_monitor_rule_inst', array('categoryid' => $categoryid, 'ruletype' => 1));
    
    if($namesonly == false) {
        return $rules;
    }
}


//Get custom rules for a given category.
function block_risk_monitor_get_custom_rules($categoryid) {
    
    global $DB;
    $rules = $DB->get_records('block_risk_monitor_rule_inst', array('categoryid' => $categoryid, 'ruletype' => 2));
    return $rules;
}

//returns an array of all the default rules that arent yet added to the category
//returns array where key = ruleid, value = names
function block_risk_monitor_get_unregistered_default_rule_names($categoryid) {
    
    global $DB;
    
    //Get the default rules
    //$default_rules = $DB->get_records('block_risk_monitor_rule_inst_type', array('custom' => 0, 'enabled' => 1));
    $default_rules = DefaultRules::getDefaultRuleObjects();
    
    //Get the registered rules
    $registered_rules = block_risk_monitor_get_default_rules($categoryid);
    
    $unregistered_defaults = array();
    while($default_rule = current($default_rules)) {
        $found = false;
        foreach($registered_rules as $registered_rule) {
            if(intval($registered_rule->defaultruleid) == intval($default_rule->id)) {
                $found = true;
            }
        }
        if ($found == false) {
            $unregistered_defaults[$default_rule->id] = $default_rule->name;
        }
        next($default_rules);
    }
    return $unregistered_defaults;
}

//Returns all custom rules that aren't already in the category.
function block_risk_monitor_get_unregistered_custom_rule_names($categoryid) {
    
    global $DB;
    
    //Get the default rules
    //$default_rules = $DB->get_records('block_risk_monitor_rule_inst_type', array('custom' => 0, 'enabled' => 1));
    $custom_rules = $DB->get_records('block_risk_monitor_cust_rule');
    
    //Get the registered rules
    $registered_rules = block_risk_monitor_get_custom_rules($categoryid);
    
    $unregistered_customs = array();
    while($custom_rule = current($custom_rules)) {
        $found = false;
        foreach($registered_rules as $registered_rule) {
            if(intval($registered_rule->custruleid) == intval($custom_rule->id)) {
                $found = true;
            }
        }
        if ($found == false) {
            $unregistered_customs[$custom_rule->id] = $custom_rule->name;
        }
        next($custom_rules);
    }
    return $unregistered_customs;
}

//Goes through existing rules and creates new weightings in order to accommodate for a new or edited rule
//Sum = 100% minus the specified weighting of the new rule
//If ruleid given, means the rule already exists and must exclude it from our rearrangements
function block_risk_monitor_adjust_weightings_rule_added($categoryid, $newsum, $ruleid = -1) {
    
    global $DB;
    
    //Get the existing rules
    $registered_rules = block_risk_monitor_get_rules($categoryid);
    
    //Check the given rule exists
    if($ruleid !== -1) {
        if(!$DB->record_exists('block_risk_monitor_rule_inst', array('id' => $ruleid))) {
            $ruleid = -1;
        }
        else {
            $rule = $DB->get_record('block_risk_monitor_rule_inst', array('id' => $ruleid));
        }
    }
    
    
    $rules_to_change = array();
    $previous_sum = 0;
    //Exclude the existing rule
    foreach($registered_rules as $registered_rule) {
            
        if(!($registered_rule->id == $ruleid)) {
            array_push($rules_to_change, $registered_rule);
        }
        $previous_sum += $registered_rule->weighting;
    }
 
    
    
    foreach($rules_to_change as $rule_to_change) {
        //Get the weighting
        $weighting_value = $rule_to_change->weighting;
        
        $new_weighting = ($weighting_value/$previous_sum) * $newsum;
        
        //Change in DB
        $new_record = new object();
        $new_record->id = $rule_to_change->id;
        $new_record->weighting = $new_weighting;
        $DB->update_record('block_risk_monitor_rule_inst', $new_record);
    }
    
}

//A rule has just been deleted from this category. old_sum = 100% minus the weighting of the deleted rule
function block_risk_monitor_adjust_weightings_rule_deleted($categoryid, $old_sum) {
    
    global $DB;
    
    //Get all the rules
    $registered_rules = block_risk_monitor_get_rules($categoryid);
    
       foreach($registered_rules as $registered_rule) {
        //Get the weighting
                $weighting_value = $registered_rule->weighting;

                $new_weighting = ($weighting_value/$old_sum) * 100;

                //Change in DB
                $new_record = new object();
                $new_record->id = $registered_rule->id;
                $new_record->weighting = $new_weighting;
                $DB->update_record('block_risk_monitor_rule_inst', $new_record);
    }
}

function block_risk_monitor_fix_url($url) {
    
    $url = trim($url);

    // remove encoded entities - we want the raw URI here
    $url = html_entity_decode($url, ENT_QUOTES, 'UTF-8');

    if (!preg_match('|^[a-z]+:|i', $url) and !preg_match('|^/|', $url)) {
        // invalid URI, try to fix it by making it normal URL,
        // please note relative urls are not allowed, /xx/yy links are ok
        $url = 'http://'.$url;
    }

    return $url;    
}

function block_risk_monitor_get_enrolled_students($courseid) {
    global $DB;

     $enrolled_students = array();

    //Get context records where context is course and instanceid = courseid
    if($context_records = $DB->get_records('context', array('contextlevel' => 50, 'instanceid' => $courseid))) {

        foreach($context_records as $context_record) {

            //Get role assignments where contextid = as given and roleid = 5(student)
            if($role_assignments = $DB->get_records('role_assignments', array('roleid' => 5, 'contextid' => $context_record->id))) {

                foreach($role_assignments as $role_assignment) {

                    if($students = $DB->get_records('user', array('id' => $role_assignment->userid))) {
                        //array_push($enrolled_students, $student);
                        foreach($students as $student) {
                            $enrolled_students[] = $student;
                        }
                    }
                }
            }

        }

    }
    
    return $enrolled_students;
}

function block_risk_monitor_clear_all_tables() {
    global $DB;
    $DB->delete_records('block_risk_monitor_course');
    $DB->delete_records('block_risk_monitor_category');
    $DB->delete_records('block_risk_monitor_rule_risk');
    $DB->delete_records('block_risk_monitor_cat_risk');
    $DB->delete_records('block_risk_monitor_rule_inst');
    $DB->delete_records('block_risk_monitor_cust_rule');
    $DB->delete_records('block_risk_monitor_question');
    $DB->delete_records('block_risk_monitor_option');
    $DB->delete_records('block_risk_monitor_answer');
    $DB->delete_records('block_risk_monitor_int_inst');
    $DB->delete_records('block_risk_monitor_int_tmp');
}