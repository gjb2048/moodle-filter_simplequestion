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
 * Functions for inserting and displaying questions
 * @package    filter
 * @subpackage simplequestion
 * @copyright  2017 Richard Jones (https://richardnz.net/)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Custom renderer class for filter_simplequestion
 * @copyright  2017 Richard Jones (https://richardnz.net/)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class filter_simplequestion_renderer extends plugin_renderer_base {
    /**
     * This function returns a question preview page link
     * @param int $number the question id
     * @param string $linktext the text for the returned link
     * @param string $popup whether the question is embedded
     *        or displayed in a popup window
     * @param int $courseid the id of the course the question is in
     * @return string the html required to display the question
     */
    public function get_question($number, $linktext, $popup, $courseid) {
        global $CFG;

        $url = '/filter/simplequestion/preview.php';

        // Check if I'm inside a module to set context.
        // Am I displaying in a popup or embedded?
        if ($this->page->cm) {
            $modname = $this->page->cm->modname;
            $cmid = $this->page->cm->id;
            $link = new moodle_url($url, array('id' => $number,
                    'courseid' => $courseid, 'popup' => $popup,
                    'modname' => $modname, 'cmid' => $cmid));
        } else {

            $link = new moodle_url($url, array('id' => $number,
                    'courseid' => $courseid,
                    'popup' => $popup));
        }

        // Check for link text.
        if ($linktext === '') {
            $linktext = get_string('link_text', 'filter_simplequestion');
        }

        // Check for popup or embed.
        if ($popup === 'popup') {
            // Show the preview page.
            return $this->output->action_link($link, $linktext,
                    new popup_action('click', $link));
        } else {
            // Embed the question right here.
            return $this->embed_question($number, $link, $linktext);
        }
    }
    /**
     * This function returns the html to display an error message
     * @param string $text the error message
     * @return string the html required to display the message
     */
    public function get_error($text) {
        $html = html_writer::start_tag('div',
                array('class' => 'filter_simplequestion_error'));
        $html .= $text;
        $html .= html_writer::end_tag('div');
        return $html;
    }
    /**
     * This function returns the html to embed a question in a sliding panel
     * @param string $number the encoded question id number
     * @param string $link the url of the page to display
     * @param string $linktext the text of the link
     * @return string the html required to embed the question
     */
    public function embed_question($number, $link, $linktext) {
        // Unique id's based on question number (encrypted).
        // This is so that multiple instances (of different questions)
        // on the same page will work.
        $buttonid = 'filter_simplequestion_button' . $number;
        $panelid = 'filter_simplequestion_panel_' . $number;
        $this->page->requires->js_call_amd(
                'filter_simplequestion/show_content', 'init',
                array('buttonid' => $buttonid, 'panelid'  => $panelid));
        $html = '';
        $buttonattributes =
                array('id' => $buttonid,
                'class' => 'filter_simplequestion_button btn btn-info');
        $button = html_writer::tag('button', $linktext, $buttonattributes);
        // Button div.
        $html .= html_writer::div($button, 'filter_simplequestion_button');

        // Get the iFrame size from config.
        $defconfig = get_config('filter_simplequestion');
        $height = $defconfig->height;
        $width = $defconfig->width;

        // The hidden div - toggles on button link being clicked.
        $html .= html_writer::start_tag('div',
                array('id' => $panelid,
                'class' => 'filter_simplequestion_container'));
        // The question preview page is embedded here in an iframe.
        $iframeattributes = array('height' => $height, 'width' => $width,
                'src' => $link);
        $html .= html_writer::start_tag('iframe', $iframeattributes);
        $html .= html_writer::end_tag('iframe');
        $html .= html_writer::end_tag('div');

        return $html;
    }
    /**
     * Return the html required to display informational text
     * @param string $popup whether the question is embedded or in popup
     * @return string the html required to display the controls
     */
    public function display_controls($popup) {

        // Add text for exiting back to course or module.
        echo html_writer::start_tag('div',
                array('class' => 'filter_simplequestion_controls'));

        // For popup close the window message.
        if ($popup === 'popup') {
            // Todo: add a button to close the window.
            echo get_string('use_close', 'filter_simplequestion');

        } else {
            // For embedded click link to close panel.
            echo get_string('click_link', 'filter_simplequestion');
        }

        echo html_writer::end_tag('div');
    }
}