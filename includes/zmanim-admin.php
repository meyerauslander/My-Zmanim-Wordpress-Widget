<?php
/**
 * Author:      Meyer Auslander
 * Descrirtion: Create a menu in the settings section of the admin for the user to enter their username/ID and password/key.  This code was adpated from https://github.com/treestonemedia/WP-Mag-attributes/tree/master/admin/magento-admin.php 
 */

defined( 'ABSPATH' ) or die( "Cannot access pages directly." ); //protect from direct access


// Register the menu in WP admin
add_action( "admin_menu", "maus_zmanim_plugin_menu_func" );
function maus_zmanim_plugin_menu_func() {
	add_submenu_page( "options-general.php",  // Which menu parent
		"Zmanim API user information",            // Page title
		"My Zmanim login info",            // Menu title
		"manage_options",       // Minimum capability (manage_options is an easy way to target administrators)
		"zmanim",            // Menu slug
		"maus_zmanimAPI_plugin_options"     // Callback that prints the markup
	);
}

// Print the markup for the admin page
function maus_zmanimAPI_plugin_options() {
	if ( ! current_user_can( "manage_options" ) ) {
		wp_die( __( "You do not have sufficient permissions to access this page." ) );
	}

    //retreive the current validation status
    $status = get_option('zman_status');
    $url = get_option('zman_url');
    $url = (empty($url)) ? "https://api.myzmanim.com/engine1.svc?wsdl" : $url; 
    //Check for proper server settings
    if (!(isset($_GET['status']))){ //only display this initially
        //check if SOAP enabled
        if ( extension_loaded( 'soap' ) ) { ?>
            <div id="message" class="updated notice is-dismissible">
                <p><?php _e( "Soap is loaded on your server, good to go!" ); ?></p>
                <button type="button" class="notice-dismiss">
                    <span class="screen-reader-text"><?php _e( "Dismiss this notice.", "zmanim-api" ); ?></span>
                </button>
            </div>
            <?php
        } else { ?>
            <div id="message" class="updated error notice is-dismissible">
                <p><?php _e( "Soap is not loaded on your server, please contact your system administrator!" ); ?></p>
                <button type="button" class="notice-dismiss">
                    <span class="screen-reader-text"><?php _e( "Dismiss this notice.", "zmanim-api" ); ?></span>
                </button>
            </div>

            <?php
        }
        //check if openssl enabled
        if ( extension_loaded( 'openssl' ) ) { ?>
            <div id="message" class="updated notice is-dismissible">
                <p><?php _e( "Openssl is loaded on your server, good to go!" ); ?></p>
                <button type="button" class="notice-dismiss">
                    <span class="screen-reader-text"><?php _e( "Dismiss this notice.", "zmanim-api" ); ?></span>
                </button>
            </div>
            <?php
        } else { ?>
            <div id="message" class="updated error notice is-dismissible">
                <p><?php _e( "Openssl is not loaded on your server, please contact your system administrator!" ); ?></p>
                <button type="button" class="notice-dismiss">
                    <span class="screen-reader-text"><?php _e( "Dismiss this notice.", "zmanim-api" ); ?></span>
                </button>
            </div>

            <?php
        }
    } else{ //Show the resluts of vailidation of user/key
	//show a success message after settings were saved
        if (  $_GET['status'] == 'success' ) {
            ?>
            <div id="message" class="updated notice is-dismissible">
                <p><?php _e( "Settings updated!", "zmanim-api" ); ?></p>
                <button type="button" class="notice-dismiss">
                    <span class="screen-reader-text"><?php _e( "Dismiss this notice.", "zmanim-api" ); ?></span>
                </button>
            </div>
            <?php
        } elseif ( $_GET['status'] == 'error' ) {
            ?>
            <div id="message" class="updated  error notice is-dismissible">
                <p><?php _e( "Couldn't connect to " . get_option( 'zman_url' ) . " Message was: " . $_GET['error_message'], "zmanim-api" ); ?></p>
                <button type="button" class="notice-dismiss">
                    <span class="screen-reader-text"><?php _e( "Dismiss this notice.", "zmanim-api" ); ?></span>
                </button>
            </div>
            <?php
        }
    }

    
	//build the form with the elements
	//default WP classes were used for ease
	?>
    <form method="post" action="<?php echo admin_url( 'admin-post.php' ); ?>">

        <input type="hidden" name="action" value="update_zmanim_settings"/>

        <h3><?php _e( "My Zmanim Info", "zmanim-api" ); ?></h3>
        <p>
            <label><?php _e( "Zmanim API URL (endpoint):", "zmanim-api" ); ?></label>
            <input class="regular-text" type="text" name="zman_url" value="<?php echo $url; ?>"/>
        </p>
        <p>
            <label><?php _e( "Zmanim API User:", "zmanim-api" ); ?></label>
            <input class="regular-text" type="text" name="zman_api_user"
                   value="<?php echo get_option( 'zman_api_user' ); ?>"/>
        </p>
        <p>
            <label><?php _e( "Zmanim Passwod:", "zmanim-api" ); ?></label>
            <input class="regular-text" type="password" name="zman_password" value="<?php echo get_option( 'zman_password' ); ?>"/>
        </p>
        <p>
            <?php _e( "Current Status: ", "zmanim-api" );
                $status = ($status=='') ? "Not yet Validated" : $status;    
                _e("$status");
            ?>
        </p>
        
        <input class="button button-primary" type="submit" value="<?php _e( "Save", "zmanim-api" ); ?>"/>

    </form>
	<?php
}


//save the admin settings
add_action( 'admin_post_update_zmanim_settings', 'maus_zmanim_handle_save' );


function maus_zmanim_handle_save() {

	// Get the options that were sent
	$url     = ( ! empty( $_POST["zman_url"] ) ) ? $_POST["zman_url"] : null;
	$apiuser = ( ! empty( $_POST["zman_api_user"] ) ) ? $_POST["zman_api_user"] : null;
	$key  = ( ! empty( $_POST["zman_password"] ) ) ? $_POST["zman_password"] : null;

	// Update the values
	update_option( "zman_url", $url, true );
	update_option( "zman_api_user", $apiuser, true );
	update_option( "zman_password", $key, true );

	//try connecting to the API
	try {
        $zm = new maus_MyZmanim_API($apiuser,$key,$url);
		$result=$zm->validateUser();
	} catch ( SoapFault $fault ) { //login failed because of a connection problem
        //Redirect back to settings page
        // The ?page=zmanim corresponds to the "slug"
	    // set in the fourth parameter of add_submenu_page() above.
        update_option('zman_status',"Invalid",true);
		$redirect_url = get_bloginfo( "url" ) . "/wp-admin/options-general.php?page=zmanim&status=error&error_message=" . $fault->faultstring;
		header( "Location: " . $redirect_url );
		exit;
	}
    
    if ($result!=null){ //login failed because of a user/key problem
        //echo "<script>alert('$result');</script>";
        //Redirect back to settings page
        update_option('zman_status',"Invalid",true);
        $redirect_url = get_bloginfo( "url" ) . "/wp-admin/options-general.php?page=zmanim&status=error&error_message=" . $result;
		header( "Location: " . $redirect_url );
		exit;
    }else{ //login success 
        update_option('zman_status',"Validated",true);
        // Redirect back to settings page
        $redirect_url = get_bloginfo( "url" ) . "/wp-admin/options-general.php?page=zmanim&status=success";
        header( "Location: " . $redirect_url );
        exit;
    }
}

?>