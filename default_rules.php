<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor
 * 
 * This class defines the default rules that can be used.
 */

/*
 * IMPORTANT- TO ADD DEFAULT RULES, ARRAY KEYS MUST REMAIN CONSECUTIVE
 */

abstract class DefaultRules {
    
    public static $default_rule_identifiers = array(0 => "NOT_LOGGED_IN",
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
    
    public static $default_rule_values = array(0 => 10,
                                               1 => 50,
                                               2 => 75,
                                               3 => 1);
    
    public static function getDefaultRuleObjects() {
        
        $default_rules = array();
        
        for($i=0; $i<count(DefaultRules::$default_rule_identifiers); $i++) {
            
            $default_rule = new object();
            $default_rule->name = DefaultRules::$default_rule_names[$i];
            $default_rule->description = DefaultRules::$default_rule_descriptions[$i];
            $default_rule->custom = 0;
            array_push($default_rules, $default_rule);
        }
        
        return $default_rules;
    }

}