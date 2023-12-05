<?php
function openai_gpt_generate_text($prompt)
{
  $api_key = trim(get_option('openai_gpt_api_key'));

  $stored_instructions = get_option('openai_gpt_high_level_instructions', '');
  $stored_instructions = stripslashes($stored_instructions);

  $api_url = 'https://api.openai.com/v1/chat/completions';

  $data = [
    'model' => 'gpt-3.5-turbo',
    'messages' => [
      ['role' => 'system', 'content' => $stored_instructions],
      ['role' => 'user', 'content' => $prompt]
    ],
    'max_tokens' => 250,
    'temperature' => 0.7
  ];

  $args = [
    'body'    => json_encode($data),
    'headers' => [
      'Content-Type' => 'application/json',
      'Authorization' => 'Bearer ' . $api_key
    ],
    'timeout' => 30
  ];

  error_log('Using API Key: ' . $api_key);
  error_log('Sending Request Data: ' . print_r($data, true));

  $response = wp_remote_post($api_url, $args);

  if (is_wp_error($response)) {
    error_log('API Request Error: ' . $response->get_error_message());
  } else {
    error_log('API Response: ' . print_r($response, true));
  }

  $body = wp_remote_retrieve_body($response);

  return json_decode($body, true);
}
