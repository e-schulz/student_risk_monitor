<?php

// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Strings for component 'block_risk_monitor', language 'en', branch 'MOODLE_20_STABLE'
 *
 * @package   block_anxiety_help
 * @copyright 2014 onwards Emily schulz  {@link http://moodle.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
 
$string['pluginname'] = 'Student Risk Monitor';
$string['studentpluginname'] = 'Wellbeing@School';
$string['title'] = 'Student Risk Monitor';
$string['studenttitle'] = 'Wellbeing@School';
$string['simplehtml:addinstance'] = 'Add a new Student Risk Monitor block';
$string['simplehtml:myaddinstance'] = 'Add a new Student Risk Monitor block to the My Moodle page';		//???
$string['student_questions'] = "Questionnaire";
$string['student_questions_description'] = "Short description";
$string['view_actions'] = "View actions..";

//Overview strings
$string['overview'] = "Overview";
$string['no_user'] = "There is no user!";
$string['wrong_user'] = "Wrong user trying to access";
$string['overview_body'] = "[Module description]<br><br><br>";
//Settings strings
$string['settings'] = "Settings";
$string['delete_course'] = "Delete a course:";
$string['submit_delete'] = "Delete";
$string['changessaved'] = "Changes saved.";
$string['add_course'] = "Add a course:";
$string['submit_add'] = "Add";
$string['edit-prepostambles'] = "The messages shown below are presented before and after the intervention module. Edit and save to change the messages.";
$string['preamble_textbox'] = "Message at the start of the module";
$string['postamble_textbox'] = "Message at the end of the module";
$string['save'] = "Save";
$string['add_button'] = "Add";
$string['delete_button'] = "Remove";
$string['add_all_button'] = "Add all";
$string['delete_all_button'] = "Remove all";
$string['registered_courses'] = "Registered courses";
$string['unregistered_courses'] = "Courses to add";
$string['course_templates_text'] = "Select a course to alter the messages that will be shown before and after the anxiety module.";
$string['back_to_settings'] = "Back to settings";
$string['edit_courses'] = "Edit courses";
$string['edit_templates'] = "Edit module messages";
$string['edit_courses_text'] = "Click to determine which courses to track.";
$string['edit_templates_text'] = "Click to edit the messages displayed before and after the anxiety module.";
$string['edit_categories_rules'] = "Edit categories and rules";
$string['edit_categories_rules_description'] = "Click to edit the categories and rules.";
$string['edit_interventions'] = "Edit interventions";
$string['edit_interventions_description'] = "Click to add new intervention templates or edit existing.";

//Edit categories rules strings
$string['new_category'] = "Add category";
$string['no_categories'] = "No categories created. Click 'Add category' to create one.";
$string['edit_category'] = "Edit...";
$string['add_rule'] = "Add new rule..";
$string['no_courses'] = "You are not currently tracking any courses.";
$string['add_courses'] = "Add courses..";
$string['edit_rule'] = "Edit...";
$string['new_custom_rule'] = "Create custom rules..";
$string['back_to_categories'] = "Back to categories and rules";


//Course strings
$string['submitintervention'] = " Generate intervention ";
$string['unread'] = "Unread";
$string['read'] = "Marked as read";
$string['new'] = "New";
$string['intervention'] = "Intervention generated";
$string['low'] = "Low";
$string['med'] = "Med";
$string['high'] = "High";

//Custom rule strings
$string['new_custom'] = "Create new custom rule";
$string['high_risk'] = "High risk";
$string['moderate_risk'] = "Moderate risk";
$string['low_risk'] = "Low risk";

//Errors
$string['errorinsertexam'] = "Error trying to add exam to database.";
$string['errorinsertcourse'] = "Error trying to add course to database.";
$string['errorcoursenotexist'] = "Error: course not found in database.";
$string['errorinsertcategory'] = "Error trying to add category to database.";
$string['errweightingnotnumeric'] = "Error: weighting must be a number";
$string['errweightingnotinrange'] = "Error: weighting must be between 0 and 100%";
$string['errorinsertrule'] = "Error trying to add rule to database.";


//Templates
$string['preamble-template'] = 'This is a small module designed to help with any stress or anxiety you might be feeling over the upcoming exam for this subject.<br>'
        . 'There is a reading followed by four short answer questions - your responses are not recorded and are completely anonymous. Feel free to write out your answers on paper if it will make you feel more comfortable.<br><br>';

$string['postamble-template'] = 'Thankyou for taking the time to complete the module. <br>'
        . 'Research shows that encouraging students to view their performance as largely due to their persistence and effort (studying better, taking notes in class, etc.) leads to students feeling more motivated and performing significantly better in the future.<br>'
        . 'In answering the previous set of questions, hopefully you were able to relate these principles to your own academic experiences.<br><br>';

//New rule
$string['weighting_description'] = "The weighting given to a rule determines how important the rule is within a category. Rules with higher weighting will count more towards the risk rating within a category. If rules already exist in this category, they will be automatically adjusted based on the weighting given to this rule.";
$string['default_rule'] = "Default rule";
$string['custom_rule'] = "Custom rule";

//Default rules
$string['name_not_logged_in'] = "";
$string['name_grade_less_than'] = "";
$string['name_grade_greater_than'] = "";
$string['name_missed_deadlines'] = "";

$string['description_not_logged_in'] = "";
$string['description_grade_less_than'] = "";
$string['description_grade_greater_than'] = "";
$string['description_missed_deadlines'] = "";

//Icon paths
$string['no_risk_icon'] = "pix/no_risk_icon.png";
$string['low_risk_icon'] = "pix/low_risk_icon.png";
$string['moderate_risk_icon'] = "pix/moderate_risk_icon.png";
$string['high_risk_icon'] = "pix/high_risk_icon.png";
$string['edit_icon'] = "../../pix/t/edit.png";
$string['delete_icon'] = "../../pix/t/delete.png";
$string['add_icon'] = "../../pix/t/add.png";
?>