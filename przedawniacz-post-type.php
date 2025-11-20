<?php
/**
 * Plugin Name: Przedawniacz post-type
 * Description: Automatyczne usuwanie/kwarantanna wpisów po określonym czasie
 * Version: 1.0
 */

if (!defined('ABSPATH')) exit;

define('AUTO_CLEANUP_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('AUTO_CLEANUP_PLUGIN_URL', plugin_dir_url(__FILE__));

function auto_cleanup_register_quarantine_type() {
    register_post_type('quarantine', [
        'label' => 'Kwarantanna',
        'public' => false,
        'show_ui' => true,
        'show_in_menu' => 'auto-cleanup-settings',
        'capability_type' => 'post',
        'hierarchical' => false
    ]);
}
add_action('init', 'auto_cleanup_register_quarantine_type');

require_once AUTO_CLEANUP_PLUGIN_DIR . 'includes/manager-kwarantanny.php';
require_once AUTO_CLEANUP_PLUGIN_DIR . 'includes/manager-sprzatania.php';
require_once AUTO_CLEANUP_PLUGIN_DIR . 'includes/sprawdzenie-plikow.php';
require_once AUTO_CLEANUP_PLUGIN_DIR . 'includes/ustawienia.php';

$sprzatacz = new Sprzatacz();
$strona_ustawienia = new Strona_Ustawienia();