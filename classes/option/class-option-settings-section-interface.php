<?php
  namespace HappyFramework\Interfaces;

  interface IOptionSettingsSection
  {
    /**
     * Print section description
     *
     * @return mixed
     */
    public function printDescription();

    /**
     * Add settings image field
     *
     * @param string $id
     * @param string $label
     * @param null   $imageSizePreview    [optional]
     * @param null   $imageSizeAttachment [optional]
     * @param string $frameTitle          [optional]
     * @param string $frameButtonLabel    [optional]
     * @param string $deleteLabel         [optional]
     * @param string $description         [optional]
     */
    public function addImage($id, $label, $imageSizePreview = null, $imageSizeAttachment = null, $frameTitle = '', $frameButtonLabel = '', $deleteLabel = '', $description = '');

    /**
     * Add settings editor field
     *
     * @param string $id
     * @param string $label
     * @param string $description    [optional]
     * @param array  $editorSettings [optional]
     */
    public function addEditor($id, $label, $description = '', $editorSettings = array('tinymce' => array('height' => 150)));

    /**
     * Add settings text field
     *
     * @param string $id
     * @param string $label
     * @param string $placeholder [optional]
     * @param string $type        [optional]
     * @param string $description [optional]
     */
    public function addTextField($id, $label, $placeholder = '', $type = 'text', $description = '');

    /**
     * Add settings textarea field
     *
     * @param string $id
     * @param string $label
     * @param string $placeholder [optional]
     * @param string $description [optional]
     */
    public function addTextArea($id, $label, $placeholder = '', $description = '');

    /**
     * Add settings dropdown pages field
     *
     * @param string $id
     * @param string $label
     * @param bool   $optionNone  [optional]
     * @param string $description [optional]
     */
    public function addDropdownPages($id, $label, $optionNone = false, $description = '');

    /**
     * Add settings select field
     *
     * @param string $id
     * @param string $label
     * @param array  $options
     * @param bool   $optionNone  [optional]
     * @param string $description [optional]
     */
    public function addSelect($id, $label, array $options, $optionNone = false, $description = '');

    /**
     * Add settings checkbox field
     *
     * @param string $id
     * @param string $label
     * @param string $description [optional]
     */
    public function addCheckbox($id, $label, $description = '');

    /**
     * Add settings html field
     *
     * @param string $id
     * @param string $label
     * @param string $html
     */
    public function addHtml($id, $label, $html);
  }
