<!DOCTYPE html>
<html>
<head>
	<meta charset="UTF-8">
	<title>PHP - MyZmanim API Example</title>
</head>
<body style='font-family:tahoma,arial;'>


<?php

/*
 * NOTE: This page requires that "php_soap.dll" and "php_openssl.dll" be enabled in "php.ini".
 */


$endpoint = 'https://api.myzmanim.com/engine1.svc?wsdl';
$user="0001583267";
$key="94c8af54b8c190573f2dc0fed60bd2d1fd5e80ddd559b6a87f60fd2b1350864f179464977a084579";

//Look up the locationID for the desired place: (Uncomment one of the following examples)
$locationID = findPostal("11559");    //Typical US zip code
//locationID = findPostal("M6B2K9");    //Typical Canadian postal code
//$locationID = findPostal("NW118AU");  //Typical UK postcode
//$locationID = findPostal("90500");    //Typical 5-digit Israel Mikud code
//$locationID = findPostal("JFK");      //Typical airport code
//$locationID = findPostal("27526341"); //Typical MyZmanim LocationID
//$locationID = findGps(48.86413211779521324,2.32941612345133754);   //Typical GPS coordinates

//Display zmanim information for said location.
DisplayZmanim($locationID);




function findGps($lat, $lon)
{
	global $user, $key, $endpoint;
	$wcfClient = new SoapClient($endpoint);
	$point  = array("Lat"=>$lat, "Lon"=>$lon);
	$params = array("User"=>$user, "Key"=>$key, "Coding"=>"PHP", "Point"=>$point);
	$response = $wcfClient->__soapCall("SearchGps", array('parameters'=>array("Param"=>$params)));
	$outterArray = ((array)$response);
	$innerArray = ((array)$outterArray['SearchGpsResult']);
	
	if ($innerArray["ErrMsg"] != NULL) {
		echo "Error: ";
        echo $innerArray["ErrMsg"];
		return;
	}
	return $innerArray["LocationID"]; 
}


function findPostal($pPostalCode) {
	global $user, $key, $endpoint;
	$wcfClient = new SoapClient($endpoint);
	$clientTimeZone = -5.0;  	//Pass the client's time zone. Optional, but if provided, is sometimes used to resolve ambiguous queries.
	$params = array("User"=>$user, "Key"=>$key, "Coding"=>"PHP", "TimeZone"=>$clientTimeZone, "Query"=>$pPostalCode);
	$response = $wcfClient->__soapCall("SearchPostal", array('parameters'=>array("Param"=>$params)));
	$outterArray = ((array)$response);
	$innerArray = ((array)$outterArray['SearchPostalResult']);

	if ($innerArray["ErrMsg"] != NULL) {
		echo "Error: ";
		echo $innerArray["ErrMsg"];
		return;
	}
	return $innerArray["LocationID"];
}


