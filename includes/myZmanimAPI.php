<?php
/*
 Class to connect to the My Zmanim API.  depends on zmanimAPI.php 
 */
class maus_MyZmanim_API extends maus_Zmanim_API{
    
    protected $clientTimeZone,$locationID;  //properties unique to my zmanim api
    
    //re client's time zone: Optional,is sometimes used to resolve ambiguous queries. -5.0 for Eastern standard time
    public function __construct($in_user, $in_key, $in_endpoint, $in_clientTimeZone=''){ 
        //set the zmanim output array
         $this->requested_zman_output_array=array("Dawn72" => "Dawn(Alos HaShachar)" , 
                                     "YakirDefault" => "Earliest Tallis",
                                     "SunriseDefault"=> "Sunrise",
                                     "ShemaMA72"=>"Shema MA",
                                     "ShemaGra"=>"Shema GRA",              
                                     "SunsetDefault"=>"Sunset");
        
        //set user specific attributes
        $this->user=$in_user;
        $this->key=$in_key;
        $this->endpoint=$in_endpoint;
        $this->clientTimeZone=$in_clientTimeZone;
        
    }
    
     //Make the api call to populate the zman, place, and time arrays
    public function getZmanim($zipcode=''){
       $transData = get_transient("$zipcode"); //check if the transient is already cached
	   if ($transData===false) { //it isn't cached
            //Set postalID.  If no zipcode is passed in then the postalID must be set by the user
            if ($zipcode!=''){
                 $this->findPostal($zipcode); 
            }
            //Instantiate $APIcaller and prepare parameters:
            $APIcaller = new SoapClient($this->endpoint);
            $params = array("User"=>$this->user
                            , "Key"=>$this->key
                            , "Coding"=>"PHP"
                            , "Language"=>"en"            
                            , "LocationID"=>$this->locationID
                            , "InputDate"=>date("c")
                            );
            //Call API:
            $response = $APIcaller->__soapCall("GetDay", array('parameters'=>
                array("Param"=>$params)));

            $outterArray = ((array)$response);
            $innerArray = ((array)$outterArray['GetDayResult']);

            $this->time  = ((array)$innerArray['Time']);
            $this->place = ((array)$innerArray['Place']);	
            $this->zman  = ((array)$innerArray['Zman']);

            //set transient for this zipcode
                //calculate time till midnight at this zipcode
                $hour = date("G", strtotime($this->zman['CurrentTime']));
                $minute = date("i", strtotime($this->zman['CurrentTime']));
                $secondsTill = (24 - $hour - 1)*60*60 + (60-$minute)*60;  
            set_transient("$zipcode", $response, $secondsTill);
       }else{ //set the arrays from the transient data
            $outterArray = ((array)$transData);
            $innerArray = ((array)$outterArray['GetDayResult']);

            $this->time  = ((array)$innerArray['Time']);
            $this->place = ((array)$innerArray['Place']);	
            $this->zman  = ((array)$innerArray['Zman']); 
       }
    } //end of getZmanim function
    
    
    
    public function findPostal($pPostalCode) {
        $wcfClient = new SoapClient($this->endpoint);
        $params = array("User"=>$this->user, "Key"=>$this->key, "Coding"=>"PHP", "TimeZone"=>$this->clientTimeZone, "Query"=>$pPostalCode);
        $response = $wcfClient->__soapCall("SearchPostal", array('parameters'=>array("Param"=>$params)));
        
        $outterArray = ((array)$response);
        $innerArray = ((array)$outterArray['SearchPostalResult']);
        if ($innerArray["ErrMsg"] != NULL) {
            return $innerArray["ErrMsg"];
        }
        $this->locationID=$innerArray["LocationID"];
    }
    
    //attempt to make a call to the My Zmanim API 
    //usage: try{ call this function } catch{ actions upon error } 
    //returns any error message returned by My Zmanim
    public function validateUser(){
        $wcfClient = new SoapClient($this->endpoint);
        $params = array("User"=>$this->user, "Key"=>$this->key, "Coding"=>"PHP", 
                        "TimeZone"=>$this->clientTimeZone, "Query"=>"44092"); //any valid zipcode will work for this
        $response = $wcfClient->__soapCall("SearchPostal", array('parameters'=>array("Param"=>$params)));

        $outterArray = ((array)$response);
        $innerArray = ((array)$outterArray['SearchPostalResult']);
        if ($innerArray["ErrMsg"] != NULL) {
            return $innerArray["ErrMsg"];
        }
        return null; //valided successfully!
    }
}  // end of maus_MyZmanim_API class
?>