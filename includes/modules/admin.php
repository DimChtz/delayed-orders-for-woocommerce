<?php

namespace Dimchtz\WCDO\Modules;

if ( !defined('ABSPATH') ) {
    exit;
}

/**
 * Plugin's admin class
 * 
 * @since 1.0.0
 */

class Admin {

	public $instance;

	/**
	 * Admin constructor.
	 *
	 * Initializing the admin interface.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function __construct($instance) {

		$this->instance = $instance;

		$this->register_hooks();
		$this->register_assets();

	}

	/**
     * Registers all admin actions and filters.
     * 
     * @since 1.0.0
     * @access public
     */
	public function register_hooks() {

		global $wcdo_settings;

		if ( $this->instance->is_active ) {

			add_action('restrict_manage_posts', [$this, 'add_filter_input']);
			add_action('manage_edit-shop_order_columns', [$this, 'add_custom_column_header'], 20);
			add_action('manage_shop_order_posts_custom_column', [$this, 'add_custom_column_data'], 20, 2);
			add_action('save_post', [$this, 'update_delay_status'], 10, 3);
			add_action('admin_init', [$this, 'update_delay_status_all']);
			add_action('pre_get_posts', [$this, 'posts_query_mod']);
			add_action('admin_bar_menu', [$this, 'admin_toolbar_delayed_orders'], 100);

			$unmarked = wcdo_count_unmarked_orders($wcdo_settings['delay_order_statuses']);
			if ( $unmarked > 0 ) {
				add_action('admin_notices', [$this, 'unmarked_orders_notification']);				
			}
			
		} else {

			add_action('admin_notices', [$this, 'requirements_notification']);
			
		}

	}

	/**
	 * Prints a notice for plugin requirements
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function requirements_notification() { ?>

	    <div class="notice notice-error">
	        <p>
	        	<?php _e('WooCommerce Delayed Orders is enabled but not effective', 'wc-delayed-orders'); ?>.
	        	<?php _e('Please install and activate', 'wc-delayed-orders'); ?> <a href="<?= admin_url('/plugin-install.php?s=WooCommerce&tab=search&type=term'); ?>"><?php _e('WooCommerce', 'wc-delayed-orders'); ?></a>.
	        </p>
	    </div>

	<?php }

	/**
	 * Prints a notice for unmarked orders
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function unmarked_orders_notification() { ?>

		<?php 
		global $wcdo_settings;
		$unmarked = wcdo_count_unmarked_orders($wcdo_settings['delay_order_statuses']); ?>

	    <div class="notice notice-warning">
	    	<p><strong><?php _e('WooCommerce Delayed Orders', 'wc-delayed-orders'); ?></strong></p>
	        <p><?php _e('We found', 'wc-delayed-orders'); ?> <strong><?= $unmarked; ?> <?php _e('order(s)', 'wc-delayed-orders'); ?></strong> <?php _e('without a delay status. It is recommended to update all orders.', 'wc-delayed-orders'); ?></p>
	    </div>

	<?php }

	/**
     * Registers all available admin assets.
     * 
     * @since 1.0.0
     * @access public
     */
	public function register_assets() {

		if ( $this->instance->is_active ) {
			add_action('admin_enqueue_scripts', [$this, 'load_scripts']);
			add_action('admin_enqueue_scripts', [$this, 'load_styles']);
		}

	}

	/**
     * Loads all scripts for admin interface.
     * 
     * @since 1.0.0
     * @access public
     */
	public function load_scripts() {

		$file_version = $this->instance->version;
		if ( defined(WCDO_ENV) && WCDO_ENV == 'DEVELOPMENT' ) {
			$file_version = time();
		}

		is_readable(WCDO_PLUGIN_ASSETS_PATH . '/js/main.admin.js') &&
		wp_enqueue_script(
			'wcdo-main', 
			WCDO_PLUGIN_ASSETS . '/js/main.admin.js', 
			array('jquery'), 
			$file_version, 
			true
		);

	}

