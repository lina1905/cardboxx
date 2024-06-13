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
 * @package   mod_cardboxx
 * @copyright 2019 RWTH Aachen (see README.md)
 * @author    Anna Heynkes
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();
define ('ENABLE_AUTOCORRECT', 0);
define ('DISABLE_AUTOCORRECT', 1);



require_once($CFG->dirroot.'/mod/cardboxx/locallib.php');

if (!isset($action)) {
    $action = required_param('action', PARAM_ALPHA);
}

/* *********************************************** Add a new flashcard *********************************************** */

if ($action === 'addflashcard') {

    require_capability('mod/cardboxx:submitcard', $context);
    require_once('card_form.php');
    $PAGE->set_url('/mod/cardboxx/view.php', ['id' => $cm->id, 'action' => 'addflashcard']);
    $returnurl = new moodle_url('/mod/cardboxx/view.php', ['id' => $cmid, 'action' => 'practice']);
    $actionurl = new moodle_url('/mod/cardboxx/view.php', ['id' => $cmid, 'action' => 'addflashcard']);

    $stringman = get_string_manager();
    $strings = $stringman->load_component_strings('cardboxx', 'en'); // Method gets the strings of the language files.
    $PAGE->requires->strings_for_js(array_keys($strings), 'cardboxx'); // Method to use the language-strings in javascript.
    $PAGE->requires->js(new moodle_url("/mod/cardboxx/js/addcard.js?ver=00001"));
    $params = [$cmid, 1, null]; // True means: the user checks their own results.
    $PAGE->requires->js_init_call('addCard', $params, true);

    // Contextual data to pass on to the card form.
    if (empty($entry)) {
        $entry = new stdClass();
        $entry->id = $cmid;
        $entry->course = $cm->course;
        $entry->action = $action;
    }

    $options = ['subdirs' => 0, 'maxbytes' => 0, 'areamaxbytes' => 10485760, 'maxfiles' => 3,
                          'accepted_types' => ['bmp', 'gif', 'jpeg', 'jpg', 'png', 'svg'], 'return_types' => 1 | 2];
    $component = 'mod_cardboxx';
    $filearea = 'content';

    $customdata = ['cardboxxid' => $cardboxx->id, 'cmid' => $cmid, 'allowautocorrection' => $cardboxx->autocorrection];
    $mform = new mod_cardboxx_card_form(null, $customdata);
    $mform->set_data($entry);

    if ($mform->is_cancelled()) {

        if (has_capability('mod/cardboxx:practice', $context)) { // For students and other participants.
            $action = 'practice';

        } else if (has_capability('mod/cardboxx:approvecard', $context)) {
            $action = 'review';

        } else { // For guests.
            redirect($actionurl, '');
        }

        // If submitted: get files from filemanager.
    } else if ($formdata = $mform->get_data()) {

        if (!empty($formdata->submitbutton)) {
            $submitbutton = $formdata->submitbutton;
        } else {
            $submitbutton = null;
        }

        /*
        // Create or select a topic for the card.
        switch ($formdata->topic) {
            case -1: // Card belongs to no topic.
                $topicid = null;
                break;
            case 0: // Card belongs to a new topic that is to be created.
                if (!empty($formdata->newtopic)) {
                    $topicid = cardboxx_save_new_topic($formdata->newtopic, $cardboxx->id);
                } else {
                    $topicid = null;
                }
                break;
            default: // Card belongs to an already existing topic.
                $topicid = $formdata->topic;
        }
        */

        $necessaryanswerslocked = $DB->get_field('cardboxx', 'necessaryanswerslocked',
                                        ['id' => $customdata['cardboxxid']], IGNORE_MISSING);
        if (!empty($formdata->answers)) {
            $necessaryanswers = $formdata->answers;
        } else if ($necessaryanswerslocked === "1") {
            $necessaryanswers = $DB->get_field('cardboxx', 'necessaryanswers',
                                       ['id' => $customdata['cardboxxid']], IGNORE_MISSING);
        } else {
            $necessaryanswers = cardboxx_EVALUATE_ALL;
        }
        if (isset($formdata->disableautocorrect)) {
            if ($formdata->disableautocorrect == DISABLE_AUTOCORRECT) {
                $disableautocorrect = true;
            } else {
                $disableautocorrect = false;
            }
        } else {
            $disableautocorrect = false;
        }
        // Create a new entry in cardboxx_cards table.
        $cardid = cardboxx_save_new_card($cardboxx->id, $context, $submitbutton, $necessaryanswers, $disableautocorrect);

        // Save the question text if there is any.
        if (!empty($formdata->question['text'])) {
            cardboxx_save_new_cardcontent($cardid, 0, cardboxx_CONTENTTYPE_TEXT, $formdata->question['text'], CARD_MAIN_INFORMATION);
        }
        // Save the text of the answer/s.

        for ($i = 1; $i <= 10; $i++) {
            $answer = 'answer'. $i;
            $answertext = $formdata->{$answer}['text'];
            if ($answertext != "") {
                $answertext = str_replace("&nbsp;", " ", $answertext);
                cardboxx_save_new_cardcontent($cardid, 1, cardboxx_CONTENTTYPE_TEXT, $answertext, CARD_MAIN_INFORMATION);
            }
        }

        // Save the questioncontext text if there is any.
        if (!empty($formdata->questioncontext['text'])) {
            cardboxx_save_new_cardcontent($cardid, 0, cardboxx_CONTENTTYPE_TEXT,
                                         $formdata->questioncontext['text'], CARD_CONTEXT_INFORMATION);
        }

        // Save the questioncontext text if there is any.
        if (!empty($formdata->answercontext['text'])) {
            cardboxx_save_new_cardcontent($cardid, 1, cardboxx_CONTENTTYPE_TEXT,
                                         $formdata->answercontext['text'], CARD_CONTEXT_INFORMATION);
        }

        // Get the draft itemid (Files in the drag-and-drop area are automatically saved as drafts in mdl_files even
        // before the form is submitted).
        $draftitemid = file_get_submitted_draft_itemid('cardimage');

        // Copy all the files from the 'real' area, into the draft area.
        file_prepare_draft_area($draftitemid, $context->id, $component, $filearea, 0, ['subdirs' => true]);

        // Save the file.
        if ($draftitemid != null) {
            $fs = get_file_storage();
            $usercontext = context_user::instance($USER->id);
            if ($files = $fs->get_area_files($usercontext->id, 'user', 'draft', $draftitemid, 'sortorder, id', false)) {
                foreach ($files as $file) {
                    // Save a reference to the image data in cardboxx_cardcontents.
                    $itemid = cardboxx_save_new_cardcontent($cardid, 0, cardboxx_CONTENTTYPE_IMAGE,
                                                           $file->get_filename(), CARD_MAIN_INFORMATION);
                    // Save the actual image data in moodle.
                    file_save_draft_area_files($draftitemid, $context->id, $component, $filearea, $itemid, $options);
                    break;
                }
            }
            // Save the imagedescription if there is any.
            if (!empty($formdata->imagedescription)) {
                cardboxx_save_new_cardcontent($cardid, 0, cardboxx_CONTENTTYPE_IMAGE,
                                             $formdata->imagedescription, CARD_IMAGEDESCRIPTION_INFORMATION);
            }
        }
        // Get the draft itemid (Files in the drag-and-drop area are automatically saved as drafts in mdl_files even
        // before the form is submitted).
        $draftitemidaudio = file_get_submitted_draft_itemid('cardsound');

        // Copy all the audio files from the 'real' area, into the draft area.
        file_prepare_draft_area($draftitemidaudio, $context->id, $component, $filearea, 0, ['subdirs' => true]);

        // Save the audio file.
        if ($draftitemidaudio != null) {
            $fs = get_file_storage();
            $usercontext = context_user::instance($USER->id);
            if ($files = $fs->get_area_files($usercontext->id, 'user', 'draft', $draftitemidaudio, 'sortorder, id', false)) {
                foreach ($files as $file) {
                    // Save a reference to the image data in cardboxx_cardcontents.
                    $itemidaudio = cardboxx_save_new_cardcontent($cardid, 0, cardboxx_CONTENTTYPE_AUDIO,
                                                                $file->get_filename(), CARD_MAIN_INFORMATION);
                    // Save the actual image data in moodle.
                    file_save_draft_area_files($draftitemidaudio, $context->id, $component, $filearea, $itemidaudio, $options);
                    break;
                }
            }
        }
        // Get the draft itemid.
        // (Files in the drag-and-drop area are automatically saved as drafts in mdl_files even before the form is submitted).
        $draftitemid3 = file_get_submitted_draft_itemid('answerimage');

        // Copy all the files from the 'real' area, into the draft area.
        file_prepare_draft_area($draftitemid3, $context->id, $component, $filearea, 0, ['subdirs' => true]);

        // Save the file.
        if ($draftitemid3 != null) {
            $fs = get_file_storage();
            $usercontext = context_user::instance($USER->id);
            if ($files = $fs->get_area_files($usercontext->id, 'user', 'draft', $draftitemid3, 'sortorder, id', false)) {
                foreach ($files as $file) {
                    // Save a reference to the image data in cardboxx_cardcontents.
                    $itemid3 = cardboxx_save_new_cardcontent($cardid, 1, cardboxx_CONTENTTYPE_IMAGE,
                                                            $file->get_filename(), CARD_MAIN_INFORMATION);
                    // Save the actual image data in moodle.
                    file_save_draft_area_files($draftitemid3, $context->id, $component, $filearea, $itemid3, $options);
                    break;
                }
            }
        }

        // Get the draft itemid.
        // (Files in the drag-and-drop area are automatically saved as drafts in mdl_files even before the form is submitted).
        $draftitemid4 = file_get_submitted_draft_itemid('answersound');
        // Copy all the audio files from the 'real' area, into the draft area.
        file_prepare_draft_area($draftitemid2, $context->id, $component, $filearea, 0, ['subdirs' => true]);
        // Save the audio file.
        if ($draftitemid2 != null) {
            $fs = get_file_storage();
            $usercontext = context_user::instance($USER->id);
            if ($files = $fs->get_area_files($usercontext->id, 'user', 'draft', $draftitemid4, 'sortorder, id', false)) {
                foreach ($files as $file) {
                    // Save a reference to the image data in cardboxx_cardcontents.
                    $itemid4 = cardboxx_save_new_cardcontent($cardid, 1, cardboxx_CONTENTTYPE_AUDIO,
                                                            $file->get_filename(), CARD_MAIN_INFORMATION);
                    // Save the actual image data in moodle.
                    file_save_draft_area_files($draftitemid4, $context->id, $component, $filearea, $itemid4, $options);
                    break;
                }
            }
        }

        if (!empty($submitbutton) && $submitbutton == get_string('saveandaccept', 'cardboxx') &&
            has_capability('mod/cardboxx:approvecard', $context)) {
            $message = get_string('success:addandapprovenewcard', 'cardboxx');
        } else {
            $message = get_string('success:addnewcard', 'cardboxx');
        }

        redirect($actionurl, $message, null, \core\output\notification::NOTIFY_INFO);
        // TODO MDL-2 : check for errors, validate form.

    } else {

        $PAGE->set_url('/mod/cardboxx/view.php', ['id' => $cm->id, 'action' => 'addflashcard']);
        echo $OUTPUT->header(); // Display course name, navigation bar at the very top and "Dashboard->...->..." bar.

        if ($mform->is_submitted() && empty($mform->is_validated())) {
            $info = get_string('error:createcard', 'cardboxx');
            echo "<span class='notification alert alert-danger alert-block fade in' role='alert' style='display:block'>"
                . $info . "</span>";
        }
        echo $OUTPUT->heading(format_string($cardboxx->name));
        echo $myrenderer->cardboxx_render_tabs($taburl, $context, $action);
        $mform->display();
    }

}

