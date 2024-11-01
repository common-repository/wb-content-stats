<?php

/*
Plugin Name: WB Content Stats
Description: A simple plugin to showcase the word & character count and reading time.
Version: 1.0.0
Requires at least: 5.2
Requires PHP: 7.2
Tested up to: 6.3.1
Author: Md. Ariful Basher
Author URI: https://www.linkedin.com/in/ababir1/
License: GPLv2 or later
text Domain: wbcontentstats
Domain Path: /languages
*/

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class WBCS
{
    function __construct()
    {
        add_action('admin_menu', array($this, 'adminPage'));
        add_action('admin_init', array($this, 'settings'));
        add_filter('the_content', array($this, 'wbContentWrap'));
        add_action('init', array($this, 'languages'));
    }

    function languages()
    {
        load_plugin_textdomain('wbcontentstats', false, dirname(plugin_basename(__FILE__)) . '/languages');
    }

    function wbContentWrap($content)
    {
        if (is_main_query() and is_single() and (get_option('wbcs_wordcount', '1') or
            get_option('wbcs_charactercount', '1') or
            get_option('wbcs_readtime', '1')
        )) {
            return $this->createHTML($content);
        }
        return $content;
    }


    /* Front end view HTML*/
    function createHTML($content)
    {
        $html = '<h5 style="border-bottom: 1px dashed lightcoral;">' . esc_html(get_option('wbcs_headline', 'Post Stats')) . '</h5> <p style="background-color: lightcyan; padding: 1rem; border-radius: 0.6rem;">';

        // get word count once because both word count and read time will need it.

        if (get_option('wbcs_wordcount', '1') or get_option('wbcs_readtime', '1')) {
            $wordCount = str_word_count(strip_tags($content));
        }

        // Word Count View
        if (get_option('wbcs_wordcount', '1')) {
            $html .= esc_html__('This content has', 'wbcontentstats') . '  ' . $wordCount . '  ' . esc_html__('words', 'wbcontentstats') . '<br>';
        }

        // Character Count View
        if (get_option('wbcs_charactercount', '1')) {
            $html .= esc_html__('This content has', 'wbcontentstats') . '  ' . strlen(strip_tags($content)) . '  ' . esc_html__('characters', 'wbcontentstats') . '<br>';
        }

        // Read Time View
        if (get_option('wbcs_readtime', '1')) {
            $html .= esc_html__('This content will take about', 'wbcontentstats') . '  ' . round($wordCount / 225) . '  ' . esc_html__('minute(s) to read', 'wbcontentstats') . '<br>';
        }

        $html .= '</p>';


        // Location define of the content stats
        if (get_option('wbcs_location', '0') == '0') {
            return $html . $content;
        }
        return $content . $html;
    }




    /* Settings page function setup*/
    function settings()
    {
        add_settings_section('wbcs_first_section', null, null, 'webbricks');

        /* Location of the number */
        add_settings_field('wbcs_location', 'Display Location', array($this, 'locationHTML'), 'webbricks', 'wbcs_first_section');
        register_setting('wordCountPlugin', 'wbcs_location', array('sanitize_callback' => array($this, 'wbSanitizeLocation'), 'default' => '0'));

        /* Headline text */
        add_settings_field('wbcs_headline', 'Headline Text', array($this, 'headlineHTML'), 'webbricks', 'wbcs_first_section');
        register_setting('wordCountPlugin', 'wbcs_headline', array('sanitize_callback' => 'sanitize_text_field', 'default' => 'Post Headline'));

        /* Word count check box*/
        add_settings_field('wbcs_wordcount', 'Word Count', array($this, 'wordcountHTML'), 'webbricks', 'wbcs_first_section');
        register_setting('wordCountPlugin', 'wbcs_wordcount', array('sanitize_callback' => 'sanitize_text_field', 'default' => '1'));

        /* Character count check box*/
        add_settings_field('wbcs_charactercount', 'Character Count', array($this, 'charactercountHTML'), 'webbricks', 'wbcs_first_section');
        register_setting('wordCountPlugin', 'wbcs_charactercount', array('sanitize_callback' => 'sanitize_text_field', 'default' => '1'));

        /* Read time check box*/
        add_settings_field('wbcs_readtime', 'Read Time', array($this, 'readtimeHTML'), 'webbricks', 'wbcs_first_section');
        register_setting('wordCountPlugin', 'wbcs_readtime', array('sanitize_callback' => 'sanitize_text_field', 'default' => '1'));
    }


    /* Custom Text Sanitizer*/
    function wbSanitizeLocation($input)
    {
        if ($input != '0' and $input != '1') {
            add_settings_error('wbcs_location', 'wbcs_location_error', esc_html__('Display location must be either beginning or end.', 'wbcontentstats'));
            return get_option('wbcs_location');
        }
        return $input;
    }



    /* Settings page HTML for front view*/
    function readtimeHTML()
    { ?>
        <input type="checkbox" name="wbcs_readtime" value="1" <?php checked(get_option('wbcs_readtime'), '1') ?>>
    <?php }

    function charactercountHTML()
    { ?>
        <input type="checkbox" name="wbcs_charactercount" value="1" <?php checked(get_option('wbcs_charactercount'), '1') ?>>
    <?php }

    function wordcountHTML()
    { ?>
        <input type="checkbox" name="wbcs_wordcount" value="1" <?php checked(get_option('wbcs_wordcount'), '1') ?>>
    <?php }

    function headlineHTML()
    { ?>
        <input type="text" name="wbcs_headline" value="<?php echo esc_attr(get_option('wbcs_headline')) ?>">
    <?php }

    function locationHTML()
    { ?>
        <select name="wbcs_location">
            <option value="0" <?php selected(get_option('wbcs_location'), '0',) ?>>Beginning of post</option>
            <option value="1" <?php selected(get_option('wbcs_location'), '1',) ?>> End of post</option>
        </select>
    <?php }


    /* Settings button position for admin dashboard*/
    function adminPage()
    {
        add_options_page('Web Bricks Settings', esc_html__('Web Bricks', 'wbcontentstats'), 'manage_options', 'webbricks', array($this, 'wpHTML'));
    }


    /* WB Word Count settings page*/
    function wpHTML()
    { ?>
        <div class="wrap">
            <h1 style="color: slateblue;">Web Bricks Content Counter Settings</h1>
            <p> <strong><u> General settings</u></strong> | Styling options <i> [Coming soon] </i></p>
            <form action="options.php" method="POST">
                <?php
                settings_fields('wordCountPlugin');
                do_settings_sections('webbricks');
                submit_button();
                ?>
            </form>
        </div>
<?php }
}


$WBCS = new WBCS();
?>