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
$courseid = optional_param('courseid', -1, PARAM_INT);              

//Error- there is no user associated with the passed param
if (!$getuser = $DB->get_record('user', array('id' => $userid))) {
    print_error('no_user', 'block_risk_monitor', '', $userid);
}

//Error - the user trying to access this instance is the wrong one
if (!($USER->id == $userid)) {
    print_error('wrong_user', 'block_risk_monitor', '', $userid);
}
        
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

$body .= html_writer::link (new moodle_url('new_category.php', array('userid' => $USER->id, 'courseid' => $courseid)), get_string('new_category','block_risk_monitor')).'<br><br>';

//Get all the categories and courses.
if($courseid !== -1) {
    $categories_rules_form = new individual_settings_form_edit_categories_rules('edit_categories_rules.php?userid='.$USER->id.'&courseid='.$courseid, array('courseid' => $courseid)); 
}       
        '<form action="http://localhost/filter/manage.php" method="post"><div><input type="hidden" name="sesskey" value="3feqmstECk" /><input type="hidden" name="contextid" value="23" /><table class="admintable generaltable" id="frontpagefiltersettings">
<thead>
<tr>
<th class="header c0 leftalign" style="" scope="col">Filter</th>
<th class="header c1 lastcol leftalign" style="" scope="col">Active?</th>
</tr>
</thead>
<tbody><tr class="r0">
<td class="leftalign cell c0" style="">Activity names auto-linking</td>
<td class="leftalign cell c1 lastcol" style=""><label class="accesshide" for="menuactivitynames">0</label><select id="menuactivitynames" class="select menuactivitynames" name="activitynames"><option selected="selected" value="0">Default (On)</option><option value="-1">Off</option><option value="1">On</option></select></td>
</tr>
<tr class="r1 lastrow">
<td class="leftalign cell c0" style="">Multimedia plugins</td>
<td class="leftalign cell c1 lastcol" style=""><label class="accesshide" for="menumediaplugin">0</label><select id="menumediaplugin" class="select menumediaplugin" name="mediaplugin"><option selected="selected" value="0">Default (On)</option><option value="-1">Off</option><option value="1">On</option></select></td>
</tr>
</tbody>
</table>
<div class="buttons"><input type="submit" name="savechanges" value="Save changes" /></div></div></form>';
        
///RENDERING THE HTML
if ($courseid !== -1) {
    
}
//Render the HTML
echo $OUTPUT->header();
echo $OUTPUT->heading($blockname);


//echo html_writer::start_tag('div', array('class' => 'no-overflow'));

//display the settings form
//echo block_risk_monitor_get_tabs_html($userid, true);
echo block_risk_monitor_get_top_tabs('settings');
echo $OUTPUT->heading("Categories and rules");
echo $body;
if ($courseid !== -1) {
    $categories_rules_form->display();
}
echo $OUTPUT->footer();