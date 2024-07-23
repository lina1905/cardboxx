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
 * Moodle restores data from course backups by executing so called restore plan.
 * The restore plan consists of a set of restore tasks and finally each restore task consists of one or more restore steps.
 * You as the developer of a plugin will have to implement one restore task that deals with your plugin data.
 * Most plugins have their restore tasks consisting of a single restore step
 * - the one that parses the plugin XML file and puts the data into its tables.
 *
 * See https://docs.moodle.org/dev/Backup_API and https://docs.moodle.org/dev/Backup_2.0_for_developers for more information.
 *
 * @package   mod_cardboxx
 * @copyright 2019 RWTH Aachen (see README.md)
 * @author    Anna Heynkes
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/cardboxx/backup/moodle2/restore_cardboxx_stepslib.php'); // Because it exists (must).

/**
 * Restore task for the cardboxx activity module
 */
class restore_cardboxx_activity_task extends restore_activity_task {

    /**
     * Define (add) particular settings this activity can have
     */
    protected function define_my_settings() {
        // No particular settings for this activity.
    }
    /**
     * Define (add) particular steps this activity can have
     */
    protected function define_my_steps() {
        // Cardboxx only has one structure step.
        $this->add_step(new restore_cardboxx_activity_structure_step('cardboxx_structure', 'cardboxx.xml'));
    }

    /*************************************** optional *******************************************/

    /**
     * Define the contents in the activity that must be
     * processed by the link decoder.
     */
    public static function define_decode_contents() {
        $contents = [];

        $contents[] = new restore_decode_content('cardboxx', ['intro'], 'cardboxx');

        return $contents;
    }

    /**
     * Define the decoding rules for links belonging
     * to the activity to be executed by the link decoder
     */
    public static function define_decode_rules() {
        $rules = [];

        $rules[] = new restore_decode_rule('CARDBOXXVIEWBYID', '/mod/cardboxx/view.php?id=$1', 'course_module');
        $rules[] = new restore_decode_rule('CARDBOXXINDEX', '/mod/cardboxx/index.php?id=$1', 'course');

        return $rules;

    }

    /**
     * Define the restore log rules that will be applied
     * by the restore_logs_processor when restoring
     * cardboxx logs. It must return one array
     * of restore_log_rule objects
     */
    public static function define_restore_log_rules() {
        $rules = [];

        $rules[] = new restore_log_rule('cardboxx', 'add', 'view.php?id={course_module}', '{cardboxx}');
        $rules[] = new restore_log_rule('cardboxx', 'update', 'view.php?id={course_module}', '{cardboxx}');
        $rules[] = new restore_log_rule('cardboxx', 'view', 'view.php?id={course_module}', '{cardboxx}');

        return $rules;
    }

    /**
     * Define the restore log rules that will be applied
     * by the restore_logs_processor when restoring
     * course logs. It must return one array
     * of restore_log_rule objects
     *
     * Note these rules are applied when restoring course logs
     * by the final restore task, but are defined here at
     * activity level. All of them are rules not linked to any module instance (cmid = 0)
     */
    public static function define_restore_log_rules_for_course() {
        $rules = [];

        $rules[] = new restore_log_rule('cardboxx', 'view all', 'index.php?id={course}', null);

        return $rules;
    }
}
