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
 * lib.php - Contains Plagiarism plugin specific functions called by Modules.
 *
 * @since 2.0
 * @package    plagiarism_crotpro
 * @subpackage plagiarism
 * @author     Tosin Komolafe
 * @copyright  CrotSoftware 2012
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');    ///  It must be included from a Moodle page
}

//get global class
global $CFG;
require_once($CFG->dirroot.'/plagiarism/lib.php');
require_once($CFG->dirroot. '/plagiarism/crotpro/post_xml.php');

///// Crot Class ////////////////////////////////////////////////////
class plagiarism_plugin_crotpro extends plagiarism_plugin {
     /**
     * hook to allow plagiarism specific information to be displayed beside a submission 
     * @param array  $linkarray contains all relevant information for the plugin to generate a link
     * @return string
     * 
     */
    public function get_links($linkarray) {
        //$userid, $file, $cmid, $course, $module
        global $DB, $CFG;
        $cmid = $linkarray['cmid'];
        $userid = $linkarray['userid'];
        $file = $linkarray['file'];
        $course = $linkarray['course'];
        $cid = $course->id;
        $output = '';
        
        if (!$plagiarism_crot_job = $DB->get_record("plagiarism_crotpro_job", array("file_id"=>$file->get_id()))){
            $output .= "<br><b>Pending!</b>";
        }else{
            if(!is_null($plagiarism_crot_job->result)){
              $file_id = $file->get_id();
                $xml = new DOMDocument();
                $xml->loadXML($plagiarism_crot_job->result);
                $xml2 = simplexml_load_string($plagiarism_crot_job->result);
               
                $web_links = $xml->getElementsByTagName("url");
                $source_document_ids = $xml->getElementsByTagName("source_document_id");
                $percents = array();
                $sus_document_length = 0;
                if($web_links->length <> 0 && $source_document_ids->length <> 0){
                    $text_length = strlen(($xml2->pdrml->suspicious_document[0]->text));
                    for($i = 0; $i < $web_links->length; $i++){
                        $web_link = $web_links->item($i)->nodeValue;
                        $source_document_id = $source_document_ids->item($i)->nodeValue;
                            foreach($xml2->pdrml->findings->feature as $feature){
                               if($feature->source_document_id == $source_document_id){ 
                                  $sus_document_length = $sus_document_length + trim ($feature->suspicious_document_length);
                               }
                            }
                        $percents[] = ($sus_document_length/$text_length) * 100;
                        $sus_document_length = 0;
                    }
                }
                $max = 0;
                if(!empty ($percents)){
                    $max = round(max($percents),2);
                    if($max > 100){
                        $max = 100.00;
                    }
                }
              $output .= "<br><b> <a href=\"../../plagiarism/crotpro/index.php?id=$file_id&cid=$cid&user_id=$userid\">pds: ".$max."%</a></b>";
            }else{
                $output .= "<br><b>In Progress!</b>";
            } 
        }
            
        return $output;
    }
    

    /* hook to save plagiarism specific settings on a module settings page
     * @param object $data - data from an mform submission.
    */
    public function save_form_elements($data) {
        global $DB;
        $plagiarismsettings = (array)get_config('plagiarism');
        if (!empty($plagiarismsettings['crotpro_use'])) {
            if (isset($data->crotpro_use)) {
                //array of posible plagiarism config options.
                $plagiarismelements = $this->config_options();
                //first get existing values
                $existingelements = $DB->get_records_menu('plagiarism_crotpro_config', array('cm'=>$data->coursemodule),'','name,id');
                foreach($plagiarismelements as $element) {
                    $newelement = new object();
                    $newelement->cm = $data->coursemodule;
                    $newelement->name = $element;
                    $newelement->value = (isset($data->$element) ? $data->$element : 0);
                    if (isset($existingelements[$element])) { //update
                        $newelement->id = $existingelements[$element];
                        $DB->update_record('plagiarism_crotpro_config', $newelement);
                    } else { //insert
                        $DB->insert_record('plagiarism_crotpro_config', $newelement);
                    }
                }
            }
        }
    }

