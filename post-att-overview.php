<?php
/*
Plugin Name: Post Attachments Overview
Plugin URI: https:/www.robertandrews.co.uk
Description: Show and control which images are attached to posts, from the Post edit screen.
Version: 1.0
Author: Robert Andrews
Author URI: https:/www.robertandrews.co.uk
 */


add_action('add_meta_boxes', function () {
    add_meta_box('att_thumb_display', 'Attached Images', function ($post) {
        $args = array(
            'post_type' => 'attachment',
            'post_mime_type' => 'image',
            'post_parent' => $post->ID,
            'posts_per_page' => -1,
        );

        echo '<div style="display:flex;flex-wrap:wrap;">';
        foreach (get_posts($args) as $image) {
            echo '<div style="position:relative;margin:5px;">';
            echo '<a href="' . get_edit_post_link($image->ID) . '" target="_blank"><img src="' . wp_get_attachment_thumb_url($image->ID) . '" /></a>';
            echo '<a href="' . wp_nonce_url(admin_url('admin-ajax.php?action=unlink_image&image_id=' . $image->ID . '&post_id=' . $post->ID . '&delete=1'), 'unlink_image_' . $image->ID) . '" style="position:absolute;top:5px;right:5px;"><i class="dashicons dashicons-trash"></i></a>';
            // echo '<a href="' . get_edit_post_link($image->ID) . '" style="position:absolute;bottom:5px;right:5px;"><i class="dashicons dashicons-admin-media"></i></a>';
            echo '<a href="' . wp_nonce_url(admin_url('admin-ajax.php?action=unlink_image&image_id=' . $image->ID . '&post_id=' . $post->ID), 'unlink_image_' . $image->ID) . '" style="position:absolute;top:5px;left:5px;"><i class="dashicons dashicons-editor-unlink"></i></a>';
            echo '</div>';
        }
        echo '</div>';
                                                            
        echo '<p><a href="' . admin_url('upload.php') . '?attachment&amp;post_parent=' . $post->ID . '" target="_blank"><strong>View in Media Library</strong></a></p>';

    }, 'post');
});

// Handle the unlink image action
add_action('wp_ajax_unlink_image', function () {
    if (!isset($_GET['image_id']) || !isset($_GET['post_id']) || !wp_verify_nonce($_GET['_wpnonce'], 'unlink_image_' . $_GET['image_id'])) {
        wp_send_json_error();
    }

    $image_id = intval($_GET['image_id']);
    $post_id = intval($_GET['post_id']);

    // Unattach the image from the post
    wp_update_post(array(
        'ID' => $image_id,
        'post_parent' => 0,
    ));

    if (isset($_GET['delete'])) {
        // Delete the image
        wp_delete_attachment($image_id);
    }

    // Redirect the user back to the post edit page they came from
    wp_redirect(admin_url('post.php?post=' . $post_id . '&action=edit'));

    exit();
});

?>