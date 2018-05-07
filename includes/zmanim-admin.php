<?php
/**
 * Author:      Meyer Auslander
 * Descrirtion: Create a menu in the settings section of the admin for the user to enter their username/ID and password/key.  This code was adpated from https://github.com/treestonemedia/WP-Mag-attributes/tree/master/admin/magento-admin.php 
 */

defined( 'ABSPATH' ) or die( "Cannot access pages directly." ); //protect from direct access

class maus_zmanim_info_manager {
    public function __construct() { 
        add_action( "admin_menu", array($this,"settings_link" )); // Register the menu in WP admin
        add_action( 'admin_post_update_login_settings', array($this,'save_settings' )); //save the admin settings
        add_action( 'admin_post_delete_transient', array($this,'delete_transient' ));
    }

    public function settings_link() {
        add_submenu_page( "options-general.php",  // Which menu parent
            "Zmanim login info and transients",            // Page title
            "Zmanim Information Manager",            // Menu title
            "manage_options",       // Minimum capability (manage_options is an easy way to target administrators)
            "maus-zmanim-manager",            // Menu slug
            array($this,"adminUI")     // Callback that prints the markup
        );
    }

    // Print the markup for the admin page
    public function adminUI() {
        if ( ! current_user_can( "manage_options" ) ) {
            wp_die( __( "You do not have sufficient permissions to access this page." ) );
        }

        //retreive the current validation status
        $status = get_option('zman_status');
        $url = get_option('zman_url');
        $url = (empty($url)) ? "https://api.myzmanim.com/engine1.svc?wsdl" : $url; 
        //Check for proper server settings
        if ( !(isset($_GET['status'])) && $status != 'Validated'){ //only display this initially
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
        } else{ //Show the resluts of the users request:  vailidation or transient deletion
        //show a success message after settings were saved
            if (  $_GET['status'] == 'success' && $_GET['request']=='validate') {
                ?>
                <div id="message" class="updated notice is-dismissible">
                    <p><?php _e( "Login information validated!", "zmanim-api" ); ?></p>
                    <button type="button" class="notice-dismiss">
                        <span class="screen-reader-text"><?php _e( "Dismiss this notice.", "zmanim-api" ); ?></span>
                    </button>
                </div>
                <?php
            } elseif ( $_GET['status'] == 'error' && $_GET['request']=='validate' ) {
                ?>
                <div id="message" class="updated  error notice is-dismissible">
                    <p><?php _e( "Couldn't connect to " . get_option( 'zman_url' ) . " Message was: " . $_GET['error_message'], "zmanim-api" ); ?></p>
                    <button type="button" class="notice-dismiss">
                        <span class="screen-reader-text"><?php _e( "Dismiss this notice.", "zmanim-api" ); ?></span>
                    </button>
                </div>
                <?php
            }  elseif ( $_GET['status'] == 'success' && $_GET['request']=='delete' ){
                ?>
                <div id="message" class="updated notice is-dismissible">
                    <p><?php _e( "Deleted the transient for " . $_GET['transient'], "zmanim-api" ); ?></p>
                    <button type="button" class="notice-dismiss">
                        <span class="screen-reader-text"><?php _e( "Dismiss this notice.", "zmanim-api" ); ?></span>
                    </button>
                </div>
                <?php
            }  elseif ( $_GET['status'] == 'error' && $_GET['request']=='delete' ){
                ?>
                <div id="message" class="updated error notice is-dismissible">
                    <p><?php _e( "Error: No transient was selected for deletion!", "zmanim-api" ); ?></p>
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

            <input type="hidden" name="action" value="update_login_settings"/>

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
        </form><br>

    <!--    Form for deleting transients-->
        <h3><?php _e( "Delete Cached Zmainim Information by zipcode", "zmanim-api" ); ?></h3>
        <form method="post" class="alignleft" action="<?php echo admin_url( 'admin-post.php' ); ?>">&nbsp;
            <input type="hidden" name="action" value="delete_transient" />
<!--            <input type="hidden" name="action" value="update_login_settings"/>-->

            <?php //wp_nonce_field('zmanim_admin'); ?>
            <?php
                $transients=maus_Zmanim_API::get_transients();
            if ( !(empty($transients)) ){ //display all avalable transients    
            ?>
                <select name="transient">
                    <option value=''>Select Transient</option>
                    <?php                          
                        foreach ($transients as $transient) {
                            $name=maus_Zmanim_API::get_transient_name($transient);
                            echo "<option value='$name'>$name</option>";
                        }  
                    ?>
                </select>
        <!--         <input class="button button-primary" type="submit" value="<?//php _e( "Save", "zmanim-api" ); ?>"/>-->
                <input type="submit" class="button secondary"  value="<?php _e( 'Delete Transient', 'zmanim-api' ); ?>" />    
            <?php
            } else{
                _e("There are currently no transients saved.", "zmanim-api");
            }
            ?>    
        </form>
        <?php
    } //end of adminUI function

    //seve the entered information and vailidate it through the My zmanim API
    public function save_settings() {
        //echo "Got in"; 
//        echo "<script>alert('Got in');</script>";
//        exit;
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
            // The ?page=maus-zmanim-manager corresponds to the "slug"
            // set in the fourth parameter of add_submenu_page() above.
            update_option('zman_status',"Invalid",true);
            $redirect_url = get_bloginfo( "url" ) . "/wp-admin/options-general.php?page=maus-zmanim-manager&request=validate&status=error&message=" . $fault->faultstring;
            header( "Location: " . $redirect_url );
            exit;
        }

        if ($result!=null){ //login failed because of a user/key problem
            //echo "<script>alert('$result');</script>";
            //Redirect back to settings page
            update_option('zman_status',"Invalid",true);
            echo "<script> alert('The result was {$result}');</script>"; 
            $redirect_url = get_bloginfo( "url" ) . "/wp-admin/options-general.php?page=maus-zmanim-manager&request=validate&status=error&message=" . $result;
            wp_safe_redirect(  $redirect_url );
            exit;
        }else{ //login success 
            update_option('zman_status',"Validated",true);
            // Redirect back to settings page
            $redirect_url = get_bloginfo( "url" ) . "/wp-admin/options-general.php?page=maus-zmanim-manager&request=validate&status=success";
            header( "Location: " . $redirect_url );
            exit;
        }
    } //end of function save settings

    public function delete_transient(){
        if ($_POST["transient"] != ''){ 
            $selected_transient = $_POST["transient"];
            delete_transient( "maus_zmanim_zipcode$selected_transient" );
            wp_safe_redirect( admin_url( "options-general.php?page=maus-zmanim-manager&request=delete&transient=$selected_transient&status=success" ) );
        }
        else{ //they chose to delete w/o selecting a transient
            wp_safe_redirect( admin_url( "options-general.php?page=maus-zmanim-manager&request=delete&status=error" ) );
        }
        exit;
    }
} //end of maus_zmanim_info_manager
$zm = new maus_zmanim_info_manager;
unset( $zm );

?>