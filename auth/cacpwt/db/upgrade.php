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
 * No authentication plugin upgrade code
 *
 * @package    auth_cacpwt
 * @copyright  2022 PowerTrain Inc
 * @license    All Rights Reserved
 */

/**
 * Function to upgrade auth_email.
 * @param int $oldversion the version we are upgrading from
 * @return bool result
 */
function xmldb_auth_cacpwt_upgrade($oldversion) {
    global $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2022022501) {
        // Define field id to be added to auth_cacpwt.
        $table = new xmldb_table('auth_cacpwt');
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null, null);
        $table->add_field('username', XMLDB_TYPE_CHAR, '100', null, XMLDB_NOTNULL, null, null, 'id');
        $table->add_field('email', XMLDB_TYPE_CHAR, '100', null, XMLDB_NOTNULL, null, null, 'username');
        $table->add_field('firstname', XMLDB_TYPE_CHAR, '100', null, XMLDB_NOTNULL, null, null, 'email');
        $table->add_field('lastname', XMLDB_TYPE_CHAR, '100', null, XMLDB_NOTNULL, null, null, 'firstname');
        $table->add_field('dodid', XMLDB_TYPE_CHAR, '20', null, XMLDB_NOTNULL, null, null, 'lastname');
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
        $table->add_index('username', XMLDB_INDEX_UNIQUE, ['username']);

        // Conditionally create table.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Cacpwt savepoint reached.
        upgrade_plugin_savepoint(true, 2022022501, 'auth', 'cacpwt');
    }

    if ($oldversion < 2022030101) {
        // Define field audit to be added to auth_cacpwt.
        $table = new xmldb_table('auth_cacpwt');
        $field = new xmldb_field('audit', XMLDB_TYPE_TEXT, null, null, XMLDB_NOTNULL, null, null, 'dodid');

        // Conditionally launch add field audit.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Cacpwt savepoint reached.
        upgrade_plugin_savepoint(true, 2022030101, 'auth', 'cacpwt');
    }

    if ($oldversion < 2022033101) {
        // Create table auth_cacpwt_import_even.
        $table = new xmldb_table('auth_cacpwt_import_even');
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null, null);
        $table->add_field('dodid', XMLDB_TYPE_CHAR, '10', null, null, null, null, 'id');
        $table->add_field('auth_required', XMLDB_TYPE_INTEGER, '1', null, null, null, '1', 'dodid');
        $table->add_field('bypass_dmdc', XMLDB_TYPE_INTEGER, '1', null, null, null, '0', 'auth_required');
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
        $table->add_index('dodid', XMLDB_INDEX_UNIQUE, ['dodid']);

        // Conditionally create table.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Create table auth_cacpwt_import_odd.
        $table = new xmldb_table('auth_cacpwt_import_odd');
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null, null);
        $table->add_field('dodid', XMLDB_TYPE_CHAR, '10', null, null, null, null, 'id');
        $table->add_field('auth_required', XMLDB_TYPE_INTEGER, '1', null, null, null, '1', 'dodid');
        $table->add_field('bypass_dmdc', XMLDB_TYPE_INTEGER, '1', null, null, null, '0', 'auth_required');
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
        $table->add_index('dodid', XMLDB_INDEX_UNIQUE, ['dodid']);

        // Conditionally create table.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Cacpwt savepoint reached.
        upgrade_plugin_savepoint(true, 2022033101, 'auth', 'cacpwt');
    }

    if ($oldversion < 2022040601) {
        // Define field suspended to be added to auth_cacpwt.
        $table = new xmldb_table('auth_cacpwt');
        $field = new xmldb_field('suspended', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '1', 'audit');

        // Conditionally launch add field suspended.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Cacpwt savepoint reached.
        upgrade_plugin_savepoint(true, 2022040601, 'auth', 'cacpwt');
    }

    return true;
}
