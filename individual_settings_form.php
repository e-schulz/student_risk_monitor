<?php

/* 
 * This file represents the form that will be used to change the settings
 * 
 */

require_once($CFG->libdir . '/formslib.php');
require_once('locallib.php');
global $OUTPUT;

class individual_settings_form_edit_categories_rules extends moodleform {
    
    public function definition() {
        global $DB, $USER;
        
        $mform =& $this->_form;
        $courseid = $this->_customdata['courseid'];
        //if(!empty($this->_customdata['courseid']) && $this->_customdata['courseid'] !== -1) {
            
            $add_category = html_writer::link (new moodle_url('new_category.php', array('userid' => $USER->id, 'courseid' => $courseid/*, 'courseid' => $this->_customdata['courseid']*/)), get_string('new_category','block_risk_monitor')).'<br><br>';
            $mform->addElement('static', 'newcategory', '', $add_category);        
            
            if($categories = $DB->get_records('block_risk_monitor_category', array('courseid' => $courseid))) {
                
                $empty_cell = new html_table_cell();               
                
                foreach($categories as $category) {
                    
                    ///TABLE
                    $table = new html_table();
                    //Start up the table
                    //Create heading: category, with an edit, and a checkbox to delete.
                    $category_name = new html_table_cell();
                    $category_name->text =  "<b>".$category->name."</b>&nbsp;".
                            html_writer::start_tag('a', array('href' => 'edit_category.php?userid='.$USER->id.'&categoryid='.$category->id."&courseid=".$courseid)).
                            html_writer::empty_tag('img', array('src' => get_string('edit_icon', 'block_risk_monitor'), 'align' => 'middle')).
                            html_writer::end_tag('a')."&nbsp;".
                            html_writer::start_tag('a', array('href' => 'edit_categories_rules.php?userid='.$USER->id.'&categoryid='.$category->id."&courseid=".$courseid)).
                            html_writer::empty_tag('img', array('src' => get_string('delete_icon', 'block_risk_monitor'), 'align' => 'middle')).
                            html_writer::end_tag('a');
                    $category_name->attributes['width'] = '200px';
                    
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
                            $rule_edit->text = html_writer::link (new moodle_url('edit_rule.php', array('userid' => $USER->id, 'courseid' => $courseid, 'ruleid' => $rule->id, 'custom' => $custom)), get_string('edit_rule','block_risk_monitor'));

                            $rule_delete = new html_table_cell();
                            $rule_delete->text = html_writer::link (new moodle_url('edit_categories_rules.php', array('userid' => $USER->id, 'courseid' => $courseid/*, 'courseid' => $this->_customdata['courseid']*/, 'ruleid' => $rule->id)), "Delete");

                            $rule_weighting = new html_table_cell();
                            $rule_weighting->text = $rule->weighting."%";
                            $table->data[] = new html_table_row(array($rule_name, $rule_edit, $rule_delete, $rule_weighting));   
                       }
                                           
                        $rule_add = new html_table_cell();
                        $rule_add->text = html_writer::link (new moodle_url('new_rule.php', array('userid' => $USER->id, 'courseid' => $courseid, 'categoryid' => $category->id)), get_string('add_rule','block_risk_monitor'));
                        $table->data[] = new html_table_row(array($rule_add, $empty_cell, $empty_cell, $empty_cell));
                    }
                    else {
                         $table->data[] = new html_table_row(array($category_name));                    
                        $rule_add = new html_table_cell();
                        $rule_add->text = html_writer::link (new moodle_url('new_rule.php', array('userid' => $USER->id, 'courseid' => $courseid, 'categoryid' => $category->id)), get_string('add_rule','block_risk_monitor'));
                        $table->data[] = new html_table_row(array($rule_add));
                    }
                    

                    $mform->addElement('static', 'selectors', '', html_writer::table($table));

                }
                $view_custom = html_writer::link (new moodle_url('view_custom_rules.php', array('userid' => $USER->id, 'courseid' => $courseid)), get_string('new_custom_rule','block_risk_monitor')).'<br><br>';
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
        $courseid = $this->_customdata['courseid'];
            $add_custom_rule = html_writer::link (new moodle_url('create_custom_rule.php', array('userid' => $USER->id, 'courseid' => $courseid)), get_string('new_custom','block_risk_monitor')).'<br><br>';
            $mform->addElement('static', 'newcustom', '', $add_custom_rule);        
            
