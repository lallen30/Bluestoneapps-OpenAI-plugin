<?php
function openai_gpt_questions_answers_page()
{
  global $wpdb;
  $table_name = $wpdb->prefix . 'openai_gpt_responses';

  $sort_field = isset($_GET['sort']) ? $_GET['sort'] : 'user_id';
  $current_order = isset($_GET['order']) && $_GET['order'] == 'ASC' ? 'DESC' : 'ASC';

  $username_link = '?page=openai-gpt-qa&sort=user_id&order=' . $current_order;
  $question_link = '?page=openai-gpt-qa&sort=question&order=' . $current_order;
  $response_link = '?page=openai-gpt-qa&sort=response&order=' . $current_order;
  $timestamp_link = '?page=openai-gpt-qa&sort=time&order=' . $current_order;

  // Query modification based on sort parameters
  $sort_order = isset($_GET['order']) ? $_GET['order'] : 'ASC';
  $results = $wpdb->get_results("SELECT * FROM $table_name ORDER BY $sort_field $sort_order");


  echo '<style>.heading-item { display:flex;} .username-heading span.sorting-indicator{visibility:visible;}</style>';
  echo '<div class="wrap">';
  echo '<h1>Questions and Answers</h1>';
  echo '<table class="widefat fixed" cellspacing="0">';

  echo '<thead><tr>';
  echo '<th><a class="heading-item username-heading" href="' . $username_link . '">Username <span class="sorting-indicator"></span></a></th>';
  echo '<th><a href="' . $question_link . '">Question</a></th>';
  echo '<th><a href="' . $response_link . '">Response</a></th>';
  echo '<th><a href="' . $timestamp_link . '">Timestamp</a></th>';
  echo '</tr></thead>';

  echo '<tbody>';

  foreach ($results as $row) {
    // Get username
    if ($row->user_id && ($user_info = get_userdata($row->user_id))) {
      // Link to user's profile
      $username = '<a href="' . esc_url(admin_url('user-edit.php?user_id=' . $row->user_id)) . '">' . esc_html($user_info->user_login) . '</a>';
    } else {
      // User is Guest
      $username = 'Guest';
    }

    // Format timestamp
    $timestamp = date('m-d-Y h:i a', strtotime($row->time));

    echo '<tr>';
    echo '<td>' . $username . '</td>';
    echo '<td>' . esc_html($row->question) . '</td>';
    echo '<td>' . esc_html($row->response) . '</td>';
    echo '<td>' . esc_html($timestamp) . '</td>';
    echo '</tr>';
  }

  echo '</tbody></table></div>';
}
