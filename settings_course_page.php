<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

require_once("../../config.php");
require_once("locallib.php");
require_once("individual_settings_form.php");

//Get the ID of the course
$courseid = required_param('courseid', PARAM_INT);

//Teacher must be logged in
require_login();

//PAGE PARAMS
$blockname = get_string('pluginname', 'block_anxiety_teacher');
$header = get_string('overview', 'block_anxiety_teacher');

//need block id! get block instance - for now we will do user :-)
$context = context_user::instance($USER->id);

$PAGE->navbar->add($blockname);
$PAGE->navbar->add($header);

$PAGE->set_context($context);
$PAGE->set_title($blockname . ': '. $header);
$PAGE->set_heading($blockname . ': '.$header);
$PAGE->set_url('/blocks/anxiety_teacher/settings_course_page.php?courseid='.$courseid);
$PAGE->set_pagetype($blockname);
$PAGE->set_pagelayout('standard');

//Get the course
$course_instance = $DB->get_record('block_anxiety_teacher_course', array('blockid' => $block_anxiety_teacher_block->id, 'id' => $courseid), '*',MUST_EXIST);

//Need to create two moodle forms, one for post one for pre
$mform1 = new individual_settings_form_edit_preamble('settings_course_page.php?courseid='.$courseid, array('preamble' => $course_instance->preamble_template));
$mform2 = new individual_settings_form_edit_postamble('settings_course_page.php?courseid='.$courseid, array('postamble' => $course_instance->postamble_template));

echo $OUTPUT->header();
echo $OUTPUT->heading($blockname);

echo html_writer::start_tag('div', array('class' => 'no-overflow'));
//html table goes here
//echo block_anxiety_teacher_get_tabs_html($USER->id, false, $courseid);
$currenttoptab = 'none';
require('top_tabs.php');
$currentcoursetab = 'course'.$courseid;
require('settings_course_tabs.php');
echo html_writer::end_tag('div');

$mform1->display();
$mform2->display();

echo $OUTPUT->footer();