/* ************************************************ Edit a flashcard ************************************************* */

if ($action === 'editcard') {

    require_capability('mod/cardboxx:approvecard', $context);
    require_capability('mod/cardboxx:submitcard', $context);
    require_once('card_form.php');

    $cardid = required_param('cardid', PARAM_INT);
    $nextcardid = optional_param('next', 0, PARAM_INT);

    $from = optional_param('from', 'review', PARAM_ALPHA);

    if ($from === 'review') {
        $returnurl = new moodle_url('/mod/cardboxx/view.php', ['id' => $cmid, 'action' => 'review']);
    } else {
        $returnurl = new moodle_url('/mod/cardboxx/view.php', ['id' => $cmid, 'action' => 'overview']);
    }

    $actionurl = $returnurl;

    $draftitemid = file_get_submitted_draft_itemid('cardimage'); // Name of the filemanager element.
    $itemid = $DB->get_field('cardboxx_cardcontents', 'id', ['card' => $cardid, 'contenttype' =>
        cardboxx_CONTENTTYPE_IMAGE], IGNORE_MISSING);

    $draftitemid2 = file_get_submitted_draft_itemid('cardsound'); // Name of the filemanager element.
    $itemid2 = $DB->get_field('cardboxx_cardcontents', 'id', ['card' => $cardid, 'contenttype' =>
        cardboxx_CONTENTTYPE_AUDIO], IGNORE_MISSING);

    $answers = [];
    $topic = cardboxx_get_topic($cardid);
    $answers = cardboxx_get_answers($cardid);
    $answersnotapproved = cardboxx_get_notapproved_answers($cardid);
    $answers = array_merge($answers, $answersnotapproved);
    $answercount = count($answers);
    $necessaryanswers = cardboxx_get_necessaryanswers($cardid);
    $disableautocorrect = $DB->get_field('cardboxx_cards', 'disableautocorrect', ['id' => $cardid], IGNORE_MISSING);

    $customdata = ['topic' => $topic, 'answercount' => $answercount, 'cardboxxid' => $cardboxx->id, 'cmid' => $cmid,
    'answers' => $necessaryanswers, 'cardid' => $cardid, 'from' => $from, 'allowautocorrection' => $disableautocorrect];
    $mform = new mod_cardboxx_card_form($actionurl, $customdata);

    $options = ['subdirs' => 0, 'maxbytes' => 0, 'areamaxbytes' => 10485760, 'maxfiles' => 3,
                          'accepted_types' => ['bmp', 'gif', 'jpeg', 'jpg', 'png', 'svg'],
                          'return_types' => FILE_INTERNAL | FILE_EXTERNAL];
    $component = 'mod_cardboxx';
    $filearea = 'content';
    // Copy the picture file (if there is on) from the 'real' area into the draft area.
    if (!empty($itemid)) {
        file_prepare_draft_area($draftitemid, $context->id, $component, $filearea, $itemid, $options);
    }

    // Copy the audio file (if there is on) from the 'real' area into the draft area.
    if (!empty($itemid2)) {
        file_prepare_draft_area($draftitemid2, $context->id, $component, $filearea, $itemid2, $options);
    }

    // Pass the data of this card to the card_form for editing.
    if (empty($entry)) {
        $entry = new stdClass();
        $entry->id = $cmid;
        $entry->course = $cm->course;
        $entry->cardid = $cardid;
        $entry->question['text'] = cardboxx_get_questiontext($cardid);
        $entry->question['format'] = '1';
        $entry->questioncontext['text'] = cardboxx_get_questioncontext($cardid);
        $entry->answercontext['text'] = cardboxx_get_answercontext($cardid);
        for ($i = 1; $i <= $answercount; $i++) {
            $answer = 'answer' . $i;
            $entry->{$answer}['text'] = $answers[($i - 1)];
            $entry->{$answer}['format'] = '1';
            $entry->from = $from;
        }
        $entry->cardimage = $draftitemid;
        $entry->imagedescription = cardboxx_get_imagedescription($cardid);
        $entry->cardsound = $draftitemid2;
        $entry->action = 'editcard';
        $entry->next = $nextcardid;
        $entry->disableautocorrect = $disableautocorrect;
    }
    $mform->set_data($entry);

    if ($mform->is_cancelled()) {

        if ($from === "review") {
            $action = 'review';
        } else {
            $action = 'overview';
        }
    } else if ($formdata = $mform->get_data()) {
        // If submitted: get files from filemanager.
        if (!empty($formdata->submitbutton)) {
            $submitbutton = $formdata->submitbutton;
        } else {
            $submitbutton = null;
        }

        /*
        // Create or select a topic for the card.
        switch ($formdata->topic) {
            case -1: // Card belongs to no topic.
                $topicid = null;
                break;
            case 0: // Card belongs to a new topic that is to be created.
                if (!empty($formdata->newtopic)) {
                    $topicid = cardboxx_save_new_topic($formdata->newtopic, $cardboxx->id);
                } else {
                    $topicid = null;
                }
                break;
            default: // Card belongs to an already existing topic.
                $topicid = $formdata->topic;
        }
        */
        $necessaryanswerslocked = $DB->get_field('cardboxx', 'necessaryanswerslocked', ['id' => $customdata['cardboxxid']],
            IGNORE_MISSING);
        if (!empty($formdata->answers)) {
            $necessaryanswers = $formdata->answers;
        } else if ($necessaryanswerslocked === "1") {
            $necessaryanswers = $DB->get_field('cardboxx', 'necessaryanswers', ['id' => $customdata['cardboxxid']],
                IGNORE_MISSING);
        } else {
            $necessaryanswers = cardboxx_EVALUATE_ALL;
        }
        if (isset($formdata->disableautocorrect)) {
            if ($formdata->disableautocorrect == DISABLE_AUTOCORRECT) {
                $disableautocorrect = true;
            } else {
                $disableautocorrect = false;
            }
        } else {
            $disableautocorrect = false;
        }

        // Update the entry in cardboxx_cards table and delete the original content items.
        $success = cardboxx_edit_card($cardid, $topicid, $context, $necessaryanswers, $disableautocorrect, $submitbutton);

        // TODO MDL-3 : Fehlerbehandlung.

        // Save the question text if there is any.
        if (!empty($formdata->question)) {
            cardboxx_save_new_cardcontent($cardid, 0, cardboxx_CONTENTTYPE_TEXT, $formdata->question['text'], CARD_MAIN_INFORMATION);
        }
        // Save the text of the answer/s.
        for ($i = 1; $i <= 10; $i++) {
            $answer = 'answer'. $i;
            $answertext = $formdata->{$answer}['text'];
            if ($answertext != "") {
                $answertext = str_replace("&nbsp;", " ", $answertext);
                cardboxx_save_new_cardcontent($cardid, 1, cardboxx_CONTENTTYPE_TEXT, $answertext, CARD_MAIN_INFORMATION);
            }
        }

        // Save the questioncontext text if there is any.
        if (!empty($formdata->questioncontext['text'])) {
            cardboxx_save_new_cardcontent($cardid, 0, cardboxx_CONTENTTYPE_TEXT,
                                         $formdata->questioncontext['text'], CARD_CONTEXT_INFORMATION);
        }
        // Save the answercontext text of the answer/s.
        if (!empty($formdata->answercontext['text'])) {
            cardboxx_save_new_cardcontent($cardid, 1, cardboxx_CONTENTTYPE_TEXT,
                                         $formdata->answercontext['text'], CARD_CONTEXT_INFORMATION);
        }

        // Get the draft itemid (Files in the drag-and-drop area are automatically saved as drafts in mdl_files
        // even before the form is submitted).
        $draftitemid = file_get_submitted_draft_itemid('cardimage');

        // Copy all the files from the 'real' area, into the draft area.
        file_prepare_draft_area($draftitemid, $context->id, $component, $filearea, 0, ['subdirs' => true]);
        // Save the file.
        if ($draftitemid != null) {
            $fs = get_file_storage();
            $usercontext = context_user::instance($USER->id);
            if ($files = $fs->get_area_files($usercontext->id, 'user', 'draft', $draftitemid, 'sortorder, id', false)) {
                foreach ($files as $file) {
                    // Save a reference to the image data in cardboxx_cardcontents.
                    $itemid = cardboxx_save_new_cardcontent($cardid, 0, cardboxx_CONTENTTYPE_IMAGE,
                                                           $file->get_filename(), CARD_MAIN_INFORMATION);
                    // Save the actual image data in moodle.
                    file_save_draft_area_files($draftitemid, $context->id, $component, $filearea, $itemid, $options);
                    break;
                }
            }
            // Save the imagedescription if there is any.
            if (!empty($formdata->imagedescription)) {
                cardboxx_save_new_cardcontent($cardid, 0, cardboxx_CONTENTTYPE_IMAGE,
                                             $formdata->imagedescription, CARD_IMAGEDESCRIPTION_INFORMATION);
            }
        }

        // Get the draft itemid.
        // (Files in the drag-and-drop area are automatically saved as drafts in mdl_files even before the form is submitted).
        $draftitemid2 = file_get_submitted_draft_itemid('cardsound');
        // Copy all the audio files from the 'real' area, into the draft area.
        file_prepare_draft_area($draftitemid2, $context->id, $component, $filearea, 0, ['subdirs' => true]);
        // Save the audio file.
        if ($draftitemid2 != null) {
            $fs = get_file_storage();
            $usercontext = context_user::instance($USER->id);
            if ($files = $fs->get_area_files($usercontext->id, 'user', 'draft', $draftitemid2, 'sortorder, id', false)) {
                foreach ($files as $file) {
                    // Save a reference to the image data in cardboxx_cardcontents.
                    $itemid2 = cardboxx_save_new_cardcontent($cardid, 0, cardboxx_CONTENTTYPE_AUDIO,
                                                            $file->get_filename(), CARD_MAIN_INFORMATION);
                    // Save the actual image data in moodle.
                    file_save_draft_area_files($draftitemid2, $context->id, $component, $filearea, $itemid2, $options);
                    break;
                }
            }
        }
        // If the card had already been approved and has possibly been practiced.
        if ($from === 'overview' && $cardboxx->enablenotifications) {
            cardboxx_send_change_notification($cmid, $cardboxx, $cardid);
        }

        if (!empty($nextcardid) && $submitbutton == get_string('saveandaccept', 'cardboxx') &&
            has_capability('mod/cardboxx:approvecard', $context)) {
            $cardid = $nextcardid;
        }

        $action = $from;

    } else {

        $PAGE->set_url('/mod/cardboxx/view.php', ['id' => $cm->id, 'action' => 'editcard', 'from' => $from]);
        echo $OUTPUT->header(); // Display course name, navigation bar at the very top and "Dashboard->...->..." bar.

        if ($mform->is_submitted() && empty($mform->is_validated())) {
            $info = get_string('error:createcard', 'cardboxx');
            echo "<span class='notification alert alert-danger alert-block fade in' role='alert' style='display:block'>"
                . $info . "</span>";
        }

        // Javacript information.

        $data = [];
        $data['showquescontext'] = ($entry->questioncontext['text'] != "");
        $data['showanscontext'] = ($entry->answercontext['text'] != "");
        $data['showquesimage'] = ($entry->cardimage != 0);
        $data['showquessound'] = ($entry->cardsound != 0);

        $stringman = get_string_manager();
        $strings = $stringman->load_component_strings('cardboxx', 'en'); // Method gets the strings of the language files.
        $PAGE->requires->strings_for_js(array_keys($strings), 'cardboxx'); // Method to use the language-strings in javascript.
        $PAGE->requires->js(new moodle_url("/mod/cardboxx/js/addcard.js?ver=00001"));
        $params = [$cmid, $answercount, $data]; // True means: the user checks their own results.
        $PAGE->requires->js_init_call('addCard', $params, true);

        echo $OUTPUT->heading(get_string('titleforcardedit', 'cardboxx'));

        if ($from === "overview") {
            echo $myrenderer->cardboxx_render_tabs($taburl, $context, 'overview');
        } else {
            echo $myrenderer->cardboxx_render_tabs($taburl, $context, 'review');
        }

        $mform->display();

    }

}

