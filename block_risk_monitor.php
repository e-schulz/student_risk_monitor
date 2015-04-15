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
 * A block used to assist teachers in identifying students who are exhibiting certain online behaviours 
 * and provide targeted interventions
 *
 * The block has two seperate views - one for students, and one for teachers
 * 
 * See README.md for more information
 * 
 * @package    risk_monitor
 * @copyright  Emily Schulz
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 */

class block_risk_monitor extends block_base {
        
	public function init() {
            
            //Set the title of the block depending on whether we are showing the teacher interface or student interface
            global $COURSE;
            $context = context_course::instance($COURSE->id);
            if(has_capability('block/risk_monitor:teacherview', $context)) {
                $this->title = get_string('title','block_risk_monitor');
            }
            else {
               $this->title = get_string('studenttitle', 'block_risk_monitor');
            }
	}
        
        public function cron() {
            
            //We use the Moodle cron() function to regularly update the risk scores
            require_once("locallib.php");
            risks_controller::calculate_risks();
            return true;
        }
        
        function applicable_formats() {
            
            //Enable this block to appear only on the homepage of the course.
            return array('course-view' => true);
        }
	
        function instance_allow_config() {
            return false;
        }
        
        function has_config() {
            return false;
        }
        
        public function instance_create() {
            
            global $DB, $COURSE;
            
            //Add a new course record 
            //this is required so when the global cron function is called, it can determine which courses to calculate risks for.
            $new_course = new object();
            $new_course->courseid = $COURSE->id;
            $new_course->fullname = $COURSE->fullname;
            $new_course->shortname = $COURSE->shortname;
            $DB->insert_record('block_risk_monitor_course', $new_course);
        }        
        
        public function instance_delete() {
            
            global $DB, $COURSE;
            
            //Delete only one record associated with this course - if multiple teachers have added this block there will be multiple course records.
            if($course_records = $DB->get_records('block_risk_monitor_course', array('courseid' => $COURSE->id))) {
                $first_record = reset($course_records);
                $DB->delete_records('block_risk_monitor_course', array('id' => $first_record->id));
            }
        }
        
        //Put together the HTML to be shown in the block.
	function get_content() {
            
                global $USER, $COURSE, $OUTPUT;
                require_once('locallib.php');
                
		//if content is already initialised, return content
		if ($this->content !== NULL) {
			return $this->content;
		}
                
                //Initialise the content
                $this->content = new stdClass;
                $this->content->footer = '';
                        
                //Determine whether we should be showing the teacher or student view
                $context = context_course::instance($COURSE->id);
                $teacher_view = has_capability('block/risk_monitor:teacherview', $context);
                $student_view = has_capability('block/risk_monitor:studentview', $context);
                $icon_class = array('class' => 'icon');
                
                if($teacher_view) {
                    //Create the Overview URL 
                    $overview_str = get_string('overview','block_risk_monitor');
                    $overview = html_writer::link(
                        new moodle_url('/blocks/risk_monitor/teacher_block/overview.php', array('userid' => $USER->id, 'courseid' => $COURSE->id)),
                        $overview_str
                    );
                    $this->content->text = $overview."<br>";
                    //$this->content->icons[] = $OUTPUT->pix_icon('i/preview', 'preview_icon', 'moodle', $icon_class);

                    //Settings URL
                    $settings_str = get_string('settings','block_risk_monitor');
                    $settings = html_writer::link(
                        new moodle_url('/blocks/risk_monitor/teacher_block/individual_settings.php', array('userid' => $USER->id, 'courseid' => $COURSE->id)),
                        $settings_str
                    );

                    $this->content->text .= $settings;
                    //$this->content->icons[] = $OUTPUT->pix_icon('i/settings', 'settings_icon', 'moodle', $icon_class);                    
                }
                
                if($student_view) {
             
                    if(($content = block_risk_monitor_generate_student_view($USER->id, $COURSE->id)) != '') {
                        $this->content->text .= $content;
                    }
                }
                
		return $this->content;
	}
}