            if($custom_rules = $DB->get_records('block_risk_monitor_cust_rule', array('userid' => $USER->id))) {
                         
                $table = new html_table();
                 
                $heading = new html_table_cell();
                $heading->text =  "<b>Custom rules</b>";
                    
                $filler = new html_table_cell();   

                $table->data[] = new html_table_row(array($heading, $filler));
                
                foreach($custom_rules as $custom_rule) {
                    
                    $numeric_scoring = false;
                    if($custom_rule->max_score < 100) {
                        $numeric_scoring = true;            
                    }           
                    $low_mod_risk_cutoff = $custom_rule->low_mod_risk_cutoff;
                    $mod_high_risk_cutoff = $custom_rule->mod_high_risk_cutoff;
                    $max_score = $custom_rule->max_score;
                    $min_score = $custom_rule->min_score;
                    
                    if($this->_customdata['viewruleid'] == $custom_rule->id) {
                        
                        $questions = $DB->get_records('block_risk_monitor_question', array('custruleid' => $custom_rule->id));
                        
                        //Description and question.
                        if($custom_rule->description == '') {
                            $desc = '<i>No description given</i>';
                        }
                        else {
                            $desc = $custom_rule->description;
                        }

                        $questions_asked = "<b>Question(s):</b><br><br>";
                        if($questions) {
                            $i = 1;
                            foreach($questions as $question) {
                                $questions_asked .= "<b>".$i.".</b>&nbsp;".$question->question."<br>";
                                $i++;
                                

                                //Options
                                $options = $DB->get_records('block_risk_monitor_option', array('questionid' => $question->id));
                                foreach($options as $option) {
                                    
                                    if($numeric_scoring) {
                                        if($low_mod_risk_cutoff < $mod_high_risk_cutoff) {
                                            if($option->value >= $mod_high_risk_cutoff/count($questions)) {
                                                $risk_level = "Low risk";
                                            }
                                            else if ($option->value >= $low_mod_risk_cutoff/count($questions)) {
                                                $risk_level = "Moderate risk";
                                            }
                                            else {
                                                $risk_level = "High risk";
                                            }
                                        }
                                        else {
                                            if($option->value >= $low_mod_risk_cutoff/count($questions)) {
                                                $risk_level = "Low risk";
                                            }
                                            else if ($option->value >= $mod_high_risk_cutoff/count($questions)) {
                                                $risk_level = "Moderate risk";
                                            }
                                            else {
                                                $risk_level = "High risk";
                                            }                                            
                                        }
                                    }
                                    else {
                                        if($option->value >= HIGH_RISK) {
                                            $risk_level = "High risk";
                                        }
                                        else if ($option->value >= MODERATE_RISK) {
                                            $risk_level = "Moderate risk";
                                        }
                                        else {
                                            $risk_level = "Low risk";
                                        }
                                    }
                                    
                                    $questions_asked .= "&nbsp;&nbsp;&nbsp;-<i>".$option->label."&nbsp;(".$risk_level.")</i><br>";
                                }
                                $questions_asked .= "<br>";
                            }
                        }
                        else {
                            $questions_asked .= "<i>None</i><br>";
                        }
                         
                        $rule_link = new html_table_cell();
                        $rule_link->text = html_writer::link (new moodle_url('view_custom_rules.php', array('userid' => $USER->id, 'courseid' => $courseid, 'custruleid' => $custom_rule->id, 'view' => 1)), $custom_rule->name)."<br>".$desc.'<br><br>'.$questions_asked;

                        $delete_icon = new html_table_cell();
                        $delete_icon->text = html_writer::start_tag('a', array('href' => 'view_custom_rules.php?userid='.$USER->id.'&courseid='.$courseid.'&custruleid='.$custom_rule->id.'&delete=1')).
                                            html_writer::empty_tag('img', array('src' => get_string('delete_icon', 'block_risk_monitor'), 'align' => 'middle')).
                                            html_writer::end_tag('a');

                        $table->data[] = new html_table_row(array($rule_link, $delete_icon));
                        
                    }
                    else {
                        $rule_link = new html_table_cell();
                        $rule_link->text =  html_writer::link (new moodle_url('view_custom_rules.php', array('userid' => $USER->id, 'courseid' => $courseid, 'custruleid' => $custom_rule->id, 'view' => 1)), $custom_rule->name);

                        $delete_icon = new html_table_cell();
                        $delete_icon->text = html_writer::start_tag('a', array('href' => 'view_custom_rules.php?userid='.$USER->id.'&courseid='.$courseid.'&custruleid='.$custom_rule->id.'&delete=1')).
                                            html_writer::empty_tag('img', array('src' => get_string('delete_icon', 'block_risk_monitor'), 'align' => 'middle')).
                                            html_writer::end_tag('a');

                        $table->data[] = new html_table_row(array($rule_link, $delete_icon));
                        
                    }

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
        $courseid = $this->_customdata['courseid'];
        $num_questions = $this->_customdata['numquestions'];
        $num_options = $this->_customdata['numoptions'];
        
        if($num_questions === -1) {
            //Name
            $mform->addElement('textarea', 'rule_name_text', "Name", 'rows="1" cols="75"');    
            $mform->addRule('rule_name_text', "Name required", 'required', '', 'client');

            //Description
            $mform->addElement('textarea', 'rule_description_text', "Description", 'rows="5" cols="75"');   
            $mform->addElement('static', 'whitespace', '', "<br><br><br>");

            //Number of questions and options:
            $mform->addElement('select', 'number_questions', "Number of questions", range(0,20));  

            $mform->addElement('select', 'number_options', "Number of options per question", range(0,5));  
            $mform->addElement('select', 'scoring_method', "Scoring method", array("Risk level (High, Med, Low)", "Numeric"));  

            //Save and submit.
            $buttons_group=array();   
            $buttons_group[] =& $mform->createElement('submit', 'submit_rule1', "Next");    
            $buttons_group[] =& $mform->createElement('static', 'cancel_link', '', "&nbsp;&nbsp;".html_writer::link(new moodle_url('view_custom_rules.php', array('userid' => $USER->id, 'courseid' => $courseid)), "Cancel"));
            $mform->addGroup($buttons_group, 'buttons_group', '', '', false);               
            
            
        }
        else {
                        //min, max, ranges.
            if($this->_customdata['scoringmethod'] == 1) {
                $mform->addElement('select', 'min_score', "Minimum score", range(0,$num_questions));  
                $mform->addElement('select', 'max_score', "Maximum score", range($num_questions*($num_options-1),$num_questions*$num_options));
                
                $buttons_group=array();   
                $buttons_group[] =& $mform->createElement('textarea', 'lowrangebegin', '', 'rows="1" cols="5"');
                $buttons_group[] =& $mform->createElement('static', 'lowrangetext', '', " to ");    
                $buttons_group[] =& $mform->createElement('textarea', 'lowrangeend', '', 'rows="1" cols="5"');
                $mform->addGroup($buttons_group, 'lowrange', "Low risk range: ", '&nbsp', false);         
                
                $buttons_group=array();   
                $buttons_group[] =& $mform->createElement('textarea', 'medrangebegin', '', 'rows="1" cols="5"');
                $buttons_group[] =& $mform->createElement('static', 'medrangetext','', " to ");    
                $buttons_group[] =& $mform->createElement('textarea', 'medrangeend', '', 'rows="1" cols="5"');
                $mform->addGroup($buttons_group, 'medrange', "Moderate risk range: ", '&nbsp', false);     
                
                $buttons_group=array();  
                $buttons_group[] =& $mform->createElement('textarea', 'highrangebegin', '', 'rows="1" cols="5"');
                $buttons_group[] =& $mform->createElement('static', 'highrangetext', '', " to ");    
                $buttons_group[] =& $mform->createElement('textarea', 'highrangeend', '', 'rows="1" cols="5"');
                $mform->addGroup($buttons_group, 'highrange', "High risk range: ", '&nbsp', false);     
                
                $mform->addRule('lowrange', "Range required", 'required', '', 'client');
                $mform->addRule('medrange', "Range required", 'required', '', 'client');
                $mform->addRule('highrange', "Range required", 'required', '', 'client');
                $mform->addElement('static', 'whitespace', '', "<br><br>");

                
            }
            
            for($i=0; $i<$this->_customdata['numquestions']; $i++) {
                
                //Question
                $mform->addElement('textarea', 'question_text'.$i, "Question ".($i+1), 'rows="2" cols="75"');   
                $mform->addElement('static', 'whitespace', '', "<br>");
                $mform->addRule('question_text'.$i, "Question is required", 'required', '', 'client');

                //Options and values
                $option_values = array();
                if($this->_customdata['scoringmethod'] == 0) {
                    $option_values = array(0 => get_string('low_risk','block_risk_monitor'), 50 => get_string('moderate_risk','block_risk_monitor'), 100 => get_string('high_risk','block_risk_monitor'));
                }
                else if ($this->_customdata['scoringmethod'] == 1) {
                    $option_values = range(0, $num_options);
                }
                
                for($j=0; $j<$num_options; $j++) {
                    $mform->addElement('textarea', 'option_text'.$i.$j, "Option text", 'rows="1"');   
                    $mform->addElement('select', 'option_value'.$i.$j, "Option value", $option_values);   
                    $mform->addElement('static', 'whitespace', '', "<br>");
                    $mform->addRule('option_text'.$i.$j, "Option must have text", 'required', '', 'client');
                    $mform->setDefault('option_value'.$i.$j, 0);        

                }  
                $mform->addElement('static', 'whitespace', '', "<br><br>");

      
            }
            
                            //Save and submit.
                $buttons_group=array();   
                $buttons_group[] =& $mform->createElement('submit', 'submit_rule2', "Create rule");    
                $buttons_group[] =& $mform->createElement('static', 'cancel_link', '', "&nbsp;&nbsp;".html_writer::link(new moodle_url('view_custom_rules.php', array('userid' => $USER->id, 'courseid' => $courseid)), "Cancel"));
                $mform->addGroup($buttons_group, 'buttons_group', '', '', false);  
        
        }
        
        //$this->add_action_buttons(true, "Create rule");
        
    }
    
    
}

class individual_settings_form_student_questions extends moodleform {
    
