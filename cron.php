<?php
/**
 * @author Tosin Komolafe
 */
    if (!defined('MOODLE_INTERNAL')) {
        die('Direct access to this script is forbidden.');    ///  It must be included from a Moodle page
    }
    // overrides php limits
    $maxtimelimit = ini_get('max_execution_time');
    ini_set('max_execution_time', 18000);
    $maxmemoryamount = ini_get('memory_limit');
    // set large amount of memory for the processing
    // fingeprint calcualtion mey be very memory consuming especially for large documents from the internet
    ini_set('memory_limit', '1024M');

    // store current time for perf measurements
    $mtime = microtime();
    $mtime = explode(" ",$mtime);
    $mtime = $mtime[1] + $mtime[0];
    $starttime = $mtime;

    global $CFG, $DB;

    require_once($CFG->dirroot.'/plagiarism/crotpro/lib.php');
    require_once($CFG->dirroot."/course/lib.php");
    require_once($CFG->dirroot."/mod/assignment/lib.php");
    require_once($CFG->dirroot.'/config.php');
    require_once($CFG->dirroot. '/plagiarism/crotpro/post_xml.php');

    $plagiarismsettings = (array)get_config('plagiarism');
    $account_id = $plagiarismsettings['crotpro_account_id'];
    $web_link = $plagiarismsettings['crotpro_service_url'];

    if (empty($account_id)||empty($web_link)) {
            die('The plugin is not properly set. Please set the plugin in admin/plugins/plagiarism prevention menu');    /// the initial settigns were not properly set up
    }

    // main loop on crot_files table - check if there are files marked for the check up 
    $link = mysql_connect("$CFG->dbhost", "$CFG->dbuser", "$CFG->dbpass") or die("Could not connect");
    mysql_select_db("$CFG->dbname") or die ("Could not select database");
    $sql_query = "SELECT * FROM {$CFG->prefix}plagiarism_crotpro_job where result is NULL";// use api...
    $files = $DB->get_records_sql($sql_query);
    if (!empty($files)){
        foreach ($files as $afile){
            try{
                $ticket = $afile->ticket_code;
                $params = array(
                     "ticket"=>$ticket,
                     "uid"=>urlencode($account_id)
                    ); 
                //$post = 'uid='.urlencode($account_id).'&ticket='.urlencode($ticket).'';
                $url = $web_link .'/checkout.php';
                $port = 80;
                $response = xml_post($params, $url, $port);
                $xml = new DOMDocument();
                $xml->loadXML($response);
                $m = $xml->getElementsByTagName("message");
                $message = '';
                if($m->length <> 0){
                    foreach($m as $value){
                        $message = $value->nodeValue;
                    }
                }
                if($message == 'queue'){
                    echo "\nfile $afile->file_id document is not processed yet\n";
                }else if($message =='in_processing'){
                    echo "\nfile $afile->file_id document is being processed\n";
                }else if($message == 'end_processing'){
                    $afile->result = $xml->saveXML();
                    $DB->update_record('plagiarism_crotpro_job', $afile);
                    echo "\nfile $afile->file_id was sucessfully processed by pds\n";
                }else{
                    echo "\n $message";
                }

            }catch (Exception $e){
                print_error("Error in processing file $afile->file_id!\n");
                continue;
            }
        }
    }else{
        echo "\nnothing to process by pds!\n";
    }
    //end of PDS

    // set back normal values for php limits
    ini_set('max_execution_time', $maxtimelimit);
    ini_set('memory_limit', $maxmemoryamount);

    // calc and display exec time
    $mtime = microtime();
    $mtime = explode(" ",$mtime);
    $mtime = $mtime[1] + $mtime[0];
    $endtime = $mtime;
    $totaltime = ($endtime - $starttime);
    echo "\nThe assignments were processed by crotpro in ".$totaltime." seconds\n";
?>
