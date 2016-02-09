<?php
  namespace HappyFramework\Abstracts;

  use HappyFramework\Abstracts\AbstractTheme as Theme;
  use HappyFramework\Interfaces\iOptionMenuItem;
  use HappyFramework\Options\OptionSubMenuItem;
  use HappyFramework\Options\SettingsSection;

  /**
   * Class OptionMenuItem
   *
   * @package HappyFramework\Options
   */
  abstract class AbstractOptionMenuItem implements IOptionMenuItem
  {
    public $callback;
    public $slug;
    public $title;
    public $optionKey;
    public $optionGroup;
    private $customIcon;

    public function __construct($title, $optionKey, $slug, $callback = '')
    {
      $this->title = $title;
      $this->optionKey = $optionKey;
      $this->optionGroup = sanitize_html_class($this->optionKey) . '-group';
      $this->slug = $slug;
      $this->callback = $callback;

      add_action('admin_init', array($this, 'registerOption'));
      add_action('admin_menu', array($this, 'addAdminMenu'));
      add_action('admin_init', array($this, 'addSectionFields'));
      add_action('update_option_' . $this->optionKey, array($this, 'updateOptions'), 99, 2);
      add_filter('pre_update_option_' . $this->optionKey, array($this, 'beforeUpdateOptions'), 99, 2);
    }

    /**
     * Set icon
     *
     * @param string $iconName
     */
    public function setIcon($iconName)
    {
      $this->customIcon = $iconName;
    }

    /**
     * When options are being updated
     *
     * @param array $oldFields
     * @param array $newFields
     */
    public function updateOptions($oldFields, $newFields)
    {
      $this->flushRewriteRules();
    }

    /**
     * Before updating options
     *
     * @param array $newFields
     * @param array $oldFields
     * @return array
     */
    public function beforeUpdateOptions($newFields, $oldFields)
    {
      return $newFields;
    }

    /**
     * Flush rewrite rules when option is updated
     */
    public function flushRewriteRules()
    {
      flush_rewrite_rules(false);
    }

    /**
     * Create section
     *
     * @param string $title       [optional]
     * @param string $description [optional
     * @return SettingsSection
     */
    public function createSection($title = '', $description = '')
    {
      return new SettingsSection(uniqid('section_id_'), $this->optionKey, $title, $this->slug, $description);
    }

    /**
     * Add submenu item
     *
     * @param string $title
     * @param string $optionKey
     * @param string $slug
     * @param string $callback
     * @return OptionSubMenuItem
     */
    public function addSubmenuItem($title, $optionKey, $slug, $callback = '')
    {
      return new OptionSubMenuItem($title, $optionKey, $this->slug, $slug, $callback);
    }

    /**
     * Register settings
     *
     * @hook admin_init
     */
    public function registerOption()
    {
      if (!current_user_can('edit_theme_options')) {
        return;
      }

      register_setting($this->optionGroup, $this->optionKey);
    }

    /**
     * Add menu item to admin
     *
     * @hook admin_menu
     */
    public function addAdminMenu()
    {
      add_menu_page(
        $this->title,
        $this->title,
        'edit_theme_options',
        $this->slug,
        array($this, 'adminMenuPageHtml'),
        $this->customIcon ?: 'dashicons-' . sanitize_html_class($this->optionKey)
      );
    }

    /**
     * Print the HTML of the admin menu page
     */
    public function adminMenuPageHtml()
    {
      if (!current_user_can('edit_theme_options')) {
        wp_die(__('You do not have permission to customize headers options.'));
      } ?>
      <div class="wrap">
      <h2><?php echo $this->title; ?></h2>

      <?php if ($this->optionKey): ?>
      <form method="post" enctype="multipart/form-data" action="options.php">
        <?php settings_fields($this->optionGroup) ?>
        <?php do_settings_sections($this->slug) ?>
        <?php
          if (!empty($this->callback)) {
            call_user_func($this->callback);
          }
        ?>
        <?php submit_button() ?>
      </form>
    <?php endif; ?>

      <?php $this->adminMenuPageBelowFormHtml(); ?>
      </div><?php
    }

    /**
     * Add html to the option admin page below te form
     */
    public function adminMenuPageBelowFormHtml()
    {
    }

    /**
     * Get option value by given variable
     *
     * @param string                  $variable
     * @param string                  $default        [optional]
     * @param string                  $imageSize      [optional] when meta data is an attachment
     * @param \AbstractOptionMenuItem $optionInstance [optional] for search in instance
     * @return string|null
     */
    public static function getOption($variable, $default = null, $imageSize = null, $optionInstance = null)
    {
      $value = null;

      /* @var AbstractOptionMenuItem $instance */
      $instance = $optionInstance ?: Theme::getOptionInstanceByClassName(get_called_class());

      if ($instance) {
        $value = '';
        $options = get_option($instance->optionKey);

        if ($options && !empty($options[$variable])) {
          $value = $options[$variable];
        }
        if (!empty($default) && empty($value)) {
          $value = $default;
        }
        if (is_array($value) && array_key_exists('attachment', $value)) {
          $value = $value['attachment'];
          $attachmentId = (int)$value['id'];
          if ($imageSize && $attachment = wp_get_attachment_image_src($attachmentId, $imageSize)) {
            $value = $attachment[0];
          } else {
            $value = !empty($value['url']) ? $value['url'] : null;
          }
        }
      }

      return $value;
    }

    /**
     * Set option value by given variable
     *
     * @param string                  $variable
     * @param string                  $value
     * @param \AbstractOptionMenuItem $optionInstance
     */
    public static function setOption($variable, $value, $optionInstance = null)
    {
      /* @var AbstractOptionMenuItem $instance */
      $instance = $optionInstance ?: Theme::getOptionInstanceByClassName(get_called_class());

      if ($instance) {
        $options = get_option($instance->optionKey);
        $options[$variable] = $value;

        update_option($instance->optionKey, $options);
      }
    }

    /**
     * Delete option by given variable
     *
     * @param string $variable
     * @param null   $optionInstance
     */
    public static function deleteOption($variable, $optionInstance = null)
    {
      /* @var AbstractOptionMenuItem $instance */
      $instance = $optionInstance ?: Theme::getOptionInstanceByClassName(get_called_class());

      if ($instance) {
        $options = get_option($instance->optionKey);
        if (isset($options[$variable])) {
          unset($options[$variable]);

          update_option($instance->optionKey, $options);
        }
      }
    }

    /**
     * Add section fields when admin is initialized
     */
    public function addSectionFields()
    {
    }
  }
