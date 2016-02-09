<?php
  namespace HappyFramework\Options;

  use HappyFramework\Interfaces\IOptionSettingsSection;

  /**
   * Class SettingsSection
   *
   * @package HappyFramework\Options
   */
  class SettingsSection implements IOptionSettingsSection
  {
    public $id;
    public $optionKey;
    public $slug;
    public $title;
    public $description;

    /**
     * @constructor
     * @param string $id
     * @param string $optionKey
     * @param string $title
     * @param string $slug
     * @param string $description
     */
    public function __construct($id, $optionKey, $title, $slug, $description = '')
    {
      $this->id = $id;
      $this->optionKey = $optionKey;
      $this->title = $title;
      $this->slug = $slug;
      $this->description = $description;

      add_settings_section($this->id, $this->title, array($this, 'printDescription'), $this->slug);
    }

    /**
     * Print section description
     */
    public function printDescription()
    {
      echo $this->description;
    }

    /**
     * Add settings image field
     *
     * @param string $id
     * @param string $label
     * @param null   $imageSizePreview
     * @param null   $imageSizeAttachment
     * @param string $frameTitle
     * @param string $frameButtonLabel
     * @param string $deleteLabel
     * @param string $description
     */
    public function addImage($id, $label, $imageSizePreview = null, $imageSizeAttachment = null, $frameTitle = '', $frameButtonLabel = '', $deleteLabel = '', $description = '')
    {
      add_settings_field($id, $label, array('HappyFramework\Helpers\Settingsfield', 'image'), $this->slug, $this->id, array(
        'optionKey'          => $this->optionKey,
        'name'               => $id,
        'preview_size'       => $imageSizePreview,
        'update_size'        => $imageSizeAttachment,
        'frame_title'        => !empty($frameTitle) ? $frameTitle : __('Select an image', 'ac'),
        'frame_button_label' => !empty($frameButtonLabel) ? $frameButtonLabel : __('Use image', 'ac'),
        'delete_label'       => !empty($deleteLabel) ? $deleteLabel : __('Delete image', 'ac'),
        'description'        => $description,
      ));
    }

    /**
     * Add settings editor field
     *
     * @param string $id
     * @param string $label
     * @param string $description
     * @param array  $editorSettings
     */
    public function addEditor($id, $label, $description = '', $editorSettings = array('tinymce' => array('height' => 150)))
    {
      add_settings_field($id, $label, array('HappyFramework\Helpers\Settingsfield', 'editor'), $this->slug, $this->id, array(
        'optionKey'   => $this->optionKey,
        'name'        => $id,
        'editor'      => $editorSettings,
        'description' => $description
      ));
    }

    /**
     * Add settings text field
     *
     * @param string $id
     * @param string $label
     * @param string $placeholder
     * @param string $type
     * @param string $description
     * @param array  $attributes // additional attributes
     */
    public function addTextField($id, $label, $placeholder = '', $type = 'text', $description = '', $attributes = array())
    {
      add_settings_field($id, $label, array('HappyFramework\Helpers\Settingsfield', 'textfield'), $this->slug, $this->id, array(
        'optionKey'   => $this->optionKey,
        'name'        => $id,
        'placeholder' => $placeholder,
        'type'        => $type,
        'description' => $description,
        'attributes'  => $attributes
      ));
    }

    /**
     * Add settings textarea field
     *
     * @param string $id
     * @param string $label
     * @param string $placeholder
     * @param string $description
     */
    public function addTextArea($id, $label, $placeholder = '', $description = '')
    {
      add_settings_field($id, $label, array('HappyFramework\Helpers\Settingsfield', 'textarea'), $this->slug, $this->id, array(
        'optionKey'   => $this->optionKey,
        'name'        => $id,
        'placeholder' => $placeholder,
        'description' => $description
      ));
    }

    /**
     * Add settings dropdown pages field
     *
     * @param string $id
     * @param string $label
     * @param bool   $optionNone
     * @param string $description
     */
    public function addDropdownPages($id, $label, $optionNone = false, $description = '')
    {
      add_settings_field($id, $label, array('HappyFramework\Helpers\Settingsfield', 'dropdownpages'), $this->slug, $this->id, array(
        'optionKey'   => $this->optionKey,
        'name'        => $id,
        'optionNone'  => $optionNone,
        'description' => $description
      ));
    }

    /**
     * Add settings select field
     *
     * @param string $id
     * @param string $label
     * @param array  $options
     * @param bool   $optionNone
     * @param string $description
     */
    public function addSelect($id, $label, array $options, $optionNone = false, $description = '')
    {
      add_settings_field($id, $label, array('HappyFramework\Helpers\Settingsfield', 'select'), $this->slug, $this->id, array(
        'optionKey'   => $this->optionKey,
        'name'        => $id,
        'optionNone'  => $optionNone,
        'options'     => $options,
        'description' => $description
      ));
    }

    /**
     * Add settings checkbox field
     *
     * @param string $id
     * @param string $label
     * @param string $description
     */
    public function addCheckbox($id, $label, $description = '')
    {
      add_settings_field($id, $label, array('HappyFramework\Helpers\Settingsfield', 'checkbox'), $this->slug, $this->id, array(
        'optionKey'   => $this->optionKey,
        'name'        => $id,
        'label'       => null,
        'description' => $description
      ));
    }

    /**
     * Add colorpicker field
     *
     * @param string $id
     * @param string $label
     * @param string $placeholder
     * @param string $description
     */
    public function addColorPicker($id, $label, $placeholder, $colors = array(), $default = "#ffffff", $irisOptions = array(), $description = '')
    {
      add_settings_field($id, $label, array('HappyFramework\Helpers\Settingsfield', 'colorpicker'), $this->slug, $this->id, array(
        'optionKey'   => $this->optionKey,
        'name'        => $id,
        'placeholder' => $placeholder,
        'colors'      => $colors,
        'default'     => $default,
        'irisOptions' => $irisOptions,
        'description' => $description
      ));
    }

    /**
     * Add settings html field
     *
     * @param string $id
     * @param string $label
     * @param string $html
     */
    public function addHtml($id, $label, $html)
    {
      add_settings_field($id, $label, array('HappyFramework\Helpers\Settingsfield', 'html'), $this->slug, $this->id, array(
        'html' => $html
      ));
    }
  }
