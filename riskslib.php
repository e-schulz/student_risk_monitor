<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 * 
 * TODO- TEST EVERYTHING IN THIS FILE.
 */

defined('MOODLE_INTERNAL') || die();
require_once("locallib.php");

define("HIGH_RISK", 75);
define("MODERATE_RISK", 50);


//This is the method run with the cron() function. Updates the student risks.
function block_risk_monitor_calculate_risks() {
    
    
    global $DB;

    //For each course this block is added on.
    $return = '';
    
    if($courses = $DB->get_records('block_risk_monitor_course')) {
        
        foreach($courses as $course) {
            
            $return .= "<br>Course id: ".$course->id;
            
            $enrolled_students = block_risk_monitor_get_enrolled_students($course->courseid);
            foreach($enrolled_students as $enrolled_student) {
        
                $return .= "<br>Student id: ".$enrolled_student->id;
                $categories = $DB->get_records('block_risk_monitor_category', array('courseid' => $course->courseid));
                foreach($categories as $category) {
                    
                    $return .= "<br>Category id: ".$category->id;
                    $rules = $DB->get_records('block_risk_monitor_rule_inst', array('categoryid' => $category->id));
                    foreach($rules as $rule) {
                        
                        $create_risk_instance = false;
                        $risk_rating = 0;
                        if($rule->ruletype == 1) {
                            
                            $create_risk_instance = true;
                            $return .= "<br>Rule type: default";
                            $default_rule_id = $rule->defaultruleid;
                            $action = DefaultRules::$default_rule_actions[$default_rule_id];
                            
                            if(DefaultRules::$default_rule_value_required[$default_rule_id]) {
                                $value = $rule->value;
                            }
                            else {
                                $value = -1;
                            }
                            
                            //Determine the risk rating (between 0 and 100)
                            $risk_rating = block_risk_monitor_calculate_risk_rating($action, $enrolled_student, $value, $course->courseid);
                        }
                        
                        //Custom rule
                        else if ($rule->ruletype == 2) {
                            $total_score = 0;
                            $return .= "<br>Rule type = custom";
                            
                            //Get the custom rule
                            $custom_rule = $DB->get_record('block_risk_monitor_cust_rule', array('id' => $rule->custruleid));
                            
                            //Get min score, max score, mod_risk_floor, high_risk_floor.
                            $min_score = $custom_rule->min_score;
                            $max_score = $custom_rule->max_score;
                            $low_mod_risk_cutoff = $custom_rule->low_mod_risk_cutoff;
                            $mod_high_risk_cutoff = $custom_rule->mod_high_risk_cutoff;
                            
                            
                            
                            //Get the questions
                            if($questions = $DB->get_records('block_risk_monitor_question', array('custruleid' => $custom_rule->id))) {
                                $total_questions = count($questions);
                                
                                foreach($questions as $question) {
                                    
                                    //Check if an answer has been submitted
                                    if($answer = $DB->get_record('block_risk_monitor_answer', array('userid' => $enrolled_student->id, 'questionid' => $question->id))) {
                                        $return .= "<br>Answer id: ".$answer->id." Question id :".$question->id;
                                        //Get the value.
                                        if($option = $DB->get_record('block_risk_monitor_option', array('id' => $answer->optionid))) {
                                            $total_score += $option->value;
                                        }
                                        $create_risk_instance = true;
                                    }
                                }
                                //Normalise
                                $default_low_range = MODERATE_RISK;
                                $default_moderate_range = HIGH_RISK - MODERATE_RISK;
                                $default_high_range = 100 - HIGH_RISK;
                                
                                if($low_mod_risk_cutoff > $mod_high_risk_cutoff) {
                                    //Scoring is reversed - higher scores, lower risk.
                                    //Invert the scores
                                    //TODO!
                                    $low_range = $max_score - $low_mod_risk_cutoff;
                                    $med_range = $low_mod_risk_cutoff - $mod_high_risk_cutoff;
                                    $high_range = $mod_high_risk_cutoff - $min_score;
                                    
                                    //swap ranges.
                                    if($total_score < $max_score && $total_score > $low_mod_risk_cutoff) {
                                        //low risk
                                        $risk_rating = MODERATE_RISK - (($default_low_range/$low_range)*($total_score - $low_mod_risk_cutoff));
                                    }
                                    else if ($total_score > $mod_high_risk_cutoff && $total_score < $low_mod_risk_cutoff) {
                                        //med risk
                                        $risk_rating = HIGH_RISK - (($default_moderate_range/$med_range)*($total_score - $mod_high_risk_cutoff));
                                    }
                                    else if ($total_score > $min_score && $total_score < $mod_high_risk_cutoff) {
                                        //high risk
                                        $risk_rating = 100 - (($default_high_range/$high_range)*($total_score - $min_score));
                                    }               
                                    
                                }
                                else {
                                    $low_range = $low_mod_risk_cutoff - $min_score;
                                    $med_range = $mod_high_risk_cutoff - $low_mod_risk_cutoff;
                                    $high_range = $max_score - $mod_high_risk_cutoff;
                                    
                                    if($total_score > $min_score && $total_score < $low_mod_risk_cutoff) {
                                        //low risk
                                        $risk_rating = ($default_low_range/$low_range)*($total_score - $min_score);
                                    }
                                    else if ($total_score < $mod_high_risk_cutoff && $total_score > $low_mod_risk_cutoff) {
                                        //med risk
                                        $risk_rating = MODERATE_RISK + ($default_moderate_range/$med_range)*($total_score - $low_mod_risk_cutoff);
                                    }
                                    else if ($total_score < $max_score && $total_score > $mod_high_risk_cutoff) {
                                        //high risk
                                        $risk_rating = HIGH_RISK + ($default_high_range/$high_range)*($total_score - $mod_high_risk_cutoff);
                                    }                                    
                                }
                            }
                           
                        }
                        
                        $return .= "<br>riskrating: ".$risk_rating;

                        //if risk instance already exists, update it or delete it
                        if($create_risk_instance && $risk_instance = $DB->get_record('block_risk_monitor_rule_risk', array('userid' => $enrolled_student->id, 'ruleid' => $rule->id))) {
                                $edited_risk_instance = new object();
                                $edited_risk_instance->id = $risk_instance->id;
                                $edited_risk_instance->value = $risk_rating;

                                $DB->update_record('block_risk_monitor_rule_risk', $edited_risk_instance);
                                $return .= "<br>Edited a risk instance";   
                        }
                                
                        //Otherwise create a new one.
                        else if ($create_risk_instance) {
                            $new_risk_instance = new object();
                            $new_risk_instance->userid = $enrolled_student->id;
                            $new_risk_instance->ruleid = $rule->id;
                            $new_risk_instance->value = $risk_rating;
                            $new_risk_instance->timestamp = time();
                                
                            $DB->insert_record('block_risk_monitor_rule_risk', $new_risk_instance);
                            $return .= "<br>Made a new risk instance"; 
                        }
                        
                    }
                    
                    //Reaching this point, all the risk calculations have been done for every rule in the category for this student
                    //Therefore we can now determine the overall category risk.
                    //Loop thru each rule in the category.
                    $category_risk_rating = 0;
                    $create_cat_risk = false;
                    foreach($rules as $rule) {
                        $weighting = $rule->weighting;
                        if($rule_risk = $DB->get_record('block_risk_monitor_rule_risk', array('ruleid' => $rule->id, 'userid' => $enrolled_student->id))){
                            $category_risk_rating += ($weighting/100)*floatval($rule_risk->value);
                            $create_cat_risk = true;
                        }
                    }
                   $return .= "<br>category risk rating: ".$category_risk_rating;
                   
                    //Check if category risk already exists
                    if($create_cat_risk && $risk_instance = $DB->get_record('block_risk_monitor_cat_risk', array('categoryid' => $category->id, 'userid' => $enrolled_student->id))){
                        if($category_risk_rating >= MODERATE_RISK) {
                            $edited_category_risk = new object();
                            $edited_category_risk->id = $risk_instance->id;
                            $edited_category_risk->value = $category_risk_rating;

                            $DB->update_record('block_risk_monitor_cat_risk', $edited_category_risk);
                            $return .= "<br>Edited a cat risk instance"; 
                        }
                    }
                       
                        //Else create new
                    else if ($create_cat_risk) {
                        $new_category_risk = new object();
                        $new_category_risk->userid = $enrolled_student->id;
                        $new_category_risk->categoryid = $category->id;
                        $new_category_risk->value = intval($category_risk_rating);
                        $new_category_risk->timestamp = time();

                        $DB->insert_record('block_risk_monitor_cat_risk', $new_category_risk);
                        $return .= "<br>Made a new cat risk instance"; 
                    }
                    
                }
                //finished looping thru categories.
            
            }
            //finished looping thru students
            
        }
        //finished looping thru courses.
    
    }
    //No courses exist.
    return $return;
}


