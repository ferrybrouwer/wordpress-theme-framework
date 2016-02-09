<?php
  namespace HappyFramework\Options;

  use HappyFramework\Abstracts\AbstractOptionMenuItem;

  /**
   * Class OptionSubmenuItem
   *
   * @package HappyFramework\Options
   */
  class OptionSubMenuItem extends AbstractOptionMenuItem
  {
    protected $parentSlug;

    public function __construct($title, $optionKey, $parentSlug, $slug, $callback = '')
    {
      parent::__construct($title, $optionKey, $slug, $callback);
      $this->parentSlug = $parentSlug;
    }

    /**
     * Add submenu page
     *
     * @hook admin_menu
     */
    public function addAdminMenu()
    {
      add_submenu_page(
        $this->parentSlug,
        $this->title,
        $this->title,
        'manage_options',
        $this->slug,
        array($this, 'adminMenuPageHtml')
      );
    }
  }
