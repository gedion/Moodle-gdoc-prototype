<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once($CFG->dirroot . '/repository/googledocs/lib.php');

$fileId = "0AFI2nRsxwgckUk9PVA";

getUserPermissions($fileId);

function getUserPermissions($fileId) {
    global $DB, $USER, $PAGE;

    $googledocs_repo = $DB->get_record('repository', array ('type'=>'googledocs'));
    $context = context_user::instance($USER->id);
    $repo_options = array();
    $gdocrepo = new repository_googledocs($googledocs_repo->id, $context, $repo_options);

    $course = $PAGE->course;
    $ccontext = context_course::instance($course->id);

    //Get User info.
    $userinfo = $gdocrepo->get_user_info();
    $gmail = $userinfo->email;
    $permissions = $gdocrepo->retrieveFilePermissions($fileId);
    $permissionId = $gdocrepo->printPermissionIdForEmail($gmail);

    //Get User permissions from Google for the given resource id.
    $gdocrepo->printUserPermission($fileId, $permissionId);
//    if(!$userperm) {
//        print("User doesn't have permission to view this file.");
//    }

    //TODO:Set the active_flag for gmail if the exisitng gmailid is not same as the new one when checked.
    //TODO:Insert permissionId in to google_refreshtokens table when user has an active gmailId associated to moodle.

    //Get User permissions from moodle.
    $table = 'google_refreshtokens';
    $select = 'gmail IS NOT NULL AND gmail_active = 1';
    $userids = $DB->get_fieldset_select($table, 'userid',$select,array());
    $gmails = $DB->get_fieldset_select($table, 'gmail',$select,array());
    
    print_object($gmails);
    if($userids) {
        foreach($userids as $userid) {
            print_object($userid);
            //TODO: Display the user list with appropriate user permissions from moodle.
            //TODO: Google drive repository permission set creation - repository/googledocs:manageactivities, repository/googledocs:view
            if(has_capability('moodle/course:manageactivities', $ccontext, $userid)) {
                print("User with userid ".$userid." is capable of editing google resource shared.<br/>");
            } else if(has_capability('moodle/course:view', $ccontext, $userid)) {
                print("User with userid ".$userid." is read only user.<br/>");
            } else {
                print("Permissions not available for the user : ". $userid);
            }
        }
    }
}

function getGoogleUsersForMoodle() {
    global $DB, $USER;
    
    echo "get Google Users list";
    $table = 'google_refreshtokens';
    $select = "user = $USER->id";
    //get_user_info();
    $DB->get_records_select($table, $select);

    //mdl_google_refreshtokens
}