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

global $block_anxiety_teacher_block, $DB;

//$DB->delete_records('block_anxiety_teacher_course', array('blockid' => $block_anxiety_teacher_block->id));

//Teacher must be logged in
require_login();

//Get the ID of the teacher
$userid = required_param('userid', PARAM_INT);
//$message = optional_param('message', 0, PARAM_INT);
$settingspage = optional_param('settingspage', 0, PARAM_INT);
$courseid = optional_param('courseid', 0, PARAM_INT);

//if no course given
if($courseid == 0 && $settingspage == 3){
    $settingspage = 2;
}

//Error- there is no user associated with the passed param
if (!$getuser = $DB->get_record('user', array('id' => $userid))) {
    print_error('no_user', 'block_anxiety_teacher', '', $userid);
}

//Error - the user trying to access this instance is the wrong one
if (!($USER->id == $userid)) {
    print_error('wrong_user', 'block_anxiety_teacher', '', $userid);
}

$context = context_user::instance($userid);

//Set the page parameters
$blockname = get_string('pluginname', 'block_anxiety_teacher');
$header = get_string('settings', 'block_anxiety_teacher');

$PAGE->navbar->add($blockname);
$PAGE->navbar->add($header);

$PAGE->set_context($context);
$PAGE->set_title($blockname . ': '. $header);
$PAGE->set_heading($blockname . ': '.$header);
$PAGE->set_url('/blocks/anxiety_teacher/individual_settings.php?userid='.$userid);
$PAGE->set_pagetype($blockname);
$PAGE->set_pagelayout('standard');

/*$DB->delete_records('block_anxiety_teacher_course');
        
   $all_courses = block_anxiety_teacher_get_courses($USER->id);

    //Set the full and short names
    foreach($all_courses as $single_course) {
            $new_course = new object();
            $new_course->courseid = $single_course->id;
            $new_course->blockid = $block_anxiety_teacher_block->id;//????TO DO!
            $new_course->preamble_template = get_string('preamble-template', 'block_anxiety_teacher');
            $new_course->postamble_template = get_string('postamble-template', 'block_anxiety_teacher');            
            $new_course->fullname = $single_course->fullname;
            $new_course->shortname = $single_course->shortname;
        if (!$DB->insert_record('block_anxiety_teacher_course', $new_course)) {
        echo get_string('errorinsertcourse', 'block_anxiety_teacher');
    }
        
    }*/
                //doesn't exist - so create one
               /* $exam = new object();
                $exam->currentgradepercent = 49;
                $exam->examid = 4;//????TO DO!
                $exam->studentid = 6;
                $exam->anxietylevel = 'med';     
                $exam->dategenerated = time();
                $exam->status = 'new';      
                $DB->insert_record('block_anxiety_teacher_anx', $exam);
                
                                $exam = new object();
                $exam->currentgradepercent = 49;
                $exam->examid = 4;//????TO DO!
                $exam->studentid = 7;
                $exam->anxietylevel = 'high';     
                $exam->dategenerated = time();
                $exam->status = 'new';      
                $DB->insert_record('block_anxiety_teacher_anx', $exam);
                
                                $exam = new object();
                $exam->currentgradepercent = 49;
                $exam->examid = 4;//????TO DO!
                $exam->studentid = 8;
                $exam->anxietylevel = 'low';     
                $exam->dategenerated = time();
                $exam->status = 'new';      
                $DB->insert_record('block_anxiety_teacher_anx', $exam);*/
                
