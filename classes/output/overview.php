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
 * This is the overview page.
 *
 * @package   mod_cardboxx
 * @copyright 2019 RWTH Aachen (see README.md)
 * @author    Anna Heynkes
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Description of overview
 *
 * @author ah105090
 */
class cardboxx_overview implements \renderable, \templatable {
    /**
     * @var int The topic ID.
     */
    private $topicid;

    /**
     * @var int The deck ID.
     */
    private $deckid;

    /**
     * @var string The sort order.
     */
    private $sort;

    /**
     * @var string The description.
     */
    private $desc;

    /**
     * @var array The topics.
     */
    private $topics = [];

    /**
     * @var array The cards.
     */
    private $cards = [];

    /**
     * @var array The decks.
     */
    private $decks = [];

    /**
     * Constructor.
     *
     * @param array $list
     * @param int $offset
     * @param context $context
     * @param int $cmid
     * @param int $cardboxxid
     * @param int $topicid
     * @param int $deck
     * @param int $sort
     * @param bool $usedforemail
     */
    public function __construct($list, $offset, $context, $cmid, $cardboxxid, $topicid, $usedforemail = false,
                                $sort = null, $deck = null) {
        if ($deck === null) {
            throw new InvalidArgumentException("Deck parameter is required");
        }
        if ($sort === null) {
            throw new InvalidArgumentException("Sort parameter is required");
        }
        require_once('card.php');

        global $DB, $PAGE;

        $topics = $DB->get_records('cardboxx_topics', ['cardboxxid' => $cardboxxid]);
        $this->topicid = $topicid;
        foreach ($topics as $topic) {
            if ($topic->id == $topicid) {
                $this->topics[] = ['topicid' => $topic->id, 'topic' => $topic->topicname, 'selected' => true];
            } else {
                $this->topics[] = ['topicid' => $topic->id, 'topic' => $topic->topicname, 'selected' => false];
            }
        }

        $this->deckid = $deck;
        for ($i = 1; $i < 6; $i++) {
            if ($deck == $i) {
                $this->decks[] = ['deck' => $i, 'selected' => true];
            } else {
                $this->decks[] = ['deck' => $i, 'selected' => false];
            }
        }

        $perpage = 10;
        $renderer = $PAGE->get_renderer('mod_cardboxx');

        if (has_capability('mod/cardboxx:approvecard', $context) && !$usedforemail) {
            $allowedtoedit = true;
        } else {
            $allowedtoedit = false;
        }

        if (has_capability('mod/cardboxx:seestatus', $context)) {
            $seestatus = true;
        } else {
            $seestatus = false;
        }

        for ($i = $offset; ($i < count($list) && $i < $offset + $perpage); $i++) {
            $card = new cardboxx_card($list[$i], $context, $cmid, $allowedtoedit, $seestatus);
            $this->cards[] = $card->export_for_template($renderer);
        }

        $this->sort = $sort;

    }

    /**
     * Export data for template.
     *
     * @param \renderer_base $output
     * @return array
     */
    public function export_for_template(\renderer_base $output) {
        global $OUTPUT;
        $data = [];

        if ($this->topicid == -1) {
            $data['nopreference'] = true;
        } else if ($this->topicid == 0) {
            $data['cardswithouttopic'] = true;
        }

        if ($this->deckid == -1) {
            $data['nopreferencedeck'] = true;
        }
        if ($this->deckid == 0) {
            $data['newcard'] = true;
        }
        if ($this->deckid == 6) {
            $data['masteredcard'] = true;
        }
        $data['decks'] = $this->decks;
        $data['topics'] = $this->topics;
        $data['sortca'] = $this->sort === 1;
        $data['sortad'] = $this->sort === 2;
        $data['sortaa'] = $this->sort === 3;
        $data['cards'] = $this->cards;


        $help = $OUTPUT->help_icon('help:whenarecardsdue', 'cardboxx');
        $data['infoHtmloverview'] = $help;

        return $data;
    }
}
