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

//Teacher must be logged in
require_login();

//Get the ID of the teacher
$userid = required_param('userid', PARAM_INT);
$courseid = required_param('courseid', PARAM_INT);
$interventionid = required_param('interventionid', PARAM_INT);
$from_overview = optional_param('from_overview', -1, PARAM_INT);
$from_studentid = optional_param('from_studentid', -1, PARAM_INT);
$from_categoryid = optional_param('from_categoryid', -1, PARAM_INT);

//Error- there is no user associated with the passed param
if (!$getuser = $DB->get_record('user', array('id' => $userid))) {
    print_error('no_user', 'block_risk_monitor', '', $userid);
}

//Error - the user trying to access this instance is the wrong one
if (!($USER->id == $userid)) {
    print_error('wrong_user', 'block_risk_monitor', '', $userid);
}

$intervention_template = $DB->get_record('block_risk_monitor_int_tmp', array('id' => $interventionid));
$context = context_user::instance($userid);

//Set the page parameters
$blockname = get_string('pluginname', 'block_risk_monitor');
$header = get_string('overview', 'block_risk_monitor'); $action = new moodle_url('overview.php', array('userid' => $USER->id, 'courseid' => $courseid));

$PAGE->navbar->add($blockname, new moodle_url('overview.php', array('userid' => $USER->id, 'courseid' => $courseid))); 
$PAGE->navbar->add($header, $action); 

$PAGE->set_context($context);
$PAGE->set_title($blockname . ': ' . $header);
$PAGE->set_heading($blockname . ': ' . $header);
$PAGE->set_url('/blocks/risk_monitor/teacher_block/edit_intervention.php?userid=' . $USER->id . '&courseid=' . $courseid.'&interventionid='.$interventionid);
$PAGE->set_pagetype($blockname);
$PAGE->set_pagelayout('standard');

$body = '';
$has_resources = false;
if($intervention_template->has_files == 1 || ($intervention_template->url != '' && $intervention_template->url != 'http://')) {
    $has_resources = true;
}

$course_context = context_course::instance($courseid);
$instructionsoptions = array('trusttext'=>true, 'context' => $course_context);
$filesoptions = array('subdirs'=>false);

$intervention_template = file_prepare_standard_editor($intervention_template, 'instructions', $instructionsoptions, $course_context, 'block_risk_monitor', 'intervention_instructions', $interventionid);
$intervention_template = file_prepare_standard_filemanager($intervention_template, 'files', $filesoptions, $course_context, 'block_risk_monitor', 'intervention_files', $interventionid);

//$student_profile = new individual_settings_form_view_student('/blocks/risk_monitor/teacher_block/view_student.php?userid='.$USER->id.'&courseid='.$courseid.'&studentid='.$studentid, array('userid' => $userid, 'courseid' => $courseid, 'studentid' => $studentid));
$intervention_form = new individual_settings_form_edit_intervention('/blocks/risk_monitor/teacher_block/edit_intervention.php?userid=' . $USER->id . '&courseid=' . $courseid."&interventionid=".$interventionid.'&from_overview='.$from_overview.'&from_studentid='.$from_studentid.'&from_categoryid='.$from_categoryid, array('userid' => $userid, 'courseid' => $courseid, 'template' => $intervention_template, 'instructionsoptions' => $instructionsoptions, 'filesoptions' => $filesoptions, 'generate_intervention' => $from_overview));

if($intervention_form->is_cancelled()) {
    if($from_overview == -1) {
        redirect(new moodle_url('view_intervention.php', array('userid' => $USER->id, 'courseid' => $courseid, 'interventionid' => $interventionid)));    
    }
    else {
        redirect(new moodle_url('view_category_risk.php', array('userid' => $USER->id, 'courseid' => $courseid, 'studentid' => $from_studentid, 'categoryid' => $from_categoryid)));
    }
}
if($fromform = $intervention_form->get_data()) {
    
    if($from_overview != -1) {
        $fromform->userid = $USER->id;
        $fromform->courseid = $courseid;
        $fromform->categoryid = 0;
        $fromform->instructionsformat = FORMAT_HTML;
    }
    else {
        $fromform->id = $interventionid;
    }
    $fromform->timestamp = time();
    $fromform->url = block_risk_monitor_fix_url($fromform->externalurl);
    $fromform->urlname = $fromform->url_text;
    unset($fromform->externalurl);
    unset($fromform->url_text);
    $fromform->contextid = $course_context->id;
    
    $fs = get_file_storage();
    $usercontext = context_user::instance($USER->id);
    if(count($fs->get_area_files($usercontext->id, 'user', 'draft', $fromform->files_filemanager, 'id')) > 1) {
        $fromform->has_files = 1;
    }
    else {
        $fromform->has_files = 0;
    }

    $fromform = file_postupdate_standard_editor($fromform, 'instructions', array(), $course_context,
                                        'block_risk_monitor', 'intervention_instructions');
    
    if($from_overview == -1) {
        $DB->update_record('block_risk_monitor_int_tmp', $fromform);
       file_save_draft_area_files($fromform->files_filemanager, $course_context->id, 'block_risk_monitor', 'intervention_files',
                      $interventionid, array('subdirs' => 0, 'maxfiles' => 50));    
       redirect(new moodle_url('edit_intervention_templates.php', array('userid' => $USER->id, 'courseid' => $courseid))); 
    }
    else {
        //Save the new intervention template
       $intervention_template_id = $DB->insert_record('block_risk_monitor_int_tmp', $fromform);
       file_save_draft_area_files($fromform->files_filemanager, $course_context->id, 'block_risk_monitor', 'intervention_files',
                      $intervention_template_id, array('subdirs' => 0, 'maxfiles' => 50));    
       $intervention_template = $DB->get_record('block_risk_monitor_int_tmp', array('id' => $intervention_template_id));
       //Create the intervention instance
        $intervention_instance = new object();
        $intervention_instance->studentid = $from_studentid;
        $intervention_instance->timestamp = time();
        $intervention_instance->interventiontemplateid = $intervention_template_id;
        $intervention_instance->viewed = 0;
        $intervention_instance->instructions = $intervention_template->instructions;
        $intervention_instance->courseid = $courseid;
        $intervention_instance->categoryid = $from_categoryid;
        $DB->insert_record('block_risk_monitor_int_inst', $intervention_instance); 
                
        redirect(new moodle_url('view_category_risk.php', array('userid' => $USER->id, 'courseid' => $courseid, 'studentid' => $from_studentid, 'categoryid' => $from_categoryid)));
    }
    
}

//Render the HTML
echo $OUTPUT->header();
echo $OUTPUT->heading($blockname);


//echo html_writer::start_tag('div', array('class' => 'no-overflow'));
//display the settings form
//echo block_risk_monitor_get_tabs_html($userid, true);
if($from_overview != -1) {
    echo block_risk_monitor_get_top_tabs('overview', $courseid); 
    echo $OUTPUT->heading("Intervention preview: ".$intervention_template->title);

}
else {
    echo block_risk_monitor_get_top_tabs('settings', $courseid);
    echo $OUTPUT->heading($intervention_template->title);

}
echo $OUTPUT->box_start();
$intervention_form->display();
echo $OUTPUT->box_end();
echo $OUTPUT->footer();
