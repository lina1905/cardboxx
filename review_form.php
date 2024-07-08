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

defined('MOODLE_INTERNAL') || die();


require_once("$CFG->libdir/formslib.php"); // Moodleform is defined in formslib.php.
require_once($CFG->dirroot.'/mod/cardboxx/locallib.php');
/**
 * Form for adding/editing a flashcard to a cardboxx.
 */
class mod_cardboxx_review_form extends moodleform {
    /**
     * Form definition.
     *
     * @param moodle_url|null $action URL to submit the form to (optional)
     * @param mixed|null $preselected Preselected form values (optional)
     */
    public function definition($action = null, $preselected = null) {
        global $CFG, $DB, $USER, $COURSE;
        $mform = $this->_form;
        $customdata = $this->_customdata;

        // Pass contextual parameters to the form (via set_data() in controller.php).
        $mform->addElement('hidden', 'id'); // Course module id.
        $mform->setType('id', PARAM_INT);
        $mform->setDefault('id', $customdata['cmid']);

        $mform->addElement('hidden', 'action');
        $mform->setType('action', PARAM_ALPHANUM);
        $mform->setDefault('action', 'review');

        /*$mform->addElement('hidden', 'course'); // Course id.
        $mform->setType('course', PARAM_INT);
        $mform->setDefault('id', $customdata['cmid']);*/
        $mform->addElement('html', '<div id="cardboxx-review">');
        $mform->addElement('html', '<div id="container-fluid cardboxx-studyview">');
        $topicname = '';

        foreach ($customdata['cardlist'] as $cardid) {

            $cardcontents = $DB->get_records_sql(
                'SELECT mcc.id, mcc.card, mcc.cardside, mcc.contenttype, mcc.content, mcc.area, mcc2.disableautocorrect,
                    (SELECT topicname from {cardboxx_topics} where id = mcc2.topic) AS topicname
                    FROM {cardboxx_cardcontents} mcc join {cardboxx_cards} mcc2 on mcc.card = mcc2.id
                        where mcc.card = :cardid and area in (:areamain, :areasugg) order by topicname',
                [
                    'cardid' => $cardid,
                    'areamain' => CARD_MAIN_INFORMATION,
                    'areasugg' => CARD_ANSWERSUGGESTION_INFORMATION,
                ]
            );

            $question = '';
            $answer = '';
            $count = 0;
            $countsuggestedanswers = 0;
            $divadded = false;
            $necessaryanswers = cardboxx_get_necessaryanswers($cardid);
            if ($necessaryanswers === "1") {

                $howmanyanswersnecessary = '<span class="badge badge-dark" data-toggle = "tooltip" title = "'.
                                           get_string("oneanswersnecessary_help", "cardboxx").'">'.
                                           get_string("oneanswersnecessary", "cardboxx").'</span>';

            } else {
                $howmanyanswersnecessary = '<span class="badge badge-dark" data-toggle = "tooltip" title = "'.
                                           get_string("allanswersnecessary_help", "cardboxx").'">'.
                                           get_string("allanswersnecessary", "cardboxx").'</span>';
            }

            foreach ($cardcontents as $cardcontent) {
                $cardcontent->content = format_text($cardcontent->content);

                if ($cardcontent->topicname === null) {
                    $topicname = get_string('notopic', 'cardboxx');;
                } else {
                    $topicname = $cardcontent->topicname;
                }

                // Question Side.
                if ($cardcontent->cardside == CARDBOXX_CARDSIDE_QUESTION) {
                    switch($cardcontent->contenttype){
                        case CARDBOXX_CONTENTTYPE_IMAGE:
                            $downloadurl = cardboxx_get_download_url($customdata['context'], $cardcontent->id,
                                        $cardcontent->content);
                            $question .= '<div class="cardboxx-image"><img src="'.$downloadurl.'" alt=""
                                            class="img-fluid  d-block"></div>';
                        break;
                        case CARDBOXX_CONTENTTYPE_TEXT:
                            $question .= '<div class="cardboxx-card-text text-center"><div class="text_to_html"
                                            style="text-align: center;">'.
                            $cardcontent->content.'</div></div>';
                        break;
                        case CARDBOXX_CONTENTTYPE_AUDIO:
                            $downloadurl = cardboxx_get_download_url($customdata['context'], $cardcontent->id,
                                $cardcontent->content);
                            $question .= '<audio controls="">
                                              <source src="'.$downloadurl.'" type="audio/mpeg">
                                          </audio>';
                        break;
                        default:
                            echo "<span class='notification alert alert-danger alert-block fade in' role='alert'
                                    style='display:block'>Something went wrong </span>";
                    }
                } else {
                    $countapprovedanswers = $DB->count_records('cardboxx_cardcontents',
                        ['cardside' => $cardcontent->cardside, 'card' => $cardid, 'area' => CARD_MAIN_INFORMATION]);
                    $countsuggestedanswers = $countapprovedanswers + $DB->count_records('cardboxx_cardcontents',
                        ['cardside' => CARDBOXX_CARDSIDE_ANSWER, 'card' => $cardid, 'area' => CARD_ANSWERSUGGESTION_INFORMATION]);

                    if ($countsuggestedanswers > 1) {
                        $count++;
                        if (!$divadded) {
                            $answer .= '<div class="cardboxx-card-right-side-multi">';
                            $divadded = true;
                        }
                        $height = (100 - ($countsuggestedanswers - 1)) / $countsuggestedanswers;

                        $answerapproved = $cardcontent->area != CARD_ANSWERSUGGESTION_INFORMATION;
                        $suggestedanswers = $DB->get_records('cardboxx_cardcontents', ['card' => $cardid,
                            'cardside' => CARDBOXX_CARDSIDE_ANSWER, 'area' => CARD_ANSWERSUGGESTION_INFORMATION], '', 'id,
                            content');
                        $class = 'cardboxx-cardside-multi';
                        if (!$answerapproved) {
                            $class .= ' suggestion';
                        }
                        $answer .= '<div class="'.$class.'" >
                                    <div class="cardboxx-card-text "><div class="text_to_html">'.$cardcontent->content.
                                    '</div></div></div>';
                        if ($count == $countsuggestedanswers) {
                            $answer .= '</div>';
                        }

                    } else {
                        $answer .= '<div class="cardboxx-cardside"><div class="cardboxx-card-text "><div class="text_to_html">'
                            .'<div style="height:100%">'.$cardcontent->content.'</div></div></div></div>';
                    }
                }
            }
            $mform->addElement('html', '<div id="cardboxx-card-in-review" data-cardid="'.$cardid.
                '" class="row reviewcontent" style="margin-bottom: 0px;">');

            if ($cardcontent->disableautocorrect == DISABLE_AUTOCORRECT) {
                $acimgurl = '<span class="badge badge-secondary" data-toggle = "tooltip" title = "'.
                            get_string("autocorrecticon_help", "cardboxx").'">'.
                            get_string("autocorrecticon", "cardboxx"). '</span>';

            } else {
                $acimgurl = '';
            }
            if ($countsuggestedanswers > 1) {
                $mform->addElement('html', '<div class="col-xl-4" style="margin-left: 3%; margin-bottom: 10px">'.
                    strtoupper(get_string('choosetopic', 'cardboxx').': '. $topicname).'</div><div class="col-xl-4"
                    style="padding-left: 0.4%;"><div class="review-icon-grid-div">'.$howmanyanswersnecessary. $acimgurl. '
                    </div></div><div class="col-xl-2"></div><div class="col-xl-4" style="padding:0px;"><div
                    class="cardboxx-column" style="height: 100%;"><div class="cardboxx-card-left-side">
                    <div class="cardboxx-cardside"><div style="height:100%">'.$question.'</div></div></div></div></div>');
            } else {
                $mform->addElement('html', '<div class="col-xl-4" style="margin-left: 3%; margin-bottom: 10px">'.
                    strtoupper(get_string('choosetopic', 'cardboxx').': '. $topicname).'</div><div class="col-xl-4"
                    style="padding-left: 0.4%;"><div class="review-icon-grid-div">'.$acimgurl.' </div></div><div
                    class="col-xl-2"></div><div class="col-xl-4" style="padding:0px;"><div class="cardboxx-column"
                    style="height: 100%;"><div class="cardboxx-card-left-side"><div class="cardboxx-cardside">
                    <div style="height:100%">'.$question.'</div></div></div></div></div>');
            }

            if ($countsuggestedanswers > 1) {
                $mform->addElement('html', '<div class="col-xl-4" style="padding:0px;"><div class="cardboxx-column"
                    style="height: 100%"><div style="height: 100%">'
                .$answer.'</div></div></div>');
            } else {
                $mform->addElement('html', '<div class="col-xl-4" style="padding:0px;"><div class="cardboxx-column"
                    style="height: 100%;"><div class="cardboxx-card-right-side"><div>'
                .$answer.'</div></div></div></div>');
            }

            $mform->addElement('html', '<div class="col-xs-2"><div id="review-button-wrapper">
                <div class="btn-group-vertical" role="group" aria-label="review-actions">
                <button id="cardboxx-edit-'.$cardid.'" type="button" class="btn btn-primary cardboxx-review-button"
                title="Edit"><i class="icon fa fa-pencil fa-fw"></i></button>
                </div></div></div>');
            $cardapproved = cardboxx_card_approved($cardid);
            if ($cardapproved) {
                $mform->addElement('html', '<div class="col-lg-1 checkbox-card">');
                while (true) {
                    if ($countapprovedanswers < 1) {
                        foreach ($suggestedanswers as $suggestedanswer) {
                                $mform->addElement('html', '<div style ="height:'.$height.'%">');
                                $mform->addElement('checkbox', 'chck'.$cardid.'-'.strip_tags(str_replace(" ",
                                        "" , $suggestedanswer->content))); // Checkbox for selection.
                                $mform->addElement('html', '</div>');
                        }
                        break;
                    } else {
                        $mform->addElement('html', '<div style ="height:'.($height - 1).'%"></div>');
                        $countapprovedanswers--;
                    }
                }

            } else {
                $mform->addElement('html', '<div class="col-lg-1 checkbox-card">');
                $mform->addElement('checkbox', 'chck'.$cardid);
            }
            $mform->addElement('html', '</div>');
            $mform->addElement('html', '</div>'); // Ending cardboxx-card-in-review and row reviewcontent.

            $qcontext = $DB->get_field('cardboxx_cardcontents', 'content', ['card' => $cardid, 'cardside' =>
                CARDBOXX_CARDSIDE_QUESTION,
                'contenttype' => CARDBOXX_CONTENTTYPE_TEXT, 'area' => CARD_CONTEXT_INFORMATION]);
            $qcontext = format_text($qcontext);
            $acontext = $DB->get_field('cardboxx_cardcontents', 'content', ['card' => $cardid, 'cardside' =>
                CARDBOXX_CARDSIDE_ANSWER,
                'contenttype' => CARDBOXX_CONTENTTYPE_TEXT, 'area' => CARD_CONTEXT_INFORMATION]);
            $acontext = format_text($acontext);
            $mform->addElement('html', '<div id="cardboxx-card-in-review" class="row reviewcontent"
                style="display: -webkit-box; margin-top: 10px">
            <div class="col-xl-4" style="margin-left: 10%; padding-right: 0px; padding-left: 1%;">
            <div class="cardboxx-column" >'.$qcontext. '</div></div><div class="col-xl-4" style="padding-left:0.5%;">
            <div class="cardboxx-column" ><div>'.$acontext.'
            </div></div></div></div>');

        }
        $mform->addElement('html', '<div id= "review-div" class="cardboxx-card-in-review sticky-review-arr">');
        $reviewbtngrp = [];
        $reviewbtngrp[] =& $mform->createElement('submit', 'approvebtn', get_string('approve', 'cardboxx'));
        $reviewbtngrp[] =& $mform->createElement('submit', 'rejectbtn', get_string('reject', 'cardboxx'));
        $mform->addGroup($reviewbtngrp, 'reviewbtnarr', '', [''], false);
        $mform->setType('reviewbtnarr', PARAM_RAW);
        $mform->closeHeaderBefore('reviewbtnarr');
        $mform->addElement('html', '</div></div></div>');
    }
}
