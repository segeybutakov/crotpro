<?php
/**
 *
 * @author    Tosin Komolafe, Sergey Butakov, Svetlana Kim
 * @copyright CrotSoftware 2012
 * 
 */


    require_once("../../config.php");
    require_once($CFG->dirroot."/course/lib.php");
    require_once($CFG->dirroot."/mod/assignment/lib.php");
    global $CFG;
    $file_id = required_param('id', PARAM_INT);   // file id
    $user_id = required_param('user_id', PARAM_INT); // user id
    $cid = required_param('cid', PARAM_INT);    // course id
    
    if (! $course = $DB->get_record("course", array("id" => $cid))) {
        print_error(get_string('incorrect_courseid','plagiarism_crotpro'));
    }
    require_course_login($course);
    
    $strmodulename = get_string("block_name", "plagiarism_crotpro");
    $strassignment  = get_string("assignments", "plagiarism_crotpro");
    $strstudent = get_string("student_name", "plagiarism_crotpro");
    $strsimilar = get_string("similar", "plagiarism_crotpro");
    $strname = get_string('col_name','plagiarism_crotpro');
    $strcourse = get_string('col_course','plagiarism_crotpro');
    $strscore = get_string('col_similarity_score','plagiarism_crotpro');
    $strnoplagiarism = get_string('no_plagiarism','plagiarism_crotpro');
    
    if (!$sub = $DB->get_record("plagiarism_crotpro_job", array("file_id" => $file_id))) {
        print_error(get_string('incorrect_file','plagiarism_crotpro'));        
    }
    
    if (!$file = $DB->get_record("files", array("id" => $sub->file_id))) {
        print_error(get_string('incorrect_fileAid','plagiarism_crotpro'));
    }
    
    if (!$submission = $DB->get_record("assignment_submissions", array("id" => $file->itemid))) {
        print_error(get_string('incorrect_submAid','plagiarism_crotpro'));
    }
    
    if (!$assign = $DB->get_record("assignment", array("id" => $submission->assignment))) {
		print_error(get_string('incorrect_assignmentAid','plagiarism_crotpro'));
    }
    
    if(!has_capability('mod/assignment:grade', get_context_instance(CONTEXT_MODULE, $sub->cm))) {
        print_error(get_string('have_to_be_a_teacher', 'plagiarism_crotpro'));
    }
    
    // build navigation and header   
    $view_url = new moodle_url('/mod/assignment/view.php', array('id' => $sub->cm));
    $PAGE->navbar->add($assign->name,$view_url);
    $PAGE->navbar->add($strmodulename. " - " . $strassignment);
    $PAGE->set_title($course->shortname.": ".$assign->name.": ".$strmodulename. " - " . $strassignment);
    $PAGE->set_heading($course->fullname);
    $PAGE->set_url('/plagiarism/crotpro/index.php', array('id' => $file_id, 'cid'=>$cid, 'user_id' => $user_id));
    echo $OUTPUT->header();
    $table = new html_table();
    $table->head  = array ($strstudent, $strsimilar);
    $table->align = array ("left", "left");
    $table->size = array('30%', '60%');
    
    $plagiarismsettings = (array)get_config('plagiarism');
    $threshold = $plagiarismsettings['crotpro_threshold'];
    // select all the assignments that have similarities with the current document
    $table2 = "<table border=2 width='100%'><tr><td width='50%'>$strname</td><td width='40%'>$strcourse</td><td  width='10%'>$strscore</td></tr>";
    $web_document = 'Web document';
    $sus_document_length = 0;
    
    $xml = new DOMDocument();
    $xml->loadXML($sub->result);
    $xml2 = simplexml_load_string($sub->result);
    $web_links = $xml->getElementsByTagName("url");
    $source_document_ids = $xml->getElementsByTagName("source_document_id");
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
            $perc = ($sus_document_length/$text_length) * 100;
            if($perc > 100){
                $perc = 100.00;
            }
            $perc_link = "<a href=\"compare.php?id=$file_id&source=$source_document_id\">".round($perc,2)."</a>";
            if($perc > $threshold )
            $table2 = $table2."<tr><td>$web_link</td><td>$web_document</td><td>$perc_link %</td></tr>";
            $sus_document_length = 0;
        }
    }else{
        $table2 = "<table border=2 width='100%'><tr><td>$strnoplagiarism</td></tr>";
    }

    $table2 = $table2."</table>";
    $user = $DB->get_record("user", array("id"=>$user_id));// get user of the current document
    $namelink = "<a href=\"../../user/view.php?id=$user_id\">".fullname($user)."</a>";
    $table->data[] = array ($namelink, $table2);
    echo html_writer::table($table);
    echo get_string('bing_search','plagiarism_crotpro')." <a href =\"http://www.bing.com\" target=\"_new\"><img src= \"http://www.bing.com/siteowner/s/siteowner/Logo_63x23_Dark.png\"> </a>";
    echo $OUTPUT->footer($course);
?>