<?php
  namespace HappyFramework\Abstracts;

  use HappyFramework\Ajax;

  /**
   * Class ShortcodeTinyMCE
   *
   * @package HappyFramework\Abstracts
   */
  class TinyMceSubmenu
  {
    public $id;
    public $label;
    public $items;

    /**
     * @constructor
     * @param string $id
     * @param string $label
     * @param array  $items
     */
    public function __construct($id, $label, $items)
    {
      $this->id = $id;
      $this->label = $label;
      $this->items = $items;

      if (current_user_can('edit_posts') || current_user_can('edit_pages')) {
        $this->initialize();
      }
    }

    /**
     * Initialize shortcode to tiny mce
     */
    private function initialize()
    {
      add_filter('mce_external_plugins', array($this, 'addButton'), 1000);
      add_filter('mce_buttons', array($this, 'registerButton'), 1000);
      add_action('admin_head', array($this, 'addMenuItemsJavascript'));

      Ajax::register('get_tinymce_template', array($this, 'getTinymceTemplate'), false, false);
    }

    /**
     * Get tinymce template
     *
     * @param string $_REQUEST ['editor_id']
     * @param string $_REQUEST ['type']
     * @return string
     */
    public function getTinymceTemplate()
    {
      $editor_id = (string)$_REQUEST['editor_id'];
      $template = (string)$_REQUEST['template'];
      $type = (string)$_REQUEST['type'];

      ob_start();
      include dirname(__FILE__) . '/../../templates/tinymce-popup-before.php';
      include(locate_template($template));
      include dirname(__FILE__) . '/../../templates/tinymce-popup-after.php';

      return ob_get_clean();
    }

    /**
     * Add menu to variable javascript `tinymce_submenus`
     *
     * @return void
     */
    public function addMenuItemsJavascript()
    {
      ?>
      <script>
        window.tinymce_submenus = window.tinymce_submenus || [];
        window.tinymce_submenus.push({
          id:    '<?php echo $this->id ?>',
          label: '<?php echo $this->label ?>',
          items: '<?php echo str_replace("'", "\\'", json_encode($this->items)) ?>'
        });
      </script>
    <?php
    }

    /**
     * Add button to tiny mce
     *
     * @param array $plugin_array
     * @return array
     */
    public function addButton($plugin_array)
    {
      $plugin_array[$this->id] = str_replace(array('http:', 'https:'), '', get_template_directory_uri()) . '/happy-framework/js/tinymce-submenu-generator.js';

      return $plugin_array;
    }

    /**
     * Register button to tiny mce
     *
     * @param array $buttons
     * @return array
     */
    public function registerButton($buttons)
    {
      array_push($buttons, $this->id);

      return $buttons;
    }
  }
