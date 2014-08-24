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
$course = $DB->get_record('block_risk_monitor_course', array('courseid' => $courseid), '*', MUST_EXIST);

//Teacher must be logged in
require_login();


//PAGE PARAMS
$blockname = get_string('pluginname', 'block_risk_monitor');
$header = get_string('overview', 'block_risk_monitor');

//need block id! get block instance - for now we will do user :-)
$context = context_user::instance($USER->id);

$PAGE->navbar->add($blockname);
$PAGE->navbar->add($header);

$PAGE->set_context($context);
$PAGE->set_title($blockname . ': '. $header);
$PAGE->set_heading($blockname . ': '.$header);
$PAGE->set_url('/blocks/risk_monitor/course_page.php?courseid='.$courseid);
$PAGE->set_pagetype($blockname);
$PAGE->set_pagelayout('standard');
$body = '';

//get all the categories and associated risk instances.
if ($categories = $DB->get_records('block_risk_monitor_category', array('courseid' => $courseid))) {
 
        //Print out the header
        $studentstable = new html_table();
        $headers = array();
           
        $studentfirstnamehead = new html_table_cell();
        $studentfirstnamehead->text = '<b>First name</b>';
        $headers[] = $studentfirstnamehead;
                    
        $studentlastnamehead = new html_table_cell();
        $studentlastnamehead->text = '<b>Last name</b>';
        $headers[] = $studentlastnamehead;
            
        $students_at_risk = array();            //array of userids of at risk students
        foreach($categories as $category) {
            
            //Get all the risk instances
            if($category_risks = $DB->get_records('block_risk_monitor_cat_risk', array('categoryid' => $category->id))) {
                   foreach($category_risks as $category_risk) {
                       array_push($students_at_risk, $category_risk->userid);
                   }
                   //make the array unique
                   array_unique($students_at_risk);
            }
            
            //Create the headers
            $categoryhead = new html_table_cell();
            $categoryhead->text = '<b>'.$category->name.'</b>';
            $headers[] = $categoryhead;            
        }    
        $studentstable->data[] = new html_table_row($headers);
        
        //Loop thru the at risk students.
        if(count($students_at_risk) > 0) {
            foreach($students_at_risk as $student_at_risk) {
                
                //get the student
                $student = $DB->get_record('user', array('id' => $student_at_risk));
                
                //Get all risk instances associated with this user
                if($student_category_risks = $DB->get_records('block_risk_monitor_cat_risk', array('userid' => $student_at_risk))) {
                    //Write out the table line
                    $studentrow = array();

                    $studentfirstname = new html_table_cell();
                    $studentfirstname->text = $student->firstname;
                    $studentrow[] = $studentfirstname;

                    $studentlastname = new html_table_cell();
                    $studentlastname->text = $student->lastname;
                    $studentrow[] = $studentlastname;

                    foreach($categories as $category) {
                        $found = false;
                        $student_risk;
                        foreach($student_category_risks as $student_category_risk) {
                            if($student_category_risk->categoryid === $category->id) {
                                $found = true;
                                $student_risk = $student_category_risk;
                            }
                        }
                        
                        $category_cell = new html_table_cell();
                        if($found == false) {           //no risk for this category, leave empty
                            $category_cell->text = html_writer::empty_tag('img', array('src' => get_string('no_risk_icon', 'block_risk_monitor')));
                        }
                        else {                          //risk for this category!
                            //Get the risk
                            $rating = $student_risk->value;
                            if($rating > MODERATE_RISK && $rating < HIGH_RISK) {
                                $category_cell->text = html_writer::empty_tag('img', array('src' => get_string('moderate_risk_icon', 'block_risk_monitor')));
                            }
                            else if($rating > HIGH_RISK) {
                                $category_cell->text = html_writer::empty_tag('img', array('src' => get_string('high_risk_icon', 'block_risk_monitor')));
                            }
                            else {
                                $category_cell->text = html_writer::empty_tag('img', array('src' => get_string('no_risk_icon', 'block_risk_monitor')));
                            }
                            
                        }
                        $studentrow[] = $category_cell;
                    }
                }
            }
            $studentstable->data[] = new html_table_row($studentrow);
        }
        else {
            //No students at risk
        }
        $body .= html_writer::table($studentstable);
}
else {
    $body .= "No categories created for this course. Go to settings to add categories and rules.";
}

echo $OUTPUT->header();
echo $OUTPUT->heading($blockname);

echo html_writer::start_tag('div', array('class' => 'no-overflow'));
//html table goes here
//echo block_risk_monitor_get_tabs_html($USER->id, false, $courseid);
echo block_risk_monitor_get_top_tabs('none');
echo get_string('overview_body', 'block_risk_monitor');
$currentcoursetab = 'course'.$courseid;
require('course_tabs.php');
echo $body;
echo html_writer::end_tag('div');


echo $OUTPUT->footer();