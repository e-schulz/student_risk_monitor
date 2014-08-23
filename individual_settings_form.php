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
            $mform->addElement('select', 'add_course', get_string('add_course', 'block_risk_monitor'), $options_add);

            $mform->addElement('submit', 'submit_add', get_string('submit_add', 'block_risk_monitor'));
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
            $mform->addElement('select', 'delete_course', get_string('delete_course', 'block_risk_monitor'), $options_delete);
            $mform->addElement('submit', 'submit_delete', get_string('submit_delete', 'block_risk_monitor'));           
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
            
            $mform->addElement('select', 'add_course', get_string('add_course', 'block_risk_monitor'), $options_add);
            $mform->addElement('submit', 'submit_add', get_string('submit_add', 'block_risk_monitor'));
       }
       
        //Create the options for courses to delete
       $options_delete = array();
       if(!empty($this->_customdata['courses_to_delete'])) {
            $delete_courses = $this->_customdata['courses_to_delete'];
            foreach($delete_courses as $single_course) {
                 $options_delete[$single_course->id] = $single_course->fullname;
             } 
             
             $mform->addElement('select', 'delete_course', get_string('delete_course', 'block_risk_monitor'), $options_delete);
             $mform->addElement('submit', 'submit_delete', get_string('submit_delete', 'block_risk_monitor'));     
       }
       
        //$table = new html_table();
        //$table->attributes['class'] = 'coursestable';

        
        //$registered_courses = html_writer::tag('div', '<b>'.get_string('registered_courses','block_risk_monitor').'</b>')
          //                          .html_writer::tag('div', html_writer::select($options_delete, 'registered_courses', '', '', array('min-width' => 300, 'multiple' => 'multiple', 'size' => 7)));
                    
       //$mform->addElement('static', 'registered_courses', '', $registered_courses);
        
        /*$centre_buttons = new html_table_cell();
        $centre_buttons->text = "<br>".html_writer::tag('p',html_writer::empty_tag('input', array('value' => $OUTPUT->larrow().''.get_string('add_button','block_risk_monitor'), 'type' => 'submit', 'id' => 'add_button')))
                                .html_writer::tag('p',html_writer::empty_tag('input', array('value' => get_string('delete_button','block_risk_monitor').''.$OUTPUT->rarrow(), 'type' => 'submit', 'id' => 'delete_button')))
                                .html_writer::tag('p',html_writer::empty_tag('input', array('value' => get_string('add_all_button','block_risk_monitor'), 'type' => 'submit', 'id' => 'add_all_button')))
                                .html_writer::tag('p',html_writer::empty_tag('input', array('value' => get_string('delete_all_button','block_risk_monitor'), 'type' => 'submit', 'id' => 'delete_all_button')));
                              */
        
        //$unregistered_courses = html_writer::tag('div', '<b>'.get_string('unregistered_courses','block_risk_monitor').'</b>')
          //                          .html_writer::tag('div', html_writer::select($options_add, 'unregistered_courses', '', '', array('min-width' => 300, 'multiple' => 'multiple', 'size' => 7)));
   
        //$table->data[] = new html_table_row(array($registered_courses, $centre_buttons, $unregistered_courses));
          //$mform->addElement('static', 'unregistered_courses', '', $unregistered_courses);
      
        
        //$mform->addElement('static', 'selectors', '', html_writer::table($table));

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
            //$mform->addElement('editor', 'preamble', get_string('preamble_textbox', 'block_risk_monitor'));
            //$mform->setType('fieldname', PARAM_RAW);
            
            $mform->addElement('textarea', 'preamble', get_string('preamble_textbox', 'block_risk_monitor'), 'wrap="virtual" rows="5" cols="100"');
            $mform->setDefault('preamble', $preamble);
            $mform->addElement('submit', 'submit_preamble', get_string('save', 'block_risk_monitor'));           
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
            
            $mform->addElement('textarea', 'postamble', get_string('postamble_textbox', 'block_risk_monitor'), 'wrap="virtual" rows="5" cols="100"');
            $mform->setDefault('postamble', $postamble);
            $mform->addElement('submit', 'submit_postamble', get_string('save', 'block_risk_monitor'));           
        }

    }
    
}

class individual_settings_form_edit_categories_rules extends moodleform {
    
