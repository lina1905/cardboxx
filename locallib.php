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
 * This file is used when adding/editing a flashcard to a cardboxx.
 *
 * @package   mod_cardboxx
 * @copyright 2019 RWTH Aachen (see README.md)
 * @author    Anna Heynkes
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('cardboxx_EVALUATE_ALL', 0);
define('cardboxx_EVALUATE_ONE', 1);
define('CARD_MAIN_INFORMATION', 0);
define('CARD_CONTEXT_INFORMATION', 1);
define('CARD_IMAGEDESCRIPTION_INFORMATION', 2);
define('CARD_ANSWERSUGGESTION_INFORMATION', 3);
define('cardboxx_CARDSIDE_QUESTION', 0);
define('cardboxx_CARDSIDE_ANSWER', 1);
define('cardboxx_CONTENTTYPE_IMAGE', 0);
define('cardboxx_CONTENTTYPE_TEXT', 1);
define('cardboxx_CONTENTTYPE_AUDIO', 2);
define ('LONG_DESCRIPTION', 1);
define ('SHORT_DESCRIPTION', 0);

/**
 * Function creates a new record in cardboxx_topics table.
 *
 * @param string $topicname The name of the topic
 * @param int $cardboxxid The id of the cardboxx
 * @return int id of the new topic
 */
function cardboxx_save_new_topic($topicname, $cardboxxid) {

    global $DB;
    $topic = new stdClass();
    $topic->topicname = $topicname;
    $topic->cardboxxid = $cardboxxid;

    return $DB->insert_record('cardboxx_topics', $topic, true);

}
/**
 * Function returns an array of options for the 'select/create a topic' dropdown
 * in the card_form.
 *
 * @param int $cardboxxid The id of the cardboxx
 * @param bool $extra The extra status
 * @return array The options for the dropdown
 */
function cardboxx_get_topics($cardboxxid, $extra = false) {

    global $DB;
    $topics = $DB->get_records('cardboxx_topics', ['cardboxxid' => $cardboxxid]);
    $options = [-1 => get_string('notopic', 'cardboxx')];
    if ($extra) {
        $options = [-1 => get_string('notopic', 'cardboxx'), 0 => get_string('addnewtopic', 'cardboxx')];
    } else {
        $options = [-1 => get_string('notopicpreferred', 'cardboxx')];
    }
    foreach ($topics as $topic) {
        $options[$topic->id] = $topic->topicname;
    }
    return $options;
}

/**
 * Function creates a new record in cardboxx_cards table.
 *
 * @param int $cardboxxid The id of the cardboxx
 * @param string $context The context of the card
 * @param string|null $submitbutton The submit button status
 * @param int|null $topicid The id of the topic
 * @param int $necessaryanswers The necessary answers count
 * @param int $disableautocorrect The disable autocorrect status
 * @return int The id of the new card
 */
function cardboxx_save_new_card($cardboxxid, $context, $submitbutton = null, $topicid = null, $necessaryanswers = 0,
                               $disableautocorrect = 0) {

    global $DB, $USER;

    $cardrecord = new stdClass();
    $cardrecord->cardboxx = $cardboxxid;
    $cardrecord->topic = $topicid;
    $cardrecord->author = $USER->id;
    $cardrecord->timecreated = time();
    $cardrecord->timemodified = null;
    if (!empty($submitbutton) && $submitbutton == get_string('saveandaccept', 'cardboxx')
        && has_capability('mod/cardboxx:approvecard', $context)) {
        $cardrecord->approved = 1;
        $cardrecord->approvedby = $USER->id;
    } else {
        $cardrecord->approved = 0;
        $cardrecord->approvedby = null;
    }
    $cardrecord->necessaryanswers = $necessaryanswers;
    $cardrecord->disableautocorrect = $disableautocorrect;
    $cardid = $DB->insert_record('cardboxx_cards', $cardrecord, true, false);

    return $cardid;

}
/**
 * Function creates a new record in cardboxx_cardcontents table.
 *
 * @param int $cardid The id of the card
 * @param int $cardside The side of the card
 * @param int $contenttype The type of the content
 * @param string $name The name of the content
 * @param int $area The area of the content
 * @return int The id of the new card content
 */
