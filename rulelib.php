<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 * 
 * TODO- TEST EVERYTHING IN THIS FILE.
 */

defined('MOODLE_INTERNAL') || die();
require_once("locallib.php");
require_once($CFG->libdir . '/gradelib.php');
require_once ($CFG->libdir.'/completionlib.php');
require_once("moduledeadlineslib.php");

class risk_calculator {
        
    public $courseid;
    
    private $enrolled_students;
    private $course;
    
    //Array of total course clicks, indexed by userid
    private $course_clicks;
    
    //Array of clicks per session, indexed by userid
    public $clicks_per_session;
    
    //Array of average session times, indexed by userid
    public $average_session_times;
    
    //Number of sessions, indexed by userid
    public $number_of_sessions;
    
    public $last_update;
    
    public function __construct($course) {
        global $DB;
        $this->courseid = $course;
        $this->course_clicks = null;
        $this->clicks_per_session = null;
        $this->average_session_times = null;
        $this->enrolled_students = block_risk_monitor_get_enrolled_students($course);
        $this->course = $DB->get_record('course', array('id' => $course));
        $this->last_update = $this->course->timecreated;
        $this->refresh();
    }
    
    //Updates the globals
    public function refresh() {
        $this->calculate_average_course_clicks();
        $this->calculate_average_clicks_and_time_per_session();
        $this->last_update = time();
    }
    
    //This function returns a risk rating between 0 and 100, given the action userid and value.
    function calculate_risk_rating($action, $user, $value) {

        $risk_rating = 0;

        //These actions must match actions specified in rules.php
        switch($action){
            CASE 'NOT_LOGGED_IN':
                $risk_rating = $this->not_logged_in_risk($user, $value);
                break;
            case 'GRADE_LESS_THAN':
                $risk_rating = $this->grade_less_than_risk($user, $value);
                break;
            case 'GRADE_GREATER_THAN':
                $risk_rating = $this->grade_greater_than_risk($user, $value);
                break;
            case 'MISSED_DEADLINES':
                $risk_rating = $this->missed_deadlines_risk($user, $value);
                break;
            CASE 'ACTIVITIES_FAILED':
                $risk_rating = $this->activities_failed_risk($user, $value);
                break;
            case 'LOW_FORUM_MESSAGES_POSTED':
                $risk_rating =$this->forum_posts_added_risk($user, $value);
                break;
            case 'LOW_FORUM_MESSAGES_READ':
                $risk_rating = $this->forum_posts_read_risk($user, $value);
                break;
            case 'LOW_TOTAL_FORUM_TIME':
                $risk_rating = $this->total_forum_time_risk($user, $value);
                break;
            CASE 'LOW_TOTAL_COURSE_CLICKS':
                $risk_rating = $this->course_clicks_risk($user, $value);
                break;
            case 'LOW_AVERAGE_CLICKS_PER_SESSION':
                $risk_rating = $this->average_session_clicks_risk($user, $value);
                break;
            case 'LOW_AVERAGE_SESSION_DURATION':
                $risk_rating = $this->average_session_duration_risk($user, $value);
                break;
            case 'EXAM_APPROACHING':
                $risk_rating = $this->exam_approaching_risk($user, $value);
                break;        
            default:
                break;
        }

        return $risk_rating;
    }

    ///THE FOLLOWING METHODS CALCULATE THE RISK RATING FOR EACH RULE, RETURNING A VALUE BETWEN 0 (LOW RISK) AND 100 (HIGH RISK)

    //this function returns 100 if user has not logged in for days greater than value, else 0
    function not_logged_in_risk($user, $value) {

        $risk_rating = 0;

        //Get the most recent login of the student.
        $most_recent_login_time = $user->currentlogin;

        //Calculate how many days it has been since then (time is in seconds)
        $seconds_since = time() - $most_recent_login_time;

        //convert to days
        $days_since = $seconds_since/(60*60*24);

        //If days >= $value, risk = 100 else risk = 0 
        if ($days_since >= $value) {
            $risk_rating = 100;
        }

        return $risk_rating;
    }


