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
//$message = optional_param('message', 0, PARAM_INT);
//$courseid = optional_param('courseid', 0, PARAM_INT);
$categoryid = optional_param('categoryid', -1, PARAM_INT);
$ruleid = optional_param('ruleid', -1, PARAM_INT);

$body = '';

//Error- there is no user associated with the passed param
if (!$getuser = $DB->get_record('user', array('id' => $userid))) {
    print_error('no_user', 'block_risk_monitor', '', $userid);
}

//Error - the user trying to access this instance is the wrong one
if (!($USER->id == $userid)) {
    print_error('wrong_user', 'block_risk_monitor', '', $userid);
}

//Delete things
if($categoryid !== -1) {
    
    //Delete category
    if($DB->record_exists('block_risk_monitor_category', array('id' => $categoryid))) {
        $DB->delete_records('block_risk_monitor_category', array('id' => $categoryid));
    }    
    
    //Delete all rules associated with a category
    if($DB->record_exists('block_risk_monitor_rule_inst', array('categoryid' => $categoryid))) {
        $DB->delete_records('block_risk_monitor_rule_inst', array('categoryid' => $categoryid));        
    }
}
else if ($ruleid !== -1) {
    
    //Delete record and readjust weightings.
    if($rule_to_delete = $DB->get_record('block_risk_monitor_rule_inst', array('id' => $ruleid))) {
        $old_sum = 100 - intval($rule_to_delete->weighting);
        $DB->delete_records('block_risk_monitor_rule_inst', array('id' => $ruleid));
        $body .= block_risk_monitor_adjust_weightings_rule_deleted($rule_to_delete->categoryid, $old_sum);        
    }
}

$back_to_settings = html_writer::link (new moodle_url('individual_settings.php', array('userid' => $USER->id)), get_string('back_to_settings','block_risk_monitor'));
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

//If any courses display first available, else signal no courses
/*if($courseid == 0) {
    
    //Check courses
    if(!$registered_courses = block_risk_monitor_get_registered_courses()) {            //no courses
        $courseid = -1;
    }
    else {                                                                              //first course returned
        $courseid = reset($registered_courses)->courseid;
    }
    
    
}*/
//Get all the categories and courses.
$categories_rules_form = new individual_settings_form_edit_categories_rules('edit_categories_rules.php?userid='.$USER->id/*.'&courseid='.$courseid, array('courseid' => $courseid)*/); 
       
/*if($courseid == -1) {
    $body .= get_string('no_courses', 'block_risk_monitor')."<br>";
    $body .= html_writer::link (new moodle_url('edit_courses.php', array('userid' => $USER->id)), get_string('add_courses','block_risk_monitor')).'<br><br>';
}*/

        
///RENDERING THE HTML
/*if ($fromform = $categories_rules_form->get_data()) {
        
        //Get the data, delete everything checked. If category deleted, need to go through and delete all rules.
    
        //Get all categories, then all rules assoc with those categories
        $all_categories = $DB->get_records('block_risk_monitor_category', array('courseid' => $courseid));
        
        $all_rules = array();
        foreach($all_categories as $category) {
            $category_rules = $DB->get_records('block_risk_monitor_rule_inst', array('categoryid' => $category->id));
            foreach($category_rules as $category_rule) {
                array_push($all_rules, $category_rule);
            }
        }
        
        foreach($all_rules as $rule) {
            $checkboxname = "delete_category".$rule->id;
            if(isset($fromform->$checkboxname)) {
                //delete from DB!
                $DB->delete_record('block_risk_monitor_rule_inst', array('id' => $rule->id));
            }
        }
}*/
    
//Render the HTML
echo $OUTPUT->header();
echo $OUTPUT->heading($blockname);


//echo html_writer::start_tag('div', array('class' => 'no-overflow'));

//display the settings form
//echo block_risk_monitor_get_tabs_html($userid, true);
echo block_risk_monitor_get_top_tabs('settings');
echo $OUTPUT->heading("Categories and rules");

//Course tabs
//echo block_risk_monitor_get_course_tabs_html($courseid);
echo $body;
//if ($courseid !== -1) {
$categories_rules_form->display();
//}
    
echo $back_to_settings;
echo $OUTPUT->footer();