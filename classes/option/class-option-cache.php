<?php
  namespace HappyFramework\Options;

  use HappyFramework\Abstracts\AbstractTheme as Theme;

  /**
   * Class CacheOptions
   *
   * @package HappyFramework\Options
   */
  class Cache extends OptionSubmenuItem
  {

    public static $okey  = 'transient_cache';
    public static $oslug = 'transient-cache';

    public function __construct()
    {
      parent::__construct(__('Transient Cache', Theme::$domain), self::$okey, 'options-general.php', self::$oslug);
      add_action('admin_init', array($this, 'handleClearCache'));
      add_action('update_option_transient_cache', array($this, 'transientOptionUpdate'));
    }

    /**
     * Add section fields
     */
    public function addSectionFields()
    {
      $section = $this->createSection();
      $section->addCheckbox('enable', __('Enable transient cache', Theme::$domain));
    }

    /**
     * Handle clear cache (clicked on the button)
     */
    public function handleClearCache()
    {
      if (!empty($_POST['_wpnonce']) && wp_verify_nonce($_POST['_wpnonce'], 'transient-cache')) {
        $this->clearCache();
      }
    }

    /**
     * Clear all transient cache
     */
    public function clearCache()
    {
      global $wpdb;
      $wpdb->query("DELETE FROM $wpdb->options WHERE `option_name` LIKE '%_transient_%'");
      add_action('admin_notices', function () {
        ?>
        <div class="updated"><p><?php _e('Transient cache has successfully cleared!', Theme::$domain); ?></p></div><?php
      });
    }

    /**
     * Clear transient cache when options are being updated
     *
     * @param array $oldFields
     * @param array $newFields
     */
    public function updateOptions($oldFields, $newFields)
    {
      if (!isset($_POST['transient_cache']) && !isset($_POST['transient_cache']['enabled']) && wp_verify_nonce($_POST['_wpnonce'], $this->optionGroup . '-options')) {
        $this->clearCache();
      }
    }

    /**
     * Print html below form
     */
    public function adminMenuPageBelowFormHtml()
    {
      ?>
    <form
      onsubmit="return confirm('<?php echo str_replace('\'', '\\\'', esc_attr('Are you shure you want the delete all transient cache?', Theme::$domain)) ?>');"
      action="<?php echo admin_url('admin.php?page=transient-cache') ?>" method="post">
      <?php wp_nonce_field('transient-cache') ?>
      <input class="button" type="submit" value="<?php esc_attr_e('Clear all transients cache', Theme::$domain) ?>"/>
      </form><?php
    }
  }
