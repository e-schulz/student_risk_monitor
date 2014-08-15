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
 * Strings for component 'block_anxiety_teacher', language 'en', branch 'MOODLE_20_STABLE'
 *
 * @package   block_anxiety_help
 * @copyright 2014 onwards Emily schulz  {@link http://moodle.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
 
$string['pluginname'] = 'Test Anxiety Help';
$string['title'] = 'Test Anxiety Help';
$string['simplehtml:addinstance'] = 'Add a new Test Anxiety Help block';
$string['simplehtml:myaddinstance'] = 'Add a new Test Anxiety Help block to the My Moodle page';		//???

//Overview strings
$string['overview'] = "Overview";
$string['no_user'] = "There is no user!";
$string['wrong_user'] = "Wrong user trying to access";
$string['overview_body'] = "This module is designed to help detect students who may be experiencing anxiety about an upcoming exam, using their online activity and grades, within a week of an upcoming exam<br>"
        . "Please note this module uses the Moodle Calendar to determine whether any exams are coming up, searching for events with 'exam' or 'test' in the name, so please ensure any exams have been added to the calendar in order for the module to work properly.<br> "
        . "Click on the course tabs below to view data for that course.<br>";

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

//Course strings
$string['submitintervention'] = " Generate intervention ";
$string['unread'] = "Unread";
$string['read'] = "Marked as read";
$string['intervention'] = "Intervention generated";
$string['low'] = "Low";
$string['med'] = "Med";
$string['high'] = "High";


//Errors
$string['errorinsertexam'] = "Error trying to add exam to database.";
$string['errorinsertcourse'] = "Error trying to add course to database.";

//Templates
$string['preamble-template'] = 'This is a small module designed to help with any stress or anxiety you might be feeling over the upcoming exam for this subject.<br>'
        . 'There is a reading followed by four short answer questions - your responses are not recorded and are completely anonymous. Feel free to write out your answers on paper if it will make you feel more comfortable.<br><br>';

$string['postamble-template'] = 'Thankyou for taking the time to complete the module. <br>'
        . 'Research shows that encouraging students to view their performance as largely due to their persistence and effort (studying better, taking notes in class, etc.) leads to students feeling more motivated and performing significantly better in the future.<br>'
        . 'In answering the previous set of questions, hopefully you were able to relate these principles to your own academic experiences.<br><br>';

?>