function cardboxx_save_new_cardcontent($cardid, $cardside, $contenttype, $name, $area = 0) {

    global $DB;

    $cardcontent = new stdClass();
    $cardcontent->card = $cardid;
    $cardcontent->cardside = $cardside;
    $cardcontent->contenttype = $contenttype;
    $cardcontent->area = $area;
    $cardcontent->content = $name;
    $itemid = $DB->insert_record('cardboxx_cardcontents', $cardcontent, true);

    return $itemid;

}
/**
 * Function updates a cardcontent record in cardboxx_cardcontents table.
 *
 * @param int $cardid
 * @param int $cardside
 * @param int $contenttype
 * @param string $name
 * @return int
 */
function cardboxx_update_cardcontent($cardid, $cardside, $contenttype, $name) {

    global $DB;

    $existsalready = $DB->record_exists('cardboxx_cardcontents', ['card' => $cardid, 'cardside' => $cardside,
        'contenttype' => $contenttype]);

}


/**
 * Function updates a card that was edited via the card_form.
 *
 * @param int $cardid The id of the card
 * @param int $topicid The id of the topic
 * @param object $context The context of the card
 * @param int $necessaryanswers The necessary answers count
 * @param int $disableautocorrect The disable autocorrect status
 * @param string|null $submitbutton The submit button status
 * @return bool whether or not the update was successful
 */
function cardboxx_edit_card($cardid, $topicid, $context, $necessaryanswers, $disableautocorrect, $submitbutton = null) {

    global $DB, $USER;

    $record = new stdClass();
    $record->id = $cardid;
    $record->topic = $topicid;
    $record->timemodified = time();

    if (!empty($submitbutton) && $submitbutton == get_string('saveandaccept', 'cardboxx')
        && has_capability('mod/cardboxx:approvecard', $context)) {
        $record->approved = 1;
        $record->approvedby = $USER->id;
    }

    $record->necessaryanswers = $necessaryanswers;
    $record->disableautocorrect = $disableautocorrect;
    $success = $DB->update_record('cardboxx_cards', $record);

    if (empty($success)) {
        return false;
    }

    $success = $DB->delete_records('cardboxx_cardcontents', ['card' => $cardid]);

    return $success;

}
/**
 * Function deletes a card, its contents and topic.
 *
 * @param int $cardid
 * @return boolean
 */
function cardboxx_delete_card($cardid) {

    global $DB;

    // Check whether the card exists.
    $card = $DB->get_record('cardboxx_cards', ['id' => $cardid], '*', MUST_EXIST);

    if (empty($card)) {
        return false;
    }

    // Delete its contents.
    $success = $DB->delete_records('cardboxx_cardcontents', ['card' => $cardid]);

    if (empty($success)) {
        return false;
    }

    // Delete its topic if no other card uses it.
    if (!empty($card->topic)) {
        $count = $DB->count_records('cardboxx_cards', ['topic' => $card->topic]);
        if ($count == 1) {
            $DB->delete_records('cardboxx_topics', ['id' => $card->topic]);
        }
    }

    // Delete the card itself.
    return $DB->delete_records('cardboxx_cards', ['id' => $cardid]);

}

/**
 * This function checks whether there are new cards available in the DB
 * and if so, adds them to the users virtual cardboxx system.
 *
 * @param int $cardboxxid The id of the cardboxx
 * @param int $topic The topic of the card
 * @return bool|null The success status of adding new cards
 */
function cardboxx_add_new_cards($cardboxxid, $topic) {

    global $DB, $USER;

    $sql2 = "SELECT c.id"
            . " FROM {cardboxx_cards} c"
            . " WHERE c.cardboxx = :cbid AND c.approved = :appr"
            . " AND NOT EXISTS (SELECT card FROM {cardboxx_progress} p WHERE p.userid = :uid AND p.card = c.id)";
    $params = ['cbid' => $cardboxxid, 'appr' => '1', 'uid' => $USER->id];
    $newcards = $DB->get_fieldset_sql($sql2, $params);

    if (empty($newcards)) {
        return;
    }

    if ($topic != -1) {
        $cards = [];
        foreach ($newcards as $card) {
            if ($DB->get_record_select('cardboxx_cards', 'id =' . $card->id, null, 'topic') === $topic) {
                $cards[] = $card;
            }
        }
        $newcards = $cards;
    }

    $dataobjects = [];
    foreach ($newcards as $cardid) {
        $dataobjects[] = ['userid' => $USER->id, 'card' => $cardid, 'cardposition' => 0,
            'lastpracticed' => null, 'repetitions' => 0];
    }
    $success = $DB->insert_records('cardboxx_progress', $dataobjects);
    return $success;

}
/**
 * Function returns the download URL for the specified context and item id.
 *
 * @param object $context The context of the card
 * @param int $itemid The id of the item
 * @param string|null $filename The name of the file (optional)
 * @return string The download URL
 */
