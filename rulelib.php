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
require_once($CFG->libdir . '/gradelib.php');

//This function returns a risk rating between 0 and 100, given the action userid and value.
function block_risk_monitor_calculate_risk_rating($action, $user, $value, $courseid) {
    
    $risk_rating = 0;
    
    //These actions must match actions specified in rules.php
    switch($action){
        CASE 'NOT_LOGGED_IN':
            $risk_rating = block_risk_monitor_not_logged_in_risk($user, $value);
            break;
        case 'GRADE_LESS_THAN':
            $risk_rating = block_risk_monitor_grade_less_than_risk($user, $value, $courseid);
            break;
        case 'GRADE_GREATER_THAN':
            $risk_rating = block_risk_monitor_grade_greater_than_risk($user, $value, $courseid);
            break;
        case 'MISSED_DEADLINES':
            $risk_rating = block_risk_monitor_missed_deadlines_risk($user, $value, $courseid);
            break;
        default:
            break;
    }
    
    return $risk_rating;
}

//this function returns 100 if user has not logged in for days greater than value, else 0
function block_risk_monitor_not_logged_in_risk($user, $value) {
   
    $risk_rating = 0;
    
    //Get the most recent login of the student.
    $most_recent_login_time = $user->currentlogin;
    
    //Calculate how many days it has been since then (time is in seconds)
    $seconds_since = time() - $most_recent_login_time;
    
    //convert to days
    $days_since = $seconds_since/(60*60*24);
    
    //If days >= $value, risk = 100 else risk = 0 
    if ($days_since >= $value) {
        $risk_rating = 100;
    }
    
    return $risk_rating;
}


function block_risk_monitor_grade_less_than_risk($user, $value, $courseid) {
    
    global $DB;
    $risk_rating = 0;
    
    //Get the grade item associated with the course.
    $course_grade_item = grade_item::fetch_course_item($courseid);
    
    //Get the grade_grade instance, if it exists.
    if($grade_grade = $DB->get_record('grade_grades', array('itemid' => $course_grade_item->id, 'userid' => $user->id))) {
        $max_grade = $grade_grade->rawgrademax;
        $final_grade = $grade_grade->rawgrade;
        $percent = intval(($final_grade/$max_grade)*100);
        if($percent < $value) {
            $risk_rating = 100;
        }
    }
    
    return $risk_rating;
}

function block_risk_monitor_grade_greater_than_risk($user, $value, $courseid) {
    
    global $DB;
    $risk_rating = 0;
    
    //Get the grade item associated with the course.
    $course_grade_item = grade_item::fetch_course_item($courseid);
    
    //Get the grade_grade instance, if it exists.
    if($grade_grade = $DB->get_record('grade_grades', array('itemid' => $course_grade_item->id, 'userid' => $user->id))) {
        $max_grade = $grade_grade->rawgrademax;
        $final_grade = $grade_grade->rawgrade;
        $percent = intval(($final_grade/$max_grade)*100);
        if($percent > $value) {
            $risk_rating = 100;
        }
    }
    
    return $risk_rating;    
}

function block_risk_monitor_missed_deadlines_risk($user, $value, $courseid) {
    //Check how many deadlines they have missed. Look at graded modules maybe?
}