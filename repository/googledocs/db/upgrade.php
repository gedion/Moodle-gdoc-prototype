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
 * @param int $oldversion the version we are upgrading from
 * @return bool result
 */
function xmldb_repository_googledocs_upgrade($oldversion) {
    global $CFG, $DB;
    require_once(__DIR__.'/upgradelib.php');

    $dbman = $DB->get_manager();

    if ($oldversion < 2012051400) {
        // Delete old user preferences containing authsub tokens.
        $DB->delete_records('user_preferences', array('name' => 'google_authsub_sesskey'));
        upgrade_plugin_savepoint(true, 2012051400, 'repository', 'googledocs');
    }

    if ($oldversion < 2012053000) {
        require_once($CFG->dirroot.'/repository/lib.php');
        $existing = $DB->get_record('repository', array('type' => 'googledocs'), '*', IGNORE_MULTIPLE);

        if ($existing) {
            $googledocsplugin = new repository_type('googledocs', array(), true);
            $googledocsplugin->delete();
            repository_googledocs_admin_upgrade_notification();
        }

        upgrade_plugin_savepoint(true, 2012053000, 'repository', 'googledocs');
    }
    if ($oldversion < 2016050100) {

        // Define table google_refreshtokens to be created.
        $table = new xmldb_table('google_refreshtokens');

        // Adding fields to table google_refreshtokens.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('refreshtokenid', XMLDB_TYPE_TEXT, null, null, XMLDB_NOTNULL, null, null);
        // Adding keys to table google_refreshtokens.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

        // Conditionally launch create table for google_refreshtokens.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }
        $index = new xmldb_index('userid', XMLDB_INDEX_UNIQUE, array('userid'));

        // Conditionally launch add index useridindex.
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }

        upgrade_plugin_savepoint(true, 2016050100, 'repository', 'googledocs');
    }

    // Moodle v2.3.0 release upgrade line
    // Put any upgrade step following this


    // Moodle v2.4.0 release upgrade line
    // Put any upgrade step following this


    // Moodle v2.5.0 release upgrade line.
    // Put any upgrade step following this.


    // Moodle v2.6.0 release upgrade line.
    // Put any upgrade step following this.

    // Moodle v2.7.0 release upgrade line.
    // Put any upgrade step following this.

    // Moodle v2.8.0 release upgrade line.
    // Put any upgrade step following this.

    // Moodle v2.9.0 release upgrade line.
    // Put any upgrade step following this.

    // Moodle v3.0.0 release upgrade line.
    // Put any upgrade step following this.

    return true;
}
