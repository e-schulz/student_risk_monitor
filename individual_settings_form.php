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
    
        //if(!empty($this->_customdata['courseid']) && $this->_customdata['courseid'] !== -1) {
            
            $add_category = html_writer::link (new moodle_url('new_category.php', array('userid' => $USER->id/*, 'courseid' => $this->_customdata['courseid']*/)), get_string('new_category','block_risk_monitor')).'<br><br>';
            $mform->addElement('static', 'newcategory', '', $add_category);        
            
            if($categories = $DB->get_records('block_risk_monitor_category'/*, array('courseid' => $this->_customdata['courseid'])*/)) {
                
                $empty_cell = new html_table_cell();               
                
                foreach($categories as $category) {
                    
                    ///TABLE
                    $table = new html_table();
                    //Start up the table
                    //Create heading: category, with an edit, and a checkbox to delete.
                    $category_name = new html_table_cell();
                    $category_name->text =  "<b>".$category->name."</b>&nbsp;".
                            html_writer::start_tag('a', array('href' => 'edit_category.php?userid='.$USER->id.'&categoryid='.$category->id)).
                            html_writer::empty_tag('img', array('src' => get_string('edit_icon', 'block_risk_monitor'), 'align' => 'middle')).
                            html_writer::end_tag('a')."&nbsp;".
                            html_writer::start_tag('a', array('href' => 'edit_categories_rules.php?userid='.$USER->id.'&categoryid='.$category->id)).
                            html_writer::empty_tag('img', array('src' => get_string('delete_icon', 'block_risk_monitor'), 'align' => 'middle')).
                            html_writer::end_tag('a');
                    
                    ///TABLE
                    if($rules = $DB->get_records('block_risk_monitor_rule_inst', array('categoryid' => $category->id))) {
                        
                            $weighting_text = new html_table_cell();
                            $weighting_text->text = "<b>Weighting</b>";
                            $table->data[] = new html_table_row(array($category_name, $empty_cell, $empty_cell, $weighting_text));

                        ///BOTH
                       foreach($rules as $rule) {

                            ///TABLE
                            $rule_name = new html_table_cell();
                            $rule_name->text =  $rule->name;
                            $rule_name->attributes['colspan'] = "2";
                            
                            if(intval($rule->ruletype) == 1) {
                                $custom = -1;
                            }
                            else if(intval($rule->ruletype) == 2) {
                                $custom = 1;
                            }
                            $rule_edit = new html_table_cell();
                            $rule_edit->text = html_writer::link (new moodle_url('edit_rule.php', array('userid' => $USER->id, 'ruleid' => $rule->id, 'custom' => $custom)), get_string('edit_rule','block_risk_monitor'));

                            $rule_delete = new html_table_cell();
                            $rule_delete->text = html_writer::link (new moodle_url('edit_categories_rules.php', array('userid' => $USER->id/*, 'courseid' => $this->_customdata['courseid']*/, 'ruleid' => $rule->id)), "Delete");

                            $rule_weighting = new html_table_cell();
                            $rule_weighting->text = $rule->weighting."%";
                            $table->data[] = new html_table_row(array($rule_name, $rule_edit, $rule_delete, $rule_weighting));   
                       }
                                           
                        $rule_add = new html_table_cell();
                        $rule_add->text = html_writer::link (new moodle_url('new_rule.php', array('userid' => $USER->id, 'categoryid' => $category->id)), get_string('add_rule','block_risk_monitor'));
                        $table->data[] = new html_table_row(array($rule_add, $empty_cell, $empty_cell, $empty_cell));
                    }
                    else {
                         $table->data[] = new html_table_row(array($category_name));                    
                        $rule_add = new html_table_cell();
                        $rule_add->text = html_writer::link (new moodle_url('new_rule.php', array('userid' => $USER->id, 'categoryid' => $category->id)), get_string('add_rule','block_risk_monitor'));
                        $table->data[] = new html_table_row(array($rule_add));
                    }
                    

                    $mform->addElement('static', 'selectors', '', html_writer::table($table));

                }
                $view_custom = html_writer::link (new moodle_url('view_custom_rules.php', array('userid' => $USER->id)), get_string('new_custom_rule','block_risk_monitor')).'<br><br>';
                $mform->addElement('static', 'viewcustom', '', $view_custom);        
                          
            }
            else {
                //No categories
                $mform->addElement('static', 'no_categories', '', get_string('no_categories', 'block_risk_monitor').'<br>');
            }
        //}    
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

