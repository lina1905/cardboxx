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
 * Define all the backup steps that will be used by the backup_cardboxx_activity_task
 *
 * Moodle creates backups of courses or their parts by executing a so called backup plan.
 * The backup plan consists of a set of backup tasks and finally each backup task consists of one or more backup steps.
 * This file provides all the backup steps classes.
 *
 * See https://docs.moodle.org/dev/Backup_API and https://docs.moodle.org/dev/Backup_2.0_for_developers for more information.
 *
 * @package   mod_cardboxx
 * @copyright 2019 RWTH Aachen (see README.md)
 * @author    Anna Heynkes
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

/**
 * Define the complete pdfannotator structure for backup, with file and id annotations
 */
class backup_cardboxx_activity_structure_step extends backup_activity_structure_step {

    /**
     * There are three main things that the method must do:
     * 1. Create a set of backup_nested_element instances that describe the required data of your plugin
     * 2. Connect these instances into a hierarchy using their add_child() method
     * 3. Set data sources for the elements, using their methods like set_source_table() or set_source_sql()
     * The method must return the root backup_nested_element instance processed by the prepare_activity_structure()
     * method (which just wraps your structures with a common envelope).
     *
     */
    protected function define_structure(): \backup_nested_element {

        // 1. To know if we are including userinfo.
        $userinfo = $this->get_setting_value('userinfo'); // This variable is always 0.

        // 2. Define each element separately.
        $cardboxx = new backup_nested_element('cardboxx', ['id'], ['name', 'intro', 'introformat', 'enablenotifications',
            'autocorrection', 'necessaryanswers', 'necessaryanswerslocked', 'casesensitive', 'timecreated', 'timemodified']);

        $cards = new backup_nested_element('cards');
        $card = new backup_nested_element('card', ['id'], ['topic', 'author', 'timecreated', 'timemodified', 'approved',
            'approvedby', 'necessaryanswers', 'disableautocorrect']);

        $cardcontents = new backup_nested_element('cardcontents');
        $cardcontent = new backup_nested_element('cardcontent', ['id'],
            ['card', 'cardside', 'contenttype', 'area', 'content']);

        $topics = new backup_nested_element('topics');
        $topic = new backup_nested_element('topic', ['id'], ['topicname', 'cardboxxid']);

        if ($userinfo != 0) {

            $progress = new backup_nested_element('progress');
            $singleprogress = new backup_nested_element('singleprogress', ['id'],
                ['userid', 'card', 'cardposition', 'lastpracticed', 'repetitions']);

            $statistics = new backup_nested_element('statistics');
            $statistic = new backup_nested_element('statistic', ['id'],
                ['userid', 'cardboxxid', 'timeofpractice', 'percentcorrect']);
        }

        // 3. Build the tree (mind the right order!)
        $cardboxx->add_child($topics);
        $topics->add_child($topic);

        $cardboxx->add_child($cards);
        $cards->add_child($card);

        $card->add_child($cardcontents);
        $cardcontents->add_child($cardcontent);

        if ($userinfo != 0) {
            $cardboxx->add_child($statistics);
            $statistics->add_child($statistic);
            $card->add_child($progress);
            $progress->add_child($singleprogress);
        }

        // 4. Define db sources
        $cardboxx->set_source_table('cardboxx', ['id' => backup::VAR_ACTIVITYID]); // Pass the course module id.

        // 4.1 Add all cards that belong to this cardboxx instance.
        $card->set_source_table('cardboxx_cards', ['cardboxx' => backup::VAR_PARENTID]);

        // 4.2 Add any topics that were created in this cardboxx instance.
        $topic->set_source_table('cardboxx_topics', ['cardboxxid' => backup::VAR_PARENTID]);

        // 4.3 Add the contents such as images and questions to the cards in this cardboxx.
        $cardcontent->set_source_table('cardboxx_cardcontents', ['card' => backup::VAR_PARENTID]);

        if ($userinfo != 0) {

            // 4.4 Add the statistics of this cardboxx instance.
            $statistic->set_source_table('cardboxx_statistics', ['cardboxxid' => backup::VAR_PARENTID]);

            // 4.5 Add the information on user progresses in this cardboxx.
            $singleprogress->set_source_table('cardboxx_progress', ['card' => backup::VAR_PARENTID]);

        }

        // 5. Define id annotations (some attributes are foreign keys).
        $card->annotate_ids('topic', 'topic');
        $card->annotate_ids('user', 'author');
        $card->annotate_ids('user', 'approvedby');

        $cardcontent->annotate_ids('card', 'card');

        if ($userinfo != 0) {

            $statistic->annotate_ids('user', 'userid');
            $singleprogress->annotate_ids('user', 'userid');

        }

        // 6. Define file area annotations (vgl. resource activity).
        $cardboxx->annotate_files('mod_cardboxx', 'intro', null); // This file area does not have an itemid.
        $cardcontent->annotate_files('mod_cardboxx', 'content', null); // By content->id.

        // 7. Return the root element (pdfannotator), wrapped into standard activity structure.
        return $this->prepare_activity_structure($cardboxx);
    }
}
