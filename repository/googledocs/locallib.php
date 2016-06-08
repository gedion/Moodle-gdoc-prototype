<?php

defined('MOODLE_INTERNAL') || die();
require_once($CFG->dirroot . '/repository/googledocs/lib.php');


function sharedoc($event) {
    global $DB;
    $courseid = $event->courseid;
    $course = $DB->get_record('course', array('id' => $courseid));
    $resources  = get_resources($courseid);
    $repo = get_google_docs_repo();
    error_log('evt ' .$event->eventname);
    switch($event->eventname) {
        case '\core\event\course_updated':
            $usersemails = get_google_authenticated_users($courseid);
            error_log('usersemails ' .json_encode($usersemails));
            foreach ($resources as $fileid) {
                foreach($usersemails as $email) {
                    if($course->visible == 1) {
                            $repo->insert_permission($fileid, $email, 'user', 'reader');
                    } else {
                            $permissionid = $repo->print_permission_id_for_email($email);
                            $repo->remove_permission($fileid, $permissionid);
                    }
                }
            }
            break;
        case '\core\event\course_module_updated':
            $fileid = get_resource_id($courseid, $event->contextinstanceid); //one per module
            $usersemails = get_google_authenticated_users($courseid);
            foreach($usersemails as $email) {
                if($course->visible == 1) {
                    $repo->insert_permission($fileid, $email, 'user', 'reader');
                }
            }
            break;
        case '\core\event\role_assigned':
            $email = get_google_authenticated_users_email($event->relateduserid);
            foreach ($resources as $fileid) {
                $repo->insert_permission($fileid, $email, 'user', 'reader');
            }
            break;
        case '\core\event\role_unassigned':
            $email = get_google_authenticated_users_email($event->relateduserid);
            foreach ($resources as $fileid) {
                remove_permission($repo, $fileid, $email);
            }
            break;
        case '\core\event\course_module_deleted':
            $fileid = get_resource_id($courseid, $event->contextinstanceid); //one per module
            $usersemails = get_google_authenticated_users($courseid);
            foreach($usersemails as $email) {
                remove_permission($repo, $fileid, $email);
            }
            break;
    }
    return true;
}

function remove_permission($repo, $fileid, $email) {
    $permissionid = $repo->print_permission_id_for_email($email);
    $repo->remove_permission($fileid, $permissionid);
}

/**
 * handle course_updated event
 * @param stdClass $event
 */
function googledocs_course_updated($event) {
    return sharedoc($event);
}

/**
 * handle role_ssigned event
 * @param stdClass $event
 */
function googledocs_role_assigned($event) {
    return sharedoc($event);
}

/**
 * handle role_unassigned event
 * @param stdClass $event
 */
function googledocs_role_unassigned($event) {
    return sharedoc($event);
}

/**
 * handle module_created event
 * @param stdClass $event
 */
function googledocs_course_module_created($event) {
    return sharedoc($event);
}

/**
 * handle module_updated event
 * @param stdClass $event
 */
function googledocs_course_module_updated($event) {
    //TO DO
    //Groupings, availability, and visibility can change
    return false;
}

/**
 * handle module_deleted event
 * @param stdClass $event
 */
function googledocs_course_module_deleted($event) {
    return sharedoc($event);
}

function get_resources($courseid, $contextinstanceid=null) {
    global $DB;
    $googledocsrepo = $DB->get_record('repository', array ('type'=>'googledocs'));
    $id = $googledocsrepo->id;
    if (empty($id)) {
        // We did not find any instance of googledocs.
        mtrace('Could not find any instance of the repository');
        return;
    }

    $sql = "SELECT f.contextid, r.reference
              FROM {files_reference} r
              LEFT JOIN {files} f
                   ON f.referencefileid = r.id
             WHERE r.repositoryid = :repoid 
               AND f.referencefileid IS NOT NULL
               AND NOT (f.component = :component
                        AND f.filearea = :filearea)"; 
   $resources = array();
   $filerecords = $DB->get_recordset_sql($sql, array('component' => 'user', 'filearea' => 'draft', 'repoid' => $id));
   foreach ($filerecords as $filerecord) {
       $docid = $filerecord->reference;
       list($context, $course, $cm) = get_context_info_array($filerecord->contextid);
       if($course->id == $courseid && is_null($contextinstanceid) or
          $course->id == $courseid && $cm->id == $contextinstanceid) {
           $resources[] = $docid;
       }
   }
   return $resources;
}

function get_resource_id($courseid, $contextinstanceid) {
    $resources  = get_resources($courseid, $event->contextinstanceid);
    return current($resources);
}

function get_google_authenticated_users_email($userid) {
    global $DB;
    $googlerefreshtoken = $DB->get_record('google_refreshtokens', array ('userid'=> $userid));
    return $googlerefreshtoken->gmail;
}

function get_google_authenticated_users($courseid) {
    global $DB;
    $sql = "SELECT DISTINCT grt.gmail
              FROM {user} eu1_u
              JOIN {google_refreshtokens} grt
                    ON eu1_u.id = grt.userid
              JOIN {user_enrolments} eu1_ue
                   ON eu1_ue.userid = eu1_u.id
              JOIN {enrol} eu1_e
                   ON (eu1_e.id = eu1_ue.enrolid AND eu1_e.courseid = :courseid)
            WHERE eu1_u.deleted = 0 AND eu1_u.id <> :guestid ";
    $users = $DB->get_recordset_sql($sql, array('courseid' => $courseid, 'guestid' => '1'));
    $usersarray = array();
    foreach($users as $user) {
        $usersarray[] = $user->gmail;
    }
    return $usersarray;
}

function get_google_docs_repo() {
    global $DB;
    $googledocsrepo = $DB->get_record('repository', array ('type'=>'googledocs'));
    return new repository_googledocs($googledocsrepo->id);
}
