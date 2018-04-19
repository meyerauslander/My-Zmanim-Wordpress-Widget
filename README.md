# My Zmanim Widget
This is a WordPress plugin that will allow the admin to enter comments to appear on the user page.  Each comment includes a zman which is selected by the admin.
For Example "Today Shachris will be at <Sunrise>.  
             Brachos will start 30 minutes beforehand."

I wrote this plugin as an exercise creating wordpress widget that accepts a variable number of input comments from the admin and displays them to the end user.  I also wanted to demonstrate the ability of the widget to pull information from other websites through their API (Application Interface).    

# Install

Download this repo as a zip and install like any other WP plugin

# Setup

Upon adding a My Zmanim Widget to your site you'll have the option to enter the title of your widget, username (to My Zmanim), password/key (to My Zmanim), and time zone (for My Zmanim.  If you don't know it just leave it as the default of -5.0).  
Enter your personal information and click 'save'.  Now you’re ready to enter as many comments as you want!

# Usage

After saving your "My Zmanim" name and key can enter your first comment.  To add your next comment, click on "Add New".  To view or edit a previous comment, click on "Edit Previous" etc.  
Be sure to save your changes before clicking to a different comment. 

# Known Issues
Clicking "Add New" or one of the "Edit" links causes the widget drop-down to collapse (in contrast to what happens if you click save).  You must click the widget rectangle to reopen it.  Now you can do your desired function.  I don't know how to easily fix this problem.

There is no way do delete a comment w/o deleting the widget and starting over.

When the admin wants to go from editing (for example) comment 1 to comment 3 he must save comment 2 before clicking "edit next" to get to comment 3 even if he did not change comment 2.  (This could be fixed by passing the current comment number through the URL and changing the “form" function accordingly.)

If you add a new Zmanim Widget after clicking add or edit on an existing Zmanim Widget then the new one will not give you the option of entering your My Zmanim user and password.  

# To Do
Implement a check of the system to see if it is capable of connecting to the My Zmanim API w/o changing any settings.  If not, display a message to the user.

Do a check on the My Zmanim user information to make sure it's valid before proceeding to comment entry menu.

Implement set transients to cash the user page and thereby minimize API calls and thereby minimize time to load the page.

Add screen-shot images to this file to make it more user-friendly.

# Dependencies
My Zmanim API needs files "php_soap.dll" and "php_openssl.dll" they may need to be enabled in the php.ini by your server systems administrator. 

# Contributing

1. Fork it (<https://github.com/meyerauslander/My-Zmanim-Wordpress-Widget/fork>)
2. Create your feature branch (`git checkout -b feature/fooBar`)
3. Commit your changes (`git commit -am 'Add some fooBar'`)
4. Push to the branch (`git push origin feature/fooBar`)
5. Create a new Pull Request

# Credits
The text input and output functionality of the widget was implemented based on a plugin named "Example Widget Plugin" written by Jon Penland (See https://github.com/jpen365/example-widget-plugin/releases/tag/1.0 for its source code or accesses it from the "sources" folder of the repository in example-widget-plugin.php.)

The API call to My Zmanim was based on the example I downloaded from their website: https://www.myzmanim.com/apidemo.aspx.  Or access it from the sources folder of the repository in Example.php.