//Create the body
$body = '';
//Add or delete course
if ($settingspage == 1) {
    
    $all_courses = block_anxiety_teacher_get_courses($USER->id);

    //If there are registered courses, need two forms, otherwise one
    if ($registered_courses = $DB->get_records('block_anxiety_teacher_course', array('blockid' => $block_anxiety_teacher_block->id))) {

        //Create a new array containing only unregistered courses
        $unregistered_courses = array();

        //Loop thru all the courses and add only those that aren't already registered
        foreach($all_courses as $single_course) {
            $registered = false;
            foreach($registered_courses as $registered_course) {
                if($single_course->id === $registered_course->courseid) {
                    $registered = true;
                    break;
                }
            }
            if ($registered == false) {
                $unregistered_courses[] = $single_course;
            }

        }
    }
    else {
        $unregistered_courses = $all_courses; 
        $registered_courses = array();
    }

    //The add/delete form
    $add_delete_form = new individual_settings_form_add_remove_courses('individual_settings.php?userid='.$USER->id.'&settingspage='.$settingspage, array('courses_to_add' => $unregistered_courses, 'courses_to_delete' => $registered_courses));    
    $back_to_settings = html_writer::link (new moodle_url('individual_settings.php', array('userid' => $USER->id, 'settingspage' => 0)), get_string('back_to_settings','block_anxiety_teacher'));
    
}
//Course templates page
else if ($settingspage == 2) {   
    
    //Text 
    $body .= get_string('course_templates_text','block_anxiety_teacher').'<br><br>';
    //Back to settings button
    $back_to_settings = html_writer::link (new moodle_url('individual_settings.php', array('userid' => $USER->id, 'settingspage' => 0)), get_string('back_to_settings','block_anxiety_teacher'));
    
}
//Individual course template instance
else if ($settingspage == 3) {

    $course_instance = $DB->get_record('block_anxiety_teacher_course', array('blockid' => $block_anxiety_teacher_block->id, 'id' => $courseid), '*',MUST_EXIST);

    $preamble_form = new individual_settings_form_edit_preamble('individual_settings.php?userid='.$USER->id.'&settingspage='.$settingspage.'&courseid='.$courseid, array('preamble' => $course_instance->preamble_template));
    $postamble_form = new individual_settings_form_edit_postamble('individual_settings.php?userid='.$USER->id.'&settingspage='.$settingspage.'&courseid='.$courseid, array('postamble' => $course_instance->postamble_template));

}
//Just go initial settings
else {
    //Link to add or delete
    $body .= html_writer::link (new moodle_url('individual_settings.php', array('userid' => $USER->id, 'settingspage' => 1)), get_string('add_or_delete','block_anxiety_teacher')).'<br><br>';
            
    //Description for add or delete
    $body .= html_writer::tag('div', get_string('add_delete_text','block_anxiety_teacher').'<br><br>');
            
    //Link to edit templates
    $body .= html_writer::link (new moodle_url('individual_settings.php', array('userid' => $USER->id, 'settingspage' => 2)), get_string('edit_templates','block_anxiety_teacher').'<br><br>');
         
    //Description for edit templates
    $body .= html_writer::tag('div', get_string('edit_templates_text','block_anxiety_teacher').'<br><br>');

}

///GETTING THE INFORMATION FROM THE DATABASE
//Here they can add or remove courses
//Need an array of unregistered courses, and an array of registered courses


///RENDERING THE HTML

//Course added
/*if ($fromform = $mform1->get_data()) {
    
    //Create a new course instance
    $new_course = new object();
    $new_course->courseid = $fromform->add_course;
    $new_course->blockid = $block_anxiety_teacher_block->id;//????TO DO!
    $new_course->preamble_template = get_string('preamble-template', 'block_anxiety_teacher');
    $new_course->postamble_template = get_string('postamble-template', 'block_anxiety_teacher');

    //Set the full and short names
    foreach($all_courses as $single_course) {
        if($single_course->id === $fromform->add_course) {
            $new_course->fullname = $single_course->fullname;
            $new_course->shortname = $single_course->shortname;
        }
    }
    
    //add to DB
    if (!$DB->insert_record('block_anxiety_teacher_course', $new_course)) {
        echo get_string('errorinsertcourse', 'block_anxiety_teacher');
    }      
    
    //reload page
    redirect(new moodle_url('/blocks/anxiety_teacher/individual_settings.php', array('userid' => $USER->id)));

}
//Course removed
if ($fromform2 = $mform2->get_data()) {
    
    if ($DB->record_exists('block_anxiety_teacher_course', array('blockid' => $block_anxiety_teacher_block->id, 'courseid' => $fromform2->delete_course))) {
        $DB->delete_records('block_anxiety_teacher_course', array('blockid' => $block_anxiety_teacher_block->id, 'courseid' => $fromform2->delete_course));
    } 
    
    //reload page
    redirect(new moodle_url('/blocks/anxiety_teacher/individual_settings.php', array('userid' => $USER->id)));
}*/

//from add/delete form
if ($settingspage == 1) {
    if ($fromform1 = $add_delete_form->get_data()) {
  $body .= "jjddbhgg";

    }
}

else if ($settingspage == 3) {
    if ($fromform2 = $preamble_form->get_data()) {

    }
    //post
    if ($fromform3 = $postamble_form->get_data()) {

    }
}



//Render the HTML
echo $OUTPUT->header();
echo $OUTPUT->heading($blockname);

//echo html_writer::start_tag('div', array('class' => 'no-overflow'));

//display the settings form
//echo block_anxiety_teacher_get_tabs_html($userid, true);
$currenttoptab = 'settings';
require('top_tabs.php');

if ($settingspage == 2 || $settingspage == 3) {
    $currentcoursetab = '';
    require('settings_course_tabs.php'); 
}

echo $body;

if ($settingspage == 1) {
    //Display form
    $add_delete_form->display();
    //Display button
    echo $back_to_settings;
}
else if ($settingspage == 2) {
    //Display button
     echo $back_to_settings;
}
else if ($settingspage == 3) {
    //Display pre form
    //Display post form
    $preamble_form->display();
    $postamble_form->display();
    
}

echo $OUTPUT->footer();