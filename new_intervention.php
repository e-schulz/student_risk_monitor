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
$categoryid = required_param('categoryid', PARAM_INT);

//Error- there is no user associated with the passed param
if (!$getuser = $DB->get_record('user', array('id' => $userid))) {
    print_error('no_user', 'block_risk_monitor', '', $userid);
}

//Error - the user trying to access this instance is the wrong one
if (!($USER->id == $userid)) {
    print_error('wrong_user', 'block_risk_monitor', '', $userid);
}

$category = $DB->get_record('block_risk_monitor_category', array('id' => $categoryid));
$context = context_user::instance($userid);

//Set the page parameters
$blockname = get_string('pluginname', 'block_risk_monitor');
$header = get_string('settings', 'block_risk_monitor');

$PAGE->navbar->add($blockname);
$PAGE->navbar->add($header);

$PAGE->set_context($context);
$PAGE->set_title($blockname . ': '. $header);
$PAGE->set_heading($blockname . ': '.$header);
$PAGE->set_url('/blocks/risk_monitor/new_interventions.php?userid='.$USER->id.'&courseid='.$courseid.'&categoryid='.$categoryid);
$PAGE->set_pagetype($blockname);
$PAGE->set_pagelayout('standard');

//$student_profile = new individual_settings_form_view_student('/blocks/risk_monitor/view_student.php?userid='.$USER->id.'&courseid='.$courseid.'&studentid='.$studentid, array('userid' => $userid, 'courseid' => $courseid, 'studentid' => $studentid));
$new_intervention_form = new individual_settings_form_new_intervention('/blocks/risk_monitor/new_intervention.php?userid='.$USER->id.'&courseid='.$courseid.'&categoryid='.$categoryid, array('userid' => $userid, 'courseid' => $courseid, 'categoryid' => $categoryid));

if($new_intervention_form->is_cancelled()) {
    //Redirect to view_interventions
    redirect(new moodle_url('view_interventions.php', array('userid' => $USER->id, 'courseid' => $courseid)));    
}
else if ($fromform = $new_intervention_form->get_data()) {
    
    $course_context = context_course::instance($courseid);
    $intervention_template = new object();
    $intervention_template->name = $fromform->name_text;
    $intervention_template->description = $fromform->description_text;
    $intervention_template->title = $fromform->title_text;
    $intervention_template->instructions = $fromform->instructions_text;
    $intervention_template->userid = $USER->id;
    $intervention_template->courseid = $courseid;
    $intervention_template->categoryid = $categoryid;
    $intervention_template->timestamp = time();
    $intervention_template->url = block_risk_monitor_fix_url($fromform->externalurl);
    $intervention_template->urlname = $fromform->url_text;
    $intervention_template->contextid = $course_context->id;
    
    $fs = get_file_storage();
    $usercontext = context_user::instance($USER->id);
    if(count($fs->get_area_files($usercontext->id, 'user', 'draft', $fromform->files, 'id')) > 1) {
        $intervention_template->has_files = 1;
    }
    else {
        $intervention_template->has_files = 0;
    }
    
    $intervention_template_id = $DB->insert_record('block_risk_monitor_int_tmp', $intervention_template);
    //$intervention = $DB->get_record('block_risk_monitor_int_tmp', $intervention_template_id);
    //file_postupdate_standard_filemanager($fromform, 'files',  array('subdirs' => 0, 'maxfiles' => 50), $intervention, 'block_risk_monitor', 'intervention_files', 0);
    file_save_draft_area_files($fromform->files, $course_context->id, 'block_risk_monitor', 'intervention_files',
                   $intervention_template_id, array('subdirs' => 0, 'maxfiles' => 50));    

    redirect(new moodle_url('view_interventions.php', array('userid' => $USER->id, 'courseid' => $courseid)));    
}

//Render the HTML
echo $OUTPUT->header();
echo $OUTPUT->heading($blockname);


//echo html_writer::start_tag('div', array('class' => 'no-overflow'));

//display the settings form
//echo block_risk_monitor_get_tabs_html($userid, true);
echo block_risk_monitor_get_top_tabs('settings', $courseid);
echo $OUTPUT->heading("New intervention: ".$category->name);
$new_intervention_form->display();
echo $OUTPUT->footer();