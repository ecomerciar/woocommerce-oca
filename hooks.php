<?php

if (!defined('ABSPATH')) {
	exit;// Exit if accessed directly.
}

// --- Settings
add_action('admin_init', 'Ecomerciar\OCA\Settings\init_settings');
add_action('admin_menu', 'Ecomerciar\OCA\Settings\create_menu_option');
add_action('admin_enqueue_scripts', 'Ecomerciar\OCA\Settings\add_assets_files');