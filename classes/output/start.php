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
 * This is the start page.
 *
 * @package   mod_cardbox
 * @copyright 2019 RWTH Aachen (see README.md)
 * @author    Anna Heynkes
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Description of start
 *
 */
class cardbox_start implements \renderable, \templatable {

    /**
     * @var array The topics for the cardbox.
     */
    private $topics;

    /**
     * @var bool The autocorrection option status.
     */
    private $autocorrectionoption = false;

    /**
     * @var array The amount of cards to study.
     */
    private $amountcards;

    /**
     * This function prepares the topics and the amount of cards to study.
     *
     * @param bool $autocorrection The autocorrection status
     * @param int $cardboxid The id of the cardbox
     */
    public function __construct($autocorrection, $cardboxid) {

        $this->cardbox_prepare_topics_to_study($cardboxid);


        if ($autocorrection == 1) {
            // Keine Saubere Lösung, aber es funktioniert; eigentlich auf true, wenn man mit autocorrect arbeitet
            $this->autocorrectionoption = false;
        }


        $this->cardbox_define_amount_of_cards_to_study();

    }

    /**
     * Function includes the list of topics in the practice options modal.
     * The user can then choose to prioritise one of the topics in the
     * selection of cards for a practice session.
     *
     * @param int $cardboxid The id of the cardbox
     */
    public function cardbox_prepare_topics_to_study($cardboxid) {

        global $CFG;
        require_once($CFG->dirroot . '/mod/cardbox/locallib.php');

        $this->topics = [];
        $this->choicestopics = [];

        $topiclist = cardbox_get_topics($cardboxid);

        foreach ($topiclist as $key => $value) {
            $this->topics[] = ['value' => $key, 'label' => $value];
            if ($key === -1) {
                $this->choicestopics[] = ['value' => $key, 'label' => 'all'];
            } else {
                $this->choicestopics[] = ['value' => $key, 'label' => $value];
            }
        }

    }

    /**
     * Function includes the amount of cards to study in the practice options modal.
     *
     */
    public function cardbox_define_amount_of_cards_to_study() {
        $this->amountcards = [];
        $this->amountcards[] = ['value' => 0, 'label' => get_string('undefined', 'cardbox')];
        $this->amountcards[] = ['value' => 10, 'label' => 10];
        $this->amountcards[] = ['value' => 20, 'label' => 20];
        $this->amountcards[] = ['value' => 30, 'label' => 30];
        $this->amountcards[] = ['value' => 40, 'label' => 40];
        $this->amountcards[] = ['value' => 50, 'label' => 50];

    }

    /**
     * Function returns an array with data. The keys of the array have matching variables
     * in the template. These are replaced with the array values by the renderer.
     *
     * @param \renderer_base $output The renderer base instance
     * @return array The data to be exported for the template
     */
    public function export_for_template(\renderer_base $output) {

        global $OUTPUT;

        $data['autoenabled'] = $this->autocorrectionoption;
        $data['autodisabled'] = !$this->autocorrectionoption;
        $data['topics'] = $this->topics;
        $data['choicestopics'] = $this->choicestopics;
        $data['helpbuttonpracticeall'] = $OUTPUT->help_icon('practiceall', 'cardbox');
        $data['amountcards'] = $this->amountcards;
        return $data;

    }

}
