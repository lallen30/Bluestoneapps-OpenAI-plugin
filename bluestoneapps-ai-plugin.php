<?php
/*
Plugin Name: BluestoneApps AI Plugin
Description: A simple plugin to integrate OpenAI GPT API with WordPress.
Version: 1.0
Author: Your Name
*/

function openai_gpt_activate()
{
  openai_gpt_create_table();
}

function openai_gpt_create_table()
{
  global $wpdb;
  $table_name = $wpdb->prefix . 'openai_gpt_responses';

  $sql = "CREATE TABLE $table_name (
      id mediumint(9) NOT NULL AUTO_INCREMENT,
      time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
      user_id mediumint(9) NOT NULL,
      question text NOT NULL,
      response text NOT NULL,
      PRIMARY KEY  (id)
  );";

  require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
  dbDelta($sql);
}
register_activation_hook(__FILE__, 'openai_gpt_create_table');

include_once plugin_dir_path(__FILE__) . 'includes/questions-answers-page.php';
include_once plugin_dir_path(__FILE__) . 'includes/openai-gpt-functions.php';
include_once plugin_dir_path(__FILE__) . 'includes/admin-menu.php';
include_once plugin_dir_path(__FILE__) . 'includes/admin-styles.php';
include_once plugin_dir_path(__FILE__) . 'includes/shortcode.php';

function openai_gpt_enqueue_admin_styles()
{
  wp_enqueue_style('wp-admin');
  wp_enqueue_style('admin-css', admin_url('css/admin.css'), array(), null);
  wp_enqueue_style('list-tables');
  wp_enqueue_style('dashboard');
  wp_enqueue_style('dashicons');
}


add_action('admin_enqueue_scripts', 'openai_gpt_enqueue_admin_styles');
