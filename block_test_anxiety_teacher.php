<?php

class block_test_anxiety_teacher extends block_base {

	public function init() {
		$this->title = get_string('title','block_test_anxiety_teacher');
	}
        
        //Where this block is allowed to appear? (Only on the my home page!)
        function applicable_formats() {
            return array('my' => true);
        }
	
        //Don't want teachers to config what is shown in the block.
        function instance_allow_config() {
            return false;
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
                $overview_str = get_string('overview','block_test_anxiety_teacher');
                $overview = html_writer::link(
                    new moodle_url('/blocks/test_anxiety_teacher/overview.php', array('userid' => $USER->id)),
                    $overview_str
                );
                $this->content->text .= $overview;
                $this->content->text .= "<br>";

                //Settings URL
                $settings_str = get_string('settings','block_test_anxiety_teacher');
                $settings = html_writer::link(
                    new moodle_url('/blocks/test_anxiety_teacher/settings.php', array('userid' => $USER->id)),
                    $settings_str
                );
                
                $this->content->text .= $settings;
                
                
		return $this->content;
	}
}