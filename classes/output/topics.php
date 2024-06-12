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
 * This is the topics page.
 *
 * @package   mod_cardboxx
 * @copyright 2019 RWTH Aachen (see README.md)
 * @author    Anna Heynkes
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Description of statistics
 */
class cardboxx_topics implements \renderable, \templatable {

    /**
     * @var array The topics for the cardboxx.
     */
    private $topics = [];
    /**
     * This function prepares the topics and the amount of cards to study.
     *
     * @param array $list The list of topics
     * @param int $offset The offset for the topics
     * @param int $cmid The course module id
     * @param int $cardboxxid The id of the cardboxx
     */
    public function __construct($list, $offset, /* $context, */ $cmid, $cardboxxid) {

        global $DB, $PAGE;

        $topic = [];
        foreach ($list as $topicid => $titel) {
            if ($topicid != -1) {
                $topic['id'] = $topicid;
                $topic['titel'] = $titel;
                $topic['cards'] = $DB->count_records('cardboxx_cards', [ "topic" => $topicid, "cardboxx" => $cardboxxid]);
                $this->topics[] = $topic;
            }
        }
        $perpage = 10;
        $renderer = $PAGE->get_renderer('mod_cardboxx');

    }

    /**
     * Export data for template.
     *
     * @param \renderer_base $output
     * @return array
     */
    public function export_for_template(\renderer_base $output) {
        $data = [];
        $data['topic'] = $this->topics;
        $data['notopics'] = empty($this->topics);
        return $data;
    }
}
