<?php
namespace mod_cardboxx\output;

defined('MOODLE_INTERNAL') || die();

class mobile {
    public static function mobile_view_practice_start($args) {
        global $OUTPUT, $USER, $CFG, $DB;
        // Include necessary files

        require_once($CFG->dirroot . '/mod/cardboxx/lib.php');
        require_once($CFG->dirroot . '/mod/cardboxx/locallib.php');

        require_once($CFG->dirroot . '/mod/cardboxx/model/cardboxx.class.php');
        require_once($CFG->dirroot . '/mod/cardboxx/model/card_selection_algorithm.php');
        require_once($CFG->dirroot . '/mod/cardboxx/model/card_sorting_algorithm.php');
        require_once($CFG->dirroot . '/mod/cardboxx/locallib.php');
        require_once($CFG->dirroot . '/mod/cardboxx/classes/output/start.php');


        // Convert arguments to an object
        $args = (object) $args;
        $cmid = $args->cmid;

        list ($course, $cm) = get_course_and_cm_from_cmid($cmid, 'cardboxx');
        $cardboxx = $DB->get_record('cardboxx', ['id' => $cm->instance], '*', MUST_EXIST);

        require_login($course, true, $cm);

        $cardboxx->revision = 1;



        // Handle optional parameters
        // $cmid = $args->cmid;
        //$startnow = optional_param('start', false, PARAM_BOOL);
        //$correction = optional_param('mode', 0, PARAM_INT); // Automatic check against solution (default) or self check.
        //$topic = optional_param('topic', null, PARAM_INT); // Topic to prioritize.
        //$onlyonetopic = optional_param('onlyonetopic', -1, PARAM_INT); // Topic to study.
        //$practiceall = optional_param('practiceall', true, PARAM_BOOL);
        //$amountcards = optional_param('amountcards', 0, PARAM_INT); // Topic to prioritize.
        //$openmodal = true;

        $topic = null;
        $practiceall = true;
        $onlyonetopic = -1;
        $amountcards = 0;

        // Use the ID from the URL (54 in this case)
        $cardboxxid = 1;
        // Instantiate the cardboxx model class
        $select = new \cardboxx_card_selection_algorithm($topic, $practiceall, $onlyonetopic);
        $sort = new \cardboxx_card_sorting_algorithm();
        $cardboxxmodel = new \cardboxx_cardboxxmodel($cardboxx->id, null, null, $onlyonetopic);

        // Fetch the card count and due card count
        $cardcount = $cardboxxmodel->cardboxx_count_cards();
        $duecardcount = $cardboxxmodel->cardboxx_count_due_cards();

        //$selection = $cardboxxmodel->cardboxx_get_card_selection($amountcards);
        //$totalcards = is_array($selection) ? count($selection) : 0;



        /*
        $autocorrection = 1;
        $cardboxxid = 54;

        // Instanziiere die cardboxx_start Klasse, um auf $duecardcount und $cardcount zuzugreifen.
        $cardboxx_start_instance = new \cardboxx_start($autocorrection, $cardboxxid);
        $duecardcount = $cardboxx_start_instance->duecardcount;
        $cardcount = $cardboxx_start_instance->cardcount;
        */

        // Prepare data for the mobile app
        $data = [];
        $data['cardcount'] = $cardcount;
        $data['duecardcount'] = $duecardcount;
        $data['duecardcountpercentage'] = round(($data['duecardcount'] / $data['cardcount']) * 100, 0);
        // Additional data for the template
        $data['case1'] = true;
        $data['case2'] = false;

        return [
            'templates' => [
                [
                    'id' => 'main',
                    'html' => $OUTPUT->render_from_template('mod_cardboxx/mobile_practice_start', $data),
                ],
            ],
            'otherdata' => '',
            'javascript' => '',
            'warnings' => [],
        ];
    }
}
