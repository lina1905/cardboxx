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

defined('MOODLE_INTERNAL') || die();

/**
 *
 * @package   mod_cardboxx
 * @copyright 2019 RWTH Aachen (see README.md)
 * @author    Anna Heynkes
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class cardboxx_cardboxxmodel {
    /**
     * @var int The ID of the cardboxx.
     */
    private $id;

    /**
     * @var array The flashcards in the cardboxx.
     */
    private $flashcards;

    /**
     * @var array The boxes in the cardboxx, each containing an array of cards.
     */
    private $boxes = [0 => [], 1 => [], 2 => [], 3 => [], 4 => [], 5 => [], 6 => []];

    /**
     * @var cardboxx_card_selection_interface The algorithm used for card selection.
     */
    private $selectionalgorithm;

    /**
     * @var cardboxx_card_sorting_interface The algorithm used for card sorting.
     */
    private $sortingalgorithm;
    /**
     * Constructor.
     *
     * @param int $cardboxxid
     * @param cardboxx_card_selection_interface $selectionalgorithm
     * @param cardboxx_card_sorting_interface $sortingalgorithm
     * @param int $topic
     */
    public function __construct($cardboxxid, cardboxx_card_selection_interface $selectionalgorithm = null,
                                cardboxx_card_sorting_interface $sortingalgorithm = null, $topic=-1) {

        $this->id = $cardboxxid;

        // 1. Add any new cards to the user's cardboxx system (represented by the cardboxx_progress table).
        cardboxx_add_new_cards($cardboxxid, $topic);

        // 2. Access all cards in this user's cardboxx system and adjust the overall cardcount.
        $this->cardboxx_get_users_cards($topic);

        $this->selectionalgorithm = $selectionalgorithm;
        $this->sortingalgorithm = $sortingalgorithm;

    }
    /**
     * Function returns the number of cards in the user's cardboxx.
     *
     * @return int
     */
    public function cardboxx_count_cards() {
        if (empty($this->flashcards)) {
            return 0;
        } else {
            return count($this->flashcards);
        }
    }
    /**
     * Function returns the number of cards in the user's cardboxx that are due for practice.
     *
     * @return int
     */
    public function cardboxx_count_due_cards() {
        $due = 0;
        foreach ($this->flashcards as $card) {
            if (cardboxx_is_card_due($card)) {
                $due++;
            }
        }
        return $due;
    }
    /**
     * Function returns the number of cards in the user's cardboxx that are not due for practice.
     *
     * @return int
     */
    public function cardboxx_count_mastered_cards() {
        return count($this->boxes[6]);
    }
    /**
     * Function returns the ids of those cards selected for practice.
     *
     * @param int $amountcards The amount of cards to select (optional)
     * @return array of ints The ids of the selected cards
     */
    public function cardboxx_get_card_selection($amountcards = 0) {

        $selection = [];

        // Select 10 flashcards for a practice session.
        if (!empty($this->flashcards) && !empty($this->selectionalgorithm)) {

            // Delegate card selection to the selection algorithm instance.
            $cards = $this->selectionalgorithm->cardboxx_select_cards_for_practice($this->flashcards);

        } else {
            return null;
        }

        // Sort the selected cards.
        if (!empty($cards) && !empty($this->sortingalgorithm)) {
            // Delegate card sorting to the sorting algorithm instance.
            $cards = $this->sortingalgorithm->cardboxx_sort_cards_for_practice($cards);
        }

        // Return the ids of the cards.
        if ($amountcards === 0) {
            foreach ($cards as $card) {
                $selection[] = $card->card;
            }
        } else {
            foreach ($cards as $card) {
                $selection[] = $card->card;
                $amountcards--;
                if ($amountcards === 0) {
                    break;
                }
            }
        }

        return $selection;
    }

    /**
     * Function returns an array specifying how many due/not-due cards there are in each box
     * (for this user and this cardboxx instance).
     *
     * @return array
     */
    public function cardboxx_get_status() {

        $now = new DateTime("now");

        $cardsperbox = [];

        $cardsperbox[0] = count($this->boxes[0]);
        $cardsperbox[6] = count($this->boxes[6]);

        for ($i = 1; $i <= 5; $i++) {
            $cardsperbox[$i] = $this->selectionalgorithm->cardboxx_count_due_and_not_due($this->boxes[$i], $now);
        }

        return $cardsperbox;
    }
    /**
     * Function retrieves all flashcards that
     * 1. belong to the current cardboxx plugin instance
     * 2. are registered for the current user in the progress table
     *    which is the virtual representation of a cardboxx system
     *
     * Each card is filed into one of the 'boxes' or 'decks'.
     *
     * @param int $topic The topic of the cards
     */
    private function cardboxx_get_users_cards($topic) {

        global $DB, $USER;

        $sql = "SELECT p.card, p.cardposition, p.lastpracticed, p.repetitions, t.topicname "
                . "FROM {cardboxx_progress} p "
                . "LEFT JOIN {cardboxx_cards} c ON c.id = p.card "
                . "LEFT JOIN {cardboxx_topics} t ON c.topic = t.id "
                . "WHERE p.userid = ? AND c.cardboxx = ? "
                . "ORDER BY p.cardposition";

        $this->flashcards = $DB->get_records_sql($sql, [$USER->id, $this->id]);

        if ($topic != -1) {
            $cards = [];
            $topicname = $DB->get_record_select('cardboxx_topics', 'id=' . $topic, null, 'topicname');
            foreach ($this->flashcards as $card) {
                if (strcmp($card->topicname, $topicname->topicname) == 0) {
                    $cards[] = $card;
                }
            }
            $this->flashcards = $cards;
        }

        foreach ($this->flashcards as $card) {
            $this->boxes[$card->cardposition][] = $card;
        }

    }
    /**
     * Function returns all content items belonging to this card. XXX move to locallib or card class!
     *
     * @param int $cardid The id of the card
     * @return array The content items of the card
     */
    public static function cardboxx_get_card_contents($cardid) {

        global $DB;
        $contents = $DB->get_records('cardboxx_cardcontents', ['card' => $cardid]);
        usort($contents, ['cardboxx_cardboxxmodel', 'cardboxx_compare_cardcontenttypes']);
        return $contents;
    }
    /**
     * This function orders the content elements of a card, e.g. groups question and answer elements.
     * XXX move to locallib or card class!
     *
     * @param object $a The first content element
     * @param object $b The second content element
     * @return int The comparison result
     */
    public static function cardboxx_compare_cardcontenttypes($a, $b) {

        if ($a->cardside == $b->cardside) {

            if ($a->contenttype == $b->contenttype) {
                return 0;
            }
            // Pictures precede text.
            return ($a->contenttype < $b->contenttype) ? -1 : 1;

        }
        // Questions precede answers.
        return ($a->cardside < $b->cardside) ? -1 : 1;

    }

    /**
     * Function returns the case sensitivity status of the card.
     *
     * @param int $cardid The id of the card
     * @return int The case sensitivity status
     */
    public static function cardboxx_get_casesensitive($cardid) {
        global $DB;

        $cardboxxid = $DB->get_field('cardboxx_cards', 'cardboxx', ['id' => $cardid], IGNORE_MISSING);
        return $DB->get_field('cardboxx', 'casesensitive', ['id' => $cardboxxid], IGNORE_MISSING);
    }
}