/* **************************************************** Delete cards **************************************************** */

if ($action === 'deletecard') {

    require_capability('mod/cardboxx:deletecard', $context);

    $cardid = required_param('cardid', PARAM_INT);
    if ($DB->record_exists('cardboxx_cards', ['id' => $cardid])) {
        $DB->delete_records('cardboxx_cards', ['id' => $cardid]);
        $DB->delete_records('cardboxx_cardcontents', ['card' => $cardid]);
        $DB->delete_records('cardboxx_progress', ['card' => $cardid]);
    }
    $action = 'overview';

}
/* ******************************************** Delete cards from review ******************************************** */
if ($action === 'rejectcard') {

    require_capability('mod/cardboxx:approvecard', $context);

    $cardids = required_param('cardid', PARAM_TEXT);
    foreach ((explode(",", $cardids)) as $cardid) {
        if (strpos($cardid, "-") != false) {
            $pos = strpos($cardid, "-");
            $content = substr($cardid, ($pos + 1));
            $idcard = substr($cardid, 0, $pos);
            $sugg = $DB->get_records_select('cardboxx_cardcontents', 'card = :id AND cardside = :cardside AND area = 3' ,
            ['id' => $idcard, 'cardside' => cardboxx_CARDSIDE_ANSWER], '', 'id, content');
            foreach ($sugg as $sug) {
                if (strip_tags(str_replace(" ", "", $sug->content)) === $content) {
                    $id = $sug->id;
                }
            }
            $DB->delete_records('cardboxx_cardcontents', ['card' => $idcard, 'area' => 3, 'id' => $id]);
        } else if ($DB->record_exists('cardboxx_cards', ['id' => $cardid])) {
            $DB->delete_records('cardboxx_cards', ['id' => $cardid]);
            $DB->delete_records('cardboxx_cardcontents', ['card' => $cardid]);
        }
    }
    $action = 'review';

}

