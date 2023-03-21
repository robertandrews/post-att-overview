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
            $thumb_url = wp_get_attachment_image_src($image->ID, 'thumbnail', true)[0];
            $smallest_size_url = wp_get_attachment_image_src($image->ID, 'smallest', true)[0];

            echo '<div style="position:relative;margin:5px;width:150px;height:150px;overflow:hidden;">';
            echo '<a href="' . get_edit_post_link($image->ID) . '" target="_blank"><img src="' . esc_url($thumb_url) . '" style="object-fit: cover; width: 100%; height: 100%;"/></a>';
            echo '<a href="' . wp_nonce_url(admin_url('admin-ajax.php?action=unlink_image&image_id=' . $image->ID . '&post_id=' . $post->ID . '&delete=1'), 'unlink_image_' . $image->ID) . '" style="position:absolute;top:5px;right:5px;"><span class="dashicons dashicons-circle"><i class="dashicons dashicons-trash"></i></span></a>';
            // echo '<a href="' . get_edit_post_link($image->ID) . '" style="position:absolute;bottom:5px;right:5px;"><i class="dashicons dashicons-admin-media"></i></a>';
            echo '<a href="' . wp_nonce_url(admin_url('admin-ajax.php?action=unlink_image&image_id=' . $image->ID . '&post_id=' . $post->ID), 'unlink_image_' . $image->ID) . '" style="position:absolute;top:5px;left:5px;"><span class="dashicons dashicons-circle"><i class="dashicons dashicons-editor-unlink"></i></span></a>';
            echo '</div>';
        }
        echo '</div>';

        echo '<p><a href="' . admin_url('upload.php') . '?attachment&amp;post_parent=' . $post->ID . '" target="_blank"><strong>View in Media Library</strong></a></p>';

    }, 'post');
});

function custom_image_sizes()
{
    add_image_size('smallest', 150, 150, true);
}
add_action('after_setup_theme', 'custom_image_sizes');

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

function post_att_overview_enqueue_scripts()
{
    $screen = get_current_screen();
    if ($screen->id !== 'post' && $screen->id !== 'edit-post') {
        return;
    }

    wp_enqueue_style('post-att-overview-styles', plugin_dir_url(__FILE__) . 'css/styles.css', array(), '1.0.0');
}
add_action('admin_enqueue_scripts', 'post_att_overview_enqueue_scripts');