    function grade_less_than_risk($user, $value) {

        global $DB;
        $risk_rating = 0;

        //Get the grade item associated with the course.
        $course_grade_item = grade_item::fetch_course_item($this->courseid);

        //Get the grade_grade instance, if it exists.
        if($grade_grade = $DB->get_record('grade_grades', array('itemid' => $course_grade_item->id, 'userid' => $user->id))) {
            $max_grade = $grade_grade->rawgrademax;
            $final_grade = $grade_grade->rawgrade;
            $percent = intval(($final_grade/$max_grade)*100);
            if($percent < $value) {
                $risk_rating = 100;
            }
        }

        return $risk_rating;
    }

    function grade_greater_than_risk($user, $value) {

        global $DB;
        $risk_rating = 0;

        //Get the grade item associated with the course.
        $course_grade_item = grade_item::fetch_course_item($this->courseid);

        //Get the grade_grade instance, if it exists.
        if($grade_grade = $DB->get_record('grade_grades', array('itemid' => $course_grade_item->id, 'userid' => $user->id))) {
            $max_grade = $grade_grade->rawgrademax;
            $final_grade = $grade_grade->rawgrade;
            $percent = intval(($final_grade/$max_grade)*100);
            if($percent > $value) {
                $risk_rating = 100;
            }
        }

        return $risk_rating;    
    }

    public function missed_deadlines_risk($user, $value) {

        global $DB;
        $missed_deadlines = 0;
        $modinfo = get_fast_modinfo($this->courseid, $user->id);

        //Quizzes.
        foreach($modinfo->cms as $cm) {
                if (!$cm->uservisible or !$cm->has_view()) {
                    continue;
                }        

                switch($cm->modname) {
                    case 'quiz':
                        if(block_risk_monitor_missed_quiz_deadline($user->id, $cm)) {
                            $missed_deadlines++;
                        }
                        break;
                    case 'assignment':
                        if(block_risk_monitor_missed_assignment_deadline($user->id, $cm)) {
                            $missed_deadlines++;
                        }
                        break;
                    case 'assign':
                        if(block_risk_monitor_missed_assign_deadline($user->id, $cm)) {
                            $missed_deadlines++;
                        }
                        break;                    
                    default:
                        break;
                }
        }
        //Assignments.

        /*$course = $DB->get_record('course', array('id' => $this->courseid));
        $completion_info = new completion_info($course);

        $completion_modules = $completion_info->get_activities();

        foreach($completion_modules as $completion_module) {
            echo "Got completion modules<br>";
            $completion_data = $completion_info->get_data($completion_module, false, $userid);

            if($cm->completionexpected != 0 && time() > $cm->completionexpected && $completion_data->completionstate == 0) {
                $missed_deadlines++;
            }
        }*/

        if($missed_deadlines >= $value) {
            return 100;
        }
        else {
            return 0;   
        }
    }

    //
    function activities_failed_risk($user, $value) {

    }

    //
    function forum_posts_added_risk($user, $value) {

    }

    //
    function forum_posts_read_risk($user, $value) {

    }

    //Clicking into forum, to clicking out. = one forum session, add these up.
    function total_forum_time_risk($user, $value) {

    }

    //
    function course_clicks_risk($user, $value) {
        $total_students = count($this->course_clicks);
        
        $student_position = array_search($user->id, array_keys($this->course_clicks));
        if($student_position < round($total_students/4)) {     //High risk - bottom quartile
            return 100;
        }
        else if($student_position < round($total_students/2)) {    //Moderate risk- second from bottom quartile.
            return 50;
        }
        
        return 0;
    }

    //Session = clicking into course, to clicking out.
    function average_session_clicks_risk($user, $value) {
        $total_students = count($this->clicks_per_session);
        
        $student_position = array_search($user->id, array_keys($this->clicks_per_session));
        if($student_position < round($total_students/4)) {     //High risk - bottom quartile
            return 100;
        }
        else if($student_position < round($total_students/2)) {    //Moderate risk- second from bottom quartile.
            return 50;
        }
        
        return 0;        
    }

