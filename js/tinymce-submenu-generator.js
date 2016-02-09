(function ($, ajaxurl) {
  'use strict';

  // update submenu count
  window.current_submenu_count = window.current_submenu_count || 0;
  window.current_submenu_count++;

  // get current submenu
  var submenu = window.tinymce_submenus[window.current_submenu_count - 1];

  // add submenu to tinymce
  tinymce.PluginManager.add(submenu.id, function (editor, url) {
    var items = JSON.parse(submenu.items);

    _.each(items, function (item) {
      // add command
      editor.addCommand(item.id, function () {
        editor.windowManager.open({
          title:  item.label,
          inline: 1,
          width:  850,
          height: window.innerHeight - 100,
          url:    ajaxurl + '?action=get_tinymce_template&type=' + item.id + '&editor_id=' + editor.id + '&template=' + item.template
        });
      });

      // add menu item
      editor.addMenuItem(item.id, {
        text:    item.label,
        context: 'insert',
        icon:    'shortcodes-' + item.id,
        cmd:     item.id
      });
    });
  });

})(jQuery, window.ajaxurl);
