<?php

function openai_gpt_admin_styles()
{
?>
  <style type="text/css">
    /* Target the outer container of the WYSIWYG editor */
    .wp-editor-wrap {
      width: 75% !important;
    }
  </style>
<?php
}
add_action('admin_head', 'openai_gpt_admin_styles');