//clears out any redundant risk ratings (rule type has been changed, rule has been changed, category has been changed)
function block_risk_monitor_clear_risks($timestamp) {
    
    global $DB;
    
    //Rules have been updated
    $updated_rules = block_risk_monitor_get_updated_rules($timestamp);
    foreach($updated_rules as $updated_rule) {
        $DB->delete_records('block_risk_monitor_rule_risk', array('ruleid' => $updated_rule->id));
    }
    
    //Categories have been updated
    $updated_categories = block_risk_monitor_get_updated_categories($timestamp);
    foreach($updated_categories as $updated_category) {
        $DB->delete_records('block_risk_monitor_cat_risk', array('categoryid' => $updated_category->id));
    }
}

//Returns rules that have been updated since timestamp
function block_risk_monitor_get_updated_rules($timestamp) {
    global $DB;

    $updated_rules = $DB->get_records_select('block_risk_monitor_rule_inst','timestamp > '.$timestamp);
    return $updated_rules;
}

//Returns categories that have been updated since timestamp
function block_risk_monitor_get_updated_categories($timestamp) {
    global $DB;

    $updated_categories = $DB->get_records_select('block_risk_monitor_category','timestamp > '.$timestamp);
    return $updated_categories;
}

//Returns array of students enrolled in a given course
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
