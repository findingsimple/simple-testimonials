<?php
/*
Plugin Name: Simple Testimonials
Plugin URI: http://plugins.findingsimple.com
Description: Adds an Testimonial CPT.
Version: 1.0
Author: Finding Simple
Author URI: http://findingsimple.com
License: GPL2
*/
/*
Copyright 2014  Finding Simple  (email : plugins@findingsimple.com)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

if ( ! class_exists( 'Simple_Testimonials' ) ) :

/**
 * So that themes and other plugins can customise the text domain, the Simple_Testimonials
 * should not be initialized until after the plugins_loaded and after_setup_theme hooks.
 * However, it also needs to run early on the init hook.
 *
 * @package Simple Testimonials
 * @since 1.0
 */
function initialize_testimonials(){
	Simple_Testimonials::init();
}
add_action( 'init', 'initialize_testimonials', -1 );

/**
 * Plugin Main Class.
 *
 * @package Simple Testimonials
 * @since 1.0
 */
class Simple_Testimonials {

	static $text_domain;

	static $post_type_name;
	
	/**
	 * Initialise
	 */
	public static function init() {

		self::$text_domain = apply_filters( 'simple_testimonials_text_domain', 'Simple_Testimonials' );

		self::$post_type_name = apply_filters( 'simple_testimonials_post_type_name', 'testimonial' );

		add_action( 'init', array( __CLASS__, 'register' ) );

		add_filter( 'post_updated_messages', array( __CLASS__, 'updated_messages' ) );

		add_action( 'add_meta_boxes', array( __CLASS__, 'add_meta_box' ) );

		add_action( 'save_post', array( __CLASS__, 'save_meta' ), 10, 1 );

	}

	/**
	 * Register the post type
	 */
	public static function register() {
		
		$labels = array(
			'name'               => __( 'Testimonials', self::$text_domain ),
			'singular_name'      => __( 'Testimonial', self::$text_domain ),
			'all_items'          => __( 'All Testimonials', self::$text_domain ),
			'add_new_item'       => __( 'Add New Testimonial', self::$text_domain ),
			'edit_item'          => __( 'Edit Testimonial', self::$text_domain ),
			'new_item'           => __( 'New Testimonial', self::$text_domain ),
			'view_item'          => __( 'View Testimonial', self::$text_domain ),
			'search_items'       => __( 'Search Testimonials', self::$text_domain ),
			'not_found'          => __( 'No testimonials found', self::$text_domain ),
			'not_found_in_trash' => __( 'No testimonials found in trash', self::$text_domain ),
			'menu_name'      	 => __( 'Testimonials', self::$text_domain ),
		);

		$labels = apply_filters( self::$post_type_name . '_cpt_labels' , $labels );		
		
		$args = array(
			'description' => __( 'Testimonials', self::$text_domain ),
			'labels' => $labels,
			'public' => false,
			'menu_icon' => 'dashicons-format-chat',
			'show_ui' => true, 
			'query_var' => true,
			'has_archive' => false,
			'rewrite' => array( 'slug' => 'testimonial', 'with_front' => false ),
			'capability_type' => 'post',
			'hierarchical' => false,
			'menu_position' => null,
			'taxonomies' => array(''),
			'show_in_nav_menus' => false,
			'supports' => array('title', 'editor', 'thumbnail', 'custom-fields')
		); 
		
		$args = apply_filters( self::$post_type_name . '_cpt_args' , $args );
		
		register_post_type( self::$post_type_name , $args );
		
	}

