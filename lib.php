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
 * This is the lib page.
 *
 * @package   mod_cardboxx
 * @copyright 2019 RWTH Aachen (see README.md)
 * @author    Anna Heynkes
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
/**
 * This function checks if the module supports a given feature.
 *
 * @param string $feature The feature to check
 * @return bool|null True if the feature is supported, false if not, null if the feature is unknown
 */
function cardboxx_supports($feature) {
    switch($feature) {
        case FEATURE_MOD_INTRO:
            return true;
        case FEATURE_BACKUP_MOODLE2:
            return true;
        case FEATURE_SHOW_DESCRIPTION:
            return true;
        case FEATURE_MOD_PURPOSE:
            return MOD_PURPOSE_COLLABORATION;
        default:
            return null;
    }
}

/**
 * The cardboxx_add_instance function is passed the variables from the mod_form.php file
 * as an object when you first create an activity and click submit. This is where you can
 * take that data, do what you want with it and then insert it into the database if you wish.
 * This is only called once when the module instance is first created, so this is where you
 * should place the logic to add the activity.
 *
 * @param stdClass $data The data from the form
 * @param stdClass $mform The form instance
 * @return int The instance id of the new cardboxx instance
 */
function cardboxx_add_instance($data, $mform) {
    global $CFG, $DB;
    require_once("$CFG->libdir/resourcelib.php");
    $cmid = $data->coursemodule;
    $data->timecreated = time();
    $data->timemodified = time();
    cardboxx_set_display_options($data);

    $data->id = $DB->insert_record('cardboxx', $data);

    // We need to use context now, so we need to make sure all needed info is already in db.
    $DB->set_field('course_modules', 'instance', $data->id, ['id' => $cmid]);

    $completiontimeexpected = !empty($data->completionexpected) ? $data->completionexpected : null;
    \core_completion\api::update_completion_date_event($cmid, 'cardboxx', $data->id, $completiontimeexpected);

    return $data->id;
}
/**
 * The cardboxx_update_instance function is passed the variables from the mod_form.php file
 * as an object whenever you update an activity and click submit. The id of the instance you
 * are editing is passed as the attribute instance and can be used to edit any existing values
 * in the database for that instance.
 *
 * @param stdClass $cardboxx The data from the form
 * @return bool True if the instance was updated successfully
 */
function cardboxx_update_instance($cardboxx) {

    global $CFG, $DB;
    require_once("$CFG->libdir/resourcelib.php");
    $cardboxx->timemodified = time();
    $cardboxx->id = $cardboxx->instance;
    $cardboxx->revision++;

    cardboxx_set_display_options($cardboxx); // Can be deleted or extended.

    $DB->update_record('cardboxx', $cardboxx);

    $completiontimeexpected = !empty($cardboxx->completionexpected) ? $cardboxx->completionexpected : null;
    \core_completion\api::update_completion_date_event($cardboxx->coursemodule, 'cardboxx', $cardboxx->id, $completiontimeexpected);

    return true;

}
/**
 * The cardboxx__delete_instance function is passed the id of your module which you can use
 * to delete the records from any database tables associated with that id.
 *
 * @param int $cardboxxinstanceid
 */
function cardboxx_delete_instance($cardboxxinstanceid) {

    global $DB;

    if (!$cardboxx = $DB->get_record('cardboxx', ['id' => $cardboxxinstanceid])) {
        return false;
    }
    if (!$cm = get_coursemodule_from_instance('cardboxx', $cardboxxinstanceid)) {
        return false;
    }
    if (!$course = $DB->get_record('course', ['id' => $cm->course])) {
        return false;
    }

    \core_completion\api::update_completion_date_event($cm->id, 'cardboxx', $cardboxxinstanceid, null);

    // 1.1 Get all the cards of this cardboxx.
    $cards = $DB->get_records('cardboxx_cards', ['cardboxx' => $cardboxxinstanceid]);

    foreach ($cards as $card) {
        // 1.2 Delete all their contents.
        if (!$DB->delete_records('cardboxx_cardcontents', ['card' => $card->id]) == 1) {
            return false;
        }
        // 1.3 Delete their references in the students cardboxxes.
        if (!$DB->delete_records('cardboxx_progress', ['card' => $card->id]) == 1) {
            return false;
        }
    }

    // 1.4 Delete the cards themselves.
    if (!$DB->delete_records('cardboxx_cards', ['cardboxx' => $cardboxxinstanceid]) == 1) {
        return false;
    }

    // 2. Delete any topics affiliated with this cardboxx.
    if (!$DB->delete_records('cardboxx_topics', ['cardboxxid' => $cardboxxinstanceid]) == 1) {
        return false;
    }

    // 3. Delete the cardboxx instance from the cardboxx table of the plugin.
    if (!$DB->delete_records('cardboxx', ['id' => $cardboxxinstanceid]) == 1) {
        return false;
    }

    return true;

}

/**
 * Updates display options based on form input.
 *
 * Shared code used by pdfannotator_add_instance and pdfannotator_update_instance.
 * keep it, if you want defind more disply options
 * @param object $data Data object
 */
function cardboxx_set_display_options($data) {
    $displayoptions = [];
    $displayoptions['printintro'] = (int) !empty($data->printintro);
    $data->displayoptions = serialize($displayoptions);
}


/**
 * Serve the files from the MYPLUGIN file areas
 *
 * @param stdClass $course the course object
 * @param stdClass $cm the course module object
 * @param stdClass $context the context
 * @param string $filearea the name of the file area
 * @param array $args extra arguments (itemid, path)
 * @param bool $forcedownload whether or not force download
 * @param array $options additional options affecting the file serving
 * @return bool false if the file not found, just send the file otherwise and do not return anything
 */
function mod_cardboxx_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options=[]) {
    global $DB;
    // 1. Check the contextlevel is as expected - if your plugin is a block, this becomes CONTEXT_BLOCK, etc.
    if ($context->contextlevel != CONTEXT_MODULE) {
        return false;
    }
    // 2. Make sure the filearea is one of those used by the plugin.
    if ($filearea != 'content') {
        return false;
    }
    // 3. Make sure the user is logged in and has access to the module (plugins that are not course modules should leave
    // out the 'cm' part).
    // Disabled, so that students can see images in changenotification emails:

    // 4. Check the relevant capabilities - these may vary depending on the filearea being accessed.
    if (!has_capability('mod/cardboxx:view', $context)) {
        return false;
    }
    // 5. Leave this line out if you set the itemid to null in make_pluginfile_url (set $itemid to 0 instead).
    $itemid = (int)array_shift($args); // The first item in the $args array.
    // Use the itemid to retrieve any relevant data records and perform any security checks to see if the
    // user really does have access to the file in question.

    // 6. Extract the filename / filepath from the $args array.
    $filename = array_pop($args);
    if (empty($args)) {
        $filepath = '/';
    } else {
        $filepath = '/'.implode('/', $args).'/';
    }
    // 7. Retrieve the file from the Files API.
    $fs = get_file_storage();
    $file = $fs->get_file($context->id, 'mod_cardboxx', $filearea, $itemid, $filepath, $filename);
    if (!$file) {
        return false; // The file does not exist.
    }
    // 8. We can now send the file back to the browser - in this case with a cache lifetime of 1 day and no filtering.
    send_stored_file($file, 86400, 0, $forcedownload, $options);
}
