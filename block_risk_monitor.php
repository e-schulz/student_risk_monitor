<?php

class block_risk_monitor extends block_base {

        public static $last_student_update;            //last time the student risks were updated. 
        
	public function init() {
		$this->title = get_string('title','block_risk_monitor');
	}
        
        //Regularly check and make sure the default rules are updated in the database.
        public function cron() {
            //Update default rules
            block_risk_monitor_update_default_rules();
            
            //Update the risks
            //block_risk_monitor_clear_risks(block_risk_monitor::$last_student_update);
            //block_risk_monitor_calculate_risks();
            //block_risk_monitor::$last_student_update = time();
        }
        
        //Where this block is allowed to appear? (Only on the my home page!)
        function applicable_formats() {
            return array('my' => true);
        }
	
        //Don't want teachers to config what is shown in the block.
        function instance_allow_config() {
            return true;
        }
        
        // enable this later if you want admin to be able to disable rules.
        function has_config() {
            return false;
        }
        
        //When the block is created, create the block instance 
        public function instance_create() {
            global $DB, $USER;
            
            $data = new object();
            $data->teacherid = $USER->id;
            $data->dateupdated = time();
            $DB->insert_record('block_risk_monitor_block', $data);
            
            //Check to make sure default rules are loaded
            block_risk_monitor_load_default_rules();
        }        
        
        //When the block is deleted, delete all courses associated with this block, and the block instance itself
        public function instance_delete() {
            global $DB, $USER;
            
            //get the block instace
            $block_instance = $DB->get_record('block_risk_monitor_block', array('teacherid' => $USER->id)); //todo - see if we can store the block id once created?
            $DB->delete_records('block_risk_monitor_course', array('blockid' => $block_instance->id));
            $DB->delete_records('block_risk_monitor_block', array('id' => $block_instance->id));
        }
        
	function get_content() {
	
            
                //check that we are definitely logged in.. and that the user is a teacher..
                global $USER;
                
		//if content is already set, return content
		if ($this->content !== NULL) {
			return $this->content;
		}
                
                //Initialise the content
                $this->content = new stdClass;
                $this->content->footer = '';
                        
		//Create the Overview URL 
                $overview_str = get_string('overview','block_risk_monitor');
                $overview = html_writer::link(
                    new moodle_url('/blocks/risk_monitor/overview.php', array('userid' => $USER->id)),
                    $overview_str
                );
                $this->content->text .= $overview;
                $this->content->text .= "<br>";

                //Settings URL
                $settings_str = get_string('settings','block_risk_monitor');
                $settings = html_writer::link(
                    new moodle_url('/blocks/risk_monitor/individual_settings.php', array('userid' => $USER->id)),
                    $settings_str
                );
                
                $this->content->text .= $settings;
                
                
		return $this->content;
	}
}