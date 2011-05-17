=== Weekly TimeTable - Wordpress PlugIn ===
Contributors: X. Villamuera
Author: X. Villamuera
Author URI: http://www.fifteenpeas.com/
Plugin URI: http://www.fifteenpeas.com/blog/wordpress-weekly-time-table-plugin/
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=FRBWAGNGFZZ6U&item_name=Donate%20for%3a%20Weekly%20Time%20Table%20Wordpress%20Plugin&no_shipping=0&no_note=1&tax=0&currency_code=USD&lc=US&charset=UTF%2d8
Tags: Time table, weekly time table
Requires at least: 2.0.2
Tested up to: 3.1
Stable Tag: 1.3

Create infinite time tables for the week and display them on your site

== Description ==

Basically, this systems installs 2 tables in the db:
- the entry table
- the time table

Whenever you create an entry you can associate it with a time table. Each cell is free to fill with whatever you want. No formatting is done.
Then you can use a shortcode with the entry id as a parameter.

The plugin has been translated in spanish and french so far.

For more information check the plugin homepage:
http://www.fifteenpeas.com/blog/wordpress-weekly-time-table-plugin/

== Installation ==

1. Upload the 'wttPlugIn' folder to the '/wp-content/plugins/' directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to Settings on the Admin Panel and set it up

Use this shortcode anywhere :

[wttdsp entry_id=5] where 5 is the id of the time table...or [wttdsp entry_id=5,17,8] for multiple entries.
 

== Frequently Asked Questions ==
= Can I add multiple entries to a timetable ? =
Yes, just use a list of IDs separated by a comma instead of one ID (v1.2)

= Can I use the timetable in spanish ? =
yes, just use the locale folder inside the weekly time yable plugin folder to put your translations (v1.2)
Spanish and french are already in place.

== Screenshots ==

1. The Wtt menu
2. A client's view using the shortcode
3. Admin WTT management 

== Changelog ==

= 1.3 =
- Spanish and French Translation available

= 1.2 =
- I18n ready
- Possibility to add multiple entries (shortcode with ID list) for one time table
- Possibility to enter html as entries
- translated in spanish and french

= 1.0 =
First Version
