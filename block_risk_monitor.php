<?php

class block_risk_monitor extends block_base {
        
	public function init() {
            global $COURSE;
            $context = context_course::instance($COURSE->id);
            if(has_capability('block/risk_monitor:teacherview', $context)) {
                $this->title = get_string('title','block_risk_monitor');
            }
            else {
               $this->title = get_string('studenttitle', 'block_risk_monitor');
            }
	}
        
        //Regularly update the risks.
        public function cron() {
            risks_controller::calculate_risks();
        }
        
        //Where this block is allowed to appear? (Only on the my home page!)
        function applicable_formats() {
            return array('course-view' => true);
        }
	
        //Don't want teachers to config what is shown in the block.
        function instance_allow_config() {
            return false;
        }
        
        // enable this later if you want admin to be able to disable rules.
        function has_config() {
            return false;
        }
        
        //When the block is created, create the block instance 
        public function instance_create() {
            global $DB, $COURSE;
             //Add this course to the course table.
            if(!($DB->record_exists('block_risk_monitor_course', array('courseid' => $COURSE->id)))) {
                $new_course = new object();
                $new_course->courseid = $COURSE->id;
                $new_course->fullname = $COURSE->fullname;
                $new_course->shortname = $COURSE->shortname;
                $DB->insert_record('block_risk_monitor_course', $new_course);
            }
            
          
              
        }        
        
        //When the block is deleted, delete all courses associated with this block, and the block instance itself
        public function instance_delete() {
            global $DB, $COURSE;
            //Delete course
            if($DB->record_exists('block_risk_monitor_course', array('courseid' => $COURSE->id))) {
                $DB->delete_records('block_risk_monitor_course', array('courseid' => $COURSE->id));
            }
            require_once('locallib.php');
            block_risk_monitor_clear_all_tables();

        }
        
	function get_content() {
	
            
                //check that we are definitely logged in.. and that the user is a teacher..
                global $USER, $COURSE, $OUTPUT;
                require_once('locallib.php');
		//if content is already set, return content
		if ($this->content !== NULL) {
			return $this->content;
		}
                
                //Initialise the content
                $this->content = new stdClass;
                $this->content->footer = '';
                        
                //TEACHER VIEW:
                $context = context_course::instance($COURSE->id);
                $teacher_view = has_capability('block/risk_monitor:teacherview', $context);
                $student_view = has_capability('block/risk_monitor:studentview', $context);
                $icon_class = array('class' => 'icon');
                
                if($teacher_view) {
                    //Create the Overview URL 
                    $overview_str = get_string('overview','block_risk_monitor');
                    $overview = html_writer::link(
                        new moodle_url('/blocks/risk_monitor/overview.php', array('userid' => $USER->id, 'courseid' => $COURSE->id)),
                        $overview_str
                    );
                    $this->content->text = $overview."<br>";
                    //$this->content->icons[] = $OUTPUT->pix_icon('i/preview', 'preview_icon', 'moodle', $icon_class);

                    //Settings URL
                    $settings_str = get_string('settings','block_risk_monitor');
                    $settings = html_writer::link(
                        new moodle_url('/blocks/risk_monitor/individual_settings.php', array('userid' => $USER->id, 'courseid' => $COURSE->id)),
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