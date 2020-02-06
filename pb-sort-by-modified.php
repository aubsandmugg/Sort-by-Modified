<?php
/**
 * Plugin Name: Sort By Modified Date
 * Plugin URI: https://aubsandmugg.com
 * Description: Shows an admin column with the modified date, time and user who last edited the post. Allows users to sort posts by modification date.
 * Version: 1.1.0
 * Author: Phil Buchanan
 * Author URI: https://aubsandmugg.com
 * Text Domain: pb
*/

defined('ABSPATH') || exit;

// Make sure to not redeclare the class
if (!class_exists('PB_Sort_By_Modified')) :

class PB_Sort_By_Modified {

	function __construct() {
		load_plugin_textdomain('pb', false, dirname(plugin_basename(__FILE__)) . '/languages/');

		// Register new modified column
		add_filter('manage_posts_columns', array($this, 'modified_column_register'));
		add_filter('manage_pages_columns', array($this, 'modified_column_register'));

		// Render modified date and author for each post
		add_action('manage_posts_custom_column', array($this, 'modified_column_display'), 10, 2);
		add_action('manage_pages_custom_column', array($this, 'modified_column_display'), 10, 2);

		// Make modified column sortable
		add_filter('manage_edit-post_sortable_columns', array($this, 'modified_column_register_sortable'));
		add_filter('manage_edit-page_sortable_columns', array($this, 'modified_column_register_sortable'));

		/**
		 * Make custom post types sortable by modified date
		 *
		 * Using `admin_init` action because we can only add the sort filters
		 * for custom post types _after_ the post types have been registered
		 * (which happens in the `init` action). The `admin_init` action is
		 * called after the `init` action.
		 */
		add_action('admin_init', array($this, 'add_sort_for_custom_post_types'));

		// Add styles for column display
		add_action('admin_enqueue_scripts', array($this, 'admin_style'));
	}




	/**
	 * Register new Modified Date column
	 */
	public function modified_column_register($columns) {
		$columns['modified'] = __('Modified Date', 'pb');

		return $columns;
	}



	/**
	 * Render modified date and author for each post
	 */
	public function modified_column_display($column_name, $post_id) {
		if ($column_name === 'modified') { ?>
			<?php _e('Modified', 'pb'); ?><br>
			<span title="<?php echo get_the_modified_time('Y/m/d g:i:s a'); ?>"><?php echo get_the_modified_time('Y/m/d'); ?></span><br>
			<?php if (get_the_modified_author()) {
				printf(__('By %s', 'pb'), get_the_modified_author());
			} ?>
		<?php }
	}



	/**
	 * Allow modified column to be sorted
	 */
	public function modified_column_register_sortable($columns) {
		$columns['modified'] = 'modified';

		return $columns;
	}



	/**
	 * Make custom post types sortable by modified date
	 *
	 * Adds modification date sorting to post types that are *not* built in and
	 * have admin UI.
	 */
	public function add_sort_for_custom_post_types() {
		$post_types = get_post_types(array(
			'show_ui' => true,
			'_builtin' => false,
		));

		if (is_array($post_types) && !empty($post_types)) {
			foreach ($post_types as $post_type) {
				add_filter('manage_edit-' . $post_type . '_sortable_columns', array($this, 'modified_column_register_sortable'));
			}
		}
	}



	/**
	 * Add styles for modified date display
	 */
	public function admin_style() { ?>
		<style>
			.fixed .column-modified {
				width: 10%;
			}

			.column-modified span[title] {
				-webkit-text-decoration: dotted underline;
				        text-decoration: dotted underline;
			}

			@media screen and (max-width: 1100px) and (min-width: 782px),
			@media screen and (max-width: 480px) {
				.fixed .column-modified {
					width: 14%;
				}
			}
		</style>
	<?php }

}

$PB_Sort_By_Modified = new PB_Sort_By_Modified;

endif;
