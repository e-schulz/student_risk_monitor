<?php

class block_test_anxiety_teacher extends block_base {

	public function init() {
	
		$this->title = get_string('title','block_test_anxiety_teacher');
		
	}
	
	function get_content() {
	
		//if content is already set, return content
		if ($this->content !== NULL) {
			return $this->content;
		}
		
		//get the system context instance
		$context = get_context_instance(CONTEXT_SYSTEM);
		
		//if the user is a teacher or nonediting teacher
		if (has_capability('block/test_anxiety_teacher:view', $context)) {
	
			//create content
			$this->content = new stdClass;	
			$this->content->text = 'Content!';
			$this->content->footer = 'Footer!';
			return $this->content;
		}
		
		
	}
}