    //From when student clicks into course, to clicks out of or logs out.
    function average_session_duration_risk($user, $value) {
        $total_students = count($this->average_session_times);
        
        $student_position = array_search($user->id, array_keys($this->average_session_times));
        if($student_position < round($total_students/4)) {     //High risk - bottom quartile
            return 100;
        }
        else if($student_position < round($total_students/2)) {    //Moderate risk- second from bottom quartile.
            return 50;
        }
        
        return 0;        
    }

    function exam_approaching_risk($user, $value) {

    }


    //// THE FOLLOWING METHODS CALCULATE THE CUTOFF FOR RELATIVE RISKS


    function calculate_average_forum_posts_added() {

    }

    function calculate_average_forum_posts_read() {

    }

    function calculate_average_forum_time() {

    }

    function calculate_average_course_clicks() {

        if($this->course_clicks == null) {
            $this->course_clicks = array();
        }
        
        //Total course clicks.
        foreach($this->enrolled_students as $student) {
            //Get avg clicks
            $clicks = get_logs_usercourse($student->id, $this->courseid, $this->last_update);
            if(array_key_exists($student->id, $this->course_clicks)) {
                $this->course_clicks[$student->id] += $clicks;
            }
            else {
                $this->course_clicks[$student->id] = $clicks;
            }
        }
        
        //Sort ascending
        asort($this->course_clicks);
    }

    function calculate_average_clicks_and_time_per_session() {
        if($this->clicks_per_session == null) {
            $this->clicks_per_session = array();
            $this->number_of_sessions = array();
            $this->average_session_times = array();
        }
        
        //Get all logs for student from course creation time.
        
        foreach($this->enrolled_students as $student) {
            
            $selector = "l.userid = ".$student->id." AND l.time > ".$this->last_update;
            $logrows = get_logs($selector, null, 'l.time DESC', '', '', $totalcount);
            $found = false;
            $clicks = 0;
            $clicks_per_session = array();
            $time_per_session = array();
            $session_start = 0;
            foreach($logrows as $logrow) {
                if($logrow->course !== $this->courseid) {
                    if($found == true) {
                        $time_per_session[] = $session_start - $logrow->time;
                        $clicks_per_session[] = $clicks;
                    }
                    $found = false;
                    continue;
                }
                
                if($found == false) {
                    $clicks = 0;
                    $session_start = $logrow->time;
                    $found = true;
                }
                $clicks++;
            }
            
            $new_number_of_sessions = count($time_per_session);
            if($new_number_of_sessions != 0) {
                if(array_key_exists($student->id, $this->number_of_sessions)) {
                    $prev_number_of_sessions = $this->number_of_sessions[$student->id];

                    $prev_clicks = $this->clicks_per_session[$student->id]*$prev_number_of_sessions;
                    $cur_clicks = array_sum($clicks_per_session);
                    $new_clicks_per_session = ($prev_clicks + $cur_clicks)/($prev_number_of_sessions + $new_number_of_sessions);
                    $this->clicks_per_session[$student->id] = $new_clicks_per_session;

                    $prev_time = $this->average_session_times[$student->id]*$prev_number_of_sessions;
                    $cur_time = array_sum($time_per_session);
                    $new_time_per_session = ($prev_time + $cur_time)/($prev_number_of_sessions + $new_number_of_sessions);
                    $this->average_session_times[$student->id] = $new_time_per_session;

                    $this->number_of_sessions[$student->id] += $new_number_of_sessions;
                }
                else {
                    $this->number_of_sessions[$student->id] = $new_number_of_sessions;
                    $this->average_session_times[$student->id] = (array_sum($time_per_session) / $new_number_of_sessions);
                    $this->clicks_per_session[$student->id] = (array_sum($clicks_per_session) / $new_number_of_sessions);
                }
            }
        }
        
        //Sort ascending
        asort($this->number_of_sessions);
        asort($this->average_session_times);
        asort($this->clicks_per_session);
    }


}