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
$studentid = required_param('studentid', PARAM_INT);
$categoryid = required_param('categoryid', PARAM_INT);
$intervention_id = optional_param('interventionid', -1, PARAM_INT);
$do_delete = optional_param('do_delete', -1, PARAM_INT);

//Error- there is no user associated with the passed param
if (!$getuser = $DB->get_record('user', array('id' => $userid))) {
    print_error('no_user', 'block_risk_monitor', '', $userid);
}

//Error - the user trying to access this instance is the wrong one
if (!($USER->id == $userid)) {
    print_error('wrong_user', 'block_risk_monitor', '', $userid);
}
//The student.
$student = $DB->get_record('user', array('id' => $studentid));
$category = $DB->get_record('block_risk_monitor_category', array('id' => $categoryid));
$context = context_user::instance($userid);

if($intervention_id !== -1 && $do_delete == -1) {
    if(!$DB->record_exists('block_risk_monitor_int_inst', array('studentid' => $studentid, 'interventiontemplateid' => $intervention_id))) {
        $intervention_template = $DB->get_record('block_risk_monitor_int_tmp', array('id' => $intervention_id));
        $intervention_template->categoryid = 0;
        unset($intervention_template->id);
        $new_template_id = $DB->insert_record('block_risk_monitor_int_tmp', $intervention_template);
        $intervention_instance = new object();
        $intervention_instance->studentid = $studentid;
        $intervention_instance->timestamp = time();
        $intervention_instance->interventiontemplateid = $new_template_id;
        $intervention_instance->viewed = 0;
        $intervention_instance->instructions = $intervention_template->instructions;
        $intervention_instance->courseid = $courseid;
        $intervention_instance->categoryid = $categoryid;
        $DB->insert_record('block_risk_monitor_int_inst', $intervention_instance);
    }
    
}
else if($intervention_id !== -1) {
    
    //Remove an intervention
    if($DB->record_exists('block_risk_monitor_int_inst', array('studentid' => $studentid, 'interventiontemplateid' => $intervention_id))) {    
       
        if($DB->record_exists('block_risk_monitor_int_tmp', array('id' => $intervention_id))) {
            $DB->delete_records('block_risk_monitor_int_tmp', array('id' => $intervention_id));
       }               
       $DB->delete_records('block_risk_monitor_int_inst', array('studentid' => $studentid, 'interventiontemplateid' => $intervention_id));
    }    
    
}

//Set the page parameters
$blockname = get_string('pluginname', 'block_risk_monitor');
$header = get_string('overview', 'block_risk_monitor'); $action = new moodle_url('overview.php', array('userid' => $USER->id, 'courseid' => $courseid));

$PAGE->navbar->add($blockname, new moodle_url('overview.php', array('userid' => $USER->id, 'courseid' => $courseid))); 
$PAGE->navbar->add($header, $action); 

$PAGE->set_context($context);
$PAGE->set_title($blockname . ': '. $header);
$PAGE->set_heading($blockname . ': '.$header);
$PAGE->set_url('/blocks/risk_monitor/view_category_risk.php?userid='.$USER->id.'&courseid='.$courseid.'&studentid='.$studentid.'&categoryid='.$categoryid);
$PAGE->set_pagetype($blockname);
$PAGE->set_pagelayout('standard');

$back_to_overview = html_writer::link (new moodle_url('overview.php', array('userid' => $USER->id, 'courseid' => $courseid)), "Back to overview");

$problem_areas = new individual_settings_form_student_problem_areas('/blocks/risk_monitor/view_category_risk.php?userid='.$USER->id.'&courseid='.$courseid.'&studentid='.$studentid.'&categoryid='.$categoryid, array('studentid' => $studentid, 'categoryid' => $categoryid));
$interventions = new individual_settings_form_student_interventions('/blocks/risk_monitor/view_category_risk.php?userid='.$USER->id.'&courseid='.$courseid.'&studentid='.$studentid.'&categoryid='.$categoryid, array('userid' => $userid, 'courseid' => $courseid, 'studentid' => $studentid, 'categoryid' => $categoryid));

/*if($fromform = $category_profile->get_data()) {
    //Get which checkboxes are checked
    if ($intervention_templates = $DB->get_records('block_risk_monitor_int_tmp', array('categoryid' => $categoryid))) {
        foreach($intervention_templates as $intervention_template) {
            $form_identifier = 'intervention'.$intervention_template->id;
            if($fromform->$form_identifier == 1) {
                //Checked, create intervention instance.
                if(!$DB->record_exists('block_risk_monitor_int_inst', array('studentid' => $studentid, 'interventiontemplateid' => $intervention_template->id))) {
                    $intervention_instance = new object();
                    $intervention_instance->studentid = $studentid;
                    $intervention_instance->timestamp = time();
                    $intervention_instance->interventiontemplateid = $intervention_template->id;
                    $intervention_instance->viewed = 0;
                    $intervention_instance->instructions = $intervention_template->instructions;

                    $DB->insert_record('block_risk_monitor_int_inst', $intervention_instance);
                }
            }
        }
    }
    //Create intervention instances.
}*/

//Render the HTML
echo $OUTPUT->header();
echo $OUTPUT->heading($blockname);


//echo html_writer::start_tag('div', array('class' => 'no-overflow'));

//display the settings form
//echo block_risk_monitor_get_tabs_html($userid, true);
echo block_risk_monitor_get_top_tabs('overview', $courseid);
echo $OUTPUT->heading($category->name." risk: ".$student->firstname."&nbsp;".$student->lastname);
echo $back_to_overview."<br><br>";

