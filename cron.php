<?php
/**
 * @author    Tosin Komolafe, Sergey Butakov, Svetlana Kim
 * @copyright CrotSoftware 2012
 */
    if (!defined('MOODLE_INTERNAL')) {
        die('Direct access to this script is forbidden.');    ///  It must be included from a Moodle page
    }
    // overrides php limits
    $maxtimelimit = ini_get('max_execution_time');
    ini_set('max_execution_time', 600);
    $maxmemoryamount = ini_get('memory_limit');
    // set large amount of memory for the processing
    // fingeprint calcualtion mey be very memory consuming especially for large documents from the internet
    ini_set('memory_limit', '256M');

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
     $sql_query = "SELECT cf.* FROM {plagiarism_crotpro_files} cf where cf.status = 'queue'";
     $files = $DB->get_records_sql($sql_query);
    // put files that were submitted for marking into queue for check up
    foreach ($files as $afile) {
        //Beginning of PDS
        $plagiarismsettings = (array)get_config('plagiarism');
        $name = $plagiarismsettings['crotpro_account_id']; // get account id
        $link = $plagiarismsettings['crotpro_service_url']; // pds service link
        $fs = get_file_storage();
        $file = $fs->get_file_by_id($afile->file_id);
        $filename = $file->get_filename(); // get file name
        $arrfilename = explode(".",$filename);
        $ext = $arrfilename[count($arrfilename)-1];// get file extension
        $contenthash = $file->get_contenthash();
        $l1 = $contenthash[0] .$contenthash[1];
        $l2 = $contenthash[2] .$contenthash[3];
        $apath= $CFG->dataroot."/filedir/$l1/$l2/$contenthash";  // get file path
        $atext = base64_encode(file_get_contents($apath));
        $params = array(
             "file"=>$atext,
             "ext"=>$ext,
             "uid"=>urlencode($name)
            ); 

        $url =$link.'/queue.php';
        $port = 80;
        $response = xml_post($params, $url, $port); // sends file to the web service
        $xml = new DOMDocument();
        $xml->loadXML($response); // get back response
        $m = $xml->getElementsByTagName("message");
        $message = '';
        if($m->length <> 0){
            foreach($m as $value){
                $message = $value->nodeValue;
            }
        }

        if($message == 'OK'){
            $t = $xml->getElementsByTagName("ticket");
            $ticket = '';
            if($t->length <> 0){
                foreach($t as $value){
                    $ticket = $value->nodeValue;
                }
            }

            $newelement = new stdclass();
            $newelement->file_id = $file->get_id();
            $newelement->path = $file->get_contenthash();
            $newelement->ticket_code = $ticket; 
            $newelement->cm = $afile->cm;    
            $newelement->courseid = $afile->courseid;
            //echo $newelement->file_id . ' '. $newelement->path . ' '.$newelement->ticket_code . ' ' .$newelement->cm . ' '.$newelement->courseid;
            $result=$DB->insert_record('plagiarism_crotpro_job', $newelement);
            $afile->status = 'sent';
            $result=$DB->update_record('plagiarism_crotpro_files', $afile);
            echo "\nfile ".$file->get_filename()." was sent to crot PDS!\n";
        }else{
            echo $message;
        }
    }
    // todo: change this query to api call
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
