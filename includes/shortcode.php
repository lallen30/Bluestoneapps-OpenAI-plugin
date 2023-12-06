<?php
function openai_gpt_shortcode()
{
  $api_key = get_option('openai_gpt_api_key');

  $site_title = get_bloginfo('name');

  $default_message = 'Welcome, I am the ' . $site_title . ' AI Bot. <br>I\'m here to assist you. <br>Please enter your question in the field below.';

  $welcome_message = get_option('openai_gpt_welcome_message', $default_message);
  if ($welcome_message === '') {
    $formatted_message = $default_message;
  } else {
    $formatted_message = wpautop($welcome_message);
  }


  ob_start(); // Start output buffering
?>
  <style>
    div#openai-gpt-form {
      display: flex;
      max-width: 90%;
      justify-content: space-around;
      margin-left: -2%;
    }

    .openai-gpt-response {
      background-color: #f0f0f0;
      margin-bottom: 10px;
      padding: 10px;
      border-radius: 5px;
    }

    .line-break {
      margin: 5px 0;
    }


    .openai-gpt-response>div>div {
      padding-left: 10px;
    }

    #loading {
      font-weight: bold;
    }
  </style>

  <div id="openai-gpt-welcome">
    <?php echo $formatted_message; ?>
  </div>

  <div id="loading" style="display: none;">
    I am getting that answer for you. Please wait<span id="loadingDots"></span>
  </div>

  <div id="openai-gpt-results" style="height: 10px; overflow-y: scroll; margin-bottom: 10px;"></div>
  <div id="openai-gpt-form">
    <input type="text" id="openai-gpt-question" placeholder="Ask a question" style="width: 80%;">
    <button id="openai-gpt-submit">Submit</button>
  </div>
  <script type="text/javascript">
    var apiKey = <?php echo json_encode($api_key); ?>; // Pass the API key to JavaScript
    // console.log('API Key:', apiKey); // Log the API key in the console

    jQuery(document).ready(function($) {
      $('#openai-gpt-submit').click(function() {
        var prompt = $('#openai-gpt-question').val();
        console.log("Prompt:", prompt);
        if (prompt) {
          $('#openai-gpt-welcome').hide();
          $('#loading').show(); // Show the loading text
          var dotsAnimation = animateDots(); // Start the dots animation
          $('#openai-gpt-results').css('height', '450px');

          $.post(ajaxurl, {
            action: 'openai_gpt_request',
            prompt: prompt
          }, function(response) {
            console.log("Response:", response);
            clearInterval(dotsAnimation); // Stop the dots animation
            $('#loading').hide(); // Hide the loading text
            response = response.replace(/\n\n/g, '<div class="line-break"><div>'); // Replace double line breaks with a single <br>
            var responseHtml = '<div class="openai-gpt-response"><strong>Question:</strong> ' + prompt + '<br><strong>Response:</strong><div>' + response + '</div></div>';
            $('#openai-gpt-results').prepend(responseHtml);
          }).fail(function(error) {
            clearInterval(dotsAnimation); // Stop the dots animation
            $('#loading').hide(); // Hide the loading text
            console.log("Error:", error);
          });
          $('#openai-gpt-question').val('');
        }
      });
    });

    function animateDots() {
      var dots = window.setInterval(function() {
        var wait = document.getElementById("loadingDots");
        if (wait.innerHTML.length > 2)
          wait.innerHTML = "";
        else
          wait.innerHTML += ".";
      }, 500);
      return dots;
    }
  </script>
<?php
  return ob_get_clean(); // Return the buffered output
}
add_shortcode('openai_gpt', 'openai_gpt_shortcode');

function openai_gpt_ajax_request()
{
  global $wpdb;
  $table_name = $wpdb->prefix . 'openai_gpt_responses';

  $prompt = sanitize_text_field($_POST['prompt']);
  $response = openai_gpt_generate_text($prompt);
  $user_id = get_current_user_id() ?: 0;

  if (!empty($response['choices'][0]['message']['content'])) {
    $reply = $response['choices'][0]['message']['content'];

    // Insert data into database
    $wpdb->insert($table_name, array(
      'time' => current_time('mysql'),
      'user_id' => $user_id,
      'question' => $prompt,
      'response' => $reply
    ));

    echo $reply;
  } else {
    echo 'I seem to be having technical difficulties right now. Please try again later.';
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