/* **************************************************** Practice cards **************************************************** */

if ($action === 'practice') {

    require_once('model/cardboxx.class.php');
    require_once('model/card_selection_algorithm.php');
    require_once('model/card_sorting_algorithm.php');

    $PAGE->set_url('/mod/cardboxx/view.php', ['id' => $cm->id, 'action' => 'practice']);
    echo $OUTPUT->header();
    echo $OUTPUT->heading(format_string($cardboxx->name));
    echo $myrenderer->cardboxx_render_tabs($taburl, $context, $action);

    $renderer = $PAGE->get_renderer('mod_cardboxx');

    $startnow = optional_param('start', false, PARAM_BOOL);
    $correction = optional_param('mode', 0, PARAM_INT); // Automatic check against solution (default) or self check.
    $topic = optional_param('topic', null, PARAM_INT); // Topic to prioritize.
    $onlyonetopic = optional_param('onlyonetopic', -1, PARAM_INT); // Topic to study.
    $practiceall = optional_param('practiceall', true, PARAM_BOOL);
    $amountcards = optional_param('amountcards', 0, PARAM_INT); // Topic to prioritize.
    $openmodal = true;

    // 1. Create a virtual cardboxx for this practice session, i.e. create the model.
    $select = new cardboxx_card_selection_algorithm($topic, $practiceall, $onlyonetopic);
    $sort = new cardboxx_card_sorting_algorithm();
    $cardboxxmodel = new cardboxx_cardboxxmodel($cardboxx->id, $select, $sort, $onlyonetopic);

    $cardcount = $cardboxxmodel->cardboxx_count_cards();
    $duecardcount = $cardboxxmodel->cardboxx_count_due_cards();

    $selection = $cardboxxmodel->cardboxx_get_card_selection($amountcards);
    $totalcards = is_array($selection) ? count($selection) : 0;

    // Inform the user that their cardboxx is empty.
    if (empty($cardcount)) {

        $info = get_string('info:nocardsavailable', 'cardboxx');
        $help = $OUTPUT->help_icon('help:nocardsavailable', 'cardboxx');
        echo "<span class='notification alert alert-info alert-block fade in' role='alert' style='display:block'>"
            . $info . " " . $help . "</span>";
        return;
    } else if ($cardcount == $cardboxxmodel->cardboxx_count_mastered_cards()) {
        // Inform the user that all of their cards have the status 'mastered' and are no longer repeated.
        $info = get_string('info:nocardsavailableforpractice', 'cardboxx');
        $help = $OUTPUT->help_icon('help:nocardsavailableforpractice', 'cardboxx');
        echo "<span class='notification alert alert-info alert-block fade in' role='alert' style='display:block'>"
            . $info . " " . $help . "</span>";
        return;
    } else if (empty($duecardcount) && !$startnow) {
        // Inform the user that none of their cards are due for practice right now.
        /* $infopart1 = get_string('info:nocardsdueforpractice', 'cardboxx');
        $infopart2 = get_string('help:practiceanyway', 'cardboxx');
        $help = $OUTPUT->help_icon('help:nocardsdueforpractice', 'cardboxx');
        echo "<span id='nocardsduenotification' class='notification alert alert-info alert-block fade in'
                    role='alert' style='display:block'>" .
             $infopart1 . " " . $help . "<br>" . $infopart2 . "</span>"; */
        $openmodal = false;
    }

    if ($startnow && !( empty($duecardcount) &&  $practiceall == false)) {

        require_once($CFG->dirroot . '/mod/cardboxx/classes/output/practice.php');

        $selection = $cardboxxmodel->cardboxx_get_card_selection($amountcards);


        $autocorrectval = [];
        foreach ($selection as $card) {
            $acvalue = $DB->get_record('cardboxx_cards', ['id' => $card]);
            array_push($autocorrectval, $card.'_'.$acvalue->disableautocorrect);
        }
        // 2. Create a view controller.
        if ($correction % 2 == 0) {
            $case = 2; // Automatic Check.
        } else {
            $case = 1;
        }
        /*$cardstatus = $DB->get_record('cardboxx_cards', array('id' => $selection[0]));
        if ($cardstatus->disableautocorrect) {
            $case = 1;
        }*/
        $practice = new cardboxx_practice($case, $context, $selection[0], count($selection), !$correction);
        $practice->totalcards = $totalcards;
        $_SESSION['totalcards'] = $totalcards; // Store totalcards in session
        $data = $practice->export_for_template($renderer);

        // 3. Give javascript access to the language string repository and to the relevant model data and add it to the page.
        $stringman = get_string_manager();
        $strings = $stringman->load_component_strings('cardboxx', 'en'); // Method gets the strings of the language files.
        $PAGE->requires->strings_for_js(array_keys($strings), 'cardboxx'); // Method to use the language-strings in javascript.
        $PAGE->requires->js(new moodle_url("/mod/cardboxx/js/practice.js?ver=00024"));
        $params = [$cmid, $selection, $case, $data, $correction, $autocorrectval]; // True means: the user checks own results.
        $PAGE->requires->js_init_call('startPractice', $params, true);

        // 3. Render the page.
        echo $renderer->cardboxx_render_practice($practice);

    } else { // Render a modal dialogue that asks the user to select their practice preferences.

        require_once($CFG->dirroot . '/mod/cardboxx/classes/output/start.php');

        $PAGE->requires->js(new moodle_url("/mod/cardboxx/js/start.js?ver=00005"));
        $PAGE->requires->js_init_call('startOptions', [$cmid, $openmodal], true);

        $start = new cardboxx_start($cardboxx->autocorrection, $cardboxx->id);
        $start->cardcount = $cardcount;
        $start->duecardcount = $duecardcount;

        echo $renderer->cardboxx_render_practice_start($start);

    }

}

