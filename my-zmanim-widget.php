<?php
/**
 * Plugin Name:   My Zmanim Widget
 * Plugin URI:    none
 * Description:   Adds a widget option in the admin area.  When added, allows the admin to enter comments that will show up on the user page.  Each comment includes a zman from drop down list of zmanim.  Also includes a WordPress "shortcode" function for displaying a requested zman.    
 * Version:       1.0
 * Author:        Meyer Auslander
 * Author URI:    none
 * License:       GPL2
 */

//define("MAUS_DEBUG",true);
define("MAUS_DEBUG",false);

//include the file that defines the maus_Zmanim_API class
$dir = plugin_dir_path( __FILE__ );
$include_path = "$dir/includes/";
include "$include_path" . "zmanimAPI.php"; //base zmanim api class definition
include "$include_path" . "myZmanimAPI.php";  //"My Zmanim" api class
include "$include_path" . "inputFormWidget.php"; //base class for widgets that accept and display information entered by the admin
include "$include_path" . "zmanim-admin.php";    //page in admin for "Zmanim Information Manager"
include "$include_path" . "display_zman.php";    //shortcode function

//globlal constatants 
define('MAUS_DEFAULT_ZMAN_OPTION','Sunrise');  

//Adds the functionality of connecting to a zmanim API and display zmanim-based comments
class maus_Zmanim_Widget extends maus_InputForm_Widget{  
    public $default_zman_option_code;
    
    public function __construct() {
        $zmanapi=new maus_MyZmanim_API('','','',''); //create an instance to access the access the zmanim names array
        $this->default_zman_option=MAUS_DEFAULT_ZMAN_OPTION; //needed in case the zman_option index is empty when the output is created
        $this->default_zman_option_code=array_search("$this->default_zman_option",$zmanapi->requested_zman_output_array); //the myZmanim name of it 
        $widget_options = array( 'classname' => 'maus_Zmanim_widget', 'description' => 'Enter comments containing today\'s zmanim.  Each comment includes a zman from drop down list of zmanim.' );
        parent::__construct( 'zmanim_widget', 'Zmanim Widget', $widget_options );
    }
    
    // Create the admin area widget settings form.
    // form() function
    public function form( $instance ) { 
        $zmanapi=new maus_MyZmanim_API('','','',''); //create an instance to access the access the zmanim names array
        $total_comments = !empty( $instance['total_comments'] ) ? $instance['total_comments'] : 1;
        $current_comment = !empty( $instance['current_comment'] ) ? $instance['current_comment'] : 1;
        
        //some defaults should only be set no value has been saved yet
        if (!array_key_exists('title', $instance)) {
            $title = 'Title text';
            $zipcode = '44092';    
        } else{
            $title = $instance['title'];
            $zipcode = $instance['zipcode'];    
        }
        
        if(isset($_GET['action']) && $_GET['id'] == $this->id) { //ignore the action if the request was sent from a differnt widget
            $action = $_GET['action'];
            switch($action) {
                case 'add';
                    echo "You clicked add comment";
                    if (isset($_GET[ 'current_comment' ]))
                        $current_comment = $total_comments = $_GET[ 'current_comment' ];
                    else //this should never execute unless someone manually sets the url (improperly)
                        $current_comment=$total_comments;
                break; 

                case 'previous';
                    echo "You clicked previous comment";
                    if (isset($_GET[ 'current_comment' ]))
                        $current_comment = $_GET[ 'current_comment' ];
                    else //this should never execute unless someone manually sets the url (improperly)
                        $current_comment = ($current_comment!=1) ? $current_comment-1 : $current_comment;
                break;    

                case 'next';
                    echo "You clicked next comment";
                    if (isset($_GET[ 'current_comment' ]))
                        $current_comment = $_GET[ 'current_comment' ];
                    else //this should never execute unless someone manually sets the url (improperly)
                    $current_comment = ($current_comment<$total_comments) ? $current_comment+1 : $current_comment;
                break;    

                default:
                    echo "Invalid action!";
                break;
            } //end switch  
        } 
        
        if (!array_key_exists("text_before_zman$current_comment", $instance)) { //set defaults if its the first time    
            $text_before_zman = 'before text';
            $text_after_zman = 'after text';
            $zman_option = $this->default_zman_option_code; 
        } else { //after the first time set them based on previous values
            $text_before_zman = $instance["text_before_zman$current_comment"];
            $text_after_zman = $instance["text_after_zman$current_comment"];
            $zman_option = $instance["zman_option$current_comment"];
        }
        
        //Create the form output
        echo "<p>"; //The form info should be in a <p> tag.
        if ($current_comment == '1') {
            $this->makeTextInput("title","Enter the Widget Title:",$title);
            echo "<br>";
        }

        //Tell the user to enter valid user and key in the settings menu
        if (get_option('zman_status')!="Validated" && !MAUS_DEBUG){ 
            echo _e("You cannot access the zmanim API until you enter a vaild username and password<br>");
            echo "To enter your information, go to the My Zmanim Login Info screen (under the settings menu of the admin dashboard).";
        }//close the first-time-through if statement
        else{ //if user name was already entered then create comments
            $this->makeTextInput("current_comment","Current Comment being displayed",$current_comment,"hidden");
            $this->makeTextInput("total_comments","Out of (total comments)",$total_comments,"hidden");
            echo "<h2>This is comment $current_comment of $total_comments.</h2><br>";
            $this->makeTextInput("text_before_zman$current_comment","Enter the text to appear before the selected zman.",$text_before_zman);

            //Give the admin the abiltliy to set the zman he desires
            $this->makeSelectInput("zman_option$current_comment","Choose a Zman",$zman_option,$zmanapi->requested_zman_output_array);
            echo "<br>"; //put an extra line break here
                
            $this->makeTextInput("zipcode","Zip Code for the place of the requested Zman",$zipcode); //<!--note the zipcode is one for all comments-->
            $this->makeTextInput("text_after_zman$current_comment","Enter the text to appear after the selected zman.",$text_after_zman);
           
            $previous_comment= ($current_comment!=1) ? $current_comment-1 : 1;
            $next_comment    = ($current_comment!=$total_comments) ? $current_comment+1 : $current_comment;
            $add_comment     = $total_comments + 1;
            
            //Give the option to add and edit other comments 
            echo "<a href='?id=$this->id&action=add&current_comment=$add_comment'>Add New</a>&nbsp";
            echo "<a href='?id=$this->id&action=previous&current_comment=$previous_comment'>Edit Previous</a>&nbsp";
            echo "<a href='?id=$this->id&action=next&current_comment=$next_comment'>Edit Next</a>";
//            echo "<form method=post>";  //eventually use this code to use post instead of passing information through the url
//            echo "<a class='btn btn-primary' name="test'' href='#'>test</a>";
            
            echo "</p>";      
        } //close the else statement
    } //close the form function

