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

        if ($oldversion < 2014260821) {

        // Define table block_risk_monitor_risk to be created.
        $table = new xmldb_table('block_risk_monitor_risk');

        // Adding fields to table block_risk_monitor_risk.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('ruleid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('value', XMLDB_TYPE_INTEGER, '3', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('timestamp', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');

        // Adding keys to table block_risk_monitor_risk.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->add_key('userid', XMLDB_KEY_FOREIGN, array('userid'), 'user', array('id'));
        $table->add_key('ruleid', XMLDB_KEY_FOREIGN, array('ruleid'), 'block_risk_monitor_rule', array('id'));

        // Conditionally launch create table for block_risk_monitor_risk.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }
        
        // Define table block_risk_monitor_rule_type to be created.
        $table = new xmldb_table('block_risk_monitor_rule_type');

        // Adding fields to table block_risk_monitor_rule_type.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('name', XMLDB_TYPE_TEXT, null, null, XMLDB_NOTNULL, null, null);
        $table->add_field('description', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('custom', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('course_specific', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0');

        // Adding keys to table block_risk_monitor_rule_type.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

        // Conditionally launch create table for block_risk_monitor_rule_type.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Test_risk_monitor savepoint reached.
        upgrade_block_savepoint(true, 2014260821, 'risk_monitor');
    }

        if ($oldversion < 2014260822) {

        // Define field ruletypeid to be added to block_risk_monitor_rule.
        $table = new xmldb_table('block_risk_monitor_rule');
        $field = new xmldb_field('ruletypeid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'timestamp');

        // Conditionally launch add field ruletypeid.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

         // Define key ruletypeid (foreign) to be added to block_risk_monitor_rule.
        $table = new xmldb_table('block_risk_monitor_rule');
        $key = new xmldb_key('ruletypeid', XMLDB_KEY_FOREIGN, array('ruletypeid'), 'block_risk_monitor_rule_type', array('id'));

        // Launch add key ruletypeid.
        $dbman->add_key($table, $key);
        
        // Test_risk_monitor savepoint reached.
        upgrade_block_savepoint(true, 2014260822, 'risk_monitor');
    }
    
    if ($oldversion < 2014260823) {

        // Changing nullability of field course_specific on table block_risk_monitor_rule_type to null.
        $table = new xmldb_table('block_risk_monitor_rule_type');
        $field = new xmldb_field('course_specific', XMLDB_TYPE_INTEGER, '1', null, null, null, null, 'custom');

        // Launch change of nullability for field course_specific.
        $dbman->change_field_notnull($table, $field);

        // Test_risk_monitor savepoint reached.
        upgrade_block_savepoint(true, 2014260823, 'risk_monitor');
    }

        if ($oldversion < 2014260824) {

        // Define field enabled to be added to block_risk_monitor_rule_type.
        $table = new xmldb_table('block_risk_monitor_rule_type');
        $field = new xmldb_field('enabled', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0', 'course_specific');

        // Conditionally launch add field enabled.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        
        // Define field userid to be added to block_risk_monitor_rule_type.
        $table = new xmldb_table('block_risk_monitor_rule_type');
        $field = new xmldb_field('userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'enabled');

        // Conditionally launch add field userid.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        
        // Define key userid (foreign) to be added to block_risk_monitor_rule_type.
        $table = new xmldb_table('block_risk_monitor_rule_type');
        $key = new xmldb_key('userid', XMLDB_KEY_FOREIGN, array('userid'), 'user', array('id'));

        // Launch add key userid.
        $dbman->add_key($table, $key);

        // Test_risk_monitor savepoint reached.
        upgrade_block_savepoint(true, 2014260824, 'risk_monitor');
    }
    
        if ($oldversion < 2014260825) {

        // Define table block_risk_monitor_rule_risk to be renamed to NEWNAMEGOESHERE.
        $table = new xmldb_table('block_risk_monitor_risk');

        // Launch rename table for block_risk_monitor_rule_risk.
        $dbman->rename_table($table, 'block_risk_monitor_rule_risk');
        
                // Define table block_risk_monitor_cat_risk to be created.
        $table = new xmldb_table('block_risk_monitor_cat_risk');

        // Adding fields to table block_risk_monitor_cat_risk.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('categoryid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('value', XMLDB_TYPE_INTEGER, '3', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('timestamp', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');

        // Adding keys to table block_risk_monitor_cat_risk.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

        // Conditionally launch create table for block_risk_monitor_cat_risk.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Test_risk_monitor savepoint reached.
        upgrade_block_savepoint(true, 2014260825, 'risk_monitor');
    }

    if ($oldversion < 2014260827) {

        // Define field value to be added to block_risk_monitor_rule.
        $table = new xmldb_table('block_risk_monitor_rule');
        $field = new xmldb_field('value', XMLDB_TYPE_INTEGER, '10', null, null, null, '0', 'ruletypeid');

        // Conditionally launch add field value.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Test_risk_monitor savepoint reached.
        upgrade_block_savepoint(true, 2014260827, 'risk_monitor');
    }

    if ($oldversion < 2014260828) {

        // Define field value_required to be added to block_risk_monitor_rule_type.
        $table = new xmldb_table('block_risk_monitor_rule_type');
        $field = new xmldb_field('value_required', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0', 'userid');

        // Conditionally launch add field value_required.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        
        // Define field value_description to be added to block_risk_monitor_rule_type.
        $table = new xmldb_table('block_risk_monitor_rule_type');
        $field = new xmldb_field('value_description', XMLDB_TYPE_TEXT, null, null, null, null, null, 'value_required');

        // Conditionally launch add field value_description.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }


        // Test_risk_monitor savepoint reached.
        upgrade_block_savepoint(true, 2014260828, 'risk_monitor');
    }

    if ($oldversion < 2014260829) {

        // Define field action to be added to block_risk_monitor_rule_type.
        $table = new xmldb_table('block_risk_monitor_rule_type');
        $field = new xmldb_field('action', XMLDB_TYPE_TEXT, null, null, XMLDB_NOTNULL, null, null, 'value_description');

        // Conditionally launch add field action.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Define field timestamp to be added to block_risk_monitor_rule_type.
        $table = new xmldb_table('block_risk_monitor_rule_type');
        $field = new xmldb_field('timestamp', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'action');

        // Conditionally launch add field timestamp.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        
        // Test_risk_monitor savepoint reached.
        upgrade_block_savepoint(true, 2014260829, 'risk_monitor');
    }
    
        if ($oldversion < 2014260834) {

        // Define table block_risk_monitor_rule_inst to be created.
        $table = new xmldb_table('block_risk_monitor_rule_inst');

        // Adding fields to table block_risk_monitor_rule_inst.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('name', XMLDB_TYPE_TEXT, null, null, XMLDB_NOTNULL, null, null);
        $table->add_field('description', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('weighting', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('timestamp', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('value', XMLDB_TYPE_INTEGER, '10', null, null, null, '0');
        $table->add_field('categoryid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('ruletype', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('ruleid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');

        // Adding keys to table block_risk_monitor_rule_inst.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->add_key('categoryid', XMLDB_KEY_FOREIGN, array('categoryid'), 'block_risk_monitor_category', array('id'));

        // Conditionally launch create table for block_risk_monitor_rule_inst.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Define table block_risk_monitor_rule to be dropped.
        $table = new xmldb_table('block_risk_monitor_rule');

        // Conditionally launch drop table for block_risk_monitor_rule.
        if ($dbman->table_exists($table)) {
            $dbman->drop_table($table);
        }

         // Define key ruleid (foreign) to be dropped form block_risk_monitor_rule_risk.
        $table = new xmldb_table('block_risk_monitor_rule_risk');
        $key = new xmldb_key('ruleid', XMLDB_KEY_FOREIGN, array('ruleid'), 'block_risk_monitor_rule', array('id'));

        // Launch drop key ruleid.
        $dbman->drop_key($table, $key);
        
                // Define key ruleid (foreign) to be added to block_risk_monitor_rule_risk.
        $table = new xmldb_table('block_risk_monitor_rule_risk');
        $key = new xmldb_key('ruleid', XMLDB_KEY_FOREIGN, array('ruleid'), 'block_risk_monitor_rule_inst', array('id'));

        // Launch add key ruleid.
        $dbman->add_key($table, $key);

                // Define table block_risk_monitor_cust_rule to be created.
        $table = new xmldb_table('block_risk_monitor_cust_rule');

        // Adding fields to table block_risk_monitor_cust_rule.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('name', XMLDB_TYPE_TEXT, null, null, XMLDB_NOTNULL, null, null);
        $table->add_field('description', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('timestamp', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');

        // Adding keys to table block_risk_monitor_cust_rule.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->add_key('userid', XMLDB_KEY_FOREIGN, array('userid'), 'user', array('id'));

        // Conditionally launch create table for block_risk_monitor_cust_rule.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }
        
                // Define table block_risk_monitor_rule_type to be dropped.
        $table = new xmldb_table('block_risk_monitor_rule_type');

        // Conditionally launch drop table for block_risk_monitor_rule_type.
        if ($dbman->table_exists($table)) {
            $dbman->drop_table($table);
        }
        
        // Changing nullability of field ruleid on table block_risk_monitor_rule_inst to null.
        $table = new xmldb_table('block_risk_monitor_rule_inst');
        $field = new xmldb_field('ruleid', XMLDB_TYPE_INTEGER, '10', null, null, null, '0', 'ruletype');

        // Launch change of nullability for field ruleid.
        $dbman->change_field_notnull($table, $field);
        
        // Rename field ruleid on table block_risk_monitor_rule_inst to NEWNAMEGOESHERE.
        $table = new xmldb_table('block_risk_monitor_rule_inst');
        $field = new xmldb_field('ruleid', XMLDB_TYPE_INTEGER, '10', null, null, null, '0', 'ruletype');

        // Launch rename field ruleid.
        $dbman->rename_field($table, $field, 'custruleid');
        
        
        // Define field defaultruleid to be added to block_risk_monitor_rule_inst.
        $table = new xmldb_table('block_risk_monitor_rule_inst');
        $field = new xmldb_field('defaultruleid', XMLDB_TYPE_INTEGER, '10', null, null, null, null, 'custruleid');

        // Conditionally launch add field defaultruleid.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Test_risk_monitor savepoint reached.
        upgrade_block_savepoint(true, 2014260834, 'risk_monitor');
    }

        if ($oldversion < 2014260835) {

        // Define table block_risk_monitor_question to be created.
        $table = new xmldb_table('block_risk_monitor_question');

        // Adding fields to table block_risk_monitor_question.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('question', XMLDB_TYPE_TEXT, null, null, XMLDB_NOTNULL, null, null);
        $table->add_field('custruleid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');

        // Adding keys to table block_risk_monitor_question.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->add_key('custruleid', XMLDB_KEY_FOREIGN, array('custruleid'), 'block_risk_monitor_cust_rule', array('id'));

        // Conditionally launch create table for block_risk_monitor_question.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }
        
                // Define table block_risk_monitor_option to be created.
        $table = new xmldb_table('block_risk_monitor_option');

        // Adding fields to table block_risk_monitor_option.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('label', XMLDB_TYPE_TEXT, null, null, XMLDB_NOTNULL, null, null);
        $table->add_field('value', XMLDB_TYPE_INTEGER, '3', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('questionid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');

        // Adding keys to table block_risk_monitor_option.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->add_key('questionid', XMLDB_KEY_FOREIGN, array('questionid'), 'block_risk_monitor_question', array('id'));

        // Conditionally launch create table for block_risk_monitor_option.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }


        // Test_risk_monitor savepoint reached.
        upgrade_block_savepoint(true, 2014260835, 'risk_monitor');
    }
    
        if ($oldversion < 2014260839) {

        // Define key blockid (foreign) to be dropped form block_risk_monitor_course.
        $table = new xmldb_table('block_risk_monitor_course');
        $key = new xmldb_key('blockid', XMLDB_KEY_FOREIGN, array('blockid'), 'block_risk_monitor_block', array('id'));

        // Launch drop key blockid.
        $dbman->drop_key($table, $key);
        
                // Define field blockid to be dropped from block_risk_monitor_course.
        $table = new xmldb_table('block_risk_monitor_course');
        $field = new xmldb_field('blockid');

        // Conditionally launch drop field blockid.
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }
        
                // Define table block_risk_monitor_block to be dropped.
        $table = new xmldb_table('block_risk_monitor_block');

        // Conditionally launch drop table for block_risk_monitor_block.
        if ($dbman->table_exists($table)) {
            $dbman->drop_table($table);
        }

        // Test_risk_monitor savepoint reached.
        upgrade_block_savepoint(true, 2014260839, 'risk_monitor');
    }

        if ($oldversion < 2014260841) {

        // Define table block_risk_monitor_answer to be created.
        $table = new xmldb_table('block_risk_monitor_answer');

        // Adding fields to table block_risk_monitor_answer.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('questionid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('optionid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('timestamp', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');

        // Adding keys to table block_risk_monitor_answer.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->add_key('questionid', XMLDB_KEY_FOREIGN, array('questionid'), 'block_risk_monitor_question', array('id'));
        $table->add_key('optionid', XMLDB_KEY_FOREIGN, array('optionid'), 'block_risk_monitor_option', array('id'));
        $table->add_key('userid', XMLDB_KEY_FOREIGN, array('userid'), 'user', array('id'));

        // Conditionally launch create table for block_risk_monitor_answer.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Test_risk_monitor savepoint reached.
        upgrade_block_savepoint(true, 2014260841, 'risk_monitor');
    }
    
        if ($oldversion < 2014260844) {

        // Define field min_score to be added to block_risk_monitor_cust_rule.
        $table = new xmldb_table('block_risk_monitor_cust_rule');
        $field = new xmldb_field('min_score', XMLDB_TYPE_INTEGER, '3', null, XMLDB_NOTNULL, null, '0', 'timestamp');

        // Conditionally launch add field min_score.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        
                // Define field max_score to be added to block_risk_monitor_cust_rule.
        $table = new xmldb_table('block_risk_monitor_cust_rule');
        $field = new xmldb_field('max_score', XMLDB_TYPE_INTEGER, '3', null, XMLDB_NOTNULL, null, '100', 'min_score');

        // Conditionally launch add field max_score.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        
                // Define field mod_risk_floor to be added to block_risk_monitor_cust_rule.
        $table = new xmldb_table('block_risk_monitor_cust_rule');
        $field = new xmldb_field('low_mod_risk_cutoff', XMLDB_TYPE_INTEGER, '3', null, XMLDB_NOTNULL, null, '50', 'max_score');

        // Conditionally launch add field mod_risk_floor.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        
                // Define field high_risk_floor to be added to block_risk_monitor_cust_rule.
        $table = new xmldb_table('block_risk_monitor_cust_rule');
        $field = new xmldb_field('mod_high_risk_cutoff', XMLDB_TYPE_INTEGER, '3', null, XMLDB_NOTNULL, null, '75', 'low_mod_risk_cutoff');

        // Conditionally launch add field high_risk_floor.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }


        // Test_risk_monitor savepoint reached.
        upgrade_block_savepoint(true, 2014260844, 'risk_monitor');
    }

    if ($oldversion < 2014260845) {

        // Define table block_risk_monitor_int_tmp to be created.
        $table = new xmldb_table('block_risk_monitor_int_tmp');

        // Adding fields to table block_risk_monitor_int_tmp.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('name', XMLDB_TYPE_TEXT, null, null, XMLDB_NOTNULL, null, null);
        $table->add_field('description', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('instructions', XMLDB_TYPE_TEXT, null, null, XMLDB_NOTNULL, null, null);
        $table->add_field('url', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('timestamp', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('categoryid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('courseid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('has_files', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0');

        // Adding keys to table block_risk_monitor_int_tmp.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->add_key('userid', XMLDB_KEY_FOREIGN, array('userid'), 'user', array('id'));
        $table->add_key('categoryid', XMLDB_KEY_FOREIGN, array('categoryid'), 'block_risk_monitor_category', array('id'));
        $table->add_key('courseid', XMLDB_KEY_FOREIGN, array('courseid'), 'course', array('id'));

        // Conditionally launch create table for block_risk_monitor_int_tmp.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Test_risk_monitor savepoint reached.
        upgrade_block_savepoint(true, 2014260845, 'risk_monitor');
    }

    if ($oldversion < 2014260846) {

        // Define field title to be added to block_risk_monitor_int_tmp.
        $table = new xmldb_table('block_risk_monitor_int_tmp');
        $field = new xmldb_field('title', XMLDB_TYPE_TEXT, null, null, XMLDB_NOTNULL, null, null, 'has_files');

        // Conditionally launch add field title.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Test_risk_monitor savepoint reached.
        upgrade_block_savepoint(true, 2014260846, 'risk_monitor');
    }
    
    if ($oldversion < 2014260847) {

        // Define field contextid to be added to block_risk_monitor_int_tmp.
        $table = new xmldb_table('block_risk_monitor_int_tmp');
        $field = new xmldb_field('contextid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'title');

        // Conditionally launch add field contextid.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        
                // Define key contextid (foreign) to be added to block_risk_monitor_int_tmp.
        $table = new xmldb_table('block_risk_monitor_int_tmp');
        $key = new xmldb_key('contextid', XMLDB_KEY_FOREIGN, array('contextid'), 'context', array('id'));

        // Launch add key contextid.
        $dbman->add_key($table, $key);

        // Test_risk_monitor savepoint reached.
        upgrade_block_savepoint(true, 2014260847, 'risk_monitor');
    }
    
    
    return true;
}
