<?php
defined('MOODLE_INTERNAL') || die;

if($ADMIN->fulltree) {
    require_once $CFG->dirroot . '/blocks/risk_monitor/default_rules.php';
    
    //for each default rule in the file, create a checkbox
    $default_rules = DefaultRules::getDefaultRuleObjects();
    $i = 0;
    foreach($default_rules as $default_rule) {
        $settings->add(new admin_setting_configcheckbox(
                    'block_risk_monitor_default_rule'.$i,
                    "Enable default rule: ".$default_rule->name,
                    "Check to allow teachers to add this rule. ".$default_rule->description,
                    '1'
                ));
        $i++;
    }
    
}