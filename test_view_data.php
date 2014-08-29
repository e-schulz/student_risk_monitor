<?php

// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Prints a page of the survey
 *
 * You can have a rather longer description of the file as well,
 * if you like, and it can span multiple lines.
 *
 * @package    mod_honourssurvey
 * @copyright  2011 Your Name
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/locallib.php');


require_login();
//PAGE PARAMS
$blockname = get_string('pluginname', 'block_risk_monitor');
$header = get_string('overview', 'block_risk_monitor');

//need block id! get block instance - for now we will do user :-)
$context = context_user::instance($USER->id);

$PAGE->navbar->add($blockname);
$PAGE->navbar->add($header);

$PAGE->set_context($context);
$PAGE->set_title($blockname . ': '. $header);
$PAGE->set_heading($blockname . ': '.$header);
$PAGE->set_url('/blocks/risk_monitor/test_view_data.php');
$PAGE->set_pagetype($blockname);
$PAGE->set_pagelayout('standard');

//$DB->delete_records('block_risk_monitor_rule_inst');

$body = '';
 
//Pretest
$body .= "<div><b>Rules (for this block)</b><br><br>";

if($pretest_instances = $DB->get_records('block_risk_monitor_rule_inst')) {
            
            $table = new html_table();
            $headers = array();
            
            $field1 = new html_table_cell();
            $field1->text = '<b>ID</b>';
            $headers[] = $field1;
                    
            $field2 = new html_table_cell();
            $field2->text = '<b>name</b>';
            $headers[] = $field2;
            
            $field3 = new html_table_cell();
            $field3->text = '<b>description</b>';
            $headers[] = $field3;

            $field4 = new html_table_cell();
            $field4->text = '<b>weighting</b>';
            $headers[] = $field4;
            
            $field6 = new html_table_cell();
            $field6->text = '<b>categoryid</b>';
            $headers[] = $field6;
                                
            $field7 = new html_table_cell();
            $field7->text = '<b>timestamp</b>';
            $headers[] = $field7;

            $field8 = new html_table_cell();
            $field8->text = '<b>ruletype</b>';
            $headers[] = $field8;
                                
            $field9 = new html_table_cell();
            $field9->text = '<b>value</b>';
            $headers[] = $field9;
            
            $table->data[] = new html_table_row($headers);
            
            //header.
            foreach($pretest_instances as $pretest_instance) {
                
                //get the user                        
                $instancerow = array();

                $field1value = new html_table_cell();
                $field1value->text = $pretest_instance->id;
                $instancerow[] = $field1value;
                
                $field2value = new html_table_cell();
                $field2value->text = $pretest_instance->name;
                $instancerow[] = $field2value;
                
                $field3value = new html_table_cell();
                $field3value->text = $pretest_instance->description;
                $instancerow[] = $field3value;

                $field4value = new html_table_cell();
                $field4value->text = $pretest_instance->weighting;
                $instancerow[] = $field4value;
                
                $field6value = new html_table_cell();
                $field6value->text = $pretest_instance->categoryid;
                $instancerow[] = $field6value;
                
                $field7value = new html_table_cell();
                $field7value->text = $pretest_instance->timestamp;
                $instancerow[] = $field7value;
                
                $field8value = new html_table_cell();
                $field8value->text = $pretest_instance->ruletype;
                $instancerow[] = $field8value;
                
                $field9value = new html_table_cell();
                $field9value->text = $pretest_instance->value;
                $instancerow[] = $field9value;
                
                 
                $table->data[] = new html_table_row($instancerow);               

            }
            
            $body .= html_writer::table($table);
            $body .= "<br><br></div>";
}

//Hypothetical
$body .= "<div><b>Categories (for this block)</b><br><br>";

