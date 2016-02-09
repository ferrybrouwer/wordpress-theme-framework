<?php
namespace HappyFramework\Abstracts;

use HappyFramework\Abstracts\AbstractTheme as Theme;
use HappyFramework\Helpers\Formatting;
use HappyFramework\Interfaces\IPostType;

/**
 * Abstract Class PostType
 *
 * @package HappyFramework\PostTypes
 */
abstract class AbstractPostType implements IPostType
{
    const SUPPORT_TITLE           = 'title';
    const SUPPORT_EDITOR          = 'editor';
    const SUPPORT_THUMBNAIL       = 'thumbnail';
    const SUPPORT_EXCERPT         = 'excerpt';
    const SUPPORT_COMMENTS        = 'comments';
    const SUPPORT_AUTHOR          = 'author';
    const SUPPORT_TRACKBACKS      = 'trackbacks';
    const SUPPORT_CUSTOM_FIELDS   = 'custom-fields';
    const SUPPORT_REVISIONS       = 'revisions';
    const SUPPORT_POST_FORMATS    = 'post-formats';
    const SUPPORT_PAGE_ATTRIBUTES = 'page-attributes';
    const NOTICE_UPDATED          = 'updated';
    const NOTICE_ERROR            = 'error';

    public  $type              = 'post';
    public  $labelSingular     = '';
    public  $labelPlural       = '';
    public  $capabilities      = array();
    public  $supports          = array();
    public  $postTypeArguments = array();
    public  $validateMetaData  = array();
    public  $taxonomies        = array();
    private $customIcon;


    /**
     * AbstractPostType constructor.
     *
     * @param null $type
     */
    public function __construct($type = null)
    {
        $self = $this;
        $this->type = $type;
        $this->supports = array(self::SUPPORT_TITLE, self::SUPPORT_EDITOR, self::SUPPORT_THUMBNAIL);

        add_action('add_meta_boxes', array($this, 'addMetaBoxes'), 99, 2);
        add_action('init', array($this, 'init'), 99);
        add_action('admin_init', array($this, 'adminInit'), 99);
        add_action('admin_menu', array($this, 'removeExtraFieldsFromAdmin'));

        // save post
        add_action('save_post', function ($post_id) use ($self) {
            if (get_post_type($post_id) === $self->type) {
                $self->savePost($post_id);
            }
        }, 99, 1);

        // pre get posts
        add_action('pre_get_posts', function ($query) use ($self) {
            /* @var \WP_Query $query */
            if ($query->is_main_query()) {
                $self->preGetPosts($query);
            }
        }, 10, 1);

        // filter permalink
        add_filter('post_link', function ($permalink, $post, $leavename) use ($self) {
            if ($post->post_type === $self->type) {
                $permalink = $self->filterPermalink($permalink, $post, $leavename);
            }

            return $permalink;
        }, 1, 3);
        add_filter('post_type_link', function ($permalink, $post, $leavename) use ($self) {
            if ($post->post_type === $self->type) {
                $permalink = $self->filterPermalink($permalink, $post, $leavename);
            }

            return $permalink;
        }, 1, 3);

        // insert post data
        add_filter('wp_insert_post_data', function ($data, $postarr) use ($self) {
            if (!empty($postarr['post_type']) && $postarr['post_type'] === $self->type) {
                $data = $self->insertPostData($data, $postarr);
            }

            return $data;
        }, 10, 2);

        // show admin notices
        add_action('admin_notices', function () {
            if ($notice = get_option('abstract_post_type_errors')) {
                echo '<div class="' . (!empty($notice['type']) ? $notice['type'] : AbstractPostType::NOTICE_ERROR) . '"><p>' . $notice['message'] . '</p></div>';
                delete_option('abstract_post_type_errors');
            }
        });
    }

    /**
     * Set custom icon for admin menu
     *
     * @param string $iconName
     */
    public function setIcon($iconName)
    {
        $this->customIcon = $iconName;
    }

    /**
     * Add metabox using the helper class HappyFramework\Helpers\Metabox
     *
     * @param string $id
     * @param string $title
     * @param string $type     [type method]
     * @param array  $callback_args
     * @param string $context  [optional]
     * @param string $priority [optional]
     */
    public function addMetabox($id, $title, $type, $callback_args = array(), $context = 'advanced', $priority = 'default')
    {
        $self = $this;

        if (did_action('add_meta_boxes') > 0) {
            add_meta_box(
                $id,
                $title,
                array('HappyFramework\Helpers\Metabox', $type),
                $self->type,
                $context,
                $priority,
                $callback_args
            );
        } else {
            add_action('add_meta_boxes', function () use ($id, $title, $type, $callback_args, $context, $priority, $self) {
                add_meta_box(
                    $id,
                    $title,
                    array('HappyFramework\Helpers\Metabox', $type),
                    $self->type,
                    $context,
                    $priority,
                    $callback_args
                );
            }, 10, 2);
        }
    }

