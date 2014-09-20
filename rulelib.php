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
require_once("modulefunctionslib.php");
require_once($CFG->dirroot."/mod/forum/lib.php");


class risk_calculator {
        
    public $courseid;
    
    private $enrolled_students;
    private $course;
    
    //Array of total course clicks, indexed by userid
    private $course_clicks;
    
    //Array of clicks per session, indexed by userid
    private $clicks_per_session;
    
    //Array of average session times, indexed by userid
    private $average_session_times;
    
    //Number of sessions, indexed by userid
    private $number_of_sessions;
    
    //Number of forum posts, indexed by userid
    private $number_forum_posts;
    
    //Number of forum posts read, indexed by userid
    private $number_forum_posts_read;
    
    //Time spent in forum, indexed by userid
    private $total_forum_time;
    
    //Average time to complete activities: 2D array, each key corresponds to module instance id
    private $activity_completion_times;
    
    private $course_modules;
    
    public function __construct($course) {
        global $DB;
        $this->courseid = $course;
        $this->enrolled_students = block_risk_monitor_get_enrolled_students($course);
        $this->course = $DB->get_record('course', array('id' => $course));
        $this->initialise();
        $this->calculate_averages();
    }
    
    private function initialise() {
        global $DB;
        $this->course_clicks = array();
        $this->clicks_per_session = array();
        $this->average_session_times = array();
        $this->number_of_sessions = array();
        $this->number_forum_posts = array();
        $this->number_forum_posts_read = array();
        $this->total_forum_time = array();           
        $this->course_modules = array();
        $fast_mod_info = get_fast_modinfo($this->courseid);
        $module_names = array_keys($fast_mod_info->instances);
        //$this->course_modules = get_fast_modinfo($this->courseid)->instances;
        foreach($module_names as $modname) {
            $module_type = array();
            $module = $DB->get_record('modules', array('name' => $modname));
            $module_instance_ids = array_keys($fast_mod_info->instances[$modname]);
            foreach($module_instance_ids as $mod_instance_id) {
                $module_type[$mod_instance_id] = $DB->get_record($modname, array('id' => $mod_instance_id));
                $module_type[$mod_instance_id]->cm = $DB->get_record('course_modules', array('course' => $this->courseid, 'instance' => $mod_instance_id, 'module' => $module->id), '*', MUST_EXIST);
            }
            $this->course_modules[$modname] = $module_type;
        }
    }
    
    private function calculate_averages() {
        $this->calculate_average_course_clicks();
        $this->calculate_average_clicks_and_time_per_session();
        $this->calculate_average_forum_posts_added();
        $this->calculate_average_forum_posts_read();
        $this->calculate_average_forum_time();    
        $this->calculate_average_time_to_finish_activities();
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
            case 'EXAM_COMING_UP':
                $risk_rating = $this->exam_approaching_risk($user, $value);
                break;   
            case 'MULTIPLE_SUBMISSIONS':
                $risk_rating = $this->multiple_submissions_risk($user, $value);
                break;            
            case 'TIME_TO_FINISH_ACTIVITY':
                $risk_rating = $this->time_to_finish_activity_risk($user, $value);
                break;            
            case 'TIME_TO_START_ACTIVITY':
                $risk_rating = $this->time_to_view_activity_risk($user, $value);
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
        if($user->currentlogin != 0) {
            $most_recent_login_time = $user->currentlogin;

            //Calculate how many days it has been since then (time is in seconds)
            $seconds_since = time() - $most_recent_login_time;

            //convert to days
            $days_since = $seconds_since/(60*60*24);

            //If days >= $value, risk = 100 else risk = 0 
            if ($days_since >= $value) {
                $risk_rating = 100;
            }
        }
        else {
            $risk_rating = 0;
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
            $max_grade = $course_grade_item->grademax;
            $current_grade = $grade_grade->finalgrade;
            $percent = intval(($current_grade/$max_grade)*100);
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
            $max_grade = $course_grade_item->maxgrade;
            $current_grade = $grade_grade->finalgrade;
            $percent = intval(($current_grade/$max_grade)*100);
            if($percent > $value) {
                $risk_rating = 100;
            }
        }

        return $risk_rating;    
    }

    public function missed_deadlines_risk($user, $value) {

        global $DB;
        $missed_deadlines = 0;

        //Quizzes.
            foreach(array_keys($this->course_modules) as $modname) {
                
                foreach($this->course_modules[$modname] as $mod_inst) {
                
                    $deadline_function = "block_risk_monitor_missed_".$modname."_deadline";
                    if(function_exists($deadline_function) && $deadline_function($user->id, $mod_inst)) {
                        $missed_deadlines++;
                    }
                }
            }

        if($missed_deadlines >= $value) {
            return 100;
        }
        else {
            return 0;   
        }
    }

