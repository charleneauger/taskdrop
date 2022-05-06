var modalWindow;
var modalTask;

$(function() {
   modalWindow = $("<div></div>").dialog({
      resizable: false,
      width: '200',
      autoOpen: false,
      height: '150',
      modal: true,
      position: {
         my: 'center'
      },
      open: function( event, ui ) {
         //remove existing tinymce when reopen modal (without this, tinymce don't load on 2nd opening of dialog)
         modalWindow.find('.mce-container').remove();
      }
   });
});

$(function() {
   modalTask = $("<div></div>").dialog({
      resizable: false,
      width: '860',
      autoOpen: false,
      height: '500',
      modal: true,
      position: {
         my: 'center'
      },
      open: function( event, ui ) {
         //remove existing tinymce when reopen modal (without this, tinymce don't load on 2nd opening of dialog)
         modalTask.find('.mce-container').remove();
      }
   });
});

 var plugin_Taskdrop = new function() {

   this.modalSettings = {
      resizable: false,
      width: '200',
      autoOpen: false,
      height: '150',
      modal: true,
      position: {
         my: 'center'
      },
      close: function() {
         $(this).dialog('close');
         $(this).remove();
      }
   }

   this.showTicketStatus = function () {
      modalWindow.load(
         '/glpi/plugins/taskdrop/ajax/planning.php?ticket_status=update_ticket_status'
      ).dialog('open');
   }

   this.showCreateTask = function (id, date) {
      modalTask.load(
         '/glpi/plugins/taskdrop/ajax/planning.php?create_task='+id+"&start="+date
      ).dialog('open');
   }

}