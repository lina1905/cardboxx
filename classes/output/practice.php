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
 * This is the practice page.
 *
 * @package   mod_cardbox
 * @copyright 2019 RWTH Aachen (see README.md)
 * @author    Anna Heynkes
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();
define ('QUESTION_SELFCHECK', 1);
define ('QUESTION_AUTOCHECK', 2);
define ('ANSWER_SELFCHECK', 3);
define ('ANSWER_AUTOCHECK', 4);
define('SUGGEST_ANSWER', 5);
/**
 * Class cardbox_practice
 *
 * This class represents the practice page in the cardbox module. It implements the renderable and templatable interfaces
 * which allows it to be used with Moodle's templating engine.
 */
class cardbox_practice implements \renderable, \templatable {

    /**
     * @var string The topic of the card.
     */
    private $topic;

    /**
     * @var array The question content of the card.
     */
    private $question = ['images' => [], 'sounds' => [], 'texts' => []];

    /**
     * @var array The answer content of the card.
     */
    private $answer = ['images' => [], 'sounds' => [], 'texts' => []];

    /**
     * @var int The case number.
     */
    private $case;

    /**
     * @var bool Flag for Question_selfcheck case.
     */
    private $case1 = false;

    /**
     * @var bool Flag for Question_autocheck case.
     */
    private $case2 = false;

    /**
     * @var bool Flag for Answer_selfcheck case.
     */
    private $case3 = false;

    /**
     * @var bool Flag for Answer_autocheck case.
     */
    private $case4 = false;

    /**
     * @var bool Flag for Suggest_answer case.
     */
    private $case5 = false;

    /**
     * @var array The input fields for the card.
     */
    private $inputfields = [];

    /**
     * @var string|null The question context.
     */
    private $questioncontext = null;

    /**
     * @var string|null The answer context.
     */
    private $answercontext = null;

    /**
     * @var int The number of necessary answers.
     */
    private $necessaryanswers = 0;

    /**
     * @var int Flag for case sensitivity.
     */
    private $casesensitive = 0;

    /**
     * @var int The count of answers.
     */
    private $answercount = 0;

    /**
     * @var int The number of cards left.
     */
    private $cardsleft;

    /**
     * This function constructs the cardbox_practice object.
     *
     * @param int $case The case number
     * @param \context $context The context object
     * @param int $cardid The id of the card
     * @param int $cardsleft The number of cards left
     * @param bool $disableautocorrect Flag for disabling auto correction
     */
    public function __construct($case, $context, $cardid, $cardsleft, $disableautocorrect) {
        global $DB;
        switch ($case) {
            case QUESTION_SELFCHECK:
                $casename = 'case'.QUESTION_SELFCHECK;
                $this->$casename = true;
                $this->case = QUESTION_SELFCHECK;
                break;
            case QUESTION_AUTOCHECK:
                $cardstatus = $DB->get_record('cardbox_cards', ['id' => $cardid]);
                if ($cardstatus->disableautocorrect) {
                    $casename = 'case'.QUESTION_SELFCHECK;
                    $this->$casename = true;
                    $this->case = QUESTION_SELFCHECK;
                } else {
                    $casename = 'case'.QUESTION_AUTOCHECK;
                    $this->$casename = true;
                    $this->case = QUESTION_AUTOCHECK;
                }
                break;
            case ANSWER_SELFCHECK:
                $casename = 'case'.ANSWER_SELFCHECK;
                $this->$casename = true;
                $this->case = ANSWER_SELFCHECK;
                break;
            case ANSWER_AUTOCHECK:
                $cardstatus = $DB->get_record('cardbox_cards', ['id' => $cardid]);
                if ($cardstatus->disableautocorrect) {
                    $casename = 'case'.ANSWER_SELFCHECK;
                    $this->$casename = true;
                    $this->case = ANSWER_SELFCHECK;
                } else {
                    $casename = 'case'.ANSWER_AUTOCHECK;
                    $this->$casename = true;
                    $this->case = ANSWER_AUTOCHECK;
                }
                break;
            case SUGGEST_ANSWER:
                $casename = 'case'.SUGGEST_ANSWER;
                $this->$casename = true;
                $this->case = SUGGEST_ANSWER;
                break;
            default:
                // TODO MDL-1 Error handling.
        }
        $this->cardsleft = $cardsleft;
        $this->cardbox_getcarddeck($cardid);
        $this->cardbox_prepare_cardcontents($context, $cardid, $disableautocorrect);

    }

