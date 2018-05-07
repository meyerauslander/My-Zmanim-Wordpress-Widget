<?php
//static class that all Zmainim API objects inherit from
class maus_Zmanim_API{
    public $requested_zman_output_array, //Assosiative array 
                                         //should be set in the inheriting class.  
                                         //The assocciative names are be the api's names for the zmanim and the values
                                         //are the names to be displayed to the user.
            $time,$zman,$place,$default_zman_option; //arrays for zmanim data of the requested location 
    
    protected $user,$key,$endpoint; //to store the username and password and url of the zmanim api
    
    public function __construct($in_user, $in_key, $in_endpoint){ 
        //set user specific attributes
        $this->user=$in_user;
        $this->key=$in_key;
        $this->endpoint=$in_endpoint;
    }    
    //Make the api call to populate the zman, place, and time arrays
    public function getZmanim($zipcode=''){
        //Override this function in the inheriting class
    }
    
    //attempt to make a call to the Zmanim API 
    //usage: try{ call this function } catch{ actions upon error } 
    //returns any error message returned by the Zmanim API
    public function validateUser(){
        //Override this function in the inheriting class 
    }
    
    public function get_transients() {
         global $wpdb;

         $sql = "SELECT * FROM $wpdb->options WHERE option_name LIKE '%\_transient\_%' AND option_name NOT LIKE '%\_transient\_timeout%'
                                                AND option_name LIKE '%maus_zmanim_zipcode%'";
         $sql .= " ORDER BY option_id DESC;";
         $transients = $wpdb->get_results( $sql );
         return $transients;
	}
    
    public function get_transient_name( $transient ) {
		$length = false !== strpos( $transient->option_name, 'site_transient_' ) ? 16 : 30;
		return substr( $transient->option_name, $length, strlen( $transient->option_name ) );
	}
    
    //process requests sent for the My Zmanim Info logic screen
//    public function process_actions() {
//        
//		if( empty( $_REQUEST['action'] ) ) {
//			return;
//		}
//
//		if( empty( $_REQUEST['transient']) || !(is_numeric( $_REQUEST['transient'] )) {
//			return;
//		}
//
//		if( ! current_user_can( 'manage_options' ) ) {
//			return;
//		}
//           //verify this request was sent from the zmanim_admin
//		if( ! wp_verify_nonce( $_REQUEST['_wpnonce'] , 'zmanim_admin' ) ) {
//			return;
//		}
//		
//        $transient = $_REQUEST['transient'];
//		switch( $_REQUEST['action'] ) {
//			case 'delete_transient' :
//				delete_transient( $transient );
//				wp_safe_redirect( admin_url( 'tools.php?page=pw-transients-manager&s=' . $search ) ); exit;
//				break;
//
//			case 'update_transient' :
//				$this->update_transient( $transient, $site_wide );
//				wp_safe_redirect( admin_url( 'tools.php?page=pw-transients-manager&s=' . $search ) ); exit;
//				break;
//
//			case 'delete_expired_transients' :
//				$this->delete_expired_transients();
//				wp_safe_redirect( admin_url( 'tools.php?page=pw-transients-manager' ) ); exit;
//				break;
//
//			case 'delete_transients_with_expiration' :
//				$this->delete_transients_with_expirations();
//				wp_safe_redirect( admin_url( 'tools.php?page=pw-transients-manager' ) ); exit;
//				break;
//
//			case 'suspend_transients' :
//
//				update_option( 'pw_tm_suspend', 1 );
//				wp_safe_redirect( remove_query_arg( array( 'action', '_wpnonce' ) ) ); exit;
//				break;
//
//			case 'unsuspend_transients' :
//
//				delete_option( 'pw_tm_suspend', 1 );
//				wp_safe_redirect( remove_query_arg( array( 'action', '_wpnonce' ) ) ); exit;
//				break;
//
//			case 'delete_all_transients' :
//
//				$this->delete_all_transients();
//				wp_safe_redirect( admin_url( 'tools.php?page=pw-transients-manager' ) ); exit;
//				break;
//
//		}
//
//	}
//

    
} //end of zmainimAPI class