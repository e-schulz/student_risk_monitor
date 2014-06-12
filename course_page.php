<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

require_once("../../config.php");
//Teacher must be logged in
require_login();

//Get the ID of the course
$courseid = required_param('courseid', PARAM_INT);

//DB STUFF - Need all anxiety instances with this course, the exam upcoming...