<?php
function openai_gpt_generate_text($prompt, $knowledge_data)
{
  // Retrieve your existing API key and instructions
  $api_key = trim(get_option('openai_gpt_api_key'));
  $stored_instructions = get_option('openai_gpt_high_level_instructions', '');
  $custom_urls = get_option('custom_urls', ''); // Ensure this key matches the one used in update_option
  $custom_urls = 'Use only the information from ' . stripslashes($custom_urls) . ' to formulate your responses.';
  $stored_instructions = stripslashes($stored_instructions);

  $knowledge_data = get_option('openai_gpt_knowledge_data', '');

  $system_message_content = $stored_instructions . "\n" . $custom_urls . "\n\n" . $knowledge_data;

  // Debug: Check API Key and Instructions
  error_log('API Key: ' . $api_key);
  error_log('Stored Instructions: ' . $stored_instructions);

  // Retrieve the session history
  $session_history = isset($_SESSION['openai_gpt_history']) ? $_SESSION['openai_gpt_history'] : '';
  $full_prompt = $session_history . "\n" . $prompt;
  // Debug: Check Session History
  error_log('Session History: ' . $session_history);

  // Prepare the data for the API request with the current prompt
  $data = [
    'model' => 'gpt-3.5-turbo',
    'messages' => [
      ['role' => 'system', 'content' => $system_message_content],
      ['role' => 'user', 'content' => $full_prompt]
    ],
    'max_tokens' => 500,
    'temperature' => 0.5
  ];

  // Debug: Check Data Being Sent
  error_log('Data being sent: ' . print_r($data, true));

  // Set up the request
  $args = [
    'body'    => json_encode($data),
    'headers' => [
      'Content-Type' => 'application/json',
      'Authorization' => 'Bearer ' . $api_key
    ],
    'timeout' => 30
  ];

  // Make the API request
  $api_url = 'https://api.openai.com/v1/chat/completions'; // Ensure this is correct
  $response = wp_remote_post($api_url, $args);

  // Check and log the response
  if (is_wp_error($response)) {
    error_log('API Request Error: ' . $response->get_error_message());
    return;
  } else {
    error_log('API Response: ' . print_r($response, true));
  }

  // Process the response
  $body = wp_remote_retrieve_body($response);
  $response_data = json_decode($body, true);

  // Debug: Check Decoded Response
  error_log('Decoded Response: ' . print_r($response_data, true));

  // Update the session history AFTER receiving the response
  $response_text = $response_data['choices'][0]['text'];
  $_SESSION['openai_gpt_history'] .= "\nUser: " . $prompt . "\nAI: " . $response_text;

  // Return the response
  return $response_data;
}



function openai_gpt_handle_file_upload()
{
  if (isset($_FILES['knowledge_files']) && is_array($_FILES['knowledge_files']['tmp_name'])) {
    $knowledge_data_combined = '';

    foreach ($_FILES['knowledge_files']['tmp_name'] as $index => $tmpName) {
      if ($_FILES['knowledge_files']['error'][$index] == UPLOAD_ERR_OK) {
        $file_contents = file_get_contents($tmpName);
        if ($file_contents !== false) {
          // Combine the contents of each file
          $knowledge_data_combined .= $file_contents . "\n\n";
        }
      }
    }

    if (!empty($knowledge_data_combined)) {
      // Store the combined data in a WordPress option
      update_option('openai_gpt_knowledge_data', $knowledge_data_combined);
    }
  }
}
