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

//$message = optional_param('message', 0, PARAM_INT);
//$courseid = optional_param('courseid', 0, PARAM_INT);
$deletecategoryid = optional_param('categoryid', -1, PARAM_INT);
$deleteruleid = optional_param('ruleid', -1, PARAM_INT);

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
if($deletecategoryid !== -1) {
    
    //Delete category
    if($DB->record_exists('block_risk_monitor_category', array('id' => $deletecategoryid))) {
        $DB->delete_records('block_risk_monitor_category', array('id' => $deletecategoryid));
    }    
    
    //Delete all rules associated with a category
    if($DB->record_exists('block_risk_monitor_rule_inst', array('categoryid' => $deletecategoryid))) {
        $rule_insts = $DB->get_records('block_risk_monitor_rule_inst', array('categoryid' => $deletecategoryid));
        foreach($rule_insts as $rule_inst) {
            
           // if($DB->record_exists('block_risk_monitor_rule_risk', array('ruleid' => $rule_inst->id))) {
             //   $DB->delete_records('block_risk_monitor_rule_risk', array('ruleid' => $rule_inst->id)); 
            //}
        }
        $DB->delete_records('block_risk_monitor_rule_inst', array('categoryid' => $deletecategoryid));        
    }
    
    //Delete all cat risks assoc with this category
    //if($DB->record_exists('block_risk_monitor_cat_risk', array('categoryid' => $categoryid))) {
      //  $DB->delete_records('block_risk_monitor_cat_risk', array('categoryid' => $categoryid));        
    //}    
}
else if ($deleteruleid !== -1) {
    
    //Delete record and readjust weightings.
    if($rule_to_delete = $DB->get_record('block_risk_monitor_rule_inst', array('id' => $deleteruleid))) {
        $rules = block_risk_monitor_get_rules($rule_to_delete->categoryid);
        $old_sum = 0;
        foreach($rules as $rule) {
            $old_sum += $rule->weighting;
        }
        $old_sum = $old_sum - intval($rule_to_delete->weighting);
        $DB->delete_records('block_risk_monitor_rule_inst', array('id' => $deleteruleid));
        $body .= block_risk_monitor_adjust_weightings_rule_deleted($rule_to_delete->categoryid, $old_sum);    

        if($DB->record_exists('block_risk_monitor_rule_risk', array('ruleid' => $rule_to_delete->id))) {
            $DB->delete_records('block_risk_monitor_rule_risk', array('ruleid' => $rule_to_delete->id));        
        }          
    }
   
}

$back_to_settings = html_writer::link (new moodle_url('individual_settings.php', array('userid' => $USER->id, 'courseid' => $courseid)), get_string('back_to_settings','block_risk_monitor'));
$context = context_user::instance($userid);

//Set the page parameters
$blockname = get_string('pluginname', 'block_risk_monitor');
$header = get_string('settings', 'block_risk_monitor');

$PAGE->navbar->add($blockname);
$PAGE->navbar->add($header);

$PAGE->set_context($context);
$PAGE->set_title($blockname . ': '. $header);
$PAGE->set_heading($blockname . ': '.$header);
$PAGE->set_url('/blocks/risk_monitor/edit_categories_rules.php?userid='.$userid.'&courseid='.$courseid);
$PAGE->set_pagetype($blockname);
$PAGE->set_pagelayout('standard');

$add_category = html_writer::link (new moodle_url('new_category.php', array('userid' => $USER->id, 'courseid' => $courseid/*, 'courseid' => $this->_customdata['courseid']*/)), "Create a new category");

$links = $add_category." | ".$back_to_settings."<br><br>";
//Get all the categories and courses.
$categories_rules_form = new individual_settings_form_edit_categories_rules('edit_categories_rules.php?userid='.$USER->id.'&courseid='.$courseid, array('courseid' => $courseid)/*.'&courseid='.$courseid, array('courseid' => $courseid)*/); 
       


//Render the HTML
echo $OUTPUT->header();
echo $OUTPUT->heading($blockname);
echo block_risk_monitor_get_top_tabs('settings', $courseid);
echo $OUTPUT->heading("Categories and rules");
echo $links;

/////   CATEGORIES  ////
if($categories = $DB->get_records('block_risk_monitor_category', array('courseid' => $courseid))) {
                
    foreach($categories as $category) {
        echo $OUTPUT->box_start();
        
        //Category name
        echo "<b>".$category->name."</b><br>";
        
        //Description
        if($category->description != "") {
            echo $category->description."<br>";
        }
        
        //Edit icon
        echo html_writer::start_tag('a', array('href' => 'edit_category.php?userid='.$USER->id.'&courseid='.$courseid.'&categoryid='.$category->id)).
                                html_writer::empty_tag('img', array('src' => get_string('edit_icon', 'block_risk_monitor'), 'align' => 'middle')).
                                html_writer::end_tag('a')."&nbsp;";
        
        //Rules
        if($rules = $DB->get_records('block_risk_monitor_rule_inst', array('categoryid' => $category->id))) {
            echo "<table><tr><td width=100px></td><td width=500px></td><td><b>Weighting</b></td></tr>";
            foreach($rules as $rule) {

                if(intval($rule->ruletype) == 1) {
                    $custom = -1;
                }
                else if(intval($rule->ruletype) == 2) {
                    $custom = 1;
                }
               
                //Rule name
                echo "<tr><td></td><td>".html_writer::empty_tag('img', array('src' => "../../pix/i/risk_xss.png"))."&nbsp;".
                        html_writer::link (new moodle_url('view_rule.php', array('userid' => $USER->id, 'courseid' => $courseid, 'ruleid' => $rule->id)), $rule->name)."<br>&emsp;".
                        $rule->description."</td><td>".$rule->weighting."%</td></tr>";
                             
            }                        
        }
        else {
            echo "<table><tr><td width=100px></td><td width=500px>No rules</td><td><b></b></td></tr>";
        }
        
        echo "</table>";
        
        //Add rule
        echo "<div align='right'><table><tr><td>".html_writer::empty_tag('img', array('src' => get_string('add_icon', 'block_risk_monitor')))."&nbsp;&nbsp;".
                html_writer::link (new moodle_url('new_rule.php', array('userid' => $USER->id, 'courseid' => $courseid, 'categoryid' => $category->id)), "Add a rule")."<br>";
        
        //Add questionnaire
         echo html_writer::empty_tag('img', array('src' => get_string('add_icon', 'block_risk_monitor')))."&nbsp;&nbsp;".
                html_writer::link (new moodle_url('create_custom_rule.php', array('userid' => $USER->id, 'courseid' => $courseid, 'categoryid' => $category->id)), "Add a questionnaire").
                "</td></tr></table></div>";       
        
        echo $OUTPUT->box_end();
    }
}

echo $OUTPUT->footer();