/* **************************************************** View progress **************************************************** */

if ($action === 'statistics') {

    require_once('model/cardboxx.class.php');
    require_once('model/card_selection_algorithm.php');
    require_once($CFG->dirroot . '/mod/cardboxx/classes/output/statistics.php');

    $PAGE->set_url('/mod/cardboxx/view.php', ['id' => $cm->id, 'action' => 'statistics']);
    echo $OUTPUT->header();
    echo $OUTPUT->heading(format_string($cardboxx->name));
    echo $myrenderer->cardboxx_render_tabs($taburl, $context, $action);


    $renderer = $PAGE->get_renderer('mod_cardboxx');
    $params = [];
    $params['ismanager'] = $ismanager = has_capability('mod/cardboxx:approvecard', $context);
    if ($ismanager) {
        $params['absoluteboxcount'] = cardboxx_get_absolute_cardcounts_per_deck($cardboxx->id);
    } else {
        $info = get_string('info:statisticspage', 'cardboxx');
        $help = $OUTPUT->help_icon('help:whenarecardsdue', 'cardboxx');
        echo "<span id='nocardsduenotification' class='notification alert alert-info alert-block fade in'
                role='alert' style='display:block'>" .
             $info . " " . $help . "</span>";
        // 1. Create a virtual cardboxx for this user, i.e. create the model.
        $select = new cardboxx_card_selection_algorithm(null, true);
        $cardboxxmodel = new cardboxx_cardboxxmodel($cardboxx->id, $select);
        $params['studentboxcount'] = $cardboxxmodel->cardboxx_get_status();
        if (cardboxx_statistics::is_enrolled_students_threshold_reached($cardboxx->id)) {
            $params['averageboxcount'] = cardboxx_get_average_cardcounts_per_deck($cardboxx->id);
        }
    }
    // 2. Create a view controller.
    $statistics = new cardboxx_statistics($cardboxx->id, $ismanager); // XXX auch hier das cardboxxmodel nutzen.
    $params['performance'] = $statistics->export_for_template($renderer);
    // 3. Give javascript access to the language string repository and to the relevant model data and add it to the page.
    $stringman = get_string_manager();
    $strings = $stringman->load_component_strings('cardboxx', 'en'); // Method gets the strings of the language files.
    $PAGE->requires->strings_for_js(array_keys($strings), 'cardboxx'); // Method to use the language-strings in javascript.
    $PAGE->requires->js(new moodle_url("/mod/cardboxx/js/statistics.js?ver=00010"));
    $PAGE->requires->js_init_call('displayCharts', [$params], true);

    // 4. Render the page.
    echo $renderer->cardboxx_render_statistics($statistics);

}
/* **************************************************** Bulk Import Cards ****************************************************** */

