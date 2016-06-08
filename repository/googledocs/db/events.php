<?php

$observers = array (

    array (
        'eventname'   => '\core\event\course_updated',
        'includefile' => '/repository/googledocs/locallib.php',
        'callback'    => 'googledocs_course_updated',
        'internal'    => true
    ),
    array (
        'eventname'   => '\core\event\course_module_created',
        'includefile' => '/repository/googledocs/locallib.php',
        'callback'    => 'googledocs_course_module_created',
        'internal'    => true
    ),
    array (
        'eventname'   => '\core\event\course_module_updated',
        'includefile' => '/repository/googledocs/locallib.php',
        'callback'    => 'googledocs_course_module_updated',
        'internal'    => true
    ),
    array (
        'eventname'   => '\core\event\course_module_delted',
        'includefile' => '/repository/googledocs/locallib.php',
        'callback'    => 'googledocs_course_module_deleted',
        'internal'    => true
    ),
    array (
        'eventname'   => '\core\event\role_assigned',
        'includefile' => '/repository/googledocs/locallib.php',
        'callback'    => 'googledocs_role_assigned',
        'internal'    => true
    ),
    array (
        'eventname'   => '\core\event\role_unassigned',
        'includefile' => '/repository/googledocs/locallib.php',
        'callback'    => 'googledocs_role_unassigned',
        'internal'    => true
    )

);

