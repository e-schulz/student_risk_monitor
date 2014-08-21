<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

defined('MOODLE_INTERNAL') || die();
require_once("../../config.php");
require_once("../../calendar/lib.php");

$block_risk_monitor_block = $DB->get_record('block_risk_monitor_block', array('teacherid' => $USER->id));

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

