<?php

/* 
 * This file represents the form that will be used to change the settings
 * 
 */

require_once($CFG->libdir . '/formslib.php');
require_once('locallib.php');
global $OUTPUT;

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

//Receives two arrays, one containing courses to add, one containing courses to delete, for populating the select boxes.
class individual_settings_form_add_remove_courses extends moodleform {
            
    //This is the form itself
    public function definition() {
        global $OUTPUT;
        
        $mform =& $this->_form;
        
       //Create the options for courses to add
       $options_add = array();
       if(!empty($this->_customdata['courses_to_add'])) {
            $add_courses = $this->_customdata['courses_to_add'];
            foreach($add_courses as $single_course) {
                 $options_add[$single_course->id] = $single_course->fullname;
            }        
       }
       
        //Create the options for courses to delete
       $options_delete = array();
       if(!empty($this->_customdata['courses_to_delete'])) {
            $delete_courses = $this->_customdata['courses_to_delete'];
            foreach($delete_courses as $single_course) {
                 $options_delete[$single_course->id] = $single_course->fullname;
             } 
       }
       
        $table = new html_table();
        $table->attributes['class'] = 'coursestable';

        
        $registered_courses = new html_table_cell();
        $registered_courses->text = html_writer::tag('div', '<b>'.get_string('registered_courses','block_anxiety_teacher').'</b>')
                                    .html_writer::tag('div', html_writer::select($options_delete, 'registered_courses', '', null, array('multiple' => 'multiple', 'size' => 7)));
        
        $centre_buttons = new html_table_cell();
        $centre_buttons->text = "<br>".html_writer::tag('p',html_writer::empty_tag('input', array('value' => $OUTPUT->larrow().''.get_string('add_button','block_anxiety_teacher'), 'type' => 'submit', 'id' => 'add_button')))
                                .html_writer::tag('p',html_writer::empty_tag('input', array('value' => get_string('delete_button','block_anxiety_teacher').''.$OUTPUT->rarrow(), 'type' => 'submit', 'id' => 'delete_button')))
                                .html_writer::tag('p',html_writer::empty_tag('input', array('value' => get_string('add_all_button','block_anxiety_teacher'), 'type' => 'submit', 'id' => 'add_all_button')))
                                .html_writer::tag('p',html_writer::empty_tag('input', array('value' => get_string('delete_all_button','block_anxiety_teacher'), 'type' => 'submit', 'id' => 'delete_all_button')));
                                
        $unregistered_courses = new html_table_cell();
        $unregistered_courses->text = html_writer::tag('div', '<b>'.get_string('unregistered_courses','block_anxiety_teacher').'</b>')
                                    .html_writer::tag('div', html_writer::select($options_add, 'unregistered_courses', '', null, array('multiple' => 'multiple', 'size' => 7)));
   
        $table->data[] = new html_table_row(array($registered_courses, $centre_buttons, $unregistered_courses));
        $mform->addElement('static', 'selectors', '', html_writer::table($table));

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