    public function definition() {
        global $DB, $USER;
        
        $mform =& $this->_form;
    
        if(!empty($this->_customdata['courseid']) && $this->_customdata['courseid'] !== -1) {
            
            $add_category = html_writer::link (new moodle_url('new_category.php', array('userid' => $USER->id, 'courseid' => $this->_customdata['courseid'])), get_string('new_category','block_risk_monitor')).'<br><br>';
            $mform->addElement('static', 'newcategory', '', $add_category);        
            
            if($categories = $DB->get_records('block_risk_monitor_category', array('courseid' => $this->_customdata['courseid']))) {
                
                //Create the heading with "delete"
                //$table = new html_table();
                /*$header1 = new html_table_cell();
                $header2 = new html_table_cell();
                $header3 = new html_table_cell();
                $header3->text = "<b>Delete</b>";
                $table->data[] = new html_table_row(array($header1, $header2, $header3));*/
                
                foreach($categories as $category) {
                    
                    $table = new html_table();
                    //Start up the table
                    //Create heading: category, with an edit, and a checkbox to delete.
                    $category_name = new html_table_cell();
                    $category_name->text =  "<b>".$category->name."</b>";
                    
                    $category_edit = new html_table_cell();
                    $category_edit->text =  html_writer::link (new moodle_url('edit_category.php', array('userid' => $USER->id, 'categoryid' => $category->id)), get_string('edit_category','block_risk_monitor'));
                                        
                    if($rules = $DB->get_records('block_risk_monitor_rule', array('categoryid' => $category->id))) {
                        $category_delete = new html_table_cell();
                        $category_delete->text = "<b>Delete</b>";//html_writer::empty_tag('input', array('type' => 'checkbox', 'id' => 'delete_category'.$category->id));
                        $table->data[] = new html_table_row(array($category_name, $category_edit, $category_delete));
                    }
                    else {
                        $table->data[] = new html_table_row(array($category_name, $category_edit));
                    }
                    
                    if($rules) {
                        
                       foreach($rules as $rule) {
                                                       
                            $rule_name = new html_table_cell();
                            $rule_name->text =  $rule->name;
                            $rule_name->attributes['colspan'] = "2";
                            
                            $rule_edit = new html_table_cell();
                            $rule_edit->text = html_writer::link (new moodle_url('edit_rule.php', array('userid' => $USER->id, 'categoryid' => $category->id)), get_string('edit_rule','block_risk_monitor'));

                            $rule_delete = new html_table_cell();
                            $rule_delete->text = html_writer::empty_tag('input', array('type' => 'checkbox', 'id' => 'delete_category'.$rule->id));

                            $table->data[] = new html_table_row(array($rule_name, $rule_edit, $rule_delete));   
                       }
                    }
                    
                    //"add rule"
                    $rule_add_empty = new html_table_cell();
                    
                    $rule_add = new html_table_cell();
                    $rule_add->text = html_writer::link (new moodle_url('new_rule.php', array('userid' => $USER->id, 'categoryid' => $category->id)), get_string('add_rule','block_risk_monitor'));
                    
                    $rule_add_empty2 = new html_table_cell();
                    
                    if($rules) {
                        $table->data[] = new html_table_row(array($rule_add, $rule_add_empty, $rule_add_empty2));
                    }
                    else {
                        $table->data[] = new html_table_row(array($rule_add, $rule_add_empty));                        
                    }
                    $mform->addElement('static', 'selectors', '', html_writer::table($table));

                }
                //$mform->addElement('static', 'selectors', '', html_writer::table($table));
                $mform->addElement('submit', 'submit'.$this->_customdata['courseid'], get_string('save', 'block_risk_monitor'));     
            }
            else {
                //No categories
                $mform->addElement('static', 'no_categories', '', get_string('no_categories', 'block_risk_monitor').'<br>');
            }
        }    
    }
}

class individual_settings_form_new_category extends moodleform {
    
    public function definition() {
        global $DB, $USER;
        
        $mform =& $this->_form;
        
        //Name: string
        $mform->addElement('textarea', 'name_text', "Name of the category", 'wrap="virtual" rows="1 cols="50"');
        $mform->addRule('name_text', "Name required", 'required', '', 'client');

        
        //Description: text
        $mform->addElement('textarea', 'description_text', "A short description of the category", 'wrap="virtual" rows="5" cols="50"');
        $this->add_action_buttons(false, "Save category");
         
    }
}

//For creating a new rule - needs to be passed the category id
class individual_settings_form_new_rule extends moodleform {
    
    public function definition() {
        global $DB, $USER;
        
        $mform =& $this->_form;
        
        $categoryid = $this->_customdata['categoryid'];
        
        //Weighting default: divide 100 by number of rules already registered+1;
        $total_rules = count(block_risk_monitor_get_rules(intval($categoryid)))+1;
        $weighting_default = 100/intval($total_rules);
        
        if($unregistered_rules = block_risk_monitor_get_unregistered_default_rules($categoryid)) {
            //Name: select from default rules (for now)
            $rulegroup = array();
            $rulegroup[] =& $mform->createElement('select', 'rule_id', '', block_risk_monitor_get_unregistered_default_rules($categoryid));
            $rulegroup[] =& $mform->createElement('submit', 'submit_get_rule_description', "View rule description");
            $mform->addGroup($rulegroup, 'rulegroup', "Rule", ' ', false);

            //Description if required
            if($this->_customdata['rule_id'] !== -1) {
                $mform->addElement('static', 'rule_description_text', "Description", DefaultRules::$default_rule_descriptions[$this->_customdata['rule_id']]);
            }

            $mform->addElement('static', 'whitespace1', '', "<br><br><br><br>");


            //Weighting        
            $weightingroup=array();
            $weightingroup[] =& $mform->createElement('textarea', 'weighting_text', '', 'rows="1"');
            $weightingroup[] =& $mform->createElement('static', 'percent_text', '', "%");
            $weightingroup[] =& $mform->createElement('submit', 'submit_get_weighting_description', "What is this?");
            $mform->addGroup($weightingroup, 'weightingroup', "Weighting of this rule", ' ', false);
            $mform->setDefault('weighting_text', round($weighting_default,2));

            if($this->_customdata['weightingdesc'] !== -1) {
                $mform->addElement('static', 'weighting_description', '',get_string('weighting_description', 'block_risk_monitor'));
            }        
            $mform->addElement('static', 'whitespace2', '', "<br><br><br><br>");

            $this->add_action_buttons(false, "Add rule");
        }
        else {
            $mform->addElement('static', 'norules', "", "There are no rules left to add.");
        }
    }
    
    
}