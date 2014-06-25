<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

require_once("../../config.php");
require_once("locallib.php");
require_once("intervention_button.php");

//Get the ID of the course
$courseid = required_param('courseid', PARAM_INT);
$anxid = optional_param('anxid', -1, PARAM_INT);

//maybe a check to ensure this teacher is actually in this course?

//DB STUFF - Need all anxiety instances with this course, the exam upcoming...
$course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);

//Teacher must be logged in
require_login($course);

//$examcreated = block_anxiety_teacher_create_exam($courseid, $USER->id);
//if anx id, generate intervention
if ($anxid !== -1) {
    if($anx_instance = $DB->get_record('block_anxiety_teacher_anx', array('id' => $anxid))) {
        
        //first: update the status.
        $DB->update_record('block_anxiety_teacher_anx', array('id' => $anxid, 'status' => 'intervention'));
        
        //second: do the whole group thing.
        //1. check if the activity has already been created.
        //2. add this student to the group
    }
}


//PAGE PARAMS
$blockname = get_string('pluginname', 'block_anxiety_teacher');
$header = get_string('overview', 'block_anxiety_teacher');

//need block id! get block instance - for now we will do user :-)
$context = context_user::instance($USER->id);

$PAGE->navbar->add($blockname);
$PAGE->navbar->add($header);

$PAGE->set_title($blockname . ': '. $header);
$PAGE->set_heading($blockname . ': '.$header);
$PAGE->set_url('/blocks/anxiety_teacher/course_page.php?courseid='.$courseid);
$PAGE->set_pagetype($blockname);
$PAGE->set_pagelayout('standard');
$PAGE->set_context($context);

//get the exam(s)
if ($exams = $DB->get_records('block_anxiety_teacher_exam', array('courseid' => $courseid))) {
    
    $body = '';
    foreach($exams as $exam) {
   
        $event = $DB->get_record('event', array('id' => $exam->eventid));
        $body .= "<div><b>Exam: <i>".$event->name."</i> on ".date("d F Y", $exam->examdate)."</b><br><br>";
        
        if($anxious_students = $DB->get_records('block_anxiety_teacher_anx', array('examid' => $exam->id))) {
            
            $studentstable = new html_table();
            $headers = array();
            
            $studentfirstnamehead = new html_table_cell();
            $studentfirstnamehead->text = '<b>First name</b>';
            $headers[] = $studentfirstnamehead;
                    
            $studentlastnamehead = new html_table_cell();
            $studentlastnamehead->text = '<b>Last name</b>';
            $headers[] = $studentlastnamehead;
            
            $activityhead = new html_table_cell();
            $activityhead->text = '<b>Activity level</b>';
            $headers[] = $activityhead;
                    
            $traithead = new html_table_cell();
            $traithead->text = '<b>Typical anxiety level</b>';
            $headers[] = $traithead;
                    
            $currentgradehead = new html_table_cell();
            $currentgradehead->text = '<b>Current grade</b>';
            $headers[] = $currentgradehead;
                    
            $anxietyhead = new html_table_cell();
            $anxietyhead->text = '<b>Proposed anxiety level</b>';
            $headers[] = $anxietyhead;
 
            $statushead = new html_table_cell();
            $statushead->text = '<b>Status</b>';
            $headers[] = $statushead;
            
            $actionhead = new html_table_cell();
            $actionhead->text = '<b>Action</b>';
            $headers[] = $actionhead;
            
            $studentstable->data[] = new html_table_row($headers);
            
            //header.
            foreach($anxious_students as $anxious_student) {
               
                
                //get the user
                $student = $DB->get_record('user', array('id' => $anxious_student->studentid));
                $trait_anxiety = $DB->get_record('block_anxiety_teacher_trait', array('studentid' => $anxious_student->studentid));
                        
                $studentrow = array();

                $studentfirstname = new html_table_cell();
                $studentfirstname->text = $student->firstname;
                $studentrow[] = $studentfirstname;
                
                $studentlastname = new html_table_cell();
                $studentlastname->text = $student->lastname;
                $studentrow[] = $studentlastname;
                
                $activity = new html_table_cell();
                $activity->text = $anxious_student->activitylevel;
                $studentrow[] = $activity;

                $trait = new html_table_cell();
                if ($trait_anxiety) {
                    $trait->text = $trait_anxiety->anxietylevel;
                }
                else {
                    $trait->text = 'N/A';                    
                }
                $studentrow[] = $trait;

                $currentgrade = new html_table_cell();
                $currentgrade->text = $anxious_student->currentgradepercent.' %';
                $studentrow[] = $currentgrade;
                
                $anxiety = new html_table_cell();
                $anxiety->text = get_string($anxious_student->anxietylevel, 'block_anxiety_teacher');
                $studentrow[] = $anxiety;
                
                $status = new html_table_cell();
                $status->text = get_string($anxious_student->status, 'block_anxiety_teacher');
                $studentrow[] = $status;
                
                $action = new html_table_cell();
                if($anxious_student->status == 'intervention') {
                    $action->text = '<button disabled="true">'.get_string('submitintervention','block_anxiety_teacher').'</button>';
                }
                else {
                    $action->text = $OUTPUT->single_button(new moodle_url('/blocks/anxiety_teacher/course_page.php', array('courseid' => $courseid, 'anxid' => $anxious_student->id)), get_string('submitintervention','block_anxiety_teacher'));
                }
                $studentrow[] = $action;
                
                
                $studentstable->data[] = new html_table_row($studentrow);               

            }
            
            $body .= html_writer::table($studentstable);
        }
        else {
            $body .= 'No anxious students.<br>';
        }
        $body .= "</div><br>";
    }
}
else {
    $body .= "No exams within ".$block_anxiety_teacher_config->timebeforeexam;
}

echo $OUTPUT->header();
echo $OUTPUT->heading($blockname);

echo html_writer::start_tag('div', array('class' => 'no-overflow'));
//html table goes here
//echo block_anxiety_teacher_get_tabs_html($USER->id, false, $courseid);
$currenttoptab = 'overview';
require('top_tabs.php');
$currentcoursetab = 'course'.$courseid;
require('course_tabs.php');
echo $body;
echo html_writer::end_tag('div');


echo $OUTPUT->footer();