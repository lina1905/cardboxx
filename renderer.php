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
 * Renderer for cardbox module.
 *
 * @package   mod_cardbox
 * @copyright 2019 RWTH Aachen (see README.md)
 * @author    Anna Heynkes
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once('../../config.php');
/**
 * Renderer for cardbox module.
 */
class mod_cardbox_renderer extends plugin_renderer_base {

    /**
     * Construct a tab header.
     *
     * @param moodle_url $baseurl Base URL for the tab
     * @param string $action Action associated with the tab
     * @param string|null $namekey Key for the name of the tab (optional)
     * @param string|null $cardboxname Name of the cardbox (optional)
     * @param string|null $nameargs Arguments for the name (optional)
     * @return tabobject The constructed tab object
     */
    private function cardbox_create_tab(moodle_url $baseurl, $action, $namekey = null, $cardboxname = null, $nameargs = null) {
        $taburl = new moodle_url($baseurl, ['action' => $action]);
        $tabname = get_string($namekey, 'cardbox', $nameargs);
        if ($cardboxname) {
            strlen($cardboxname) > 20 ? $tabname = substr($cardboxname, 0, 21) . "..." : $tabname = $cardboxname;
        }
        $id = $action;
        $tab = new tabobject($id, $taburl, $tabname);
        return $tab;
    }
    /**
     * Render the tab header hierarchy.
     *
     * @param moodle_url $baseurl Base URL for the tabs
     * @param context $context The context for the tabs
     * @param string|null $selected The selected tab (optional)
     * @param string|null $inactive The inactive tab (optional)
     * @return string Rendered tabs
     */
    public function cardbox_render_tabs(moodle_url $baseurl, $context, $selected = null, $inactive = null) {

        global $USER;

        if (has_capability('mod/cardbox:submitcard', $context)) {
            $level1 = [$this->cardbox_create_tab($baseurl, 'addflashcard', 'addflashcard')];
            $level1[] = $this->cardbox_create_tab($baseurl, 'massimport', 'massimport');
        }
        $level1[] = $this->cardbox_create_tab($baseurl, 'practice', 'practice');
        $level1[] = $this->cardbox_create_tab($baseurl, 'statistics', 'statistics');

        if (has_capability('mod/cardbox:approvecard', $context)) {
            $level1[] = $this->cardbox_create_tab($baseurl, 'review', 'review');
        }

        $level1[] = $this->cardbox_create_tab($baseurl, 'overview', 'overview');

        if (has_capability('mod/cardbox:edittopics', $context)) {
            $level1[] = $this->cardbox_create_tab($baseurl, 'edittopic', 'edittopic');
        }

        return $this->tabtree($level1, $selected, $inactive);
    }
    /**
     * Render the study view.
     *
     * @param \templatable $studyview The study view to render
     * @return string Rendered study view
     */
    public function cardbox_render_studyview(\templatable $studyview) {
        $data = $studyview->export_for_template($this);
        // 1. Param specifies the template, 2. param the data to pass into it.
        return $this->render_from_template('mod_cardbox/studyview', $data);
    }
    /**
     * Render the practice view.
     *
     * @param \templatable $practice The practice view to render
     * @return string Rendered practice view
     */
    public function cardbox_render_practice(\templatable $practice) {
        $data = $practice->export_for_template($this);
        return $this->render_from_template('mod_cardbox/practice', $data);
    }
    /**
     * Function renders a modal dialogue which asks the user to choose a correction mode
     * and/or topics to prefer in card selection.
     *
     * @param \templatable $practice The practice start view to render
     * @return string Rendered practice start view
     */
    public function cardbox_render_practice_start(\templatable $practice) {
        $data = $practice->export_for_template($this);
        return $this->render_from_template('mod_cardbox/practice_start', $data);
    }
    /**
     * Render the statistics view.
     *
     * @param \templatable $statistics The statistics view to render
     * @return string Rendered statistics view
     */
    public function cardbox_render_statistics(\templatable $statistics) {
        $data = $statistics->export_for_template($this);
        // 1. Param specifies the template, 2. param the data to pass into it.
        return $this->render_from_template('mod_cardbox/statistics', $data);
    }
    /**
     * Render the review view.
     *
     * @param \templatable $review The review view to render
     * @return string Rendered review view
     */
    public function cardbox_render_review(\templatable $review) {
        $data = $review->export_for_template($this);
        // 1. Param specifies the template, 2. param the data to pass into it.
        return $this->render_from_template('mod_cardbox/review', $data);
    }
    /**
     * Render the overview view.
     *
     * @param \templatable $review The overview view to render
     * @return string Rendered overview view
     */
    public function cardbox_render_overview(\templatable $review) {
        $data = $review->export_for_template($this);
        return $this->render_from_template('mod_cardbox/overview', $data);
    }
    /**
     * Render the error import view.
     *
     * @param array $errorlines The error lines to render
     * @return string Rendered error import view
     */
    public function cardbox_render_errimport(array $errorlines) {
        return $this->render_from_template('mod_cardbox/errimport', $errorlines);
    }
    /**
     * Render the topics view.
     *
     * @param \templatable $topics The topics view to render
     * @return string Rendered topics view
     */
    public function cardbox_render_topics(\templatable $topics) {
        $data = $topics->export_for_template($this);
        // 1. Param specifies the template, 2. param the data to pass into it.
        return $this->render_from_template('mod_cardbox/topic', $data);
    }
}
