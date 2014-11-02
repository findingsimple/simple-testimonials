<?php
/**
 * Testimonial post type
 *
 * @package TenderBytes
 * @subpackage CPTs
 */

Tenderbytes_Testimonials::bootstrap();

/**
 * Testimonial post type
 *
 * @package TenderBytes
 * @subpackage CPTs
 */
class Tenderbytes_Testimonials {
	/**
	 * Bootstrap
	 */
	public static function bootstrap() {
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
			'name' => _x('Testimonials', 'post type general name', hybrid_get_parent_textdomain() ),
			'singular_name' => _x('Testimonial', 'post type singular name', hybrid_get_parent_textdomain() ),
			'add_new' => _x('Add New', 'testimonial', hybrid_get_parent_textdomain() ),
			'add_new_item' => __('Add New Testimonial', hybrid_get_parent_textdomain() ),
			'edit_item' => __('Edit Testimonial', hybrid_get_parent_textdomain() ),
			'new_item' => __('New Testimonial', hybrid_get_parent_textdomain() ),
			'view_item' => __('View Testimonial', hybrid_get_parent_textdomain() ),
			'search_items' => __('Search Testimonials', hybrid_get_parent_textdomain() ),
			'not_found' =>  __('No testimonials found', hybrid_get_parent_textdomain() ),
			'not_found_in_trash' => __('No testimonials found in Trash', hybrid_get_parent_textdomain() ),
			'parent_item_colon' => ''
		);
		$args = array(
			'labels' => $labels,
			'public' => true,
			'publicly_queryable' => true,
			'show_ui' => true, 
			'query_var' => true,
			'rewrite' => array( 'slug' => 'testimonials', 'with_front' => false ),
			'capability_type' => 'post',
			'hierarchical' => false,
			'menu_position' => null,
			'taxonomies' => array(''),
			'supports' => array('title', 'editor', 'thumbnail', 'custom-fields')
		); 

		register_post_type('testimonial', $args);
	}

	/**
	 * Filter the "post updated" messages
	 *
	 * @param array $messages
	 * @return array
	 */
	public static function updated_messages( $messages ) {
		global $post;

		$messages['testimonial'] = array(
			0 => '', // Unused. Messages start at index 1.
			1 => sprintf( __('Testimonial updated. <a href="%s">View testimonial</a>', hybrid_get_parent_textdomain() ), esc_url( get_permalink($post->ID) ) ),
			2 => __('Custom field updated.', hybrid_get_parent_textdomain() ),
			3 => __('Custom field deleted.', hybrid_get_parent_textdomain() ),
			4 => __('Testimonial updated.', hybrid_get_parent_textdomain() ),
			/* translators: %s: date and time of the revision */
			5 => isset($_GET['revision']) ? sprintf( __('Testimonial restored to revision from %s', hybrid_get_parent_textdomain() ), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
			6 => sprintf( __('Testimonial published. <a href="%s">View testimonial</a>', hybrid_get_parent_textdomain() ), esc_url( get_permalink($post->ID) ) ),
			7 => __('Testimonial saved.', hybrid_get_parent_textdomain() ),
			8 => sprintf( __('Testimonial submitted. <a target="_blank" href="%s">Preview bio</a>', hybrid_get_parent_textdomain() ), esc_url( add_query_arg( 'preview', 'true', get_permalink($post->ID) ) ) ),
			9 => sprintf( __('Testimonial scheduled for: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Preview testimonial</a>', hybrid_get_parent_textdomain() ),
			  // translators: Publish box date format, see http://php.net/date
			  date_i18n( __( 'M j, Y @ G:i' ), strtotime( $post->post_date ) ), esc_url( get_permalink($post->ID) ) ),
			10 => sprintf( __('Testimonial draft updated. <a target="_blank" href="%s">Preview testimonial</a>', hybrid_get_parent_textdomain() ), esc_url( add_query_arg( 'preview', 'true', get_permalink($post->ID) ) ) ),
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
}