        public function definition() {
            
             global $DB, $USER;
             $mform =& $this->_form;
             
             $questions = $this->_customdata['questions'];
             
             foreach($questions as $question) {
        
                $options = $DB->get_records('block_risk_monitor_option', array('questionid' => $question->id));
                $mform->addElement('static', 'question'.$question->id, '', $question->question."<br>");

                $radioarray = array();
                foreach($options as $option) {
                    $radioarray[] =& $mform->createElement('radio', 'question_option'.$question->id, '', $option->label, $option->id);
                }
                $mform->addGroup($radioarray, 'questiongroup'.$question->id, '', array(' '), false);
                $mform->addElement('static', 'whitespace', '', "<br><br>");

            }
            
            $this->add_action_buttons(true, "Submit");
            
        }
}

class individual_settings_form_view_student extends moodleform {
    
    public function definition() {
            
         global $DB, $USER;
         $mform =& $this->_form;
             
         $courseid = $this->_customdata['courseid'];
         $userid = $this->_customdata['userid'];
         $studentid = $this->_customdata['studentid'];
         $student = $DB->get_record('user', array('id' => $studentid));
         $mform->addElement('static', 'name', '', "<b><i>".$student->firstname."&nbsp;".$student->lastname."</i></b><br><br>");
         //Get all the categories
         if($categories = $DB->get_records('block_risk_monitor_category', array('courseid' => $courseid))) {

            foreach($categories as $category) {

                //Get the category risk for this student
                if($cat_risk = $DB->get_record('block_risk_monitor_cat_risk', array('categoryid' => $category->id, 'userid' => $studentid))) {

                    if($cat_risk->value >= HIGH_RISK) {
                         $risk_icon = html_writer::empty_tag('img', array('src' => get_string('high_risk_icon', 'block_risk_monitor'),'align' => 'middle'));
                    }
                    else if ($cat_risk->value >= MODERATE_RISK) {
                         $risk_icon = html_writer::empty_tag('img', array('src' => get_string('moderate_risk_icon', 'block_risk_monitor'),'align' => 'middle'));
                    }                    
                    
                    //Only show moderate to high risk students
                    if($cat_risk->value >= MODERATE_RISK) {
                        
                        $categoryhead = $risk_icon."&nbsp;&nbsp;<b>".$category->name."</b><br>";
                        $mform->addElement('static', 'category'.$category->id, '', $categoryhead); 

                        //get all the rules
                        if($rules = $DB->get_records(('block_risk_monitor_rule_inst'), array('categoryid' => $category->id))) {

                            foreach($rules as $rule) {

                                //Get the risk.
                                if($rule_risk = $DB->get_record('block_risk_monitor_rule_risk', array('ruleid' => $rule->id, 'userid' => $studentid))) {
                                    /*if($rule_risk->value >= HIGH_RISK) {
                                        $risk_icon = html_writer::empty_tag('img', array('src' => get_string('high_risk_icon', 'block_risk_monitor'),'align' => 'middle'));
                                    }
                                    else if ($rule_risk->value >= MODERATE_RISK) {
                                        $risk_icon = html_writer::empty_tag('img', array('src' => get_string('moderate_risk_icon', 'block_risk_monitor'),'align' => 'middle'));
                                    }*/
                                    
                                    //show only moderate to high risk rules
                                    /*if($rule_risk->value >= MODERATE_RISK) {
                                        $rulerow = array();

                                        $rulename = new html_table_cell();
                                        $rulename->text = $rule->name;
                                        $rulerow[] = $rulename;

                                        
                                        $ruleaction = new html_table_cell();
                                        //$ruleaction->text = html_writer::link (new moodle_url('view_actions.php', array('userid' => $USER->id, 'courseid' => $courseid,  'studentid' => $studentid, 'ruleid' => $rule->id)), get_string('view_actions', 'block_risk_monitor'));;
                                        $rulerow[] = $ruleaction;
                                        $studentstable->data[] = new html_table_row($rulerow);                                        
                                    }*/
                                }
                            }
                        }
                        
                        //Get all the interventions.
                        if($intervention_templates = $DB->get_records('block_risk_monitor_int_tmp', array('categoryid' => $category->id))) {
                            
                            $mform->addElement('static', 'interventionhead', '', "<div>&emsp;<i>Suggested interventions</i>");
                            foreach($intervention_templates as $intervention_template) {
                                $mform->addElement('static', 'interventioncontent', '', "<li>&emsp;".html_writer::link(new moodle_url('view_intervention.php', array('userid' => $USER->id, 'courseid' => $courseid, 'interventionid' => $intervention_template->id)), $intervention_template->name)."</li>");   
                            }
                            $mform->addElement('static', 'interventionend', '', "</div>");                            
                        }

                    }
                    
                }

            }

        }
            
    }    
}


class individual_settings_form_view_interventions extends moodleform {
    