//////////
/// PROBLEM AREAS
//////////
echo $OUTPUT->box_start();
echo "<b>Problem areas</b>";
echo "<table><tr><td width=100px></td><td></td></tr>";

if($rules_broken = $DB->get_records_sql("SELECT * FROM {block_risk_monitor_rule_risk} r WHERE r.value >= ".MODERATE_RISK." AND r.userid = ".$studentid)) {
     foreach($rules_broken as $rule_broken) {
         $rule_inst = $DB->get_record('block_risk_monitor_rule_inst', array('id' => $rule_broken->ruleid));
         if($rule_inst->categoryid == $categoryid) {
             if($rule_broken->value >= MODERATE_RISK) {
                 echo "<tr><td></td><td>".html_writer::empty_tag('img', array('src' => "../../../pix/i/risk_xss.png"))."&nbsp;";
             }
             echo $rule_inst->name."</td></tr>";
         }
     }
}
echo "</table>";
echo $OUTPUT->box_end();
echo "<br><br>";
////////
//// INTERVENTIONS
//////////
echo $OUTPUT->box_start();
echo "<b>Interventions generated</b>";
if($interventions = $DB->get_records('block_risk_monitor_int_inst', array('categoryid' => $categoryid, 'studentid' => $studentid))) {
    echo "<table><tr><td width=100px></td><td width=250px></td><td width=150px><b>Date generated</b></td><td width=150px><b>Viewed by student?</b></td><td></td></tr>";       
    foreach($interventions as $intervention) { 
        if($DB->record_exists('block_risk_monitor_int_tmp', array('id' => $intervention->interventiontemplateid))) {
            $intervention_template = $DB->get_record('block_risk_monitor_int_tmp', array('id' => $intervention->interventiontemplateid));
            $viewed = "No";
            if($intervention->viewed == 1) {
                $viewed = "Yes";
            }
            echo "<tr><td></td><td><li>".
                html_writer::link (new moodle_url('view_intervention.php', array('userid' => $USER->id, 'courseid' => $courseid, 'interventionid' => $intervention_template->id, 'from_overview' => 1, 'from_studentid' => $studentid, 'from_categoryid' => $categoryid)), $intervention_template->name)."<br>&emsp;".
            $intervention_template->description."</li></td><td>".date("F j, Y", $intervention->timestamp)."</td><td>".$viewed."</td><td>".
              html_writer::empty_tag('img', array('src' => get_string('delete_icon', 'block_risk_monitor')))."&nbsp;&nbsp;".
            html_writer::link (new moodle_url('view_category_risk.php', array('userid' => $USER->id, 'courseid' => $courseid, 'studentid' => $studentid, 'interventionid' => $intervention_template->id, 'do_delete' => 1, 'categoryid' => $categoryid)), "Remove this intervention")
                    ."</td></tr><br>";           
        }                                  
    }
    
    echo "</table>";
    
}
else {
    echo "<table><tr><td width=100px></td><td><br>No interventions generated.</td></tr></table>";
}
        ///create new intervention
        echo "<div align='right'><table><tr><td>".html_writer::empty_tag('img', array('src' => get_string('add_icon', 'block_risk_monitor')))."&nbsp;&nbsp;".
                html_writer::link (new moodle_url('new_intervention.php', array('userid' => $USER->id, 'courseid' => $courseid, 'categoryid' => $categoryid, 'from_overview' => 1, 'from_studentid' => $studentid)), "Create a new intervention")
               ."</td></tr></table></div>";

echo $OUTPUT->box_end();
echo "<br><br>";

//////////////
///////////TEMPLATES
////////////////
echo $OUTPUT->box_start();
echo "<b>Intervention templates</b>";
if($templates = $DB->get_records('block_risk_monitor_int_tmp', array('categoryid' => $categoryid))) {
    echo "<table>";       
    foreach($templates as $template) { 
        
        //If not already generated
        if(!$DB->record_exists('block_risk_monitor_int_inst', array('interventiontemplateid' => $template->id, 'studentid' => $studentid))) {

            echo "<tr><td width=100px></td><td width=400px><li>".
                html_writer::link (new moodle_url('view_intervention.php', array('userid' => $USER->id, 'courseid' => $courseid, 'interventionid' => $template->id, 'from_overview' => 1, 'from_studentid' => $studentid, 'from_categoryid' => $categoryid)), $template->name)."<br>&emsp;".
            $template->description."</li></td><td width=100px></td><td>"
                    .html_writer::empty_tag('img', array('src' => get_string('add_icon', 'block_risk_monitor')))."&nbsp;&nbsp;".
     html_writer::link (new moodle_url('view_category_risk.php', array('userid' => $USER->id, 'courseid' => $courseid, 'studentid' => $studentid, 'interventionid' => $template->id, 'categoryid' => $category->id)), "Use this template")."<br>"
                    .html_writer::empty_tag('img', array('src' => get_string('edit_icon2', 'block_risk_monitor')))."&nbsp;&nbsp;".
     html_writer::link (new moodle_url('edit_intervention.php', array('userid' => $USER->id, 'courseid' => $courseid, 'interventionid' => $template->id, 'from_studentid' => $studentid, 'from_categoryid' => $category->id, 'from_overview' => 1)), "Edit before using")
                    ."</td></tr><br>";     
            
      
        }                                  
    }
    echo "</table>";
             
}
else {
    echo "<table><tr><td width=100px></td><td><br>No templates for this category.</td></tr></table>";
}

echo $OUTPUT->box_end();
echo $OUTPUT->footer();