function cardboxx_get_download_url($context, $itemid, $filename = null) {

    $fs = get_file_storage();

    $files = $fs->get_area_files($context->id, 'mod_cardboxx', 'content', $itemid, 'sortorder', false);

    foreach ($files as $file) { // Find better solution than foreach to get the first and only element.
        $fileurl = moodle_url::make_pluginfile_url($file->get_contextid(), $file->get_component(), $file->get_filearea(),
                                                   $file->get_itemid(), $file->get_filepath(), $file->get_filename());
        $downloadurl = $fileurl->get_port() ? $fileurl->get_scheme() . '://' . $fileurl->get_host() . $fileurl->get_path() .
            ':' . $fileurl->get_port() : $fileurl->get_scheme() . '://' . $fileurl->get_host() . $fileurl->get_path();
        return $downloadurl;
    }

}
/**
 * Function returns the topic of the card, if a topic was selected.
 *
 * @param int $cardid
 * @return int
 */
function cardboxx_get_topic($cardid) {

    global $DB;

    $topic = $DB->get_field('cardboxx_cards', 'topic', ['id' => $cardid], IGNORE_MISSING);

    if (empty($topic)) {
        $topic = -1; // No topic selected.
    }

    return $topic;

}
/**
 * Function returns the amount of necessary answers of the card.
 *
 * @param int $cardid
 * @return int
 */
function cardboxx_get_necessaryanswers($cardid) {
    global $DB;

    $necessaryanswers = $DB->get_field('cardboxx_cards', 'necessaryanswers', ['id' => $cardid], IGNORE_MISSING);

    return $necessaryanswers;

}
/**
 * Function returns the question text (if there is one) of the specified card.
 *
 * @param int $cardid
 * @return string
 */
function cardboxx_get_questiontext($cardid) {

    global $DB;
    $questiontext = $DB->get_field('cardboxx_cardcontents', 'content',
        ['card' => $cardid, 'cardside' => cardboxx_CARDSIDE_QUESTION, 'contenttype' => cardboxx_CONTENTTYPE_TEXT,
        'area' => CARD_MAIN_INFORMATION], IGNORE_MISSING);
    if (empty($questiontext)) {
        $questiontext = '';
    }
    return $questiontext;
}

/**
 * Function returns an array of answer items belonging to the specified card.
 *
 * @param int $cardid The id of the card
 * @return array The array of answer items
 */
function cardboxx_get_answers($cardid) {
    global $DB;
    return $DB->get_fieldset_select('cardboxx_cardcontents', 'content',
        'card = :cardid AND cardside = :cardside AND contenttype = :contenttype AND area = :area',
        ['cardid' => $cardid, 'cardside' => cardboxx_CARDSIDE_ANSWER, 'contenttype' => cardboxx_CONTENTTYPE_TEXT,
        'area' => CARD_MAIN_INFORMATION]);
}

/**
 * Function returns 1...n answer items belonging to the specified card.
 *
 * @param int $cardid The id of the card
 * @return array The array of not approved answer items
 */
function cardboxx_get_notapproved_answers($cardid) {
    global $DB;
    return $DB->get_fieldset_select('cardboxx_cardcontents', 'content',
        'card = :cardid AND cardside = :cardside AND contenttype = :contenttype AND area = :area',
        ['cardid' => $cardid, 'cardside' => cardboxx_CARDSIDE_ANSWER, 'contenttype' => cardboxx_CONTENTTYPE_TEXT,
        'area' => CARD_ANSWERSUGGESTION_INFORMATION]);
}

/**
 * Function returns the context belonging to the specified question if set.
 *
 * @param int $cardid The id of the card
 * @return string The context of the question
 */
function cardboxx_get_questioncontext($cardid) {

    global $DB;
    $context = $DB->get_field('cardboxx_cardcontents', 'content', ['card' => $cardid, 'cardside' => cardboxx_CARDSIDE_QUESTION,
     'area' => CARD_CONTEXT_INFORMATION], IGNORE_MISSING);
    if (empty($context)) {
        $context = '';
    }
    return $context;

}

/**
 * Function returns the context belonging to the specified answer if set.
 *
 * @param int $cardid The id of the card
 * @return string The context of the answer
 */
