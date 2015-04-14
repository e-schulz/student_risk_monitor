<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

///REQUIRES AND ERROR MESSAGES

require_once("../../../config.php");
require_once("../locallib.php");
require_once("../student_risk_monitor_forms.php");

global $DB;

require_login();

//Get the ID of the teacher
$userid = required_param('userid', PARAM_INT);
$courseid = required_param('courseid', PARAM_INT);
$questionnaireid = required_param('questionnaireid', PARAM_INT);
       
//Error- there is no user associated with the passed param
if (!$getuser = $DB->get_record('user', array('id' => $userid))) {
    print_error('no_user', 'block_risk_monitor', '', $userid);
}

//Error - the user trying to access this instance is the wrong one
if (!($USER->id == $userid)) {
    print_error('wrong_user', 'block_risk_monitor', '', $userid);
}
$questionnaire = $DB->get_record('block_risk_monitor_cust_rule', array('id' => $questionnaireid));
$student = $DB->get_record('user', array('id' => $userid));
$context = context_user::instance($userid);

//Set the page parameters
$blockname = get_string('studentpluginname', 'block_risk_monitor');
$header = "Questionnaire";
if($questionnaire->title != "") {
  $header = $questionnaire->title;  
}

$PAGE->navbar->add($blockname); 
$PAGE->navbar->add($header);

$PAGE->set_context($context);
$PAGE->set_title($blockname . ': '. $header);
$PAGE->set_heading($blockname . ': '.$header);
$PAGE->set_url('/blocks/risk_monitor/student_block/student_questionnaire.php?userid='.$userid.'&courseid='.$courseid);
$PAGE->set_pagetype($blockname);
$PAGE->set_pagelayout('standard');

//Create the body
$body = '';

//Create the form
$questions = block_risk_monitor_get_questions($questionnaireid, $userid);
$student_questions_form = new individual_settings_form_student_questions('student_questionnaire.php?userid='.$USER->id.'&courseid='.$courseid.'&questionnaireid='.$questionnaireid, array('questions' => $questions)); 

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
if($questionnaire->title != null) {
    $title = $questionnaire->title;
}
else {
    $title = "Questionnaire";
}
echo $OUTPUT->heading($title);
echo $body;
echo $OUTPUT->box_start();
echo str_replace('<studentname>', $student->firstname, htmlspecialchars_decode($questionnaire->instructions));
$student_questions_form->display();
echo $OUTPUT->box_end();
echo $OUTPUT->footer();