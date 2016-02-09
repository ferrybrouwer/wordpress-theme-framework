<?php
  namespace HappyFramework\Interfaces;

  interface IOptionMenuItem
  {
    /**
     * Get option value by given variable
     *
     * @param string                  $variable
     * @param string                  $default        [optional]
     * @param string                  $imageSize      [optional] when meta data is an attachment
     * @param \AbstractOptionMenuItem $optionInstance [optional] for search in instance
     * @return string|null
     */
    public static function getOption($variable, $default = null, $imageSize = null, $optionInstance = null);

    /**
     * Set option value by given variable
     *
     * @param string                  $variable
     * @param string                  $value
     * @param \AbstractOptionMenuItem $optionInstance
     */
    public static function setOption($variable, $value, $optionInstance = null);

    /**
     * @param string $variable
     * @param \AbstractOptionMenuItem|null $optionInstance
     * @return mixed
     */
    public static function deleteOption($variable, $optionInstance = null);

    /**
     * Add submenu item
     *
     * @param string   $title
     * @param string   $option_key
     * @param string   $slug
     * @param callable $callback [optional]
     */
    public function addSubmenuItem($title, $option_key, $slug, $callback = null);

    /**
     * Add section fields when admin is initialized
     */
    public function addSectionFields();

    /**
     * Create new section in option page
     *
     * @param string $title
     * @param string $description
     */
    public function createSection($title = '', $description = '');

    /**
     * Flush rewrite rules when option is updated
     */
    public function flushRewriteRules();

    /**
     * Register option group and variables
     */
    public function registerOption();

    /**
     * Add option as page to admin menu
     */
    public function addAdminMenu();

    /**
     * Print the HTML of the admin menu page
     */
    public function adminMenuPageHtml();

    /**
     * Add html to the option admin page below te form
     */
    public function adminMenuPageBelowFormHtml();

    /**
     * When options are being updated
     *
     * @param array $oldFields
     * @param array $newFields
     */
    public function updateOptions($oldFields, $newFields);

    /**
     * Before options are being updated
     *
     * @param array $oldFields
     * @param array $newFields
     */
    public function beforeUpdateOptions($oldFields, $newFields);
  }