        public function definition() {
            
             global $DB, $USER;
             $mform =& $this->_form;
             $courseid = $this->_customdata['courseid'];
             $userid = $this->_customdata['userid'];

             //Get the categories for this course.
             if($categories = $DB->get_records('block_risk_monitor_category', array('courseid' => $courseid))) {
                 
                //Display by category, listing the interventions.
                foreach($categories as $category) {
                    
                    $mform->addElement('header', 'category'.$category->id, $category->name);
                    //$mform->setExpanded('category'.$category->id);       
                    
                    //for each intervention add a new rule with description
                    if($interventions = $DB->get_records('block_risk_monitor_int_tmp', array('categoryid' => $category->id, 'courseid' => $courseid))) {
                        
                        foreach($interventions as $intervention) {
                            
                            if($intervention->description != "") {
                                $desc = $intervention->description;
                            }
                            else {
                                $desc = "<i>No description given</i>";
                            }
                            
                            $output = html_writer::start_tag('ul')."\n";
                            $output .= html_writer::tag('li', html_writer::link (new moodle_url('view_intervention.php', array('userid' => $userid, 'courseid' => $courseid, 'interventionid' => $intervention->id)), $intervention->name))."\n";
                            $output .= $desc."<br>";
                            $output .= html_writer::end_tag('ul');      
                            
                            $mform->addElement('static', 'intervention'.$intervention->id, '', $output);
                        }
                    }
                    
                    $new_intervention = html_writer::link (new moodle_url('new_intervention.php', array('userid' => $userid, 'courseid' => $courseid, 'categoryid' => $category->id)), "Add an intervention template..");   
                    
                    $mform->addElement('static', 'new_intervention', '', $new_intervention);
                }
                 
             }
             
            
        }
}

class individual_settings_form_new_intervention extends moodleform {
    
