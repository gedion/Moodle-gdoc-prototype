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
 * Form class for preference.php
 *
 * @package    repository
 * @subpackage googledocs
 * @copyright  2012 onwards Totara Learning Solutions Ltd {@link http://www.totaralms.com/}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author     Geidion Woldeselassie <gedion@umn.edu>
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');
require_once($CFG->dirroot . '/repository/googledocs/lib.php');

/*
 * URL of Google drive.
 */
define('GOOGLE_DRIVE_URL', 'https://drive.google.com');

/** * Form to edit repository google docs initial details.
 *
 */
class edit_repository_googledocs_form extends moodleform {

    /**
     * Defines the form
     */
    public function definition() {

        $mform = $this->_form;

        $mform->addElement('html', html_writer::tag('span', '', array('class' => 'notconnected', 'id' => 'connection-error')));
        $mform->addElement('header', 'googledocsheader', get_string('driveconnection', 'repository_googledocs'));
        $mform->addHelpButton('googledocsheader', 'googledriveconnection', 'repository_googledocs');

        list($redirecturl, $status, $email) = $this->get_redirect_url_and_connection_status();
        if ($status != 'connected') {
            $pleaseelement = html_writer::tag('span', get_string('pleaseconnect', 'repository_googledocs'),
                array('class' => 'pleaseconnect', 'id' => 'pleaseconnect'));
            $mform->addElement('static', 'pleaseconnect', '', $pleaseelement);
        }
        $mform->addElement('static', 'url', get_string('url'), GOOGLE_DRIVE_URL);

        if ($status == 'connected') {
            $statuselement = html_writer::tag('span', get_string('connected', 'repository_googledocs'),
                array('class' => 'connected', 'id' => 'connection-status'));
        } else {
            $statuselement = html_writer::tag('span', get_string('notconnected', 'repository_googledocs'),
                array('class' => 'notconnected', 'id' => 'connection-status'));
        }
        $mform->addElement('static', 'status', get_string('status'), $statuselement);
        if ($email) {
            $mform->addElement('static', 'email', get_string('email'), $email);
            $mform->addHelpButton('email', 'googleemail', 'repository_googledocs');
        }
        $mform->addElement('static', 'googledocs', '', $redirecturl);
        if ($status != 'connected') {
            $mform->addHelpButton('googledocs', 'googledriveconnection', 'repository_googledocs');
        }
    }

    /**
     * returns google redirect url(which can be either
     * a login to google url or a revoke token url) and
     * a login status
     */
    public function get_redirect_url_and_connection_status() {
        global $DB, $USER;

        $googledocsrepo = $DB->get_record('repository', array ('type'=>'googledocs'));
        $googlerefreshtoken = $DB->get_record('google_refreshtokens', array ('userid'=>$USER->id));
        $repooptions = array(
            'ajax' => false,
            'mimetypes' => array('.mp3')
        );

        $context = context_user::instance($USER->id);
        $repo = new repository_googledocs($googledocsrepo->id, $context, $repooptions);
        $code = optional_param('oauth2code', null, PARAM_RAW);
        if (!$googlerefreshtoken || (is_null($googlerefreshtoken->refreshtokenid) && empty($code))) {
            $redirecturl = $repo->get_login_url();
            $email = null;
            $status = "notconnected";
        } else {
            if ($code) {
                $repo->callback();
            }
            $status = "connected";
            $redirecturl = $repo->get_revoke_url();
            $email = $repo->get_user_info()->email;
        }
        return array($redirecturl, $status, $email);
    }

}

