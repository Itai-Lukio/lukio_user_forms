<?php

/**
 * The template for displaying the content rest password email
 *
 * This template can be overridden by copying it to yourtheme/lukio-user-forms/lost_email.php.
 */

defined('ABSPATH') || exit;

?>

<div>
        <span><?php
                /* TRANSLATORS: use wordpress defaul. does not need translation */
                echo __('Someone has requested a password reset for the following account:');
                ?></span>

        <p style="margin: 10px 8px;"><?php
                                        /* TRANSLATORS: use wordpress defaul. does not need translation */
                                        echo sprintf(__('Site Name: %s'), $site_name);
                                        ?></p>

        <p style="margin: 10px 8px;"><?php
                                        /* TRANSLATORS: use wordpress defaul. does not need translation */
                                        echo sprintf(__('Username: %s'), $user_login);
                                        ?></p>

        <p style="margin: 0 0 6px;"><?php
                                        /* TRANSLATORS: use wordpress defaul. does not need translation */
                                        echo __('If this was a mistake, ignore this email and nothing will happen.');
                                        ?></p>

        <span><?php
                /* TRANSLATORS: use wordpress defaul. does not need translation */
                echo __('To reset your password, visit the following address:');
                ?></span>
        <br>
        <?php
        echo $reset_link;
        ?>
</div>