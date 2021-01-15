<?php
/**
 * Plugin Name:     Trupayers Signup
 * Plugin URI:      https://www.spinit.se
 * Description:     Sign up form for Trupayers
 * Author:          Spinit AB
 * Author URI:      https://www.spinit.se
 * Text Domain:     trupayers-signup
 * Domain Path:     /languages
 * Version:         0.1.0
 *
 * @package         Trupayers_Signup
 */

// Your code starts here.


// Setting react app path constants.
define('TRU_PLUGIN_VERSION','0.1.0' );
define('TRU_PLUGIN_DIR_URL', plugin_dir_url( __FILE__ ) . 'trupayers-signup-react/');
define('TRU_REACT_APP_BUILD', TRU_PLUGIN_DIR_URL . 'build/');
define('TRU_MANIFEST_URL', TRU_REACT_APP_BUILD . 'asset-manifest.json');

/**
 * Calling the plugin class with parameters.
 */
function tru_load_plugin(){
	// Loading the app in WordPress admin main screen.
	new TruLoadReactApp( 'admin_enqueue_scripts', 'index.php', false,'#wpbody .wrap');
	// Loading the app WordPress front end page.
	new TruLoadReactApp( 'wp_enqueue_scripts', '', 'is_front_page', '#page');
}

add_action('init','tru_load_plugin');


/**
 * Class TruLoadReactApp.
 */
class TruLoadReactApp {

	/**
	 * @var string
	 */
	private $selector = '';
	/**
	 * @var string
	 */
	private $limit_load_hook = '';
	/**
	 * @var bool|string
	 */
	private $limit_callback = '';

	/**
	 * TruLoadReactApp constructor.
	 *
	 * @param string $enqueue_hook Hook to enqueue scripts.
	 * @param string $limit_load_hook Limit load to hook in admin load. If front end pass empty string.
	 * @param bool|string $limit_callback Limit load by callback result. If back end send false.
	 * @param string $css_selector Css selector to render app.
	 */
	function __construct( $enqueue_hook, $limit_load_hook, $limit_callback = false, $css_selector)  {
		$this->selector = $css_selector;
		$this->limit_load_hook = $limit_load_hook;
		$this->limit_callback= $limit_callback;

		add_action( $enqueue_hook, [$this,'load_react_app']);
	}

	/**
	 * Load react app files in WordPress admin.
	 *
	 * @param $hook
	 *
	 * @return bool|void
	 */
	function load_react_app( $hook ) {
		// Limit app load in admin by admin page hook.
		$is_main_dashboard = $hook === $this->limit_load_hook;

		if ( ! $is_main_dashboard && is_bool($this->limit_callback))
			return;

		// Limit app load in front end by callback.
		$limit_callback = $this->limit_callback;

		if(is_string($limit_callback) && !$limit_callback()  )
			return;

		// Get assets links.
		$assets_files = $this->get_assets_files();

		$js_files = array_filter($assets_files, array($this, 'tru_filter_js_files'));
		$css_files = array_filter($assets_files, array($this, 'tru_filter_css_files'));

		// Load css files.
		foreach ( $css_files as $index => $css_file ) {
			wp_enqueue_style( 'trupayers-signup-' . $index, TRU_REACT_APP_BUILD . $css_file );
		}

		// Load js files.
		foreach ( $js_files as $index => $js_file ) {
			wp_enqueue_script( 'trupayers-signup-' . $index, TRU_REACT_APP_BUILD . $js_file, array(), TRU_PLUGIN_VERSION, true );
		}

		// Variables for app use - These variables will be available in window.truReactApp variable.
		wp_localize_script( 'trupayers-signup-0', 'truReactApp',
			array( 'appSelector' => $this->selector )
		);
	}

	/**
	 * Get app entry points assets files.
	 *
	 * @return array|bool
	 */
	private function get_assets_files(){

		// Request manifest file. TODO: fix this path
		$request = file_get_contents( __DIR__ . '/trupayers-signup-react/build/asset-manifest.json' );

		// If the remote request fails.
		if ( !$request  )
			return false;

		// Convert json to php array.
		$files_data = json_decode( $request );
		if ( $files_data === null )
			return false;

		// No entry points found.
		if ( ! property_exists( $files_data, 'entrypoints' ) )
			return false;

		return $files_data->entrypoints;
	}

	/**
	 * Get js files from assets array.
	 *
	 * @param array $file_string
	 *
	 * @return bool
	 */
	private function tru_filter_js_files ($file_string){
		return pathinfo($file_string, PATHINFO_EXTENSION) === 'js';
	}

	/**
	 * Get css files from assets array.
	 *
	 * @param array $file_string
	 *
	 * @return bool
	 */
	private function tru_filter_css_files ($file_string) {
		return pathinfo( $file_string, PATHINFO_EXTENSION ) === 'css';
	}
}