if ($action === 'massimport') {
    $returnurl = new moodle_url('/mod/cardboxx/view.php', ['id' => $cmid, 'action' => 'massimport']);

    $step = optional_param('step', 1, PARAM_INT);
    if (!empty($cancelclicked)) {
        echo "<span id='cardboxx-review-notification' class='notification'>
                <div class='alert alert-info alert-block fade in' role='alert'>" . $cancelclicked. "</div></span>";
    }
    if ($step == 1) {
        // Data provision.
        $customdata = ['cardboxxid' => $cardboxx->id, 'cmid' => $cmid, 'context' => $context];
        $mform = new \mod_cardboxx\output\massimport_form(null, $customdata);
        if ($formdata = $mform->get_data()) {

            // Store csv content in moodledata/temp for next step.
            $iid = csv_import_reader::get_new_iid('cardboxx');
            $csvcontent = $mform->get_file_content('cardimportfile'); // Full content of the file.
            $cir = new csv_import_reader($iid, 'cardboxx');
            $readcount = $cir->load_csv_content($csvcontent, $formdata->encoding, $formdata->delimiter_name);
            $csvloaderror = $cir->get_error();
            if (!is_null($csvloaderror)) {
                throw new moodle_exception('csvloaderror', '', $returnurl, $csvloaderror);
            }
            if ($readcount > 1) {
                // Show csv content preview.
                $PAGE->set_url('/mod/cardboxx/view.php', ['id' => $cmid, 'action' => 'massimport']);
                echo $OUTPUT->header();
                echo $OUTPUT->heading(format_string($cardboxx->name));
                echo $myrenderer->cardboxx_render_tabs($taburl, $context, $action);
                echo "<h2>". get_string('importpreview', 'cardboxx')."</h2>";
                $csvcolumns = $cir->get_columns();
                $errorflag = 0; // No error.
                $columnexceptions = cardboxx_import_validate_columns($csvcolumns, LONG_DESCRIPTION);
                if (!empty($columnexceptions[0])) {
                    echo '<div class="alert alert-danger" role="alert">Error(s)<ul>';
                    foreach ($columnexceptions[0] as $error) {
                        echo '<li>'.$error.'</li>';
                    }
                    echo '</ul></div>';
                }
                if (!empty($columnexceptions[1])) {
                    echo '<div class="alert alert-warning" role="alert">Warning(s)<ul>';
                    foreach ($columnexceptions[1] as $warning) {
                        echo '<li>'.$warning.'</li>';
                    }
                    echo '</ul></div>';
                }
                if (!empty($columnexceptions[0])) {
                    $errorflag = 1;
                }
                $importpreviewtable = new \mod_cardboxx\output\previewtable($cir, $csvcolumns);
                echo html_writer::tag('div', html_writer::table($importpreviewtable), ['class' => 'flexible-wrap']);
                $customdata = ['id' => $cmid, 'cardboxxid' => $cardboxx->id, 'context' => $context, 'iid' => $iid,
                'step' => 2, 'count' => $readcount, 'error' => $errorflag];
                $mform2 = new \mod_cardboxx\output\massimportpreview_form(null, $customdata);
                $mform2->display();
            } else {
                redirect($returnurl, get_string('emptyimportfile', 'cardboxx'), null, \core\output\notification::NOTIFY_INFO);
            }
        } else {
            $PAGE->set_url('/mod/cardboxx/view.php', ['id' => $cm->id, 'action' => 'massimport']);
            echo $OUTPUT->header();
            echo $OUTPUT->heading(format_string($cardboxx->name));
            echo $myrenderer->cardboxx_render_tabs($taburl, $context, $action);
            $mform->display();
        }
    } else if ($step == 2) {
        // Processing.
        $iid = required_param('iid', PARAM_INT);
        $mform2 = new \mod_cardboxx\output\massimportpreview_form(null, []);
        if ($formdata2 = $mform2->get_data()) {
            $btn = preg_grep('/btn/', array_keys(get_object_vars($formdata2)));
            $btnfunc = substr(array_values($btn)[0], 0, -3);
            if (($btnfunc) == 'import') {
                $cir = new csv_import_reader($iid, 'cardboxx');
                $cir->init();
                $PAGE->set_url('/mod/cardboxx/view.php', ['id' => $cm->id, 'action' => 'massimport']);
                echo $OUTPUT->header();
                echo $OUTPUT->heading(format_string($cardboxx->name));
                echo $myrenderer->cardboxx_render_tabs($taburl, $context, $action);
                $errlines = cardboxx_import_cards($cir, $cir->get_columns(), $cardboxx->id);
                $cir->close();
                $cir->cleanup();
                if (!empty($errlines)) {
                    $errorlines = [];
                    $errorlines['err'] = true;
                    $errorlines['rows'] = $errlines;
                    $errorlines['successfullyimported'] = ($formdata2->count) - (1 + count($errlines));
                    $errorlines['continueurl'] = $returnurl->out(false);
                    $renderer = $PAGE->get_renderer('mod_cardboxx');
                    echo $renderer->cardboxx_render_errimport($errorlines);
                } else {
                    $errorlines['err'] = false;
                    $errorlines['successfullyimported'] = ($formdata2->count) - (1 + count($errlines));
                    $errorlines['continueurl'] = $returnurl->out(false);
                    $renderer = $PAGE->get_renderer('mod_cardboxx');
                    echo $renderer->cardboxx_render_errimport($errorlines);
                }
            } else {
                redirect($returnurl, get_string('cancelimport', 'cardboxx'), null, \core\output\notification::NOTIFY_INFO);
            }
        }
    }
}

