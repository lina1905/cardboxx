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
 * This page is used by Moodle when listing all the instances of the cardboxx module
 * that are in a particular course with the course id being passed to this script.
 *
 * @package   mod_cardboxx
 * @copyright 2019 RWTH Aachen (see README.md)
 * @author    Anna Heynkes
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
require_once($CFG->dirroot.'/mod/cardboxx/locallib.php');
require_once('model/cardcollection.class.php'); // Model.

// For this type of page this is the course id.
$id = required_param('id', PARAM_INT); // Course ID.

$courseid = $DB->get_record('course', ['id' => $id], '*', MUST_EXIST);
$course = get_course($courseid->id);
require_login($course);
$PAGE->set_url('/mod/cardboxx/index.php', ['id' => $id]);
$PAGE->set_pagelayout('incourse');

// Print the header.
$strplural = get_string("modulenameplural", "cardboxx");
$PAGE->navbar->add($strplural);
$PAGE->set_title($strplural);
$PAGE->set_heading($course->fullname);
echo $OUTPUT->header();
echo $OUTPUT->heading(format_string($strplural));

$context = context_course::instance($course->id);

require_capability('mod/cardboxx:view', $context);

$strplural = get_string('modulenameplural', 'cardboxx');
$usesections = course_format_uses_sections($course->format);
$modinfo = get_fast_modinfo($course);
if ($usesections) {
    $strsectionname = get_string('sectionname', 'format_'.$course->format);
    $sections = $modinfo->get_section_info_all();
}
$html = '<table class="generaltable" width="90%" cellspacing="1" cellpadding="5" text-align="center" ><thead>' . "\n";
$html .= '<tr><th class="header-c0  cbx-center-align" scope="col">'.get_string('choosetopic', 'cardboxx').'</th>';
$html .= '<th class="header-c1  cbx-center-align" scope="col">' . get_string('modulename', 'cardboxx') . '</th>';
$html .= '<th class="header-c2  cbx-center-align" scope="col"> '.ucfirst(get_string('barchartyaxislabel', 'cardboxx')).' </th>';
if (!has_capability('mod/cardboxx:practice', $context)) {
    $html .= '</tr></thead><tbody>';
} else {
    $html .= '<th class="header-c3  cbx-center-align" scope="col">'.ucfirst(get_string('lastpractise', 'cardboxx')).'</th>';
    $html .= '<th class="header-c4  cbx-center-align" scope="col">'.ucfirst(get_string('newcard', 'cardboxx')).'</th>';
    $html .= '<th class="header-c3  cbx-center-align" scope="col">'.ucfirst(get_string('knowncard', 'cardboxx')).'</th>';
    $html .= '<th class="header-c5  cbx-center-align" scope="col">'.ucfirst(get_string('flashcards', 'cardboxx').' '.
            get_string('flashcardsdue', 'cardboxx')).'</th>';
    $html .= '<th class="header-c6  cbx-center-align" scope="col">'.ucfirst(get_string('flashcards', 'cardboxx').' '.
            get_string('flashcardsnotdue', 'cardboxx')).'</th></tr></thead><tbody>';
}
foreach ($modinfo->instances['cardboxx'] as $cm) {
    if (!$cm->uservisible) {
        continue;
    }
    $sectionname = '';
    if ($usesections && $cm->sectionnum >= 0) {
        $sectionname = get_section_name($course, $sections[$cm->sectionnum]); // Gives the section name where the cardboxx is.
        if ($DB->record_exists('cardboxx_cards', ['cardboxx' => $cm->instance, 'approved' => '1'])) {
            // If cardboxx activity has cards.
            $html .= '<tr>';
            // Row begins with section and cardboxx activity name.
            $html .= '<td class="cell-c0  cbx-center-align" >'.$sectionname.'</td>';
            $html .= '<td class="cell-c1  cbx-center-align" >
                        <a href="'.$CFG->wwwroot.'/mod/cardboxx/view.php?id='.$cm->id.'">'.$cm->get_formatted_name().'</a></td>';
            // Number of cards in the cardboxx.
            $cardcount = $DB->count_records('cardboxx_cards', ['cardboxx' => $cm->instance, 'approved' => '1']);
            $html .= '<td class="cell-c2  cbx-center-align" >'.$cardcount.'</td>';
            if (has_capability('mod/cardboxx:practice', $context)) {
                // Last Practised column.
                $lastpractised = $DB->get_records_sql('SELECT max(lastpracticed) as lstprac
            FROM {cardboxx_progress} cbp
            WHERE cbp.card in (SELECT id from {cardboxx_cards} cc
                                WHERE cc.cardboxx = :cardboxx and approved = :approved)
                                AND cbp.userid = :userid',
                                ['cardboxx' => $cm->instance, 'userid' => $USER->id, 'approved' => '1']);
                if (implode(',', array_keys($lastpractised)) == '') {
                    $html .= '<td class="cell-c3  cbx-center-align">'.get_string('nopractise', 'cardboxx').'</td>';
                } else {
                    $html .= '<td class="cell-c3  cbx-center-align">'.userdate(implode(',', array_keys($lastpractised)),
                                                                        get_string('strftimerecent')).'</td>';
                }
                // Card Status columns.
                $due = 0;
                $notdue = 0;
                require_once('model/cardboxx.class.php');
                require_once('model/card_selection_algorithm.php');
                $select = new cardboxx_card_selection_algorithm(null, true);
                $cardboxxmodel = new cardboxx_cardboxxmodel($cm->instance, $select);
                $boxcount = $cardboxxmodel->cardboxx_get_status();
                // New cards.
                $html .= '<td class="cell-c4  cbx-center-align">'.$boxcount[0].'</td>';
                // Mastered cards.
                $html .= '<td class="cell-c5  cbx-center-align">'.$boxcount[6].'</td>';
                // Due  and Not Due cards.
                for ($i = 1; $i <= 5; $i++) {
                    $due += $boxcount[$i]['due'];
                    $notdue += $boxcount[$i]['notdue'];
                }
                $html .= '<td class="cell-c6  cbx-center-align">'.$due.'</td>';
                $html .= '<td class="cell-c7  cbx-center-align">'.$notdue.'</td>';
            } else {
                echo "<span class='notification alert alert-danger alert-block fade in' role='alert'
                        style='display:block'>Something went wrong </span>";
            }
        } else {
            $html .= '<tr>';
            $html .= '<td class="cell-c0  cbx-center-align">'.$sectionname.'</td>';
            $html .= '<td class="cell-c1  cbx-center-align"><a href="'.$CFG->wwwroot.'/mod/cardboxx/view.php?id='.$cm->id.'">'.
            $cm->get_formatted_name().'</a></td>';
            if (has_capability('mod/cardboxx:practice', $context)) {
                for ($i = 2; $i <= 7; $i++) {
                    $html .= '<td class="cell-c'.$i.'  cbx-center-align">--</td>';
                }
            } else {
                $html .= '<td class="cell-c2  cbx-center-align">--</td>';
            }
            $html .= '</tr>';
        }
    }
}


$html .= '</tbody></table>';
echo $html;
echo $OUTPUT->footer();