if($hypothetical_instances = $DB->get_records('block_risk_monitor_category')) {
            
            $table = new html_table();
            $headers = array();
            
            $field1 = new html_table_cell();
            $field1->text = '<b>ID</b>';
            $headers[] = $field1;
                    
            $field2 = new html_table_cell();
            $field2->text = '<b>name</b>';
            $headers[] = $field2;
            
            $field3 = new html_table_cell();
            $field3->text = '<b>description</b>';
            $headers[] = $field3;
            
            $field4 = new html_table_cell();
            $field4->text = '<b>courseid</b>';
            $headers[] = $field4;
                    
            $field5 = new html_table_cell();
            $field5->text = '<b>timestamp</b>';
            $headers[] = $field5;
 
            $table->data[] = new html_table_row($headers);
            
            //header.
            foreach($hypothetical_instances as $hypothetical_instance) {
                
                //get the user                        
                $instancerow = array();

                $field1value = new html_table_cell();
                $field1value->text = $hypothetical_instance->id;
                $instancerow[] = $field1value;
                
                $field2value = new html_table_cell();
                $field2value->text = $hypothetical_instance->name;
                $instancerow[] = $field2value;
                
                $field3value = new html_table_cell();
                $field3value->text = $hypothetical_instance->description;
                $instancerow[] = $field3value;

                $field4value = new html_table_cell();
                $field4value->text = $hypothetical_instance->courseid;
                $instancerow[] = $field4value;
                
                $field5value = new html_table_cell();
                $field5value->text = $hypothetical_instance->timestamp;
                $instancerow[] = $field5value;
               
                $table->data[] = new html_table_row($instancerow);               

            }
            
            $body .= html_writer::table($table);
            $body .= "<br><br></div>";
}

$body .= "<div><b>Custom rules</b><br><br>";

if($hypothetical_instances = $DB->get_records('block_risk_monitor_cust_rule')) {
            
            $table = new html_table();
            $headers = array();
            
            $field1 = new html_table_cell();
            $field1->text = '<b>ID</b>';
            $headers[] = $field1;
                    
            $field2 = new html_table_cell();
            $field2->text = '<b>name</b>';
            $headers[] = $field2;
            
            $field3 = new html_table_cell();
            $field3->text = '<b>description</b>';
            $headers[] = $field3;
            
            $field4 = new html_table_cell();
            $field4->text = '<b>userid</b>';
            $headers[] = $field4;
                    
            $field5 = new html_table_cell();
            $field5->text = '<b>timestamp</b>';
            $headers[] = $field5;
            
/*            $field6 = new html_table_cell();
            $field6->text = '<b>enabled</b>';
            $headers[] = $field6;
                    
            $field7 = new html_table_cell();
            $field7->text = '<b>userid</b>';
            $headers[] = $field7; 

            $field8 = new html_table_cell();
            $field8->text = '<b>value_required</b>';
            $headers[] = $field8;
                    
            $field9 = new html_table_cell();
            $field9->text = '<b>value_description</b>';
            $headers[] = $field9; */
            $table->data[] = new html_table_row($headers);
            
            //header.
            foreach($hypothetical_instances as $hypothetical_instance) {
                
                //get the user                        
                $instancerow = array();

                $field1value = new html_table_cell();
                $field1value->text = $hypothetical_instance->id;
                $instancerow[] = $field1value;
                
                $field2value = new html_table_cell();
                $field2value->text = $hypothetical_instance->name;
                $instancerow[] = $field2value;
                
                $field3value = new html_table_cell();
                $field3value->text = $hypothetical_instance->description;
                $instancerow[] = $field3value;

                $field4value = new html_table_cell();
                $field4value->text = $hypothetical_instance->userid;
                $instancerow[] = $field4value;
                
                $field5value = new html_table_cell();
                $field5value->text = $hypothetical_instance->timestamp;
                $instancerow[] = $field5value;
               
/*                $field6value = new html_table_cell();
                $field6value->text = $hypothetical_instance->enabled;
                $instancerow[] = $field6value;
                
                $field7value = new html_table_cell();
                $field7value->text = $hypothetical_instance->userid;
                $instancerow[] = $field7value;

                $field8value = new html_table_cell();
                $field8value->text = $hypothetical_instance->value_required;
                $instancerow[] = $field8value;
                
                $field9value = new html_table_cell();
                $field9value->text = $hypothetical_instance->value_description;
                $instancerow[] = $field9value;*/
                               
                $table->data[] = new html_table_row($instancerow);               

            }
            
            $body .= html_writer::table($table);
            $body .= "<br><br></div>";
}

