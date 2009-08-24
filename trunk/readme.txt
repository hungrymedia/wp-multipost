=== Multipost MU ===
Contributors: hungrymedia
Donate link: http://hungry-media.com/index.php#donate
Tags: wpmu, broadcast, post, multicast
Requires at least: 2.8.2
Tested up to: 2.8.4
Stable tag: trunk

Multipost MU is a Wordpress MU plugin that allows you to publish a post to your top-level blog and have it be automatically posted on ALL sub-blogs.

== Description ==

NOTE: This plugin is for Wordpress MU.

WPMU Multipost is a Wordpress MU plugin that allows you to publish a post to your top-level blog and have it be automatically posted on ALL sub-blogs.
If the "master post" is edited or deleted, all sub-blog copies will also be edited or deleted.

NOTE: This plugin will not currently work if you are using subdomains for your sub-blogs. A fix for this is forthcoming.


== Installation ==

1. Unzip and upload `multipost-mu.php` to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu of the main/master blog in WordPress MU
3. Go to the Multipost MU options page (under Settings) and 'Enable Multipost MU'

== Frequently Asked Questions ==

= Can I choose on which sub-blogs my post will display? =

No. The current version of Multipost MU will post any posts made to the main blog to ALL sub-blogs as long as the plugin is enabled.

= Can I modify or delete a post from all sub-blogs simultaneously? =

Yes. Any posts made to the main blog can be edited or deleted and those changes will be reflected on each sub-blog.

= What is this HMMultipostMU_children custom field I see on my main post? =

The HMMultipostMU_children custom field stores the data for each sub-blog post made from the main blog. This is what maintains the relationship between each main post and sub-blog posts. If you remove or alter this custom field, you may be unable to automatically edit/delete related sub-blog posts any longer.

= Why am I am seeing hundreds of copies of each post show up? =

This plugin will not currently work if you are using subdomains for your sub-blogs. A fix for this is forthcoming.

== Screenshots ==

1. Shows the options page for Multipost MU. Simply a way to enable/disable multiposting functionality. This may be useful to temporarily disable the plugin if you wish to make a post to the main blog WITHOUT posting to all sub-blogs. 

== Changelog ==

= 1.0 =
Initial release.

