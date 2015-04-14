<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

//Check the deadlines depending on the module
function block_risk_monitor_check_due_date($modname, $mod_inst) {
    switch($modname) {
        case 'assignment':
            return $mod_inst->timedue;
        case 'quiz':
            return $mod_inst->timeclose;
        case 'assign':
            return $mod_inst->duedate;
        case 'lesson':
            return $mod_inst->deadline;
        case 'workshop':
            return $mod_inst->submissionend;
        default:
            return 0;
    }
}

function block_risk_monitor_missed_assignment_deadline($userid, $mod_inst) {
    //Get the assignment.
    global $DB;
    
    //check due date
    $time_due = $mod_inst->timedue;
    if(!$DB->record_exists('assignment_submissions', array('userid' => $userid, 'assignment' => $mod_inst->id)) && $time_due != 0 && time() > $time_due) {
        return true;
    }
    return false;
}

function block_risk_monitor_missed_quiz_deadline($userid, $mod_inst) {
    global $DB;
    
    //check due date
    $time_due = $mod_inst->timeclose;
    if(!$DB->record_exists('quiz_attempts', array('userid' => $userid, 'quiz' => $mod_inst->id)) && $time_due != 0 && time() > $time_due) {
        return true;
    }
    return false;  
}

function block_risk_monitor_missed_assign_deadline($userid, $mod_inst) {
    //Get the assign
    global $DB;
    
    //check due date
    $time_due = $mod_inst->duedate;
    if(!$DB->record_exists('assign_submission', array('userid' => $userid, 'assignment' => $mod_inst->id)) && $time_due != 0 && time() > $time_due) {
        return true;
    }
    return false;    
}

function block_risk_monitor_missed_lesson_deadline($userid, $mod_inst) {
    global $DB;
    
    //check due date
    $time_due = $mod_inst->deadline;
    if(!$DB->record_exists('lesson_attempts', array('userid' => $userid, 'lessonid' => $mod_inst->id)) && $time_due != 0 && time() > $time_due) {
        return true;
    }
    return false;        
}

/*function block_risk_monitor_missed_scorm_deadline($userid, $mod_inst) {
    
    global $DB;
    $scorm_instance = $DB->get_record('scorm', array('id' => $mod_inst->instance));
    
    //check due date
    $time_due = $scorm_instance->timeclose;
    if(!$DB->record_exists('scorm_scoes_track', array('userid' => $userid, 'scormid' => $scorm_instance->id)) && $time_due != 0 && time() > $time_due) {
        return true;
    }
    return false;           
}*/

function block_risk_monitor_missed_workshop_deadline($userid, $mod_inst) {
    global $DB;
    
    //check due date
    $time_due = $mod_inst->submissionend;
    if(!$DB->record_exists('workshop_submissions', array('authorid' => $userid, 'workshopid' => $mod_inst->id)) && $time_due != 0 && time() > $time_due) {
        return true;
    }
    return false;      
}

