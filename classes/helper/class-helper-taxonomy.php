<?php
  namespace HappyFramework\Helpers;

  class Taxonomy
  {

    /**
     * Get taxonomy from term_id
     *
     * @param   int   $term_id
     * @return  null|string
     * @global  \wpdb $wpdb
     */
    public static function getTaxonomyFromTermId($term_id)
    {
      /* @var \wpdb $wpdb */
      global $wpdb;

      $taxonomy = $wpdb->get_var(
        $wpdb->prepare('
          SELECT `term_tax`.`taxonomy` FROM ' . $wpdb->term_taxonomy . ' AS `term_tax`
          WHERE `term_tax`.`term_id` = %d
          LIMIT 1;
        ', $term_id)
      );

      return $taxonomy;
    }

    /**
     * Get parents of given term when taxonomy is hierarchical
     *
     * @param int    $term_id
     * @param string $taxonomy
     * @return array
     */
    public static function getTermAncestors($term_id, $taxonomy)
    {
      $ancestors = array();
      foreach (get_ancestors($term_id, $taxonomy) as $term_id) {
        array_push($ancestors, get_term_by('id', $term_id, $taxonomy));
      }

      return $ancestors;
    }

    /**
     * Get post types which registered the taxonomy
     *
     * @param string $taxonomy
     * @return array|null
     */
    public static function registeredPostTypes($taxonomy)
    {
      $post_types = get_taxonomy($taxonomy)->object_type;

      return (is_array($post_types) && count($post_types) > 0) ? $post_types : null;
    }
  }
