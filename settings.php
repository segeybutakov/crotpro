<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * plagiarism.php - allows the admin to configure plagiarism stuff
 *
 * @package   plagiarism_crotpro
 * @author    Tosin Komolafe
 * @copyright 1999 onwards Martin Dougiamas  {@link http://moodle.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

    require_once(dirname(dirname(__FILE__)) . '/../config.php');
    require_once($CFG->libdir.'/adminlib.php');
    require_once($CFG->libdir.'/plagiarismlib.php');
    require_once($CFG->dirroot.'/plagiarism/crotpro/lib.php');
    require_once($CFG->dirroot.'/plagiarism/crotpro/plagiarism_form.php');
    
    require_login();
    admin_externalpage_setup('plagiarismcrotpro');

    $context = get_context_instance(CONTEXT_SYSTEM);

    require_capability('moodle/site:config', $context, $USER->id, true, "nopermissions");

    require_once('plagiarism_form.php');
    $mform = new plagiarism_setup_form();
    
        
    $plagiarismplugin = new plagiarism_plugin_crotpro();

    if ($mform->is_cancelled()) {
        redirect('');
    }

    echo $OUTPUT->header();

    if (($data = $mform->get_data()) && confirm_sesskey()) {
        if (!isset($data->crotpro_use)) {
            $data->crotpro_use = 0;
        }
        foreach ($data as $field=>$value) {
            if (strpos($field, 'crotpro')===0) {
                if ($tiiconfigfield = $DB->get_record('config_plugins', array('name'=>$field, 'plugin'=>'plagiarism'))) {
                    $tiiconfigfield->value = $value;
                    if (! $DB->update_record('config_plugins', $tiiconfigfield)) {
                        error("errorupdating");
                    }
                } else {
                    $tiiconfigfield = new stdClass();
                    $tiiconfigfield->value = $value;
                    $tiiconfigfield->plugin = 'plagiarism';
                    $tiiconfigfield->name = $field;
                    if (! $DB->insert_record('config_plugins', $tiiconfigfield)) {
                        error("errorinserting");
                    }
                }
            }
            if($field == 'delall' && $value == true) {
                clean_data();
            }
            
            if($field == 'testglobal' && $value == true) {
                test_global_search();
            }
/*   
 * todo remove this registration as it will be done automatically         
            if($field == 'registration' && $value == true) {
                echo $OUTPUT->box("<b><a href=\"https://spreadsheets.google.com/viewform?formkey=dFRPVTRiSkNzSzI1cTVManUwNWVKZXc6MQ\" target=\"_new\">Please follow this link to register!</a></b>");
            }
*/            
        }
        notify(get_string('savedconfigsuccess', 'plagiarism_crotpro'), 'notifysuccess');
    }
    $plagiarismsettings = (array)get_config('plagiarism');
    $mform->set_data($plagiarismsettings);

function clean_data(){
    global $DB;
    // cleaning up all the tables for Crot Pro'
    $DB->delete_records("plagiarism_crotpro_config");
    $DB->delete_records("plagiarism_crotpro_job");
    $DB->delete_records("plagiarism_crotpro_files");
    notify(get_string('tables_cleaned_up','plagiarism_crotpro'), 'notifysuccess');
}

function test_global_search(){
    // method sends a few queries to test search
  	global $CFG;
        require_once($CFG->dirroot. '/plagiarism/crotpro/post_xml.php');
        $plagiarismsettings = (array)get_config('plagiarism');
        $account_id = $plagiarismsettings['crotpro_account_id']; // get account id
        $service_url = $plagiarismsettings['crotpro_service_url']; // pds service link
        $params = array(
            "uid"=>urlencode($account_id)
        );
	// testing global connectivity
	echo "Testing global connectivity & xml parsers...<br>";
        // access http://pds.noplagiarism.org/connect.xml
        $service_url = "http://pds.noplagiarism.org/"; //remove later
        $url =$service_url.'/connect.xml';
        $port = 80;
        $response = xml_post($params, $url, $port); // sends file to the web service
        $xml = new DOMDocument();
        if($xml->loadXML($response)){ // get back response
            echo "<i>$service_url is accessible from your server - <font color=\"green\"><b>OK</b></font></i><br><hr>";
            $m = $xml->getElementsByTagName("message");
            $message = '';
            if($m->length <> 0){
                foreach($m as $value){
                    $message = $value->nodeValue;
                }
            }
            echo "<i>- <font color=\"green\"><b>OK</b></font></i><hr>";
            echo "Message from server: <i>".$message."</i><br>";
        }else{
            echo "<i>noplagiarism.org is not accessible from your server - <font color=\"red\"><b>FAIL</b></font></i><br><hr>";
        }
        echo "<script type=\"text/javascript\">alert(\"Test is over\");</script>";
	flush();
}


echo $OUTPUT->box_start('generalbox boxaligncenter', 'intro');
$mform->display();
echo $OUTPUT->box_end();
echo $OUTPUT->footer();