function block_risk_monitor_time_to_finish_assignment($userid, $mod_inst, $cm) {
    global $DB;
    
    if($DB->record_exists('assignment_submissions', array('userid' => $userid, 'assignment' => $mod_inst->id))) {
        //Get first submission
        $submissions = $DB->get_records('assignment_submissions', array('userid' => $userid, 'assignment' => $mod_inst->id), "timecreated ASC");
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

function block_risk_monitor_time_to_finish_quiz($userid, $mod_inst, $cm) {
    global $DB;
    
    if($DB->record_exists('quiz_attempts', array('userid' => $userid, 'quiz' => $mod_inst->id))) {
        //Get first submission
        $submissions = $DB->get_records('quiz_attempts', array('userid' => $userid, 'quiz' => $mod_inst->id), "timestart ASC");
        $first_submission = reset($submissions);
        return $first_submission->timefinish - $first_submission->timestart;
    }
    return 0;      
}

function block_risk_monitor_time_to_finish_assign($userid, $mod_inst, $cm) {
    global $DB;
    
    if($DB->record_exists('assign_submission', array('userid' => $userid, 'assignment' => $mod_inst->id))) {
        //Get first submission
        $submissions = $DB->get_records('assign_submission', array('userid' => $userid, 'assignment' => $mod_inst->id), "timecreated ASC");
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

function block_risk_monitor_time_to_finish_lesson($userid, $mod_inst, $cm) {
    global $DB;
    
    if($DB->record_exists('lesson_attempts', array('userid' => $userid, 'lessonid' => $mod_inst->id))) {
        //Get first submission
        $submissions = $DB->get_records('lesson_attempts', array('userid' => $userid, 'lessonid' => $mod_inst->id), "timeseen ASC");
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

function block_risk_monitor_time_to_finish_workshop($userid, $mod_inst, $cm) {
    global $DB;
    
    if($DB->record_exists('workshop_submissions', array('authorid' => $userid, 'workshopid' => $mod_inst->id))) {
        //Get first submission
        $submissions = $DB->get_records('workshop_submissions', array('authorid' => $userid, 'lessonid' => $mod_inst->id), "timecreated ASC");
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


function block_risk_monitor_time_before_deadline_assignment($userid, $mod_inst, $value) {
    //Get the assignment.
    global $DB;
    
    //check due date
    $time_before_due = $value*24*60*60;
    $time_due = $assignment_instance->timedue;
    if($DB->record_exists('assignment_submissions', array('userid' => $userid, 'assignment' => $mod_inst->id))) {
        $submissions = $DB->get_records('assignment_submissions', array('userid' => $userid, 'assignment' => $mod_inst->id), "timecreated ASC");
        if(reset($submissions)->timecreated > $time_due - $time_before_due) {
            return 100;
        }
    }
    return 0;
}

function block_risk_monitor_time_before_deadline_quiz($userid, $mod_inst, $value) {
    global $DB;
    
    $time_before_due = $value*24*60*60;
    $time_due = $quiz_instance->timeclose;
    if($DB->record_exists('quiz_attempts', array('userid' => $userid, 'quiz' => $mod_inst->id))) {
        $submissions = $DB->get_records('quiz_attempts', array('userid' => $userid, 'quiz' => $mod_inst->id), "timefinish ASC");
        if(reset($submissions)->timefinish > $time_due - $time_before_due) {
            return 100;
        }    
       
    }
    return 0;  
}

function block_risk_monitor_time_before_deadline_assign($userid, $mod_inst, $value) {
    //Get the assign
    global $DB;
    
    $time_before_due = $value*24*60*60;
    $time_due = $assign_instance->duedate;
    if($DB->record_exists('assign_submission', array('userid' => $userid, 'assignment' => $mod_inst->id))) {
        $submissions = $DB->get_records('assign_submission', array('userid' => $userid, 'assignment' => $mod_inst->id), "timecreated ASC");
        if(reset($submissions)->timecreated > $time_due - $time_before_due) {
            return 100;
        }    
     
    }
    return 0;    
}

function block_risk_monitor_time_before_deadline_lesson($userid, $mod_inst, $value) {
    global $DB;
    
    $time_before_due = $value*24*60*60;
    $time_due = $lesson_instance->deadline;
    if($DB->record_exists('lesson_attempts', array('userid' => $userid, 'lessonid' => $mod_inst->id))) {
        $submissions = $DB->get_records('lesson_attempts', array('userid' => $userid, 'lessonid' => $mod_inst->id), "timeseen ASC");
        if(reset($submissions)->timeseen > $time_due - $time_before_due) {
            return 100;
        }    
        
    }
    return 0;        
}

function block_risk_monitor_time_before_deadline_workshop($userid, $mod_inst, $value) {
    global $DB;
    
    $time_before_due = $value*24*60*60;
    $time_due = $workshop_instance->submissionend;
    if($DB->record_exists('workshop_submissions', array('authorid' => $userid, 'workshopid' => $mod_inst->id))) {
        $submissions = $DB->get_records('workshop_submissions', array('authorid' => $userid, 'workshopid' => $mod_inst->id), "timecreated ASC");
        if(reset($submissions)->timecreated > $time_due - $time_before_due) {
            return 100;
        }    
      
    }
    return 0;      
}

function block_risk_monitor_multiple_submissions_assignment($userid, $mod_inst, $value) {
    //Get the assignment.
    global $DB;
    
    if($DB->record_exists('assignment_submissions', array('userid' => $userid, 'assignment' => $mod_inst->id))) {
        $submissions = $DB->get_records('assignment_submissions', array('userid' => $userid, 'assignment' => $mod_inst->id));
        if(count($submissions) >= $value) {
            return 100;
        }
    }
    return 0;
}

function block_risk_monitor_multiple_submissions_quiz($userid, $mod_inst, $value) {
    global $DB;

    if($DB->record_exists('quiz_attempts', array('userid' => $userid, 'quiz' => $mod_inst->id))) {
        $submissions = $DB->get_records('quiz_attempts', array('userid' => $userid, 'quiz' => $mod_inst->id));
        if(count($submissions) >= $value) {
            return 100;
        }    
       
    }
    return 0;  
}

function block_risk_monitor_multiple_submissions_assign($userid, $mod_inst, $value) {
    //Get the assign
    global $DB;
    
    if($DB->record_exists('assign_submission', array('userid' => $userid, 'assignment' => $mod_inst->id))) {
        $submissions = $DB->get_records('assign_submission', array('userid' => $userid, 'assignment' => $mod_inst->id));
        if(count($submissions) >= $value) {
            return 100;
        }    
     
    }
    return 0;    
}

function block_risk_monitor_multiple_submissions_lesson($userid, $mod_inst, $value) {
    global $DB;
    if($DB->record_exists('lesson_attempts', array('userid' => $userid, 'lessonid' => $mod_inst->id))) {
        $submissions = $DB->get_records('lesson_attempts', array('userid' => $userid, 'lessonid' => $mod_inst->id));
        if(count($submissions) >= $value) {
            return 100;
        }    
        
    }
    return 0;        
}

function block_risk_monitor_multiple_submissions_workshop($userid, $mod_inst, $value) {
    global $DB;
    
    if($DB->record_exists('workshop_submissions', array('authorid' => $userid, 'workshopid' => $mod_inst->id))) {
        $submissions = $DB->get_records('workshop_submissions', array('authorid' => $userid, 'workshopid' => $mod_inst->id));
        if(count($submissions) >= $value) {
            return 100;
        }    
      
    }
    return 0;      
}

function block_risk_monitor_get_deadline_assignment($userid, $mod_inst) {
    //Get the assignment.
    global $DB;
    
    //check due date
    return $mod_inst->timedue;
}

function block_risk_monitor_get_deadline_quiz($userid, $mod_inst) {
    global $DB;
    
    //check due date
    return $mod_inst->timeclose;
}

function block_risk_monitor_get_deadline_assign($userid, $mod_inst) {
    //Get the assign
    global $DB;
    
    //check due date
    return $mod_inst->duedate;
}

function block_risk_monitor_get_deadline_lesson($userid, $mod_inst) {
    global $DB;    
    //check due date
    return $mod_inst->deadline;
}

function block_risk_monitor_get_deadline_workshop($userid, $mod_inst) {
    global $DB;
    
    //check due date
    return $mod_inst->submissionend;
}