<?php

$handlers = array (

/*
 * Event Handlers
 */
    'assessable_file_uploaded' => array (
        'handlerfile'      => '/plagiarism/crotpro/lib.php',
        'handlerfunction'  => 'crot_event_file_uploaded_crotpro',
        'schedule'         => 'cron'
    ),
    'assessable_files_done' => array (
        'handlerfile'      => '/plagiarism/crotpro/lib.php',
        'handlerfunction'  => 'crot_event_files_done_crotpro',
        'schedule'         => 'cron'
    ),
    'mod_created' => array (
        'handlerfile'      => '/plagiarism/crotpro/lib.php',
        'handlerfunction'  => 'crot_event_mod_created_crotpro',
        'schedule'         => 'cron'
    ),
    'mod_updated' => array (
        'handlerfile'      => '/plagiarism/crotpro/lib.php',
        'handlerfunction'  => 'crot_event_mod_updated_crotpro',
        'schedule'         => 'cron'
    ),
    'mod_deleted' => array (
        'handlerfile'      => '/plagiarism/crotpro/lib.php',
        'handlerfunction'  => 'crot_event_mod_deleted_crotpro',
        'schedule'         => 'cron'
    ),

);
