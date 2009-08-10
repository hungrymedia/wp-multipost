=== Multipost MU ===
Contributors: hungrymedia
Donate link: http://example.com/
Tags: wpmu, broadcast, post, multicast
Requires at least: 2.8.2
Tested up to: 2.8.2
Stable tag: trunk

Multipost MU is a Wordpress MU plugin that allows you to publish a post to your top-level blog and have it be automatically posted on ALL sub-blogs.

== Description ==

NOTE: This plugin is for Wordpress MU and has been tested on WPMU 2.8.2 only

WPMU Multipost is a Wordpress MU plugin that allows you to publish a post to your top-level blog and have it be automatically posted on ALL sub-blogs.
If the "master post" is edited or deleted, all sub-blog copies will also be edited or deleted.

== Installation ==

1. Unzip and upload `multipost-mu.php` to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu of the top-level blog in WordPress MU
3. Go to the Multipost MU options page (under Settings) and 'Enable Multipost MU'

== Frequently Asked Questions ==

= Can I choose on which sub-blogs my post will display? =

No. The current version of Multipost MU will post any posts made to the main blog to ALL sub-blogs as long as the plugin is enabled.

= Can I modify or delete a post from all sub-blogs simultaneously? =

Yes. Any posts made to the main blog can be edited or deleted and those changes will be reflected on each sub-blog.

= What is this HMMultipostMU_children custom field I see on my main post? =

The HMMultipostMU_children custom field stores the data for each sub-blog post made from the main blog. This is what maintains the relationship between each main post and sub-blog posts. If you remove or alter this custom field, you may be unable to automatically edit/delete related sub-blog posts any longer.

== Changelog ==

= 1.0 =
Initial release.