/* **************************************************** Review **************************************************** */

if ($action === 'review') {
    require_once('review_form.php');
    $PAGE->set_url('/mod/cardboxx/view.php', ['id' => $cm->id, 'action' => 'review']);
    echo $OUTPUT->header();
    echo $OUTPUT->heading(format_string($cardboxx->name));
    echo $myrenderer->cardboxx_render_tabs($taburl, $context, $action);
    $actionurl = new moodle_url('/mod/cardboxx/view.php', ['id' => $cmid, 'action' => 'review']);

    require_once('model/cardcollection.class.php');

    $page = optional_param('page', 0, PARAM_INT);
    $perpage = 10;
    $offset = $page * $perpage;

    // Load unapproved cards.
    $collection = new cardboxx_cardcollection($cardboxx->id);
    $unapprovedcardids = $collection->cardboxx_get_card_list();
    $reviewablecardids = $unapprovedcardids;

    // Load cards that have suggested answers.
    $cards = $DB->get_records('cardboxx_cards', ['cardboxx' => $cardboxx->id]);
    if (!empty($cards)) {
        list($insql, $inparams) = $DB->get_in_or_equal(array_column($cards, 'id'), SQL_PARAMS_NAMED);
        $anssuggestions = $DB->get_records_select('cardboxx_cardcontents',
            'area = :area AND cardside = :cardside AND card ' . $insql,
            array_merge(['area' => CARD_ANSWERSUGGESTION_INFORMATION, 'cardside' => cardboxx_CARDSIDE_ANSWER], $inparams));
        foreach ($anssuggestions as $anssuggestion) {
            if (!in_array($anssuggestion->card, $reviewablecardids)) {
                array_push($reviewablecardids, $anssuggestion->card);
            }
        }
    }
    $totalcount = count($reviewablecardids);
    $reviewablecardids = array_slice($reviewablecardids, $offset, $perpage);
    $customdata = ['cardboxxid' => $cardboxx->id, 'cmid' => $cmid, 'cardlist' => $reviewablecardids, 'context' =>
        $context, 'page' => $page, 'perpage' => $perpage, 'offset' => $offset, 'totalcount' => $totalcount];
    if (empty($reviewablecardids)) {
        $info = get_string('info:nocardsavailableforreview', 'cardboxx');
        echo "<span id='cardboxx-review-notification' class='notification'>
                <div class='alert alert-info alert-block fade in' role='alert'>$info</div></span>";
        return;
    } else {
        if (empty($message)) {
            $info = get_string('titleforreview', 'cardboxx');
            echo "<span id='cardboxx-review-notification' class='notification'>
                    <div class='alert alert-info alert-block fade in' role='alert'>" . $info . "</div></span>";
        } else {
            echo "<span id='cardboxx-review-notification' class='notification'>
                    <div class='alert alert-info alert-block fade in' role='alert'>" . $message. "</div></span>";
        }
    }

    $stringman = get_string_manager();
    $strings = $stringman->load_component_strings('cardboxx', 'en');
    $PAGE->requires->strings_for_js(array_keys($strings), 'cardboxx');
    $PAGE->requires->js(new moodle_url("/mod/cardboxx/js/review.js?ver=00003"));
    $PAGE->requires->js_init_call('startReview', [$cmid], true);
    $mform = new mod_cardboxx_review_form(null, $customdata);

    if ($fromform = $mform->get_data()) {
        // Processign form data submitted.
        $filtered = [];
        $btn = preg_grep('/btn/', array_keys(get_object_vars($fromform)));
        $btnfunc = rtrim(array_values($btn)[0], 'btn');
        if (($btnfunc) == 'approve') {
            foreach ($fromform as $key => $value) {
                if (preg_match('/chck/', $key)) {
                    $filtered[] = substr($key, 4, strlen($key));
                    $id = substr($key, 4, strlen($key));
                    if (strpos($id, "-") != false) {
                        $pos = strpos($id, "-");
                        $content = substr($id, ($pos + 1));
                        $id = substr($id, 0, $pos);
                    }
                    $dataobject = new stdClass();
                    $cardapproved = cardboxx_card_approved($id);
                    if ($cardapproved) {
                        $sugg = $DB->get_records_select('cardboxx_cardcontents', 'card = :id AND cardside = 1 AND area = 3' ,
                                            ['id' => $id], '', 'id, content');
                        foreach ($sugg as $sug) {
                            if (strip_tags(str_replace(" ", "", $sug->content)) === $content) {
                                $dataobject->id = $sug->id;
                                $dataobject->content = $sug->content;
                            }
                        }
                        $dataobject->card = $id;
                        $dataobject->area = '0';
                        $success = $DB->update_record('cardboxx_cardcontents', $dataobject, false);
                    } else {
                        $dataobject->id = $id;
                        $dataobject->approved = '1';
                        $dataobject->approvedby = $USER->id;
                        $success = $DB->update_record('cardboxx_cards', $dataobject, false);
                    }
                }
            }
            redirect($actionurl, '');
        } else {
            foreach ($fromform as $key => $value) {
                if (preg_match('/chck/', $key)) {
                    $filtered[] = substr($key, 4, strlen($key));
                }
            }
            $rejectparams = [$cmid, $filtered, count($filtered)];
            $PAGE->requires->js_init_call('rejectcard', $rejectparams, true);

        }
    } else {

        $mform->display();
    }
    echo $OUTPUT->paging_bar($totalcount, $page, $perpage, $actionurl);
}