    public function definition() {
        global $DB, $USER;
        
        $mform =& $this->_form;
        
        $links = array();

        $categoryid = $this->_customdata['categoryid'];
        $userid = $this->_customdata['userid'];
        $courseid = $this->_customdata['courseid'];
        
        //Name
        $mform->addElement('header', 'general', "General");
        $mform->setExpanded('general');
        $mform->addElement('textarea', 'name_text', "Intervention name", 'wrap="virtual" rows="1 cols="50"');
        $mform->addRule('name_text', "Name required", 'required', '', 'client');
        
        //Description:
        $mform->addElement('textarea', 'description_text', "Intervention description", 'wrap="virtual" rows="2" cols="50"'); 
                
        //Instructions to student
        $mform->addElement('header', 'content', "Content");
        $mform->addElement('textarea', 'title_text', "Title", 'wrap="virtual" rows="1" cols="50"'); 
        $mform->addRule('title_text', "Title required", 'required', '', 'client');
        $mform->addElement('textarea', 'instructions_text', "Instructions to student", 'wrap="virtual" rows="5" cols="50"'); 
        $mform->addRule('instructions_text', "Instructions required", 'required', '', 'client');

        //URL
        $mform->addElement('url', 'externalurl', "External URL", array('size'=>'60'), array('usefilepicker'=>true));
        $mform->setType('externalurl', PARAM_RAW_TRIMMED);
        
        //Upload file
        $filemanager_options = array();
        $filemanager_options['accepted_types'] = '*';
        $filemanager_options['maxbytes'] = 0;
        $filemanager_options['maxfiles'] = -1;
        $filemanager_options['mainfile'] = true;

        $mform->addElement('filemanager', 'files', "Upload files", null, $filemanager_options);        

        //Submit button
        $this->add_action_buttons(true, "Save template");       
    }
    
    
}

class individual_settings_form_view_intervention extends moodleform {
    
