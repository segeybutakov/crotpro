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
 *
 * @package   plagiarism_crotpro
 * @author    Tosin Komolafe, Sergey Butakov, Svetlana Kim
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright CrotSoftware 2012
 */

$string['pluginname'] = 'Crot Pro Plagiarism Checker';
$string['studentdisclosuredefault']  ='All files uploaded will be submitted to a plagiarism detection service';
// strings used for help information
$string['studentdisclosure'] = 'Student Disclosure';
$string['studentdisclosure_help'] = 'This text will be displayed to all students on the file upload page.';
$string['colour'] = 'Colours';
$string['colour_help'] = 'These colours are used for highlighting of similar text fragments in the side by side comparison of documents. But at the moment colouring function works only with the one colour (#FF0000).';
$string['globalsearchquerysize'] = 'Global Search Query Size';
$string['globalsearchquerysize_help'] = 'This is the number of words in the query for global search. Recommended value is 7.';
$string['cultureinfo'] = 'Culture info for global search';
$string['cultureinfo_help'] = 'Culture information is used in queries for Bing search engine.';
$string['defaultthreshold'] = 'Default Threshold';
$string['defaultthreshold_help'] = 'Assignments with similarity score less than threshold value are not displayed on Anti-Plagiarism - Assignments page.';
$string['globalsearchthreshold'] = 'Global search threshold';
$string['globalsearchthreshold_help'] = 'Reserved for future development. Recommended value is 90.';
$string['account_id'] = 'Account ID<script language="JavaScript">function win1() {
    var url = "registration.php?url="+document.getElementById("id_crotpro_service_url").value;
    window.open(url,"Registration_form","menubar=no,width=400,height=360,toolbar=no");
    } </script><a href="javascript:win1()" onMouseOver="self.status=\'Open A Window\'; return true;"><br/><b>Free Account ID</b></a>';
/*'Account ID<script language="JavaScript">function win1() {
    window.open("registration.php","Registraion form","menubar=no,width=460,height=360,toolbar=no");
    } </script> <br/><br/> <a href="javascript:win1()" onMouseOver="self.status=\'Open A Window\'; return true;">get <b>FREE</b> ID</a><br/>';*/

$string['account_id_help'] = 'This is your account id for Crot Pro.';
$string['service_url'] = 'Link to Cro Pro Service';
$string['service_url_help'] = 'This is the link to the PDS service for Crot Pro';
$string['cleantables'] = 'Clean tables';
$string['cleantables_help'] = 'It removes all Crotpro data except assignments set for check up! Please note that all teh checkup results will be removed from the database. The assignments will NOT be resubmitted for the check up again';

$string['newexplain'] = 'For more information on this plugin see:';
$string['crotpro'] = 'Crot Pro';
$string['crotpro_help'] = 'Crot Pro is an anti-plagiarism tool which supports doc, docx, pptx, ppt, pdf, odt, rtf, txt, cpp and java files.';
$string['crotexplain'] = 'Crot Pro is a connector to plagiarism detection  service. <br/><br/>More information can be found at <a href="http://www.crotsoftware.com">www.crotsoftware.com</a> <br/>';

$string['crotpro_reg'] = '<script language="JavaScript">function win1() {
    window.open("registration.php","Registration_form","menubar=no,width=460,height=360,toolbar=no");
    } </script><a href="javascript:win1()" onMouseOver="self.status=\'Open A Window\'; return true;"><b>FREE registration</b></a>';

$string['default_threshold']='Default Threshold';
$string['usecrot'] ='Enable Crot Pro';
$string['savedconfigsuccess'] = 'Crot Pro Settings have been saved';
$string['compareinternet'] = 'Compare submitted files with Internet';

//strings copied from block - probably need reworking for new plugin
$string['block_name'] = 'Anti-Plagiarism';
$string['course_summary'] = 'Course Summary';
$string['Topics'] = 'Topics';
$string['report'] = 'Report';
//$string['settings'] = 'Settings';
$string['have_to_be_a_teacher'] = 'You have to be a teacher to see this content';
$string['assignments']='Assignments';
$string['global']='Global';
$string['settings']='Settings';
$string['settings_cancelled']='Antiplagiarism settings have been cancelled';
$string['settings_saved']='Antiplagiarism settings have been saved successfully';
$string['save']='Save';
$string['select_assignment']='Select the assignment';
$string['student_name']='Student name';
$string['similar']='Similar assignments';
$string['colours']='Colours';
$string['global_search_settings']='<b>Global Search settings</b>';
$string['test_global_search']='Test global search';
//$string['global_search_threshold']='Global Search Threshold';
$string['culture_info']='Culture info for global search';
$string['tools']='<b>Tools</b>';
$string['registration'] = '<b>Registration</b>';
$string['createaccount']='<b>CrotPro Free Account</b>';

$string['no_similarities'] = 'no similarities';
$string['incorrect_courseid'] = 'Course ID is incorrect';
$string['incorrect_courseAid'] = 'Course A ID is incorrect';
$string['incorrect_courseBid'] = 'Course B ID is incorrect';
$string['incorrect_docAid'] = 'Doc A ID is incorrect';
$string['incorrect_docBid'] = 'Doc B ID is incorrect';
$string['incorrect_fileAid'] = 'File A ID is incorrect';
$string['incorrect_fileBid'] = 'File B ID is incorrect';
$string['incorrect_submAid'] = 'Submission A ID is incorrect';
$string['incorrect_submBid'] = 'Submission B ID is incorrect';
$string['incorrect_assignmentAid'] = 'Assignment A ID is incorrect';
$string['incorrect_assignmentBid'] = 'Assignment B ID is incorrect';
$string['tables_cleaned_up'] = 'Crot tables were cleaned up!';
$string['incorrect_file'] = 'File not found';


$string['col_name'] = 'Name';
$string['col_course'] = 'Course';
$string['col_similarity_score'] = 'Similarity score';
$string['file_was_not_found'] = 'Cannot find local file. Most likely it was removed from the system';
$string['course_not_applicable'] = 'not applicable';
$string['no_plagiarism'] = 'no plagiarism have been detected';
$string['name_unknown'] = 'name is unknown';
$string['webdoc'] = 'Web document: <br> Source:';
$string['webdocument'] = 'Web document';
$string['bing_search'] = '<br>Crot Pro is supported by Bing search engine';
$string['assignments_not_displayed'] = 'Assignments with similarity score less than {$a}% are not displayed';

$string['FILE_FORMAT_NOT_ALLOWED'] = "Sorry but this file format is not allowed for upload";