function displayZmanim($pLocationID){
	global $user, $key, $endpoint;
	date_default_timezone_set('UTC');	//Defines behaviour of strtotime().
	$wcfClient = new SoapClient($endpoint);
	$params = array("User"=>$user, "Key"=>$key, "Coding"=>"PHP", "Language"=>"en", "LocationID"=>$pLocationID, "InputDate"=>date("c") );
	$response = $wcfClient->__soapCall("GetDay", array('parameters'=>array("Param"=>$params)));
	$outterArray = ((array)$response);
	$innerArray = ((array)$outterArray['GetDayResult']);

	$time  = ((array)$innerArray['Time']);
	$place = ((array)$innerArray['Place']);	
	$zman  = ((array)$innerArray['Zman']);
	
	if ($innerArray["ErrMsg"] != NULL) {
		echo "Error: ";
		echo $innerArray["ErrMsg"];
		return;
	}
	
	echo ("     SAMPLE USAGE");
	echo ("<br/>=========================");
	echo ("<div style='text-align:center;'>");
	echo ("<br/>" . $place['Name']);
	echo ("<br/>" . $time['DateSemiLong']);
	echo ("<br/>" . $time['Weekday']);
	if ( !zmanIsNull($zman['CurrentTime']) ) echo ("<br/>" . date("g:i a", strtotime($zman['CurrentTime'])) );  //Display the current time if available and applicable.
	if ($time['ParshaAndHoliday'] <> NULL) echo ("<br/>" . $time['ParshaAndHoliday']);
	if ($time['Omer'] != 0) echo("<br/>" . "Omer count " . $time['Omer']);
	switch($time['DaylightTime']){
		case 0:
			echo("<br/>Standard Time");
			break;
		case 1:
			echo("<br/>Daylight Saving Time");
			break;
		case 2:
			echo("<br/>Add 1 hr for DST when applicable.");
			break;
		case 3:
			echo("<br/>Add 1 hr for DST if/when applicable");
			break;
	}	

	echo("<br/>-------");
	echo("<br/>Dawn: "             . formatZman($zman["Dawn72"]));
	echo("<br/>Earliest Talis ("   . $place["YakirDegreesDefault"] .  "&deg;): "   . formatZman($zman["YakirDefault"]));
	echo("<br/>Sunrise: "          . date('g:i:s a', strtotime($zman["SunriseDefault"])));
	echo("<br/>Shema MA: "         . formatZman($zman["ShemaMA72"]));
	echo("<br/>Shema Gra: "        . formatZman($zman["ShemaGra"]));
	echo("<br/>Shachris Gra: "     . formatZman($zman["ShachrisGra"]));
	echo("<br/>Midday: "           . formatZman($zman["Midday"]));
	echo("<br/>Earliest Mincha: "  . formatZman($zman["MinchaStrict"]));
	echo("<br/>Plag Hamincha: "    . formatZman($zman["PlagGra"]));
	
	if ( !zmanIsNull($zman["Candles"]) ) echo("<br/>Candlelighting (" . $place["CandlelightingMinutes"] . " min): " . formatZman($zman["Candles"])   );   // Display candlelighting time if available and applicable.
	echo("<br/>Sunset: "           . formatZman($zman["SunsetDefault"]));
	echo("<br/>Night 3 stars: "    . formatZman($zman["NightShabbos"]));
	echo("<br/>Night 72 minutes: " . formatZman($zman["Night72fix"]));
	echo("<br/>");
	echo("<br/>Minyan for Mincha at: " . date("g:i a", strtotime($zman["SunsetDefault"]) - (20*60)) );
	echo("<br/>");
	echo("<br/>Powered by <a href='http://www.myZmanim.com/'>MyZmanim</a>");
	echo("</div>");

	echo("<br/>");
	echo("<br/>MEMBERS");
	echo("<br/>==============");
	echo("<br/>Place.LocationID: "            . $place["LocationID"]);
	echo("<br/>Place.Name: "                  . $place["Name"]);
	echo("<br/>Place.NameShort: "             . $place["NameShort"]);
	echo("<br/>Place.Country: "               . $place["Country"]);
	echo("<br/>Place.State: "                 . $place["State"]);
	echo("<br/>Place.County: "                . $place["County"]);
	echo("<br/>Place.City: "                  . $place["City"]);
	echo("<br/>Place.PostalCode: "            . $place["PostalCode"]);
	echo("<br/>Place.DavenDirectionGC: "      . $place["DavenDirectionGC"]);
	echo("<br/>Place.DavenDirectionRL: "      . $place["DavenDirectionRL"]);
	echo("<br/>Place.CandlelightingMinutes: " . $place["CandlelightingMinutes"]);
	echo("<br/>Place.YakirDegreesDefault: "   . $place["YakirDegreesDefault"]);
	echo("<br/>Place.ElevationObserver: "     . $place["ElevationObserver"]);
	echo("<br/>Place.ElevationWest: "         . $place["ElevationWest"]);
	echo("<br/>Place.ElevationEast: "         . $place["ElevationEast"]);
	echo("<br/>Place.ObservesDST: "           . $place["ObservesDST"]);
	echo("<br/>Place.AirportCode: "           . $place["AirportCode"]);
	echo("<br/>Place.CityHebrew: "            . $place["CityHebrew"]);
	echo("<br/>");
	echo("<br/>Time.DateCivil: "             . date("m/d/Y", strtotime($time["DateCivil"]))  );
	echo("<br/>Time.DateCivilLong: "         . $time["DateCivilLong"]);
	echo("<br/>Time.DateJewish: "            . $time["DateJewish"]);
	echo("<br/>Time.DateJewishLong: "        . $time["DateJewishLong"]);
	echo("<br/>Time.DateJewishShort: "       . $time["DateJewishShort"]);
	echo("<br/>Time.DateFullLong: "          . $time["DateFullLong"]);
	echo("<br/>Time.DateFullShort: "         . $time["DateFullShort"]);
	echo("<br/>Time.DateSemiLong: "          . $time["DateSemiLong"]);
	echo("<br/>Time.DateSemiShort: "         . $time["DateSemiShort"]);
	echo("<br/>Time.Weekday: "               . $time["Weekday"]);
	echo("<br/>Time.WeekdayShort: "          . $time["WeekdayShort"]);
	echo("<br/>Time.Omer: "                  . $time["Omer"]);
	echo("<br/>Time.DafYomiTract: "          . $time["DafYomiTract"]);
	echo("<br/>Time.DafYomiPage: "           . $time["DafYomiPage"]);
	echo("<br/>Time.DafYomi: "               . $time["DafYomi"]);
	echo("<br/>Time.DaylightTime: "          . $time["DaylightTime"]);
	echo("<br/>Time.Parsha: "                . $time["Parsha"]);
	echo("<br/>Time.ParshaShort: "           . $time["ParshaShort"]);
	echo("<br/>Time.Holiday: "               . $time["Holiday"]);
	echo("<br/>Time.HolidayShort: "          . $time["HolidayShort"]);
	echo("<br/>Time.ParshaAndHoliday: "      . $time["ParshaAndHoliday"]);
	echo("<br/>Time.TomorrowParsha: "        . $time["TomorrowParsha"]);
	echo("<br/>Time.TomorrowParshaOrHoliday: " . $time["TomorrowParshaOrHoliday"]);
	echo("<br/>");
	echo("<br/>Time.IsShabbos: "             . $time["IsShabbos"]);
	echo("<br/>Time.IsYomTov: "              . $time["IsYomTov"]);
	echo("<br/>Time.IsCholHamoed: "          . $time["IsCholHamoed"]);
	echo("<br/>Time.IsYomKipper: "           . $time["IsYomKipper"]);
	echo("<br/>Time.IsTishaBav: "            . $time["IsTishaBav"]);
	echo("<br/>Time.IsErevTishaBav: "        . $time["IsErevTishaBav"]);
	echo("<br/>Time.IsShivaAsarBitammuz: "   . $time["IsShivaAsarBitammuz"]);
	echo("<br/>Time.IsTaanisEsther: "        . $time["IsTaanisEsther"]);
	echo("<br/>Time.IsTzomGedalia: "         . $time["IsTzomGedalia"]);
	echo("<br/>Time.IsAsaraBiteves: "        . $time["IsAsaraBiteves"]);
	echo("<br/>Time.IsFastDay: "             . $time["IsFastDay"]);
	echo("<br/>Time.IsErevPesach: "          . $time["IsErevPesach"]);
	echo("<br/>Time.IsRoshChodesh: "         . $time["IsRoshChodesh"]);
	echo("<br/>Time.IsTuBeshvat: "           . $time["IsTuBeshvat"]);
	echo("<br/>Time.IsErevShabbos: "         . $time["IsErevShabbos"]);
	echo("<br/>Time.IsErevYomTov: "          . $time["IsErevYomTov"]);
	echo("<br/>Time.IsErevYomKipper: "       . $time["IsErevYomKipper"]);
	echo("<br/>Time.TonightIsYomTov: "       . $time["TonightIsYomTov"]);
	echo("<br/>Time.TomorrowNightIsYomTov: " . $time["TomorrowNightIsYomTov"]);
	echo("<br/>");
	echo("<br/>Zman.CurrentTime: " . formatZman($zman["CurrentTime"]));
	echo("<br/>Zman.Dawn90: " . formatZman($zman["Dawn90"]));
	echo("<br/>Zman.Dawn72: " . formatZman($zman["Dawn72"]));
	echo("<br/>Zman.Dawn72fix: " . formatZman($zman["Dawn72fix"]));
	echo("<br/>Zman.DawnRMF: " . formatZman($zman["DawnRMF"]));
	echo("<br/>Zman.Yakir115: " . formatZman($zman["Yakir115"]));
	echo("<br/>Zman.Yakir110: " . formatZman($zman["Yakir110"]));
	echo("<br/>Zman.Yakir102: " . formatZman($zman["Yakir102"]));
	echo("<br/>Zman.YakirDefault: " . formatZman($zman["YakirDefault"]));
	echo("<br/>Zman.SunriseLevel: " . date('g:i:s a', strtotime($zman["SunriseLevel"])));
	echo("<br/>Zman.SunriseElevated: " . date('g:i:s a', strtotime($zman["SunriseElevated"])));
	echo("<br/>Zman.SunriseDefault: " . date('g:i:s a', strtotime($zman["SunriseDefault"])));
	echo("<br/>Zman.ShemaBenIsh90ToFastTuc: " . formatZman($zman["ShemaBenIsh90ToFastTuc"]));
	echo("<br/>Zman.ShemaBenIsh72ToFastTuc: " . formatZman($zman["ShemaBenIsh72ToFastTuc"]));
	echo("<br/>Zman.ShemaBenIsh72ToShabbos: " . formatZman($zman["ShemaBenIsh72ToShabbos"]));
	echo("<br/>Zman.ShemaMA90: " . formatZman($zman["ShemaMA90"]));
	echo("<br/>Zman.ShemaMA72: " . formatZman($zman["ShemaMA72"]));
	echo("<br/>Zman.ShemaMA72fix: " . formatZman($zman["ShemaMA72fix"]));
	echo("<br/>Zman.ShemaGra: " . formatZman($zman["ShemaGra"]));
	echo("<br/>Zman.ShemaRMF: " . formatZman($zman["ShemaRMF"]));
	echo("<br/>Zman.ShachrisMA90: " . formatZman($zman["ShachrisMA90"]));
	echo("<br/>Zman.ShachrisMA72: " . formatZman($zman["ShachrisMA72"]));
	echo("<br/>Zman.ShachrisMA72fix: " . formatZman($zman["ShachrisMA72fix"]));
	echo("<br/>Zman.ShachrisGra: " . formatZman($zman["ShachrisGra"]));
	echo("<br/>Zman.ShachrisRMF: " . formatZman($zman["ShachrisRMF"]));
	echo("<br/>Zman.Midday: " . formatZman($zman["Midday"]));
	echo("<br/>Zman.MiddayRMF: " . formatZman($zman["MiddayRMF"]));
	echo("<br/>Zman.MinchaGra: " . formatZman($zman["MinchaGra"]));
	echo("<br/>Zman.Mincha30fix: " . formatZman($zman["Mincha30fix"]));
	echo("<br/>Zman.MinchaMA72fix: " . formatZman($zman["MinchaMA72fix"]));
	echo("<br/>Zman.MinchaStrict: " . formatZman($zman["MinchaStrict"]));
	echo("<br/>Zman.KetanaGra: " . formatZman($zman["KetanaGra"]));
	echo("<br/>Zman.KetanaMA72fix: " . formatZman($zman["KetanaMA72fix"]));
	echo("<br/>Zman.PlagGra: " . formatZman($zman["PlagGra"]));
	echo("<br/>Zman.PlagMA72fix: " . formatZman($zman["PlagMA72fix"]));
	echo("<br/>Zman.PlagBenIsh90ToFastTuc: " . formatZman($zman["PlagBenIsh90ToFastTuc"]));
	echo("<br/>Zman.PlagBenIsh72ToFastTuc: " . formatZman($zman["PlagBenIsh72ToFastTuc"]));
	echo("<br/>Zman.PlagBenIsh72ToShabbos: " . formatZman($zman["PlagBenIsh72ToShabbos"]));
	echo("<br/>Zman.SunsetLevel: " . formatZman($zman["SunsetLevel"]));
	echo("<br/>Zman.SunsetElevated: " . formatZman($zman["SunsetElevated"]));
	echo("<br/>Zman.SunsetDefault: " . formatZman($zman["SunsetDefault"]));
	echo("<br/>Zman.NightGra180: " . formatZman($zman["NightGra180"]));
	echo("<br/>Zman.NightGra225: " . formatZman($zman["NightGra225"]));
	echo("<br/>Zman.NightGra240: " . formatZman($zman["NightGra240"]));
	echo("<br/>Zman.NightZalman: " . formatZman($zman["NightZalman"]));
	echo("<br/>Zman.NightFastTuc: " . formatZman($zman["NightFastTuc"]));
	echo("<br/>Zman.NightFastRMF: " . formatZman($zman["NightFastRMF"]));
	echo("<br/>Zman.NightMoed: " . formatZman($zman["NightMoed"]));
	echo("<br/>Zman.NightShabbos: " . formatZman($zman["NightShabbos"]));
	echo("<br/>Zman.NightChazonIsh: " . formatZman($zman["NightChazonIsh"]));
	echo("<br/>Zman.Night50fix: " . formatZman($zman["Night50fix"]));
	echo("<br/>Zman.Night60fix: " . formatZman($zman["Night60fix"]));
	echo("<br/>Zman.Night72: " . formatZman($zman["Night72"]));
	echo("<br/>Zman.Night72fix: " . formatZman($zman["Night72fix"]));
	echo("<br/>Zman.Night72fixLevel: " . formatZman($zman["Night72fixLevel"]));
	echo("<br/>Zman.Night90: " . formatZman($zman["Night90"]));
	echo("<br/>Zman.Midnight: " . formatZman($zman["Midnight"]));
	echo("<br/>");
	echo("<br/>Zman.ChametzEatGra: " . formatZman($zman["ChametzEatGra"]));
	echo("<br/>Zman.ChametzEatMA72: " . formatZman($zman["ChametzEatMA72"]));
	echo("<br/>Zman.ChametzEatMA72fix: " . formatZman($zman["ChametzEatMA72fix"]));
	echo("<br/>Zman.ChametzEatRMF: " . formatZman($zman["ChametzEatRMF"]));
	echo("<br/>Zman.ChametzBurnGra: " . formatZman($zman["ChametzBurnGra"]));
	echo("<br/>Zman.ChametzBurnMA72: " . formatZman($zman["ChametzBurnMA72"]));
	echo("<br/>Zman.ChametzBurnMA72fix: " . formatZman($zman["ChametzBurnMA72fix"]));
	echo("<br/>Zman.ChametzBurnRMF: " . formatZman($zman["ChametzBurnRMF"]));
	echo("<br/>");
	echo("<br/>Zman.TomorrowNightShabbos: "    . formatZman($zman["TomorrowNightShabbos"   ]));
	echo("<br/>Zman.TomorrowSunriseLevel: "    . date('g:i:s a', strtotime($zman["TomorrowSunriseLevel"]    ))    );
	echo("<br/>Zman.TomorrowSunriseElevated: " . date('g:i:s a', strtotime($zman["TomorrowSunriseElevated"] ))    );
	echo("<br/>Zman.TomorrowSunriseDefault: "  . date('g:i:s a', strtotime($zman["TomorrowSunriseDefault"]  ))    );
	echo("<br/>Zman.TomorrowSunsetLevel: "     . formatZman($zman["TomorrowSunsetLevel"    ]));
	echo("<br/>Zman.TomorrowSunsetElevated: "  . formatZman($zman["TomorrowSunsetElevated" ]));
	echo("<br/>Zman.TomorrowSunsetDefault: "   . formatZman($zman["TomorrowSunsetDefault"  ]));
	echo("<br/>Zman.TomorrowNight72fix: "      . formatZman($zman["TomorrowNight72fix"     ]));
	echo("<br/>Zman.TomorrowNightChazonIsh: "  . formatZman($zman["TomorrowNightChazonIsh" ]));
	echo("<br/>Zman.Tomorrow2NightShabbos: "   . formatZman($zman["Tomorrow2NightShabbos"  ]));
	echo("<br/>Zman.Tomorrow2SunsetLevel: "    . formatZman($zman["Tomorrow2SunsetLevel"   ]));
	echo("<br/>Zman.Tomorrow2SunsetElevated: " . formatZman($zman["Tomorrow2SunsetElevated"]));
	echo("<br/>Zman.Tomorrow2SunsetDefault: "  . formatZman($zman["Tomorrow2SunsetDefault" ]));
	echo("<br/>Zman.Tomorrow2Night72fix: "     . formatZman($zman["Tomorrow2Night72fix"    ]));
	echo("<br/>Zman.Tomorrow2NightChazonIsh: " . formatZman($zman["Tomorrow2NightChazonIsh"]));
	echo("<br/>");
	echo("<br/>Zman.PropGra: "               . ticksToMinutes($zman["PropGra"])              );
	echo("<br/>Zman.PropMA72: "              . ticksToMinutes($zman["PropMA72"])             );
	echo("<br/>Zman.PropMA72fix: "           . ticksToMinutes($zman["PropMA72fix"])          );
	echo("<br/>Zman.PropMA90: "              . ticksToMinutes($zman["PropMA90"])             );
	echo("<br/>Zman.PropRmfMorning: "        . ticksToMinutes($zman["PropRmfMorning"])        );
	echo("<br/>Zman.PropBenIsh90ToFastTuc: " . ticksToMinutes($zman["PropBenIsh90ToFastTuc"]));
	echo("<br/>Zman.PropBenIsh72ToFastTuc: " . ticksToMinutes($zman["PropBenIsh72ToFastTuc"]));
	echo("<br/>Zman.PropBenIsh72ToShabbos: " . ticksToMinutes($zman["PropBenIsh72ToShabbos"]));
	echo("<br/>");
	echo("<br/>" . $innerArray['Copyright'] );



	echo("<br/>");
	
    return;
}


function ticksToMinutes($ticks){
	$seconds = $ticks * 1/10000000;
	$minutes = $seconds / 60;
	return round( $minutes, 1, PHP_ROUND_HALF_DOWN); 
}


function formatZman($aZman){
	return date("g:i a", strtotime($aZman));
}


function zmanIsNull($aZman){
	return date("Y-m-d", strtotime($aZman)) == "0001-01-01";
}


?>

</body>
</html> 