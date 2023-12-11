<?php
function openai_gpt_admin_menu()
{
  // Main menu item - points to Training Data page
  add_menu_page(
    'BluestoneApps OpenAI', // Page title
    'BluestoneApps AI',     // Menu title
    'manage_options',       // Capability
    'openai-gpt-training',  // Menu slug (same as the first submenu)
    'openai_gpt_training_page', // Function for Training Data page
    'dashicons-admin-generic', // Icon
    81                       // Position
  );

  // Submenu for Training Data (this will be the default page for the main menu)
  add_submenu_page(
    'openai-gpt-training',
    'Training Data',
    'Training Data',
    'manage_options',
    'openai-gpt-training',
    'openai_gpt_training_page'
  );

  add_submenu_page(
    'openai-gpt-training',
    'History',
    'History',
    'manage_options',
    'openai-gpt-qa',
    'openai_gpt_questions_answers_page'
  );

  // Submenu for OpenAI Key
  add_submenu_page(
    'openai-gpt-training',
    'Settings',
    'Settings',
    'manage_options',
    'openai-gpt-settings',
    'openai_gpt_settings_page'
  );
}
add_action('admin_menu', 'openai_gpt_admin_menu');




function openai_gpt_settings_page()
{
  // Retrieve the stored welcome message
  $welcome_message = get_option('openai_gpt_welcome_message', '');

?>
  <div class="wrap">
    <h1>BluestoneApps OpenAI Settings</h1>
    <style>
      .openai-gpt-input {
        width: 75%;
      }
    </style>
    <form method="post" action="options.php">
      <?php
      settings_fields('openai-gpt-settings-group');
      do_settings_sections('openai-gpt-settings-group');
      ?>
      <table class="form-table">
        <tr valign="top">
          <th scope="row">OpenAI API Key</th>
          <td><input type="text" name="openai_gpt_api_key" value="<?php echo esc_attr(get_option('openai_gpt_api_key')); ?>" class="openai-gpt-input" /></td>
        </tr>
        <tr valign="top">
          <th scope="row">Welcome Message</th>
          <td>
            <?php
            // Settings for the wp_editor
            $editor_settings = array(
              'textarea_name' => 'openai_gpt_welcome_message',
              'textarea_rows' => 10
            );
            wp_editor(html_entity_decode($welcome_message), 'openai_gpt_welcome_message_editor', $editor_settings);
            ?>
          </td>
        </tr>
      </table>
      <?php submit_button(); ?>
    </form>
    <hr>
    <h2>Using the Shortcode</h2>
    <p>To use the BluestoneApps OpenAI GPT integration in your pages or posts, add the following shortcode to the content:</p>
    <code>[openai_gpt]</code>
    <p>When you add this shortcode, it will display an input field for asking questions and a results area for displaying responses from OpenAI's GPT model. Place the shortcode in any post or page where you want this feature to appear.</p>
  </div>
<?php
}


function openai_gpt_training_page()
{
  openai_gpt_handle_file_upload(); // Handle file upload

  // Check if the form has been submitted
  if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['high_level_instructions'])) {
      // Sanitize and save the high-level instructions
      update_option('openai_gpt_high_level_instructions', sanitize_textarea_field($_POST['high_level_instructions']));
    }

    if (isset($_POST['custom_urls'])) {
      // Sanitize and save the custom URLs
      update_option('openai_gpt_custom_urls', sanitize_textarea_field($_POST['custom_urls']));
    }
  }

  // Retrieve the stored instructions and custom URLs
  $stored_instructions = get_option('openai_gpt_high_level_instructions', '');
  $stored_instructions = stripslashes($stored_instructions);
  $custom_urls = get_option('openai_gpt_custom_urls', '');
  $custom_urls = stripslashes($custom_urls);

?>
  <div class="wrap">
    <h1>Training Data for OpenAI</h1>
    <form method="post" enctype="multipart/form-data">
      <table class="form-table">
        <!-- High-level System Instructions -->
        <tr valign="top">
          <th scope="row">High-level System Instructions</th>
          <td>
            <?php
            $editor_settings = array(
              'textarea_name' => 'high_level_instructions',
              'textarea_rows' => 14,
              'media_buttons' => false, // Disable media buttons
              'tinymce'       => array(
                'toolbar1' => 'link,unlink,bullist,numlist,pastetext,undo,redo', // Specify the buttons you want on the first toolbar
                'toolbar2' => '', // Clear the second toolbar
              ),
              'quicktags'     => true // Disable quicktags
            );
            wp_editor(html_entity_decode($stored_instructions), 'high_level_instructions', $editor_settings);
            ?>
          </td>
        </tr>

        <!-- Custom URLs -->
        <tr valign="top">
          <th scope="row">Enter Custom URLs <br><small>(Only one URL per line)</small></th>
          <td>
            <textarea name="custom_urls" rows="5" cols="50"><?php echo esc_textarea($custom_urls); ?></textarea>
          </td>
        </tr>

        <!-- File Upload Section -->
        <tr valign="top">
          <th scope="row">Upload Knowledge Files</th>
          <td><input type="file" name="knowledge_files[]" multiple /></td>
        </tr>
      </table>
      <?php submit_button('Save'); ?>
    </form>
  </div>
<?php
}




function openai_gpt_register_settings()
{
  register_setting('openai-gpt-settings-group', 'openai_gpt_api_key');
  register_setting('openai-gpt-settings-group', 'openai_gpt_welcome_message'); // Register new setting
}
add_action('admin_init', 'openai_gpt_register_settings');
