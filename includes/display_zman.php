<?php
//Short code function.  Invoke by putting code on the user page.
//for example [display_zmanim zip="44122" zman="SunsetDefault" offset=-20]
//see myZmanimAPI.php for list of zmanim to choose from
function display_zmanim($input) {
    $invalid_info_message="Error in display_zmanim shortcode function<br>";
    if (get_option('zman_status')=="Validated"){
        $zmantext='';    
        $zipcode=$input['zip'];
        $zman=$input['zman'];
        $offset=(int)$input['offset']; 
        $endpoint = get_option('zman_url');
        $user=get_option( 'zman_api_user' );
        $key=get_option('zman_password');    
        
        //get zmanim for this zip
        $zmanimAPI=new maus_MyZmanim_API($user,$key,$endpoint);
        $zmanimAPI->getZmanim("$zipcode");
        
        //set the output with the requested zman 
        $zmantext .= maus_InputForm_Widget::formatZman($zmanimAPI->zman["$zman"],$offset);
    } else $zmantext=$invalid_info_message;
    return $zmantext; 
} //end display_zmanim function

//create shortcode to display zman
add_shortcode('display_zmanim','display_zmanim');

?>