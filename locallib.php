<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

defined('MOODLE_INTERNAL') || die();
require_once("../../config.php");
require_once("../../calendar/lib.php");
if(!$block_anxiety_teacher_config = $DB->get_record('block_anxiety_teacher_config', array('teacherid' => $USER->id))) {
    $block_anxiety_teacher_config = new object();
    $block_anxiety_teacher_config->teacherid = $USER->id;
    $block_anxiety_teacher_config->dateupdated = time();
    $block_anxiety_teacher_config->timebeforeexam = (7*24*60*60);
    $DB->insert_record('block_anxiety_teacher_config', $block_anxiety_teacher_config);
}

/**
 * Does something really useful with the passed things
 *
 * @param array $things
 * @return object
 */

///Create an exam instance for the course if there is an exam within a week
//returns true if new exam instance created, else false
function block_anxiety_teacher_create_exam($courseid, $teacherid) {
    
    global $DB;
    
    $course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);
    
    //If there is a setting to choose the time before exam use that, otherwise week
    if ($timebeforeexam = $DB->get_record('block_anxiety_teacher_config', array('teacherid' => $teacherid), 'timebeforeexam')) {
        $enddate = time() + $timebeforeexam;
    }
    else 
    {
        $enddate = time() + (7 * 24 * 60 * 60);
    }
    //Get the upcoming events for this course
    $events = calendar_get_events(time(), $enddate, false, false, $course->id);
    
    //parse the events
    foreach ($events as $event) {
        
        //get the name
        $name = $event->name;
        
        //parse for exam or test
        if ((stripos($name,'exam') !== false) || (stripos($name,'test') !== false)) {
            
            
            //found an exam! check it doesn't already exist.
            if (!$existing = $DB->get_record('block_anxiety_teacher_exam', array('eventid' => $event->id))) {
                
                //doesn't exist - so create one
                $exam = new object();
                $exam->examdate = $event->timestart;
                $exam->weighting = 75;//????TO DO!
                $exam->courseid = $courseid;
                $exam->eventid = $event->id;
                $exam->examname = $name;

                //add to DB
                if (!$DB->insert_record('block_anxiety_teacher_exam', $exam)) {
                    echo get_string('errorinsertexam', 'block_anxiety_teacher');
                }  
                return true;
            }
        }
    }
    return false;
}

/**
 * Creates the html tabs
 * 
 * @param int $userid - teacher id
 * @param bool $settings - whether we are in the settings tab (false for overview)
 * @param int $courseid - id of the course tab we are in 
 * @return object
 */
function block_anxiety_teacher_get_tabs_html($userid, $settings, $courseid = null) {
 
    global $USER, $DB;
    
    $table = new html_table();
    $table->attributes['class'] = 'tabs';

    //OVERVIEW AND SETTINGS
    $tabs = array();

    $overviewtab = new html_table_cell();
    $settingstab = new html_table_cell();

    if ($settings) {
        $overviewtab->text = html_writer::link(
            new moodle_url('/blocks/anxiety_teacher/overview.php', array('userid' => $USER->id)),
            get_string('overview', 'block_anxiety_teacher')
        );        

        $settingstab->text = html_writer::tag('static',
            get_string('settings', 'block_anxiety_teacher'));
    }
    else {
        $overviewtab->text = html_writer::tag('static',
            get_string('overview', 'block_anxiety_teacher'));

        $settingstab->text = html_writer::link(
            new moodle_url('/blocks/anxiety_teacher/individual_settings.php', array('userid' => $USER->id)),
            get_string('settings', 'block_anxiety_teacher')
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
                    new moodle_url('/blocks/anxiety_teacher/course_page.php', array('courseid' => $course->id)),
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
    
    