	/**
     * Loads all styles for admin interface.
     * 
     * @since 1.0.0
     * @access public
     */
	public function load_styles() {

		$file_version = $this->instance->version;
		if ( defined(WCDO_ENV) && WCDO_ENV == 'DEVELOPMENT' ) {
			$file_version = time();
		}

		
		is_readable(WCDO_PLUGIN_ASSETS_PATH . '/css/main.admin.css') &&
		wp_enqueue_style(
			'wcdo-main', 
			WCDO_PLUGIN_ASSETS . '/css/main.admin.css', 
			array(), 
			$file_version, 
		);

	}

	/**
	 * Adds a delayed orders filter input on orders list
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function add_filter_input() {

		global $post_type;

		if ( $post_type == 'shop_order' ) { ?>

			<?php $placeholder = __('Filter by number of days delayed', 'wc-delayed-orders'); ?>
			<?php $value = isset($_GET['delay_days']) ? intval(sanitize_text_field($_GET['delay_days'])) : ''; ?>
			<input type="text" name="delay_days" value="<?= esc_html($value); ?>" placeholder="<?= $placeholder; ?>" style="width: 240px;" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');">

		<?php }

	}

	/**
	 * Adds a header for the delayed orders custom column
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function add_custom_column_header($columns) {

		$reordered_columns = array();

		// Add the new column after the order status column
	    foreach( $columns as $key => $column){
	        $reordered_columns[$key] = $column;
	        if( $key ==  'order_status' ){
	            $reordered_columns['delay-status'] = __('Delay Status', 'wc-delayed-orders');
	        }
	    }
	    return $reordered_columns;

	}

	/**
	 * Adds data for the delayed orders custom column
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function add_custom_column_data($column, $post_id) {

		$delay_status = get_post_meta($post_id, '_wc_delay_status', true);

		if ( $column == 'delay-status' ) {

			$order = wc_get_order($post_id);
			$order_date = $order->get_date_created();
			$delay = human_time_diff(strtotime($order_date), time());
			$icon = $delay_status != '';
			$class = ['', ' green', ' red'][$icon ? (intval($delay_status) + 1) : 0];

			?>

				<mark class="delayed-order-tag<?= $class; ?>">
					<span>
						<?php if ( $icon ): ?>
							<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
								<path d="M256 8C119 8 8 119 8 256s111 248 248 248 248-111 248-248S393 8 256 8zm0 448c-110.5 0-200-89.5-200-200S145.5 56 256 56s200 89.5 200 200-89.5 200-200 200zm61.8-104.4l-84.9-61.7c-3.1-2.3-4.9-5.9-4.9-9.7V116c0-6.6 5.4-12 12-12h32c6.6 0 12 5.4 12 12v141.7l66.8 48.6c5.4 3.9 6.5 11.4 2.6 16.8L334.6 349c-3.9 5.3-11.4 6.5-16.8 2.6z" fill="currentColor"/>
							</svg>
							<?= $delay; ?>
						<?php else: ?>
							N/A
						<?php endif; ?>
					</span>
				</mark>

			<?php

		}

	}

	/**
	 * Updates the order delay status of an order
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function update_delay_status($post_ID, $post, $update) {

		global $wcdo_settings;

		if ( $post->post_type != 'shop_order' )
			return;

		$order = wc_get_order($post_ID);
		$delay_status = '0';

		if ( in_array(get_post_status($post_ID), $wcdo_settings['delay_order_statuses']) ) {

			// Map saved value to actual word
			$range = ['days', 'weeks', 'months', 'years'][$wcdo_settings['delay_days_range']];

			// Check if the order is actually delayed
			if ( strtotime($order->get_date_created()) < strtotime("-{$wcdo_settings['delay_days']} {$range}") ) {
				$delay_status = '1';
			}

		}

		update_post_meta($post->ID, '_wc_delay_status', $delay_status);

	}

	/**
	 * Updates the order delay status of all orders
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @uses update_delay_status
	 */
	public function update_delay_status_all() {

		$query = new \WP_Query(array(
			'post_type' 		=> 'shop_order',
			'posts_per_page' 	=> -1,
			'post_status' 		=> array_keys(wc_get_order_statuses()),
		));

		if ( $query->have_posts() ) {
			while ( $query->have_posts() ) {
				$query->the_post();
				$this->update_delay_status(get_the_ID(), get_post(get_the_ID()), false);
			}
			wp_reset_postdata();
		}

	}