class individual_settings_form_edit_category extends moodleform {
    
    public function definition() {
        global $DB, $USER;
        
        $mform =& $this->_form;
        $getcategory = $DB->get_record('block_risk_monitor_category', array('id' => $this->_customdata['categoryid']));
        
        //Coursename
        //$mform->addElement('static', 'course_name', "Course", $this->_customdata['coursename']);

        //Name: string
        $mform->addElement('textarea', 'name_text', "Name of the category", 'wrap="virtual" rows="1 cols="50"');
        $mform->addRule('name_text', "Name required", 'required', '', 'client');
        $mform->setDefault('name_text', $getcategory->name);
        
        //Description: text
        $mform->addElement('textarea', 'description_text', "A short description of the category", 'wrap="virtual" rows="5" cols="50"');
        $mform->setDefault('description_text', $getcategory->description);
        $this->add_action_buttons(false, "Save category");
         
    }
}

//For creating a new rule - needs to be passed the category id
class individual_settings_form_new_default_rule extends moodleform {
    
    public function definition() {
        global $DB, $USER;
        
        $mform =& $this->_form;
        
        $links = array();

        $categoryid = $this->_customdata['categoryid'];
        
        //Weighting default: divide 100 by number of rules already registered+1;
        $total_rules = count(block_risk_monitor_get_rules(intval($categoryid)))+1;
        $weighting_default = 100/intval($total_rules);
        
        if($unregistered_rules = block_risk_monitor_get_unregistered_default_rule_names($categoryid)) {
            //Name: select from default rules (for now)
            $rulegroup = array();
            $rulegroup[] =& $mform->createElement('select', 'rule_id', '', $unregistered_rules);
            $rulegroup[] =& $mform->createElement('submit', 'submit_get_rule_description', "View rule description");
            $mform->addGroup($rulegroup, 'rulegroup', "Rule", ' ', false);
           
            //Description if required
            if($this->_customdata['rule_id'] !== -1) {
                //$default_rule = //$DB->get_record('block_risk_monitor_rule_inst_type', array('id' => $this->_customdata['rule_id']));                
                $mform->setDefault('rule_id', $this->_customdata['rule_id']);
                $mform->addElement('static', 'rule_description_text', "Description", DefaultRules::$default_rule_descriptions[$this->_customdata['rule_id']]);//$default_rule->description);

                $mform->addElement('static', 'whitespace1', '', "<br><br>");
            }    
                //Value 
                $mform->addElement('textarea', 'value_text', "Value for x", 'rows="1"');
                
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

                $this->add_action_buttons(true, "Add rule");
            
            
            //Value
           
        }
        else {
            $mform->addElement('static', 'norules', "", "There are no rules left to add.");
        }
    }
    
    
}

class individual_settings_form_new_custom_rule extends moodleform {
    
