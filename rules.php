<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor
 * 
 * Everything related to rules - default rule definitions, and action methods
 */

/*
 * IMPORTANT- TO ADD DEFAULT RULES, ARRAY KEYS MUST REMAIN CONSECUTIVE
 */

abstract class DefaultRules {
    
    public static $default_rule_actions = array(0 => "NOT_LOGGED_IN",
                                         1 => "GRADE_LESS_THAN",
                                         2 => "GRADE_GREATER_THAN",
                                         3 => "MISSED_DEADLINES",
                                         4 => "ACTIVITIES_FAILED",
                                         5 => "LOW_FORUM_MESSAGES_POSTED",
                                         6 => "LOW_FORUM_MESSAGES_READ",
                                         7 => "LOW_TOTAL_FORUM_TIME",
                                         8 => "LOW_TOTAL_COURSE_CLICKS",
                                         9 => "LOW_AVERAGE_CLICKS_PER_DAY",
                                         10 => "LOW_AVERAGE_SESSION_DURATION",
                                         11 => "EXAM_APPROACHING");
    
    public static $default_rule_names = array(0 => "Student not logged in for x days",
                                              1 => "Student has grade lower than x",
                                              2 => "Student has grade higher than x",
                                              3 => "Student has missed x activity deadlines",
                                              4 => "Student has failed x activities",
                                              5 => "Student has posted a low number of messages in the forum",
                                              6 => "Student has read a low number of messages in the forum",
                                              7 => "Student has spent a low amount of time in the forum",
                                              8 => "Student has low total clicks within the course",
                                              9 => "Student has low average clicks per session",
                                              10 => "Student has low average login session duration",
                                              11 => "Student has an exam approaching");
    
    public static $default_rule_descriptions = array(0 => "This rule is triggered when a student has not logged in for a specified number of days in a row.",
                                                     1 => "This rule is triggered when a student has a current grade lower than a specified percentage.",
                                                     2 => "This rule is triggered when a student has a current grade higher than a specified percentage.",
                                                     3 => "This rule is triggered when a student has missed a specified number of deadlines within Moodle.",
                                                     4 => "This rule is triggered when a student has failed a specified number of activities within the course.",
                                                     5 => "This rule is triggered when a student has posted a low number of messages in the forum, relative to other students.",
                                                     6 => "This rule is triggered when a student has read a low number of messages in the forum, relative to other students.",
                                                     7 => "This rule is triggered when a student has spent a low amount of time in the forum, relative to other students.",
                                                     8 => "This rule is triggered when a student has a low amount of total clicks within the course, relative to other students.",
                                                     9 => "This rule is triggered when a student has a low number of average clicks within the course per session, relative to other students.",
                                                     10 => "This rule is triggered when a student has a low average login session duration, relative to other students.",
                                                     11 => "This rule is triggered when a student has an exam approaching.");
    
    public static $default_rule_value_required = array(0 => 1,
                                               1 => 1,
                                               2 => 1,
                                               3 => 1,
                                               4 => 1,
                                               5 => 0,
                                               6 => 0,
                                               7 => 0,
                                               8 => 0,
                                               9 => 0,
                                               10 => 0,
                                               11 => 0);
    
    public static $default_rule_value_description = array(0 => "Days since last login",
                                               1 => "Grade cutoff",
                                               2 => "Grade cutoff",
                                               3 => "Number of deadlines",
                                               4 => "Number of activities failed",
                                               5 => "",
                                               6 => "",
                                               7 => "",
                                               8 => "",
                                               9 => "",
                                               10 => "",
                                               11 => "");
    
    public static function getDefaultRuleObjects() {
        
        $default_rules = array();
        
        for($i=0; $i<count(DefaultRules::$default_rule_actions); $i++) {
            
            $default_rule = new object();
            $default_rule->id = $i;
            $default_rule->name = DefaultRules::$default_rule_names[$i];
            $default_rule->description = DefaultRules::$default_rule_descriptions[$i];
            $default_rule->value_required = DefaultRules::$default_rule_value_required[$i];
            if ($default_rule->value_required == 1) {
                $default_rule->value_description = DefaultRules::$default_rule_value_description[$i];
            }
            $default_rule->action = DefaultRules::$default_rule_actions[$i];
            array_push($default_rules, $default_rule);
        }
        
        return $default_rules;
    }

}