	/**
	 * Modifies the WP_Query
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function posts_query_mod($wp_query) {

		global $pagenow;

	    if ( is_admin() && $pagenow == 'edit.php' ) {

	    	// Add the custom filter link
	        add_filter('views_edit-shop_order', [$this, 'add_filter_link']);

	        if ( $wp_query->query_vars['post_type'] == 'shop_order' ) {

	        	if ( (isset($_GET['delay_status']) && $_GET['delay_status'] == '1') || isset($_GET['delay_days']) ) {

	        		$meta_query 	= (array)$wp_query->get('meta_query');
	        		$meta_query[] 	= array(
	        			'key' 		=> '_wc_delay_status',
						'value' 	=> '1',
						'compare' 	=> '='
	        		);

	        		$wp_query->set('meta_query', $meta_query);

	        	}

	        	if ( isset($_GET['delay_days']) ) {

	        		$days 			= intval(sanitize_text_field($_GET['delay_days']));
	        		$date_query 	= (array)$wp_query->get('date_query');
	        		$date_query[] 	= array(
	        			'before'	=> date('F jS, Y', strtotime("-{$days} days"))
	        		);

	        		$wp_query->set('date_query', $date_query);

	        	}

	        }
	    }

	}

	/**
	 * Adds a delayed orders filter link on orders list
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function add_filter_link($views) {

		$query = new \WP_Query(array(
			'post_type' 		=> 'shop_order',
			'posts_per_page' 	=> -1,
			'post_status' 		=> array('wc-processing'),
			'meta_query'		=> array(
				array(
					'key' 		=> '_wc_delay_status',
					'value' 	=> '1',
					'compare' 	=> '='
				)
			)
		));

		$link 	= admin_url('edit.php?post_type=shop_order&delay_status=1');
		$class 	= isset($_GET['delay_status']) && $_GET['delay_status'] == '1' ? 'current' : '';
		$label 	= __('Delayed Orders', 'wc-delayed-orders');

	    $views['delayed-orders'] = "<a href=\"{$link}\" class=\"{$class}\">{$label} <span class=\"count\">({$query->post_count})</span></a>";

	    return $views;

	}

	/**
	 * Adds a delayed orders toolbar menu item
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function admin_toolbar_delayed_orders($toolbar) {

		if ( !current_user_can('manage_options') ) {
	    	return;
	    }

	    global $wcdo_settings;

	    $delayed_orders_count = wcdo_count_delay_orders($wcdo_settings['delay_order_statuses']);

		$toolbar->add_menu( array(
	        'id'    => 'delayed-orders',
	        'title' => "{$delayed_orders_count} " . __('Delayed Order(s)', 'wc-delayed-orders'),
	        'href'  => admin_url('edit.php?post_type=shop_order&delay_status=1'),
	        'meta'  => array(
	            'title' => __('Delayed Orders', 'wc-delayed-orders'),
	            'class'	=> $delayed_orders_count ? 'has-delayed-orders' : '',
	        ),
	    ));

	    $toolbar->add_menu( array(
	        'id'    	=> 'delayed-orders-display',
	        'parent'    => 'delayed-orders',
	        'title' 	=> __('Show Delayed Orders', 'wc-delayed-orders'),
	        'href'  	=> admin_url('edit.php?post_type=shop_order&delay_status=1'),
	        'meta'  	=> array(
	            'title' => __('Show Delayed Orders', 'wc-delayed-orders'),
	        ),
	    ));

	    $toolbar->add_menu( array(
	        'id'    	=> 'delayed-orders-settings',
	        'parent'    => 'delayed-orders',
	        'title' 	=> __('Settings', 'wc-delayed-orders'),
	        'href'  	=> admin_url('admin.php?page=wc-settings&tab=delayed_orders'),
	        'meta'  	=> array(
	            'title' => __('Settings', 'wc-delayed-orders'),
	        ),
	    ));

	}

}