    //
    function activities_failed_risk($user, $value) {
        
        global $DB;
        $activities_failed = 0;
        
        //Loop thru all the activities.
            foreach(array_keys($this->course_modules) as $modname) {
                
                foreach($this->course_modules[$modname] as $mod_inst) {
                    $grades = grade_get_grades($this->courseid, 'mod', $modname, $mod_inst->id, $user->id);

                    //Only using numerical grades, not scales.
                    foreach($grades->items as $gradeitem) {
                        $gradepass = $gradeitem->gradepass;
                        $usergrade = $gradeitem->grades[$user->id]->grade;
                        if($usergrade < $gradepass) {
                            $activities_failed++;
                        }
                    }
                }
            }
        
        if($activities_failed > $value) {
            return 100;
        }
        return 0;
    }

    //
    function forum_posts_added_risk($user, $value) {
        
        $total_students = count($this->number_forum_posts);
        
        $average = array_sum($this->number_forum_posts)/count($this->number_forum_posts);
        $student_value = array_search($user->id, $this->number_forum_posts);
        
        if($student_value < ($value/100)*$average) {    
            return 100;
        }
        return 0;
    }

    //
    function forum_posts_read_risk($user, $value) {
        $total_students = count($this->number_forum_posts_read);
        
        $average = array_sum($this->number_forum_posts_read)/count($this->number_forum_posts_read);
        $student_value = array_search($user->id, $this->number_forum_posts_read);
        
        if($student_value < ($value/100)*$average) {    
            return 100;
        }
        return 0;
    }

    //Clicking into forum, to clicking out. = one forum session, add these up.
    function total_forum_time_risk($user, $value) {
        $total_students = count($this->total_forum_time);
        
        $average = array_sum($this->total_forum_time)/count($this->total_forum_time);
        $student_value = array_search($user->id, $this->total_forum_time);
        
        if($student_value < ($value/100)*$average) {    
            return 100;
        }
        return 0;
    }

    //
    function course_clicks_risk($user, $value) {
        $total_students = count($this->course_clicks);
        
        $average = array_sum($this->course_clicks)/count($this->course_clicks);
        $student_value = array_search($user->id, $this->course_clicks);
        
        if($student_value < ($value/100)*$average) {    
            return 100;
        }
        return 0;
    }

    //Session = clicking into course, to clicking out.
    function average_session_clicks_risk($user, $value) {
        $total_students = count($this->clicks_per_session);
        if(array_key_exists($user->id, $this->clicks_per_session)) {
            $average = array_sum($this->clicks_per_session)/count($this->clicks_per_session);
            $student_value = array_search($user->id, $this->clicks_per_session);

            if($student_value < ($value/100)*$average) {    
                return 100;
            }
        }
        return 0;        
    }

    //From when student clicks into course, to clicks out of or logs out.
    function average_session_duration_risk($user, $value) {
        $total_students = count($this->average_session_times);
        
        if(array_key_exists($user->id, $this->average_session_times)) {
            $average = array_sum($this->average_session_times)/count($this->average_session_times);
            $student_value = array_search($user->id, $this->average_session_times);

            if($student_value < ($value/100)*$average) {    
                return 100;
            }
        }
        return 0;        
    }
    
    function exam_approaching_risk($user, $value) {
        $time_from_now = time() + ($value*24*60*60);
        
        //Get the upcoming events for this course
       $events = calendar_get_events(time(), $time_from_now, false, false, $this->courseid);

       //parse the events and search for "exam" or "test"
       foreach ($events as $event) {

           //get the name
           $name = $event->name;

           //parse for exam or test
           if ((stripos($name,'exam') !== false) || (stripos($name,'test') !== false)) {
               //found an exam!
               return 100;
           }
       }
       
       return 0;
    }
    
    function multiple_submissions_risk($user, $value) {
        global $DB;
        foreach(array_keys($this->course_modules) as $modname) {
                
            foreach($this->course_modules[$modname] as $mod_inst) {
                $multiple_submissions_function = "block_risk_monitor_multiple_submissions_".$modname;
                if(function_exists($multiple_submissions_function)) {
                    if($ret_value = $multiple_submissions_function($user->id, $mod_inst, $value)) {
                        return $ret_value;
                    }
                }
            }
        }
        return 0;        
    }
    
    function time_to_finish_activity_risk($user, $value) {
        
        $total_activities_above_average = 0;
        foreach($this->activity_completion_times as $cm_activity_completion_times) {
            $total_students = count($cm_activity_completion_times);

            if($total_students < 10) {
                break;
            }
            
            if(array_key_exists($user->id, $cm_activity_completion_times)) {
                $average = array_sum($cm_activity_completion_times)/$total_students;
                $student_value = array_search($user->id, $cm_activity_completion_times);

                if($student_value > ($value/100)*$average) {    
                    $total_activities_above_average++;
                }
            }
        }
        
        if($total_activities_above_average > 0) {
            return 100;
        }
        return 0;          
        
    }
    
    function time_to_submit_activity_risk($user, $value) {
        
        global $DB;
        foreach(array_keys($this->course_modules) as $modname) {
                
            foreach($this->course_modules[$modname] as $mod_inst) {
                
           
                $get_deadline_function = "block_risk_monitor_time_before_deadline_".$module->name;
                if(function_exists($get_deadline_function)) {
                    return $get_deadline_function($user->id, $mod_inst, $value);
                }
            }
        }
        return 0;
    }
    