    /**
     * hook to add plagiarism specific settings to a module settings page
     * @param object $mform  - Moodle form
     * @param object $context - current context
     */
    public function get_form_elements_module($mform, $context) {
        global $DB;
        $plagiarismsettings = (array)get_config('plagiarism');
        if (!empty($plagiarismsettings['crotpro_use'])) {
            $cmid = optional_param('update', 0, PARAM_INT); //there doesn't seem to be a way to obtain the current cm a better way - $this->_cm is not available here.
            if (!empty($cmid)) {
                $plagiarismvalues = $DB->get_records_menu('plagiarism_crotpro_config', array('cm'=>$cmid),'','name,value');
            }
            $plagiarismelements = $this->config_options();

            $ynoptions = array( 0 => get_string('no'), 1 => get_string('yes'));
            $mform->addElement('header', 'crotprodesc', get_string('crotpro', 'plagiarism_crotpro'));
            $mform->addHelpButton('crotprodesc', 'crotpro', 'plagiarism_crotpro');
            $mform->addElement('select', 'crotpro_use', get_string("usecrot", "plagiarism_crotpro"), $ynoptions);
            foreach ($plagiarismelements as $element) {
                if (isset($plagiarismvalues[$element])) {
                    $mform->setDefault($element, $plagiarismvalues[$element]);
                }
            }
        }
        //Add elements to form using standard mform like:
        //$mform->addElement('hidden', $element);
        //$mform->disabledIf('plagiarism_draft_submit', 'var4', 'eq', 0);
    }

    /**
     * hook to allow a disclosure to be printed notifying users what will happen with their submission
     * @param int $cmid - course module id
     * @return string
     */
    public function print_disclosure($cmid) {
         global $DB, $OUTPUT;
         // check if this cmid has plagiarism enabled
         $select = 'cm = ? AND '.$DB->sql_compare_text('name').' = "crotpro_use"';
         if (! $crot_use = $DB->get_record_select('plagiarism_crotpro_config', $select, array($cmid))) {
            return;
         } else if ($crot_use->value == 0) {
            return;
         }
        $plagiarismsettings = (array)get_config('plagiarism');
        echo $OUTPUT->box_start('generalbox boxaligncenter', 'intro');
        $formatoptions = new stdClass;
        $formatoptions->noclean = true;
        echo format_text($plagiarismsettings['crotpro_student_disclosure'], FORMAT_MOODLE, $formatoptions);
        echo $OUTPUT->box_end();
    }

    /**
     * hook to allow status of submitted files to be updated - called on grading/report pages.
     *
     * @param object $course - full Course object
     * @param object $cm - full cm object
     */
    public function update_status($course, $cm) {
        //called at top of submissions/grading pages - allows printing of admin style links or updating status
    }

    /**
     * called by admin/cron.php 
     *
     */
    public function cron() {
        //do any scheduled task stuff
        global $CFG;
        require_once($CFG->dirroot.'/plagiarism/crotpro/cron.php');
        
    }
    public function config_options() {
        return array('crotpro_use');
    }
}

function crot_event_file_uploaded_crotpro($eventdata) {
    global $DB;
    $result = true;
        //a file has been uploaded - submit this to the plagiarism prevention service.
        
    return $result;
}
function crot_event_files_done_crotpro($eventdata) {
    global $DB;
    global $CFG;
    $result = true;
        //mainly used by assignment finalize - used if you want to handle "submit for marking" events
        //a file has been uploaded/finalised - submit this to the plagiarism prevention service.
    $plagiarismvalues = $DB->get_records_menu('plagiarism_crotpro_config', array('cm'=>$eventdata->cmid),'','name,value');
    if (empty($plagiarismvalues['crotpro_use'])) {
        return $result;
    }
    else {
        $modulecontext = get_context_instance(CONTEXT_MODULE, $eventdata->cmid);
        $fs = get_file_storage();
        $status_value = array('queue','sent');
        if ($files = $fs->get_area_files($modulecontext->id, 'mod_assignment','submission', $eventdata->itemid)) {
           // put files that were submitted for marking into queue for check up
            foreach ($files as $file) {
                if ($file->get_filename()==='.') {
                    continue;
                }
                
                $newelement = new stdclass();
                $newelement->file_id = $file->get_id();
                $newelement->path = $file->get_contenthash();
                $newelement->status = $status_value[0]; 
                $newelement->time = time();
                $newelement->cm = $eventdata->cmid;    
                $newelement->courseid = $eventdata->courseid;
                $result=$DB->insert_record('plagiarism_crotpro_files', $newelement);
                echo "\nfile ".$file->get_filename()." was queued up for crot PDS!\n";
            }
        }
        return $result;
    }
}

function crot_event_mod_created_crotpro($eventdata) {
    $result = true;
        //a new module has been created - this is a generic event that is called for all module types
        //make sure you check the type of module before handling if needed.

    return $result;
}

function crot_event_mod_updated_crotpro($eventdata) {
    $result = true;
        //a module has been updated - this is a generic event that is called for all module types
        //make sure you check the type of module before handling if needed.

    return $result;
}

function crot_event_mod_deleted_crotpro($eventdata) {
    $result = true;
        //a module has been deleted - this is a generic event that is called for all module types
        //make sure you check the type of module before handling if needed.

    return $result;
}