function cardboxx_get_answercontext($cardid) {

    global $DB;
    $context = $DB->get_field('cardboxx_cardcontents', 'content', ['card' => $cardid, 'cardside' => cardboxx_CARDSIDE_ANSWER,
     'area' => CARD_CONTEXT_INFORMATION], IGNORE_MISSING);
    if (empty($context)) {
        $context = '';
    }
    return $context;

}

/**
 * Function returns the status belonging to the specified card.
 *
 * @param int $cardid The id of the card
 * @param int $userid The id of the user
 * @return string The status of the card
 */
function cardboxx_get_status($cardid, $userid) {

    global $DB;
    $status = $DB->get_field('cardboxx_progress', 'cardposition', ['card' => $cardid, 'userid' => $userid], IGNORE_MISSING);
    if ($status === "0" || $status === false) {
        $status = get_string('newcard', 'cardboxx');
    }
    if ($status === "6") {
        $status = get_string('knowncard', 'cardboxx');
    }
    return $status;

}

/**
 * Function returns true if the specified card card is approved.
 *

 * @param int $cardid The id of the card
 * @return bool The approval status of the card
 */
function cardboxx_card_approved($cardid) {

    global $DB;
    $status = $DB->get_field('cardboxx_cards', 'approved', ['id' => $cardid], IGNORE_MISSING);
    if ($status === "0") {
        return false;
    } else {
        return true;
    }
}

/**
 * This function returns the number of cards in the cardboxx.
 *
 * @param int $cardboxxid The id of the cardboxx
 * @return array The number of cards in each deck
 */
function cardboxx_get_absolute_cardcounts_per_deck($cardboxxid) {
    global $DB;
    $cardsperdeck = $DB->get_records_sql(
                        'SELECT cardposition, count(card) AS cardcount
                        FROM {cardboxx_progress}
                        where card in (select id from {cardboxx_cards} where cardboxx = :cardboxxid) GROUP by cardposition',
                        ['cardboxxid' => $cardboxxid]);
    $cardsperdeck = array_column($cardsperdeck, 'cardcount', 'cardposition');

    for ($i = 0; $i < 7; ++$i) {
        if (!array_key_exists($i, $cardsperdeck)) {
            $cardsperdeck[$i] = 0;
        }
    }
    return $cardsperdeck;
}
/**
 * This function returns the average number of cards in the cardboxx.
 *
 * @param int $cardboxxid The id of the cardboxx
 * @return array The average number of cards in each deck
 */
function cardboxx_get_average_cardcounts_per_deck($cardboxxid) {
    global $DB;
    $absolutes = cardboxx_get_absolute_cardcounts_per_deck($cardboxxid);
    $practisingstudentcount = $DB->count_records_sql(
                                'SELECT count(distinct userid)
                                FROM {cardboxx_progress}
                                where card in (select id from {cardboxx_cards} where cardboxx = :cardboxxid)',
                                ['cardboxxid' => $cardboxxid]);
    $averages = [];
    foreach ($absolutes as $position => $absolute) {
        $averages[$position] = $absolute / $practisingstudentcount;
    }
    return $averages;
}

/**
 * Function returns 0...1 image item ids belonging to the specified card.
 *
 * @param int $cardid The id of the card
 * @return int The id of the image item
 */
function cardboxx_get_image_itemid($cardid) {

    global $DB;
    $imageitemid = $DB->get_field('cardboxx_cardcontents', 'id',
        ['card' => $cardid, 'contenttype' => cardboxx_CONTENTTYPE_IMAGE], IGNORE_MISSING);
    return $imageitemid;

}
/**
 * Function returns the imagedescription belonging to the specified image if set.
 *
 * @param int $cardid The id of the card
 * @return string The description of the image
 */
function cardboxx_get_imagedescription($cardid) {

    global $DB;
    $imagedescription = $DB->get_field('cardboxx_cardcontents', 'content',
        ['card' => $cardid, 'cardside' => cardboxx_CARDSIDE_QUESTION,
     'area' => CARD_IMAGEDESCRIPTION_INFORMATION], IGNORE_MISSING);
    if (empty($imagedescription)) {
        $imagedescription = '';
    }
    return $imagedescription;

}
/**
 * Function converts the timestamp into a human readable format (D. M Y),
 * taking the user's timezone into account.
 *
 * @param int $timestamp The timestamp to convert
 * @return string The converted date
 */