    public function definition() {
        global $DB, $USER, $CFG;
        
        $mform =& $this->_form;
        $intervention = $DB->get_record('block_risk_monitor_int_tmp', array('id' => $this->_customdata['interventionid']));
        $course_context = context_course::instance($this->_customdata['courseid']);
        
        //Student instructions
        //$mform->addElement('header', 'description', "Description");
        $mform->addElement('static', 'instructions', '', $intervention->instructions);
        
        //Url
        if($intervention->url != null) {
            //$mform->addElement('header', 'links', "Links");
            $exturl = trim($intervention->url);
            if (!(empty($exturl) or $exturl === 'http://')) {
                
                //$fullurl = str_replace('&amp;', '&', url_get_full_url($url, $cm, $course));
                $mform->addElement('static', 'URL', '', html_writer::alist(array(html_writer::link($exturl, "link"))));
            }
        }
        
        //files
        $fs = get_file_storage();
        $files = $fs->get_area_files($course_context->id, 'block_risk_monitor', 'intervention_files', $intervention->id, 'sortorder', false); // TODO: this is not very efficient!!
        //$fs->get_area_files($usercontext->id, 'user', 'draft', $fromform->files, 'id')
        //if (count($files) > 1) {
            //$file = reset($files);
            
            //Render the files
           // $i = 0;
            foreach($files as $file) {
                //if($i != 0) {
                    
                    $url = file_encode_url("$CFG->wwwroot/pluginfile.php", '/'.$course_context->id.'/block_risk_monitor/intervention_files/'.$intervention->id.$file->get_filepath().$file->get_filename(), false);
                    $filename = $file->get_filename();
                    //$image = $this->output->pix_icon(file_file_icon($file), $filename, 'moodle', array('class'=>'icon'));
                    //$result .= html_writer::link($url,$filename);
                    //$fullurl = moodle_url::make_pluginfile_url($file->get_contextid(), $file->get_component(), $file->get_filearea(), $file->get_itemid(), $file->get_filepath(), $file->get_filename());
                    //$path = '/'.$intervention->id.'/block_risk_monitor/intervention_files'.$file->get_filepath().$file->get_filename();
                    //$fullurl = moodle_url::make_file_url('/pluginfile.php', $path);
                    $image = html_writer::empty_tag('img', array('src' => "../../pix/".file_file_icon($file).".png", 'align' => 'middle'));
                    $mform->addElement('static', 'file', '', html_writer::link($url,$image."&nbsp;".$filename));
                //}
                //$i++;
            }
        //}
    }
    
    
}

class individual_settings_form_view_category extends moodleform {
    
