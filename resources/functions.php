<?php

/**
 * Do not edit anything in this file unless you know what you're doing
 */

use Roots\Sage\Config;
use Roots\Sage\Container;

/**
 * Helper function for prettying up errors
 * @param string $message
 * @param string $subtitle
 * @param string $title
 */
$sage_error = function ($message, $subtitle = '', $title = '') {
    $title = $title ?: __('Sage &rsaquo; Error', 'sage');
    $footer = '<a href="https://roots.io/sage/docs/">roots.io/sage/docs/</a>';
    $message = "<h1>{$title}<br><small>{$subtitle}</small></h1><p>{$message}</p><p>{$footer}</p>";
    wp_die($message, $title);
};

/**
 * Ensure compatible version of PHP is used
 */
if (version_compare('7.1', phpversion(), '>=')) {
    $sage_error(__('You must be using PHP 7.1 or greater.', 'sage'), __('Invalid PHP version', 'sage'));
}

/**
 * Ensure compatible version of WordPress is used
 */
if (version_compare('4.7.0', get_bloginfo('version'), '>=')) {
    $sage_error(__('You must be using WordPress 4.7.0 or greater.', 'sage'), __('Invalid WordPress version', 'sage'));
}

/**
 * Ensure dependencies are loaded
 */
if (!class_exists('Roots\\Sage\\Container')) {
    if (!file_exists($composer = __DIR__.'/../vendor/autoload.php')) {
        $sage_error(
            __('You must run <code>composer install</code> from the Sage directory.', 'sage'),
            __('Autoloader not found.', 'sage')
        );
    }
    require_once $composer;
}

/**
 * Sage required files
 *
 * The mapped array determines the code library included in your theme.
 * Add or remove files to the array as needed. Supports child theme overrides.
 */
array_map(function ($file) use ($sage_error) {
    $file = "../app/{$file}.php";
    if (!locate_template($file, true, true)) {
        $sage_error(sprintf(__('Error locating <code>%s</code> for inclusion.', 'sage'), $file), 'File not found');
    }
}, ['helpers', 'setup', 'filters', 'admin']);

/**
 * Here's what's happening with these hooks:
 * 1. WordPress initially detects theme in themes/sage/resources
 * 2. Upon activation, we tell WordPress that the theme is actually in themes/sage/resources/views
 * 3. When we call get_template_directory() or get_template_directory_uri(), we point it back to themes/sage/resources
 *
 * We do this so that the Template Hierarchy will look in themes/sage/resources/views for core WordPress themes
 * But functions.php, style.css, and index.php are all still located in themes/sage/resources
 *
 * This is not compatible with the WordPress Customizer theme preview prior to theme activation
 *
 * get_template_directory()   -> /srv/www/example.com/current/web/app/themes/sage/resources
 * get_stylesheet_directory() -> /srv/www/example.com/current/web/app/themes/sage/resources
 * locate_template()
 * ├── STYLESHEETPATH         -> /srv/www/example.com/current/web/app/themes/sage/resources/views
 * └── TEMPLATEPATH           -> /srv/www/example.com/current/web/app/themes/sage/resources
 */
array_map(
    'add_filter',
    ['theme_file_path', 'theme_file_uri', 'parent_theme_file_path', 'parent_theme_file_uri'],
    array_fill(0, 4, 'dirname')
);
Container::getInstance()
    ->bindIf('config', function () {
        return new Config([
            'assets' => require dirname(__DIR__).'/config/assets.php',
            'theme' => require dirname(__DIR__).'/config/theme.php',
            'view' => require dirname(__DIR__).'/config/view.php',
        ]);
    }, true);


/**
 * Add text domain global variable
 */
global $text_domain;
$text_domain = 'breda-actief';

/**
 * Image sizes
 */
add_image_size('image-header', 1920, 1300, true);
add_image_size('image-banner', 1920, 713, true);
add_image_size('image-banner-m', 1280, 475, true);
add_image_size('image-banner-s', 600, 223, true);
add_image_size('image-feat', 650, 650, true);
add_image_size('image-feat-s', 360, 360, true);
add_image_size('image-half-block', 600, 750, true);

/**
 * Add excerpts to pages
 */
add_post_type_support( 'page', 'excerpt' );

/**
 * Add post thumbnail theme support
 */
add_theme_support( 'post-thumbnails' );

/**
 * Add ACF Options page
 */
if( function_exists('acf_add_options_page') ) {
	acf_add_options_page(array(
		'page_title' 	=> 'Theme General Settings',
		'menu_title'	=> 'Theme Settings',
		'menu_slug' 	=> 'theme-general-settings',
		'capability'	=> 'edit_posts',
		'redirect'		=> false
	));
	
	acf_add_options_sub_page(array(
		'page_title' 	=> 'Contact',
		'menu_title'	=> 'Contact',
		'parent_slug'	=> 'theme-general-settings',
	));
	
	acf_add_options_sub_page(array(
		'page_title' 	=> 'Footer',
		'menu_title'	=> 'Footer',
		'parent_slug'	=> 'theme-general-settings',
	));
	
	acf_add_options_sub_page(array(
		'page_title' 	=> 'Social',
		'menu_title'	=> 'Social',
		'parent_slug'	=> 'theme-general-settings',
	));
}

/**
 * Add Shortcode for social icons
 */
//shortcode
function social_shortcode($atts, $content = null){
	
	$fb_url = get_field('facebook_url', 'option');
	$twitter_url = get_field('twitter_url', 'option');
	$insta_url = get_field('instagram_url', 'option');
	$linkedin_url = get_field('linkedin_url', 'option');
	$youtube_url = get_field('youtube_url', 'option');
	
	$template_url = get_template_directory_uri();
	$social = '';
	$social .= '<nav class="social">';
	$social .= '<ul>';
	
	if (!empty($fb_url)) :
	$social .= '<li class="facebook"><a href="'.esc_url($fb_url).'" target="_blank">'.file_get_contents($template_url . '/assets/images/icon_fb.svg').'</a></li>';
	endif;
	
	if (!empty($twitter_url)) :
	$social .= '<li class="twitter"><a href="'.esc_url($twitter_url).'" target="_blank">'.file_get_contents($template_url . '/assets/images/icon_twitter.svg').'</a></li>';
	endif;
	
	if (!empty($insta_url)) :
	$social .= '<li class="instagram"><a href="'.esc_url($insta_url).'" target="_blank">'.file_get_contents($template_url . '/assets/images/icon_insta.svg').'</a></li>';
	endif;
	
	if (!empty($linkedin_url)) :
	$social .= '<li class="linkedin"><a href="'.esc_url($linkedin_url).'" target="_blank">'.file_get_contents($template_url . '/assets/images/icon_linkedin.svg').'</a></li>';
	endif;
	
	if (!empty($youtube_url)) :
	$social .= '<li class="youtube"><a href="'.esc_url($youtube_url).'" target="_blank">'.file_get_contents($template_url . '/assets/images/icon_youtube.svg').'</a></li>';
	endif;
	
	$social .= '</ul>';
	$social .= '</nav>';
	
	return $social;
}
add_shortcode( 'social', 'social_shortcode' );