    /**
     * Show admin notice
     *
     * @param string $message
     * @param string $type
     */
    public function showAdminNotices($message, $type = 'error')
    {
        update_option('abstract_post_type_errors', array('message' => $message, 'type' => $type));
        add_filter('redirect_post_location', array($this, 'redirectPostLocation'));
    }

    /**
     * Redirect post location
     *
     * @param string $location
     * @return string
     */
    public function redirectPostLocation($location)
    {
        remove_filter('redirect_post_location', array($this, 'redirectPostLocation'));

        return add_query_arg('show_error', 1, $location);
    }

    /**
     * Get hierarchical taxonomy objects
     * Default hierarchical taxonomy is `category` for example.
     *
     * @return array
     */
    public function getHierarchicalTaxonomies()
    {
        $hierarchicalTaxonomies = array();
        foreach (get_object_taxonomies($this->type, 'objects') as $taxonomyName => $taxonomyObject) {
            if ($taxonomyObject->hierarchical === true) {
                array_push($hierarchicalTaxonomies, $taxonomyObject);
            }
        }

        return apply_filters('hierarchical-taxonomies', $hierarchicalTaxonomies);
    }

    /**
     * Get non-hierarchical taxonomy object
     * Default non-hierarchical taxonomy is `tag` for example and live on there own.
     *
     * @return array
     */
    public function getNonHierarchicalTaxonomies()
    {
        $nonHierarchicalTaxonomies = array();
        foreach (get_object_taxonomies($this->type, 'objects') as $taxonomyName => $taxonomyObject) {
            if (!$taxonomyObject->hierarchical) {
                array_push($nonHierarchicalTaxonomies, $taxonomyObject);
            }
        }

        return apply_filters('non-hierarchical-taxonomies', $nonHierarchicalTaxonomies);
    }


    /**
     * Get meta value
     *
     * @param string $metaKey
     * @param int    $post_id   [optional]
     * @param string $imageSize [optional] when meta data is an attachment
     * @return mixed|null
     */
    public static function getMetaValue($metaKey, $post_id = null, $imageSize = null)
    {
        global $post;

        $post_id = $post_id ?: (isset($post->ID) ? $post->ID : null);
        $value = $post_id ? get_post_meta($post_id, $metaKey, true) : null;

        // if value is attachment
        if (is_array($value) && array_key_exists('attachment', $value)) {
            $value = (array)$value['attachment'];
            $attachmentId = (int)$value['id'];
            if ($imageSize && $attachment = wp_get_attachment_image_src($attachmentId, $imageSize)) {
                $value = $attachment[0];
            } else {
                $value = !empty($value['url']) ? $value['url'] : null;
            }
        }

        // handle value as content when 'content' appears in meta key
        if ($value && strpos($metaKey, 'content') !== false && $value !== '1' && !empty($value)) {
            $value = Formatting::toHtml($value);
        }

        return $value;
    }

    /**
     * Get id of attachment meta data
     *
     * @param string $metaKey
     * @param null   $post_id [optional]
     * @return int|null
     */
    public static function getMetaAttachmentId($metaKey, $post_id = null)
    {
        global $post;
        $attachmentId = null;
        $post_id = $post_id ?: (isset($post->ID) ? $post->ID : null);
        $value = $post_id ? get_post_meta($post_id, $metaKey, true) : null;

        if (is_array($value) && array_key_exists('attachment', $value)) {
            $value = (array)$value['attachment'];
            $attachmentId = (int)$value['id'];
        }

        return $attachmentId;
    }

    /**
     * Remove extra fields column from being displayed in the admin
     *
     * @hook admin_ajax
     */
    public function removeExtraFieldsFromAdmin()
    {
        remove_meta_box('postcustom', $this->type, 'normal');
    }

    /**
     * Set post type supports
     *
     * @param array $supports
     */
    public function setSupports(array $supports)
    {
        $this->supports = $supports;
    }

    /**
     * Set post type capabilities
     *
     * @param array $capabilities
     */
    public function setCapabilities(array $capabilities)
    {
        $this->capabilities = $capabilities;
    }

    /**
     * Register post type
     */
    public function registerPostType(array $args)
    {
        $self = $this;

        // set default post type arguments
        $this->postTypeArguments = array_merge(
            array(
                'label'              => $self->labelSingular,
                'labels'             => $self->getLabels(),
                'show_ui'            => true,
                'supports'           => $self->supports,
                'publicly_queryable' => true,
                'public'             => true,
                'show_in_menu'       => true,
                'query_var'          => true,
                'menu_icon'          => $this->customIcon ?: 'dashicons-' . $self->type

            ),
            $args
        );

        // apply capabilities when set
        if (!empty($this->capabilities)) {
            $this->postTypeArguments['capabilities'] = $this->capabilities;
        }

        // when wordpress has initialized, register post type
        add_action('init', function () use ($self) {
            register_post_type($self->type, $self->postTypeArguments);
        });
    }

