<?php

/* 
 * This file represents the form that will be used to change the settings
 * 
 */

require_once($CFG->libdir . '/formslib.php');
require_once('locallib.php');

class individual_settings_form extends moodleform {
        
    //This is the form itself
    public function definition() {
   
        global $block_anxiety_teacher_config;
        
        $mform =& $this->_form;
        $options = array('1' => 1,
                         '2' => 2,
                         '3' => 3,
                         '4' => 4,
                         '5' => 5,
                         '6' => 6,
                         '7' => 7,
                         '8' => 8,
                         '9' => 9,
                         '10' => 10,
                         '11' => 11,
                         '12' => 12,
                         '13' => 13,
                         '14' => 14,
                         '15' => 15,
                         '16' => 16,
                         '17' => 17,
                         '18' => 18,
                         '19' => 19,
                         '20' => 20);
        $mform->addElement('select', 'numberdays', get_string('daysbeforeexam', 'block_anxiety_teacher'), $options);
        $mform->setDefault('numberdays', ($block_anxiety_teacher_config->timebeforeexam)/(60*60*24));        
        $mform->addElement('submit', 'save', get_string('save', 'block_anxiety_teacher'));


    }
    
}