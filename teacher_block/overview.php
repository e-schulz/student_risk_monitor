<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

///REQUIRES AND ERROR MESSAGES

require_once("../../../config.php");
require_once("../locallib.php");
//Teacher must be logged in
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
//CRON IS HERE FOR NOW - TO DO - GET RID OF IT
//block_risk_monitor_cron();
//PAGE PARAMS
$blockname = get_string('pluginname', 'block_risk_monitor');
$header = get_string('overview', 'block_risk_monitor'); 
$action = new moodle_url('overview.php', array('userid' => $USER->id, 'courseid' => $courseid));

//need block id! get block instance - for now we will do course :-)
$context = context_user::instance($userid);

$PAGE->navbar->add($blockname, new moodle_url('overview.php', array('userid' => $USER->id, 'courseid' => $courseid))); 
$PAGE->navbar->add($header, $action); 

$PAGE->set_context($context);
$PAGE->set_title($blockname . ': '. $header);
$PAGE->set_heading($blockname . ': '.$header);
$PAGE->set_url('/blocks/risk_monitor/teacher_block/overview.php&courseid='.$courseid);
$PAGE->set_pagetype($blockname);
$PAGE->set_pagelayout('standard');

$body = '';

//get all the categories and associated risk instances.
if ($categories = $DB->get_records('block_risk_monitor_category', array('courseid' => $courseid))) {
 
        //Print out the header
        $studentstable = new html_table();
        $headers = array();
           
        $studentfirstnamehead = new html_table_cell();
        $studentfirstnamehead->text = '<b>Student</b>';
        $headers[] = $studentfirstnamehead;
                    
        $students_at_risk = array();            //array of userids of at risk students
        $interventions_generated = array();
        foreach($categories as $category) {
            
            //Get all the risk instances
            if($category_risks = $DB->get_records('block_risk_monitor_cat_risk', array('categoryid' => $category->id))) {
                   foreach($category_risks as $category_risk) {
                       
                       /*if($category_risk->value >= MODERATE_RISK && $DB->record_exists('block_risk_monitor_int_inst', array('categoryid' => $category->id, 'studentid' => $category_risk->userid))) {
                           $interventions_generated[] = $category_risk->userid;
                       }
                       else*/ if($category_risk->value >= MODERATE_RISK) {
                           if(array_key_exists($category_risk->userid, $students_at_risk)) {
                               $students_at_risk[$category_risk->userid] += $category_risk->value; 
                           }
                           else {
                               $students_at_risk[$category_risk->userid] = $category_risk->value;
                           }
                       }
                   }
            }
            
            //Create the headers
            $categoryhead = new html_table_cell();
            $categoryhead->text = '<b>'.$category->name.'</b>';
            $headers[] = $categoryhead;            
        }    
        $studentstable->data[] = new html_table_row($headers);
        arsort($students_at_risk);
        $student_ids_at_risk = array_keys($students_at_risk);
        /*foreach($student_ids_at_risk as $student_id) {
            $key = array_search($student_id, $interventions_generated);
            if($key !== false) {
                unset($interventions_generated[$key]);
            }
        }*/
        
        //Loop thru the at risk students.
        if(count($students_at_risk) > 0) {
            
            $students_at_risk = array_keys($students_at_risk);
            foreach($students_at_risk as $student_at_risk) {
                
                //get the student
                $student = $DB->get_record('user', array('id' => $student_at_risk));
                
                //Get all risk instances associated with this user
                if($student_category_risks = $DB->get_records('block_risk_monitor_cat_risk', array('userid' => $student_at_risk))) {
                    //Write out the table line
                    $studentrow = array();

                    $studentname = new html_table_cell();
                    $studentname->text = $student->firstname."&nbsp;".$student->lastname;
                    $studentrow[] = $studentname;                   

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
                            //$category_cell->text = html_writer::empty_tag('img', array('src' => get_string('no_risk_icon', 'block_risk_monitor'), 'align' => 'middle'));
                        }
                        else {                          //risk for this category!
                            //Get the risk
                            $rating = $student_risk->value;
                            if($rating >= MODERATE_RISK && $rating < HIGH_RISK) {
                                $category_cell->text = html_writer::start_tag('a', array('href' => 'view_category_risk.php?userid='.$USER->id.'&categoryid='.$category->id."&courseid=".$courseid.'&studentid='.$student->id.'&categoryid='.$category->id))
                                     .html_writer::empty_tag('img', array('src' => get_string('moderate_risk_icon', 'block_risk_monitor'),'align' => 'middle'))
                                     .html_writer::end_tag('a');  
                            }
                            else if($rating >= HIGH_RISK) {
                                $category_cell->text = html_writer::start_tag('a', array('href' => 'view_category_risk.php?userid='.$USER->id.'&categoryid='.$category->id."&courseid=".$courseid.'&studentid='.$student->id.'&categoryid='.$category->id))
                                      .html_writer::empty_tag('img', array('src' => get_string('high_risk_icon', 'block_risk_monitor'),'align' => 'middle'))
                                     .html_writer::end_tag('a');                                  
                            }
                            /*else {
                                $category_cell->text = html_writer::empty_tag('img', array('src' => get_string('low_risk_icon', 'block_risk_monitor'),'align' => 'middle'));
                            }*/
                            
                        }
                        $studentrow[] = $category_cell;
                    }    
                    $studentstable->data[] = new html_table_row($studentrow);
                }
            }
        }
        
        //Loop thru students who have had interventions generated.
        /*if(count($interventions_generated) > 0) {
            
            foreach($interventions_generated as $student_at_risk) {
                
                //get the student
                $student = $DB->get_record('user', array('id' => $student_at_risk));
                
                //Get all risk instances associated with this user
                if($student_category_risks = $DB->get_records('block_risk_monitor_cat_risk', array('userid' => $student_at_risk))) {
                    //Write out the table line
                    $studentrow = array();

                    $studentname = new html_table_cell();
                    $studentname->text = $student->firstname."&nbsp;".$student->lastname;
                    $studentrow[] = $studentname;                   

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
                            //$category_cell->text = html_writer::empty_tag('img', array('src' => get_string('no_risk_icon', 'block_risk_monitor'), 'align' => 'middle'));
                        }
                        else {                          //risk for this category!
                            //Get the risk
                            $rating = $student_risk->value;
                            if($rating >= MODERATE_RISK && $rating < HIGH_RISK) {
                                $category_cell->text = html_writer::start_tag('a', array('href' => 'view_category_risk.php?userid='.$USER->id.'&categoryid='.$category->id."&courseid=".$courseid.'&studentid='.$student->id.'&categoryid='.$category->id))
                                     .html_writer::empty_tag('img', array('src' => get_string('moderate_risk_intervention_generated', 'block_risk_monitor'),'align' => 'middle'))
                                     .html_writer::end_tag('a');  
                            }
                            else if($rating >= HIGH_RISK) {
                                $category_cell->text = html_writer::start_tag('a', array('href' => 'view_category_risk.php?userid='.$USER->id.'&categoryid='.$category->id."&courseid=".$courseid.'&studentid='.$student->id.'&categoryid='.$category->id))
                                      .html_writer::empty_tag('img', array('src' => get_string('high_risk_intervention_generated', 'block_risk_monitor'),'align' => 'middle'))
                                     .html_writer::end_tag('a');                                  
                            }
                            else {
                                $category_cell->text = html_writer::empty_tag('img', array('src' => get_string('low_risk_icon', 'block_risk_monitor'),'align' => 'middle'));
                            }
                            
                        }
                        $studentrow[] = $category_cell;
                    }    
                    $studentstable->data[] = new html_table_row($studentrow);
                }
            }
        }*/
        else {
            //No students at risk
        }
        $body .= html_writer::table($studentstable);
}
else {
    $body .= "No categories created for this course. Go to settings to add categories and rules.";
}


//NEXT - BODY
//$body = get_string('overview_body', 'block_risk_monitor');

//FINAL RENDERING
echo $OUTPUT->header();
echo $OUTPUT->heading($blockname);

echo html_writer::start_tag('div', array('class' => 'no-overflow'));
//html table goes here.
//echo block_risk_monitor_get_tabs_html($userid, false);
$currentcoursetab = '';
echo block_risk_monitor_get_top_tabs('overview', $courseid);
echo $body;
echo html_writer::end_tag('div');


echo $OUTPUT->footer();