    /**
     * Get labels
     *
     * @param string $singular [optional] default $this->labelSingular
     * @param string $plural   [optional] default $this->labelPlural
     * @return array
     */
    public function getLabels($singular = null, $plural = null)
    {
        $singular = $singular ?: $this->labelSingular;
        $plural = $plural ?: $this->labelPlural;

        return array(
            'name'               => $plural,
            'singular_name'      => $singular,
            'add_new'            => sprintf(__('New %s', Theme::$domain), $singular),
            'add_new_item'       => sprintf(__('Add New %s', Theme::$domain), $singular),
            'edit_item'          => sprintf(__('Edit %s', Theme::$domain), $singular),
            'new_item'           => sprintf(__('New %s', Theme::$domain), $singular),
            'view_item'          => sprintf(__('View %s', Theme::$domain), $singular),
            'search_items'       => sprintf(__('Search %s', Theme::$domain), $singular),
            'not_found'          => sprintf(__('No %s found', Theme::$domain), $singular),
            'not_found_in_trash' => sprintf(__('No %s found in trash', Theme::$domain), $plural),
            'all_items'          => sprintf(__('All %s', Theme::$domain), $plural),
            'menu_name'          => $plural,
            'name_admin_bar'     => $singular,
        );
    }

    /**
     * Set label translation for singular and plural
     *
     * @param string $labelSingular
     * @param string $labelPlural
     */
    public function setLabelTranslation($labelSingular, $labelPlural)
    {
        $this->labelSingular = $labelSingular;
        $this->labelPlural = $labelPlural;
    }

    /**
     * Register Taxonomy
     *
     * @param string $taxonomy
     * @param string $labelSingular
     * @param string $labelPlural
     * @param array  $args
     */
    public function registerTaxonomy($taxonomy, $labelSingular = null, $labelPlural = null, array $args = array())
    {
        $self = $this;

        $args = array_merge(
            array(
                'label'              => $labelSingular ?: $self->labelSingular,
                'labels'             => $self->getLabels($labelSingular, $labelPlural),
                'hierarchical'       => false,
                'rewrite'            => array(
                    'slug' => false
                ),
                'show_ui'            => true,
                'show_admin_columns' => true,
                'query_var'          => true,
            ), $args
        );

        add_action('init', function () use ($self, $taxonomy, $args) {
            array_push($self->taxonomies, $taxonomy);
            register_taxonomy($taxonomy, $self->type, $args);
        });
    }

    /**
     * Check if current page is a taxonomy page of this post type
     *
     * @return boolean
     */
    public static function isTaxonomyPage()
    {
        $is_taxonomy = false;

        /* @var AbstractPostType $instance */
        if ($instance = Theme::getPostTypeInstanceByClassName(get_called_class())) {
            $is_taxonomy = is_tax($instance->taxonomies);
        }

        return $is_taxonomy;
    }

    /**
     * Check if current page is a archive page of this post type
     *
     * @return boolean
     */
    public static function isArchivePage()
    {
        $is_archive = false;

        /* @var AbstractPostType $instance */
        if ($instance = Theme::getPostTypeInstanceByClassName(get_called_class())) {
            $is_archive = is_post_type_archive($instance->type);
        }

        return $is_archive;
    }

    /**
     * Check if current page is a single page of this post type
     */
    public static function isSinglePage()
    {
        $is_single = false;

        /* @var AbstractPostType $instance */
        if ($instance = Theme::getPostTypeInstanceByClassName(get_called_class())) {
            $is_single = (is_page() || is_single()) && get_post_type() === $instance->type;
        }

        return $is_single;
    }

    /**
     * Get all posts of current post_type
     *
     * @param array $args
     * @return array
     */
    public static function getPosts(array $args = array())
    {
        $posts = null;

        /* @var AbstractPostType $instance */
        if ($instance = Theme::getPostTypeInstanceByClassName(get_called_class())) {
            $posts = get_posts(
                array_merge(
                    array(
                        'post_status' => 'publish',
                        'numberposts' => -1,
                        'post_type'   => $instance->type,
                    ), $args
                )
            );
        }

        return $posts;
    }

    /**
     * Calls when admin is initialized
     */
    public function adminInit()
    {
    }

    /**
     * Calls when wordpress is initialized
     */
    public function init()
    {
    }

    /**
     * Save post hook
     *
     * @param int $post_id
     */
    public function savePost($post_id)
    {
    }

    /**
     * Filter query before posts are fetched
     *
     * @param \WP_Query $query
     */
    public function preGetPosts($query)
    {
    }

    /**
     * Add metaboxes for custom post type
     *
     * @param string   $post_type
     * @param \WP_Post $post
     */
    public function addMetaBoxes($post_type, $post)
    {
    }

    /**
     * Filter permalink structure
     *
     * @param string   $permalink
     * @param \WP_Post $post
     * @param boolean  $leavename
     * @return string
     */
    public function filterPermalink($permalink, $post, $leavename)
    {
        return $permalink;
    }

    /**
     * Filter post data before insert
     *
     * @param array $data
     * @param array $postarr
     * @return array
     */
    public function insertPostData($data, $postarr)
    {
        return $data;
    }
}
