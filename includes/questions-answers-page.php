<?php
function openai_gpt_questions_answers_page()
{
  global $wpdb;
  $table_name = $wpdb->prefix . 'openai_gpt_responses';

  // Set default sorting field to 'time' and order to 'DESC'
  $sort_field = isset($_GET['sort']) ? $_GET['sort'] : 'time';
  $current_order = isset($_GET['order']) && $_GET['order'] == 'ASC' ? 'ASC' : 'DESC';

  // Determine the sort icon for each column
  $sort_icon = $current_order === 'ASC' ? 'dashicons-arrow-up-alt2' : 'dashicons-arrow-down-alt2';

  echo '<style>
        .dashicons-arrow-up-alt2:before {
            content: "\f142";
        }
        .dashicons-arrow-down-alt2:before {
            content: "\f140";
        }
    </style>';

  $items_per_page = 50;
  $current_page = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
  $offset = ($current_page - 1) * $items_per_page;

  // Update links for sorting
  $username_link = '?page=openai-gpt-qa&sort=user_id&order=' . ($sort_field === 'user_id' ? ($current_order === 'ASC' ? 'DESC' : 'ASC') : 'ASC');
  $question_link = '?page=openai-gpt-qa&sort=question&order=' . ($sort_field === 'question' ? ($current_order === 'ASC' ? 'DESC' : 'ASC') : 'ASC');
  $response_link = '?page=openai-gpt-qa&sort=response&order=' . ($sort_field === 'response' ? ($current_order === 'ASC' ? 'DESC' : 'ASC') : 'ASC');
  $timestamp_link = '?page=openai-gpt-qa&sort=time&order=' . ($sort_field === 'time' ? ($current_order === 'ASC' ? 'DESC' : 'ASC') : 'ASC');

  // Query modification based on sort parameters
  $sort_order = isset($_GET['order']) ? $_GET['order'] : 'DESC';
  $results = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name ORDER BY $sort_field $sort_order LIMIT %d OFFSET %d", $items_per_page, $offset));

  $total_items = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
  $total_pages = ceil($total_items / $items_per_page);

  // Display pagination
  $pagination_args = array(
    'base' => add_query_arg('paged', '%#%'),
    'format' => '',
    'prev_text' => __('&laquo;'),
    'next_text' => __('&raquo;'),
    'total' => $total_pages,
    'current' => $current_page
  );
  echo '<div class="tablenav"><div class="tablenav-pages">';
  $pagination_links = paginate_links($pagination_args);
  $pagination_links = str_replace("page-numbers", "page-numbers button", $pagination_links);
  echo $pagination_links;

  echo '</div></div>';

  echo '<div class="wrap">';
  echo '<h1>Questions and Answers History</h1>';
  echo '<table class="widefat fixed" cellspacing="0">';

  echo '<thead><tr>';
  echo '<th><a class="heading-item username-heading" href="' . $username_link . '">Username <span class="dashicons ' . ($sort_field === 'user_id' ? $sort_icon : '') . '"></span></a></th>';
  echo '<th><a href="' . $question_link . '">Question <span class="dashicons ' . ($sort_field === 'question' ? $sort_icon : '') . '"></span></a></th>';
  echo '<th><a href="' . $response_link . '">Response <span class="dashicons ' . ($sort_field === 'response' ? $sort_icon : '') . '"></span></a></th>';
  echo '<th><a href="' . $timestamp_link . '">Timestamp <span class="dashicons ' . ($sort_field === 'time' ? $sort_icon : '') . '"></span></a></th>';
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
