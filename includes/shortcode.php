<?php
function openai_gpt_shortcode()
{
  $api_key = get_option('openai_gpt_api_key');

  ob_start(); // Start output buffering
?>
  <style>
    div#openai-gpt-form {
      display: flex;
      justify-content: space-around;
      max-width: 80%;
      margin-bottom: 20px;
    }
  </style>
  <div id="openai-gpt-results" style="height: 300px; overflow-y: scroll; margin-bottom: 20px;"></div>
  <div id="openai-gpt-form" style="position: fixed; bottom: 0; width: 100%;">
    <input type="text" id="openai-gpt-question" placeholder="Ask a question" style="width: 80%;">
    <button id="openai-gpt-submit">Submit</button>
  </div>
  <script type="text/javascript">
    var apiKey = <?php echo json_encode($api_key); ?>; // Pass the API key to JavaScript
    console.log('API Key:', apiKey); // Log the API key in the console

    jQuery(document).ready(function($) {
      $('#openai-gpt-submit').click(function() {
        var prompt = $('#openai-gpt-question').val();
        console.log("Prompt:", prompt); // Debugging line
        if (prompt) {
          $.post(ajaxurl, {
            action: 'openai_gpt_request',
            prompt: prompt
          }, function(response) {
            console.log("Response:", response); // Debugging line
            $('#openai-gpt-results').prepend('<div>' + response + '</div>');
          }).fail(function(error) {
            console.log("Error:", error); // Debugging line
          });
        }
      });
    });
  </script>
<?php
  return ob_get_clean(); // Return the buffered output
}
add_shortcode('openai_gpt', 'openai_gpt_shortcode');

function openai_gpt_ajax_request()
{
  $prompt = sanitize_text_field($_POST['prompt']);
  $response = openai_gpt_generate_text($prompt);

  if (!empty($response['choices'][0]['message']['content'])) {
    echo $response['choices'][0]['message']['content'];
  } else {
    echo 'No response from OpenAI.';
  }

  wp_die(); // Terminate AJAX request
}

add_action('wp_ajax_openai_gpt_request', 'openai_gpt_ajax_request');
add_action('wp_ajax_nopriv_openai_gpt_request', 'openai_gpt_ajax_request');

function openai_gpt_enqueue_scripts()
{
  // Enqueue jQuery if not already loaded
  wp_enqueue_script('jquery');

  // Define ajaxurl
  wp_add_inline_script('jquery', 'var ajaxurl = "' . admin_url('admin-ajax.php') . '";', 'before');
}
add_action('wp_enqueue_scripts', 'openai_gpt_enqueue_scripts');
