<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

//Check the deadlines depending on the module

function block_risk_monitor_missed_assignment_deadline($userid, $cm) {
    //Get the assignment.
    global $DB;
    $assignment_instance = $DB->get_record('assignment', array('id' => $cm->instance));
    
    //check due date
    $time_due = $assignment_instance->timedue;
    if(!$DB->record_exists('assignment_submissions', array('userid' => $userid, 'assignment' => $assignment_instance->id)) && $time_due != 0 && time() > $time_due) {
        return true;
    }
    return false;
}

function block_risk_monitor_missed_quiz_deadline($userid, $cm) {
    global $DB;
    $quiz_instance = $DB->get_record('quiz', array('id' => $cm->instance));
    
    //check due date
    $time_due = $quiz_instance->timeclose;
    if(!$DB->record_exists('quiz_attempts', array('userid' => $userid, 'quiz' => $quiz_instance->id)) && $time_due != 0 && time() > $time_due) {
        return true;
    }
    return false;  
}

function block_risk_monitor_missed_assign_deadline($userid, $cm) {
    //Get the assign
    global $DB;
    $assign_instance = $DB->get_record('assign', array('id' => $cm->instance));
    
    //check due date
    $time_due = $assign_instance->duedate;
    if(!$DB->record_exists('assign_submission', array('userid' => $userid, 'assignment' => $assign_instance->id)) && $time_due != 0 && time() > $time_due) {
        return true;
    }
    return false;    
}

function block_risk_monitor_missed_lesson_deadline($userid, $cm) {
    global $DB;
    $lesson_instance = $DB->get_record('lesson', array('id' => $cm->instance));
    
    //check due date
    $time_due = $lesson_instance->deadline;
    if(!$DB->record_exists('lesson_attempts', array('userid' => $userid, 'lessonid' => $lesson_instance->id)) && $time_due != 0 && time() > $time_due) {
        return true;
    }
    return false;        
}

/*function block_risk_monitor_missed_scorm_deadline($userid, $cm) {
    
    global $DB;
    $scorm_instance = $DB->get_record('scorm', array('id' => $cm->instance));
    
    //check due date
    $time_due = $scorm_instance->timeclose;
    if(!$DB->record_exists('scorm_scoes_track', array('userid' => $userid, 'scormid' => $scorm_instance->id)) && $time_due != 0 && time() > $time_due) {
        return true;
    }
    return false;           
}*/

function block_risk_monitor_missed_workshop_deadline($userid, $cm) {
    global $DB;
    $workshop_instance = $DB->get_record('workshop', array('id' => $cm->instance));
    
    //check due date
    $time_due = $workshop_instance->submissionend;
    if(!$DB->record_exists('workshop_submissions', array('authorid' => $userid, 'workshopid' => $workshop_instance->id)) && $time_due != 0 && time() > $time_due) {
        return true;
    }
    return false;      
}

function block_risk_monitor_time_to_finish_assignment($userid, $cm) {
    global $DB;
    $assignment_instance = $DB->get_record('assignment', array('id' => $cm->instance));
    
    if($DB->record_exists('assignment_submissions', array('userid' => $userid, 'assignment' => $assignment_instance->id))) {
        //Get first submission
        $submissions = $DB->get_records('assignment_submissions', array('userid' => $userid, 'assignment' => $assignment_instance->id), "timecreated ASC");
        $first_submission_time = reset($submissions)->timecreated;
        
        //Get first view
        $params = array();
        $selector = "l.cmid = ".$cm->id." AND l.userid = ".$userid." AND l.action='view'";
        $totalcount = 0;
        $logs = get_logs($selector, null, 'l.time ASC', '', '', $totalcount);        
        if(count($logs)) {
            return $first_submission_time - reset($logs)->time;
        }
    }
    return 0;   
}

function block_risk_monitor_time_to_finish_quiz($userid, $cm) {
    global $DB;
    $quiz_instance = $DB->get_record('quiz', array('id' => $cm->instance));
    
    if($DB->record_exists('quiz_attempts', array('userid' => $userid, 'quiz' => $quiz_instance->id))) {
        //Get first submission
        $submissions = $DB->get_records('quiz_attempts', array('userid' => $userid, 'quiz' => $quiz_instance->id), "timestart ASC");
        $first_submission = reset($submissions);
        return $first_submission->timefinish - $first_submission->timestart;
    }
    return 0;      
}

function block_risk_monitor_time_to_finish_assign($userid, $cm) {
    global $DB;
    $assign_instance = $DB->get_record('assign', array('id' => $cm->instance));
    
    if($DB->record_exists('assign_submission', array('userid' => $userid, 'assignment' => $assign_instance->id))) {
        //Get first submission
        $submissions = $DB->get_records('assign_submission', array('userid' => $userid, 'assignment' => $assign_instance->id), "timecreated ASC");
        $first_submission_time = reset($submissions)->timecreated;
        
        //Get first view
        $params = array();
        $selector = "l.cmid = ".$cm->id." AND l.userid = ".$userid." AND l.action='view'";
        $totalcount = 0;
        $logs = get_logs($selector, null, 'l.time ASC', '', '', $totalcount);        
        if(count($logs)) {
            return $first_submission_time - reset($logs)->time;
        }
    }
    return 0;     
}

