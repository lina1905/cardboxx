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
 * This file keeps track of upgrades to the cardboxx plugin
 *
 * @package   mod_cardboxx
 * @copyright 2019 RWTH Aachen (see README.md)
 * @author    Anna Heynkes
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die;

require_once($CFG->dirroot.'/mod/cardboxx/locallib.php');
/**
 * This function does anything necessary to upgrade
 *
 * @param int $oldversion The version we are upgrading from
 * @return bool True if upgrade was successful
 */
function xmldb_cardboxx_upgrade($oldversion) {

    global $CFG, $DB;
    $dbman = $DB->get_manager();

    if ($oldversion < 2019022601) {

        // Define table cardboxx_cards to be created.
        $table = new xmldb_table('cardboxx_cards');

        // Adding fields to table.

        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null, null);
        $table->add_field('cardboxx', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null, 'id');
        $table->add_field('topic', XMLDB_TYPE_INTEGER, '10', null, null, null, null, 'cardboxx');
        $table->add_field('author', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null, 'topic');
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null, 'author');
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, null, null, null, 'timecreated');
        $table->add_field('approvedby', XMLDB_TYPE_INTEGER, '10', null, null, null, null, 'timemodified');

        // Adding keys to table cardboxx_cards.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);

        // Conditionally launch create table for cardboxx_cards.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // cardboxx savepoint reached.
        upgrade_mod_savepoint(true, 2019022601, 'cardboxx');
    }

    if ($oldversion < 2019022602) {

        // Define table cardboxx_cards to be created.
        $table = new xmldb_table('cardboxx_progress');

        // Adding fields to table.

        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null, null);
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null, 'id');
        $table->add_field('card', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null, 'userid');
        $table->add_field('cardposition', XMLDB_TYPE_INTEGER, '2', null, null, null, null, 'card');
        $table->add_field('lastpracticed', XMLDB_TYPE_INTEGER, '10', null, null, null, null, 'cardposition');
        $table->add_field('repetitions', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'lastpracticed');

        // Adding keys to table cardboxx_progress.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);

        // Conditionally launch create table for cardboxx_progress.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // cardboxx savepoint reached.
        upgrade_mod_savepoint(true, 2019022602, 'cardboxx');
    }

    if ($oldversion < 2019022603) {

        // Define table cardboxx_cardcontents to be created.
        $table = new xmldb_table('cardboxx_cardcontents');

        // Adding fields to table.

        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null, null);
        $table->add_field('card', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null, 'id');
        $table->add_field('contenttype', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null, 'card');
        $table->add_field('content', XMLDB_TYPE_TEXT, null, null, XMLDB_NOTNULL, null, null, 'contenttype');

        // Adding keys to table cardboxx_cardcontents.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);

        // Conditionally launch create table for cardboxx_cardcontents.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // cardboxx savepoint reached.
        upgrade_mod_savepoint(true, 2019022603, 'cardboxx');
    }

    if ($oldversion < 2019022604) {

        // Define table cardboxx_contenttypes to be created.
        $table = new xmldb_table('cardboxx_contenttypes');

        // Adding fields to table.

        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null, null);
        $table->add_field('type', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null, 'id');
        $table->add_field('name', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null, 'type');

        // Adding keys to table cardboxx_contenttypes.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);

        // Conditionally launch create table for cardboxx_contenttypes.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // cardboxx savepoint reached.
        upgrade_mod_savepoint(true, 2019022604, 'cardboxx');
    }

    if ($oldversion < 2019022605) {

        // Define table cardboxx_topics to be created.
        $table = new xmldb_table('cardboxx_topics');

        // Adding fields to table.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null, null);
        $table->add_field('topicname', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null, 'id');

        // Adding keys to table cardboxx_topics.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);

        // Conditionally launch create table for cardboxx_topics.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // cardboxx savepoint reached.
        upgrade_mod_savepoint(true, 2019022605, 'cardboxx');
    }

    if ($oldversion < 2019022700) {

        // cardboxx savepoint reached.
        upgrade_mod_savepoint(true, 2019022700, 'cardboxx');
    }

    if ($oldversion < 2019022702) {

        // Define field cardside to be added to cardboxx_cardcontents.
        $table = new xmldb_table('cardboxx_cardcontents');
        $field = new xmldb_field('cardside', XMLDB_TYPE_INTEGER, '2', null, XMLDB_NOTNULL, null, null, 'card');

        // Conditionally launch add field cardside.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // cardboxx savepoint reached.
        upgrade_mod_savepoint(true, 2019022702, 'cardboxx');
    }

    if ($oldversion < 2019032700) {

        // Define field autocorrection to be added to cardboxx.
        $table = new xmldb_table('cardboxx');
        $field = new xmldb_field('autocorrection', XMLDB_TYPE_INTEGER, '4', null, XMLDB_NOTNULL, null, '1', 'introformat');

        // Conditionally launch add field autocorrection.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // cardboxx savepoint reached.
        upgrade_mod_savepoint(true, 2019032700, 'cardboxx');
    }

    if ($oldversion < 2019032800) {

        // Define field cardboxxid to be added to changeme.
        $table = new xmldb_table('cardboxx_topics');
        $field = new xmldb_field('cardboxxid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null, 'topicname');

        // Conditionally launch add field cardboxxid.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // cardboxx savepoint reached.
        upgrade_mod_savepoint(true, 2019032800, 'cardboxx');
    }

    if ($oldversion < 2019040200) {

        // Define table cardboxx_statistics to be created.
        $table = new xmldb_table('cardboxx_statistics');

        // Adding fields to table cardboxx_statistics.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('timeofpractice', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('percentcorrect', XMLDB_TYPE_INTEGER, '4', null, XMLDB_NOTNULL, null, null);

        // Adding keys to table cardboxx_statistics.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);

        // Conditionally launch create table for cardboxx_statistics.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // cardboxx savepoint reached.
        upgrade_mod_savepoint(true, 2019040200, 'cardboxx');
    }

    if ($oldversion < 2019040201) {

        // Define field cardboxxid to be added to cardboxx_statistics.
        $table = new xmldb_table('cardboxx_statistics');
        $field = new xmldb_field('cardboxxid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null, 'userid');

        // Conditionally launch add field cardboxxid.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // cardboxx savepoint reached.
        upgrade_mod_savepoint(true, 2019040201, 'cardboxx');
    }

    if ($oldversion < 2019062700) {

        // Define field approved to be added to cardboxx_cards.
        $table = new xmldb_table('cardboxx_cards');
        $field = new xmldb_field('approved', XMLDB_TYPE_INTEGER, '2', null, XMLDB_NOTNULL, null, '0', 'timemodified');

        // Conditionally launch add field approved.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // cardboxx savepoint reached.
        upgrade_mod_savepoint(true, 2019062700, 'cardboxx');
    }

    if ($oldversion < 2019070101) {

        global $DB;

        $sql = "UPDATE {cardboxx_cards} SET approved = 1 WHERE approvedby IS NOT NULL";
        $DB->execute($sql);

        // cardboxx savepoint reached.
        upgrade_mod_savepoint(true, 2019070101, 'cardboxx');
    }

    if ($oldversion < 2019081300) {

        global $DB;
        $table = 'cardboxx_contenttypes';
        $condition = [];
        $types = $DB->record_exists($table, $condition);
        if (!$types) {
            $DB->insert_record($table, ['type' => 'file', 'name' => 'image'], false, false);
            $DB->insert_record($table, ['type' => 'text', 'name' => 'text'], false, false);
            $DB->insert_record($table, ['type' => 'file', 'name' => 'audio'], false, false);
        }

        // cardboxx savepoint reached.
        upgrade_mod_savepoint(true, 2019081300, 'cardboxx');
    }

    if ($oldversion < 2021032301) {

        $table = new xmldb_table('cardboxx');
        $index = new xmldb_index('course_idx', XMLDB_INDEX_NOTUNIQUE, ['course']);

        // Conditionally launch add index course_idx.
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }
        // cardboxx savepoint reached.
        upgrade_mod_savepoint(true, 2021032301, 'cardboxx');
    }

    if ($oldversion < 2021032302) {

        $table = new xmldb_table('cardboxx_topics');
        $indexcardboxxid = new xmldb_index('cardboxxid_idx', XMLDB_INDEX_NOTUNIQUE, ['cardboxxid']);

        // Conditionally launch add index course_idx.
        if (!$dbman->index_exists($table, $indexcardboxxid)) {
            $dbman->add_index($table, $indexcardboxxid);
        }
        // cardboxx savepoint reached.
        upgrade_mod_savepoint(true, 2021032302, 'cardboxx');
    }

    if ($oldversion < 2021032303) {

        $tablecards = new xmldb_table('cardboxx_cards');
        $indexcardboxxid = new xmldb_index('cardboxxid_idx', XMLDB_INDEX_NOTUNIQUE, ['cardboxx']);
        $indextopic = new xmldb_index('topic_idx', XMLDB_INDEX_NOTUNIQUE, ['topic']);
        $indexcardboxxapproved = new xmldb_index('cardboxxapproved_idx', XMLDB_INDEX_NOTUNIQUE, ['cardboxx', 'approved']);
        // Adding indexes to table cardboxx_cards.
        if (!$dbman->index_exists($tablecards, $indexcardboxxid)) {
            $dbman->add_index($tablecards, $indexcardboxxid);
        }
        if (!$dbman->index_exists($tablecards, $indextopic)) {
            $dbman->add_index($tablecards, $indextopic);
        }
        if (!$dbman->index_exists($tablecards, $indexcardboxxapproved)) {
            $dbman->add_index($tablecards, $indexcardboxxapproved);
        }
        upgrade_mod_savepoint(true, 2021032303, 'cardboxx');
    }

    if ($oldversion < 2021032304) {

        $tableprogress = new xmldb_table('cardboxx_progress');
        $indexuseridcard = new xmldb_index('cardboxxid_idx', XMLDB_INDEX_NOTUNIQUE, ['userid', 'card']);
        $indexcardposition = new xmldb_index('cardboxxapproved_idx', XMLDB_INDEX_NOTUNIQUE, ['cardposition']);
        // Adding indexes to table cardboxx_progress.
        if (!$dbman->index_exists($tableprogress, $indexuseridcard)) {
            $dbman->add_index($tableprogress, $indexuseridcard);
        }
        if (!$dbman->index_exists($tableprogress, $indexcardposition)) {
            $dbman->add_index($tableprogress, $indexcardposition);
        }
        upgrade_mod_savepoint(true, 2021032304, 'cardboxx');
    }

    if ($oldversion < 2021032305) {
        $tablecardcontents = new xmldb_table('cardboxx_cardcontents');
        $indexcardcontenttype = new xmldb_index('card_contenttype_idx', XMLDB_INDEX_NOTUNIQUE, ['card', 'contenttype']);
        $indexcard = new xmldb_index('card_idx', XMLDB_INDEX_NOTUNIQUE, ['card']);
        $indexcardside = new xmldb_index('cardside_idx', XMLDB_INDEX_NOTUNIQUE, ['cardside']);
        $indexcontenttype = new xmldb_index('contenttype_idx', XMLDB_INDEX_NOTUNIQUE, ['contenttype']);
        // Adding indexes to table cardboxx_cardcontents.
        if (!$dbman->index_exists($tablecardcontents, $indexcardcontenttype)) {
            $dbman->add_index($tablecardcontents, $indexcardcontenttype);
        }
        if (!$dbman->index_exists($tablecardcontents, $indexcard)) {
            $dbman->add_index($tablecardcontents, $indexcard);
        }
        if (!$dbman->index_exists($tablecardcontents, $indexcardside)) {
            $dbman->add_index($tablecardcontents, $indexcardside);
        }
        if (!$dbman->index_exists($tablecardcontents, $indexcontenttype)) {
            $dbman->add_index($tablecardcontents, $indexcontenttype);
        }
        upgrade_mod_savepoint(true, 2021032305, 'cardboxx');
    }

    if ($oldversion < 2021032306) {
        $tablecontenttypes = new xmldb_table('cardboxx_contenttypes');

        $indexcontentname = new xmldb_index('contentname_idx', XMLDB_INDEX_NOTUNIQUE, ['name']);
        $indextypes = new xmldb_index('types_idx', XMLDB_INDEX_NOTUNIQUE, ['type']);

        // Adding indexes to table cardboxx_contenttypes.
        if (!$dbman->index_exists($tablecontenttypes, $indexcontentname)) {
            $dbman->add_index($tablecontenttypes, $indexcontentname);
        }
        if (!$dbman->index_exists($tablecontenttypes, $indextypes)) {
            $dbman->add_index($tablecontenttypes, $indextypes);
        }
        // cardboxx savepoint reached.
        upgrade_mod_savepoint(true, 2021032306, 'cardboxx');
    }

    if ($oldversion < 2021032307) {
        $tablestatistics = new xmldb_table('cardboxx_statistics');

        $indexuseriduserid = new xmldb_index('userid_userid_idx', XMLDB_INDEX_NOTUNIQUE, ['id', 'userid']);
        // Adding indexes to table cardboxx_statistics.
        if (!$dbman->index_exists($tablestatistics, $indexuseriduserid)) {
            $dbman->add_index($tablestatistics, $indexuseriduserid);
        }

        upgrade_mod_savepoint(true, 2021032307, 'cardboxx');
    }

    if ($oldversion < 2021032308) {

        $table = new xmldb_table('cardboxx_topics');
        $indextopicname = new xmldb_index('topicname_idx', XMLDB_INDEX_NOTUNIQUE, ['topicname']);

        if (!$dbman->index_exists($table, $indextopicname)) {
            $dbman->add_index($table, $indextopicname);
        }
        // cardboxx savepoint reached.
        upgrade_mod_savepoint(true, 2021032308, 'cardboxx');
    }

    if ($oldversion < 2021072102) {

        // Define field context to be added to cardboxx_cardcontents.
        $table = new xmldb_table('cardboxx_cardcontents');
        $field = new xmldb_field('context', XMLDB_TYPE_TEXT, null, null, null, null, null, 'content');

        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // cardboxx savepoint reached.
        upgrade_mod_savepoint(true, 2021072102, 'cardboxx');
    }

    if ($oldversion < 2021072600) {

        // Define field context to be added to cardboxx_cardcontents.
        $table = new xmldb_table('cardboxx_cardcontents');
        $field = new xmldb_field('necessaryanswers', XMLDB_TYPE_INTEGER, '2', null, XMLDB_NOTNULL, null, '0', 'context');

        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // cardboxx savepoint reached.
        upgrade_mod_savepoint(true, 2021072600, 'cardboxx');
    }

    if ($oldversion < 2021072800) {

        // Define field context to be added to cardboxx_cardcontents.
        $table = new xmldb_table('cardboxx');
        $field = new xmldb_field('necessaryanswers', XMLDB_TYPE_INTEGER, '2', null, XMLDB_NOTNULL, null, '-1', 'autocorrection');

        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // cardboxx savepoint reached.
        upgrade_mod_savepoint(true, 2021072800, 'cardboxx');
    }

    if ($oldversion < 2021072902) {

        // Define field context to be added to cardboxx_cardcontents.
        $table = new xmldb_table('cardboxx_cardcontents');
        $field = new xmldb_field('area', XMLDB_TYPE_INTEGER, '2', null, XMLDB_NOTNULL, null, '0', 'contenttype');

        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // cardboxx savepoint reached.
        upgrade_mod_savepoint(true, 2021072902, 'cardboxx');
    }

    if ($oldversion < 2021073003) {

        // Define field context to be dropped from cardboxx_cardcontents.
        $table = new xmldb_table('cardboxx_cardcontents');
        $field = new xmldb_field('necessaryanswers', XMLDB_TYPE_INTEGER, '2', null, XMLDB_NOTNULL, null, '0');

        if (!$dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }

        // Define default value to be changed.
        $table = new xmldb_table('cardboxx');
        $field = new xmldb_field('necessaryanswers', XMLDB_TYPE_INTEGER, '2', null, XMLDB_NOTNULL, null, '0', 'autocorrection');

        if (!$dbman->field_exists($table, $field)) {
            $dbman->change_field_default($table, $field);
        }

        // Define field necessaryanswers to be added to cardboxx_cardcontents.
        $table = new xmldb_table('cardboxx_cards');
        $field = new xmldb_field('necessaryanswers', XMLDB_TYPE_INTEGER, '2', null, XMLDB_NOTNULL, null, '0', 'approvedby');

        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // cardboxx savepoint reached.
        upgrade_mod_savepoint(true, 2021073003, 'cardboxx');
    }

    if ($oldversion < 2021080201) {

        // Define field necessaryanswerseditable to be added to cardboxx.
        $table = new xmldb_table('cardboxx');
        $field = new xmldb_field('necessaryanswerslocked', XMLDB_TYPE_INTEGER, '2', null, XMLDB_NOTNULL, null, '0',
            'necessaryanswers');

        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // cardboxx savepoint reached.
        upgrade_mod_savepoint(true, 2021080201, 'cardboxx');
    }

    if ($oldversion < 2021080400) {

        // Define field necessaryanswerseditable to be added to cardboxx.
        $table = new xmldb_table('cardboxx');
        $field = new xmldb_field('casesensitive', XMLDB_TYPE_INTEGER, '2', null, XMLDB_NOTNULL, null, '0',
            'necessaryanswerslocked');

        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // cardboxx savepoint reached.
        upgrade_mod_savepoint(true, 2021080400, 'cardboxx');
    }

    if ($oldversion < 2021090102) {

        // Define field context to be dropped from cardboxx_cardcontents.
        $table = new xmldb_table('cardboxx_cardcontents');
        $field = new xmldb_field('necessaryanswers');

        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }

        // Define field context to be dropped from cardboxx_cardcontents.
        $table = new xmldb_table('cardboxx_cardcontents');
        $field = new xmldb_field('context');

        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }

        // cardboxx savepoint reached.
        upgrade_mod_savepoint(true, 2021090102, 'cardboxx');
    }

    if ($oldversion < 2021090600) {

        // Replace old contenttypes table with new DEFINEs.
        $contenttypeimage = $DB->get_field('cardboxx_contenttypes', 'id', ['name' => 'image'], MUST_EXIST);
        $contenttypetext = $DB->get_field('cardboxx_contenttypes', 'id', ['name' => 'text'], MUST_EXIST);
        $contenttypeaudio = $DB->get_field('cardboxx_contenttypes', 'id', ['name' => 'audio'], MUST_EXIST);
        $DB->execute("UPDATE {cardboxx_cardcontents} SET contenttype = :newvalue WHERE contenttype = :oldvalue",
            ['oldvalue' => $contenttypeimage, 'newvalue' => cardboxx_CONTENTTYPE_IMAGE]);
        $DB->execute("UPDATE {cardboxx_cardcontents} SET contenttype = :newvalue WHERE contenttype = :oldvalue",
            ['oldvalue' => $contenttypetext, 'newvalue' => cardboxx_CONTENTTYPE_TEXT]);
        $DB->execute("UPDATE {cardboxx_cardcontents} SET contenttype = :newvalue WHERE contenttype = :oldvalue",
            ['oldvalue' => $contenttypeaudio, 'newvalue' => cardboxx_CONTENTTYPE_AUDIO]);

        // Define table cardboxx_contenttypes to be created.
        $table = new xmldb_table('cardboxx_contenttypes');

        // Conditionally launch drop table for cardboxx_contenttypes.
        if ($dbman->table_exists($table)) {
            $dbman->drop_table($table);
        }

        // cardboxx savepoint reached.
        upgrade_mod_savepoint(true, 2021090600, 'cardboxx');
    }

    if ($oldversion < 2021100101) {

        // Define field numberofcards to be added to cardboxx_statistics.
        $table = new xmldb_table('cardboxx_statistics');
        $field = new xmldb_field('numberofcards', XMLDB_TYPE_INTEGER, '4', null, null, null, null, 'timeofpractice');

        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Define field duration to be added to cardboxx_statistics.
        $table = new xmldb_table('cardboxx_statistics');
        $field = new xmldb_field('duration', XMLDB_TYPE_INTEGER, '10', null, null, null, null, 'numberofcards');

        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // cardboxx savepoint reached.
        upgrade_mod_savepoint(true, 2021100101, 'cardboxx');
    }

    if ($oldversion < 2021101400) {
        $table = new xmldb_table('cardboxx_progress');
        $index = new xmldb_index('card_idx', XMLDB_INDEX_NOTUNIQUE, ['card']);
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }
        upgrade_mod_savepoint(true, 2021101400, 'cardboxx');
    }

    // Remove unused index and add new indices to cardboxx_statistics.
    if ($oldversion < 2021102701) {
        $table = new xmldb_table('cardboxx_statistics');

        // Remove (id, userid) index.
        $index = new xmldb_index('userid_userid_idx', XMLDB_INDEX_NOTUNIQUE, ['id', 'userid']);
        if ($dbman->index_exists($table, $index)) {
            $dbman->drop_index($table, $index);
        }

        // Add (userid, cardboxxid) index.
        $index = new xmldb_index('userid_cardboxxid_idx', XMLDB_INDEX_NOTUNIQUE, ['userid', 'cardboxxid']);
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }

        // Add (cardboxxid) index.
        $index = new xmldb_index('userid_cardboxxid_idx', XMLDB_INDEX_NOTUNIQUE, ['cardboxxid']);
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }
        upgrade_mod_savepoint(true, 2021102701, 'cardboxx');
    }

    if ($oldversion < 2021111900) {
        global $DB;
        $sql = "UPDATE {cardboxx_cardcontents} SET contenttype = 1 WHERE contenttype = 2 and cardside = 1";
        $DB->execute($sql);
        // cardboxx savepoint reached.
        upgrade_mod_savepoint(true, 2021111900, 'cardboxx');
    }

    if ($oldversion < 2022060100) {
        global $DB;

        $table = new xmldb_table('cardboxx_cards');
        $field = new xmldb_field('disableautocorrect', XMLDB_TYPE_INTEGER, '2', null, XMLDB_NOTNULL, null, '0', 'necessaryanswers');

        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        $sql = "UPDATE {cardboxx_cards} SET disableautocorrect = 0 WHERE disableautocorrect IS NULL";
        $DB->execute($sql);

        // cardboxx savepoint reached.
        upgrade_mod_savepoint(true, 2022060100, 'cardboxx');
    }

    if ($oldversion < 2023052401) {

        // Define field autocorrection to be added to cardboxx.
        $table = new xmldb_table('cardboxx');
        $field = new xmldb_field('enablenotifications', XMLDB_TYPE_INTEGER, '4', null, XMLDB_NOTNULL, null, '0', 'introformat');

        // Conditionally launch add field autocorrection.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // cardboxx savepoint reached.
        upgrade_mod_savepoint(true, 2023052401, 'cardboxx');
    }

    // Enable notifications for current cardboxxes.
    if ($oldversion < 2023052402) {
        global $DB;

        $sql = "UPDATE {cardboxx} SET enablenotifications = 1";
        $DB->execute($sql);

        // cardboxx savepoint reached.
        upgrade_mod_savepoint(true, 2023052402, 'cardboxx');
    }

    return true;

}
