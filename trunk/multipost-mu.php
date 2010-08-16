<?php
/*
Plugin Name: Multipost MU
Plugin URI:	http://wordpress.org/extend/plugins/multipost-mu/
Version: v2.2
Author: Warren Harrison
Description: Allow a Wordpress MU site administrator to duplicate posts and pages to multiple sub-blogs at once.
*/

if( !class_exists( 'HMMultipostMU' ) ){

	class HMMultipostMU {
	
		var $defaultToAllOptions = array( 'Yes', 'No' );
		var $enabled;
		var $adminOptionsName = "HMMultipostMUOptions";
	
		function HMMultipostMU(){
			if (isset($_GET['uh'])) {
				$this->userhash = $_GET['uh'];
				//print_r( $_COOKIE );
			}
		}
		
		function getAdminOptions() {
			$adminOptions = array( 'default_to_all' => 'No' );
			$pluginOptions = get_option( $this->adminOptionsName );
			if( !empty( $pluginOptions ) ){
				foreach( $pluginOptions as $key => $value ){
					$adminOptions[$key] = $value;
				}
			}
			update_option( $this->adminOptionsName, $adminOptions );
			return $adminOptions;
		}
		
		function init() {
			$this->getAdminOptions();
		}

		function displayAdminPage(){
			$pluginOptions = $this->getAdminOptions();
			if( isset( $_POST['update_HMMultipostMU'] ) ){
				if( isset( $_POST['default_to_all'] ) ){
					$pluginOptions['default_to_all'] = $_POST['default_to_all'];
				}
				update_option( $this->adminOptionsName, $pluginOptions );
				?>

<div class="updated">
  <p><strong>
    <?php _e("Settings Updated.", "HMMultipostMU");?>
    </strong></p>
</div>
<?php
			}
			?>
<div class="wrap">
  <form method="post" action="<?php echo $_SERVER['REQUEST_URI'] ?>" >
    <h2>Multipost MU</h2>
    <p>This plugin provides the ability to duplicate posts and pages to any other blogs in the same Wordpress MU installation to which the posting user has access. The posts and pages made to each sub-blog will be completely independent posts/pages and are, therefore, fully editable on each sub-blog without affecting the matching posts/pages elsewhere. If you delete or edit the top-level post or page, however, the corresponding post or page on each sb-blog will be modified or deleted.</p>
    <label for="enable">Post to all blogs by default? </label>
    <?php
				foreach( $this->defaultToAllOptions as $defaultToAllOption ){
					$selectedHTML = '';
					if( $pluginOptions['default_to_all'] == $defaultToAllOption ){
						$selectedHTML = 'checked="true"';
					}
					?>
    <input <?php echo $selectedHTML; ?> type="radio" id="default_to_all" name="default_to_all" value="<?php echo $defaultToAllOption; ?>" />
    <?php echo $defaultToAllOption; ?>
    <?php
				}
				?>
    <div class="submit">
      <input type="submit" name="update_HMMultipostMU" value="<?php _e('Update Settings', 'HMMultipostMU') ?>" />
    </div>
  </form>
</div>
<?php
		}

		/*********************************************************************************
		MULTIPOST FOR POSTS
		*********************************************************************************/
		function multiPost( $postID ) {
			global $switched, $blog_id, $current_user;
			get_currentuserinfo();
			// ensure multipost is only triggered from source blog to prevent massive cascade of posts
			if( $blog_id != $_POST['HMMPMU_source_blog_id'] ){
				return false;
			}
			// get existing child posts, if any
			$childPosts = unserialize( get_post_meta( $postID, 'HMMultipostMU_children', true ) );
			if( empty( $childPosts ) ) {
				$childPosts = array(); // key = blog_id, val = post_id
			}
			// get post
			$thisPost = get_post( $postID );
			$postCustomFields = get_post_custom( $postID );
			unset( $postCustomFields['HMMultipostMU_children'] ); // we don't want to copy this one to sub-blogs... rippled chaos will ensue!
			unset( $postCustomFields['_edit_lock'] ); 
			unset( $postCustomFields['_edit_last'] );
			$thisPostTags = wp_get_post_tags( $postID );
			// get array of categories (need ->name parameter)
			$thisPostCategories = wp_get_object_terms( $postID, 'category' );
			$masterPostCats = array();
			// pull category id/name into array for easier searching
			foreach( $thisPostCategories as $thisPostCategory ) {
				$masterPostCats[$thisPostCategory->term_id] = $thisPostCategory->name;
			}
			$thisPostTags_string = '';
			foreach( $thisPostTags as $thisPostTag ) {
				$thisPostTags_string .= $thisPostTag->name .',';
			}
			$thisPostTags_string = trim( $thisPostTags_string, ',' );
			// create post object with this post's data
			$dupePost = array(
				'post_title' => $thisPost->post_title, 
				'post_content' => $thisPost->post_content, 
				'post_status' => $thisPost->post_status, 
				'post_author' => $thisPost->post_author, 
				'post_excerpt' => $thisPost->post_excerpt, 
				'tags_input' => $thisPostTags_string
			);
			//check if post is sticky
			$sticky = is_sticky($postID);
			
			// get list of blogs
			//$subBlogs = get_blog_list( 0, 'all' );
			$subBlogs = get_blogs_of_user( $current_user->ID );
			// get the subBlogs in chronological order as get_blog_list() pulls in reverse cron order
			
			foreach( $subBlogs as $subBlog ) {
				// if user selected specific blogs in which to post and this blog isn't among them, skip to next
				if( !empty( $_POST['HMMPMU_selectedSubBlogs'] ) && !in_array( $subBlog->userblog_id, $_POST['HMMPMU_selectedSubBlogs'] ) ) {
					// if a previous post exists on this blog, but isnt now needed, delete it
					if( in_array( $subBlog->userblog_id, array_keys( $childPosts ) ) ) {
						if( switch_to_blog( $subBlog->userblog_id ) === true ) { 
							wp_delete_post( $childPosts[$subBlog->userblog_id] );
							// jump back to master blog
							restore_current_blog();
							unset( $childPosts[$subBlog->userblog_id] );
						}
					}
					continue;
				}
				if( $blog_id != $subBlog->userblog_id ) { // skip the current blog
					$childPostID = 0;	// used to hold new/updated post for each sub-blog
					// switch each sub-blog
					if( switch_to_blog( $subBlog->userblog_id ) === true ) { 
							if( isset( $childPosts[$subBlog->userblog_id] ) ) {
								// there is already an existing post for this blog
								$dupePost['ID'] = $childPosts[$subBlog->userblog_id];	// set post ID
								$childPostID = wp_update_post( $dupePost );
								unset( $dupePost['ID'] );	// remove post ID from duped post object
							} else {
								// no existing post for this blog, and was checked, create a new post
								if( !empty( $_POST['HMMPMU_selectedSubBlogs'] ) && in_array( $subBlog->userblog_id, $_POST['HMMPMU_selectedSubBlogs'] ) ) {
									$childPostID = wp_insert_post( $dupePost );
								}
							}
							if( $childPostID > 0 ) {
								// get the new post's object
								$childPost = get_post( $childPostID );
								// get existing categories for this blog
								$childBlogCats = get_terms( 'category' );
								// if matching category found, add post to it
								$matchingCatID = 0;
								$childCatsToAdd = array();
								foreach( $masterPostCats as $masterPostCats_key=>$masterPostCats_value ) {
									$matchingTerm = get_term_by( 'name', $masterPostCats_value, 'category' );
									if( $matchingTerm === false ) {
										// create new term/category
										$newCatID = wp_create_category( $masterPostCats_value );
										$matchingTerm = get_term( $newCatID, 'category' );
									}
									array_push( $childCatsToAdd, $matchingTerm->term_id );
								}
								// add terms/categories to post
								wp_set_post_categories( $childPostID, $childCatsToAdd );
								// update or set custom fields
								foreach( $postCustomFields as $postCustomFieldKey=>$postCustomFieldValue ) {
									//update existing custom field (this adds first if fields does not yet exist)
									foreach( $postCustomFieldValue as $postCustomFieldValueItem ){
										update_post_meta( $childPostID, $postCustomFieldKey, $postCustomFieldValueItem );
									}
								}
								// if the update/new post was successful, add it to the array of child posts
								$childPosts[$subBlog->userblog_id] = $childPostID;
								
								// if the original post was sticky, set the new one sticky. otherwise remove sticky.
								if($sticky === true){
									stick_post($childPostID);	
								} elseif(is_sticky($childPostID)===true) {
									unstick_post($childPostID);
								}
							}
						// jump back to master blog
						restore_current_blog();
					}
				}
			} /* /foreach */
			
			// add list of child posts to master post as metadata
			if( !empty( $childPosts ) ) {
				update_post_meta( $postID, 'HMMultipostMU_children', serialize( $childPosts ) );
			}
		}

		/*********************************************************************************
		MULTIPOST FOR PAGES
		*********************************************************************************/
		function multiPostPage( $postID ) {
			global $switched, $blog_id, $current_user;
			get_currentuserinfo();
			// ensure multipost is only triggered from source blog to prevent massive cascade of posts
			if( $blog_id != $_POST['HMMPMU_source_blog_id'] ) {
				return false;
			}
			// get existing child pages, if any
			$childPages = unserialize( get_post_meta( $postID, 'HMMultipostMU_children', true ) );
			if( empty( $childPages ) ) {
				$childPages = array(); // key = blog_id, val = post_id
			}
			
			// get page template setting
			$template_filename = get_post_meta( $postID, '_wp_page_template', true );
			//die("DEBUG: tf = $template_filename");
			
			// get page
			$thisPage = get_page( $postID, ARRAY_A );
			// create page object with this page's data
			$dupePage = $thisPage;
			// get the parent page (we'll need this later)
			if( $thisPage['post_parent'] > 0 ) {
				// get the parent page and get it's multipost children
				$parentsChildPages = unserialize( get_post_meta( $thisPage['post_parent'], 'HMMultipostMU_children', true ) );
				if( empty( $parentsChildPages ) ) {
					$parentsChildPages = array(); // key = blog_id, val = post_id
				}
			}
			unset( $dupePage['post_parent'] );
			unset( $dupePage['ID'] );
			unset( $dupePage['guid'] );
			/*
			echo "<pre>";
			print_r( $dupePage );
			echo "</pre>";
			*/
			// get list of blogs
			//$subBlogs = get_blog_list( 0, 'all' );
			$subBlogs = get_blogs_of_user( $current_user->ID );
			// get the subBlogs in chronological order as get_blog_list() pulls in reverse cron order
			foreach( $subBlogs as $subBlog ){
				// if user selected specific blogs in which to page and this blog isn't among them, skip to next
				if( !empty( $_POST['HMMPMU_selectedSubBlogs'] ) && !in_array( $subBlog->userblog_id, $_POST['HMMPMU_selectedSubBlogs'] ) ) {
					// if a previous page exists on this blog, but isnt now needed, delete it
					if( in_array( $subBlog->userblog_id, array_keys( $childPages ) ) ) {
						if( switch_to_blog( $subBlog->userblog_id ) === true ) { 
							wp_delete_post( $childPages[$subBlog->userblog_id] );
							// jump back to master blog
							restore_current_blog();
							unset( $childPages[$subBlog->userblog_id] );
						}
					}
					continue;
				}
				if( $blog_id != $subBlog->userblog_id ) { // skip the current blog
					$childPageID = 0;	// used to hold new/updated page for each sub-blog
					// switch each sub-blog
					if( switch_to_blog( $subBlog->userblog_id ) === true ) { 
							// if the current page has a valid parent, set the parent accordingly
							if( isset( $parentsChildPages[$subBlog->userblog_id] ) ) {
								$dupePage['post_parent'] = $parentsChildPages[$subBlog->userblog_id];	// set parent ID
							}
							if( isset( $childPages[$subBlog->userblog_id] ) ) {
								// there is already an existing page for this blog
								$dupePage['ID'] = $childPages[$subBlog->userblog_id];	// set post ID
								$childPageID = wp_update_post( $dupePage );
								unset( $dupePage['ID'] );	// remove page ID from duped page object
							}else{
								// no existing page for this blog, and was checked, create a new page
								if( !empty( $_POST['HMMPMU_selectedSubBlogs'] ) && in_array( $subBlog->userblog_id, $_POST['HMMPMU_selectedSubBlogs'] ) ) {
									$childPageID = wp_insert_post( $dupePage );
								}
							}
							if( $childPageID > 0 ){
								// get the new pages's object
								$childPage = get_page( $childPageID );
								// if the update/new post was successful, add it to the array of child posts
								$childPages[$subBlog->userblog_id] = $childPageID;
							}
							
							// set the meta for the page template too same as original
							// todo: might be worthwhile to check if the template file exists in the active theme before changing from "default".
							if(!empty($template_filename)){
								update_post_meta( $childPageID, '_wp_page_template', $template_filename);
							}
							
						// jump back to master blog
						restore_current_blog();
					}
				}
			}
			// add list of child posts to master post as metadata
			if( !empty( $childPages ) ) {
				update_post_meta( $postID, 'HMMultipostMU_children', serialize( $childPages ) );
			}
		}

		/*********************************************************************************
		DELETE MULTIPOST
		*********************************************************************************/
		function deleteMultiPost( $postID ) {
			global $switched;
			// get existing child posts, if any
			$childPosts = unserialize( get_post_meta( $postID, 'HMMultipostMU_children', true ) );
			if( !is_array( $childPosts ) ) {
				return false;
			}
			foreach( $childPosts as $blogID => $blogPostID ) {
				// switch each sub-blog
				if( switch_to_blog( $blogID ) === true ) { 
					wp_delete_post( $blogPostID );
					// jump back to master blog
					restore_current_blog();
				}
			}
		}
	}	
} // End HMMultipostMU class

