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
require_once('card_sorting_interface.php');
/**
 * Class cardboxx_card_sorting_algorithm
 */
class cardboxx_card_sorting_algorithm implements cardboxx_card_sorting_interface {

    /**
     * This function sorts a selection of cards for practice.
     * 1. Cards are shuffled by topic to make them more memorable.
     * 2. New and difficult material is positioned at the beginning and end.
     *
     * @param array $cardselection The selection of cards to sort
     * @return array The sorted cards
     */
    public function cardboxx_sort_cards_for_practice($cardselection) {

        global $DB;

        $cardboxxid = $DB->get_field('cardboxx_cards', 'cardboxx', ['id' => $cardselection[0]->card], $strictness = MUST_EXIST);
        $topics = $DB->get_fieldset_select('cardboxx_topics', 'topicname', 'cardboxxid = ?', [$cardboxxid]);

        shuffle($cardselection); // Randomize order of cards before sorting.

        if (empty($topics)) {

            // 0. Move new and difficult material to the beginning and end of the practice session.
            usort($cardselection, ['cardboxx_card_sorting_algorithm', 'cardboxx_compare_cards_primacy_recency']);

            return $cardselection;

        }

        // 1. Shuffle the topics.

        // 1.1 Collect the topics.
        $coll = new stdClass();
        $coll->notopic = [];
        foreach ($topics as $topic) {
            $coll->$topic = [];

        }
        // 1.2 Sort cards by topic.
        foreach ($cardselection as $card) {
            if (!empty($card->topicname)) {
                $topicname = $card->topicname;
                $coll->{$topicname}[] = $card;
            } else {
                $coll->notopic[] = $card;
            }
        }

        // 1.3 Mix topics.

        $newselection = [];

        $remaining = count($cardselection);

        for ($i = 0; $remaining > 0; $i++) {

            foreach ($coll as $topic) {

                if (!empty($topic[$i]) && ($remaining > 0) ) {

                    $newselection[] = $topic[$i];
                    $remaining--;
                }
            }

        }

        // 2. Move new and difficult material to the beginning and end of the practice session.
        usort($newselection, ['cardboxx_card_sorting_algorithm', 'cardboxx_compare_cards_primacy_recency']);

        return $newselection;

    }

    /**
     * This function sorts cards according to their position in the Leitner system.
     * Cards from box one move to the head of the queue and new cards move to its tail.
     * This sorting uses the effects of primacy and recency to help students remember
     * difficult and new facts.
     *
     * @param stdClass $a Object representing a card
     * @param stdClass $b Object representing a card
     * @return int The comparison result
     */
    public static function cardboxx_compare_cards_primacy_recency($a, $b) {

        if ($a->cardposition == $b->cardposition) {
            return 0;
        }
        if ($a->cardposition == 1 || $b->cardposition == 0) {
            return -1;

        } else if ($a->cardposition == 0 || $b->cardposition == 1) {
            return 1;

        }
        return 0;
    }

}
