<?php

/**
 * markup of the plugin admin option tab for extra css tab content
 */

defined('ABSPATH') || exit;

?>

<textarea class="lukio_user_forms_textarea" name="extra_css" id="extra_css" cols="30" rows="10" placeholder=".lukio_user_forms_form {&#10;&#x09;color: #4aa896;&#10;}"><?php echo $active_options['extra_css']; ?></textarea>