function cardboxx_get_user_date($timestamp) {
    return userdate($timestamp, get_string('strftimedate', 'cardboxx'), $timezone = 99, $fixday = true, $fixhour = true);
    // Method in lib/moodlelib.php.
}

/**
 * Function converts the timestamp into a human readable format (D. M),
 * taking the user's timezone into account.
 *
 * @param int $timestamp The timestamp to convert
 * @return string The converted date
 */
function cardboxx_get_user_date_short($timestamp) {
    return userdate($timestamp, get_string('strftimedateshortmonthabbr', 'cardboxx'),
        $timezone = 99, $fixday = true, $fixhour = true); // Method in lib/moodlelib.php.
}

/**
 * Function converts the timestamp into a human readable format,
 * taking the user's timezone into account.
 *
 * @param int $timestamp The timestamp to convert
 * @return string The converted date and time
 */
function cardboxx_get_user_datetime_shortformat($timestamp) {
    $shortformat = get_string('strftimedatetime', 'cardboxx'); // Format strings in moodle\lang\en\langconfig.php.
    $userdatetime = userdate($timestamp, $shortformat, $timezone = 99, $fixday = true, $fixhour = true);
    // Method in lib/moodlelib.php.
    return $userdatetime;
}
/**
 * This function checks whether a card is due.
 *
 * @param object $carddata The data of the card
 * @return bool Whether the card is due or not
 */
function cardboxx_is_card_due($carddata) {

    if ($carddata->cardposition == 0) {
        return true;
    } else if ($carddata->cardposition > 5) {
        return false;
    }

    $now = new DateTime("now");

    $spacing = [];
    $spacing[1] = new DateInterval('P1D');
    $spacing[2] = new DateInterval('P2D');
    $spacing[3] = new DateInterval('P4D');
    $spacing[4] = new DateInterval('P8D');
    $spacing[5] = new DateInterval('P16D');

    $last = new DateTime("@$carddata->lastpracticed");
    $interval = $spacing[$carddata->cardposition];
    $due = $last->add($interval);

    if ($due > $now) {
        return false;
    } else {
        return true;
    }
}
/**
 * This function updates the progress of a card.
 *
 * @param object $dataobject The data object of the card
 * @param bool $iscorrect Whether the card was answered correctly or not
 * @return bool The success status of the update
 */
function cardboxx_update_card_progress($dataobject, $iscorrect) {

    global $DB;

    // Cards that were answered correctly proceed.
    if ($iscorrect == 1) {

        // New cards proceed straight to box two.
        if ($dataobject->cardposition == 0) {
            $dataobject->cardposition = 2;
        } else {
            // Other cards proceed to the next box.
            $dataobject->cardposition = $dataobject->cardposition + 1;
        }
    } else {
        // Cards that were not answered correctly go back to box one or stay there.
        $dataobject->cardposition = 1;
    }

    $dataobject->lastpracticed = time();
    $dataobject->repetitions = $dataobject->repetitions + 1;

    $success = $DB->update_record('cardboxx_progress', $dataobject, false);

    return $success;
}

/**
 * This function sends system and/or email notifications to
 * inform students that an already approved card was edited.
 *
 * @param int $cmid The course module id
 * @param object $cardboxx The cardboxx object
 * @param int $cardid The id of the card
 */
