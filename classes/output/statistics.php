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
 * This is the startistcs page.
 *
 * @package   mod_cardboxx
 * @copyright 2019 RWTH Aachen (see README.md)
 * @author    Anna Heynkes
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Description of statistics
 *
 */
class cardboxx_statistics implements \renderable, \templatable {

    /**
     * @var bool Flag indicating if the user is a manager.
     */
    private $ismanager;

    /**
     * @var string Information about the enrolled students threshold.
     */
    private $infoenrolledstudentsthreshold;

    // Student stats.
    /**
     * @var array The dates for the statistics.
     */
    private $dates;

    /**
     * @var array The performances for the statistics.
     */
    private $performances;

    /**
     * @var bool Flag indicating if the average progress should be displayed.
     */
    private $displayaverageprogress;

    // Manager stats.
    /**
     * @var bool Flag indicating if the weekly stats should be displayed.
     */
    private $displayweeklystats;

    /**
     * @var array The weeks for the statistics.
     */
    private $weeks;

    /**
     * @var stdClass The tooltips for the statistics.
     */
    private $tooltips;

    /**
     * @var int The minimum number of cards for the statistics.
     */
    private $numberofcardsmin;

    /**
     * @var int The maximum number of cards for the statistics.
     */
    private $numberofcardsmax;

    /**
     * @var int The average number of cards for the statistics.
     */
    private $numberofcardsavg;

    /**
     * @var int The minimum duration for the statistics.
     */
    private $durationmin;

    /**
     * @var int The maximum duration for the statistics.
     */
    private $durationmax;

    /**
     * @var int The average duration of session for the statistics.
     */
    private $durationofsessionavg;

    /**
     * Constructor.
     *
     * @param int $cardboxxid
     * @param bool $ismanager
     */
    public function __construct($cardboxxid, $ismanager) {
        $this->ismanager = $ismanager;
        if ($ismanager) {
            $this->init_manager($cardboxxid);
        } else {
            $this->init_student($cardboxxid);
        }
    }

    /**
     * Initialize the statistics for a student.
     *
     * @param int $cardboxxid
     */
    private function init_student($cardboxxid) {
        global $CFG, $DB, $USER;
        require_once($CFG->dirroot . '/mod/cardboxx/locallib.php');

        $this->dates = [];
        $this->performances = [];

        $this->set_enrolled_students_threshold_info(false);

        $this->displayaverageprogress = $this->is_enrolled_students_threshold_reached($cardboxxid);

        $data = $DB->get_records('cardboxx_statistics', ['userid' => $USER->id, 'cardboxxid' => $cardboxxid]);
        foreach ($data as $record) {
            $this->dates[] = cardboxx_get_user_date($record->timeofpractice);
            $this->performances[] = $record->percentcorrect;
        }
    }

