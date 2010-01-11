<?php
/*
Plugin Name: Multipost MU
Plugin URI:	http://wordpress.org/extend/plugins/multipost-mu/
Version: v1.7
Author: Warren Harrison
Description: Allows Wordpress MU publishers to cross-post to multiple blogs at once.

*/

if( !class_exists( 'HMMultipostMU' ) ){

	class HMMultipostMU{
	
		var $defaultToAllOptions = array( 'Yes', 'No' );
		var $enabled;
		var $adminOptionsName = "HMMultipostMUOptions";
	
		function HMMultipostMU(){
			$this->userhash = $_GET['uh'];
//print_r( $_COOKIE );
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
				<div class="updated"><p><strong><?php _e("Settings Updated.", "HMMultipostMU");?></strong></p></div>
        <?php
			}
			?>
			<div class="wrap">
			<form method="post" action="<?php echo $_SERVER['REQUEST_URI'] ?>" >
				<h2>Multipost MU</h2>
        <p>This plugin provides the ability to cross-post to any other blogs in the same Wordpress MU installation to which the posting user has access. The posts made to each sub-blog will be completely independent posts and are, therefore, fully editable on each sub-blog without affecting the matching posts elsewhere.</p>
				<label for="enable">Post to all blogs by default? </label>
        <?php
				foreach( $this->defaultToAllOptions as $defaultToAllOption ){
					$selectedHTML = '';
					if( $pluginOptions['default_to_all'] == $defaultToAllOption ){
						$selectedHTML = 'checked="true"';
					}
					?>
        <input <?php echo $selectedHTML; ?> type="radio" id="default_to_all" name="default_to_all" value="<?php echo $defaultToAllOption; ?>" /> <?php echo $defaultToAllOption; ?>
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
		function multiPost( $postID ){
			global $switched, $blog_id;
			// ensure multipost is only triggered from source blog to prevent massive cascade of posts
			if( $blog_id != $_POST['HMMPMU_source_blog_id'] ){
				return false;
			}
			// get existing child posts, if any
			$childPosts = unserialize( get_post_meta( $postID, 'HMMultipostMU_children', true ) );
			if( empty( $childPosts ) ){
				$childPosts = array(); // key = blog_id, val = post_id
			}
			// get post
			$thisPost = get_post( $postID );
			$thisPostTags = wp_get_post_tags( $postID );
			// get array of categories (need ->name parameter)
			$thisPostCategories = wp_get_object_terms( $postID, 'category' );
			$masterPostCats = array();
			// pull category id/name into array for easier searching
			foreach( $thisPostCategories as $thisPostCategory ){
				$masterPostCats[$thisPostCategory->term_id] = $thisPostCategory->name;
			}
			$thisPostTags_string = '';
			foreach( $thisPostTags as $thisPostTag ){
				$thisPostTags_string .= $thisPostTag->name .',';
			}
			$thisPostTags_string = trim( $thisPostTags_string, ',' );
			// create post object with this post's data
			$dupePost = array(
				'post_title' => $thisPost->post_title, 
				'post_content' => $thisPost->post_content, 
				'post_status' => $thisPost->post_status, 
				'post_author' => $thisPost->post_author, 
				'tags_input' => $thisPostTags_string
			);
			// get list of blogs
			$subBlogs = get_blog_list( 0, 'all' );
/*
echo "<pre>";
print_r( $subBlogs );
echo "</pre>";
*/
			// get the subBlogs in chronological order as get_blog_list() pulls in reverse cron order
			foreach( $subBlogs as $subBlog ){
				// if user selected specific blogs in which to post and this blog isn't among them, skip to next
				if( !empty( $_POST['HMMPMU_selectedSubBlogs'] ) && !in_array( $subBlog['blog_id'], $_POST['HMMPMU_selectedSubBlogs'] ) ){
					// if a previous post exists on this blog, but isnt now needed, delete it
					if( in_array( $subBlog['blog_id'], array_keys( $childPosts ) ) ){
						if( switch_to_blog( $subBlog['blog_id'] ) === true ){ 
							wp_delete_post( $childPosts[$subBlog['blog_id']] );
							// jump back to master blog
							restore_current_blog();
							unset( $childPosts[$subBlog['blog_id']] );
						}
					}
					continue;
				}
				if( $blog_id != $subBlog['blog_id'] ){ // skip the current blog
					$childPostID = 0;	// used to hold new/updated post for each sub-blog
					// switch each sub-blog
					if( switch_to_blog( $subBlog['blog_id'] ) === true ){ 
							if( isset( $childPosts[$subBlog['blog_id']] ) ){
								// there is already an existing post for this blog
								$dupePost['ID'] = $childPosts[$subBlog['blog_id']];	// set post ID
								$childPostID = wp_update_post( $dupePost );
								unset( $dupePost['ID'] );	// remove post ID from duped post object
							}else{
								// no existing post for this blog, create a new post
								$childPostID = wp_insert_post( $dupePost );
							}
							if( $childPostID > 0 ){
								// get the new post's object
								$childPost = get_post( $childPostID );
								// get existing categories for this blog
								$childBlogCats = get_terms( 'category' );
								// if matching category found, add post to it
								$matchingCatID = 0;
								$childCatsToAdd = array();
								foreach( $masterPostCats as $masterPostCats_key=>$masterPostCats_value ){
									$matchingTerm = get_term_by( 'name', $masterPostCats_value, 'category' );
									if( $matchingTerm === false ){
										// create new term/category
										$newCatID = wp_create_category( $masterPostCats_value );
										$matchingTerm = get_term( $newCatID, 'category' );
									}
									array_push( $childCatsToAdd, $matchingTerm->term_id );
								}
								// add terms/categories to post
								wp_set_post_categories( $childPostID, $childCatsToAdd );
								// if the update/new post was successful, add it to the array of child posts
								$childPosts[$subBlog['blog_id']] = $childPostID;
							}
						// jump back to master blog
						restore_current_blog();
					}
				}
			}
			// add list of child posts to master post as metadata
			update_post_meta( $postID, 'HMMultipostMU_children', serialize( $childPosts ) );
		}

		function deleteMultiPost( $postID ){
			global $switched;
			// get existing child posts, if any
			$childPosts = unserialize( get_post_meta( $postID, 'HMMultipostMU_children', true ) );
			if( !is_array( $childPosts ) ){
				return false;
			}
			foreach( $childPosts as $blogID => $blogPostID ){
				// switch each sub-blog
				if( switch_to_blog( $blogID ) === true ){ 
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
		if( function_exists( 'add_options_page' ) ){
			add_options_page( 'Multipost MU', 'Multipost MU', 9, basename( __FILE__ ), array( &$hmMultipostMU, 'displayAdminPage' ) );
		}
	}
}

if( !function_exists( 'HMMultipostMU_postUI' ) ){
	function HMMultipostMU_postUI(){
		global $hmMultipostMU, $blog_id;
//		if( !isset( $hmMultipostMU ) || $blog_id > 1 ){
		if( !isset( $hmMultipostMU ) ){
			return;
		}
		if( function_exists( 'add_meta_box' ) ){
			add_meta_box('HMMPMU_meta', 'Multipost', 'HMMPMU_showSubBlogBoxes', 'post', 'side', 'low' );
		}
	}
}

function HMMPMU_showSubBlogBoxes( $post ){
	global $user_ID, $blog_id, $hmMultipostMU;
	$pluginOptions = $hmMultipostMU->getAdminOptions();
	?>
  <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.3.2/jquery.min.js"></script>
  <script type="text/javascript">
  $(document).ready( function(){
		$('#HMMPMU_checkall').click( function(e){
			e.preventDefault();
			HMMPMU_check( 'check' );
		});
		$('#HMMPMU_checknone').click( function(e){
			e.preventDefault();
			HMMPMU_check( 'uncheck' );
		});
	});
	function HMMPMU_check( action ){
		if( action == 'check' ){
			$('.HMMPMU_selectedSubBlogs_checkbox').attr('checked', 'true');
		}else{
			$('.HMMPMU_selectedSubBlogs_checkbox[disabled!=true]').removeAttr('checked');
		}
	}

  </script>
  <input type="hidden" name="HMMPMU_source_blog_id" value="<?php echo $blog_id; ?>" />
	<p style="float: right; font-size: 0.8em;">Check <a href="#" id="HMMPMU_checkall">all</a> / <a href="#"id="HMMPMU_checknone">none</a></p>
  <p>Post to:</p>
	<?php
	get_currentuserinfo();
	// get existing child posts, if any
	if( $post->ID > 0 ){
		$childBlogs = unserialize( get_post_meta( $post->ID, 'HMMultipostMU_children', true ) );
		if( !empty( $childBlogs ) ){ 
			$childPostBlogIDs = array_keys( $childBlogs );
		}
	}
	$oSubBlogs = get_blogs_of_user( $user_ID );
	$subBlogs = array();
	foreach( $oSubBlogs as $oSubBlog ){
		$subBlogs[$oSubBlog->userblog_id] = $oSubBlog->blogname;
	}
	asort( $subBlogs, SORT_STRING );
	foreach( $subBlogs as $subBlogID => $subBlogName ){
			$checkedHTML = '';
			$disabledHTML = '';
			if( ( $post->ID == 0 && $pluginOptions['default_to_all'] == 'Yes' ) || ( is_array( $childPostBlogIDs ) && in_array( $subBlogID, $childPostBlogIDs ) ) ){
				$checkedHTML = 'checked="true"';
			}
			if( (int)$subBlogID == (int)$blog_id ){
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
			<?php 
//			$currentBlog = get_blog_details( $subBlog->userblog_id );
			echo $subBlogName;?><br />
			<?php
	}
}


// Actions & Filters
if( isset( $hmMultipostMU ) ){
	// Actions
	add_action('multipost-mu/multipost-mu.php',  array(&$hmMultipostMU, 'init')); 
	add_action('admin_menu', 'HMMultipostMU_op'); 
	add_action('admin_menu', 'HMMultipostMU_postUI');  
	add_action('publish_post', array(&$hmMultipostMU, 'multiPost'), 1);
	add_action('delete_post', array(&$hmMultipostMU, 'deleteMultiPost'), 1);
	// Filters
	
}


?>
