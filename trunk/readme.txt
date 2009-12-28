=== Multipost MU ===
Contributors: hungrymedia
Donate link: http://hungry-media.com/index.php#donate
Tags: wpmu, broadcast, post, multicast
Requires at least: 2.8.2
Tested up to: 2.8.5
Stable tag: trunk

Multipost MU is a Wordpress MU plugin that allows you to publish a post to your top-level blog and have it be automatically posted on ALL sub-blogs.

== Description ==

NOTE: This plugin is for Wordpress MU.

WPMU Multipost is a Wordpress MU plugin that allows you to publish a post to your top-level blog and have it be automatically posted on ALL sub-blogs.
If the "master post" is edited or deleted, all sub-blog copies will also be edited or deleted.

== Installation ==

1. Unzip and upload `multipost-mu.php` to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu of the main/master blog in WordPress MU
3. Go to the Multipost MU options page (under Settings) and 'Enable Multipost MU'

== Frequently Asked Questions ==

= Can I choose on which sub-blogs my post will display? =

Yes. Once the plugin is enabled, you will see a Multipost MU area on the right side of the "Add New Post" and "Edit Post" screens. This will allow you to select which blogs receive a copy of the post.

= Can I modify or delete a post from all sub-blogs simultaneously? =

Yes. Any posts made to the main blog can be edited or deleted and those changes will be reflected on each sub-blog.

= What is this HMMultipostMU_children custom field I see on my main post? =

The HMMultipostMU_children custom field stores the data for each sub-blog post made from the main blog. This is what maintains the relationship between each main post and sub-blog posts. If you remove or alter this custom field, you may be unable to automatically edit/delete related sub-blog posts any longer.

== Screenshots ==

1. Shows the options page for Multipost MU. Simply a way to enable/disable multiposting functionality. This may be useful to temporarily disable the plugin if you wish to make a post to the main blog WITHOUT posting to all sub-blogs. 

2. Shows the additional panel displayed on "Add New Post" and "Edit Post" screens. This allows you to choose to which sub-blogs your master post is added.

== Changelog ==

= 1.6 =
* Added the ability to choose to which sub-blogs your master post is also posted.

= 1.5 =
* Added category support. If categories from master post do not yet exist in sub-blogs, they will be created.

= 1.4 =
* BUG FIX: Removed debug statements.

= 1.3 =
* BUG FIX: Added logic to ensure ALL sub-blogs are updated (not just the first 10). Thanks to Ben Gribaudo (http://www.bengribaudo.com)

= 1.2 =
* Added tag support so that master post's tags are carried through to sub-blog posts.

= 1.1 =
* BUG FIX: Added logic to prevent multi-posts from posting to source blog, resulting in hundreds of duplicate posts.

= 1.0 =
* Initial release.