$body .= "<div><b>Questions</b><br><br>";

if($hypothetical_instances = $DB->get_records('block_risk_monitor_question')) {
            
            $table = new html_table();
            $headers = array();
            
            $field1 = new html_table_cell();
            $field1->text = '<b>ID</b>';
            $headers[] = $field1;
                    
            $field2 = new html_table_cell();
            $field2->text = '<b>question</b>';
            $headers[] = $field2;
            
            $field3 = new html_table_cell();
            $field3->text = '<b>custruleid</b>';
            $headers[] = $field3;
            
            $table->data[] = new html_table_row($headers);
            
            //header.
            foreach($hypothetical_instances as $hypothetical_instance) {
                
                //get the user                        
                $instancerow = array();

                $field1value = new html_table_cell();
                $field1value->text = $hypothetical_instance->id;
                $instancerow[] = $field1value;
                
                $field2value = new html_table_cell();
                $field2value->text = $hypothetical_instance->question;
                $instancerow[] = $field2value;
                
                $field3value = new html_table_cell();
                $field3value->text = $hypothetical_instance->custruleid;
                $instancerow[] = $field3value;;
               
                $table->data[] = new html_table_row($instancerow);               

            }
            
            $body .= html_writer::table($table);
            $body .= "<br><br></div>";
}

$body .= "<div><b>Options</b><br><br>";

if($hypothetical_instances = $DB->get_records('block_risk_monitor_option')) {
            
            $table = new html_table();
            $headers = array();
            
            $field1 = new html_table_cell();
            $field1->text = '<b>ID</b>';
            $headers[] = $field1;
                    
            $field2 = new html_table_cell();
            $field2->text = '<b>label</b>';
            $headers[] = $field2;
            
            $field3 = new html_table_cell();
            $field3->text = '<b>value</b>';
            $headers[] = $field3;
            
            $field4 = new html_table_cell();
            $field4->text = '<b>questionid</b>';
            $headers[] = $field4;
                    
 
            $table->data[] = new html_table_row($headers);
            
            //header.
            foreach($hypothetical_instances as $hypothetical_instance) {
                
                //get the user                        
                $instancerow = array();

                $field1value = new html_table_cell();
                $field1value->text = $hypothetical_instance->id;
                $instancerow[] = $field1value;
                
                $field2value = new html_table_cell();
                $field2value->text = $hypothetical_instance->label;
                $instancerow[] = $field2value;
                
                $field3value = new html_table_cell();
                $field3value->text = $hypothetical_instance->value;
                $instancerow[] = $field3value;

                $field4value = new html_table_cell();
                $field4value->text = $hypothetical_instance->questionid;
                $instancerow[] = $field4value;
               
                $table->data[] = new html_table_row($instancerow);               

            }
            
            $body .= html_writer::table($table);
            $body .= "<br><br></div>";
}

$body .= "<div><b>Risk instances</b><br><br>";