    /**
     * This function prepares the content of a card for the practice view.
     *
     * @param \context $context The context object
     * @param int $cardid The id of the card
     * @param bool $disableautocorrect Flag for disabling auto correction
     */
    public function cardbox_prepare_cardcontents($context, $cardid, $disableautocorrect) {

        global $CFG, $DB;
        require_once($CFG->dirroot . '/mod/cardbox/locallib.php');
        require_once('model/cardbox.class.php');

        $contents = cardbox_cardboxmodel::cardbox_get_card_contents($cardid);

        $topic = cardbox_get_topic($cardid);
        if ($topic === 0 || $topic == "NULL") {
            $this->topic = "";
        } else {
            $this->topic = strtoupper($DB->get_field('cardbox_topics', 'topicname', ['id' => $topic]));
        }

        $this->casesensitive = cardbox_cardboxmodel::cardbox_get_casesensitive($cardid);

        $fs = get_file_storage();
        $solutioncount = 0;
        foreach ($contents as $content) {

            if ($content->area == CARD_CONTEXT_INFORMATION && $content->cardside == CARDBOX_CARDSIDE_QUESTION) {
                // Check for question context.
                $this->questioncontext = format_text($content->content);

            } else if ($content->area == CARD_CONTEXT_INFORMATION && $content->cardside == CARDBOX_CARDSIDE_ANSWER) {
                // Check for answer context.
                $this->answercontext = format_text($content->content);

            } else if ($content->contenttype == CARDBOX_CONTENTTYPE_IMAGE) { // Check for images.

                $downloadurl = cardbox_get_download_url($context, $content->id, $content->content);
                if ($content->cardside == CARDBOX_CARDSIDE_QUESTION) {
                    if ($content->area == CARD_IMAGEDESCRIPTION_INFORMATION) {
                        $this->question['images'][0] += ['imagealt' => $content->content];
                        continue;
                    }
                    $this->question['images'][] = ['imagesrc' => $downloadurl];
                } else {
                    $this->answer['images'][] = ['imagesrc' => $downloadurl];
                }

            } else if ($content->contenttype == CARDBOX_CONTENTTYPE_AUDIO) { // Audio files.

                $downloadurl = cardbox_get_download_url($context, $content->id, $content->content);
                if ($content->cardside == CARDBOX_CARDSIDE_QUESTION) {
                    $this->question['sounds'][] = ['soundsrc' => $downloadurl];
                } else {
                    $this->answer['sounds'][] = ['soundsrc' => $downloadurl];
                }

            } else if ($content->cardside == CARDBOX_CARDSIDE_QUESTION) {

                $content->content = format_text($content->content);

                $this->question['texts'][] = ['text' => $content->content, 'puretext' => $content->content];

            } else {

                $content->content = format_text($content->content, FORMAT_MOODLE, ['para' => false]);
                if ($disableautocorrect) {
                    // We want the bare text for answer comparison, no HTML tags.
                    // Otherwise autocorrection doesn't work.
                    $content->content = strip_tags($content->content);
                    $content->content = trim($content->content);
                }

                if ($content->area === "3") {
                    continue;
                }
                $this->answer['texts'][] = ['text' => $content->content, 'puretext' => $content->content];
                $solutioncount++;
                $this->inputfields[] = ['number' => $solutioncount];
            }
        }
        $this->answercount = $solutioncount;
        $this->necessaryanswers = $DB->get_field('cardbox_cards', 'necessaryanswers', ['id' => $cardid], IGNORE_MISSING);
        if ($this->necessaryanswers != 0) {
            $this->inputfields = ['number' => '1'];
        }
    }
    /**
     * This function gets the deck of a card.
     *
     * @param int $cardid
     * @return void
     */
    public function cardbox_getcarddeck(int $cardid) {
        global $CFG, $DB, $USER;
        if ($DB->record_exists('cardbox_progress', ['userid' => $USER->id, 'card' => $cardid])) {
            $this->deck = $DB->get_field('cardbox_progress', 'cardposition',
                                         ['userid' => $USER->id, 'card' => $cardid], IGNORE_MISSING);
            if ($this->deck == 0) {
                $this->deckimgurl = $CFG->wwwroot . '/mod/cardbox/pix/newpix/new.svg';
            } else if ($this->deck == 6) {
                $this->deckdeckimgurlimg = $CFG->wwwroot . '/mod/cardbox/pix/newpix/mastered.svg';
            } else {
                $this->deckimgurl = $CFG->wwwroot . '/mod/cardbox/pix/newpix/'.$this->deck.'.svg';
            }
        } else {
            $this->deck = null;
            $this->deckimgurl = $CFG->wwwroot . '/mod/cardbox/pix/newpix/new.svg';
        }

    }

    /**
     * This function exports the data of a card for the template.
     *
     * @param renderer_base $output
     * @return array
     */
    public function export_for_template(\renderer_base $output) {

        $data = [];
        $data['topic'] = $this->topic;
        $data['question'] = $this->question;
        $data['answer'] = $this->answer;
        $data['case1'] = $this->case1;
        $data['case2'] = $this->case2;
        $data['case3'] = $this->case3;
        $data['case4'] = $this->case4;
        $data['case5'] = $this->case5;
        $data['inputfields'] = $this->inputfields;
        $data['contextquestion'] = $this->questioncontext;
        $data['contextanswer'] = $this->answercontext;
        $data['necessaryanswers'] = $this->necessaryanswers;
        $data['casesensitive'] = $this->casesensitive;
        $data['contextquestionavailable'] = $this->questioncontext != null;
        $data['contextansweravailable'] = $this->answercontext != null;
        $data['icon'] = "";
        $data['morethanonesolution'] = ($this->answercount > 1);
        $data['cardsleft'] = $this->cardsleft;
        $data['deck'] = $this->deck;
        $data['deckimgurl'] = $this->deckimgurl;
        return $data;

    }

}
