<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

///REQUIRES AND ERROR MESSAGES

require_once("../../config.php");
require_once("locallib.php");
require_once("student_risk_monitor_forms.php");

global $DB;

//Teacher must be logged in
require_login();

//Get the ID of the teacher
$userid = required_param('userid', PARAM_INT);
$ruleid = required_param('ruleid', PARAM_INT);
$courseid = required_param('courseid', PARAM_INT);
$editing = optional_param('editing', 0, PARAM_INT);
$message = optional_param('message', -1, PARAM_INT);

/////////
/////ERROR MESSAGES 
////////
if (!$getuser = $DB->get_record('user', array('id' => $userid))) {
    print_error('no_user', 'block_risk_monitor', '', $userid);
}

//Error - the user trying to access this instance is the wrong one
if (!($USER->id == $userid)) {
    print_error('wrong_user', 'block_risk_monitor', '', $userid);
}
        
//Check that the rule exists.
if(!$getrule = $DB->get_record('block_risk_monitor_rule_inst', array('id' => $ruleid))) {
    print_error('no_rule', 'block_risk_monitor', '', $ruleid);
}

$errors = '';
if($message != -1) {
    switch($message){
        CASE 1:
            $errors = get_string('errweightingnotnumeric','block_risk_monitor');
            break;
        CASE 2: 
            $errors = get_string('errweightingnotinrange','block_risk_monitor');
            break;
        CASE 3:
            $errors = "Error: value must be numeric";
            break;
        CASE 4: 
            $errors = "Error: value must be a positive number.";
            break;
        CASE 5:
            $errors = "Error: must insert a value";
            break;
        default:
            break;
    }
}

if($getrule->ruletype == 1) {
    $rule_type = DefaultRules::getDefaultRuleObject($getrule->defaultruleid);
}
else {
    $rule_type = $DB->get_record('block_risk_monitor_cust_rule', array('id' => $getrule->custruleid));
}
$getcategory = $DB->get_record('block_risk_monitor_category', array('id' => $getrule->categoryid));




////////
/////PAGE PARAMETERS
///////////
$context = context_user::instance($userid);

//Set the page parameters
$blockname = get_string('pluginname', 'block_risk_monitor');
$header = get_string('settings', 'block_risk_monitor'); $action = new moodle_url('individual_settings.php', array('userid' => $USER->id, 'courseid' => $courseid));

$PAGE->navbar->add($blockname, new moodle_url('overview.php', array('userid' => $USER->id, 'courseid' => $courseid))); 
$PAGE->navbar->add($header, $action); 

$PAGE->set_context($context);
$PAGE->set_title($blockname . ': '. $header);
$PAGE->set_heading($blockname . ': '.$header);
$PAGE->set_url('/blocks/risk_monitor/view_rule.php?userid='.$userid.'&ruleid='.$ruleid.'&courseid='.$courseid);
$PAGE->set_pagetype($blockname);
$PAGE->set_pagelayout('standard');



/////////
/////CREATE LINKS AND FORMS
//////////
$edit_link = html_writer::link (new moodle_url('edit_rule.php', array('userid' => $USER->id, 'courseid' => $courseid, 'ruleid' => $ruleid)), "Edit");
$delete_link = html_writer::link (new moodle_url('delete_rule.php', array('userid' => $USER->id, 'courseid' => $courseid, 'ruleid' => $ruleid)), "Delete rule");
$back_to_categories = html_writer::link (new moodle_url('edit_categories_rules.php', array('userid' => $USER->id, 'courseid' => $courseid)), "Back to categories");

$links = $delete_link." | ".$back_to_categories."<br><br>";

$general_form = new individual_settings_form_rule_instance('/blocks/risk_monitor/view_rule.php?userid='.$userid.'&ruleid='.$ruleid.'&courseid='.$courseid.'&editing='.$editing, array('editing' => $editing, 'rule_instance' => $getrule, 'rule_type' => $rule_type));



////////////////
//////// GET FORM DATA
////////////    
if($general_form->is_cancelled()) {
    //Redirect to view rule
    redirect(new moodle_url('view_rule.php', array('userid' => $USER->id, 'ruleid' => $ruleid, 'courseid' => $courseid, 'editing' => 0)));
}

