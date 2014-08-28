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

global $block_risk_monitor_block, $DB;

//$DB->delete_records('block_risk_monitor_course', array('blockid' => $block_risk_monitor_block->id));

//Teacher must be logged in
require_login();

//Get the ID of the teacher
$userid = required_param('userid', PARAM_INT);
//$courseid = required_param('courseid', PARAM_INT);              

//Error- there is no user associated with the passed param
if (!$getuser = $DB->get_record('user', array('id' => $userid))) {
    print_error('no_user', 'block_risk_monitor', '', $userid);
}

//Error - the user trying to access this instance is the wrong one
if (!($USER->id == $userid)) {
    print_error('wrong_user', 'block_risk_monitor', '', $userid);
}
        
//Check that the course exists.

$context = context_user::instance($userid);

//Set the page parameters
$blockname = get_string('pluginname', 'block_risk_monitor');
$header = get_string('settings', 'block_risk_monitor');

$PAGE->navbar->add($blockname);
$PAGE->navbar->add($header);

$PAGE->set_context($context);
$PAGE->set_title($blockname . ': '. $header);
$PAGE->set_heading($blockname . ': '.$header);
$PAGE->set_url('/blocks/risk_monitor/edit_categories_rules.php?userid='.$userid);
$PAGE->set_pagetype($blockname);
$PAGE->set_pagelayout('standard');

//Create the body
$body = '';

//Create the form
$new_category_form = new individual_settings_form_new_category('new_category.php?userid='.$USER->id/*.'&courseid='.$courseid*/); 

//On submit
if ($fromform = $new_category_form->get_data()) {
    //Create the category
    $new_category = new object();
    $new_category->name = $fromform->name_text;
    $new_category->description = $fromform->description_text;
    $new_category->courseid = /*$courseid*/0;
    $new_category->timestamp = time();
    
    //add to DB
    if (!$DB->insert_record('block_risk_monitor_category', $new_category)) {
        echo get_string('errorinsertcategory', 'block_risk_monitor');
    }     
    
    //Redirect to categories+rules
    redirect(new moodle_url('edit_categories_rules.php', array('userid' => $USER->id/*, 'courseid' => $courseid*/)));

}

//Render the HTML
echo $OUTPUT->header();
echo $OUTPUT->heading($blockname);


//echo html_writer::start_tag('div', array('class' => 'no-overflow'));

//display the settings form
//echo block_risk_monitor_get_tabs_html($userid, true);
echo block_risk_monitor_get_top_tabs('settings');
echo $OUTPUT->heading("New Category");
echo $body;
$new_category_form->display();
echo $OUTPUT->footer();