<?php


/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Guest_User
 * @subpackage Guest_User/includes
 * @author     Nesho Sabakov <neshosab16@gmail.com>
 */
class Post_User {



	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {

		add_action( 'admin_menu', array( $this, 'post_user_register_menu_page' ) );

	}

	public function post_user_register_menu_page() {

		add_submenu_page( 'users.php', 'Migrate Post to User', 'Migrate P2U', 'manage_options', 'post_user', array( $this, 'post_user_menu_page' ) );

	}

	// Function to display the input
	public function post_user_menu_page() {

		echo '<div class="wrapper">';

			$this->post_user_start_import();
			echo '<h1>' . __( 'Import Users from Custom post type', 'post-to-user' ) . '</h1>';
			echo '<hr>';

			$args = array(
				'public'   => true,
				'_builtin' => false,
			);

			$output = 'names'; // names or objects, note names is the default

			$post_types = get_post_types( $args, $output );

			echo '<form method="POST" id="createuser" action="">';
			echo '<label for="chosen_post">Post type</label>';
			echo '<select name="chosen_post">';
		foreach ( $post_types  as $post_type ) {
			echo '<option value="' . $post_type . '">' . $post_type . '</option>';
		}
			echo '</select>';
			echo '<label for="role">User role</label>';
			echo '<select name="role" id="role">';
			wp_dropdown_roles( 'subscriber' );
			echo '</select>';
			echo '<input type="hidden" name="import" value="true"';
			echo '  <p class="submit"><input type="submit" class="button button-primary" value="Start Import"><span class="acf-spinner"></span></p>';

			echo '</form>';

			echo '</div>';

	}

	public function post_user_start_import() {

		if ( ! isset( $_POST['import'] ) || ! isset( $_POST['chosen_post'] ) || $_POST['chosen_post'] == '' ) {
			return;
		}

		// The Query
		$the_query = new WP_Query(
			array(
				'post_type'      => $_POST['chosen_post'],
				'posts_per_page' => -1,
			)
		);

		// The Loop
		if ( $the_query->have_posts() ) {
			while ( $the_query->have_posts() ) {
				$the_query->the_post();
				// Get post title
				$title = get_the_title();
				// Separate first name from last name
				$title = explode( ' ', $title );

				// In order to get the slug
				global $post;

				$social = get_field( 'social_networks' );
				// Gather user data
				$user_data = array(
					'username'    => $post->post_name,
					'first_name'  => $title[0],
					'last_name'   => $title[1],
					'description' => $post->post_content,
					'twitter'     => $social['twitter'], // need to loop through array
					'linkedin'    => $social['linkedin'], // need to loop through array
					'company'     => get_field( 'company' ),
					'job_title'   => get_field( 'job_title' ),
					'role'        => $_POST['role'],
				);

				// Create the user
				$this->post_user_add_user( $user_data );
			}
			// Restore original Post Data
			wp_reset_postdata();
			echo '<div class="updated"><p>' . __( 'User Imported', 'post-to-user' ) . '</p></div>';
		} else {
			echo '<div class="error"><p>' . __( 'No posts found', 'post-to-user' ) . '</p></div>';
		}
	}


	// Function to process the input
	public function post_user_add_user( $user_data ) {

		if ( ! $user_data ) {
			return;
		}

		// define the new user
		$args = array(
			'user_login'  => $user_data['username'],
			'first_name'  => $user_data['first_name'],
			'last_name'   => $user_data['last_name'],
			'twitter'     => $user_data['twitter'],
			'linkedin'    => $user_data['linkedin'],
			'description' => $user_data['description'],
			'user_pass'   => md5( rand( 1000000, 9999999 ) ), // create hash of randomized number as password
			'role'        => $user_data['role'],
		);

		// Define user meta fields
		$user_meta = array(
			'company'   => $user_data['company'],
			'job_title' => $user_data['job_title'],
		);

		// try to insert the user
		$user_id = wp_insert_user( $args );

		// check if everything went coorectly
		if ( ! is_wp_error( $user_id ) ) {
			// add a small notice
			foreach ( $user_meta as $key => $value ) {
				if ( $value && $value != '' ) {
					add_user_meta( $user_id, $key, $value );
				}
			}

			echo '<p>' . __( 'User created', 'post-to-user' ) . ': ' .$user_data['username'] . '</p>';
		} else {
			// show error message
			$errors = implode( ', ', $user_id->get_error_messages() );
			echo '<div class="error"><p>' . __( 'Error', 'post-to-user' ) . ': ' . $errors . '</p></div>';
		}

	}

}
