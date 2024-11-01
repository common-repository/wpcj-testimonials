=== wpCJ Testimonials ===
Contributors: willcast
Plugin URI: http://www.wpcj.com/plugins/testimonials
Author URI: http://www.wpcj.com
Tags: testimonials,reviews,critics
Requires at least: 2.8
Tested up to: 2.8.4
Stable tag: 1.0.4

This plugin helps you manage a list of testimonials that you can place anywhere in your blog using shortcodes, php calls or widgets. 

== Description ==

This plugin helps you manage a list of testimonials that you can place anywhere in your blog using shortcodes, php calls or widgets.

Optionally, you can specify what "product" a given testimonial is refering to so basically you can use this plugin to present testimonials about different products or services.

== Installation ==

The easiest way to install it is using the WordPress build-in installation feature:

* Look for wpCJ Testimonials
* Click on Install
* Then click on Activate

If you prefer to use the old fashion way then:

* Download the plugin zip file, 
* Uncompress it anywhere in your hard disk and then 
* Upload it to your plugin directory.
* Visit the Plugins page of your WordPress installation, 
* Locate wpCJ Testimonials in the plugin list and * Click on Activate.

Once you have activate it, a new option is added to your WordPress Settings module named wpCJ Testimonials and a new widget as well.

In the plugins page, you will find four options to set:

* **Template:** This setting is used to specify the "layout" of a testimonial. You can use a pure HTML template or, much better, a CSS powered template. It's up to you, just be sure to include the pseudo-tags that the plugin provides ([CLIENT] [COMPANY] [LCOMPANY] [WEBSITE] and [TESTIMONIAL]).
* **Link Attribute:** You can specify if the links that the plugin create based on the website of the clients, will have the attribute rel="nofollow" or not.
* **Max. Size for images:** (still not used but) It specify the maximum weight in KB that an image can be.
* **Zap Me!:** It just tell the plugin that you want to completelly uninstall the plugin so the next time it gets deleted, it delete any trace of the plugin from your WordPress installation.

**Usage**

Once you have set the configuration settings of the plugin, it will allow you to introduce the testimonials.

**Handling Testimonials**

The Manage Testimonials page have two section: An upper section where you add or edit testimonials and a lower section showing the current list of testimonials.

There are two mandatory fields for any testimonial: Client and Testimonial.

Optionally you can especify the client's company, its website and an image.

There is a field named "Product". This field is useful if you want to store different testimonials for different products or services. All you have to do is especify a unique "product key" for every object you want to store testimonials to. Or leave it blank if they are not useful to you.

If you need to edit a testimonial, click on the Client Name in the Testimonial List and then proceed to edit it. Click on Save Changes when you are done and that's it.

**Showing Testimonials**

wpCJ Testimonials allows you to show your testimonials in every possible way allowed by WordPress:

* **Shortcodes:** If you need to add a list of testimonials to any post or page, use the shortcode [wpcjt]. It has three optional parameters: limit, product and id. Limit define the number of testimonials to be show (default 1), Product defines the criteria to use when the plugin choose the testimonials to show and ID a certain testimonial to show (or the latest $limit testimonials if you use 'last' as id. I.e.- **[wpcjt limit="3" product="wpCJ"]** or **[wpcjt limit="3" id="last"]** 
* **Widgets:** If you need to show a testimonial box in any of your sidebars, just add a wpCJ Testimonials widget to it. This widget needs three parameters: Title, Number of Testimonials, Product and ID. They are pretty self-explanatories... or not?
* **PHP Calls:** Just in case you need to add testimonials to your themes, we have provided an option to do so. Just add the following code to your theme: **`<?php if ( function_exists('wpcjt') ) wpcjt($limit,$product,$limit); ?>`**

== Changelog ==

= 1.0 =
* First public version

= 1.0.1 =
* Support for i18n initiated (still working on it though).
* Added a new parameter to the shortcode, php call and widgets: ID. Now you can specify which ID you wan't to show. Also, there is a special id called 'last'): If you specify the parameters $limit and $id='last' you will get the latest $limit testimonials.

= 1.0.2 =
* Correct stripping of slashes in testimonials and client names.
* In testimonials list, added an "Edit" link below client name.

= 1.0.3 =
* handling some weird slashes here and there.
* Added the possibility to add images to the testimonials.

= 1.0.4 =
* I think I finally solved the issues with non-english characters, charsets, slashshes and stuff like that.
* Also solved and false error given when you saved an testimonial where no image was supplied,

