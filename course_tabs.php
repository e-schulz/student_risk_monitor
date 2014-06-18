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
 * Prints navigation tabs
 *
 * @package    core_group
 * @copyright  2010 Petr Skoda (http://moodle.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
    $row = array();
            //Get the context instances where the user is the teacher
        $roleassigns = $DB->get_records('role_assignments', array('userid' => $USER->id, 'roleid' => 3), 'contextid');

        $teachercourses = array();

        foreach ($roleassigns as $roleassign) {

            //Get only the context instances where context = course 
            $contextinstances = $DB->get_records('context', array('contextlevel' => 50, 'id' => $roleassign->contextid));

            //add to the courses
            $teachercourses = array_merge($teachercourses, $contextinstances);
        }

        foreach($teachercourses as $teachercourse) {

            //Get the course.
            $course = $DB->get_record('course', array('id' => $teachercourse->instanceid));

            $row[] = new tabobject('course'.$course->id,
                            new moodle_url('/blocks/anxiety_teacher/course_page.php', array('courseid' => $course->id)),
                            $course->shortname);
        }
        
    echo '<div class="coursedisplay">';
    echo $OUTPUT->tabtree($row, $currentcoursetab);
    echo '</div>';
