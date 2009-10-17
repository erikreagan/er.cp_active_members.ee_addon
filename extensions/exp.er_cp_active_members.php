<?php

/**
 * ER CP Active Members
 * 
 * This file must be placed in the
 * /system/extensions/ folder in your ExpressionEngine installation.
 *
 * @package ERCPActiveMembers
 * @version 1.0
 * @author Erik Reagan http://erikreagan.com
 * @copyright Copyright (c) 2009 Erik Reagan
 * @see http://erikreagan.com/projects/er_cp_active_members/
 */


if ( ! defined('EXT')) exit('Invalid file request');


class Er_cp_active_members
{
   
   var $settings = array();

   var $name = 'ER CP Active Members';
   var $version = '1.0';
   var $description = 'Displays active members in the footer of the CP';
   var $settings_exist = 'y';
   var $docs_url = '';


   /**
   * PHP4 Constructor
   *
   * @see __construct()
   */

   function Er_cp_active_members($settings='')
   {
      $this->__construct($settings);
   }

   
   /**
   * PHP 5 Constructor
   *
   * @param array|string  Extension settings associative array or an empty string
   */
   function __construct($settings='')
   {
      $this->settings = $settings;
   }


   
   /**
   * Configuration for the extension settings page
   *
   * @return array
   */
      function settings()
      {
         global $LANG, $DB, $PREFS;

         // Grab the member groups from our current site
         $member_groups = $DB->query("SELECT group_id,site_id,group_title FROM exp_member_groups WHERE `site_id` = " . $PREFS->ini("site_id"));

         // Create an array of our member groups in the format that $settings needs
         foreach ($member_groups->result as $group)
         {
            $member_groups_array[$group['group_id']] = $group['group_title'];
         }

         $settings = array();
         $settings['groups'] = array('ms', $member_groups_array, '1');
         return $settings;
      }
      
      
      
   /**
   * Activates the extension
   *
   * @return bool
   */
   function activate_extension()
   {
      global $DB;

      $settings = array(
               'groups' => array('1')
               );

      $hooks = array(
         'show_full_control_panel_end' => 'show_full_control_panel_end'
      );

      foreach ($hooks as $hook => $method)
      {
         $sql[] = $DB->insert_string('exp_extensions',
            array(
               'extension_id' => '',
               'class'        => get_class($this),
               'method'       => $method,
               'hook'         => $hook,
               'settings'     => serialize($settings),
               'priority'     => 10,
               'version'      => $this->version,
               'enabled'      => "y"
            )
         );
      }

      // run all sql queries
      foreach ($sql as $query)
      {
         $DB->query($query);
      }
      
      return TRUE;
   }
   
   
   
   /**
    * Update the extension
    *
    * @param string
    * @return bool
    **/
   function update_extension($current='')
   {
       global $DB;

       if ($current == '' OR $current == $this->version)
       {
           return FALSE;
       }

       $DB->query("UPDATE exp_extensions 
                   SET version = '".$DB->escape_str($this->version)."' 
                   WHERE class = 'Er_cp_active_members'");
   }
   
   
   
   /**
   * Disables the extension the extension and deletes settings from DB
   */
   function disable_extension()
   {
       global $DB;
       $DB->query("DELETE FROM exp_extensions WHERE class = 'Er_cp_active_members'");
   }
   
   
   
   /**
    * Alter Control Panel by adding member list
    *
    * @return string
    **/
   function show_full_control_panel_end( $out )
   {
      global $EXT, $STAT, $SESS;
      $STAT = new Stats_CP();
      $STAT->update_stats();
      
      
      if ( ! in_array($SESS->userdata['group_id'], $this->settings['groups']) )
      {
         return $out;
      }
      
      
      if($EXT->last_call !== FALSE)
      {
         $out = $EXT->last_call;
      }
      
      foreach ($STAT->stats['current_names'] as $member) {
         $list[] = $member[0];
      }
      
      $list = implode(', ', $list);
      
      $find = "<div class='copyright'>";
      $add = "
      <div style='margin:0 19px;padding:10px;background:#ccc;border:1px solid #555;'>
      <p><strong>Active Members:</strong> $list</p>
</div>
".$find;

      return str_replace($find, $add, $out);
      
      
   }
   
   
}
// END class

/* End of file ext.er_cp_active_members.php */
/* Location: ./system/extensions/ext.er_er_cp_active_members.php */