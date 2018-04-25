<?php
/**
 * Created by PhpStorm.
 * User: info
 * Date: 1/28/2018
 * Time: 8:52 PM
 */

defined( 'ABSPATH' ) or die( "Cannot access pages directly." ); //protect from direct access


// Register the menu in WP admin
add_action( "admin_menu", "treestone_plugin_menu_func" );
function treestone_plugin_menu_func() {
    //echo "<script>console.log('creating the menu.');</script>"; 
	add_submenu_page( "options-general.php",  // Which menu parent
		"Magento",            // Page title
		"Magento",            // Menu title
		"manage_options",       // Minimum capability (manage_options is an easy way to target administrators)
		"magento",            // Menu slug
		"treestone_plugin_options"     // Callback that prints the markup
	);
}

// Print the markup for the admin page
function treestone_plugin_options() {
	if ( ! current_user_can( "manage_options" ) ) {
		wp_die( __( "You do not have sufficient permissions to access this page." ) );
	}

	//check if SOAP enabled
	if ( extension_loaded( 'soap' ) ) { ?>

        <div id="message" class="updated notice is-dismissible">
            <p><?php _e( "Soap is loaded on your server, good to go!" ); ?></p>
            <button type="button" class="notice-dismiss">
                <span class="screen-reader-text"><?php _e( "Dismiss this notice.", "magento-api" ); ?></span>
            </button>
        </div>
		<?php
	} else { ?>
        <div id="message" class="updated error notice is-dismissible">
            <p><?php _e( "Soap is not loaded on your server, please contact your system administrator!" ); ?></p>
            <button type="button" class="notice-dismiss">
                <span class="screen-reader-text"><?php _e( "Dismiss this notice.", "magento-api" ); ?></span>
            </button>
        </div>

		<?php
	}

	//show a success message after settings were saved
	if ( isset( $_GET['status'] ) && $_GET['status'] == 'success' ) {
		?>
        <div id="message" class="updated notice is-dismissible">
            <p><?php _e( "Settings updated!", "magento-api" ); ?></p>
            <button type="button" class="notice-dismiss">
                <span class="screen-reader-text"><?php _e( "Dismiss this notice.", "magento-api" ); ?></span>
            </button>
        </div>
		<?php
	} elseif ( isset( $_GET['status'] ) && $_GET['status'] == 'error' ) {
		?>
        <div id="message" class="updated  error notice is-dismissible">
            <p><?php _e( "Couldn't connect to " . get_option( 'mg_url' ) . " Message was: " . $_GET['error_message'], "magento-api" ); ?></p>
            <button type="button" class="notice-dismiss">
                <span class="screen-reader-text"><?php _e( "Dismiss this notice.", "magento-api" ); ?></span>
            </button>
        </div>

		<?php
	}


	//build the form with the elements
	//default WP classes were used for ease
	?>
    <form method="post" action="<?php echo admin_url( 'admin-post.php' ); ?>">

        <input type="hidden" name="action" value="update_magento_settings"/>

        <h3><?php _e( "Magento Info", "magento-api" ); ?></h3>
        <p>
            <label><?php _e( "Magento URL:", "magento-api" ); ?></label>
            <input class="regular-text" type="text" name="mg_url" value="<?php echo get_option( 'mg_url' ); ?>"/>
        </p>
        <p>
            <label><?php _e( "Magento API User:", "magento-api" ); ?></label>
            <input class="regular-text" type="text" name="mg_api_user"
                   value="<?php echo get_option( 'mg_api_user' ); ?>"/>
        </p>
        <p>
            <label><?php _e( "Magento Secret:", "magento-api" ); ?></label>
            <input class="regular-text" type="password" name="mg_scrt" value="<?php echo get_option( 'mg_scrt' ); ?>"/>
        </p>

        <input class="button button-primary" type="submit" value="<?php _e( "Save", "magento-api" ); ?>"/>

    </form>
	<?php
}


//save the admin settings


add_action( 'admin_post_update_magento_settings', 'magento_handle_save' );


function magento_handle_save() {

	// Get the options that were sent
	$url     = ( ! empty( $_POST["mg_url"] ) ) ? $_POST["mg_url"] : null;
	$apiuser = ( ! empty( $_POST["mg_api_user"] ) ) ? $_POST["mg_api_user"] : null;
	$secret  = ( ! empty( $_POST["mg_scrt"] ) ) ? $_POST["mg_scrt"] : null;

	// Validation would go here
	//TODO add validation

	// Update the values
	update_option( "mg_url", $url, true );
	update_option( "mg_api_user", $apiuser, true );
	update_option( "mg_scrt", $secret, true );

	//try connecting to the API

	try {
		$mg = new magento();
		$mg->connect();
	} catch ( SoapFault $fault ) {
		trigger_error( "SOAP Fault: (faultcode: {$fault->faultcode}, faultstring: {$fault->faultstring}),E_USER_ERROR" );

		// Redirect back to settings page
		// The ?page=magento corresponds to the "slug"
		// set in the fourth parameter of add_submenu_page() above.
		$redirect_url = get_bloginfo( "url" ) . "/wp-admin/options-general.php?page=magento&status=error&error_message=" . $fault->faultstring;
		header( "Location: " . $redirect_url );
		exit;
	}

	// Redirect back to settings page
	// The ?page=magento corresponds to the "slug"
	// set in the fourth parameter of add_submenu_page() above.
	$redirect_url = get_bloginfo( "url" ) . "/wp-admin/options-general.php?page=magento&status=success";
	header( "Location: " . $redirect_url );
	exit;

}

?>