    public function definition() {
        global $DB, $USER;
        
        $mform =& $this->_form;

        $categoryid = $this->_customdata['categoryid'];
        
        //Weighting default: divide 100 by number of rules already registered+1;
        $total_rules = count(block_risk_monitor_get_rules(intval($categoryid)))+1;
        $weighting_default = 100/intval($total_rules);
        
        if($unregistered_rules = block_risk_monitor_get_unregistered_custom_rule_names($categoryid)) {
            
            //Name: select from default rules (for now)
            $rulegroup = array();
            $rulegroup[] =& $mform->createElement('select', 'rule_id', '', $unregistered_rules);
            $rulegroup[] =& $mform->createElement('submit', 'submit_get_rule_description', "View rule description");
            $mform->addGroup($rulegroup, 'rulegroup', "Rule", ' ', false);
           
            //Description if required
            if($this->_customdata['rule_id'] !== -1) {
                $custom_rule = $DB->get_record('block_risk_monitor_cust_rule', array('id' => $this->_customdata['rule_id']));           
                $mform->setDefault('rule_id', $this->_customdata['rule_id']);
                
                //Description
                if($custom_rule->description == '') {
                    $desc = 'None given';
                }
                else {
                    $desc = $custom_rule->description;
                }
                $mform->addElement('static', 'rule_description_text', "Description", $desc);//$default_rule->description);
                
                //Question
                if($questions = $DB->get_records('block_risk_monitor_question', array('custruleid' => $custom_rule->id))) {
                    
                    foreach($questions as $question) {
                        //Question
                        $mform->addElement('static', 'question_text', "Question", $question->question);                       
                        
                        //Any options
                        if($options = $DB->get_records('block_risk_monitor_option', array('questionid' => $question->id))) {
                            
                            $radioarray=array();
                            foreach($options as $option) {
                                //Option.
                                $radioarray[] =& $mform->createElement('radio', 'option', '', $option->label.'&nbsp;&nbsp;&nbsp;&nbsp;', 1);
                            }
                            $mform->addGroup($radioarray, 'optiongroup', '', array(' '), false); 
                        }
                        $mform->addElement('static', 'whitespace', "", "<br>");

                    }
                }
                
                //Options
                $mform->addElement('static', 'whitespace1', '', "<br><br>");
                
                //
                
            }    
            
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

                $this->add_action_buttons(true, "Add rule");
            
            
            //Value
           
        }
        else {
            $mform->addElement('static', 'norules', "", "There are no rules left to add.");
        }
    }
    
    
}

class individual_settings_form_new_survey extends moodleform {
    public function definition() {
        
        $mform =& $this->_form;
        
    }
}

class individual_settings_form_edit_rule extends moodleform {
    
    public function definition() {
        global $DB, $USER;
        
        $mform =& $this->_form;
        
        $ruleid = $this->_customdata['ruleid'];
        $getrule = $DB->get_record('block_risk_monitor_rule_inst', array('id' => $ruleid));
        
        //Weighting default: divide 100 by number of rules already registered+1;
        $weighting_default = $getrule->weighting;
        
            //Category
            $mform->addElement('static', 'category_name', "Category", $this->_customdata['categoryname']);

            //Name
            $mform->addElement('static', 'rule_name', "Name", $getrule->name);
            
            //Description
            if($getrule->description == '') {
                $desc = "None given";
            }
            else {
                $desc = $getrule->description;
            }
            $mform->addElement('static', 'rule_description_text', "Description", $desc);
            
            //Value 
            if($this->_customdata['custom'] == -1) {
                $mform->addElement('textarea', 'value_text', "Value for x", 'rows="1"');
                $mform->setDefault('value_text', $getrule->value);
            }
            
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

            $this->add_action_buttons(false, "Save changes");
    }
    
    
}

class individual_settings_form_view_custom_rules extends moodleform {
    