if($hypothetical_instances = $DB->get_records('block_risk_monitor_rule_risk')) {
            
            $table = new html_table();
            $headers = array();
            
            $field1 = new html_table_cell();
            $field1->text = '<b>ID</b>';
            $headers[] = $field1;
                    
            $field2 = new html_table_cell();
            $field2->text = '<b>userid</b>';
            $headers[] = $field2;
            
            $field3 = new html_table_cell();
            $field3->text = '<b>ruleid</b>';
            $headers[] = $field3;
            
            $field4 = new html_table_cell();
            $field4->text = '<b>value</b>';
            $headers[] = $field4;
                    
            $field5 = new html_table_cell();
            $field5->text = '<b>timestamp</b>';
            $headers[] = $field5;
 
            $table->data[] = new html_table_row($headers);
            
            //header.
            foreach($hypothetical_instances as $hypothetical_instance) {
                
                //get the user                        
                $instancerow = array();

                $field1value = new html_table_cell();
                $field1value->text = $hypothetical_instance->id;
                $instancerow[] = $field1value;
                
                $field2value = new html_table_cell();
                $field2value->text = $hypothetical_instance->userid;
                $instancerow[] = $field2value;
                
                $field3value = new html_table_cell();
                $field3value->text = $hypothetical_instance->ruleid;
                $instancerow[] = $field3value;

                $field4value = new html_table_cell();
                $field4value->text = $hypothetical_instance->value;
                $instancerow[] = $field4value;
                
                $field5value = new html_table_cell();
                $field5value->text = $hypothetical_instance->timestamp;
                $instancerow[] = $field5value;
               
                $table->data[] = new html_table_row($instancerow);               

            }
            
            $body .= html_writer::table($table);
            $body .= "<br><br></div>";
}
$body .= "<div><b>Category instances</b><br><br>";

if($hypothetical_instances = $DB->get_records('block_risk_monitor_cat_risk')) {
            
            $table = new html_table();
            $headers = array();
            
            $field1 = new html_table_cell();
            $field1->text = '<b>ID</b>';
            $headers[] = $field1;
                    
            $field2 = new html_table_cell();
            $field2->text = '<b>userid</b>';
            $headers[] = $field2;
            
            $field3 = new html_table_cell();
            $field3->text = '<b>categoryid</b>';
            $headers[] = $field3;
            
            $field4 = new html_table_cell();
            $field4->text = '<b>value</b>';
            $headers[] = $field4;
                    
            $field5 = new html_table_cell();
            $field5->text = '<b>timestamp</b>';
            $headers[] = $field5;
 
            $table->data[] = new html_table_row($headers);
            
            //header.
            foreach($hypothetical_instances as $hypothetical_instance) {
                
                //get the user                        
                $instancerow = array();

                $field1value = new html_table_cell();
                $field1value->text = $hypothetical_instance->id;
                $instancerow[] = $field1value;
                
                $field2value = new html_table_cell();
                $field2value->text = $hypothetical_instance->userid;
                $instancerow[] = $field2value;
                
                $field3value = new html_table_cell();
                $field3value->text = $hypothetical_instance->categoryid;
                $instancerow[] = $field3value;

                $field4value = new html_table_cell();
                $field4value->text = $hypothetical_instance->value;
                $instancerow[] = $field4value;
                
                $field5value = new html_table_cell();
                $field5value->text = $hypothetical_instance->timestamp;
                $instancerow[] = $field5value;
               
                $table->data[] = new html_table_row($instancerow);               

            }
            
            $body .= html_writer::table($table);
            $body .= "<br><br></div>";
}

$body .= "<div><b>Answers</b><br><br>";

