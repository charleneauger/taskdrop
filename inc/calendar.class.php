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

class PluginTaskdropCalendar extends CommonDBTM{

   public static $rightname = 'calendar';
   
   /**
    * getTypeName
    *
    * @param  Int $nb
    * @return String
    */
   static function getTypeName($nb = 0) {
      return __('TaskDrop', 'TaskDrop');
   }
   
   /**
    * addTask
    *
    * @return String
    */
   static function addTask() {
      global $DB;

      $div="<h3>".__('Plan this task')."</h3>";
      foreach ($_SESSION['glpi_plannings']['plannings'] as $key => $value) {
         if (preg_match('/^user_/', $key)) {
            if ($value['display']==1) {
               $actor = explode('_', $key);
               $query=[
                  'FROM'=>'glpi_tickettasks',
                  'WHERE'=>[
                     'state'=>1,
                     'begin'=>null,
                     'users_id_tech'=>$actor[1],
                  ]
               ];
               foreach ($DB->request($query) as $id => $row) {
                  $div.="<div class='fc-event-external event_type' style='padding:2px;margin:2px;background-color: ".$value['color'].";' tid=".$row['id']." action='add_task'>".Toolbox::addslashes_deep(HTML::clean($row['content']))."</div>";
               }
            }
         }else{
         	if (preg_match('/^group_/', $key)) {
         		if ($value['display']==1) {
         			$group=explode('_', $key);
         			$query=[
	                  'FROM'=>'glpi_tickettasks',
	                  'WHERE'=>[
	                     'state'=>1,
	                     'begin'=>null,
	                     'groups_id_tech'=>$group[1],
	                  ]
	               ];
	               foreach ($DB->request($query) as $id => $row) {
	                  $div.="<div class='fc-event-external' style='padding:2px;margin:2px;background-color: ".$value['color'].";' tid=".$row['id']." action='add_task'>".Toolbox::addslashes_deep(HTML::clean($row['content']))."</div>";
	               }
         		}
         	}
         }
      }
      return $div;
   }
   
   /**
    * addTicket
    *
    * @return String
    */
   static function addTicket($status = 2) {
      global $DB, $CFG_GLPI;

      $allStatus = Ticket::getAllStatusArray();

      if(isset($_SESSION['taskdrop']['tickets_status'])) {
         $status = $_SESSION['taskdrop']['tickets_status'];
      }

      $div="<h3>".__('Tickets')." - ".$allStatus[$status]."<a class='planning_link planning_add_filter' href='javascript:plugin_Taskdrop.showTicketStatus();'><img class='pointer' id='ticket_status_filter' src='".$CFG_GLPI['root_doc']."/pics/menu_search.png'></a></h3>";

      foreach ($_SESSION['glpi_plannings']['plannings'] as $key => $value) {
         if (preg_match('/^user_/', $key)) {
            if ($value['display']==1) {
               $actor = explode('_', $key);
               $query=[
                  'SELECT'=>[
                     'glpi_tickets.*',
                     'glpi_tickets_users.*',
                  ],
                  'FROM'=>'glpi_tickets',
                  'LEFT JOIN'=>[
                     'glpi_tickets_users'=>[
                        'FKEY'=>[
                           'glpi_tickets'=>'id',
                           'glpi_tickets_users'=>'tickets_id',
                        ]
                     ]
                  ],
                  'WHERE'=>[
                     'glpi_tickets_users.type'=>2,
                     'glpi_tickets.status'=>$status,
                     'glpi_tickets.is_deleted'=>0,
                     'glpi_tickets_users.users_id'=>$actor[1]
                  ]
               ];

               foreach ($DB->request($query) as $id => $row) {
                  $div.="<div class='fc-event-external' style='padding:2px;margin:2px;background-color: ".$value['color'].";' tid=".$row['tickets_id']." action='create_task'>".Toolbox::addslashes_deep(HTML::clean($row['tickets_id']." - ".$row['name']))."</div>";
               }
            }
         } elseif (preg_match('/^group_/', $key)){
            if ($value['display']==1) {
               $group=explode('_', $key);
               $query=[
                  'SELECT'=>[
                     'glpi_tickets.*',
                     'glpi_groups_tickets.*',
                  ],
                  'FROM'=>'glpi_tickets',
                  'LEFT JOIN'=>[
                     'glpi_groups_tickets'=>[
                        'FKEY'=>[
                           'glpi_tickets'=>'id',
                           'glpi_groups_tickets'=>'tickets_id',
                        ]
                     ]
                  ],
                  'WHERE'=>[
                     'glpi_groups_tickets.type'=>2,
                     'glpi_tickets.status'=>$status,
                     'glpi_tickets.is_deleted'=>0,
                     'glpi_groups_tickets.groups_id'=>$group[1]
                  ]
               ];

               foreach ($DB->request($query) as $id => $row) {
                  $div.="<div class='fc-event-external' style='padding:2px;margin:2px;background-color: ".$value['color'].";' tid=".$row['tickets_id']." action='create_task'>".Toolbox::addslashes_deep(HTML::clean($row['tickets_id']." - ".$row['name']))."</div>";
               }
            }
         }
      }
      
      return $div;
   }
   
