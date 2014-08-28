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
                                         3 => "MISSED_DEADLINES");
    
    public static $default_rule_names = array(0 => "Student not logged in for x days",
                                              1 => "Student has grade lower than x",
                                              2 => "Student has grade higher than x",
                                              3 => "Student has missed x deadlines");
    
    public static $default_rule_descriptions = array(0 => "This rule is triggered when a student has not logged in for a specified number of days in a row.",
                                                     1 => "This rule is triggered when a student has a current grade lower than a specified percentage.",
                                                     2 => "This rule is triggered when a student has a current grade higher than a specified percentage.",
                                                     3 => "This rule is triggered when a student has missed a specified number of deadlines within Moodle.");
    
    public static $default_rule_value_required = array(0 => 1,
                                               1 => 1,
                                               2 => 1,
                                               3 => 1);
    
    public static $default_rule_value_description = array(0 => "Days since last login",
                                               1 => "Grade cutoff",
                                               2 => "Grade cutoff",
                                               3 => "Number of deadlines");
    
    public static function getDefaultRuleObjects() {
        
        $default_rules = array();
        
        for($i=0; $i<count(DefaultRules::$default_rule_actions); $i++) {
            
            $default_rule = new object();
            $default_rule->name = DefaultRules::$default_rule_names[$i];
            $default_rule->description = DefaultRules::$default_rule_descriptions[$i];
            $default_rule->custom = 0;
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