    // Apply settings to the widget instance.  in order for it to save the new information to the wordpress database
    // update() function
    public function update( $new_instance, $old_instance ) {
        $current_comment=strip_tags( $new_instance[ 'current_comment' ] );            
        $instance = $old_instance;
        $test=$_GET[ 'current_comment' ];
        if ($current_comment == '1') $instance[ 'title' ] = strip_tags( $new_instance[ 'title' ] );
        $instance[ 'current_comment' ] = strip_tags( $new_instance[ 'current_comment' ] );
        $instance[ 'total_comments' ] = strip_tags( $new_instance[ 'total_comments' ] );
        $instance[ "text_before_zman$current_comment" ] = strip_tags( $new_instance[ "text_before_zman$current_comment" ] );
        $instance[ "text_after_zman$current_comment" ] = strip_tags( $new_instance[ "text_after_zman$current_comment" ] );
        $instance[ "zman_option$current_comment" ] = strip_tags( $new_instance[ "zman_option$current_comment" ] );  
        $instance[ 'zipcode' ] = strip_tags( $new_instance[ 'zipcode' ] ); 
        
        return $instance;
    }
    
    // echo the widget output.
    //widget() function
    public function widget( $args, $instance ) {
        $output = ''; //initialize output variable to which to add all the widget output
        $title = apply_filters( 'widget_title', $instance[ 'title' ] );
        //Get times from Zmanim server
        $user=get_option('zman_api_user');
        $key=get_option('zman_password');
        $endpoint=get_option('zman_url');
        $status = get_option('zman_status');
         
        $zipcode=$instance['zipcode']; //don't do it if there's no zipcode entered 
        if ((!empty($zipcode) && ($status == "Validated")) || MAUS_DEBUG){
            //$zmanimAPI=new maus_MyZmanim_API($instance['user'],$instance['key'], $instance['endpoint'],$instance['timezone']);
            $zmanimAPI=new maus_MyZmanim_API($user,$key,$endpoint);
            $zmanimAPI->getZmanim("$zipcode");
        
            //prepare to produce the output
            $current_comment=$instance['current_comment'];
            $total_comments=$instance['total_comments'];

            date_default_timezone_set('UTC');	//Defines behaviour of strtotime().

            //Output the title
            $output .= $args['before_widget'] . $args['before_title'] .  $title . $args['after_title'];
            //Ouput all the comments
            for ($i = 1; $i <= $total_comments; $i++) {
                if (array_key_exists("text_before_zman$i", $instance)){ //text_before_zman is just an example of a comment #i element 
                    $text_before_zman=$instance["text_before_zman$i"];
                    $text_after_zman=$instance["text_after_zman$i"];
                    $zman_option=$instance["zman_option$i"]; 
                    $output .= $text_before_zman . " " . $this->formatZman($zmanimAPI->zman["$zman_option"]) ." <br>". $text_after_zman . "<br><br>" ;
                } else {
                    $output .= "The comment information for comment #$i is missing!  Reenter it in the admin and be sure to save it!<br><br>";
                }
            }            
            $output .= $args['after_widget'];
        } else { //there is a problem
            //Output the title
            $output .= $args['before_widget'] . $args['before_title'] .  $title . $args['after_title'];
            if (empty($zipcode)) $output .= "Error: Zipcode field is empty.<br>";
            if ($status != "Validated") { 
                $output .= "Error: You cannot access the zmanim API until you enter a vaild username and password.<br>";
                $output .= "To enter your information, go to the 'My Zmanim Login' Info screen (under the settings menu of the admin dashboard).";
            }
            $output .= $args['after_widget'];
        }
        echo $output;
    } //end of function widget()
} //close the maus_Zmanim_Widget class

// Register the widget.
function maus_register_zmanim_widget() { 
  register_widget( 'maus_Zmanim_Widget' );
}
add_action( 'widgets_init', 'maus_register_zmanim_widget' );


?>