   /**
    * addReminder
    *
    * @return String
    */
   static function addReminder() {
      global $DB;

      $div="<h3>".__('Planning reminder')."</h3>";
      foreach ($_SESSION['glpi_plannings']['plannings'] as $key => $value) {
         if (preg_match('/^user_/', $key)) {
            if ($value['display']==1) {
               $actor = explode('_', $key);
               $query=[
                  'FROM'=>'glpi_reminders',
                  'WHERE'=>[
                     'state'=>1,
                     'begin'=>null,
                     'users_id'=>$actor[1],
                  ]
               ];
               foreach ($DB->request($query) as $id => $row) {
                  $div.="<div class='fc-event-external' style='padding:2px;margin:2px;background-color: ".$value['color'].";' tid=".$row['id']." action='add_reminder'>".Toolbox::addslashes_deep(HTML::clean($row['name']))."</div>";
               }
            }
         }
      }
      return $div;
   }
   
   /**
    * listTask
    *
    * @param  Array $params
    * @return void
    */
   static function listTask($params) {
      global $CFG_GLPI;

      $options=$params['options'];
      if ($options['itemtype']!='Planning') {
         return;
      }
      $div="<div id='external-events'>";
      $div.=self::addTask();
      $div.=self::addTicket();
      $div.=self::addReminder();
      $div.="</div>";

      $ajax_url=Plugin::getWebDir('taskdrop')."/ajax/planning.php";

      $script=<<<JAVASCRIPT
		$(document).ready(function() {

         $('#planning_filter_content').append("{$div}");

			var Draggable = FullCalendarInteraction.Draggable;
			var containerEl = document.getElementById('external-events');
			new Draggable(containerEl, {
				itemSelector: '.fc-event-external',
			});

			GLPIPlanning.calendar.setOption('editable',true);
			GLPIPlanning.calendar.setOption('droppable',true);
			GLPIPlanning.calendar.setOption('dropAccept','.fc-event-external');
			GLPIPlanning.calendar.setOption('dragRevertDuration',0);
			GLPIPlanning.calendar.on('drop',function(dropInfo){
				$.ajax({
					url: '{$ajax_url}',
					type: 'POST',
					data:{
						action: $(dropInfo.draggedEl).attr('action'),
						start: dropInfo.date.toISOString(),
						id: $(dropInfo.draggedEl).attr('tid')
					},
					success: function(event){
                  $(dropInfo.draggedEl).remove();
						GLPIPlanning.refresh();
					},
					error: function(xhr) {
						alert('An error occured: '+ xhr.status + ' ' + xhr.statusText);
					}
				});
			});

			$('#planning_filter li.user input[type="checkbox"],#planning_filter li.group input[type="checkbox"]').on('click',function(){
				setTimeout(function(){
					$.ajax({
	               url:  '{$ajax_url}',
	               type: 'POST',
	               data: {
	                  action:  'update_task'
	               },
	               success: function(div) {
							$('#external-events').html(div);
	               }
	            });
				},500);
			});
         $('#'+GLPIPlanning.dom_id+' .fc-toolbar .fc-center h2')
            .after(
               $('<i id="refresh_planning" class="fa fa-sync pointer"></i>')
            ).after(
               $('<div id="planning_datepicker"><a data-toggle><i class="far fa-calendar-alt fa-lg pointer"></i></a>')
            );
         GLPIPlanning.initFCDatePicker();
      });
JAVASCRIPT;
      echo Html::scriptBlock($script);
   }
   
   /**
    * showTicketStatusForm
    *
    * @return void
    */
   static function showTicketStatusForm() {
      global $CFG_GLPI;

      $rand = mt_rand();
      echo "<form action='".self::getFormURL()."'>";
      echo __("Status").": <br>";

      Dropdown::showFromArray(
         'tickets_status',
         Ticket::getAllStatusArray(),
         [
            'display_emptychoice' => true,
            'rand'                =>  $rand
         ]
      );

      echo "<br /><br />";
      echo Html::hidden('action', ['value' => 'send_change_ticket_status_form']);
      echo Html::submit(_sx('button', 'Add'));
      Html::closeForm();
   }
   
   /**
    * sendTicketStatusForm
    *
    * @param  Array $params
    * @return void
    */
   static function sendTicketStatusForm($params = []) {
      $_SESSION['taskdrop']['tickets_status'] = $params['tickets_status'];
   }

}