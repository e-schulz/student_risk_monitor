<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

defined('MOODLE_INTERNAL') || die();
require_once("../../config.php");
require_once("../../calendar/lib.php");
require_once("rules.php");
require_once("riskslib.php");
require_once("rulelib.php");

$block_risk_monitor_block = $DB->get_record('block_risk_monitor_block', array('teacherid' => $USER->id));

///Create an exam instance for the course if there is an exam within a week
//returns true if new exam instance created, else false
function block_risk_monitor_create_exam($courseid) {
    
    global $DB;
    
    $block_course = $DB->get_record('block_risk_monitor_course', array('id' => $courseid), '*', MUST_EXIST);
    $course = $DB->get_record('course', array('id' => $block_course->courseid), '*', MUST_EXIST);
    
    $enddate = time() + (7 * 24 * 60 * 60);
    
    //Get the upcoming events for this course
    $events = calendar_get_events(time(), $enddate, false, false, $course->id);
    
    //parse the events
    foreach ($events as $event) {
        
        //get the name
        $name = $event->name;
        
        //parse for exam or test
        if ((stripos($name,'exam') !== false) || (stripos($name,'test') !== false)) {
            
            
            //found an exam! check it doesn't already exist.
            if (!$existing = $DB->get_record('block_risk_monitor_exam', array('eventid' => $event->id))) {
                
                //doesn't exist - so create one
                $exam = new object();
                $exam->examdate = $event->timestart;
                $exam->weighting = 75;//????TO DO!
                $exam->courseid = $block_course->id;
                $exam->eventid = $event->id;

                //add to DB
                if (!$DB->insert_record('block_risk_monitor_exam', $exam)) {
                    echo get_string('errorinsertexam', 'block_risk_monitor');
                }  
                return true;
            }
        }
    }
    return false;
}

//Get all the courses a teacher is teacher of
function block_risk_monitor_get_courses($teacherid) {
    
        global $DB;
        $roleassigns = $DB->get_records('role_assignments', array('userid' => $teacherid, 'roleid' => 3), 'contextid');

        $teachercourses = array();

        foreach ($roleassigns as $roleassign) {

            //Get only the context instances where context = course 
            $contextinstances = $DB->get_records('context', array('contextlevel' => 50, 'id' => $roleassign->contextid));

            //add to the courses
            $teachercourses = array_merge($teachercourses, $contextinstances);
        }

        $courses = array();

        foreach($teachercourses as $teachercourse) {

            //Get the course.
            $course = $DB->get_records('course', array('id' => $teachercourse->instanceid));
            $courses = array_merge($courses, $course);
        }
        
        return $courses;
}

//Get all the courses registered for this block
function block_risk_monitor_get_registered_courses() {
    
        global $DB, $block_risk_monitor_block;
        $registered_courses = $DB->get_records('block_risk_monitor_course', array('blockid' => $block_risk_monitor_block->id));
        return $registered_courses;
}

/**
 * Creates the html tabs
 * 
 * @param int $userid - teacher id
 * @param bool $settings - whether we are in the settings tab (false for overview)
 * @param int $courseid - id of the course tab we are in 
 * @return object
 */
function block_risk_monitor_get_tabs_html($userid, $settings, $courseid = null) {
 
    global $USER, $DB;
    
    $table = new html_table();
    $table->attributes['class'] = 'tabs';

    //OVERVIEW AND SETTINGS
    $tabs = array();

    $overviewtab = new html_table_cell();
    $settingstab = new html_table_cell();

    if ($settings) {
        $overviewtab->text = html_writer::link(
            new moodle_url('/blocks/risk_monitor/overview.php', array('userid' => $USER->id)),
            get_string('overview', 'block_risk_monitor')
        );        

        $settingstab->text = html_writer::tag('static',
            get_string('settings', 'block_risk_monitor'));
    }
    else {
        $overviewtab->text = html_writer::tag('static',
            get_string('overview', 'block_risk_monitor'));

        $settingstab->text = html_writer::link(
            new moodle_url('/blocks/risk_monitor/individual_settings.php', array('userid' => $USER->id)),
            get_string('settings', 'block_risk_monitor')
        );        
    }
    
    $tabs[] = $overviewtab;
    $tabs[] = $settingstab;

    $table->data[] = new html_table_row($tabs);

    //COURSE TABS
    if (!$settings) {
        
        $coursetabs = array();

        //Get the context instances where the user is the teacher
        $roleassigns = $DB->get_records('role_assignments', array('userid' => $userid, 'roleid' => 3), 'contextid');

        $teachercourses = array();

        foreach ($roleassigns as $roleassign) {

            //Get only the context instances where context = course 
            $contextinstances = $DB->get_records('context', array('contextlevel' => 50, 'id' => $roleassign->contextid));

            //add to the courses
            $teachercourses = array_merge($teachercourses, $contextinstances);
        }

        foreach($teachercourses as $teachercourse) {

            //Get the course.
            $course = $DB->get_record('course', array('id' => $teachercourse->instanceid));

            $coursetab = new html_table_cell();
            
            if ($courseid === null || $courseid != $course->id) {
                $coursetab->text = html_writer::link(
                    new moodle_url('/blocks/risk_monitor/course_page.php', array('courseid' => $course->id)),
                    $course->shortname
                );
            }
            else {
                $coursetab->text = html_writer::tag('static',
                    $course->shortname);
            }    
            $coursetabs[] = $coursetab;
        }
        $table->data[] = new html_table_row($coursetabs);
    }
    
    return html_writer::table($table);
}


//Takes $courseid = table course field id
function block_risk_monitor_get_course_tabs_html($courseid = -1) {
    
    global $OUTPUT, $USER;
    //If courseid = -1, not currently in a course
    if($courseid == -1) {
        $tab = ''; 
    }
    //Else in course
    else {
        $tab = 'course'.$courseid;
    }
    
    
    $row = array();
    $courses = block_risk_monitor_get_registered_courses();
    foreach($courses as $course) {
        $row[] = new tabobject('course'.$course->courseid,
                            new moodle_url('/blocks/risk_monitor/edit_categories_rules.php', array('userid' => $USER->id, 'courseid' => $course->courseid)),
                            $course->fullname);        
    }
    
    return '<div class="coursedisplay">'.$OUTPUT->tabtree($row, $tab).'</div>';
}

function populate_with_test_data($examid) {
    
    //Get all user IDs
    //
    //Create the anx data using random for grade percent, anxiety level can be the same
    
}

function block_risk_monitor_get_top_tabs($currenttoptab) {
    global $OUTPUT, $USER;
    
    $row = array();
    $row[] = new tabobject('overview',
                           new moodle_url('/blocks/risk_monitor/overview.php', array('userid' => $USER->id)),
                            get_string('overview', 'block_risk_monitor'));

    $row[] = new tabobject('settings',
                           new moodle_url('/blocks/risk_monitor/individual_settings.php', array('userid' => $USER->id)),
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
function block_risk_monitor_get_default_rules($categoryid) {
    
    global $DB;
    $rules = $DB->get_records('block_risk_monitor_rule_inst', array('categoryid' => $categoryid, 'ruletype' => 1));
    return $rules;
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