function block_risk_monitor_time_to_finish_lesson($userid, $cm) {
    global $DB;
    $lesson_instance = $DB->get_record('lesson', array('id' => $cm->instance));
    
    if($DB->record_exists('lesson_attempts', array('userid' => $userid, 'lessonid' => $lesson_instance->id))) {
        //Get first submission
        $submissions = $DB->get_records('lesson_attempts', array('userid' => $userid, 'lessonid' => $lesson_instance->id), "timeseen ASC");
        $first_submission_time = reset($submissions)->timeseen;
        
        //Get first view
        $params = array();
        $selector = "l.cmid = ".$cm->id." AND l.userid = ".$userid." AND l.action='view'";
        $totalcount = 0;
        $logs = get_logs($selector, null, 'l.time ASC', '', '', $totalcount);        
        if(count($logs)) {
            return $first_submission_time - reset($logs)->time;
        }
    }
    return 0;       
}

function block_risk_monitor_time_to_finish_workshop($userid, $cm) {
    global $DB;
    $workshop_instance = $DB->get_record('workshop', array('id' => $cm->instance));
    
    if($DB->record_exists('workshop_submissions', array('authorid' => $userid, 'workshopid' => $workshop_instance->id))) {
        //Get first submission
        $submissions = $DB->get_records('workshop_submissions', array('authorid' => $userid, 'lessonid' => $workshop_instance->id), "timecreated ASC");
        $first_submission_time = reset($submissions)->timecreated;
        
        //Get first view
        $params = array();
        $selector = "l.cmid = ".$cm->id." AND l.userid = ".$userid." AND l.action='view'";
        $totalcount = 0;
        $logs = get_logs($selector, null, 'l.time ASC', '', '', $totalcount);        
        if(count($logs)) {
            return $first_submission_time - reset($logs)->time;
        }
    }
    return 0;       
}


function block_risk_monitor_time_before_deadline_assignment($userid, $cm, $value) {
    //Get the assignment.
    global $DB;
    $assignment_instance = $DB->get_record('assignment', array('id' => $cm->instance));
    
    //check due date
    $time_before_due = $value*24*60*60;
    $time_due = $assignment_instance->timedue;
    if($DB->record_exists('assignment_submissions', array('userid' => $userid, 'assignment' => $assignment_instance->id))) {
        $submissions = $DB->get_records('assignment_submissions', array('userid' => $userid, 'assignment' => $assignment_instance->id), "timecreated ASC");
        if(reset($submissions)->timecreated > $time_due - $time_before_due) {
            return 100;
        }
    }
    return 0;
}

function block_risk_monitor_time_before_deadline_quiz($userid, $cm, $value) {
    global $DB;
    $quiz_instance = $DB->get_record('quiz', array('id' => $cm->instance));
    
    $time_before_due = $value*24*60*60;
    $time_due = $quiz_instance->timeclose;
    if($DB->record_exists('quiz_attempts', array('userid' => $userid, 'quiz' => $quiz_instance->id))) {
        $submissions = $DB->get_records('quiz_attempts', array('userid' => $userid, 'quiz' => $quiz_instance->id), "timefinish ASC");
        if(reset($submissions)->timefinish > $time_due - $time_before_due) {
            return 100;
        }    
       
    }
    return 0;  
}

function block_risk_monitor_time_before_deadline_assign($userid, $cm, $value) {
    //Get the assign
    global $DB;
    $assign_instance = $DB->get_record('assign', array('id' => $cm->instance));
    
    $time_before_due = $value*24*60*60;
    $time_due = $assign_instance->duedate;
    if($DB->record_exists('assign_submission', array('userid' => $userid, 'assignment' => $assign_instance->id))) {
        $submissions = $DB->get_records('assign_submission', array('userid' => $userid, 'assignment' => $assign_instance->id), "timecreated ASC");
        if(reset($submissions)->timecreated > $time_due - $time_before_due) {
            return 100;
        }    
     
    }
    return 0;    
}

function block_risk_monitor_time_before_deadline_lesson($userid, $cm, $value) {
    global $DB;
    $lesson_instance = $DB->get_record('lesson', array('id' => $cm->instance));
    
    $time_before_due = $value*24*60*60;
    $time_due = $lesson_instance->deadline;
    if($DB->record_exists('lesson_attempts', array('userid' => $userid, 'lessonid' => $lesson_instance->id))) {
        $submissions = $DB->get_records('lesson_attempts', array('userid' => $userid, 'lessonid' => $lesson_instance->id), "timeseen ASC");
        if(reset($submissions)->timeseen > $time_due - $time_before_due) {
            return 100;
        }    
        
    }
    return 0;        
}

