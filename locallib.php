<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

defined('MOODLE_INTERNAL') || die();
require_once("../../config.php");
require_once("../../calendar/lib.php");
require_once("default_rules.php");

$block_risk_monitor_block = $DB->get_record('block_risk_monitor_block', array('teacherid' => $USER->id));

define("HIGH_RISK", 75);
define("MODERATE_RISK", 50);
/**
 * Does something really useful with the passed things
 *
 * @param array $things
 * @return object
 */

//This is to be implemented in cron later on
function block_risk_monitor_cron() {
    
    //Check the registered courses
    $registered_courses = block_risk_monitor_get_registered_courses();
    foreach($registered_courses as $registered_course) {
        block_risk_monitor_create_exam($registered_course->id);
    }
    
}

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

//Get rules for a given category.
function block_risk_monitor_get_rules($categoryid) {
    
    global $DB;
    $rules = $DB->get_records('block_risk_monitor_rule', array('categoryid' => $categoryid, 'enabled' => 1));
    return $rules;
}

//returns an array of all the default rules that arent yet added to the category
//returns array where key = ruleid, value = names
function block_risk_monitor_get_unregistered_default_rule_names($categoryid) {
    
    global $DB;
    
    //Get the default rules
    $default_rules = $DB->get_records('block_risk_monitor_rule_type', array('custom' => 0, 'enabled' => 1));
    
    //Get the registered rules
    $registered_rules = block_risk_monitor_get_rules($categoryid);
    
    $unregistered_defaults = array();
    while($default_rule = current($default_rules)) {
        $found = false;
        foreach($registered_rules as $registered_rule) {
            if(strcmp($registered_rule->name, $default_rule->name) == 0) {
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

//Goes through existing rules and creates new weightings in order to accommodate for a new or edited rule
//Sum = 100% minus the specified weighting of the new rule
//If ruleid given, means the rule already exists and must exclude it from our rearrangements
function block_risk_monitor_adjust_weightings($categoryid, $newsum, $ruleid = -1) {
    
    global $DB;
    
    //Get the existing rules
    $registered_rules = block_risk_monitor_get_rules($categoryid);
    
    //Check the given rule exists
    if($ruleid !== -1) {
        if(!$DB->record_exists('block_risk_monitor_rule', array('id' => $ruleid))) {
            $ruleid = -1;
        }
        else {
            $rule = $DB->get_record('block_risk_monitor_rule', array('id' => $ruleid));
        }
    }
    
    $previoussum = 0;
    $rules_to_change = array();
    //Exclude the existing rule
    if ($ruleid !== -1) {
        //Loop thru rules
        foreach($registered_rules as $registered_rule) {
            
            if(!($registered_rule->id == $ruleid)) {
                array_push($rules_to_change, $registered_rule);
            }
        }
        
        $previoussum = 100 - floatval($rule->weighting);
    }
    else {
        $rules_to_change = $registered_rules;
        $previoussum = 100;
    }
    
    
    foreach($rules_to_change as $rule_to_change) {
        //Get the weighting
        $weighting_value = $rule_to_change->weighting;
        
        $new_weighting = ($weighting_value/$previoussum) * $newsum;
        
        //Change in DB
        $new_record = new object();
        $new_record->id = $rule_to_change->id;
        $new_record->weighting = $new_weighting;
        $DB->update_record('block_risk_monitor_rule', $new_record);
    }
    
}

function block_risk_monitor_update_default_rules() {
    global $DB;
    
    //Get all current default rules in the database.
    if($DB->record_exists('block_risk_monitor_rule_type', array('custom' => 0))) {
        
        //Get the enabled and disabled rules -DONT BOTHER FOR NOW.
        /*$default_rules = DefaultRules::getDefaultRuleObjects();
        $enabled_rules_by_admin = array();
        $disabled_rules_by_admin = array();
        $m = 'moodle';
        $i = 0;
        foreach($default_rules as $default_rule) {
             $default_rule_enabled = get_config($m, 'block_risk_monitor_default_rule'.$i);
             if(intval($default_rule_enabled) == 1) {
                 array_push($enabled_rules_by_admin, $default_rule);
             }
             else {
                 array_push($disabled_rules_by_admin, $default_rule);
             }
             $i++;
        }
        
        $enabled_rules_in_database = $DB->get_records('block_risk_monitor_rule_type', array('custom' => 0, 'enabled' => 1));
        $disabled_rules_in_database = $DB->get_records('block_risk_monitor_rule_type', array('custom' => 0, 'enabled' => 0));

        foreach($enabled_rules_by_admin as $enabled_rule_by_admin) {
            foreach($disabled_rules_in_database as $disabled_rule_in_database) {
                if(strcmp($enabled_rule_by_admin->name, $disabled_rule_in_database->name) == 0) {
                    //enable in database
                    $changed_rule = new object();
                    $changed_rule->id = $disabled_rule_in_database->id;
                    $changed_rule->enabled = 1;
                    $DB->update_record('block_risk_monitor_rule_type', $changed_rule);
                }
            }
        }
        
        foreach($disabled_rules_by_admin as $disabled_rule_by_admin) {
            foreach($enabled_rules_in_database as $enabled_rule_in_database) {
                if(strcmp($disabled_rule_by_admin->name, $enabled_rule_in_database->name) == 0) {
                    $changed_rule = new object();
                    $changed_rule->id = $enabled_rule_in_database->id;
                    $changed_rule->enabled = 0;
                    $DB->update_record('block_risk_monitor_rule_type', $changed_rule);                }                
            }
        }*/
    }
    else {      //default rules have not yet been added to database
        //Get the default objects
        $default_rules = DefaultRules::getDefaultRuleObjects();
        foreach($default_rules as $default_rule) {
            $DB->insert_record('block_risk_monitor_rule_type', $default_rule);
        }
    }

}