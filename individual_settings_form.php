<?php

/* 
 * This file represents the form that will be used to change the settings
 * 
 */

require_once($CFG->libdir . '/formslib.php');
require_once('locallib.php');

//Recieves a list of all unregistered courses taught by the teacher (ie, courses that are able to be added)
class individual_settings_form_add_course extends moodleform {
        
    //This is the form itself
    public function definition() {
   
        $mform =& $this->_form;
    
        if(!empty($this->_customdata['courses'])) {
        
            //Add courses
            $options_add = array();
            $all_courses = $this->_customdata['courses'];
            foreach($all_courses as $single_course) {
                $options_add[$single_course->id] = $single_course->fullname;
            }
            $mform->addElement('select', 'add_course', get_string('add_course', 'block_anxiety_teacher'), $options_add);

            $mform->addElement('submit', 'submit_add', get_string('submit_add', 'block_anxiety_teacher'));
        }
    }
    
}

//Received an array containing all the courses able to be deleted (registered courses)
class individual_settings_form_remove_course extends moodleform {
        
    //This is the form itself
    public function definition() {
   
        $mform =& $this->_form;
        
        //Delete courses
        if(!empty($this->_customdata['courses'])) {
            $courses = $this->_customdata['courses'];
            $options_delete = array();
            foreach ($courses as $course) {
                $options_delete[$course->courseid] = $course->fullname;
            }
            $mform->addElement('select', 'delete_course', get_string('delete_course', 'block_anxiety_teacher'), $options_delete);
            $mform->addElement('submit', 'submit_delete', get_string('submit_delete', 'block_anxiety_teacher'));           
        }

    }
    
}

//Takes preamble text
class individual_settings_form_edit_preamble extends moodleform {
        
    //This is the form itself
    public function definition() {
   
        $mform =& $this->_form;
        
        //Delete courses
        if(!empty($this->_customdata['preamble'])) {
            $preamble = $this->_customdata['preamble'];
            //$mform->addElement('editor', 'preamble', get_string('preamble_textbox', 'block_anxiety_teacher'));
            //$mform->setType('fieldname', PARAM_RAW);
            
            $mform->addElement('textarea', 'preamble', get_string('preamble_textbox', 'block_anxiety_teacher'), 'wrap="virtual" rows="5" cols="100"');
            $mform->setDefault('preamble', $preamble);
            $mform->addElement('submit', 'submit_preamble', get_string('save', 'block_anxiety_teacher'));           
        }

    }
    
}

//Takes postamble text
class individual_settings_form_edit_postamble extends moodleform {
        
    //This is the form itself
    public function definition() {
   
        $mform =& $this->_form;
        
        //Delete courses
        if(!empty($this->_customdata['postamble'])) {
            $postamble = $this->_customdata['postamble'];
            
            $mform->addElement('textarea', 'postamble', get_string('postamble_textbox', 'block_anxiety_teacher'), 'wrap="virtual" rows="5" cols="100"');
            $mform->setDefault('postamble', $postamble);
            $mform->addElement('submit', 'submit_postamble', get_string('save', 'block_anxiety_teacher'));           
        }

    }
    
}