function cardboxx_send_change_notification($cmid, $cardboxx, $cardid) {

    global $CFG, $DB, $PAGE;
    require_once($CFG->dirroot . '/mod/cardboxx/classes/output/overview.php');

    $context = context_module::instance($cmid);

    $sm = get_string_manager();

    $topicid = $DB->get_field('cardboxx_cards', 'topic', ['id' => $cardid], MUST_EXIST);
    $renderer = $PAGE->get_renderer('mod_cardboxx');
    $overview = new cardboxx_overview([$cardid], 0, $context, $cmid, $cardid, $topicid, true, $sort, $deck);

    $recipients = get_enrolled_users($context, 'mod/cardboxx:practice');

    foreach ($recipients as $recipient) {
        $modinfo = get_fast_modinfo($cardboxx->course, $recipient->id);
        $cm = $modinfo->get_cm($cmid);
        $info = new \core_availability\info_module($cm);
        $information = '';
        if (!$info->is_available($information, false, $recipient->id)) {
            continue;
        }
        $message = new \core\message\message();
        $message->component = 'mod_cardboxx';
        $message->name = 'changenotification';
        $message->userfrom = core_user::get_noreply_user();
        $message->userto = $recipient;
        $message->subject = $sm->get_string('changenotification:subject', 'cardboxx', null, $recipient->lang);
        $message->fullmessage = $sm->get_string('changenotification:message', 'cardboxx', null, $recipient->lang) .
            '<br>' . $renderer->cardboxx_render_overview($overview);
        $message->fullmessageformat = FORMAT_MARKDOWN;
        $message->fullmessagehtml = $sm->get_string('changenotification:message', 'cardboxx', null, $recipient->lang) .
            '<br>' . $renderer->cardboxx_render_overview($overview);
        $message->smallmessage = 'small message';
        $message->notification = 1; // For personal messages '0'. Important: the 1 without '' and 0 with ''.
        $message->courseid = $cardboxx->course;

        message_send($message);

    }

}
/**
 * This function imports cards from a CSV file.
 *
 * @param \csv_import_reader $cir The CSV import reader
 * @param array $columns The columns of the CSV file
 * @param int $cardboxxid The id of the cardboxx
 * @return array The lines with errors
 */
function cardboxx_import_cards(\csv_import_reader $cir, array $columns, int $cardboxxid) {
    global $DB, $USER;
    $topiccache = [];
    $i = 1;
    $j = 0;
    $errorlines = [];
    while ($line = $cir->next()) {
        $errors = [];
        $atleastoneanswer = 0;
        $rowcols = [];
        $rowcols['line'] = $i;
        foreach ($line as $key => $field) {
            $rowcols[$columns[$key]] = s(trim($field));
        }
        $errors = cardboxx_import_validate_row($atleastoneanswer, $rowcols);
        if (empty($errors)) {
            $card = new stdClass;
            $card->topic = null;
            if ($topicidx = array_search('topic', $columns)) {
                $topic = trim($line[$topicidx]);
                if (array_key_exists($topic, $topiccache)) {
                    $card->topic = $topiccache[$topic];
                } else {
                    if (!empty($topic) && $topic != "null" ) {
                        if (!$DB->record_exists('cardboxx_topics', ['topicname' => $topic, 'cardboxxid' => $cardboxxid])) {
                            $card->topic = $DB->insert_record('cardboxx_topics',
                                ['topicname' => $topic, 'cardboxxid' => $cardboxxid], true);
                        } else {
                            $card->topic = $DB->get_field("cardboxx_topics", "id",
                                ["topicname" => $topic, 'cardboxxid' => $cardboxxid]);
                        }
                    }
                    $topiccache[$topic] = $card->topic;
                }
            }
            $card->cardboxx = $cardboxxid;
            $card->author = $USER->id;
            $card->timecreated = time();
            $card->approved = '1';
            $card->approvedby = $USER->id;
            $card->necessaryanswers = '0';
            if ($disableautocorrect = array_search('acdisable', $columns)) {
                $card->disableautocorrect = trim($line[$disableautocorrect]);
            } else {
                $card->disableautocorrect = '0';
            }
            $cardid = $DB->insert_record('cardboxx_cards', $card, true); // New row in cardboxx_cards table created.
            $cardcontent = new stdClass;
            foreach ($line as $key => $value) {
                $value = trim($value);
                if ($value !== "") {
                    // Common to all content.
                    $cardcontent->card = $cardid;
                    $cardcontent->contenttype = cardboxx_CONTENTTYPE_TEXT;
                    $cardcontent->content = '<p>'.$value.'</p>';
                    // Based on which info it is, create DB records
                    // ques : This is the main question
                    // ans : This is the main answer. Multiple answer not supported yet
                    // qcontext: This is the context info for question
                    // acontext: This is the context info for answer.
                    $columnname = $columns[$key];
                    if ($columnname == 'ques') {
                        $cardcontent->cardside = cardboxx_CARDSIDE_QUESTION;
                        $cardcontent->area = CARD_MAIN_INFORMATION;
                    } else if (preg_match('/^ans[0-9]*$/', $columnname)) {
                        $cardcontent->cardside = cardboxx_CARDSIDE_ANSWER;
                        $cardcontent->area = CARD_MAIN_INFORMATION;
                    } else if ($columnname == 'qcontext') {
                        $cardcontent->cardside = cardboxx_CARDSIDE_QUESTION;
                        $cardcontent->area = CARD_CONTEXT_INFORMATION;
                    } else if ($columnname == 'acontext') {
                        $cardcontent->cardside = cardboxx_CARDSIDE_ANSWER;
                        $cardcontent->area = CARD_CONTEXT_INFORMATION;
                    } else {
                        continue;
                    }
                    $cardcontent->id = $DB->insert_record('cardboxx_cardcontents', $cardcontent, true);
                }
            }
        } else {
            $status = "";
            foreach ($errors as $error) {
                $status .= $error;
            }
            $rowcols['status'] = $status;
            $errorlines[$j] = $rowcols;
            $j++;
        }
        $i++;
    }
    return $errorlines;
}
/**
 * This function validates the columns of the uploaded CSV file.
 *
 * @param array $filecolumns
 * @param int $descriptiontype
 * @return array
 */
