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
 * This file contains the cardboxx-card class which implements renderable and templatable interfaces.
 *
 * @package   mod_cardboxx
 * @copyright 2019 RWTH Aachen (see README.md)
 * @author    Anna Heynkes
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();
define ('MASTERED_POSITION', 6);

/**
 * Class cardboxx_card
 */
class cardboxx_card implements \renderable, \templatable {
    /**
     * @var int $cmid
     */
    private $cmid;
    /**
     * @var int $cardid
     */
    private $cardid;
    /**
     * @var string $topic
     */
    private $topic;
    /**
     * @var array $question
     */
    private $question = ['images' => [], 'texts' => []];
    /**
     * @var array $answer
     */
    private $answer = ['images' => [], 'texts' => []];
    /**
     * @var bool $multipleanswers
     */
    private $multipleanswers = false;
    /**
     * @var bool $allowedtoedit
     */
    private $allowedtoedit = false;
    /**
     * @var string $questioncontext
     */
    private $questioncontext = null;
    /**
     * @var string $answercontext
     */
    private $answercontext = null;
    /**
     * @var bool $seestatus
     */
    private $seestatus = false;
    /**
     * @var int $status
     */
    private $status;
    /**
     * @var int $howmanyanswersnecessary
     */
    private $howmanyanswersnecessary;
    /**
     * @var int $decktext
     */
    private $decktext;
    /**
     * @var int $repsnummer
     */
    private $repsnummer;
    /**
     * @var int $acimgurl
     */
    private $acimgurl;

    /**
     * This function constructs the card object.
     *
     * @param int $cardid The id of the card
     * @param \context $context The context object
     * @param int $cmid The course module id
     * @param bool $allowedtoedit Whether the user is allowed to edit
     * @param bool $seestatus Whether the user can see the status
     */
    public function __construct($cardid, $context, $cmid, $allowedtoedit, $seestatus) {

        require_once('model/cardcollection.class.php');
        require_once('locallib.php');

        global $CFG, $USER;
        $this->cmid = $cmid;
        $this->cardid = $cardid;
        $answercount = 0;

        if ($allowedtoedit) {
            $this->allowedtoedit = true;
        }

        if ($seestatus) {
            $this->seestatus = true;
        }

        $this->status = cardboxx_get_status($cardid, $USER->id);

        $contents = cardboxx_cardcollection::cardboxx_get_cardcontents($cardid);

        $this->topic = cardboxx_cardcollection::cardboxx_get_topic($cardid);

        $necessaryanswers = cardboxx_get_necessaryanswers($cardid);

        if ($necessaryanswers === "1") {
            $this->allansnecessary = false;
            $this->howmanyanswersnecessary = get_string("oneanswersnecessary", "cardboxx");
        } else {
            $this->allansnecessary = true;
            $this->howmanyanswersnecessary = get_string("allanswersnecessary", "cardboxx");
        }

        $this->cardboxx_getcarddeck($cardid, $allowedtoedit);
        $this->getcardreps_ifmastered($cardid, $allowedtoedit);

        if (empty($this->topic)) {
            $this->topic = get_string('notopic', 'cardboxx');
        }

        $fs = get_file_storage();
        foreach ($contents as $content) {

            if ($content->area == CARD_CONTEXT_INFORMATION && $content->cardside == CARDBOXX_CARDSIDE_QUESTION) {
                // Check if there is context for the question.

                $this->questioncontext = format_text($content->content);

            } else if ($content->area == CARD_CONTEXT_INFORMATION && $content->cardside == CARDBOXX_CARDSIDE_ANSWER) {
                // Check if there is context for the answer.

                $this->answercontext = format_text($content->content);

            } else if ($content->contenttype == CARDBOXX_CONTENTTYPE_IMAGE) {

                $downloadurl = cardboxx_get_download_url($context, $content->id, $content->content);
                if ($content->cardside == CARDBOXX_CARDSIDE_QUESTION) {
                    if ($content->area == CARD_IMAGEDESCRIPTION_INFORMATION) {
                        $this->question['images'][0] += ['imagealt' => $content->content];
                        continue;
                    }
                    $this->question['images'][] = ['imagesrc' => $downloadurl];
                } else {
                    $this->answer['images'][] = ['imagesrc' => $downloadurl];
                    $answercount++;
                }

            } else if ($content->cardside == CARDBOXX_CARDSIDE_QUESTION && $content->contenttype == CARDBOXX_CONTENTTYPE_AUDIO) {

                $downloadurl = cardboxx_get_download_url($context, $content->id, $content->content);
                $this->question['sounds'][] = ['soundsrc' => $downloadurl];

            } else if ($content->cardside == CARDBOXX_CARDSIDE_QUESTION) {

                $content->content = format_text($content->content);
                $this->question['texts'][] = ['text' => $content->content];

            } else {

                $content->content = format_text($content->content);
                $this->answer['texts'][] = ['text' => $content->content];
                $answercount++;
            }
        }
        if ($answercount > 1) {
            $this->multipleanswers = true;
        }

    }