function block_risk_monitor_time_before_deadline_workshop($userid, $cm, $value) {
    global $DB;
    $workshop_instance = $DB->get_record('workshop', array('id' => $cm->instance));
    
    $time_before_due = $value*24*60*60;
    $time_due = $workshop_instance->submissionend;
    if($DB->record_exists('workshop_submissions', array('authorid' => $userid, 'workshopid' => $workshop_instance->id))) {
        $submissions = $DB->get_records('workshop_submissions', array('authorid' => $userid, 'workshopid' => $workshop_instance->id), "timecreated ASC");
        if(reset($submissions)->timecreated > $time_due - $time_before_due) {
            return 100;
        }    
      
    }
    return 0;      
}

function block_risk_monitor_multiple_submissions_assignment($userid, $cm, $value) {
    //Get the assignment.
    global $DB;
    $assignment_instance = $DB->get_record('assignment', array('id' => $cm->instance));
    
    if($DB->record_exists('assignment_submissions', array('userid' => $userid, 'assignment' => $assignment_instance->id))) {
        $submissions = $DB->get_records('assignment_submissions', array('userid' => $userid, 'assignment' => $assignment_instance->id));
        if(count($submissions) >= $value) {
            return 100;
        }
    }
    return 0;
}

function block_risk_monitor_multiple_submissions_quiz($userid, $cm, $value) {
    global $DB;
    $quiz_instance = $DB->get_record('quiz', array('id' => $cm->instance));

    if($DB->record_exists('quiz_attempts', array('userid' => $userid, 'quiz' => $quiz_instance->id))) {
        $submissions = $DB->get_records('quiz_attempts', array('userid' => $userid, 'quiz' => $quiz_instance->id));
        if(count($submissions) >= $value) {
            return 100;
        }    
       
    }
    return 0;  
}

function block_risk_monitor_multiple_submissions_assign($userid, $cm, $value) {
    //Get the assign
    global $DB;
    $assign_instance = $DB->get_record('assign', array('id' => $cm->instance));
    
    if($DB->record_exists('assign_submission', array('userid' => $userid, 'assignment' => $assign_instance->id))) {
        $submission = $DB->get_record('assign_submission', array('userid' => $userid, 'assignment' => $assign_instance->id));
        if($submission->attemptnumber >= $value-1) {
            return 100;
        }    
     
    }
    return 0;    
}

function block_risk_monitor_multiple_submissions_lesson($userid, $cm, $value) {
    global $DB;
    $lesson_instance = $DB->get_record('lesson', array('id' => $cm->instance));
    if($DB->record_exists('lesson_attempts', array('userid' => $userid, 'lessonid' => $lesson_instance->id))) {
        $submissions = $DB->get_records('lesson_attempts', array('userid' => $userid, 'lessonid' => $lesson_instance->id));
        if(count($submissions) >= $value) {
            return 100;
        }    
        
    }
    return 0;        
}

function block_risk_monitor_multiple_submissions_workshop($userid, $cm, $value) {
    global $DB;
    $workshop_instance = $DB->get_record('workshop', array('id' => $cm->instance));
    
    if($DB->record_exists('workshop_submissions', array('authorid' => $userid, 'workshopid' => $workshop_instance->id))) {
        $submissions = $DB->get_records('workshop_submissions', array('authorid' => $userid, 'workshopid' => $workshop_instance->id));
        if(count($submissions) >= $value) {
            return 100;
        }    
      
    }
    return 0;      
}

function block_risk_monitor_get_deadline_assignment($userid, $cm) {
    //Get the assignment.
    global $DB;
    $assignment_instance = $DB->get_record('assignment', array('id' => $cm->instance));
    
    //check due date
    return $assignment_instance->timedue;
}

function block_risk_monitor_get_deadline_quiz($userid, $cm) {
    global $DB;
    $quiz_instance = $DB->get_record('quiz', array('id' => $cm->instance));
    
    //check due date
    return $quiz_instance->timeclose;
}

function block_risk_monitor_get_deadline_assign($userid, $cm) {
    //Get the assign
    global $DB;
    $assign_instance = $DB->get_record('assign', array('id' => $cm->instance));
    
    //check due date
    return $assign_instance->duedate;
}

function block_risk_monitor_get_deadline_lesson($userid, $cm) {
    global $DB;
    $lesson_instance = $DB->get_record('lesson', array('id' => $cm->instance));
    
    //check due date
    return $lesson_instance->deadline;
}

function block_risk_monitor_get_deadline_workshop($userid, $cm) {
    global $DB;
    $workshop_instance = $DB->get_record('workshop', array('id' => $cm->instance));
    
    //check due date
    return $workshop_instance->submissionend;
}