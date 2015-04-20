<?php
/**
 * Plugin Name: Post Status Dashboard
 * Plugin URI: http://www.fuzzguard.com.au/plugins/post-status-dash
 * Description: Used to display post status in the admin dashboard
 * Version: 1.0
 * Author: Benjamin Guy
 * Author URI: http://www.fuzzguard.com.au
 * Text Domain: post-status-dash
 * License: GPL2

    Copyright 2014  Benjamin Guy  (email: beng@fuzzguard.com.au)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA

*/


/**
* Don't display if wordpress admin class is not found
* Protects code if wordpress breaks
* @since 0.1
*/
if ( ! function_exists( 'is_admin' ) ) {
    header( 'Status: 403 Forbidden' );
    header( 'HTTP/1.1 403 Forbidden' );
    exit();
}



class postStatusDash {

        /**
        * Loads localization files for each language
        * @since 1.4
        */
        function _action_init()
        {
                // Localization
                load_plugin_textdomain('post-status-dash', false, 'post-status-dash/lang/');
        }


function post_status_dashboard_widgets() {
        global $wp_meta_boxes;
        wp_add_dashboard_widget('post_status_dashboard', 'Post Status Dashboard', array( $this, 'post_status_dashboard_content' ), array( $this, 'post_status_dashboard_handle' ));
}

function post_status_dashboard_content() {
   # get saved data
    if( !$post_status_dashboard = get_option( 'post_status_dashboard' ) )
        $post_status_dashboard = array('category' => 0, 'status' => 0);


    # default output
    $output = sprintf(
        '<h2 style="text-align:right">%s</h2>',
        __( 'Please, configure the widget ?' )
    );


$posts_array = get_posts(array(
        'posts_per_page'   => 5,
        'category'         => $post_status_dashboard['category'],
        'orderby'          => 'post_date',
        'order'            => 'DESC',
        'post_type'        => 'post',
        'post_status'      => $post_status_dashboard['status']
));

    # check if saved data contains content
    $saved_category = isset( $post_status_dashboard['category'] ) 
        ? $post_status_dashboard['category'] : false;
                $today    = date( 'Y-m-d', current_time( 'timestamp' ) );
                $tomorrow = date( 'Y-m-d', strtotime( '+1 day', current_time( 'timestamp' ) ) );


    # custom content saved by control callback, modify output
    if( $saved_category && !empty($posts_array) ) {
                $output = '<div class="activity-block"><ul>';
                foreach ( $posts_array as $post ) {

                        $time = strtotime($post->post_date);
                        if ( date( 'Y-m-d', $time ) == $today ) {
                                $relative = __( 'Today' );
                        } elseif ( date( 'Y-m-d', $time ) == $tomorrow ) {
                                $relative = __( 'Tomorrow' );
                        } else {
                                /* translators: date and time format for recent posts on the dashboard, see http://php.net/date */
                                $relative = date_i18n( __( 'M jS' ), $time );
                        }
                        $relative .= ", ".date( 'g:i a' );
                        $output .= '
                        <li>
                                <span style="margin-right: 11px;">'.$relative.'</span>
                                <span class="dashicons dashicons-admin-post"></span>
                                <a href="'.get_home_url().'/?p='.$post->ID.'">'.$post->post_title.'</a>
                                 - <a href="'.get_admin_url().'post.php?post='.$post->ID.'&amp;action=edit">edit</a>
                        </li>';
                }
                $output .= '</ul></div>';
    } else if (empty($posts_array)) {
                    $output = sprintf(
        '<h2 style="text-align:right">%s</h2>',
        __( 'No posts to load' )
    );
        }
    echo "<div class='feature_post_class_wrap'>
        <div class='feature_post_class_wrap'><label><strong>".__('Category', 'post-status-dash' ).":</strong> ".get_cat_name($post_status_dashboard['category'])."</label></div>
        <div class='feature_post_class_wrap'><label><strong>".__('Status', 'post-status-dash' ).":</strong> ".$post_status_dashboard['status']."</label></div>
        <label style='background:#ccc;'>$output</label>
    </div>
    ";

#        echo '<p>Welcome to Custom Blog Theme! Need help? Contact the developer <a href="mailto:yourusername@gmail.com">here</a>. For WordPress Tutorials visit: <a href="http://www.wpbeginner.com" target="_blank">WPBeginner</a></p>';
}

function post_status_dashboard_handle()
{
    # get saved data
    if( !$post_status_dashboard = get_option( 'post_status_dashboard' ) )
        $post_status_dashboard = array('category' => 0, 'status' => 0);



    # process update
    if( 'POST' == $_SERVER['REQUEST_METHOD'] && isset( $_POST['post_status_dashboard'] ) ) {
        # minor validation
        $post_status_dashboard['category'] = absint( $_POST['post_status_dashboard']['category'] );
        $post_status_dashboard['status'] = sanitize_text_field( $_POST['post_status_dashboard']['status'] );
        # save update
        update_option( 'post_status_dashboard', $post_status_dashboard );
    }

    # set defaults  
    if( !isset( $post_status_dashboard['category'] ) )
        $post_status_dashboard['category'] = '';

    echo "<p><strong>".__('Select a Post Category and Status', 'post-status-dash' )."</strong></p>
    <div class='feature_post_class_wrap'>
        <label>".__('Category', 'post-status-dash' ).":</label>";
    wp_dropdown_categories( array(
        'orderby'            => 'name',
        'order'              => 'ASC',
        'selected'         => $post_status_dashboard['category'],
        'name'             => 'post_status_dashboard[category]',
        'taxonomy'           => 'category'
    ) );
    echo "</div>
    <div class='feature_post_class_wrap'>
        <label>".__('Status', 'post-status-dash' ).":</label>
<select class='postform' id='post_status_dashboard[status]' name='post_status_dashboard[status]'>";

?>
    <option <?php if ($post_status_dashboard['status']=='publish') { echo "selected='selected'"; } ?> value='publish' class='level-0'>Publish</option>
    <option <?php if ($post_status_dashboard['status']=='pending') { echo "selected='selected'"; } ?>  value='pending' class='level-0'>Pending</option>
    <option <?php if ($post_status_dashboard['status']=='draft') { echo "selected='selected'"; } ?> value='draft' class='level-0'>Draft</option>
    <option <?php if ($post_status_dashboard['status']=='auto-draft') { echo "selected='selected'"; } ?>  value='auto-draft' class='level-0'>Auto-Draft</option>
    <option <?php if ($post_status_dashboard['status']=='future') { echo "selected='selected'"; } ?>  value='future' class='level-0'>Future</option>
    <option <?php if ($post_status_dashboard['status']=='private') { echo "selected='selected'"; } ?>  value='private' class='level-0'>Private</option>
    <option <?php if ($post_status_dashboard['status']=='inherit') { echo "selected='selected'"; } ?>  value='inherit' class='level-0'>Inherit</option>
    <option <?php if ($post_status_dashboard['status']=='trash') { echo "selected='selected'"; } ?>  value='trash' class='level-0'>Trash</option>
    <option <?php if ($post_status_dashboard['status']=='any') { echo "selected='selected'"; } ?>  value='any' class='level-0'>Any</option>
<?php
echo "</select></div>";
}

}

/**
* Define the Class
* @since 0.1
*/
$mypostStatusDashboard = new postStatusDash();

/**
* Action of what function to call on wordpress initialization
* @since 0.1
*/
add_action('plugins_loaded', array($mypostStatusDashboard, '_action_init'));


/**
* Action of what function to call to display Dashboard Widget
* Register the new dashboard widget with the 'wp_dashboard_setup' action
* @since 0.1
*/
add_action('wp_dashboard_setup', array( $mypostStatusDashboard, 'post_status_dashboard_widgets' ));
?>