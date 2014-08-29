<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

///REQUIRES AND ERROR MESSAGES

require_once("../../config.php");
require_once("locallib.php");
require_once("individual_settings_form.php");

global $DB;

require_login();

//Get the ID of the teacher
$userid = required_param('userid', PARAM_INT);
$courseid = required_param('courseid', PARAM_INT);
       
//Error- there is no user associated with the passed param
if (!$getuser = $DB->get_record('user', array('id' => $userid))) {
    print_error('no_user', 'block_risk_monitor', '', $userid);
}

//Error - the user trying to access this instance is the wrong one
if (!($USER->id == $userid)) {
    print_error('wrong_user', 'block_risk_monitor', '', $userid);
}
        
$context = context_user::instance($userid);

//Set the page parameters
$blockname = get_string('pluginname', 'block_risk_monitor');
$header = get_string('settings', 'block_risk_monitor');

$PAGE->navbar->add($blockname);
$PAGE->navbar->add($header);

$PAGE->set_context($context);
$PAGE->set_title($blockname . ': '. $header);
$PAGE->set_heading($blockname . ': '.$header);
$PAGE->set_url('/blocks/risk_monitor/edit_categories_rules.php?userid='.$userid.'&courseid='.$courseid);
$PAGE->set_pagetype($blockname);
$PAGE->set_pagelayout('standard');

//Create the body
$body = '';

//Create the form
$questions = block_risk_monitor_get_questions($courseid);
$student_questions_form = new individual_settings_form_student_questions('student_questions.php?userid='.$USER->id.'&courseid='.$courseid, array('questions' => $questions)); 

//On submit
if($student_questions_form->is_cancelled()) {
    //redirect to course
    redirect(new moodle_url('/course/view.php?id='.$courseid));
}
else if ($fromform = $student_questions_form->get_data()) {

    foreach($questions as $question) {
       
        $question_id = 'question_option'.$question->id;
        
        if(isset($fromform->$question_id)) {
            
            $submitted_answer = new object();
            $submitted_answer->questionid = $question->id;
            $submitted_answer->optionid = $fromform->$question_id;
            $submitted_answer->userid = $userid;
            $submitted_answer->timestamp = time();
            
            $DB->insert_record('block_risk_monitor_answer', $submitted_answer);
            
        }
    }
    
    redirect(new moodle_url('/course/view.php?id='.$courseid));
}

//Render the HTML
echo $OUTPUT->header();
echo $OUTPUT->heading($blockname);

echo $OUTPUT->heading("Questions");
echo $body;
$student_questions_form->display();
echo $OUTPUT->footer();