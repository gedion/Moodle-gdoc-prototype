<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once($CFG->dirroot . '/repository/googledocs/lib.php');

$fileId = "18jb3m2gmm_HEuwwFkUfe4kbszcKZ-8pVGIqYZpncIaA";
$role = "reader"; //"owner", "writer" or "reader".
$permaction = $_GET['permaction'];

get_user_permissions($fileId, $permaction);

function get_user_permissions($fileId, $permaction) {
    global $DB, $USER, $PAGE;

    $googledocs_repo = $DB->get_record('repository', array ('type'=>'googledocs'));
    $context = context_user::instance($USER->id);
    $repo_options = array();
    $gdocrepo = new repository_googledocs($googledocs_repo->id, $context, $repo_options);

    $course = $PAGE->course;
    $ccontext = context_course::instance($course->id);

    //Get User info.
    $userinfo = $gdocrepo->get_user_info();
    if($userinfo) {
        $gmail = $userinfo->email;
    }

    //Pull User ids and thier associative gmail account for moodle information.
    $table = 'google_refreshtokens';
    $select = 'gmail IS NOT NULL';
    $params = null;
    $fields = 'userid, gmail';
    $sort = 'userid';
    $gmailids = $DB->get_records_select_menu($table,$select,$params,$sort,$fields);
    
    //Type could be typically either "user", "group", "domain" or "default".
    $type = "user";
    
    //Get User permissions from moodle.
    if($gmailids) {
        print("MOODLE PERMISSIONS: <br/>");
        foreach($gmailids as $userid=>$gmail) {
            print("<br/>UserId ".$userid);
            //Fetch users with writer capability.
            if(has_capability('moodle/course:manageactivities', $ccontext, $userid)) {
                print("(".$gmail."): WRITER.");
                $role = "writer";
            //Fetch users with reader capability.
            }elseif (has_capability('moodle/block:view', $ccontext, $userid)) {
                print("(".$gmail."): READER.");
            }
        }
        print("<br/><br/>");
    }

    //Get user permissions from google for the given resource.
    $permissions = $gdocrepo->retrieve_file_permissions($fileId);

    //Set User permissions to given google resource based on the permaction.
    switch($permaction) {
        case 'insert':
            insert_permission($fileId, $gdocrepo, $gmail, $type, $role, $permissions);
            break;
        case 'update':
            update_permission($fileId, $gdocrepo, $gmail, $type, $role, $permissions);
            break;
        case 'delete':
            remove_permission($fileId, $gdocrepo, $gmail);
            break;
        default:
            print "No action has been selected. Please choose one.";
    }
}

function insert_permission($fileId, $gdocrepo=null, $gmail, $type, $role, $permissions) {
    if($gdocrepo) {
        foreach($permissions as $userperm) {
            if($userperm->getEmailAddress() == $gmail) {
                print("<br/> The file has been shared with the user(".$gmail.") already as a ".  strtoupper($userperm->getRole())."<br/>");
                return;
            }
        }

        //Insert permissions for the given file.
        $insertedperm = $gdocrepo->insert_permission($fileId, $gmail, $type, $role);
        if($insertedperm) {
            print("<br/>Successfully inserted the permissions for the user.<br/>");
        }
    }
}

function update_permission($fileId, $gdocrepo=null, $gmail, $type, $role, $permissions) {
    if($gdocrepo) {
        foreach($permissions as $userperm) {
            if($userperm->getEmailAddress() == $gmail) {
                $newrole = ($role=='writer')?'reader':'writer';
                $permissionid = $gdocrepo->print_permission_id_for_email($gmail);

                $updateperm = $gdocrepo->update_permission($fileId, $permissionid, $newrole);
                if($updateperm) {
                    print("<br/>Successfully updated the permissions for the user.<br/>");  
                }
                return;
            }
        }
        print("<br/>The current user you specified doesn't have any permission for the file. So inserting now..<br/>");
        insert_permission($fileId, $gdocrepo, $gmail, $type, $role, $permissions);
    }
}

function remove_permission($fileId, $gdocrepo, $gmail) {
    $permissionid = $gdocrepo->print_permission_id_for_email($gmail);
    $gdocrepo->remove_permission($fileId, $permissionid);
}