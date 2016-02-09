<?php
  namespace HappyFramework\Interfaces;

  interface IPostType
  {
    /**
     * Set support for custom post type
     *
     * @param array $supports
     */
    public function setSupports(array $supports);

    /**
     * Set capabilities for custom post type
     *
     * @param array $capabilities
     */
    public function setCapabilities(array $capabilities);

    /**
     * Register a new custom post type
     *
     * @param array $args
     */
    public function registerPostType(array $args);

    /**
     * Set label translations for single and plural
     *
     * @param string $single
     * @param string $plural
     */
    public function setLabelTranslation($single, $plural);

    /**
     * Add metaboxes for custom post type
     *
     * @param string   $post_type
     * @param \WP_Post $post
     */
    public function addMetaBoxes($post_type, $post);

    /**
     * Save post hook
     *
     * @param int $post_id
     */
    public function savePost($post_id);

    /**
     * Filter query before posts are fetched
     *
     * @param \WP_Query $query
     */
    public function preGetPosts($query);

    /**
     * Get all posts of post_type
     *
     * @param array $args
     * @return array
     */
    public static function getPosts(array $args = array());

    /**
     * Calls when admin is initialized

     */
    public function adminInit();

    /**
     * Calls when wordpress is initialized

     */
    public function init();

    /**
     * Filter permalink structure
     *
     * @param string   $permalink
     * @param \WP_Post $post
     * @param boolean  $leavename
     * @return string
     */
    public function filterPermalink($permalink, $post, $leavename);

    /**
     * Filter post data before insert
     *
     * @param array $data
     * @param array $postarr
     * @return array
     */
    public function insertPostData($data, $postarr);
  }
