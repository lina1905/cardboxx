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
 * Moodle restores data from course backups by executing a so called restore plan.
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

/**
 * Define all the restore steps that will be used by the restore_cardboxx_activity_task
 */

/**
 * Structure step to restore one cardboxx activity
 */
class restore_cardboxx_activity_structure_step extends restore_activity_structure_step {
    /**
     * Defines the structure of the cardboxx activity
     */
    protected function define_structure() {

        $paths = [];

        $userinfo = $this->get_setting_value('userinfo');

        $paths[] = new restore_path_element('cardboxx', '/activity/cardboxx');
        $paths[] = new restore_path_element('cardboxx_topics', '/activity/cardboxx/topics/topic');
        $paths[] = new restore_path_element('cardboxx_cards', '/activity/cardboxx/cards/card');
        $paths[] = new restore_path_element('cardboxx_cardcontents', '/activity/cardboxx/cards/card/cardcontents/cardcontent');
        if ($userinfo != 0) {
            $paths[] = new restore_path_element('cardboxx_statistics', '/activity/cardboxx/statistics/statistic');
            $paths[] = new restore_path_element('cardboxx_progress', '/activity/cardboxx/cards/card/progress/singleprogress');
        }
        // Return the paths wrapped into standard activity structure.
        return $this->prepare_activity_structure($paths);
    }
    /**
     * Process the cardboxx element
     *
     * @param array $data The data from the XML file
     */
    protected function process_cardboxx($data) {

        global $DB;

        $data = (object)$data;
        $oldid = $data->id;
        $data->course = $this->get_courseid();
        $data->timecreated = $this->apply_date_offset($data->timecreated);
        $data->timemodified = $this->apply_date_offset($data->timemodified);

        $newitemid = $DB->insert_record('cardboxx', $data); // Insert the cardboxx record.

        $this->apply_activity_instance($newitemid); // Immediately after inserting "activity" record, call this.
    }
    /**
     * Process the cardboxx_topics element
     *
     * @param array $data The data from the XML file
     */
    protected function process_cardboxx_topics($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;

        $data->cardboxxid = $this->get_new_parentid('cardboxx');

        $newitemid = $DB->insert_record('cardboxx_topics', $data);
        $this->set_mapping('cardboxx_topics', $oldid, $newitemid);
    }
    /**
     * Process the cardboxx_cards element
     *
     * @param array $data The data from the XML file
     */
    protected function process_cardboxx_cards($data) {

        global $DB;

        $data = (object)$data;
        $oldid = $data->id;

        $data->cardboxx = $this->get_new_parentid('cardboxx');

        $newitemid = $DB->insert_record('cardboxx_cards', $data);
        $this->set_mapping('cardboxx_cards', $oldid, $newitemid);

    }
    /**
     * Process the cardboxx_cardcontents element
     *
     * @param array $data The data from the XML file
     */
    protected function process_cardboxx_cardcontents($data) {

        global $DB;

        $data = (object)$data;
        $oldid = $data->id;

        $data->card = $this->get_new_parentid('cardboxx_cards');

        $newitemid = $DB->insert_record('cardboxx_cardcontents', $data);
        $this->set_mapping('cardboxx_cardcontents', $oldid, $newitemid, true);

    }
    /**
     * Process the cardboxx_statistics element
     *
     * @param array $data The data from the XML file
     */
    protected function process_cardboxx_statistics($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;

        $data->userid = $this->get_mappingid('user', $data->userid);
        $data->cardboxxid = $this->get_new_parentid('cardboxx');
        $data->timeofpractice = $this->apply_date_offset($data->timeofpractice);

        $newitemid = $DB->insert_record('cardboxx_statistics', $data);
        $this->set_mapping('cardboxx_statistics', $oldid, $newitemid);
    }
    /**
     * Process the cardboxx_progress element
     *
     * @param array $data The data from the XML file
     */
    protected function process_cardboxx_progress($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;

        $data->userid = $this->get_mappingid('user', $data->userid);
        $data->card = $this->get_new_parentid('cardboxx_cards');
        $data->lastpracticed = $this->apply_date_offset($data->lastpracticed);

        $newitemid = $DB->insert_record('cardboxx_progress', $data);
        $this->set_mapping('cardboxx_progress', $oldid, $newitemid);
    }
    /**
     * After the execution of the step
     */
    protected function after_execute() {
        // Add cardboxx related files, no need to match by itemname (just internally handled context).
        $this->add_related_files('mod_cardboxx', 'intro', null);
        $this->add_related_files('mod_cardboxx', 'content', 'cardboxx_cardcontents'); // Cardimage or content?.
    }
}