function cardboxx_import_validate_columns(array $filecolumns, int $descriptiontype) {
    $errors = [];
    $processed = [];
    $filecolumns = array_map('strtolower', $filecolumns);
    if (empty($filecolumns)) {
        $errors[] = get_string('cannotreadtmpfile', 'error');
    }
    if (count($filecolumns) < 2) {
        $errors[] = get_string('csvfewcolumns', 'error');
    }
    if (!in_array('ques', $filecolumns)) {
        $errors[] = 'ERR: '.get_string('qfieldmissing', 'cardboxx');
    }
    if (!in_array('ans', $filecolumns) && empty(preg_grep('/^ans[0-9]*$/', $filecolumns))) {
        $errors[] = 'ERR: '.get_string('afieldmissing', 'cardboxx');
    }
    $allowed = ['ques', 'ans', 'acontext', 'qcontext', 'topic', 'acdisable'];
    $allowedwithmeaning = [
        'ques' => get_string('ques', 'cardboxx'),
        'ans' => get_string('ans', 'cardboxx'),
        'acontext' => get_string('acontext', 'cardboxx'),
        'qcontext' => get_string('qcontext', 'cardboxx'),
        'topic' => get_string('topic', 'cardboxx'),
        'acdisable' => get_string('acdisable', 'cardboxx'),
    ];
    foreach ($filecolumns as $key => $column) {
        if (cardboxx_string_starts_with($column, 'ans')) { // Replace with str_starts_with in PHP 8.0.
            array_push($allowed, $column);
        }
    }
    foreach ($filecolumns as $filecolumn) {
        if (in_array($filecolumn, $allowed) ) {
            if (!in_array($filecolumn, $processed)) {
                array_push($processed, $filecolumn);
            } else if (in_array($filecolumn, $processed)) {
                $errors[] = get_string('duplicatefieldname', 'error', $filecolumn);
            }
        } else {
            if ($descriptiontype == LONG_DESCRIPTION) {
                $errstr = get_string('invalidfieldname', 'error', $filecolumn).'<br><ul>';
                /*
                foreach ($allowedwithmeaning as $shortname => $meaning) {
                    $errstr .= '<li><b>'.$shortname.'</b> => '.$meaning.'</li>';
                }
                */
                $errstr .= '</ul>';
                $errors[] = $errstr;
            } else {
                $errors[] = get_string('invalidfieldname', 'error', $filecolumn);
            }

        }
    }
    return [$errors];
}
/**
 * This function validates the rows of the uploaded CSV file.
 *
 * @param int $atleastoneanswer
 * @param array $rowcols
 * @return array
 */
function cardboxx_import_validate_row(int $atleastoneanswer, array $rowcols) {
    $matches  = preg_grep ('/^ans[0-9]*$/', array_keys($rowcols));
    $errors = [];
    foreach ($matches as $match) {
        if (!is_null($rowcols[$match])) {
            if (!($rowcols[$match] == "")) {
                $atleastoneanswer++;
            }
        }
    }
    if (is_null($rowcols['ques']) || $rowcols['ques'] == "") {
        $errors[] = get_string('qmissing', 'cardboxx');
    }
    if ($atleastoneanswer == 0) {
        $errors[] = get_string('amissing', 'cardboxx');
    }
    return $errors;
}
/**
 * This function checks if a string starts with a specific value.
 *
 * @param string $fullvalue The full string
 * @param string $searchvalue The value to search for
 * @return bool Whether the string starts with the search value or not
 */
function cardboxx_string_starts_with($fullvalue, $searchvalue) {
    return substr_compare($fullvalue, $searchvalue, 0, strlen($searchvalue)) === 0;
}
