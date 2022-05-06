<?php
/*
 -------------------------------------------------------------------------
 Task&drop plugin for GLPI
 Copyright (C) 2018 by the TICgal Team.

 https://github.com/ticgal/Task&drop
 -------------------------------------------------------------------------

 LICENSE

 This file is part of the Task&drop plugin.

 Task&drop plugin is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 3 of the License, or
 (at your option) any later version.

 Task&drop plugin is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with Task&drop. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 @package   Task&drop
 @author    the TICgal team
 @copyright Copyright (c) 2018 TICgal team
 @license   AGPL License 3.0 or (at your option) any later version
            http://www.gnu.org/licenses/agpl-3.0-standalone.html
 @link      https://tic.gal
 @since     2018
 ---------------------------------------------------------------------- */

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

class PluginTaskdropProfile extends Profile {

   public static $rightname = 'profile';

   static $all_profile_rights = array(
		PluginTaskdropCalendar::class
	);
   
   /**
    * getTabNameForItem
    *
    * @param  Object $item
    * @param  Int $withtemplate
    * @return String
    */
   public function getTabNameForItem(CommonGLPI $item, $withtemplate = 0) {
		if ($item->getType() == 'Profile') {
			return PluginTaskdropCalendar::getTypeName(0);
		}
		return '';
	}
   
   /**
    * displayTabContentForItem
    *
    * @param  Object $item
    * @param  Int $tabnum
    * @param  Int $withtemplate
    * @return Boolean
    */
   static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0) {
		$_profile = new self();

		if ($item->getType() == 'Profile') {
			$id = $item->getID();
			self::addRight(false, 0);
			$_profile->showForm($id);
		}

		return true;
	}
   
   /**
    * install
    *
    * @param  Object $migration
    * @return void
    */
   static function install(Migration $migration) {
		// Create admin access
      self::addRight($_SESSION['glpiactiveprofile']['id'], READ + READTASK + READTICKET + READREMINDER);
	}
	
	/**
	 * uninstall
	 *
	 * @return Boolean
	 */
	static function uninstall() {
		// Remove all profiles
		self::removeAllRight();
		return true;
	}
   
   /**
    * removeAllRight
    *
    * @return void
    */
   static function removeAllRight() {
		$_profile = new ProfileRight();

		foreach ($_profile->find(['name' => 'PluginTaskdropCalendar']) as $data) {
			$_profile->delete($data);
		}
	}
		
	/**
	 * addRight
	 *
	 * @param  Int $ID
    * @param Int $rightLvl
	 * @return void
	 */
	static function addRight($profiles_id, $rightLvl) {
		$_profile = new ProfileRight();
		foreach (self::$all_profile_rights as $profile_name) {
			if (!$_profile->find("`profiles_id` = $profiles_id and `name` = '$profile_name'")) {
				$right['profiles_id'] = $profiles_id ?: $_SESSION['glpiactiveprofile']['id'];
				$right['name'] = $profile_name;
				$right['rights'] = $rightLvl;
				$_profile->add($right);
			}
		}
	}

   
   /**
    * showForm
    *
    * @param  Int $profiles_id
    * @param  Boolean $openform
    * @param  Boolean $closeform
    * @return Boolean
    */
   public function showForm($profiles_id = 0, $openform = true, $closeform = true) {
		$profile = new Profile();
		$canedit = Session::haveRightsOr(self::$rightname, array(READ, CREATE));

		echo "<div class='firstbloc'>";
		if ($canedit && $openform) {
			echo "<form method='post' action='" . $profile->getFormURL() . "'>";
		}

		$profile->getFromDB($profiles_id);

		$config_right = $this->getAllRights();

		$profile->displayRightsChoiceMatrix($config_right, array(
         'canedit' => $canedit,
         'default_class' => 'tab_bg_2',
         'title' => __s('General'))
      );

		if ($canedit && $closeform) {
			echo "<div class='center'>";
			echo Html::hidden('id', array('value' => $profiles_id));
			echo Html::submit(_sx('button', 'Save'), array('name' => 'update'));
			echo "</div>";
			Html::closeForm();
		}
      
		echo "</div>";

		$this->showLegend();

		return true;
	}
   
   /**
    * getAllRights
    *
    * @return Int
    */
   static function getAllRights() {
		$rights = array(
         array(
            'itemtype' => PluginTaskdropCalendar::class,
            'label' => __s('TaskDrop'),
            'field' => 'PluginTaskdropCalendar',
            'rights' => [
               READ => __('Read'), 
               READTASK => __('Plan this task'), 
               READTICKET => __('Tickets'),
               READREMINDER => __('Planning reminder')
            ]
         ),
		);

		return $rights;
	}

}