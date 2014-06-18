<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

require_once("../../config.php");
require_once("locallib.php");

//Get the ID of the course
$courseid = required_param('courseid', PARAM_INT);

//maybe a check to ensure this teacher is actually in this course?

//DB STUFF - Need all anxiety instances with this course, the exam upcoming...
$course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);

//Teacher must be logged in
require_login($course);

$examcreated = block_anxiety_teacher_create_exam($courseid, $USER->id);

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
        $body .= "<div><b>Exam: <i>".$event->name."</i> on ".date("d F Y", $exam->examdate)."</b><br>";
        //anxious students here...
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