    /**
     * This function gets the number of repetitions for a card if it is mastered.
     *
     * @param int $cardid
     * @param bool $allowedtoedit
     * @return void
     */
    public function getcardreps_ifmastered(int $cardid, bool $allowedtoedit) {
        global $DB, $USER;
        $showreps = "";
        if ($allowedtoedit) {
            $cardrepssum = $DB->get_records_sql(
                            'SELECT SUM(repetitions) as repssum
                            FROM {cardboxx_progress} where
                            cardposition = :cardposition and
                            card = :cardid',
                            ['cardid' => $cardid, 'cardposition' => MASTERED_POSITION]);
            $usercount = $DB->count_records_sql(
                'SELECT count(distinct userid)
                            FROM {cardboxx_progress} where
                            cardposition = :cardposition and
                            card = :cardid',
                            ['cardposition' => MASTERED_POSITION, 'cardid' => $cardid]
            );
            foreach ($cardrepssum as $record) {
                if (!empty($usercount)) {
                    $showreps = $record->repssum / $usercount;
                }
            }
        } else {
            $cardrepssum = $DB->get_records_sql(
                'SELECT SUM(repetitions) as repssum
                FROM {cardboxx_progress} where
                cardposition = :cardposition and
                card = :cardid and userid = :userid group by card',
                ['cardid' => $cardid, 'cardposition' => MASTERED_POSITION, 'userid' => $USER->id]);
            foreach ($cardrepssum as $record) {
                $showreps = $record->repssum;
            }
        }
        if ($showreps != "") {
            $this->reps = true;
            $this->repsnummer = round($showreps);
        } else {
            $this->reps = false;
        }
    }
    /**
    /**
     * Get the card deck.
     *
     * @param int $cardid The id of the card
     * @param bool $allowedtoedit Whether the user is allowed to edit
     */
    public function cardboxx_getcarddeck(int $cardid, bool $allowedtoedit) {
        global $CFG, $DB, $USER;
        $acval = $DB->get_field('cardboxx_cards', 'disableautocorrect', ['id' => $cardid]);
        if ($acval == 1) {
            $this->disableautocorrect = true;
            $this->acimgurl = get_string("autocorrecticon", "cardboxx");
        } else {
            $this->disableautocorrect = false;
        }

        if ($allowedtoedit) {
            $decktostudentcount = $DB->get_records_sql(
                'SELECT cardposition, count(userid) as users FROM {cardboxx_progress}
                    where card = :cardid
                        group by cardposition',
                            ['cardid' => $cardid]);
            $totalstudent = 0;
            $weightedsum = 0;
            foreach ($decktostudentcount as $carddecktostudent) {
                $totalstudent += $carddecktostudent->users;
                $weightedsum += ($carddecktostudent->cardposition + 1) * $carddecktostudent->users;
            }
            if ($totalstudent != 0) {
                $this->deck = round($weightedsum / $totalstudent);
            } else {
                $this->deck = 1;
            }
        } else if ($DB->record_exists('cardboxx_progress', ['userid' => $USER->id, 'card' => $cardid])) {
            $this->deck = $DB->get_field('cardboxx_progress', 'cardposition',
                                         ['userid' => $USER->id, 'card' => $cardid], IGNORE_MISSING);
        } else {
            $this->deck = null;
        }

        if ($allowedtoedit) {
            if ($this->deck == 1 || $this->deck == null ) {
                $this->decktext = ucfirst(get_string('new', 'cardboxx'));
                $this->deckimgurl = $CFG->wwwroot . '/mod/cardboxx/pix/newpix/new.svg';
            } else if ($this->deck == 7) {
                $this->decktext = ucfirst(get_string('known', 'cardboxx'));
                $this->deckimgurl = $CFG->wwwroot . '/mod/cardboxx/pix/newpix/mastered.svg';
            } else {
                $deck = $this->deck - 1;
                $this->decktext = $deck;
                $this->deckimgurl = $CFG->wwwroot . '/mod/cardboxx/pix/newpix/'.$deck.'.svg';
            }
        } else {
            if ($this->deck == 0 || $this->deck == null) {
                $this->decktext = $this->decktext = ucfirst(get_string('new', 'cardboxx'));
                $this->deckimgurl = $CFG->wwwroot . '/mod/cardboxx/pix/newpix/new.svg';
            } else if ($this->deck == 6) {
                $this->decktext = ucfirst(get_string('known', 'cardboxx'));
                $this->deckimgurl = $CFG->wwwroot . '/mod/cardboxx/pix/newpix/mastered.svg';
            } else {
                $this->decktext = $this->deck;
                $this->deckimgurl = $CFG->wwwroot . '/mod/cardboxx/pix/newpix/'.$this->deck.'.svg';
            }
        }

    }

    /**
     * Get the card id.
     *
     * @return int
     */
    public function cardboxx_getcarddecknumber() {
        return $this->deck;
    }
    /**
     * Export for template.
     *
     * @param \renderer_base $output
     * @return array
     */
    public function export_for_template(\renderer_base $output) {

        global $OUTPUT;

        $data = [];
        $data['cmid'] = $this->cmid;
        $data['cardid'] = $this->cardid;
        $data['topic'] = strtoupper($this->topic);
        $data['question'] = $this->question;
        $data['answer'] = $this->answer;
        $data['multipleanswers'] = $this->multipleanswers;
        $data['answercontext'] = $this->answercontext;
        $data['questioncontext'] = $this->questioncontext;
        $data['contextquestionavailable'] = $this->questioncontext != null;
        $data['contextansweravailable'] = $this->answercontext != null;
        $data['allowedtoedit'] = $this->allowedtoedit;
        $data['seestatus'] = $this->seestatus;
        $data['status'] = $this->status;
        $data['helpicon'] = $OUTPUT->help_icon('cardposition', 'cardboxx');
        $data['deck'] = $this->deck;
        $data['deckimgurl'] = $this->deckimgurl;
        $data['howmanyanswersnecessary'] = $this->howmanyanswersnecessary;
        $data['reps'] = $this->reps;
        $data['repsnummer'] = $this->repsnummer;
        $data['decktext'] = $this->decktext;
        $data['acimgurl'] = $this->acimgurl;
        $data['disableautocorrect'] = $this->disableautocorrect;
        $data['allansnecessary'] = $this->allansnecessary;
        return $data;

    }
}
