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
 * This file is used when adding/editing a flashcard to a cardboxx.
 *
 * @package   mod_cardboxx
 * @copyright 2019 RWTH Aachen (see README.md)
 * @author    Anna Heynkes
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die(); // It must be included from a Moodle page.
define ('ALLOW_AUTOCORRECTION_FOR_ENTIRE_CARDBOXX', 1);
define ('AUTOCORRECTION_NOT_ALLOWED_FOR_ENTIRE_CARDBOXX', 0);
require_once("$CFG->libdir/formslib.php");
require_once('locallib.php');

/**
 * Class mod_cardboxx_card_form
 */
class mod_cardboxx_card_form extends moodleform {

    /**
     * This function is called by the constructor.
     *
     * @param string|null $action The action to perform, default is null
     * @param array|null $preselected The preselected data, default is null
     * @param int $cardid The id of the card, default is 0
     */
    public function definition($action = null, $preselected = null, $cardid=0) {

        global $CFG, $DB, $USER, $COURSE;

        $mform = $this->_form;

        $customdata = $this->_customdata;

        // Pass contextual parameters to the form (via set_data() in controller.php).
        $mform->addElement('hidden', 'id'); // Course module id.
        $mform->setType('id', PARAM_INT);

        $mform->addElement('hidden', 'course'); // Course id.
        $mform->setType('course', PARAM_INT);

        $mform->addElement('hidden', 'action');
        $mform->setType('action', PARAM_ALPHANUM);

        $mform->addElement('hidden', 'from');
        $mform->setType('from', PARAM_ALPHA);

        $mform->addElement('hidden', 'cardid');
        $mform->setType('cardid', PARAM_INT);
        $mform->setDefault('cardid', 0);

        $mform->addElement('hidden', 'next');
        $mform->setType('next', PARAM_INT);
        $mform->setDefault('next', 0);

        /*
        // Get topics to choose from when creating a new card.
        $topiclist = cardboxx_get_topics($customdata['cardboxxid'], true);

        $choosetopicarray = [];
        $choosetopicarray[] =& $mform->createElement('select', 'topic', get_string('choosetopic', 'cardboxx'), $topiclist);
        $choosetopicarray[] =& $mform->createElement('text', 'newtopic', '');
        $mform->addGroup($choosetopicarray, 'choosetopicar', get_string('choosetopic', 'cardboxx'), [' '], false);

        if (!empty($customdata['topic'])) {
            $choosetopicarray[0]->setSelected($customdata['topic']);
        }

        // Text input field for creating a new topic.
        $mform->setType('newtopic', PARAM_CLEANHTML); // Supports german letters ä, ö, ü.
        */

        /****************** end of question experiment **********************/

        /****************** question **********************/

        $mform->addElement('editor', 'question', get_string('enterquestion', 'cardboxx'),
                           'wrap="virtual" rows="5" cols="150"');
        $mform->setType('question', PARAM_RAW);

        $btnarrayquestion = [];
        $btnarrayquestion[] =& $mform->createElement('button', 'addimage', get_string('addimage', 'cardboxx'));
        $btnarrayquestion[] =& $mform->createElement('button', 'addsound', get_string('addsound', 'cardboxx'));
        // ...$btnarrayquestion[] =& $mform->createElement('button', 'addcontextques', get_string('addcontext', 'cardboxx'));
        $mform->addGroup($btnarrayquestion, 'buttonar', '', [' '], false);

        $options = [];
        $options['accepted_types'] = ['.bmp', '.gif', '.jpeg', '.jpg', '.png', '.svg'];

        $options['maxbytes'] = 0;
        $options['maxfiles'] = 1;
        $options['mainfile'] = true;
        $mform->addElement('filemanager', 'cardimage', get_string('image', 'cardboxx'), null, $options);

        $mform->addElement('text', 'imagedescription', get_string('imagedescription', 'cardboxx'));
        $mform->setType('imagedescription', PARAM_TEXT);

        $label = get_string('imgdescriptionnecessary_label', 'cardboxx');
        $imagedescriptionarray = [];
        $imagedescriptionarray[] =& $mform->createElement('checkbox', 'imgdescriptionnecessary', '');
        $imagedescriptionarray[] =& $mform->createElement('html', "<p style='margin: 1rem'>$label</p>");
        $mform->addGroup($imagedescriptionarray, 'imgdescriptionar', '', [' '], false);

        $audiooptions = [];
        $audiooptions['accepted_types'] = ['.mp3'];
        $audiooptions['maxbytes'] = 0;
        $audiooptions['maxfiles'] = 1;
        $audiooptions['mainfile'] = true;
        $mform->addElement('filemanager', 'cardsound', get_string('sound', 'cardboxx'), null, $audiooptions);

        /****************** questioncontext **********************/
        // ...$mform->addElement('editor', 'questioncontext', get_string('entercontextquestion', 'cardboxx'),
        // ...'wrap="virtual" rows="5" cols="150"');
        // ...$mform->setType('question', PARAM_RAW);

        /****************** end of question **********************/

        $infoanswer = get_string('answer_repeat_help', 'cardboxx');

        for ($i = 1; $i <= 10; $i++) {
            $mform->addElement('editor', "answer$i", get_string('enteranswer', 'cardboxx') ,
                               'wrap="virtual" rows="5" cols="150"');
            $mform->setType("answer$i", PARAM_RAW);
            if ($i === 1) {
                $mform->addElement('html',
                "<div class='form-group row fitem' style='margin-bottom: 1.5rem;'>
                <div class='col-md-3 col-form-label d-flex pb-0 pr-md-0'></div>
                <div class='col-md-9 form-inline align-items-start felement'></div></div>");
            }
        }

        // ...$btnarrayanswer = [];
        // ...$btnarrayanswer[] =& $mform->createElement('button', 'addanswer', get_string('answer_repeat', 'cardboxx'));
        // ...$btnarrayanswer[] =& $mform->createElement('button', 'addcontextans', get_string('addcontext', 'cardboxx'));
        // ...$mform->addGroup($btnarrayanswer, 'buttonar', '', [' '], false);

        /*
        $necessaryanswerslocked = $DB->get_field('cardboxx', 'necessaryanswerslocked',
                                                 ['id' => $customdata['cardboxxid']], IGNORE_MISSING);
        if ($necessaryanswerslocked === "0") {
            $aoptions = [
                '0' => get_string('necessaryanswers_all', 'cardboxx'),
                '1' => get_string('necessaryanswers_one', 'cardboxx'),
            ];
            $select = $mform->addElement('select', 'answers', get_string('necessaryanswers_card', 'cardboxx'), $aoptions);
            $necessaryanswers = $DB->get_field('cardboxx', 'necessaryanswers',
                                               ['id' => $customdata['cardboxxid']], IGNORE_MISSING);

            if (!empty($customdata['answers']) && $customdata['answers'] != $necessaryanswers) {
                $necessaryanswers = $customdata['answers'];
            }
            $select->setSelected($necessaryanswers);
        }
        */

        /****************** answercontext **********************/

        // ...$mform->addElement('editor', 'answercontext', get_string('entercontextanswer', 'cardboxx'),
        // ...'wrap="virtual" rows="5" cols="150"');
        // ...$mform->setType('question', PARAM_RAW);

        /****************** Disable Auto check setting ****************** */

        /*
        if ($customdata['allowautocorrection'] == ALLOW_AUTOCORRECTION_FOR_ENTIRE_cardboxx) {
            $mform->addElement('checkbox', 'disableautocorrect', get_string('autocorrectlocked', 'cardboxx'));
            $mform->addHelpButton('disableautocorrect', 'autocorrectlocked', 'cardboxx');
            $mform->setDefault('disableautocorrect', 0);
        } else {
            $mform->addElement('checkbox', 'disableautocorrect', get_string('autocorrectlocked', 'cardboxx'));
            $mform->addHelpButton('disableautocorrect', 'autocorrectlocked', 'cardboxx');
            $mform->setDefault('disableautocorrect', 0);
        }
        */

        $context = context_module::instance($customdata['cmid']);
        if (array_key_exists('cardid', $customdata)) {
            $cardapproved = cardboxx_card_approved($customdata['cardid']);
        } else {
            $cardapproved = false;
        }

        if (has_capability('mod/cardboxx:approvecard', $context)) {
            $this->add_action_buttons_for_managers(true);

        }
        /*else {
            $this->add_action_buttons(true, get_string('savecard', 'cardboxx'));
        }*/

    }

    /**
     * This function allows managers to save and accept a card in one action.
     *
     * @param bool $cancel Whether to cancel the action, default is true
     * @param string|null $submitlabel The label for the submit button, default is null
     */
    public function add_action_buttons_for_managers($cancel=true, $submitlabel=null) {
        if (is_null($submitlabel)) {
            $submitlabel = get_string('saveandaccept', 'cardboxx');
        }
        /*
        if (is_null($submit2label)) {
            $submit2label = get_string('savecard', 'cardboxx');
        }
        */

        $mform = $this->_form;

        // Elements in a row need a group.
        $buttonarray = [];

        /*
        if ($submit2label !== false) {
            $buttonarray[] = &$mform->createElement('submit', 'submitbutton2', $submit2label);
        }
        */

        if ($submitlabel !== false) {
            $buttonarray[] = &$mform->createElement('submit', 'submitbutton', $submitlabel);
        }

        if ($cancel) {
            $buttonarray[] = &$mform->createElement('cancel');
        }

        $mform->addGroup($buttonarray, 'buttonar', '', [' '], false);
        $mform->setType('buttonar', PARAM_RAW);
        $mform->closeHeaderBefore('buttonar');
    }


    /**
     * This function checks whether the user entered text, an image and/or an audio file
     * for a question.
     *
     * @param array $data The data to validate
     * @param array $files The files to validate
     * @return array The validation errors
     */
    public function validation($data, $files) {

        global $USER;

        $errors = parent::validation($data, $files);

        $question = $data['question'];
        $questiontext = $question['text'];

        $fs = get_file_storage();
        $usercontext = context_user::instance($USER->id);

        $draftitemid = $data['cardimage'];
        $imagefiles = $fs->get_area_files($usercontext->id, 'user', 'draft', $draftitemid, 'sortorder, id', false);

        $draftitemid2 = $data['cardsound'];
        $audiofiles = $fs->get_area_files($usercontext->id, 'user', 'draft', $draftitemid2, 'sortorder, id', false);

        $answer = $data['answer1'];
        $answertext = $answer['text'];

        if (empty($data['imgdescriptionnecessary']) && !empty($imagefiles) && $data['imagedescription'] === "") {
            $errors['files'] = get_string('error:imagedescription', 'cardboxx');
        }

        if ( (empty($questiontext) && empty($imagefiles) && empty($audiofiles)) || empty($answertext) ) {
            $errors['files'] = get_string('required');
        }
        return $errors;
    }

}