    /**
     * Initialize the statistics for a manager.
     *
     * @param int $cardboxxid
     */
    private function init_manager($cardboxxid) {
        global $DB;

        $this->weeks = [];
        $this->numberofcardsavg = [];
        $this->durationofsessionavg = [];
        $this->tooltips = new stdClass();
        $this->tooltips->durationofsession = new stdClass();
        $this->tooltips->numberofcards = new stdClass();
        $this->tooltips->durationofsession->min = [];
        $this->tooltips->durationofsession->average = [];
        $this->tooltips->durationofsession->max = [];
        $this->tooltips->numberofcards->min = [];
        $this->tooltips->numberofcards->average = [];
        $this->tooltips->numberofcards->max = [];

        $this->set_enrolled_students_threshold_info(true);

        $this->displayweeklystats = $this->is_enrolled_students_threshold_reached($cardboxxid);
        if (!$this->displayweeklystats) {
            return;
        }

        $data = $DB->get_records('cardboxx_statistics', ['cardboxxid' => $cardboxxid]);
        $endoflastweek = new DateTime();
        $endoflastweek->modify('Monday this week');
        $endoflastweek = $endoflastweek->format('U');

        $startdate = $endoflastweek - (10 * (86400 * 7));
        $week = 1;

        for ($i = $startdate; $i < $endoflastweek; $i = $i + (86400 * 7), $week++) {
            $mondayweeklater = $i + (86400 * 7);
            $numberofcards = 0;
            $durationofsession = 0;
            $count = 0;
            $distinctusers = [];
            foreach ($data as $record) {
                if ($record->numberofcards === null || $record->duration === null) {
                    // Do not count unfinished practice sessions.
                    continue;
                }
                if ($record->timeofpractice > $i && $record->timeofpractice < $mondayweeklater) {
                    $numberofcards += $record->numberofcards;
                    $durationofsession += $record->duration;
                    $distinctusers[$record->userid] = true;
                    $count++;
                }
            }
            $this->weeks[] = "" .cardboxx_get_user_date_short($i). " - " .cardboxx_get_user_date_short($mondayweeklater - 86400);

            $sqlmin = "SELECT MIN(numberofcards) AS numberofcards, MIN(duration) AS duration"
                . " FROM {cardboxx_statistics}"
                . " WHERE cardboxxid = :cbid AND timeofpractice > :start AND timeofpractice < :end";
            $params = ['cbid' => $cardboxxid, 'start' => $i, 'end' => $mondayweeklater];
            $sqlmax = "SELECT MAX(numberofcards) AS numberofcards, MAX(duration) AS duration"
                . " FROM {cardboxx_statistics}"
                . " WHERE cardboxxid = :cbid AND timeofpractice > :start AND timeofpractice < :end";
            $numberofcardsmin = $DB->get_record_sql($sqlmin, $params);
            $numberofcardsmax = $DB->get_record_sql($sqlmax, $params);

            $practicingusersthreshold = get_config('mod_cardboxx', 'weekly_statistics_user_practice_threshold');
            if ($count == 0 || count($distinctusers) < $practicingusersthreshold) {
                $durationofsession = 0;
                $numberofcards = 0;
                $this->numberofcardsmin[] = 0;
                $durationofsessiontooltipmin = 0;
                $this->numberofcardsmax[] = 0;
                $durationofsessiontooltipmax = 0;
                $this->durationmin[] = 0;
                $numberofcardstooltipmin = 0;
                $this->durationmax[] = 0;
                $numberofcardstooltipmax = 0;
            } else {
                $durationofsession = ($durationofsession) / 60 / $count;
                $numberofcards = round($numberofcards / $count);
                $numberofcardstooltipmin = get_string('numberofcardsmin', 'cardboxx') . ": " . $numberofcardsmin->numberofcards;
                $this->numberofcardsmin[] = $numberofcardsmin->numberofcards;
                $numberofcardstooltipmax = get_string('numberofcardsmax', 'cardboxx') . ": " . $numberofcardsmax->numberofcards;
                $this->numberofcardsmax[] = $numberofcardsmax->numberofcards;
                $durationofsessiontooltipmin = get_string('durationmin', 'cardboxx') . ": " .
                                               format_time($numberofcardsmin->duration);
                $this->durationmin[] = $numberofcardsmin->duration / 60;
                $durationofsessiontooltipmax = get_string('durationmax', 'cardboxx') . ": " .
                                               format_time($numberofcardsmax->duration);
                $this->durationmax[] = $numberofcardsmax->duration / 60;
            }
            $this->durationofsessionavg[] = $durationofsession;
            $this->numberofcardsavg[] = $numberofcards;

            $durationofsessiontooltipavg = get_string('durationavg', 'cardboxx') . ": " . format_time($durationofsession * 60);
            $numberofcardstooltipavg = get_string('numberofcardsavg', 'cardboxx') . ": " . $numberofcards;
            if (count($distinctusers) < $practicingusersthreshold) {
                $belowthreshold = get_string('linegraphtooltiplabel_below_threshold', 'cardboxx', $practicingusersthreshold);
                $numberofcardstooltipmin = $belowthreshold;
                $numberofcardstooltipavg = $belowthreshold;
                $numberofcardstooltipmax = $belowthreshold;
                $durationofsessiontooltipmin = $belowthreshold;
                $durationofsessiontooltipavg = $belowthreshold;
                $durationofsessiontooltipmax = $belowthreshold;
            }
            $this->tooltips->durationofsession->min[] = $durationofsessiontooltipmin;
            $this->tooltips->durationofsession->average[] = $durationofsessiontooltipavg;
            $this->tooltips->durationofsession->max[] = $durationofsessiontooltipmax;
            $this->tooltips->numberofcards->min[] = $numberofcardstooltipmin;
            $this->tooltips->numberofcards->average[] = $numberofcardstooltipavg;
            $this->tooltips->numberofcards->max[] = $numberofcardstooltipmax;
        }
    }


    /**
     * Set the information about the enrolled students threshold.
     *
     * @param bool $ismanager
     */
    private function set_enrolled_students_threshold_info($ismanager) {
        $enrolledstudentsthreshold = get_config('mod_cardboxx', 'weekly_statistics_enrolled_students_threshold');
        /*
        if ($enrolledstudentsthreshold > 0) {
            $stringid = $ismanager ? 'info:enrolledstudentsthreshold_manager' : 'info:enrolledstudentsthreshold_student';
            $this->infoenrolledstudentsthreshold = get_string($stringid, 'cardboxx', $enrolledstudentsthreshold);
        } else {
            $this->infoenrolledstudentsthreshold = false;
        }
        */
    }

    /**
     * Check if the enrolled students threshold is reached.
     *
     * @param int $cardboxxid
     * @return bool
     */
    public static function is_enrolled_students_threshold_reached($cardboxxid) {
        $cm = get_coursemodule_from_instance('cardboxx', $cardboxxid);
        $context = context_module::instance($cm->id);
        $enrolledstudents = get_enrolled_users($context, 'mod/cardboxx:practice');
        $enrolledstudentsthreshold = get_config('mod_cardboxx', 'weekly_statistics_enrolled_students_threshold');
        return count($enrolledstudents) >= $enrolledstudentsthreshold;
    }


    /**
     * Export data for template.
     *
     * @param \renderer_base $output
     * @return array
     */
    public function export_for_template(\renderer_base $output) {

        $data = [];
        $data['ismanager'] = $this->ismanager;

        $data['dates'] = $this->dates;
        $data['performances'] = $this->performances;
        $data['displayaverageprogress'] = $this->displayaverageprogress;

        $data['displayweeklystats'] = $this->displayweeklystats;
        $data['infoenrolledstudentsthreshold'] = $this->infoenrolledstudentsthreshold;
        $data['weeks'] = $this->weeks;
        $data['tooltips'] = $this->tooltips;
        $data['numberofcardsmin'] = $this->numberofcardsmin;
        $data['numberofcardsmax'] = $this->numberofcardsmax;
        $data['numberofcardsavg'] = $this->numberofcardsavg;
        $data['durationofsessionmin'] = $this->durationmin;
        $data['durationofsessionmax'] = $this->durationmax;
        $data['durationofsessionavg'] = $this->durationofsessionavg;
        return $data;

    }
}
