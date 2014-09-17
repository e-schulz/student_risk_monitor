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
                                         9 => "LOW_AVERAGE_CLICKS_PER_SESSION",
                                         10 => "LOW_AVERAGE_SESSION_DURATION");
    
    public static $default_rule_names = array(0 => "Student not logged in for number of days",
                                              1 => "Student has grade lower than certain cutoff",
                                              2 => "Student has grade higher than certain cutoff",
                                              3 => "Student has missed a number of activity deadlines",
                                              4 => "Student has failed a number of activities",
                                              5 => "Student has posted below average messages in the forum",
                                              6 => "Student has read below average of messages in the forum",
                                              7 => "Student has spent below average time in the forum",
                                              8 => "Student has below average total clicks within the course",
                                              9 => "Student has below average clicks per session",
                                              10 => "Student has below average course session duration");
    
    public static $default_rule_descriptions = array(0 => "This rule is triggered when a student has not logged in for a specified number of days in a row.",
                                                     1 => "This rule is triggered when a student has a current grade lower than a specified percentage.",
                                                     2 => "This rule is triggered when a student has a current grade higher than a specified percentage.",
                                                     3 => "This rule is triggered when a student has missed a specified number of deadlines within Moodle.",
                                                     4 => "This rule is triggered when a student has failed a specified number of activities within the course.",
                                                     5 => "This rule is triggered when a student has posted a below average number of messages in the forum, relative to other students.",
                                                     6 => "This rule is triggered when a student has read a below average number of messages in the forum, relative to other students.",
                                                     7 => "This rule is triggered when a student has spent a below average amount of time in the forum, relative to other students.",
                                                     8 => "This rule is triggered when a student has a below average amount of total clicks within the course, relative to other students.",
                                                     9 => "This rule is triggered when a student has a below average number of average clicks within the course per session, relative to other students.",
                                                     10 => "This rule is triggered when a student has a below average time spent within the course in a single session session, relative to other students.");
    
    public static $default_rule_value_required = array(0 => 1,
                                               1 => 1,
                                               2 => 1,
                                               3 => 1,
                                               4 => 1,
                                               5 => 1,
                                               6 => 1,
                                               7 => 1,
                                               8 => 1,
                                               9 => 1,
                                               10 => 1);
    
        public static $default_rule_value= array(0 => 5,
                                               1 => 50,
                                               2 => 50,
                                               3 => 3,
                                               4 => 3,
                                               5 => 50,
                                               6 => 50,
                                               7 => 50,
                                               8 => 50,
                                               9 => 50,
                                               10 => 50);
        
    public static $default_rule_value_description = array(0 => "Days without logging in to trigger rule",
                                               1 => "Grade cutoff (between 0 and 100%)",
                                               2 => "Grade cutoff (between 0 and 100%)",
                                               3 => "Number of deadlines to miss",
                                               4 => "Number of activities to fail",
                                               5 => "Percent below average",
                                               6 => "Percent below average",
                                               7 => "Percent below average",
                                               8 => "Percent below average",
                                               9 => "Percent below average",
                                               10 => "Percent below average");
    
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
    
    public static function getDefaultRuleObject($default_rule_id) {
        
        $default_rule = new object();
        $default_rule->id = $default_rule_id;
        $default_rule->name = DefaultRules::$default_rule_names[$default_rule_id];
        $default_rule->description = DefaultRules::$default_rule_descriptions[$default_rule_id];
        $default_rule->value_required = DefaultRules::$default_rule_value_required[$default_rule_id];
        if ($default_rule->value_required == 1) {
            $default_rule->value_description = DefaultRules::$default_rule_value_description[$default_rule_id];
        }
        $default_rule->action = DefaultRules::$default_rule_actions[$default_rule_id];

        return $default_rule;
    }

}