    public function definition() {
            
         global $DB, $USER;
         $mform =& $this->_form;
             
         $courseid = $this->_customdata['courseid'];
         $userid = $this->_customdata['userid'];
         $studentid = $this->_customdata['studentid'];
         $categoryid = $this->_customdata['categoryid'];
         $student = $DB->get_record('user', array('id' => $studentid));
         
         $mform->addElement('header', 'problem_areas', "Problem areas");
         
         $mform->addElement('header', 'actions', "Interventions");
         
         if($intervention_templates = $DB->get_records('block_risk_monitor_int_tmp', array('categoryid' => $categoryid))) {
                            
             foreach($intervention_templates as $intervention_template) {
                 if($intervention_template->description != '') {
                     $desc = $intervention_template->description;
                 }
                 else {
                     $desc = "<i>No description given</i>";
                 }
                 
                $rulegroup = array();
                $rulegroup[] =& $mform->createElement('advcheckbox', 'intervention'.$intervention_template->id);
                $rulegroup[] =& $mform->createElement('static', 'interventioncontent', '', "<b>".$intervention_template->name."</b>");
                $mform->addGroup($rulegroup, 'intervention_'.$intervention_template->id, '', ' ', false);
            
                if($DB->record_exists('block_risk_monitor_int_inst', array('studentid' => $studentid, 'interventiontemplateid' => $intervention_template->id))) {
                    $mform->setDefault('intervention'.$intervention_template->id, 1);
                }
                else {
                    $mform->setDefault('intervention'.$intervention_template->id, 0);
                }
                 //$mform->addElement('static', 'interventioncontent', '', "<li><b>".$intervention_template->name."</b></li>");   
                 $mform->addElement('static', 'interventioncontent', '', '&emsp;'.$desc);   
                 $mform->addElement('static', 'interventioncontent', '', '&emsp;'.html_writer::link(new moodle_url('view_intervention.php', array('userid' => $USER->id, 'courseid' => $courseid, 'interventionid' => $intervention_template->id, 'categoryid' => $categoryid)), 'Preview..<br><br>'));  
             }
             
             $mform->addElement('submit', 'update', 'Save');   
         }         
            
    }    
}