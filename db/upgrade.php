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
 * This file keeps track of upgrades to the examhelp module
 *
 * Sometimes, changes between versions involve alterations to database
 * structures and other major things that may break installations. The upgrade
 * function in this file will attempt to perform all the necessary actions to
 * upgrade your older installation to the current version. If there's something
 * it cannot do itself, it will tell you what you need to do.  The commands in
 * here will all be database-neutral, using the functions defined in DLL libraries.
 *
 * @package    mod_examhelp
 * @copyright  2011 Your Name
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Execute examhelp upgrade from the given old version
 *
 * @param int $oldversion
 * @return bool
 */
function xmldb_block_risk_monitor_upgrade($oldversion) {
    global $DB;

    $dbman = $DB->get_manager(); // loads ddl manager and xmldb classes

    // And upgrade begins here. For each one, you'll need one
    // block of code similar to the next one. Please, delete
    // this comment lines once this file start handling proper
    // upgrade code.

    // if ($oldversion < YYYYMMDD00) { //New version in version.php
    //
    // }

    // Lines below (this included)  MUST BE DELETED once you get the first version
    // of your module ready to be installed. They are here only
    // for demonstrative purposes and to show how the examhelp
    // iself has been upgraded.

    // For each upgrade block, the file examhelp/version.php
    // needs to be updated . Such change allows Moodle to know
    // that this file has to be processed.

    // To know more about how to write correct DB upgrade scripts it's
    // highly recommended to read information available at:
    //   http://docs.moodle.org/en/Development:XMLDB_Documentation
    // and to play with the XMLDB Editor (in the admin menu) and its
    // PHP generation posibilities.
    if ($oldversion < 2014180603) {

        // Define field id to be added to block_risk_monitor_trait.
        $table = new xmldb_table('block_risk_monitor_trait');
        $field = new xmldb_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null, null);

        // Conditionally launch add field id.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Test_risk_monitor savepoint reached.
        upgrade_block_savepoint(true, 2014180603, 'risk_monitor');
    }

        if ($oldversion < 2014130800) {

        // Define field id to be dropped from block_risk_monitor_config.
        $table = new xmldb_table('block_risk_monitor_config');
        $field = new xmldb_field('timebeforeexam');

        // Conditionally launch drop field id.
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }

        // Define field id to be dropped from block_risk_monitor_anx.
        $table = new xmldb_table('block_risk_monitor_anx');
        $field = new xmldb_field('activitylevel');

        // Conditionally launch drop field id.
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }
        
        // Define table block_risk_monitor_exam to be dropped.
        $table = new xmldb_table('block_risk_monitor_trait');

        // Conditionally launch drop table for block_risk_monitor_exam.
        if ($dbman->table_exists($table)) {
            $dbman->drop_table($table);
        }

        // Test_risk_monitor savepoint reached.
        upgrade_block_savepoint(true, 2014130800, 'risk_monitor');
    }

    if ($oldversion < 2014260802) {

        // Rename field dateadded on table block_risk_monitor_config to NEWNAMEGOESHERE.
        $table = new xmldb_table('block_risk_monitor_config');
        $field = new xmldb_field('dateupdated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'teacherid');

        // Launch rename field dateadded.
        $dbman->rename_field($table, $field, 'dateadded');
        
        // Define table block_risk_monitor_block to be renamed to NEWNAMEGOESHERE.
        $table = new xmldb_table('block_risk_monitor_block');

        // Launch rename table for block_risk_monitor_block.
        $dbman->rename_table($table, 'block_risk_monitor_config');

        // Define table block_risk_monitor_course to be created.
        $table = new xmldb_table('block_risk_monitor_course');

        // Adding fields to table block_risk_monitor_course.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('courseid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('blockid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('preamble_template', XMLDB_TYPE_TEXT, null, null, XMLDB_NOTNULL, null, null);
        $table->add_field('postamble_template', XMLDB_TYPE_TEXT, null, null, XMLDB_NOTNULL, null, null);

        // Adding keys to table block_risk_monitor_course.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->add_key('courseid', XMLDB_KEY_FOREIGN, array('courseid'), 'course', array('id'));
        $table->add_key('blockid', XMLDB_KEY_FOREIGN, array('blockid'), 'block_risk_monitor_block', array('id'));

        // Conditionally launch create table for block_risk_monitor_course.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Test_risk_monitor savepoint reached.
        upgrade_block_savepoint(true, 2014260802, 'risk_monitor');
    }

    if ($oldversion < 2014130804) {

         // Test_risk_monitor savepoint reached.
        upgrade_block_savepoint(true, 2014130804, 'risk_monitor');
    }
    
        if ($oldversion < 2014260807) {

        // Define field fullname to be added to block_risk_monitor_course.
        $table = new xmldb_table('block_risk_monitor_course');
        $field = new xmldb_field('fullname', XMLDB_TYPE_TEXT, null, null, null, null, null, 'postamble_template');

        // Conditionally launch add field fullname.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Define field fullname to be added to block_risk_monitor_course.
        $table = new xmldb_table('block_risk_monitor_course');
        $field = new xmldb_field('shortname', XMLDB_TYPE_TEXT, null, null, null, null, null, 'fullname');

        // Conditionally launch add field fullname.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        
        // Test_risk_monitor savepoint reached.
        upgrade_block_savepoint(true, 2014260807, 'risk_monitor');
    }
    
        if ($oldversion < 2014260809) {

        // Define index courseid (not unique) to be dropped form block_risk_monitor_exam.
        $table = new xmldb_table('block_risk_monitor_exam');
        $index = new xmldb_index('courseid', XMLDB_INDEX_NOTUNIQUE, array('courseid'));

        // Conditionally launch drop index courseid.
        if ($dbman->index_exists($table, $index)) {
            $dbman->drop_index($table, $index);
        }

        // Define key courseid (foreign) to be added to block_risk_monitor_exam.
        $table = new xmldb_table('block_risk_monitor_exam');
        $key = new xmldb_key('courseid', XMLDB_KEY_FOREIGN, array('courseid'), 'block_risk_monitor_course', array('id'));

        // Launch add key courseid.
        $dbman->add_key($table, $key);
        
        // Test_risk_monitor savepoint reached.
        upgrade_block_savepoint(true, 2014260809, 'risk_monitor');
    }

        if ($oldversion < 2014260810) {

        // Define field examname to be dropped from block_risk_monitor_exam.
        $table = new xmldb_table('block_risk_monitor_exam');
        $field = new xmldb_field('examname');

        // Conditionally launch drop field examname.
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }

        // Test_risk_monitor savepoint reached.
        upgrade_block_savepoint(true, 2014260810, 'risk_monitor');
    }

         if ($oldversion < 2014260813) {
   
       // Define field id to be dropped from block_risk_monitor_anx.
        $table = new xmldb_table('block_risk_monitor_anx');
        $field = new xmldb_field('activitylevel');

        // Conditionally launch drop field id.
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }
        
        // Define table block_risk_monitor_exam to be dropped.
        $table = new xmldb_table('block_risk_monitor_trait');

        // Conditionally launch drop table for block_risk_monitor_exam.
        if ($dbman->table_exists($table)) {
            $dbman->drop_table($table);
        }

        // Test_risk_monitor savepoint reached.
        upgrade_block_savepoint(true, 2014260813, 'risk_monitor');
        
         }
         
      if ($oldversion < 2014260814) {

        // Define key examid (foreign-unique) to be dropped form block_risk_monitor_anx.
        $table = new xmldb_table('block_risk_monitor_anx');
        $key = new xmldb_key('examid', XMLDB_KEY_FOREIGN_UNIQUE, array('examid'), 'exam', array('id'));

        // Launch drop key examid.
        $dbman->drop_key($table, $key);

                // Define key examid (foreign) to be added to block_risk_monitor_anx.
        $table = new xmldb_table('block_risk_monitor_anx');
        $key = new xmldb_key('examid', XMLDB_KEY_FOREIGN, array('examid'), 'exam', array('id'));

        // Launch add key examid.
        $dbman->add_key($table, $key);
        
        // Test_risk_monitor savepoint reached.
        upgrade_block_savepoint(true, 2014260814, 'risk_monitor');
    }
    
        if ($oldversion < 2014260816) {

        // Define table block_risk_monitor_anx to be dropped.
        $table = new xmldb_table('block_risk_monitor_anx');

        // Conditionally launch drop table for block_risk_monitor_anx.
        if ($dbman->table_exists($table)) {
            $dbman->drop_table($table);
        }
        
        // Define table block_risk_monitor_anx to be dropped.
        $table = new xmldb_table('block_risk_monitor_exam');

        // Conditionally launch drop table for block_risk_monitor_anx.
        if ($dbman->table_exists($table)) {
            $dbman->drop_table($table);
        }
        
        // Define table block_risk_monitor_anx to be dropped.
        $table = new xmldb_table('block_risk_monitor_log');

        // Conditionally launch drop table for block_risk_monitor_anx.
        if ($dbman->table_exists($table)) {
            $dbman->drop_table($table);
        }
        
        // Define field postamble_template to be dropped from block_risk_monitor_course.
        $table = new xmldb_table('block_risk_monitor_course');
        $field = new xmldb_field('preamble_template');

        // Conditionally launch drop field postamble_template.
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }
        
        // Define field postamble_template to be dropped from block_risk_monitor_course.
        $table = new xmldb_table('block_risk_monitor_course');
        $field = new xmldb_field('postamble_template');

        // Conditionally launch drop field postamble_template.
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }        
        // Define table block_risk_monitor_rule to be created.
        $table = new xmldb_table('block_risk_monitor_rule');

        // Adding fields to table block_risk_monitor_rule.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('name', XMLDB_TYPE_TEXT, null, null, XMLDB_NOTNULL, null, null);
        $table->add_field('description', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('weighting', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('enabled', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('categoryid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('timestamp', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');

        // Adding keys to table block_risk_monitor_rule.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->add_key('categoryid', XMLDB_KEY_FOREIGN, array('categoryid'), 'block_risk_monitor_category', array('id'));

        // Conditionally launch create table for block_risk_monitor_rule.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }
        
                // Define table block_risk_monitor_category to be created.
        $table = new xmldb_table('block_risk_monitor_category');

        // Adding fields to table block_risk_monitor_category.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('name', XMLDB_TYPE_TEXT, null, null, XMLDB_NOTNULL, null, null);
        $table->add_field('description', XMLDB_TYPE_TEXT, null, null, XMLDB_NOTNULL, null, null);
        $table->add_field('courseid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('timestamp', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');

        // Adding keys to table block_risk_monitor_category.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->add_key('courseid', XMLDB_KEY_FOREIGN, array('courseid'), 'course', array('id'));

        // Conditionally launch create table for block_risk_monitor_category.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }
        
        
        
        // Test_risk_monitor savepoint reached.
        upgrade_block_savepoint(true, 2014260816, 'risk_monitor');
    }
    
        if ($oldversion < 2014260818) {

        // Changing nullability of field description on table block_risk_monitor_category to null.
        $table = new xmldb_table('block_risk_monitor_category');
        $field = new xmldb_field('description', XMLDB_TYPE_TEXT, null, null, null, null, null, 'name');

        // Launch change of nullability for field description.
        $dbman->change_field_notnull($table, $field);

        // Test_risk_monitor savepoint reached.
        upgrade_block_savepoint(true, 2014260818, 'risk_monitor');
    }


    return true;
}
