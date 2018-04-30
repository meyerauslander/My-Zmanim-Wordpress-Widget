# My Zmanim Widget
* This is a WordPress Plugin that will allow the admin to enter comments to appear on the user page.  Each comment includes a zman which is selected by the admin.
For Example "Today Shachris will be at (Sunrise).  
             Brachos will start 30 minutes beforehand."
I wrote this plugin as an exercise creating wordpress widget that accepts a variable number of input comments from the admin and displays them to the end user.  I also wanted to demonstrate the ability of the widget to pull information from other websites through their API (Application Interface).  This Plugin uses the Wordpress “transients” to cache the API response in order to minimize unnecessary API calls and therefore greatly reduces the average wait time for the page to reach the end user.    
* Included in the Plugin is a WordPress “Shortcode” function for displaying a requested zman.
# Install
Download this repo as a zip and install like any other WP plugin

# Setup
1. Go To the "Admin Dashboard".  Then select the "My Zmanim login info" screen under the   "Settings" Menu.
2. Enter your user Id and Key.  Clicking "save" will cause your information to be validated and saved to the database.  Don’t proceed to the next step unless your information is confirmed to be valid.
3. Add a My Zmanim Widget to your site. Now you’re ready to enter as many comments as you want!  You will also have the ability to use the "display_zmanim” Shortcode function upon the validation of your user information (even if you don’t add a My Zmanim Widget to your site).  

# Usage
* My Zmanim Widget
    * Fill in the comment to appear before the requested zman.  Select a zman from the dropdown list.  Enter the zip code of the place in which the zman is being requested.  Fill in the comment to appear after the requested zman.  Click “Save”.
    * To add your next comment, click on "Add New".  To view or edit a previous comment, click on "Edit Previous" etc.  
    * Be sure to save your changes before clicking to a different comment. 
* display_zmanim Shortcode function
	* use the following format:  for example [display_zmanim zip="44122" zman="SunriseDefault" offset=0].  See includes/myZmanimAPI.php for names of other Zmanim.  “offset” is a whole number of minutes to be added to the requested zman (it can be negative).
# Dependencies
My Zmanim API needs files "php_soap.dll" and "php_openssl.dll" they may need to be enabled in the php.ini by your server systems administrator.  The "My Zmanim login info" screen automatically tells the user whether these files are enabled or not.

# Contributing
1. Fork it (<https://github.com/meyerauslander/My-Zmanim-Wordpress-Widget/fork>)
2. Create your feature branch (`git checkout -b feature/fooBar`)
3. Commit your changes (`git commit -am 'Add some fooBar'`)
4. Push to the branch (`git push origin feature/fooBar`)
5. Create a new Pull Request

# Credits
* The text input and output functionality of the widget was implemented based on a plugin named "Example Widget Plugin" written by Jon Penland (See https://github.com/jpen365/example-widget-plugin/releases/tag/1.0 for its source code or accesses it from the "sources" folder of the repository in example-widget-plugin.php.)
* The API call to My Zmanim was based on the example I downloaded from their website: https://www.myzmanim.com/apidemo.aspx.  Or access it from the “sources” folder of the repository in Example.php.
* The "My Zmanim login info" screen was implemented based on a plugin written by TreeStone Media Group LLC named “WP-Mag-attributes” (See https://github.com/treestonemedia/WP-Mag-attributes/admin/ in the file named “magento-admin.php”.  Or access it from the “sources” folder of this repository.)