	/**
	 * Filter the "post updated" messages
	 *
	 * @param array $messages
	 * @return array
	 */
	public static function updated_messages( $messages ) {
		global $post;

		$messages[ self::$post_type_name ] = array(
			0 => '', // Unused. Messages start at index 1.
			1 => sprintf( __('Testimonial updated.', self::$text_domain ), esc_url( get_permalink($post->ID) ) ),
			2 => __('Custom field updated.', self::$text_domain ),
			3 => __('Custom field deleted.', self::$text_domain ),
			4 => __('Testimonial updated.', self::$text_domain ),
			/* translators: %s: date and time of the revision */
			5 => isset($_GET['revision']) ? sprintf( __('Testimonial restored to revision from %s', self::$text_domain ), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
			6 => sprintf( __('Testimonial published.', self::$text_domain ), esc_url( get_permalink($post->ID) ) ),
			7 => __('Testimonial saved.', self::$text_domain ),
			8 => sprintf( __('Testimonial submitted.', self::$text_domain ), esc_url( add_query_arg( 'preview', 'true', get_permalink($post->ID) ) ) ),
			9 => sprintf( __('Testimonial scheduled for: <strong>%1$s</strong>.', self::$text_domain ),
			  // translators: Publish box date format, see http://php.net/date
			  date_i18n( __( 'M j, Y @ G:i' ), strtotime( $post->post_date ) ), esc_url( get_permalink($post->ID) ) ),
			10 => sprintf( __('Testimonial draft updated.', self::$text_domain ), esc_url( add_query_arg( 'preview', 'true', get_permalink($post->ID) ) ) ),
		);

		return $messages;
	}

	/**
	 * Add the citation meta box
	 *
	 * @wp-action add_meta_boxes
	 */
	public static function add_meta_box() {
		add_meta_box( 'testimonial-citation', __( 'Citation', hybrid_get_parent_textdomain()  ), array( __CLASS__, 'do_meta_box' ), 'testimonial', 'normal', 'high' );
	}

	/**
	 * Output the citation meta box HTML
	 *
	 * @param WP_Post $object Current post object
	 * @param array $box Metabox information
	 */
	public static function do_meta_box( $object, $box ) {
		wp_nonce_field( basename( __FILE__ ), 'testimonial-citation' );
?>

		<p>
			<label for="testimonial-citation-name"><?php _e( 'Name:', hybrid_get_parent_textdomain() ); ?></label>
			<br />
			<input type="text" name="testimonial-citation-name" id="testimonial-citation-name"
				value="<?php echo esc_attr( get_post_meta( $object->ID, 'testimonial-citation-name', true ) ); ?>"
				size="30" tabindex="30" style="width: 99%;" />
		</p>

		<p>
			<label for="testimonial-citation-position"><?php _e( 'Position:', hybrid_get_parent_textdomain() ); ?></label>
			<br />
			<input type="text" name="testimonial-citation-position" id="testimonial-citation-position"
				value="<?php echo esc_attr( get_post_meta( $object->ID, 'testimonial-citation-position', true ) ); ?>"
				size="30" tabindex="30" style="width: 99%;" />
		</p>

		<p>
			<label for="testimonial-citation-company"><?php _e( 'Company:', hybrid_get_parent_textdomain() ); ?></label>
			<br />
			<input type="text" name="testimonial-citation-company" id="testimonial-citation-company"
				value="<?php echo esc_attr( get_post_meta( $object->ID, 'testimonial-citation-company', true ) ); ?>"
				size="30" tabindex="30" style="width: 99%;" />
		</p>

		<p>
			<label for="testimonial-citation-company-url"><?php _e( 'Company URL:', hybrid_get_parent_textdomain() ); ?></label>
			<br />
			<input type="url" name="testimonial-citation-company-url" id="testimonial-citation-company-url"
				value="<?php echo esc_attr( get_post_meta( $object->ID, 'testimonial-citation-company-url', true ) ); ?>"
				size="30" tabindex="30" style="width: 99%;" />
		</p>
<?php
	}

	/**
	 * Save the citation metadata
	 *
	 * @wp-action save_post
	 * @param int $post_id The ID of the current post being saved.
	 */
	public static function save_meta( $post_id ) {
		$prefix = hybrid_get_prefix();

		/* Verify the nonce before proceeding. */
		if ( !isset( $_POST['testimonial-citation'] ) || !wp_verify_nonce( $_POST['testimonial-citation'], basename( __FILE__ ) ) )
			return $post_id;

		$meta = array(
			'testimonial-citation-name',
			'testimonial-citation-position',
			'testimonial-citation-company',
			'testimonial-citation-company-url'
		);

		foreach ( $meta as $meta_key ) {
			$new_meta_value = $_POST[$meta_key];

			/* Get the meta value of the custom field key. */
			$meta_value = get_post_meta( $post_id, $meta_key, true );

			/* If there is no new meta value but an old value exists, delete it. */
			if ( '' == $new_meta_value && $meta_value )
				delete_post_meta( $post_id, $meta_key, $meta_value );

			/* If a new meta value was added and there was no previous value, add it. */
			elseif ( $new_meta_value && '' == $meta_value )
				add_post_meta( $post_id, $meta_key, $new_meta_value, true );

			/* If the new meta value does not match the old value, update it. */
			elseif ( $new_meta_value && $new_meta_value != $meta_value )
				update_post_meta( $post_id, $meta_key, $new_meta_value );
		}
	}

	/**
	 * Method overloading
	 *
	 * Provides a "the_*" for the "get_*" methods. If the corresponding method
	 * does not exist, triggers an error.
	 *
	 * @param string $name Method name
	 * @param array $args Arguments to pass to method
	 */
	public static function __callStatic($name, $args) {
		$get_method = 'get_' . substr($name, 4);
		if (substr($name, 0, 4) === 'the_' && method_exists(__CLASS__, $get_method)) {
			echo call_user_func_array(array(__CLASS__, $get_method), $args);
			return;
		}

		// No luck finding the method, do the same as normal PHP calls
		$trace = debug_backtrace();
		$file = $trace[0]['file'];
		$line = $trace[0]['line'];
		trigger_error('Call to undefined method ' . __CLASS__ . '::' . $name . "() in $file on line $line", E_USER_ERROR);
	}

	/**#@+
	 * @internal Template tag for use in templates
	 */
	/**
	 * Get the testimonial author's name
	 *
	 * @param int $post_ID Post ID. Defaults to the current post's ID
	 */
	public static function get_author($post_ID = 0) {
		if (absint($post_ID) === 0) {
			$post_ID = $GLOBALS['post']->ID;
		}

		return get_post_meta($post_ID, 'testimonial-citation-name', true);
	}

	/**
	 * Get the testimonial author's position
	 *
	 * @param int $post_ID Post ID. Defaults to the current post's ID
	 */
	public static function get_position($post_ID = 0) {
		if (absint($post_ID) === 0) {
			$post_ID = $GLOBALS['post']->ID;
		}

		return get_post_meta($post_ID, 'testimonial-citation-position', true);
	}

	/**
	 * Get the testimonial author's company name
	 *
	 * @param int $post_ID Post ID. Defaults to the current post's ID
	 */
	public static function get_company($post_ID = 0) {
		if (absint($post_ID) === 0) {
			$post_ID = $GLOBALS['post']->ID;
		}

		return get_post_meta($post_ID, 'testimonial-citation-company', true);
	}

	/**
	 * Get the testimonial author's company URL
	 *
	 * @param int $post_ID Post ID. Defaults to the current post's ID
	 */
	public static function get_company_url($post_ID = 0) {
		if (absint($post_ID) === 0) {
			$post_ID = $GLOBALS['post']->ID;
		}

		return get_post_meta($post_ID, 'testimonial-citation-company-url', true);
	}

	/**
	 * Get a link to the testimonial author's company
	 *
	 * Either returns the company name, or if the company URL has been set,
	 * returns a HTML link to the company.
	 *
	 * @param int $post_ID Post ID. Defaults to the current post's ID
	 */
	public static function get_company_link($post_ID = 0) {
		$company = self::get_company($post_ID);

		if (empty($company)) {
			return '';
		}

		$url = self::get_company_url($post_ID);
		if (!empty($url)) {
			return sprintf('<a href="%1$s" title="%2$s">%2$s</a>', $url, $company);
		}

		return $company;
	}
	/**#@-*/

};

endif;