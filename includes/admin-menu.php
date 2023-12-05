<?php
function openai_gpt_admin_menu()
{
  add_menu_page(
    'BluestoneApps OpenAI Settings',
    'BluestoneApps OpenAI',
    'manage_options',
    'openai-gpt-settings',
    'openai_gpt_settings_page',
    'dashicons-admin-generic',
    81
  );

  // Add submenu for Training Data
  add_submenu_page(
    'openai-gpt-settings',
    'Training Data', // Page title
    'Training Data', // Menu title
    'manage_options', // Capability
    'openai-gpt-training', // Menu slug
    'openai_gpt_training_page' // Function to display the page
  );
}
add_action('admin_menu', 'openai_gpt_admin_menu');

function openai_gpt_settings_page()
{
?>
  <div class="wrap">
    <h1>BluestoneApp OpenAI Settings</h1>
    <form method="post" action="options.php">
      <?php
      settings_fields('openai-gpt-settings-group');
      do_settings_sections('openai-gpt-settings-group');
      ?>
      <table class="form-table">
        <tr valign="top">
          <th scope="row">OpenAI API Key</th>
          <td><input type="text" name="openai_gpt_api_key" value="<?php echo esc_attr(get_option('openai_gpt_api_key')); ?>" /></td>
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
  // Check if the form has been submitted
  if (isset($_POST['high_level_instructions'])) {
    // Sanitize and save the data
    update_option('openai_gpt_high_level_instructions', sanitize_textarea_field($_POST['high_level_instructions']));
  }

  // Retrieve the stored instructions
  $stored_instructions = get_option('openai_gpt_high_level_instructions', '');
  $stored_instructions = stripslashes($stored_instructions);


?>
  <div class="wrap">
    <h1>Training Data for OpenAI</h1>
    <form method="post">
      <table class="form-table">
        <tr valign="top">
          <th scope="row">High-level System Instructions</th>
          <td>
            <?php
            // Settings array for wp_editor
            $editor_settings = array(
              'textarea_name' => 'high_level_instructions',
              'textarea_rows' => 20
            );

            // Replace the existing wp_editor call with this one
            wp_editor(html_entity_decode($stored_instructions), 'high_level_instructions', $editor_settings);
            ?>
          </td>
        <tr valign="top">
          <th scope="row">Data File</th>
          <td><input type="file" name="training_data_file" /></td>
        </tr>
      </table>
      <?php submit_button('Upload Data'); ?>
    </form>
  </div>
<?php
}

function openai_gpt_register_settings()
{
  register_setting('openai-gpt-settings-group', 'openai_gpt_api_key');
}
add_action('admin_init', 'openai_gpt_register_settings');