if($hypothetical_instances = $DB->get_records('block_risk_monitor_answer')) {
            
            $table = new html_table();
            $headers = array();
            
            $field1 = new html_table_cell();
            $field1->text = '<b>ID</b>';
            $headers[] = $field1;
                    
            $field2 = new html_table_cell();
            $field2->text = '<b>userid</b>';
            $headers[] = $field2;
            
            $field3 = new html_table_cell();
            $field3->text = '<b>questionid</b>';
            $headers[] = $field3;
            
            $field4 = new html_table_cell();
            $field4->text = '<b>optionid</b>';
            $headers[] = $field4;
                    
            $field5 = new html_table_cell();
            $field5->text = '<b>timestamp</b>';
            $headers[] = $field5;
 
            $table->data[] = new html_table_row($headers);
            
            //header.
            foreach($hypothetical_instances as $hypothetical_instance) {
                
                //get the user                        
                $instancerow = array();

                $field1value = new html_table_cell();
                $field1value->text = $hypothetical_instance->id;
                $instancerow[] = $field1value;
                
                $field2value = new html_table_cell();
                $field2value->text = $hypothetical_instance->userid;
                $instancerow[] = $field2value;
                
                $field3value = new html_table_cell();
                $field3value->text = $hypothetical_instance->questionid;
                $instancerow[] = $field3value;

                $field4value = new html_table_cell();
                $field4value->text = $hypothetical_instance->optionid;
                $instancerow[] = $field4value;
                
                $field5value = new html_table_cell();
                $field5value->text = $hypothetical_instance->timestamp;
                $instancerow[] = $field5value;
               
                $table->data[] = new html_table_row($instancerow);               

            }
            
            $body .= html_writer::table($table);
            $body .= "<br><br></div>";
}
$body .= "<div><b>Courses</b><br><br>";

if($hypothetical_instances = $DB->get_records('block_risk_monitor_course')) {
            
            $table = new html_table();
            $headers = array();
            
            $field1 = new html_table_cell();
            $field1->text = '<b>id</b>';
            $headers[] = $field1;

            $field1 = new html_table_cell();
            $field1->text = '<b>courseid</b>';
            $headers[] = $field1;
 
            $table->data[] = new html_table_row($headers);
            
            //header.
            foreach($hypothetical_instances as $hypothetical_instance) {
                
                //get the user                        
                $instancerow = array();

                $field1value = new html_table_cell();
                $field1value->text = $hypothetical_instance->id;
                $instancerow[] = $field1value;
                
                 $field1value = new html_table_cell();
                $field1value->text = $hypothetical_instance->courseid;
                $instancerow[] = $field1value;
                     
               
                $table->data[] = new html_table_row($instancerow);               

            }
            
            $body .= html_writer::table($table);
            $body .= "<br><br></div>";
}
//Posttest
/*$body .= "<div><b>Log (for this block)</b><br><br>";

if($posttest_instances = $DB->get_records('block_risk_monitor_log')) {
            
            $table = new html_table();
            $headers = array();
            
            $field1 = new html_table_cell();
            $field1->text = '<b>ID</b>';
            $headers[] = $field1;
                    
            $field2 = new html_table_cell();
            $field2->text = '<b>teacherid</b>';
            $headers[] = $field2;
            
            $field3 = new html_table_cell();
            $field3->text = '<b>anxietyid</b>';
            $headers[] = $field3;
                    
            $field4 = new html_table_cell();
            $field4->text = '<b>teacheraction</b>';
            $headers[] = $field4;
            
            $field5 = new html_table_cell();
            $field5->text = '<b>dateandtime</b>';
            $headers[] = $field5;
            
            $table->data[] = new html_table_row($headers);
            
            //header.
            foreach($posttest_instances as $posttest_instance) {
                
                //get the user                        
                $instancerow = array();

                $field1value = new html_table_cell();
                $field1value->text = $posttest_instance->id;
                $instancerow[] = $field1value;
                
                $field2value = new html_table_cell();
                $field2value->text = $posttest_instance->teacherid;
                $instancerow[] = $field2value;
                
                $field3value = new html_table_cell();
                $field3value->text = $posttest_instance->anxietyid;
                $instancerow[] = $field3value;
                
                $field4value = new html_table_cell();
                $field4value->text = $posttest_instance->teacheraction;
                $instancerow[] = $field4value;
                
                $field5value = new html_table_cell();
                $field5value->text = $posttest_instance->dateandtime;
                $instancerow[] = $field5value;
                                
                $table->data[] = new html_table_row($instancerow);               

            }
            
            $body .= html_writer::table($table);
            $body .= "<br><br></div>";
}*/


// Output starts here
echo $OUTPUT->header();

echo $body;

// Finish the page
echo $OUTPUT->footer();
