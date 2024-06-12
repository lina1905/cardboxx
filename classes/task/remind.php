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
namespace mod_cardboxx\task;

use core_user;

/**
 * This clas
 */
class remind extends \core\task\scheduled_task {

    /**
     * Function that is executed periodically according to the task schedule
     */
    public function execute() {
        global $DB, $SESSION;

        $sql = "SELECT cm.id, cm.course AS courseid, cm.id AS coursemoduleid, ca.name AS cardboxxname, co.fullname AS coursename "
                . "FROM {course_modules} cm "
                . "LEFT JOIN {modules} m ON cm.module = m.id "
                . "JOIN {cardboxx} ca ON cm.instance = ca.id "
                . "LEFT JOIN {course} co ON cm.course = co.id "
                . "WHERE m.name = ? AND ca.enablenotifications = 1";
        $cardboxxes = $DB->get_records_sql($sql, ['cardboxx']);

        foreach ($cardboxxes as $cardboxx) {
            $cardboxx->context = \context_module::instance($cardboxx->coursemoduleid);
            $recipients = get_enrolled_users($cardboxx->context, 'mod/cardboxx:practice');

            foreach ($recipients as $recipient) {
                $modinfo = get_fast_modinfo($cardboxx->courseid, $recipient->id);
                $cm = $modinfo->get_cm($cardboxx->coursemoduleid);
                $info = new \core_availability\info_module($cm);
                $information = '';
                if (!$info->is_available($information, false, $recipient->id)) {
                    continue;
                }

                // Change language temporarily.
                $course = $info->get_course();
                if (!empty($course->lang)) {
                    // Use course language if it's enforced.
                    $lang = $course->lang;
                } else {
                    // Use recipient's preferred language.
                    $lang = $recipient->lang;
                }
                $forcelangisset = isset($SESSION->forcelang);
                if ($forcelangisset) {
                    $forcelang = $SESSION->forcelang;
                }
                $SESSION->forcelang = $lang;

                $a = new \stdClass();
                $a->cardboxxname = format_string($cardboxx->cardboxxname);
                $a->coursename = format_string($cardboxx->coursename);

                $message = new \core\message\message();
                $message->component = 'mod_cardboxx';
                $message->name = 'memo';
                $message->userfrom = core_user::get_noreply_user();
                $message->userto = $recipient;
                $message->subject = get_string('remindersubject', 'cardboxx');
                $message->fullmessage = get_string('remindergreeting', 'cardboxx', $recipient->firstname).' '.
                                        get_string('remindermessagebody', 'cardboxx') . ' ' .
                                        get_string('reminderfooting', 'cardboxx', $a);
                $message->fullmessageformat = FORMAT_MARKDOWN;
                $message->fullmessagehtml = '<p>'.
                        get_string('remindergreeting', 'cardboxx', $recipient->firstname).
                        '</p><p>'.get_string('remindermessagebody', 'cardboxx').
                '</p><p><em>'.get_string('reminderfooting', 'cardboxx', $a) . '</em></p>';
                $message->smallmessage = 'small message';
                $message->notification = 1;
                $message->courseid = $cardboxx->courseid;

                message_send($message);

                // Reset language.
                if ($forcelangisset) {
                    $SESSION->forcelang = $forcelang;
                } else {
                    unset($SESSION->forcelang);
                }
            }

        }

    }

    /**
     * Function returns the name of the task as shown in admin screens
     */
    public function get_name(): string {
        return get_string('send_practice_reminders', 'cardboxx');
    }
}
