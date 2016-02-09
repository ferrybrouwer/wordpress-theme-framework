<?php
  namespace HappyFramework;

  use HappyFramework\Abstracts\AbstractTheme;
  use HappyFramework\Helpers\Formatting;

  class Taxonomy_Meta
  {
    const TYPE_SELECTBOX       = 'selectbox';
    const TYPE_CHECKBOX        = 'checkbox';
    const TYPE_COLOR_PICKER    = 'colorPicker';
    const TYPE_EDITOR          = 'editor';
    const TYPE_IMAGE           = 'image';
    const TYPE_CUSTOM          = 'custom';
    const TYPE_INPUT           = 'input';
    const TYPE_MULTIPLE_FIELDS = 'multipleFields';

    const MODE_EDIT_AND_NEW = 'editAndNewMode';
    const MODE_ONLY_EDIT    = 'onlyEditMode';
    const MODE_ONLY_NEW     = 'onlyNewMode';


    /**
     * Add taxonomy meta data fields
     *
     * @param string $taxonomy
     * @param string $name
     * @param string $title
     * @param string $metaboxType
     * @param array  $callback_args
     * @param string $mode
     */
    public static function add($taxonomy, $name, $title, $metaboxType, $callback_args = array(), $mode = self::MODE_EDIT_AND_NEW)
    {
      // form field
      $form_field = function ($term) use ($taxonomy, $name, $title, $metaboxType, $callback_args, $mode) {
        switch ($metaboxType) {
          case Taxonomy_Meta::TYPE_SELECTBOX:
            Taxonomy_Meta::selectbox($taxonomy, $term, $name, $title, $callback_args, $mode);
            break;
          case Taxonomy_Meta::TYPE_CHECKBOX:
            Taxonomy_Meta::checkbox($taxonomy, $term, $name, $title, $callback_args, $mode);
            break;
          case Taxonomy_Meta::TYPE_COLOR_PICKER:
            Taxonomy_Meta::colorPicker($taxonomy, $term, $name, $title, $callback_args, $mode);
            break;
          case Taxonomy_Meta::TYPE_EDITOR:
            Taxonomy_Meta::editor($taxonomy, $term, $name, $title, $callback_args, $mode);
            break;
          case Taxonomy_Meta::TYPE_IMAGE:
            Taxonomy_Meta::image($taxonomy, $term, $name, $title, $callback_args, $mode);
            break;
          case Taxonomy_Meta::TYPE_CUSTOM:
            Taxonomy_Meta::custom($taxonomy, $term, $name, $title, $callback_args, $mode);
            break;
          case Taxonomy_Meta::TYPE_INPUT:
            Taxonomy_Meta::input($taxonomy, $term, $name, $title, $callback_args, $mode);
            break;
          case Taxonomy_Meta::TYPE_MULTIPLE_FIELDS:
            Taxonomy_Meta::multipleFields($taxonomy, $term, $name, $title, $callback_args, $mode);
            break;
        }
      };

      // add term metabox
      add_action($taxonomy . '_add_form_fields', $form_field, 10, 2);
      add_action($taxonomy . '_edit_form_fields', $form_field, 10, 2);

      // save term metabox
      add_action('edited_' . $taxonomy, array('HappyFramework\Taxonomy_Meta', 'saveMeta'), 10, 1);
      add_action('created_' . $taxonomy, array('HappyFramework\Taxonomy_Meta', 'saveMeta'), 10, 1);

      // delete term metabox
      add_action('delete_term', array('HappyFramework\Taxonomy_Meta', 'deleteTerm'), 9, 4);
    }

    /**
     * Save term meta
     *
     * @param int $term_id
     * @return void
     */
    public static function saveMeta($term_id)
    {
      if (is_int($term_id) && !empty($_POST['taxmeta']) && is_array($_POST['taxmeta'])) {
        // remove option keys with `checkfor` values
        if (!empty($_POST['taxmeta']['checkfor'])) {
          foreach ($_POST['taxmeta']['checkfor'] as $name => $value) {
            if (!array_key_exists($name, $_POST['taxmeta'])) {
              delete_option(Taxonomy_Meta::sanitize_option_key($term_id, $name));
            }
          }
        }

        // update term meta
        foreach ($_POST['taxmeta'] as $key => $value) {
          update_option(Taxonomy_Meta::sanitize_option_key($term_id, $key), !is_array($value) ? stripslashes($value) : $value);
        }
      }
    }

    /**
     * Delete term
     *
     * @param \stdClass $term
     * @param int       $term_id
     * @param string    $taxonomy
     * @param \stdClass $deleted_term
     */
    public static function deleteTerm($term, $term_id, $taxonomy, $deleted_term)
    {
      /* @var \wpdb $wpdb */
      global $wpdb;

      if (is_object($deleted_term)) {
        $wpdb->query($wpdb->prepare("DELETE FROM `" . $wpdb->options . "` WHERE `option_name` LIKE 'term_meta_%d_%%' ", $deleted_term->term_id));
      }
    }

    /**
     * Get meta data from taxonomy
     *
     * @param int    $term_id
     * @param string $name
     * @return mixed
     */
    public static function get($term_id, $name)
    {
      $data = get_option(Taxonomy_Meta::sanitize_option_key($term_id, $name));

      // return url when data is an attachment
      if (is_array($data) && array_key_exists('attachment', $data)) {
        $data = (string)$data['attachment']['url'];
      }

      return $data;
    }

    /**
     * Get a sanitized option key
     *
     * @param int    $term_id
     * @param string $term_key
     * @return string
     */
    public static function sanitize_option_key($term_id, $term_key)
    {
      return 'term_meta_' . sanitize_key($term_id) . '_' . sanitize_key($term_key);
    }

    /**
     * Get all attached custom meta data
     *
     * @param int $term_id
     * @return null|\stdClass
     */
    public static function getAll($term_id)
    {
      // default return value
      $data = null;

      if (is_int($term_id) && $term_id > 0) {
        /* @var \wpdb $wpdb */
        global $wpdb;

        // get all term_meta_{term_id} values
        $entries = $wpdb->get_results(
          $wpdb->prepare('
            SELECT o.`option_name` AS `key`, o.`option_value` AS `value`
            FROM ' . $wpdb->options . ' o
            WHERE o.`option_name` LIKE \'%%term_meta_%d%%\';
          ', (string)$term_id)
        );

        // when entries are found, format results
        if (count($entries) > 0) {
          $data = new \stdClass();

          foreach ($entries as $entry) {
            $key = preg_replace('/^term_meta_[\d]+__/', '', $entry->key);
            $value = is_serialized($entry->value) ? unserialize($entry->value) : $entry->value;

            // return url when data is an attachment
            if (is_array($value) && array_key_exists('attachment', $value)) {
              $value = (string)$value['attachment']['url'];
            }

            $data->$key = $value;
          }
        }
      }

      return $data;
    }

    /**
     * Add a multiple fields
     *
     * @param string           $taxonomy
     * @param \stdClass|string $term
     * @param string           $name
     * @param string           $title
     * @param array            $args
     * @param string           $mode
     */
    public static function multipleFields($taxonomy, $term, $name, $title, array $args, $mode = self::MODE_EDIT_AND_NEW)
    {
      $term_id = is_object($term) ? (int)$term->term_id : null;
      $meta_value = $term_id ? Taxonomy_Meta::get($term_id, $name) : '';

      if ($term_id): ?>

        <?php /* when term is in `edit` mode */ ?>
        <?php if ($mode !== self::MODE_ONLY_NEW): ?>
          <table class="form-table">
            <tr class="form-field">
              <th scope="row"><label for="name"><?php echo $title ?></label></th>
              <td>
                <?php
                  // put html in buffer
                  ob_start();
                  foreach ($args as $taxonomyMeta) {
                    $method = $taxonomyMeta['type'];
                    $title = !empty($taxonomyMeta['title']) ? $taxonomyMeta['title'] : null;
                    $name = !empty($taxonomyMeta['name']) ? $taxonomyMeta['name'] : $name . '][' . sanitize_key($method);

                    unset($taxonomyMeta['type']);
                    unset($taxonomyMeta['title']);
                    unset($taxonomyMeta['name']);

                    call_user_func_array(
                      array(__NAMESPACE__ . '\Taxonomy_Meta', $method),
                      array($taxonomy, $term, $name, $title, $taxonomyMeta)
                    );
                  }
                  $html = ob_get_clean();

                  // remove default table markup and add `form-field` containers
                  $html = str_replace(
                    array('<table class="form-table">', '</table>', '<tr class="form-field">', '</tr>', '<th scope="row">', '</td>', '<td>', '</td>'),
                    array('<div class="form-field">', '</div>', '', '', '', '', '', ''),
                    preg_replace('~>\\s+<~m', '><', $html)
                  );

                  // output the re-formatted html
                  echo $html;
                ?>
              </td>
            </tr>
          </table>
        <?php endif; ?>

      <?php else: ?>

        <?php /* when term is in `new` mode */ ?>
        <?php if ($mode !== self::MODE_ONLY_EDIT): ?>
          <h3><?php echo $title ?></h3><?php
          foreach ($args as $taxonomyMeta) {
            $method = $taxonomyMeta['type'];
            $title = !empty($taxonomyMeta['title']) ? $taxonomyMeta['title'] : $title;
            $name = !empty($taxonomyMeta['name']) ? $taxonomyMeta['name'] : $name . '][' . sanitize_key($method);

            unset($taxonomyMeta['type']);
            unset($taxonomyMeta['title']);
            unset($taxonomyMeta['name']);

            call_user_func_array(
              array(__NAMESPACE__ . '\Taxonomy_Meta', $method),
              array($taxonomy, $term, $name, $title, $taxonomyMeta)
            );
          }
        endif;

      endif;
    }

    /**
     * Add a input field
     *
     * @param string           $taxonomy
     * @param \stdClass|string $term
     * @param string           $name
     * @param string           $title
     * @param array            $args
     * @param string           $mode
     */
    public static function input($taxonomy, $term, $name, $title, array $args, $mode = self::MODE_EDIT_AND_NEW)
    {
      $term_id = is_object($term) ? (int)$term->term_id : null;
      $meta_value = $term_id ? Taxonomy_Meta::get($term_id, $name) : '';

      if ($term_id): ?>

        <?php /* when term is in `edit` mode */ ?>
        <?php if ($mode !== self::MODE_ONLY_NEW): ?>
          <table class="form-table">
            <tr class="form-field">
              <th scope="row"><label for="<?php echo esc_attr($name) ?>"><?php echo $title ?></label></th>
              <td>
                <input
                  type="<?php echo !empty($args['inputType']) ? $args['inputType'] : 'text' ?>"
                  name="taxmeta[<?php echo esc_attr($name) ?>]" id="<?php echo esc_attr($name) ?>"
                  value="<?php echo esc_attr($meta_value) ?>"
                  <?php if (!empty($args['placeholder'])): ?>placeholder="<?php echo esc_attr($args['placeholder']) ?>" <?php endif; ?>
                <?php foreach ((!empty($args['attributes']) ? $args['attributes'] : array()) as $attributeName => $attributeValue): ?>
                  <?php echo $attributeName ?>="<?php echo $attributeValue ?>"
                <?php endforeach; ?>
                />
                <?php if (!empty($args['description'])): ?>
                  <span class="description"><?php echo $args['description'] ?></span>
                <?php endif; ?>
              </td>
            </tr>
          </table>
        <?php endif; ?>

      <?php else: ?>

        <?php /* when term is in `new` mode */ ?>
        <?php if ($mode !== self::MODE_ONLY_EDIT): ?>
          <div class="form-field">
            <label><?php echo $title ?></label>
            <input
              type="<?php echo !empty($args['inputType']) ? $args['inputType'] : 'text' ?>"
              name="taxmeta[<?php echo esc_attr($name) ?>]" id="<?php echo esc_attr($name) ?>"
              value="<?php echo esc_attr($meta_value) ?>"
              <?php if (!empty($args['placeholder'])): ?>placeholder="<?php echo esc_attr($args['placeholder']) ?>" <?php endif; ?>
            <?php foreach ((!empty($args['attributes']) ? $args['attributes'] : array()) as $attributeName => $attributeValue): ?>
              <?php echo $attributeName ?>="<?php echo $attributeValue ?>"
            <?php endforeach; ?>
            />
            <?php if (!empty($args['description'])): ?>
              <span class="description"><?php echo $args['description'] ?></span>
            <?php endif; ?>
          </div>
        <?php endif; ?>

      <?php endif;
    }

    /**
     * Add a selectbox
     *
     * @param string           $taxonomy
     * @param \stdClass|string $term
     * @param string           $name
     * @param string           $title
     * @param array            $args
     * @param string           $mode
     */
    public static function checkbox($taxonomy, $term, $name, $title, array $args, $mode = self::MODE_EDIT_AND_NEW)
    {
      $term_id = is_object($term) ? (int)$term->term_id : null;
      $meta_value = $term_id ? Taxonomy_Meta::get($term_id, $name) : '';

      if ($term_id): ?>

        <?php /* when term is in `edit` mode */ ?>
        <?php if ($mode !== self::MODE_ONLY_NEW): ?>
          <table class="form-table">
            <tr class="form-field">
              <th scope="row"></th>
              <td>
                <label for="<?php echo esc_attr($name) ?>">
                  <input name="taxmeta[checkfor][<?php echo esc_attr($name) ?>]" type="hidden" value="1"/>
                  <input type="checkbox" name="taxmeta[<?php echo esc_attr($name) ?>]" id="<?php echo esc_attr($name) ?>"
                         value="1" <?php checked($meta_value, '1') ?> />
                  <span> <?php echo $title ?></span>
                </label>
                <?php if (!empty($args['description'])): ?>
                  <span class="description"><?php echo $args['description'] ?></span>
                <?php endif; ?>
              </td>
            </tr>
          </table>
        <?php endif; ?>

      <?php else: ?>

        <?php /* when term is in `new` mode */ ?>
        <?php if ($mode !== self::MODE_ONLY_EDIT): ?>
          <div class="form-field">
            <label for="<?php echo esc_attr($name) ?>">
              <input name="taxmeta[checkfor][<?php echo esc_attr($name) ?>]" type="hidden" value="1"/>
              <input type="checkbox" name="taxmeta[<?php echo esc_attr($name) ?>]" id="<?php echo esc_attr($name) ?>"
                     value="1" <?php checked($meta_value, '1') ?> />
              <span> <?php echo $title ?></span>
            </label>
            <?php if (!empty($args['description'])): ?>
              <span class="description"><?php echo $args['description'] ?></span>
            <?php endif; ?>
          </div>
        <?php endif; ?>

      <?php endif;
    }

    /**
     * Add a selectbox
     *
     * @param string           $taxonomy
     * @param \stdClass|string $term
     * @param string           $name
     * @param string           $title
     * @param array            $args
     * @param string           $mode
     */
    public static function selectbox($taxonomy, $term, $name, $title, array $args, $mode = self::MODE_EDIT_AND_NEW)
    {
      $term_id = is_object($term) ? (int)$term->term_id : null;
      $meta_value = $term_id ? Taxonomy_Meta::get($term_id, $name) : '';

      if ($term_id): ?>

        <?php /* when term is in `edit` mode */ ?>
        <?php if ($mode !== self::MODE_ONLY_NEW): ?>
          <table class="form-table">
            <tr class="form-field">
              <th scope="row"><label for="<?php echo esc_attr($name) ?>"><?php echo $title ?></label></th>
              <td>
                <select name="taxmeta[<?php echo esc_attr($name) ?>]" id="<?php echo esc_attr($name) ?>">
                  <?php foreach ($args['options'] as $option_value => $option_label): ?>
                    <option value="<?php echo esc_attr($option_value) ?>" <?php selected($option_value, $meta_value) ?>>
                      <?php echo $option_label ?>
                    </option>
                  <?php endforeach; ?>
                </select>
                <?php if (!empty($args['description'])): ?>
                  <span class="description"><?php echo $args['description'] ?></span>
                <?php endif; ?>
              </td>
            </tr>
          </table>
        <?php endif; ?>

      <?php else: ?>

        <?php /* when term is in `new` mode */ ?>
        <?php if ($mode !== self::MODE_ONLY_EDIT): ?>
          <div class="form-field">
            <label for="tag-description"><?php echo $title ?></label>
            <select name="taxmeta[<?php echo esc_attr($name) ?>]" id="<?php echo esc_attr($name) ?>">
              <?php foreach ($args['options'] as $option_value => $option_label): ?>
                <option value="<?php echo esc_attr($option_value) ?>" <?php selected($option_value, $meta_value) ?>>
                  <?php echo $option_label ?>
                </option>
              <?php endforeach; ?>
            </select>
            <?php if (!empty($args['description'])): ?>
              <span class="description"><?php echo $args['description'] ?></span>
            <?php endif; ?>
          </div>
        <?php endif; ?>

      <?php endif;
    }

    /**
     * Add a color picker
     *
     * @param string    $taxonomy
     * @param \stdClass $term
     * @param string    $name
     * @param string    $title
     * @param array     $args
     * @param string    $mode
     */
    public static function colorPicker($taxonomy, $term, $name, $title, array $args, $mode = self::MODE_EDIT_AND_NEW)
    {
      $term_id = is_object($term) ? (int)$term->term_id : null;
      $meta_value = $term_id ? Taxonomy_Meta::get($term_id, $name) : '';
      $uniqueId = uniqid('_colorpicker');

      if ($term_id): ?>

        <?php /* when term is in `edit` mode */ ?>
        <?php if ($mode !== self::MODE_ONLY_NEW): ?>
          <table class="form-table">
            <tr class="form-field">
              <th scope="row"><label for="<?php echo esc_attr($name) ?>"><?php echo $title ?></label></th>
              <td>
                <input
                  type="text"
                  name="taxmeta[<?php echo esc_attr($name) ?>]"
                  id="<?php echo $uniqueId ?>"
                  <?php if (array_key_exists('placeholder', $args)): ?>placeholder="<?php echo esc_attr($args['placeholder']) ?>"<?php endif; ?>
                  value="<?php echo esc_attr($meta_value) ?>"
                  />
                <?php if (!empty($args['description'])): ?>
                  <span class="description"><?php echo $args['description'] ?></span>
                <?php endif; ?>
              </td>
            </tr>
          </table>
        <?php endif; ?>

      <?php else: ?>

        <?php /* when term is in `new` mode */ ?>
        <?php if ($mode !== self::MODE_ONLY_EDIT): ?>
          <div class="form-field">
            <label for="tag-description"><?php echo $title ?></label>
            <input
              type="text"
              name="taxmeta[<?php echo esc_attr($name) ?>]"
              id="<?php echo $uniqueId ?>"
              <?php if (array_key_exists('placeholder', $args)): ?>placeholder="<?php echo esc_attr($args['placeholder']) ?>"<?php endif; ?>
              value="<?php echo esc_attr($meta_value) ?>"
              />

            <?php if (!empty($args['description'])): ?>
              <span class="description"><?php echo $args['description'] ?></span>
            <?php endif; ?>
          </div>
        <?php endif; ?>

      <?php endif; ?>

      <?php /* print javascript inline colorpicker */ ?>
      <?php ob_start(); ?>
      <script type="application/javascript">
        (function ($) {
          'use strict';

          $(document).ready(function () {
            var irisSettings = {};

            <?php /* Add colors to iris settings */ ?>
            <?php if (!empty($args['colors'])): ?>
            irisSettings['palettes'] = JSON.parse('<?php echo json_encode($args['colors']) ?>');
            <?php endif; ?>

            <?php /* Merge iris settings */ ?>
            <?php if (!empty($args['iris_options'])): ?>
            irisSettings = _.extend(irisSettings, JSON.parse('<?php echo json_encode($args['iris_options']) ?>'));
            <?php endif; ?>

            <?php /* attach the iris instance */ ?>
            $('input#<?php echo $uniqueId ?>').iris(irisSettings);
          });
        })(jQuery);
      </script><?php
      $script = ob_get_clean();
      echo trim(preg_replace('#[\r\n]+|[\s]{2,}#', '', $script));
    }

    /**
     * Add a editor
     *
     * @param string    $taxonomy
     * @param \stdClass $term
     * @param string    $name
     * @param string    $title
     * @param array     $args
     * @param string    $mode
     */
    public static function editor($taxonomy, $term, $name, $title, array $args, $mode = self::MODE_EDIT_AND_NEW)
    {
      $term_id = is_object($term) ? (int)$term->term_id : null;
      $meta_value = $term_id ? Taxonomy_Meta::get($term_id, $name) : '';

      if ($term_id): ?>

        <?php /* when term is in `edit` mode */ ?>
        <?php if ($mode !== self::MODE_ONLY_NEW): ?>
          <table class="form-table">
            <tr class="form-field">
              <th scope="row"><label for="<?php echo esc_attr($name) ?>"><?php echo $title ?></label></th>
              <td>
                <?php
                  wp_editor(
                    $meta_value,
                    'metabox_editor_' . uniqid(),
                    array(
                      'textarea_name' => 'taxmeta[' . $name . ']',
                      'tinymce'       => array('height' => 300),
                      'quicktags'     => (isset($args['quicktags']) && $args['quicktags'] === false) ? false : true,
                      'media_buttons' => (isset($args['media_buttons']) && $args['media_buttons'] === false) ? false : true,
                    )
                  );
                ?>
                <?php if (!empty($args['description'])): ?>
                  <span class="description"><?php echo $args['description'] ?></span>
                <?php endif; ?>
              </td>
            </tr>
          </table>
        <?php endif; ?>

      <?php else: ?>

        <?php /* when term is in `new` mode */ ?>
        <?php if ($mode !== self::MODE_ONLY_EDIT): ?>
          <div class="form-field">
            <label for="tag-description"><?php echo $title ?></label>
            <?php
              wp_editor(
                $meta_value,
                'metabox_editor_' . uniqid(),
                array(
                  'textarea_name' => 'taxmeta[' . $name . ']',
                  'tinymce'       => array('height' => 300),
                  'quicktags'     => (isset($args['quicktags']) && $args['quicktags'] === false) ? false : true,
                  'media_buttons' => (isset($args['media_buttons']) && $args['media_buttons'] === false) ? false : true,
                )
              );
            ?>
            <?php if (!empty($args['description'])): ?>
              <span class="description"><?php echo $args['description'] ?></span>
            <?php endif; ?>
          </div>
        <?php endif; ?>

      <?php endif;
    }

    /**
     * Add a image
     *
     * @param string    $taxonomy
     * @param \stdClass $term
     * @param string    $name
     * @param string    $title
     * @param array     $args
     * @param string    $mode
     */
    public static function image($taxonomy, $term, $name, $title, array $args, $mode = self::MODE_EDIT_AND_NEW)
    {
      $term_id = is_object($term) ? (int)$term->term_id : null;
      $image_data = $term_id ? get_option(Taxonomy_Meta::sanitize_option_key($term_id, $name)) : '';
      $attachment = !empty($image_data) && !empty($image_data['attachment']) ? $image_data['attachment'] : null;

      // attachment
      $attachment_id_value = $attachment && !empty($attachment['id']) ? $attachment['id'] : '';
      $attachment_url_value = $attachment && !empty($attachment['url']) ? $attachment['url'] : '';

      // translations
      $delete_label = !empty($args['delete_label']) ? $args['delete_label'] : __('Delete selected image', AbstractTheme::$domain);
      $frame_title = !empty($args['frame_title']) ? $args['frame_title'] : __('Select Image', AbstractTheme::$domain);
      $frame_button_label = !empty($args['frame_button_label']) ? $args['frame_button_label'] : __('Use this image', AbstractTheme::$domain);

      // preview
      $preview_url = !empty($attachment_id_value) && !empty($args['preview_size']) ? wp_get_attachment_image_src($attachment_id_value, $args['preview_size']) : $attachment_url_value;
      if (is_array($preview_url) && count($preview_url) > 0) {
        $preview_url = $preview_url[0];
      }

      if ($term_id): ?>

        <?php /* when term is in `edit` mode */ ?>
        <?php if ($mode !== self::MODE_ONLY_NEW): ?>
          <table class="form-table">
            <tr class="form-field">
              <th scope="row"><label for="<?php echo esc_attr($name) ?>"><?php echo $title ?></label></th>
              <td>
                <div class="metabox-field">
                  <div class="media-library-instance" data-bind="
                                  with: $root.medialibrary.newInstance.bind($data, {
                                      previewUrl:             '<?php echo !empty($preview_url) ? $preview_url : '' ?>',
                                      attachmentUrl:          '<?php echo $attachment_url_value ?>',
                                      attachmentId:           '<?php echo $attachment_id_value ?>',
                                      <?php if (!empty($args['preview_size'])): ?>imageSizePreview: '<?php echo $args['preview_size'] ?>',<?php endif; ?>
                                      <?php if (!empty($args['update_size'])): ?>imageSizeAttachment: '<?php echo $args['update_size'] ?>',<?php endif; ?>
                                      frameTitle:             '<?php echo Formatting::toAttributeString($frame_title) ?>',
                                      frameButtonText:        '<?php echo Formatting::toAttributeString($frame_button_label) ?>'
                                  })
                              ">

                    <?php // image preview ?>
                    <div class="media-library-preview-container" data-bind="visible: showPreview">
                      <img src="" alt="" data-bind="attr: {src: previewUrl}"/>
                    </div>

                    <?php // delete button ?>
                    <a class="button media-library-remove" href="#" data-bind="visible: showDeleteButton, click: removeAttachment">
                      <?php echo $delete_label ?>
                    </a>

                    <?php // select button ?>
                    <a class="button media-library-add" href="#" data-bind="visible: showSelectButton, click: addAttachment">
                      <?php _e('Select image', AbstractTheme::$domain) ?>
                    </a>

                    <?php // hidden input values ?>
                    <input name="taxmeta[<?php echo $name ?>][attachment][id]" type="hidden" data-bind="value: attachmentId"/>
                    <input name="taxmeta[<?php echo $name ?>][attachment][url]" type="hidden" data-bind="value: attachmentUrl"/>

                    <?php // description ?>
                    <?php if (!empty($args['description'])): ?>
                      <span class="description"><?php echo $args['description'] ?></span>
                    <?php endif; ?>
                  </div>
                </div>
                <!-- .metabox-field -->
              </td>
            </tr>
          </table>
        <?php endif; ?>

      <?php else: ?>

        <?php /* when term is in `new` mode */ ?>
        <?php if ($mode !== self::MODE_ONLY_EDIT): ?>
          <div class="form-field">
            <label><?php echo $title ?></label>

            <div class="metabox-field">
              <div class="media-library-instance" data-bind="
                          with: $root.medialibrary.newInstance.bind($data, {
                              previewUrl:             '<?php echo !empty($preview_url) ? $preview_url : '' ?>',
                              attachmentUrl:          '<?php echo $attachment_url_value ?>',
                              attachmentId:           '<?php echo $attachment_id_value ?>',
                              <?php if (!empty($args['preview_size'])): ?>imageSizePreview: '<?php echo $args['preview_size'] ?>',<?php endif; ?>
                              <?php if (!empty($args['update_size'])): ?>imageSizeAttachment: '<?php echo $args['update_size'] ?>',<?php endif; ?>
                              frameTitle:             '<?php echo Formatting::toAttributeString($frame_title) ?>',
                              frameButtonText:        '<?php echo Formatting::toAttributeString($frame_button_label) ?>'
                          })
                      ">

                <?php // image preview ?>
                <div class="media-library-preview-container" data-bind="visible: showPreview">
                  <img src="" alt="" data-bind="attr: {src: previewUrl}"/>
                </div>

                <?php // delete button ?>
                <a class="button media-library-remove" href="#" data-bind="visible: showDeleteButton, click: removeAttachment">
                  <?php echo $delete_label ?>
                </a>

                <?php // select button ?>
                <a class="button media-library-add" href="#" data-bind="visible: showSelectButton, click: addAttachment">
                  <?php _e('Select image', AbstractTheme::$domain) ?>
                </a>

                <?php // hidden input values ?>
                <input name="taxmeta[<?php echo $name ?>][attachment][id]" type="hidden" data-bind="value: attachmentId"/>
                <input name="taxmeta[<?php echo $name ?>][attachment][url]" type="hidden" data-bind="value: attachmentUrl"/>

                <?php // description ?>
                <?php if (!empty($args['description'])): ?>
                  <span class="description"><?php echo $args['description'] ?></span>
                <?php endif; ?>
              </div>
            </div>
            <!-- .metabox-field -->
          </div>
        <?php endif; ?>

      <?php endif;
    }

    /**
     * Add a custom field
     *
     * @param string    $taxonomy
     * @param \stdClass $term
     * @param string    $name
     * @param string    $title
     * @param array     $args
     * @param string    $mode
     */
    public static function custom($taxonomy, $term, $name, $title, array $args, $mode = self::MODE_EDIT_AND_NEW)
    {
      $term_id = is_object($term) ? (int)$term->term_id : null;
      $meta_value = $term_id ? Taxonomy_Meta::get($term_id, $name) : '';

      if ($term_id): ?>


      <?php /* when term is in `edit` mode */ ?>
      <?php if ($mode !== self::MODE_ONLY_NEW): ?>
        <table class="form-table">
          <tr class="form-field">
            <th scope="row"><label for="<?php echo esc_attr($name) ?>"><?php echo $title ?></label></th>
            <td><?php call_user_func($args['callback'], $term_id, $name) ?></td>
          </tr>
        </table>
      <?php endif; ?>

    <?php else: ?>

      <?php /* when term is in `new` mode */ ?>
      <?php if ($mode !== self::MODE_ONLY_EDIT): ?>
        <div class="form-field">
          <label><?php echo $title ?></label>
          <?php call_user_func($args['callback'], $term_id, $name) ?>
        </div>
      <?php endif; ?>

    <?php endif;
    }
  }