if ( class_exists( 'HMMultipostMU' ) ) {
	$hmMultipostMU = new HMMultipostMU();
}

if( !function_exists( 'HMMultipostMU_op' ) ){
	function HMMultipostMU_op(){
		global $hmMultipostMU;
		if( !isset( $hmMultipostMU ) ){
			return;
		}
		if( function_exists( 'add_options_page' ) ) {
			add_options_page( 'Multipost MU', 'Multipost MU', 'publish_posts', basename( __FILE__ ), array( &$hmMultipostMU, 'displayAdminPage' ) );
		}
	}
}

if( !function_exists( 'HMMultipostMU_postUI' ) ) {
	function HMMultipostMU_postUI(){
		global $hmMultipostMU, $blog_id;
		//if( !isset( $hmMultipostMU ) || $blog_id > 1 ) {
		if( !isset( $hmMultipostMU ) ) {
			return;
		}
		if( function_exists( 'add_meta_box' ) ){
			add_meta_box('HMMPMU_meta', 'Multipost', 'HMMPMU_showSubBlogBoxes', 'post', 'side', 'low' );
			add_meta_box('HMMPMU_meta', 'Multipost', 'HMMPMU_showSubBlogBoxes', 'page', 'side', 'low' );
		}
	}
}

function HMMPMU_showSubBlogBoxes( $post ) {
	global $current_user, $blog_id, $hmMultipostMU;
	wp_enqueue_script('jquery');
	$pluginOptions = $hmMultipostMU->getAdminOptions();
	?>
<script type="text/javascript">
  jQuery(document).ready( function($){
		jQuery('#HMMPMU_checkall').click( function(e){
			e.preventDefault();
			HMMPMU_check( 'check' );
		});
		jQuery('#HMMPMU_checknone').click( function(e){
			e.preventDefault();
			HMMPMU_check( 'uncheck' );
		});
	});
	function HMMPMU_check( action ){
		if( action == 'check' ){
			jQuery('.HMMPMU_selectedSubBlogs_checkbox').attr('checked', 'true');
		}else{
			jQuery('.HMMPMU_selectedSubBlogs_checkbox[disabled!=true]').removeAttr('checked');
		}
	}

  </script>
<input type="hidden" name="HMMPMU_source_blog_id" value="<?php echo $blog_id; ?>" />
<p style="float: right; font-size: 0.8em;">Check <a href="#" id="HMMPMU_checkall">all</a> / <a href="#"id="HMMPMU_checknone">none</a></p>
<p>Post to:</p>
<?php
	get_currentuserinfo();
	// get existing child posts, if any
	// in wp 3.0.1 it looks like ID is never 0 so this runs every time which is probably fine, just a bit slower for new posts
	if( $post->ID > 0 ) {
		 $childBlogs = unserialize( get_post_meta( $post->ID, 'HMMultipostMU_children', true ) );
		if( !empty( $childBlogs ) ) { 
			$childPostBlogIDs = array_keys( $childBlogs );
		} else {
			$childPostBlogIDs = array();
		}
	}
	
	$is_new_post = empty($childPostBlogIDs);
	
	$oSubBlogs = get_blogs_of_user( $current_user->ID );
	$subBlogs = array();
	foreach( $oSubBlogs as $oSubBlog ) {
		$subBlogs[$oSubBlog->userblog_id] = $oSubBlog->blogname;
	}
	asort( $subBlogs, SORT_STRING );
	foreach( $subBlogs as $subBlogID => $subBlogName ) {
			$checkedHTML = '';
			$disabledHTML = '';
			//updated for wp 3.0.1 since it looks like we dont get $post->ID == 0 now. on new post creation it already has an id.
			if(( $is_new_post && $pluginOptions['default_to_all'] == 'Yes' ) || ( is_array( $subBlogs ) && in_array( $subBlogID, $childPostBlogIDs ))){
			//if( ( $post->ID == 0 && $pluginOptions['default_to_all'] == 'Yes' ) || ( is_array( $subBlogs ) && in_array( $subBlogID, $childPostBlogIDs ) ) ) {
			//if( $post->ID == 0 && $pluginOptions['default_to_all'] == 'Yes' ) {
				$checkedHTML = 'checked="true"';
			}
			if( (int)$subBlogID == (int)$blog_id ) {
				$checkedHTML = 'checked="true"';
				$disabledHTML = 'disabled = "true"';
			}
		
			?>
      <input type="checkbox" 
              class="HMMPMU_selectedSubBlogs_checkbox" 
              name="HMMPMU_selectedSubBlogs[]" 
              <?php echo $checkedHTML; ?>
              <?php echo $disabledHTML; ?>
              value="<?php echo $subBlogID; ?>" />
            <?php //$currentBlog = get_blog_details( $subBlog->userblog_id );
            echo $subBlogName;?>
      <br />
      <?php
	} /* /foreach */
}


// Actions & Filters
if( isset( $hmMultipostMU ) ) {
	// Actions
	add_action('multipost-mu/multipost-mu.php',  array(&$hmMultipostMU, 'init')); 
	add_action('admin_menu', 'HMMultipostMU_op'); 
	add_action('admin_menu', 'HMMultipostMU_postUI');  
    add_action('publish_page', array(&$hmMultipostMU, 'multiPostPage'), 1);
	add_action('publish_post', array(&$hmMultipostMU, 'multiPost'), 1);
	add_action('delete_post', array(&$hmMultipostMU, 'deleteMultiPost'), 1);
	add_action('delete_page', array(&$hmMultipostMU, 'deleteMultiPost'), 1); 
	// Filters
}
?>
