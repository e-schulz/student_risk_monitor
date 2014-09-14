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

//Teacher must be logged in
require_login();

//Get the ID of the teacher
$userid = required_param('userid', PARAM_INT);
$courseid = required_param('courseid', PARAM_INT);
$interventionid = required_param('interventionid', PARAM_INT);

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
$header = get_string('overview', 'block_risk_monitor');

$PAGE->navbar->add($blockname);
$PAGE->navbar->add($header);

$PAGE->set_context($context);
$PAGE->set_title($blockname . ': ' . $header);
$PAGE->set_heading($blockname . ': ' . $header);
$PAGE->set_url('/blocks/risk_monitor/edit_intervention.php?userid=' . $USER->id . '&courseid=' . $courseid.'&interventionid='.$interventionid);
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

//$student_profile = new individual_settings_form_view_student('/blocks/risk_monitor/view_student.php?userid='.$USER->id.'&courseid='.$courseid.'&studentid='.$studentid, array('userid' => $userid, 'courseid' => $courseid, 'studentid' => $studentid));
$intervention_form = new individual_settings_form_edit_intervention('/blocks/risk_monitor/edit_intervention.php?userid=' . $USER->id . '&courseid=' . $courseid."&interventionid=".$interventionid, array('userid' => $userid, 'courseid' => $courseid, 'template' => $intervention_template, 'instructionsoptions' => $instructionsoptions, 'filesoptions' => $filesoptions));

if($intervention_form->is_cancelled()) {
    redirect(new moodle_url('view_intervention.php', array('userid' => $USER->id, 'courseid' => $courseid, 'interventionid' => $interventionid)));    
}

if($fromform = $intervention_form->get_data()) {
    
    $fromform->id = $interventionid;
    $fromform->timestamp = time();
    $fromform->url = block_risk_monitor_fix_url($fromform->externalurl);
    $fromform->urlname = $fromform->url_text;
    unset($fromform->externalurl);
    unset($fromform->url_text);
    $fromform->contextid = $course_context->id;
    
    $fs = get_file_storage();
    $usercontext = context_user::instance($USER->id);
    if(count($fs->get_area_files($usercontext->id, 'user', 'draft', $fromform->files, 'id')) > 1) {
        $fromform->has_files = 1;
    }
    else {
        $fromform->has_files = 0;
    }

    $fromform = file_postupdate_standard_editor($fromform, 'instructions', array(), $course_context,
                                        'block_risk_monitor', 'intervention_instructions');
    
     $DB->update_record('block_risk_monitor_int_tmp', $fromform);
    //$intervention = $DB->get_record('block_risk_monitor_int_tmp', $intervention_template_id);
    //file_postupdate_standard_filemanager($fromform, 'files',  array('subdirs' => 0, 'maxfiles' => 50), $intervention, 'block_risk_monitor', 'intervention_files', 0);
    file_save_draft_area_files($fromform->files, $course_context->id, 'block_risk_monitor', 'intervention_files',
                   $interventionid, array('subdirs' => 0, 'maxfiles' => 50));    
    
    
    redirect(new moodle_url('view_interventions.php', array('userid' => $USER->id, 'courseid' => $courseid)));        
}

//Render the HTML
echo $OUTPUT->header();
echo $OUTPUT->heading($blockname);


//echo html_writer::start_tag('div', array('class' => 'no-overflow'));
//display the settings form
//echo block_risk_monitor_get_tabs_html($userid, true);
echo block_risk_monitor_get_top_tabs('settings', $courseid);
echo $OUTPUT->heading($intervention_template->title);
echo $OUTPUT->box_start();
$intervention_form->display();
echo $OUTPUT->box_end();
echo $OUTPUT->footer();
