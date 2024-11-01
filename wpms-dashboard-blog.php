<?php
/*
Plugin Name: WPMS Dashboard Blog
Plugin URI: http://www.joshparker.us/blog/wordpress/wpms_dashboard_blog_plugin.html
Description: Redirects users without a blog to the /wp-admin page of a "dashboard" blog.
Author: Joshua Parker
Version: 1.0
Author URI: http://www.joshparker.us/
*/

add_action('wpmu_options', 'wpms_dashboard_blog_admin_options');
add_action('update_wpmu_options', 'wpms_dashboard_blog_options_process');
add_action('wpmu_new_blog', 'wpms_global_dashboard_blog', 1, 1);  

function wpms_global_dashboard_blog($blog_ID) {
	$dashboard_blog = get_site_option('dashboard_blog');
}

function wpms_dashboard_blog_options_process() {
	$dashboard_blog = $_POST['dashboard_blog'];
	update_site_option( 'dashboard_blog' , $dashboard_blog );
}

function set_wpms_dashboard_blog_admin_options() {
	add_site_option('dashboard_blog','','Dashboard Blog');
}

function unset_wpms_dashboard_blog_admin_options() {
	delete_site_option('dashboard_blog');
}

function wpms_dashboard_blog_admin_options() {
$dashboard_blog =  get_site_option('dashboard_blog');
?>
		<h3><?php _e( 'Dashboard Blog Settings' ); ?></h3>
    <table class="form-table">
		<tr valign="top">
				<th scope="row"><?php _e('Dashboard Blog') ?></th>
				<td>
					<?php
					if ( $dashboard_blog = get_site_option( 'dashboard_blog' ) ) {
						//$details = get_blog_details( $dashboard_blog );
						//$blogname = untrailingslashit( sanitize_user( str_replace( '.', '', str_replace( $current_site->domain . $current_site->path, '', $details->domain . $details->path ) ) ) );
						$blogname = get_site_option('dashboard_blog');
					} else {
						$blogname = '';
					}?>
					<input name="dashboard_blog_orig" type="hidden" id="dashboard_blog_orig" value="<?php echo $blogname; ?>" />
					<input name="dashboard_blog" type="text" id="dashboard_blog" value="<?php echo $blogname; ?>" size="30" />
					<br />
					<?php _e( "Blogname ('dashboard', 'control', 'manager', etc) or blog id.<br />New users are added to this blog as subscribers (or the user role defined below) if they don't have a blog. Leave blank for the main blog." ); ?>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><?php _e('Dashboard User Default Role') ?></th>
				<td>
					<select name="default_user_role" id="role"><?php
					wp_dropdown_roles( get_site_option( 'default_user_role', 'subscriber' ) );
					?>
					</select>
					<br />
					<?php _e( "The default role for new users on the Dashboard blog. This should probably be 'Subscriber' or maybe 'Contributor'." ); ?>
				</td>
			</tr>
	</table>
<?php
}

function add_new_user_to_dashboard_blog( $user_id ) {
  global $wpdb;
	$blogs = get_blogs_of_user( $user_id );
	if ( empty( $blogs ) ) {
			$dashboard_blog = get_dashboard_blog();
			add_user_to_blog( $dashboard_blog->blog_id, $user_id, get_site_option( 'default_user_role' ) ); // Add role permission for dashboard blog
			update_usermeta( $user_id, 'primary_blog', $dashboard_blog->blog_id );
			return $dashboard_blog;
		}
}
add_action ( 'wpmu_activate_user', 'add_new_user_to_dashboard_blog', 10, 3 );

/*
function wpms_dashboard_blog_admin() {
global $wpdb;
	if(get_site_option('dashboard_blog') != '') {
		if(get_option('siteurl') == 'http://' . get_site_option('dashboard_blog')) {
		$protocol = ( is_ssl() ? 'https://' : 'http://' );
		return header('Location: ' . $protocol . get_site_option('dashboard_blog') . '/wp-admin/');
		die();
		}
	}
}

add_action('template_redirect', 'wpms_dashboard_blog_admin');
*/


/* 
get_wpms_dashboard_blog is a customized function in case the get_dashboard_blog 
function gets deleted from future versions of WordPress.
*/

/*
function get_wpms_dashboard_blog() {
	global $current_site;

	if ( get_site_option( 'wpms_dashboard_blogname' ) == false ) {
		return get_blog_details( $current_site->blog_id );
	} else {
		return get_blog_details( get_site_option( 'wpms_dashboard_blogname' ) );
	}
}
*/

/* Redirect all hits to "dashboard" blog to wp-admin/ Dashboard. */
function redirect_wpms_dashboard() {
	global $current_site, $current_blog;

	$dashboard_blog = get_dashboard_blog();
	if ( $current_blog->blog_id == $dashboard_blog->blog_id && $dashboard_blog->blog_id != $current_site->blog_id ) { 
		$protocol = ( is_ssl() ? 'https://' : 'http://' ); 
		wp_redirect( $protocol . $dashboard_blog->domain . trailingslashit( $dashboard_blog->path ) . 'wp-admin/' );
		die();
	}
}
add_action( 'template_redirect', 'redirect_wpms_dashboard' );
?>