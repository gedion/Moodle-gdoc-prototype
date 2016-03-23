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
 * Steps definitions for the googledocs repository type.
 *
 * @package    repository_googledocs
 * @category   test
 * @copyright  2016 UC Regents
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// NOTE: no MOODLE_INTERNAL test here, this file may be required by behat before including /config.php.

require_once(__DIR__ . '/../../../../lib/behat/behat_base.php');

use Behat\Behat\Context\Step\Given as Given;
use Behat\Gherkin\Node\TableNode as TableNode;
use Behat\Mink\Exception\SkippedException as SkippedException;


/**
 * Steps definitions to deal with the Google repository.
 *
 * @package    repository_googledocs
 * @category   test
 * @copyright  2016 UC Regents
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class behat_googledocs extends behat_base {

    /**
     * Used to determine if we need to login to Google Drive again.
     * @var boolean
     */
    private static $loggedin = false;

    /**
     * Enables the Google Drive repo.
     *
     * Make sure that repo clientid and secret is set in config.php.
     *
     * @Given /^Google Drive repository is enabled$/
     */
    public function google_drive_repository_is_enabled() {
        global $CFG;
        require_once($CFG->dirroot . '/repository/lib.php');
        
        $fromform = array();
        $fromform['clientid'] = get_config('googledocs', 'clientid');
        $fromform['secret'] = get_config('googledocs', 'secret');
        if (empty($fromform['clientid']) || empty($fromform['secret'])) {
            debugging('Googledocs clientid/secret not set in config');
            throw new SkippedException;
        }

        $type = new repository_type('googledocs', $fromform, true);
        if (!$repoid = $type->create()) {
            debugging('Cannot create Googledocs repo');
            throw new SkippedException;
        }
    }
    
    /**
     * Logins into Google Drive.
     *
     * Make sure that Google Drive user/password is set in config.php.
     *
     * @Given /^I login to Google Drive$/
     */
    public function i_login_to_google_drive() {

        $config = get_config('googledocs');
        if (empty($config->behatuser) || empty($config->behatpassword)) {
            debugging('Googledocs behat user/password not set in config');
            throw new SkippedException;
        }

        $login = new TableNode();
        $login->addRow(array('email', $config->behatuser));
        $password = new TableNode();
        $password->addRow(array('Passwd', $config->behatpassword));

        // If running other Behat tests in which user already logged into
        // Google Drive before, then session should still exist and Google will
        // just ask for password
        if (self::$loggedin) {
            return array(
                new Given('I press "Login to your account"'),
                new Given('I switch to "repo_auth" window'),
                new Given('I set the following fields to these values:', $password),
                new Given('I press "Sign in"'),
                new Given('I switch to the main window')
            );
        } else {
            // Go through entire login process.
            self::$loggedin = true;

            return array(
                new Given('I press "Login to your account"'),
                new Given('I switch to "repo_auth" window'),
                new Given('I set the following fields to these values:', $login),
                new Given('I press "next"'),
                new Given('I set the following fields to these values:', $password),
                new Given('I press "Sign in"'),
                new Given('I wait "2" seconds'),
                new Given('I press "Allow"'),
                new Given('I switch to the main window')
            );
        }

    }
}
