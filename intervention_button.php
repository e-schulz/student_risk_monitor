<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

require_once("$CFG->libdir/formslib.php");

class intervention_button extends moodleform {
    
    public function definition() {
        
        $mform = $this->_form;
    
        $mform->addElement('submit', 'interventionbutton', get_string('submitintervention','block_anxiety_teacher'));
        //Change above to a string!

        }
}