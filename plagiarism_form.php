<?php
/**
 * @author    Tosin Komolafe, Sergey Butakov, Svetlana Kim
 * @copyright CrotSoftware 2012
 */
require_once($CFG->dirroot.'/lib/formslib.php');

class plagiarism_setup_form extends moodleform {

/// Define the form
    function definition () {
        global $CFG;

        $mform =& $this->_form;
        $choices = array('No','Yes');
        $mform->addElement('html', get_string('crotexplain', 'plagiarism_crotpro'));
        $mform->addElement('checkbox', 'crotpro_use', get_string('usecrot', 'plagiarism_crotpro'));

        $mform->addElement('textarea', 'crotpro_student_disclosure', get_string('studentdisclosure','plagiarism_crotpro'),'wrap="virtual" rows="6" cols="50"');
        $mform->addHelpButton('crotpro_student_disclosure', 'studentdisclosure', 'plagiarism_crotpro');
        $mform->setDefault('crotpro_student_disclosure', get_string('studentdisclosuredefault','plagiarism_crotpro'));

        $mform->addElement('text', 'crotpro_colours', get_string('colours', 'plagiarism_crotpro'));
        $mform->addHelpButton('crotpro_colours', 'colour', 'plagiarism_crotpro');
        $mform->setDefault('crotpro_colours', '#FF0000,#0000FF, #A0A000, #00A0A0');

        
        $mform->addElement('text', 'crotpro_threshold', get_string('default_threshold', 'plagiarism_crotpro'));
        $mform->addHelpButton('crotpro_threshold', 'defaultthreshold', 'plagiarism_crotpro');
        $mform->setDefault('crotpro_threshold', '0');
        $mform->addRule('crotpro_threshold', null, 'numeric', null, 'client');
        // Global Search settings
        
        /*$mform->addElement('text', 'crotpro_culture_info', get_string('culture_info', 'plagiarism_crotpro'));
        $mform->addHelpButton('crotpro_culture_info', 'cultureinfo', 'plagiarism_crotpro');
        $mform->setDefault('crotpro_culture_info', 'en-us');*/
        
        // Web Service Configuration
        $mform->addElement('text', 'crotpro_service_url', get_string('service_url', 'plagiarism_crotpro'));
        $mform->setDefault('crotpro_service_url', 'http://beta.noplagiarism.org');
        $mform->addRule('crotpro_service_url', null, 'required', null, 'client');        
         
        $mform->addElement('text', 'crotpro_account_id', get_string('account_id', 'plagiarism_crotpro'));
        $mform->setDefault('crotpro_account_id', '');
        $mform->addRule('crotpro_account_id', null, 'required', null, 'client');
        
        // Tools
        $mform->addElement('html', get_string('tools', 'plagiarism_crotpro'));
        $mform->addElement('checkbox', 'delall', get_string('cleantables', 'plagiarism_crotpro'));
        $mform->addHelpButton('delall', 'cleantables', 'plagiarism_crotpro');
        $mform->addElement('checkbox', 'testglobal', get_string('test_global_search', 'plagiarism_crotpro'));
//        $mform->addElement('checkbox', 'createaccount', get_string('createaccount', 'plagiarism_crotpro'));
//        $mform->addElement('checkbox', 'registration', get_string('registration', 'plagiarism_crotpro'));

        $this->add_action_buttons(true);
    }
}

