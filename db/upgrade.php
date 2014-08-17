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
function xmldb_block_anxiety_teacher_upgrade($oldversion) {
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

        // Define field id to be added to block_anxiety_teacher_trait.
        $table = new xmldb_table('block_anxiety_teacher_trait');
        $field = new xmldb_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null, null);

        // Conditionally launch add field id.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Test_anxiety_teacher savepoint reached.
        upgrade_block_savepoint(true, 2014180603, 'anxiety_teacher');
    }

        if ($oldversion < 2014130800) {

        // Define field id to be dropped from block_anxiety_teacher_config.
        $table = new xmldb_table('block_anxiety_teacher_config');
        $field = new xmldb_field('timebeforeexam');

        // Conditionally launch drop field id.
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }

        // Define field id to be dropped from block_anxiety_teacher_anx.
        $table = new xmldb_table('block_anxiety_teacher_anx');
        $field = new xmldb_field('activitylevel');

        // Conditionally launch drop field id.
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }
        
        // Define table block_anxiety_teacher_exam to be dropped.
        $table = new xmldb_table('block_anxiety_teacher_trait');

        // Conditionally launch drop table for block_anxiety_teacher_exam.
        if ($dbman->table_exists($table)) {
            $dbman->drop_table($table);
        }

        // Test_anxiety_teacher savepoint reached.
        upgrade_block_savepoint(true, 2014130800, 'anxiety_teacher');
    }

    if ($oldversion < 2014260802) {

        // Rename field dateadded on table block_anxiety_teacher_config to NEWNAMEGOESHERE.
        $table = new xmldb_table('block_anxiety_teacher_config');
        $field = new xmldb_field('dateupdated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'teacherid');

        // Launch rename field dateadded.
        $dbman->rename_field($table, $field, 'dateadded');
        
        // Define table block_anxiety_teacher_block to be renamed to NEWNAMEGOESHERE.
        $table = new xmldb_table('block_anxiety_teacher_block');

        // Launch rename table for block_anxiety_teacher_block.
        $dbman->rename_table($table, 'block_anxiety_teacher_config');

        // Define table block_anxiety_teacher_course to be created.
        $table = new xmldb_table('block_anxiety_teacher_course');

        // Adding fields to table block_anxiety_teacher_course.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('courseid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('blockid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('preamble_template', XMLDB_TYPE_TEXT, null, null, XMLDB_NOTNULL, null, null);
        $table->add_field('postamble_template', XMLDB_TYPE_TEXT, null, null, XMLDB_NOTNULL, null, null);

        // Adding keys to table block_anxiety_teacher_course.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->add_key('courseid', XMLDB_KEY_FOREIGN, array('courseid'), 'course', array('id'));
        $table->add_key('blockid', XMLDB_KEY_FOREIGN, array('blockid'), 'block_anxiety_teacher_block', array('id'));

        // Conditionally launch create table for block_anxiety_teacher_course.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Test_anxiety_teacher savepoint reached.
        upgrade_block_savepoint(true, 2014260802, 'test_anxiety_teacher');
    }

    if ($oldversion < 2014130804) {

         // Test_anxiety_teacher savepoint reached.
        upgrade_block_savepoint(true, 2014130804, 'anxiety_teacher');
    }
    
        if ($oldversion < 2014260807) {

        // Define field fullname to be added to block_anxiety_teacher_course.
        $table = new xmldb_table('block_anxiety_teacher_course');
        $field = new xmldb_field('fullname', XMLDB_TYPE_TEXT, null, null, null, null, null, 'postamble_template');

        // Conditionally launch add field fullname.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Define field fullname to be added to block_anxiety_teacher_course.
        $table = new xmldb_table('block_anxiety_teacher_course');
        $field = new xmldb_field('shortname', XMLDB_TYPE_TEXT, null, null, null, null, null, 'fullname');

        // Conditionally launch add field fullname.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        
        // Test_anxiety_teacher savepoint reached.
        upgrade_block_savepoint(true, 2014260807, 'anxiety_teacher');
    }
    
        if ($oldversion < 2014260809) {

        // Define index courseid (not unique) to be dropped form block_anxiety_teacher_exam.
        $table = new xmldb_table('block_anxiety_teacher_exam');
        $index = new xmldb_index('courseid', XMLDB_INDEX_NOTUNIQUE, array('courseid'));

        // Conditionally launch drop index courseid.
        if ($dbman->index_exists($table, $index)) {
            $dbman->drop_index($table, $index);
        }

        // Define key courseid (foreign) to be added to block_anxiety_teacher_exam.
        $table = new xmldb_table('block_anxiety_teacher_exam');
        $key = new xmldb_key('courseid', XMLDB_KEY_FOREIGN, array('courseid'), 'block_anxiety_teacher_course', array('id'));

        // Launch add key courseid.
        $dbman->add_key($table, $key);
        
        // Test_anxiety_teacher savepoint reached.
        upgrade_block_savepoint(true, 2014260809, 'anxiety_teacher');
    }

        if ($oldversion < 2014260810) {

        // Define field examname to be dropped from block_anxiety_teacher_exam.
        $table = new xmldb_table('block_anxiety_teacher_exam');
        $field = new xmldb_field('examname');

        // Conditionally launch drop field examname.
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }

        // Test_anxiety_teacher savepoint reached.
        upgrade_block_savepoint(true, 2014260810, 'anxiety_teacher');
    }

         if ($oldversion < 2014260813) {
   
       // Define field id to be dropped from block_anxiety_teacher_anx.
        $table = new xmldb_table('block_anxiety_teacher_anx');
        $field = new xmldb_field('activitylevel');

        // Conditionally launch drop field id.
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }
        
        // Define table block_anxiety_teacher_exam to be dropped.
        $table = new xmldb_table('block_anxiety_teacher_trait');

        // Conditionally launch drop table for block_anxiety_teacher_exam.
        if ($dbman->table_exists($table)) {
            $dbman->drop_table($table);
        }

        // Test_anxiety_teacher savepoint reached.
        upgrade_block_savepoint(true, 2014260813, 'anxiety_teacher');
        
         }
         
      if ($oldversion < 2014260814) {

        // Define key examid (foreign-unique) to be dropped form block_anxiety_teacher_anx.
        $table = new xmldb_table('block_anxiety_teacher_anx');
        $key = new xmldb_key('examid', XMLDB_KEY_FOREIGN_UNIQUE, array('examid'), 'exam', array('id'));

        // Launch drop key examid.
        $dbman->drop_key($table, $key);

                // Define key examid (foreign) to be added to block_anxiety_teacher_anx.
        $table = new xmldb_table('block_anxiety_teacher_anx');
        $key = new xmldb_key('examid', XMLDB_KEY_FOREIGN, array('examid'), 'exam', array('id'));

        // Launch add key examid.
        $dbman->add_key($table, $key);
        
        // Test_anxiety_teacher savepoint reached.
        upgrade_block_savepoint(true, 2014260814, 'anxiety_teacher');
    }
    return true;
}
