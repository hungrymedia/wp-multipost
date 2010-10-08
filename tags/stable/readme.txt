=== Multipost MU ===
Contributors: hungrymedia, mirmillo, tmuka
Donate link: http://hungry-media.com/index.php#donate
Tags: wpmu, broadcast, post, multicast
Requires at least: 2.9.2
Tested up to: 3.0
Stable tag: trunk

Multipost MU is a Wordpress MU plugin that allows you to duplicate posts and pages to multiple sub-blogs at once.

== Description ==

********
NOTE: This plugin has been updated for WordPress 3.0
********
Multipost MU is a Wordpress MU plugin that allows you publish a post or page made to a single blog across multiple blogs. An option panel is added to the sidebar of the Add/Edit post and Add/Edit page screens that allow you to select other blogs to which the current post/page is published. If the "master post/page" is edited or deleted, all multiposted copies will also be edited or deleted. 

== Installation ==

1. Unzip and upload `multipost-mu.php` to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu of the main/master blog in WordPress MU
3. Add a new or edit an existing post and choose to which other blogs this post should be published (lower right of screen)

== Frequently Asked Questions ==

= Can I choose on which sub-blogs my post/page will display? =

Yes. Once the plugin is enabled, you will see a Multipost area on the right side of the "Add New Post", "Edit Post", "Add New Page" and "Edit Page" screens. This will allow you to select which blogs receive a copy of the post or page.

= Can I modify or delete a post or page from all sub-blogs simultaneously? =

Yes. Any posts or pages made to the main blog can be edited or deleted and those changes will be reflected on each sub-blog.

= What is this HMMultipostMU_children custom field I see on my main post or page? =

The HMMultipostMU_children custom field stores the data for each sub-blog post made from the main blog. This is what maintains the relationship between each main post/page and sub-blog posts/pages. If you remove or alter this custom field, you may be unable to automatically edit/delete related sub-blog posts or pages any longer.

== Screenshots ==

1. Shows the settings page for Multipost MU. Simply a way to choose whether or not all blogs are pre-selected to receive cross posts. 

2. Shows the additional panel displayed on "Add New Post" and "Edit Post" screens. This allows you to choose to which additional blogs your master post is published.

== Changelog ==
= 2.2 =
* BUG FIX: Now "Post to all blogs by default?" option works in WordPress 3.0.1

= 2.1 =
* Fixed conflicting jQuery libraries - props Awolverine   (mirmillo)
  [http://wordpress.org/support/topic/plugin-multipost-mu-tagging-is-disabled-wmultipost-activated?replies=6#post-1642268]

= 2.0 =
* Added support for sticky posts (tmuka)
* Added support for page templates. Note that the feature assumes the selected template file exists on all sites. (tmuka)

= 1.9 =
* Updated for WordPress 3.0 (mirmillo)

= 1.8 =
* Added excerpt and custom field support to posts.
* Added page support. Pages can now be duplicated across multiple blogs.

= 1.7.1 =
* BUG FIX: Corrected bug where edits to auto-generated sub-blog posts were creating NEW posts in other blogs. Now, edits to automatically-generated sub-blog posts will only affect the edited post. Note that editing the master post will continue to overwrite sub-blog post edits. This behavior is by design.

= 1.7 =
* Properly implemented cross-posting. User may now post FROM any blog TO any other blogs to which they have access.
* Removed useless "enable" option from plugin settings page and added option to default to pre-check all blogs when posting, or not.

= 1.6.1 =
* BUG FIX: Corrected bug where master posts were not removed from a sub-blog if a master post was unassigned from the sub-blog by unchecking the sub-blog checkbox during an edit.

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

