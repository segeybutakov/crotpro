<?php
/**
 *
 * @author    Tosin Komolafe, Sergey Butakov, Svetlana Kim
 * @copyright CrotSoftware 2012
 * 
 */
    require_once("../../config.php");
    global $CFG, $DB;
    require_once($CFG->dirroot."/course/lib.php");
    require_once($CFG->dirroot."/mod/assignment/lib.php");
    
    // globals
    $plagiarismsettings = (array)get_config('plagiarism');
    $allColors	= explode(",", $plagiarismsettings['crotpro_colours']);
    
    $file_id = required_param('id', PARAM_INT);   // file id
    $source_id = required_param('source', PARAM_INT);   // source id
    
    if (!$sub = $DB->get_record("plagiarism_crotpro_job", array("file_id" => $file_id))) {
        print_error(get_string('incorrect_file','plagiarism_crotpro'));        
    }
    
    if (!$file = $DB->get_record("files", array("id" => $sub->file_id))) {
        print_error(get_string('incorrect_fileBid','plagiarism_crotpro'));
    }
    if (!$submission = $DB->get_record("assignment_submissions", array("id" => $file->itemid))) {
        print_error(get_string('incorrect_submBid','plagiarism_crotpro'));
    }
    if (!$assign = $DB->get_record("assignment", array("id" => $submission->assignment))) {
            print_error(get_string('incorrect_assignmentBid','plagiarism_crotpro'));
    }
    if (!$course = $DB->get_record("course", array("id" => $sub->courseid))) {
            print_error(get_string('incorrect_courseBid','plagiarism_crotpro'));
    }

    require_course_login($course);
    if(!has_capability('mod/assignment:grade', get_context_instance(CONTEXT_MODULE, $sub->cm))) {
        print_error(get_string('have_to_be_a_teacher', 'plagiarism_crotpro'));
    }
    
    // built navigation	
    $strmodulename = get_string("block_name", "plagiarism_crotpro");
    $strassignment  = get_string("assignments", "plagiarism_crotpro");
    
    $view_url = new moodle_url('/mod/assignment/view.php', array('id' => $sub->cm));
    $PAGE->navbar->add($assign->name,$view_url);
    $PAGE->navbar->add($strmodulename. " - " . $strassignment);
    $PAGE->set_title($course->shortname.": ".$assign->name.": ".$strmodulename. " - " . $strassignment);
    $PAGE->set_heading($course->fullname);
    $PAGE->set_url('/plagiarism/crotpro/compare.php', array('id' => $file_id, 'source' => $source_id));
    echo $OUTPUT->header();
    $xml = simplexml_load_string($sub->result); 
    foreach($xml->pdrml->source_document as $source_document){
        if($source_document->source_document_id == $source_id){
            $web_link = $source_document->url;
            if (strlen($web_link)>40) {
		$link_name = substr($web_link,0,40);  
            }else{
                $link_name = $web_link;
            }
            $web_link = get_string('webdoc','plagiarism_crotpro'). " ". "<a href=".$web_link." target=\"_blank\">".$link_name."</a>";
            break;
        }
    }
    
    // get content of the document
    $textA = stripslashes($xml->pdrml->suspicious_document[0]->text);
    $textB = stripslashes($source_document->text);
    $color_size = count($allColors);
    $color_counter = 0;
    // TODO create separate function for coloring ?
    foreach($xml->pdrml->findings->feature as $feature){
       if($color_size == $color_counter){
           $color_counter = 0;
       }
       if($feature->source_document_id == $source_id){ 
          $source_document_offset = trim($feature->source_document_offset);
          $source_document_length = trim ($feature->source_document_length);
          $suspicious_document_offset = trim ($feature->suspicious_document_offset);
          $suspicious_document_length = trim ($feature->suspicious_document_length);
          $endPosA = $suspicious_document_offset+$suspicious_document_length;
          $endPosB = $source_document_offset+$source_document_length;
          $textA = colorer($textA, $suspicious_document_offset, $endPosA, $allColors[$color_counter]);
          $textB = colorer($textB, $source_document_offset ,$endPosB , $allColors[$color_counter]);
          $color_counter = $color_counter + 1;
       }
    }
    
    if (!$student = $DB->get_record("files", array("id" => $sub->file_id))) {
        $strstudent = get_string('name_unknown','plagiarism_crotpro');
    } else{
       $strstudent = $student->author.":<br> ".$course->shortname.", ".$assign->name;
    }
?>
<STYLE><!--
#example{scrollbar-3d-light-color:'#0084d8';scrollbar-arrow-color:'black';scrollbar-base-color:'#00568c';scrollbar-dark-shadow-color:'';scrollbar-face-color:'';scrollbar-highlight-color:'';scrollbar-shadow-color:'';text-align:left;position:relative;width:440px; 
padding:2px;height:300px;overflow:scroll;border-width:2px;border-style:outset;background-color:lightgray;}
--></STYLE>

<?php
    $textA = "<div id=\"example\"><FONT SIZE=1>".ereg_replace("\n","<br>",$textA)."</font> </div>";
    $textB = "<div id=\"example\"><FONT SIZE=1>".ereg_replace("\n","<br>",$textB)."</font> </div>";
    $table = new html_table();
    $table->head  = array ($strstudent, $web_link);
    $table->align = array ("center", "center");
    $table->data[] = array ($textA, $textB);
    echo html_writer::table($table);
    echo $OUTPUT->footer($course);
    
/*
* it replaces part of the text from $start to $end with the same text but colored with $color
*/
function colorer($text, $start, $end, $color) {
    $rem = mb_strlen($text)-$end-1;
	return mb_substr($text,0,$start, "utf-8")."<b><font color=\"$color\">".mb_substr($text,$start,$end-$start+1, "utf-8")."</font></b>".mb_substr($text,$end+1,$rem, "utf-8");
}// end of function colorer
    
?>