/* ******************************************* Overview of all cards ******************************************* */

if ($action === 'overview') {

    $topic = optional_param('topic', -1, PARAM_INT);
    $sort = optional_param('sort', 0, PARAM_INT);
    $deck = optional_param('deck', -1, PARAM_INT);
    $page = optional_param('page', 0, PARAM_INT);
    $perpage = 10;
    $offset = $page * $perpage;
    $PAGE->requires->js_amd_inline("require(['jquery', 'theme_boost/bootstrap/tooltip'], function($){
        $('[data-toggle=\"tooltip\"]').tooltip();
    });");
    require_once('model/cardcollection.class.php');
    require_once($CFG->dirroot . '/mod/cardboxx/classes/output/overview.php');
    require_once('classes/output/card.php');

    $PAGE->set_url('/mod/cardboxx/view.php', ['id' => $cm->id, 'action' => 'overview']);
    echo $OUTPUT->header();
    echo $OUTPUT->heading("$cardboxx->name");
    echo $myrenderer->cardboxx_render_tabs($taburl, $context, $action);

    $context = context_module::instance($cmid);

    // 1. Create the model.
    $collection = new cardboxx_cardcollection($cardboxx->id, $topic, true, $deck);
    $list = $collection->cardboxx_get_card_list();

    // Karten sortieren.
    if ($sort === 0) {
        rsort($list);
    } else if ($sort === 1) {
        sort($list);
    } else if ($sort === 2 || $sort === 3) {
        $questions = [];
        for ($i = 0; $i < count($list); $i++) {
            $questions[$list[$i]] = $collection->cardboxx_get_question($list[$i]);
        }

        if ($sort === 3) {
            asort($questions, SORT_STRING);
        } else {
            arsort($questions, SORT_STRING);
        }
        $index = 0;
        foreach ($questions as $key => $value) {
            $list[$index] = $key;
            $index++;
        }
    }

    // Filter cards deckwise.

    if ($deck != -1) {
        if (has_capability('mod/cardboxx:approvecard', $context)) {
            $allowedtoedit = true;
        } else {
            $allowedtoedit = false;
        }

        if (has_capability('mod/cardboxx:seestatus', $context)) {
            $seestatus = true;
        } else {
            $seestatus = false;
        }
        $filtereddeck = [];
        $index = 0;
        foreach ($list as $flashcard) {
            $card = new cardboxx_card($flashcard, $context, $cardboxx->id, $allowedtoedit, $seestatus);
            $card->cardboxx_getcarddeck($flashcard, $allowedtoedit);

            if (has_capability('mod/cardboxx:approvecard', $context)) {
                if ($card->cardboxx_getcarddecknumber() == ($deck + 1)) {
                    $filtereddeck[$index] = $flashcard;
                    $index++;
                }
            } else {
                if ($card->cardboxx_getcarddecknumber() == ($deck)) {
                    $filtereddeck[$index] = $flashcard;
                    $index++;
                }
            }
        }
        $list = $filtereddeck;
    }

    if (empty($list) && $deck == -1) {
        $info = get_string('info:nocardsavailableforoverview', 'cardboxx');
        echo "<span class='notification alert alert-info alert-block fade in' role='alert' style='display:block'>" .
            $info . "</span>";
        return;
    } else {
        $totalcount = count($list);
        $baseurl = new moodle_url('/mod/cardboxx/view.php', ['id' => $cmid, 'action' => 'overview',  'topic' =>
            $topic, 'sort' => $sort, 'deck' => $deck]);

        /*
        $info = get_string('intro:overview', 'cardboxx');
        echo "<span class='notification alert alert-info alert-block fade in' role='alert' style='display:block'>" .
            $info . "</span>";
        */

        // Load strings and include js.
        $stringman = get_string_manager();
        $strings = $stringman->load_component_strings('cardboxx', 'en');
        $PAGE->requires->strings_for_js(array_keys($strings), 'cardboxx');

        $PAGE->requires->js(new moodle_url("/mod/cardboxx/js/overview.js?ver=00009"));
        $PAGE->requires->js_init_call('startOverview', [$cmid, $topic, $sort, $deck]);
        // 2. Create a view controller.
        $overview = new cardboxx_overview($list, $offset, $context, $cmid, $cardboxx->id, $topic, false, $sort, $deck);

        // 4. Render the page.
        $renderer = $PAGE->get_renderer('mod_cardboxx');
        echo $renderer->cardboxx_render_overview($overview);
        echo $OUTPUT->paging_bar($totalcount, $page, $perpage, $baseurl);
    }

}

/* **************************************************** Edit topics **************************************************** */

if ($action === 'savenewtopic') {

    require_capability('mod/cardboxx:edittopics', $context);
    $returnurl = new moodle_url('/mod/cardboxx/view.php', ['id' => $cmid, 'action' => 'edittopic']);

    $newtopic = required_param('newtopic', PARAM_TEXT);
    cardboxx_save_new_topic($newtopic, $cardboxx->id);

    redirect($returnurl);

}

/* **************************************************** Edit topics **************************************************** */

if ($action === 'edittopic') {

    require_capability('mod/cardboxx:edittopics', $context);

    $page = optional_param('page', 0, PARAM_INT);
    $perpage = 10;
    $offset = $page * $perpage;

    require_once($CFG->dirroot . '/mod/cardboxx/classes/output/topics.php');
    $PAGE->set_url('/mod/cardboxx/view.php', ['id' => $cm->id, 'action' => 'edittopic']);
    echo $OUTPUT->header();
    echo $OUTPUT->heading(format_string($cardboxx->name));
    echo $myrenderer->cardboxx_render_tabs($taburl, $context, $action);
    $renderer = $PAGE->get_renderer('mod_cardboxx');

    $list = cardboxx_get_topics($cardboxx->id);

    $topics = new cardboxx_topics($list, $offset, /* $context, */ $cmid, $cardboxx->id);
    $PAGE->requires->js_call_amd('mod_cardboxx/topics', 'init', [$cmid]);

    echo $renderer->cardboxx_render_topics($topics);
}
