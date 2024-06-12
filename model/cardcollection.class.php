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

/**
 * Interface for card selection
 */
class cardboxx_cardcollection {
    /**
     * @var int The ID of the cardboxx.
     */
    private $cardboxx;

    /**
     * @var array The new/unapproved flashcards in the cardboxx.
     */
    private $flashcards; // New/unapproved flashcards.

    /**
     * Constructor.
     *
     * @param int $cardboxxid
     * @param int $topic
     * @param bool $getall
     */
    public function __construct($cardboxxid, $topic = null, $getall = false) {

        global $DB;
        $this->cardboxx = $cardboxxid;

        $approved = '0';
        if ($getall) {
            $approved = '1';
        }

        if (is_null($topic) || $topic == -1) { // No topic preference.
            $this->flashcards = $DB->get_fieldset_select('cardboxx_cards', 'id', 'cardboxx = ? AND approved = ?',
                [$cardboxxid, $approved]);

        } else if ($topic == 0) { // Only cards without a topic.
            $this->flashcards = $DB->get_fieldset_select('cardboxx_cards', 'id', 'cardboxx = ? AND approved = ?
            AND topic IS NULL', [$cardboxxid, $approved]);

        } else { // A specific topic preference.
            $this->flashcards = $DB->get_fieldset_select('cardboxx_cards', 'id', 'cardboxx = ? AND approved = ?
            AND topic = ?', [$cardboxxid, $approved, $topic]);
        }

    }

    /**
     * Function returns all flashcards that have yet to be approved.
     *
     * @param int|null $offset The offset for the card list (optional)
     * @return array card ids
     */
    public function cardboxx_get_card_list($offset = null) {

        if (!empty($offset)) {
            echo "<span class='notification alert alert-danger alert-block fade in' role='alert' style='display:block'
                    >Something went wrong </span>";
        } else {
            return $this->flashcards;
        }

    }
    /**
     * Function returns the number of cards in the user's cardboxx.
     *
     * @return int
     */
    public function cardboxx_get_first_cardid() {
        return $this->flashcards[0];
    }
    /**
     * Function returns the number of cards in the user's cardboxx.
     *
     * @return array The initial card contents
     */
    public function cardboxx_get_cardcontents_initial() {
        return self::cardboxx_get_cardcontents($this->flashcards[0]);
    }
    /**
     * Function returns the number of cards in the user's cardboxx.
     *
     * @param int $cardid The id of the card
     * @return array The card contents
     */
    public static function cardboxx_get_cardcontents($cardid) {
        global $DB;
        $cardcontents = $DB->get_records('cardboxx_cardcontents', ['card' => $cardid, 'area' => CARD_MAIN_INFORMATION]);
        $cardcontexts = $DB->get_records('cardboxx_cardcontents', ['card' => $cardid, 'area' => CARD_CONTEXT_INFORMATION]);
        return array_merge($cardcontents, $cardcontexts);
    }
    /**
     * Function returns the topic of the card.
     *
     * @param int $cardid The id of the card
     * @return string The topic of the card
     */
    public static function cardboxx_get_topic($cardid) {
        global $DB;
        $sql = "SELECT t.topicname "
                . "FROM {cardboxx_cards} c "
                . "LEFT JOIN {cardboxx_topics} t ON c.topic = t.id "
                . "WHERE c.id = ?";
        return $DB->get_field_sql($sql, [$cardid], $strictness = IGNORE_MISSING);
    }

    /**
     * Function returns the necessary answers locked status of the card.
     *
     * @param int $cardid The id of the card
     * @return int The necessary answers locked status
     */
    public static function cardboxx_get_necessaryanswerslocked($cardid) {
        global $DB;

        $cardboxxid = $DB->get_field('cardboxx_cards', 'cardboxx', ['id' => $cardid], IGNORE_MISSING);
        return $DB->get_field('cardboxx', 'necessaryanswerslocked', ['id' => $cardboxxid], IGNORE_MISSING);
    }

    /**
     * Function returns the question of the card.
     *
     * @param int $cardid The id of the card
     * @return string The question of the card
     */
    public static function cardboxx_get_question($cardid) {
        global $DB;

        $question = $DB->get_field('cardboxx_cardcontents', 'content', ['card' => $cardid, 'area' => CARD_MAIN_INFORMATION,
            'cardside' => cardboxx_CARDSIDE_QUESTION]);
        return strip_tags($question);
    }

}