    function time_to_view_activity_risk($user, $value) {
        
        global $DB;
        foreach(array_keys($this->course_modules) as $modname) {
                
            foreach($this->course_modules[$modname] as $mod_inst) {
            
                $get_deadline_function = "block_risk_monitor_get_deadline_".$modname;
                if(function_exists($get_deadline_function) && $deadline = $get_deadline_function($user->id, $mod_inst) != 0) {

                    $params = array();
                    $selector = "l.cmid = ".$mod_inst->cm->id." AND l.userid = ".$user->id." AND l.action='view'";
                    $totalcount = 0;
                    $logs = get_logs($selector, null, 'l.time ASC', '', '', $totalcount);        
                    if(count($logs)) {
                        $first_view = reset($logs)->time;
                        if($deadline - $value*60*60*24 < $first_view) {
                            return 100;
                        }
                    }
                }
            }
        }
        return 0;
    }
    //// THE FOLLOWING METHODS CALCULATE THE CUTOFF FOR RELATIVE RISKS


    function calculate_average_forum_posts_added() {
        foreach($this->enrolled_students as $student) {
            $this->number_forum_posts[$student->id] = count(forum_get_posts_by_user($student, array($this->course))->posts);
        }
        asort($this->number_forum_posts);
    }

    function calculate_average_forum_posts_read() {
        
        //Get the forums in this course
        
        foreach($this->enrolled_students as $student) {
            $forums = forum_get_readable_forums($student->id, $this->courseid);
            $total_forum_posts_read = 0;
            foreach($forums as $forum) { 
                $total_forum_posts_read += count(forum_tp_get_read_records($student->id, -1, -1, $forum->id));
            }
            $this->number_forum_posts_read[$student->id] = $total_forum_posts_read;
        }
        asort($this->number_forum_posts_read);
    }

    function calculate_average_forum_time() {
        
       foreach($this->enrolled_students as $student) {
            $totalcount = 0;
            $selector = "l.userid = ".$student->id;
            $logrows = get_logs($selector, null, 'l.time DESC', '', '', $totalcount);
            $found = false;
            $total_forum_time = 0;
            $session_start = 0;
            $prev_log_row = null;
            foreach($logrows as $logrow) {
                if($logrow->module !== 'forum' || $logrow->course != $this->courseid) {
                    if($found == true && $prev_log_row != null) {
                        $total_forum_time += $session_start - $prev_log_row->time;
                    }
                    $found = false;
                    continue;
                }
                
                if($found == false) {
                    $session_start = $logrow->time;
                    $found = true;
                }
                $prev_log_row = $logrow;
                
            }
                        
            $this->total_forum_time[$student->id] = $total_forum_time;
        }
        
        //Sort ascending
        asort($this->total_forum_time);
    }

    function calculate_average_course_clicks() {
        
        //Total course clicks.
        foreach($this->enrolled_students as $student) {
            //Get avg clicks
            $clicks = get_logs_usercourse($student->id, $this->courseid, $this->course->timecreated);
            $this->course_clicks[$student->id] = count($clicks);
        }
        
    }

    function calculate_average_clicks_and_time_per_session() {
                
        foreach($this->enrolled_students as $student) {
            $totalcount = 0;
            $selector = "l.userid = ".$student->id;
            $logrows = get_logs($selector, null, 'l.time DESC', '', '', $totalcount);
            $found = false;
            $clicks = 0;
            $clicks_per_session = array();
            $time_per_session = array();
            $session_start = 0;
            $prev_log_row = null;
            foreach($logrows as $logrow) {
                if($logrow->course !== $this->courseid) {
                    if($found == true && $prev_log_row != null) {
                        $time_per_session[] = $session_start - $prev_log_row->time;
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
                $prev_log_row = $logrow;
                
            }
            
            $number_of_sessions = count($time_per_session);
            
            $this->number_of_sessions[$student->id] = $number_of_sessions;
            if($number_of_sessions != 0) {
                $this->average_session_times[$student->id] = (array_sum($time_per_session) / $number_of_sessions);
                $this->clicks_per_session[$student->id] = (array_sum($clicks_per_session) / $number_of_sessions);
            }
        }
        
        //Sort ascending
        asort($this->number_of_sessions);
        asort($this->average_session_times);
        asort($this->clicks_per_session);
    }
    
    function calculate_average_time_to_finish_activities() {
 
        global $DB;
        foreach($this->enrolled_students as $student) {

            foreach(array_keys($this->course_modules) as $modname) {
                
                foreach($this->course_modules[$modname] as $mod_inst) {
                
                    $time_function = "block_risk_monitor_time_to_finish_".$modname;
                    if(function_exists($time_function)) {
                        $time_to_finish = $time_function($student->id, $mod_inst, $mod_inst->cm);
                        if($time_to_finish != 0) {
                            $this->activity_completion_times[$mod_inst->cm->id][$student->id] = $time_to_finish;
                        }
                    }
                }
            }
               
        }
    }


}