if($fromform = $general_form->get_data()) {
    
    $rule_to_update = new object();
    $rule_to_update->id = $ruleid;
    
    ///WEIGHTING
    if(!is_numeric($fromform->weighting)) {
        redirect(new moodle_url('view_rule.php', array('userid' => $USER->id, 'ruleid' => $ruleid, 'message' => 1, 'courseid' => $courseid, 'editing' => $editing)));
    }
    else if(intval($fromform->weighting < 0 || $fromform->weighting > 100)) {
        redirect(new moodle_url('view_rule.php', array('userid' => $USER->id, 'ruleid' => $ruleid, 'message' => 2, 'courseid' => $courseid, 'editing' => $editing)));        
    }
    else {
        if(empty($fromform->weighting)) {
            $fromform->weighting = 0;
        }
        $rule_to_update->weighting = $fromform->weighting;
        block_risk_monitor_adjust_weightings_rule_added($getcategory->id, (100-floatval($fromform->weighting)), $ruleid);
    }
    
    
    ///VALUE
    if($getrule->ruletype == 1 && $rule_type->value_required == 1) {
        if(!is_numeric($fromform->value)) {
            redirect(new moodle_url('view_rule.php', array('userid' => $USER->id, 'ruleid' => $ruleid, 'message' => 3, 'courseid' => $courseid, 'editing' => $editing)));
        }
        else if(intval($fromform->value < 0)) {
            redirect(new moodle_url('view_rule.php', array('userid' => $USER->id, 'ruleid' => $ruleid, 'message' => 4, 'courseid' => $courseid, 'editing' => $editing)));        
        }

        if(empty($fromform->value)) {
            redirect(new moodle_url('view_rule.php', array('userid' => $USER->id, 'ruleid' => $ruleid, 'message' => 5, 'courseid' => $courseid, 'editing' => $editing)));
        }
        $rule_name = DefaultRules::$default_rule_names[$getrule->defaultruleid];
        $rule_name = str_replace("a number of", $fromform->value, $rule_name);
        $rule_name = str_replace("certain cutoff", $fromform->value."%", $rule_name);
        $rule_name = str_replace("number of", $fromform->value, $rule_name);
        $rule_name = str_replace("below average", $fromform->value."% below average", $rule_name);
        $rule_name = str_replace("above average", $fromform->value."% above average", $rule_name);
        $rule_to_update->name = $rule_name;        
        $rule_to_update->value = $fromform->value;
    }
    
    $rule_to_update->timestamp = time();
    $DB->update_record('block_risk_monitor_rule_inst', $rule_to_update);    
    
    
    ////QUESTIONNAIRE VALUES   
    if($getrule->ruletype == 2) {
        $questionnaire_to_update = new object();
        $questionnaire_to_update->id = $getrule->custruleid;
        
        $questionnaire_to_update->name = $fromform->name;
        $questionnaire_to_update->description = $fromform->description;
        
        //Ranges.
        $questionnaire_to_update->low_risk_floor = $fromform->lowrangebegin;
        $questionnaire_to_update->low_risk_ceiling = $fromform->lowrangeend;
        $questionnaire_to_update->med_risk_floor = $fromform->medrangebegin;
        $questionnaire_to_update->med_risk_ceiling = $fromform->medrangeend;
        $questionnaire_to_update->high_risk_floor = $fromform->highrangebegin;
        $questionnaire_to_update->high_risk_ceiling = $fromform->highrangeend;
        
        $questionnaire_to_update->timestamp = time();
        $DB->update_record('block_risk_monitor_cust_rule', $questionnaire_to_update);    
    }
    
    //Delete all risks for this rule
    if($DB->record_exists('block_risk_monitor_rule_risk', array('ruleid' => $getrule->id))) {
        $DB->delete_records('block_risk_monitor_rule_risk', array('ruleid' => $getrule->id));
    }  
    
    //Delete all risks for this category
    if($DB->record_exists('block_risk_monitor_cat_risk', array('categoryid' => $getcategory->id))) {
        $DB->delete_records('block_risk_monitor_cat_risk', array('categoryid' => $getcategory->id));
    }   
    
    //Recalculate risks for this category
    risks_controller::calculate_risks($getcategory->id);
    
    redirect(new moodle_url('view_rule.php', array('userid' => $USER->id, 'ruleid' => $ruleid, 'courseid' => $courseid, 'editing' => 0)));

}




///////////
///// RENDERING THE HTML
////////////

echo $OUTPUT->header();
echo $OUTPUT->heading($blockname);

//echo html_writer::start_tag('div', array('class' => 'no-overflow'));

//display the settings form
//echo block_risk_monitor_get_tabs_html($userid, true);
echo block_risk_monitor_get_top_tabs('settings', $courseid);
echo $OUTPUT->heading($getrule->name);
echo $links;
echo $errors;
////////////////
//GENERAL SECTION
///////////////
echo $OUTPUT->box_start();
echo "<b>General</b><br>";

//Edit icon
echo html_writer::start_tag('a', array('href' => 'view_rule.php?userid='.$USER->id.'&courseid='.$courseid.'&ruleid='.$getrule->id."&editing=1")).
            html_writer::empty_tag('img', array('src' => get_string('edit_icon', 'block_risk_monitor'))).
            html_writer::end_tag('a')."&nbsp;";
        
$general_form->display();
echo $OUTPUT->box_end();
        

////////////////
//////QUESTIONNAIRE PREVIEW SECTION
////////////////

if($getrule->ruletype == 2) {
    echo "<br><br><br>".$OUTPUT->heading("Questionnaire preview", 2);    
    echo $OUTPUT->box_start();
    echo $rule_type->instructions."<br>";
    echo $OUTPUT->box_end();
    if($questions = $DB->get_records('block_risk_monitor_question', array('custruleid' => $getrule->custruleid))) {
        foreach($questions as $question) {
            echo $OUTPUT->box_start();
            echo $question->question."<br>";
            $options = $DB->get_records('block_risk_monitor_option', array('questionid' => $question->id));
            foreach($options as $option) {
                echo "<li>".$option->label."</li>";
            }
            echo "<br>";
            echo $OUTPUT->box_end();
        }
    }    
}

echo $OUTPUT->footer();