    public function definition() {
        
        global $USER, $DB, $OUTPUT;
        
           $mform =& $this->_form;
        
            $add_custom_rule = html_writer::link (new moodle_url('create_custom_rule.php', array('userid' => $USER->id)), get_string('new_custom','block_risk_monitor')).'<br><br>';
            $mform->addElement('static', 'newcustom', '', $add_custom_rule);        
            
            if($custom_rules = $DB->get_records('block_risk_monitor_cust_rule', array('userid' => $USER->id))) {
                         
                $table = new html_table();
                 
                $heading = new html_table_cell();
                $heading->text =  "<b>Custom rules</b>";
                    
                $filler = new html_table_cell();   
                $table->data[] = new html_table_row(array($heading, $filler));
                
                foreach($custom_rules as $custom_rule) {
                    
                    $rule_link = new html_table_cell();
                    $rule_link->text =  html_writer::link (new moodle_url('view_custom_rules.php', array('userid' => $USER->id, 'custruleid' => $custom_rule->id, 'view' => 1)), $custom_rule->name);
                    
                    $delete_icon = new html_table_cell();
                    $delete_icon->text = html_writer::start_tag('a', array('href' => 'view_custom_rules.php?userid='.$USER->id.'&custruleid='.$custom_rule->id.'&delete=1')).
                                        html_writer::empty_tag('img', array('src' => get_string('delete_icon', 'block_risk_monitor'), 'align' => 'middle')).
                                        html_writer::end_tag('a');
                    
                    $table->data[] = new html_table_row(array($rule_link, $delete_icon));

                }
                
                $mform->addElement('static', 'cust_rules', '', html_writer::table($table));
                                          
            }
            else {
                //Nothing to see here folks
            }    
        
    }
}

class individual_settings_form_create_custom_rule extends moodleform {
    
    public function definition() {
        
        global $DB, $USER;
        
        $mform =& $this->_form;
        
        //Name
        $mform->addElement('textarea', 'rule_name_text', "Name", 'rows="1" cols="75"');    
        $mform->addRule('rule_name_text', "Name required", 'required', '', 'client');

        //Description
        $mform->addElement('textarea', 'rule_description_text', "Description", 'rows="5" cols="75"');   
        $mform->addElement('static', 'whitespace', '', "<br><br><br>");
        
        //Question
        $mform->addElement('textarea', 'question_text', "Question", 'rows="2" cols="75"');   
        $mform->addElement('static', 'whitespace', '', "<br><br>");
        $mform->addRule('question_text', "Question is required", 'required', '', 'client');
        
        //Options and values
        $risk_values = array(0 => get_string('low_risk','block_risk_monitor'), 50 => get_string('moderate_risk','block_risk_monitor'), 100 => get_string('high_risk','block_risk_monitor'));
        $mform->addElement('textarea', 'option1_text', "Option label", 'rows="1"');   
        $mform->addElement('select', 'option1_value', "Risk rating", $risk_values);   
        $mform->addElement('static', 'whitespace', '', "<br>");
        $mform->addRule('option1_text', "At least two options are required", 'required', '', 'client');
        
        $mform->addElement('textarea', 'option2_text', "Option label", 'rows="1"');   
        $mform->addElement('select', 'option2_value', "Risk rating", $risk_values);  
        $mform->addElement('static', 'whitespace', '', "<br>");
        $mform->addRule('option2_text', "At least two options are required", 'required', '', 'client');
        
        $mform->addElement('textarea', 'option3_text', "Option label", 'rows="1"');   
        $mform->addElement('select', 'option3_value', "Risk rating", $risk_values);  
        $mform->addElement('static', 'whitespace', '', "<br>");
        
        $mform->setDefault('option1_value', 0);        
        $mform->setDefault('option2_value', 0);        
        $mform->setDefault('option3_value', 0);   
        
        //Save and submit.
        $buttons_group=array();   
        $buttons_group[] =& $mform->createElement('submit', 'submit_rule', "Create rule");    
        $buttons_group[] =& $mform->createElement('static', 'cancel_link', '', "&nbsp;&nbsp;".html_writer::link(new moodle_url('view_custom_rules.php', array('userid' => $USER->id)), "Cancel"));
        $mform->addGroup($buttons_group, 'buttons_group', '', '', false);
        
        //$this->add_action_buttons(true, "Create rule");
        
    }
    
    
}