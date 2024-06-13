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
 * This file is used when adding/editing a cardboxx module to a course.
 * It contains the elements that will be displayed on the form responsible
 * for creating/installing an instance of cardboxx.
 *
 * @package   mod_cardboxx
 * @copyright 2019 RWTH Aachen (see README.md)
 * @author    Anna Heynkes
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/course/moodleform_mod.php');
require_once($CFG->dirroot.'/mod/cardboxx/lib.php');
require_once($CFG->dirroot.'/mod/cardboxx/locallib.php');

/**
 * cardboxx module instance settings.
 */
class mod_cardboxx_mod_form extends moodleform_mod {

    /**
     * Defines the form.
     */
    public function definition() {
        global $CFG, $DB, $OUTPUT, $USER, $COURSE;

        $mform =& $this->_form;
        $config = get_config('mod_cardboxx');

        $mform->addElement('hidden', 'idcreator', $USER->id);
        $mform->setType('idcreator', PARAM_INT);

        $mform->addElement('hidden', 'idCourse', $COURSE->id);
        $mform->setType('idCourse', PARAM_INT);

        $mform->addElement('text', 'name', get_string('cardboxxname', 'cardboxx'), ['size' => '64']);
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');

        // Description.
        $this->standard_intro_elements();

        $element = $mform->getElement('introeditor');
        $attributes = $element->getAttributes();
        $attributes['rows'] = 5;
        $element->setAttributes($attributes);

        /*
        $mform->addElement('advcheckbox', 'enablenotifications', get_string('setting_enablenotifications', 'cardboxx'),
            get_string('setting_enablenotifications_label', 'cardboxx'), null, [0, 1]);
        $mform->setType('enablenotifications', PARAM_BOOL);
        $mform->setDefault('enablenotifications', 0);
        $mform->addHelpButton('enablenotifications', 'setting_enablenotifications', 'cardboxx');
        */

        /*
        $mform->addElement('advcheckbox', 'autocorrection', get_string('setting_autocorrection', 'cardboxx'),
                    get_string('setting_autocorrection_label', 'cardboxx'), null, [0, 1]);
        $mform->setType('autocorrection', PARAM_BOOL);
        $mform->setDefault('autocorrection', 1);
        $mform->addHelpButton('autocorrection', 'setting_autocorrection', 'cardboxx');
        */

        /*
        $mform->addElement('select', 'necessaryanswers', get_string('necessaryanswers_activity', 'cardboxx'),
                  [
                      '0' => get_string('necessaryanswers_all', 'cardboxx'),
                      '1' => get_string('necessaryanswers_one', 'cardboxx')]);
        $mform->setDefault('necessaryanswers', cardboxx_EVALUATE_ALL);
        $mform->addHelpButton('necessaryanswers', 'necessaryanswers_activity', 'cardboxx');

        $mform->addElement('select', 'necessaryanswerslocked', get_string('necessaryanswers_activity_locked', 'cardboxx'),
                  [
                      '0' => get_string('yes', 'cardboxx'),
                      '1' => get_string('no', 'cardboxx')]);
        $mform->addHelpButton('necessaryanswerslocked', 'necessaryanswers_activity_locked', 'cardboxx');

        $mform->addElement('select', 'casesensitive', get_string('casesensitive', 'cardboxx'),
                  [
                      '0' => get_string('yes', 'cardboxx'),
                      '1' => get_string('no', 'cardboxx')]);
        $mform->addHelpButton('casesensitive', 'casesensitive', 'cardboxx');
        */

        $this->standard_coursemodule_elements();

        $this->add_action_buttons();

        $mform->addElement('hidden', 'revision'); // Hard-coded as 1; should be changed if version becomes important.
        $mform->setType('revision', PARAM_INT);
        $mform->setDefault('revision', 1);
    }
}
