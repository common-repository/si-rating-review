<?php 
/* 
Plugin Name: SI Rating & Review 
Plugin URI: 
Description: Allows user to add rating and review with simple on click. 
Version: 1.3
Author: Hiren Patel
Author URI: https://hikebranding.com
License: GPLv2 or later
*/

	global $wpdb;
	include('list_review_rating.php');

/*============================================
	Add settings link on plugin page
============================================*/
	add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), 'SIRR_add_action_links' );
	function SIRR_add_action_links ( $links ) {
	 $mylinks = array(
		'<a href="' . admin_url( 'admin.php?page=edit_rating' ) . '">Settings</a>',
	);
	return array_merge( $links, $mylinks );
	}

/*============================================
				plugin menu
============================================*/
	add_action('admin_menu', 'SIRR_manage_rating_review');
	function SIRR_manage_rating_review(){	
		add_menu_page('SI Rating & Review', 'SI Rating & Review','manage_options', 'edit_rating', 'SIRR_rating_list','dashicons-star-filled');
	}

/*============================================
			plugin activation
============================================*/
	register_activation_hook(__FILE__, 'SIRR_create_plugin_database_table' );
	function SIRR_create_plugin_database_table() {
		ob_start();
		global $wpdb;

		$table_name = $wpdb->prefix . 'si_rating_review';
		$sql = " CREATE TABLE IF NOT EXISTS $table_name (
					`rating_id` int(11) NOT NULL AUTO_INCREMENT,
					`rating_postid` int(11) NOT NULL,
					`rating_posttitle` varchar(255) NOT NULL,
					`rating_postdesc` longtext NOT NULL,
					`rating_count` int(2) NOT NULL,
					`rating_timestamp` varchar(60) NOT NULL,
					`rating_ip` varchar(100) NOT NULL,
					`rating_username` varchar(100) NOT NULL,
					`rating_userid` int(2) NOT NULL,
					`rating_url` varchar(255) NOT NULL,  
					PRIMARY KEY (`rating_id`),
					UNIQUE KEY `rating_id` (`rating_id`)
					) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=0 " ;
		
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );
	}

/*======================================================
		For display on Froentend script and style
======================================================*/
	add_action( 'wp_head', 'SIRR_rating_review_display_script');
	//add_action( 'wp_enqueue_scripts', 'si_rating_review_display_style');
	function SIRR_rating_review_display_script() 
	{
		wp_enqueue_script( 'rating' );    
		wp_enqueue_script( 'rating', plugin_dir_url(__FILE__).'js/si-rating.js');
	}
	add_action( 'wp_head', 'SIRR_myplugin_scripts' );
	function SIRR_myplugin_scripts() {
	    wp_enqueue_style( 'si-rating',  plugin_dir_url(__FILE__) . 'css/si-rating.css' );
	    wp_enqueue_style( 'si-rating' );
	}

/*============================================
			plugin shortcode 
============================================*/
	add_shortcode('si_rating_review','SIRR_frontend_display');
	function SIRR_frontend_display()
	{
		global $wpdb;
		$ip_address=$_SERVER['REMOTE_ADDR'];
?>
		<script language="javascript" type="text/javascript">
			jQuery(document).ready(function(){	
				jQuery('.rating_id').click(function(){
					var status = jQuery(this).attr('id');   
					jQuery('#rating-popup-'+status).show();
					jQuery('.fade-in-div').show();				
				});
				jQuery('#rate-review-link').click(function(){		
					jQuery('#rating-popup').show();
					jQuery('.fade-in').show();				
				});	
				jQuery('.fade-in').click(function(){
					jQuery('.edit-popup').hide();
					jQuery('.fade-in').hide();				
				});
				jQuery('#close').click(function(){
					jQuery('#rating-popup').hide();
					jQuery('.fade-in').hide();				
				});
				jQuery('.fade-in-div').click(function(){
					var status = jQuery(this).attr('id');   
					jQuery('.edit-popup').hide();
					jQuery('.fade-in-div').hide();			
				});
				jQuery('#close-btn').click(function(){
					var status = jQuery(this).attr('id');   
					jQuery('.edit-popup').hide();
					jQuery('.fade-in-div').hide();			
				});
			});
			jQuery(function() {
			    jQuery("#rating_star").codexworld_rating_widget({
			        starLength: '5',
			        initialValue: '<?php echo $ratingNum?>',
			        callbackFunctionName: 'processRating',
			        imageDirectory: '<?php echo plugin_dir_url(__FILE__)."/images/";?>',
			        inputAttr: 'postID'
			    });
			});

			function processRating(val, attrVal){
			    jQuery.ajax({
			        type: 'POST',
			        url: '<?php echo plugin_dir_url(__FILE__)."/si-rating-review.php";?>',
			        data: 'postID='+attrVal+'&ratingPoints='+val,
			        dataType: 'json',
			        success : function(data) {
			            if (data.status == 'ok') {
			                alert('You have rated '+val+' to CodexWorld');
			                jQuery('#avgrat').text(data.average_rating);
			                jQuery('#totalrat').text(data.rating_number);
			            }else{
			                alert('Some problem occured, please try again.');
			            }
			        }
			    });
			}
		</script>
<?php 
	if(isset($_POST['rating_submit']))
	{
		global $wpdb;	
		$current_user = wp_get_current_user();
		if ($current_user->ID==0)
		{
			$user_name='Guest';
			$user_id=0;
		}
		else 
		{
			$user_name=$current_user->user_login;
			$user_id=$current_user->ID;
		}

		$postID = get_the_ID();	
		$page_url=get_permalink($postID);
		$table_name=$wpdb->prefix.'si_rating_review';
		$post_title=get_the_title($postID);
		$timestamp=time();
		$comments= sanitize_text_field($_POST['comments']);
		$ratingPoints = sanitize_text_field($_POST['rating']);   
		$prevRatingQuery = "SELECT * FROM $table_name WHERE rating_ip='".$ip_address."' AND rating_postid='".$postID."'";
		$prevRatingResult = $wpdb->get_results($prevRatingQuery);
		if(count($prevRatingResult) > 0)
		{	
			echo "Sorry! but you have Already Voted";
		} 
		else
		{				
			$query = "INSERT INTO $table_name(rating_postid, rating_posttitle, rating_postdesc, rating_count, rating_timestamp, rating_ip, rating_username, rating_userid, rating_url) VALUES('".$postID."', '".$post_title."', '".$comments."', '".$ratingPoints."', '".$timestamp."', '".$ip_address."', '".$user_name."', '".$user_id."', '".$page_url."')";
			$insert =$wpdb->query($query);
			if($insert)
			{
?>
				<script>
				window.location= "<?php the_permalink();?>"; 
				</script>
<?php 
			}
	    }
	}
?>
	<div>
	<p class="btn">
		<a href="javascript:void(0)" id="rate-review-link">Rate and Review <?php the_title();?></a>
	</p>
</div>
	<div id="rating-popup" style="display:none;">
		<a href="javascript:void(0)" class="pull-right" id="close">X</a>
		<h4>Review and Rating</h4>
<?php 
		$pid=get_the_ID();
		$table_name=$wpdb->prefix.'si_rating_review';
		$prevRatingQuery1 = "SELECT * FROM $table_name WHERE rating_postid='".$pid."'";
		$prevRatingResult1 = $wpdb->get_results($prevRatingQuery1);							
		$already=0;

 		foreach ($prevRatingResult1 as $key => $prevRatingRow1) {
	 		// print_r($prevRatingRow1);
	 	/*}
		while($prevRatingRow1 = mysql_fetch_array($prevRatingResult1,MYSQL_BOTH))
		{*/							
			if ($ip_address == $prevRatingRow1->rating_ip)
			{
				//echo "Sorry! but you have already Voted.<br/>";
				$already=1;
			}							
		}							
		if($already==1) {
			echo "Sorry! but you have already Voted.<br/>";
		}
		else {
?>																
			<form  method="post" name="comment_rating">	
				<input name="rating" value="0" id="rating_star" type="hidden" required/>
				<br/>														
				<textarea cols="25" rows="3" name="comments" value="" required placeholder="Please Enter Some Review "></textarea><br/><br/>
				<input type="submit" class="submit_btn" name="rating_submit" value="Submit Rating"/>
			</form>
<?php 
		}  
?>						 						 
	</div>
<?php
	$table_name=$wpdb->prefix.'si_rating_review';
 	$prevRatingQuery = "SELECT * FROM $table_name WHERE rating_postid=".$pid;
	$prevRatingResult = $wpdb->get_results($prevRatingQuery);
	// $count=mysql_num_rows($prevRatingResult);
	$i=1;
	if(count($prevRatingResult) > 0)
	{
		echo "Other Comments below listed:<br/>";
		//echo '<p>Developed By : <a href="http://satvikinfotech.com/" target="_blank">Satvik Infotech</a></p>';
	}
	echo "
	<div>";
		foreach ($prevRatingResult as $key => $prevRatingRow) {
		// while($prevRatingRow = mysql_fetch_array($prevRatingResult))
		// {
?>
			<script>							
			jQuery(function() {								
				jQuery(".rating_id").click(function() {									
					var status = jQuery(this).attr('id');        							        							
					jQuery('#rating-popup-'+status).show();
					document.getElementById("edit_id").value = status;
				});
				jQuery("#rating_star_<?php echo $i;?>").codexworld_rating_widget({
					starLength: '5',
					initialValue: '<?php echo $prevRatingRow->rating_count?>',
					callbackFunctionName: 'processRating',
					imageDirectory: '<?php echo plugin_dir_url(__FILE__)."/images/";?>',
					inputAttr: 'postID'
				});
			});
			</script>							
			<input name="rating" value="<?php echo esc_attr($prevRatingRow->rating_count);?>" id="rating_star_<?php echo $i ?>" type="hidden" />
			<br/>
<?php  
			echo '
			<div style="margin-bottom:10px;float:left;width:100%;">
			<span><strong>'.$i.".</strong> ".$prevRatingRow->rating_postdesc.'</span>';
?>
			<br/>							
<?php 
			$i++;
			if ( current_user_can( 'administrator' ) ) 
			{
				echo "
				<a class='rating_id' href='javascript:void(0);' id='".$prevRatingRow->rating_id."'>Edit Rating and Review</a>"."<br/>";							
			}
?>						
				<div class="edit-popup" style="display:none" id="rating-popup-<?php echo $prevRatingRow->rating_id;?>">
					<h4>Update Rating and Review </h4>
					<a id="close-btn" href="javascript:void(0);">X</a>
<?php 
					$eid=$prevRatingRow->rating_id;																		
					$edit_rating=$wpdb->get_row("SELECT * from $table_name where rating_id='".$eid."'");
					// echo "<pre>";print_r($edit_rating);
					// $result=mysql_fetch_row($edit_rating,MYSQL_ASSOC);
					if(isset($_POST['update_rating']))
					{							
						$desc=sanitize_text_field($_POST['description']);
						$rating_count=sanitize_text_field($_POST['rating_count']);							
						$current_edit_id=sanitize_text_field($_POST['edit_id']);
						$update_record=$wpdb->query("UPDATE $table_name SET rating_postdesc='$desc', rating_count='$rating_count' where rating_id='$current_edit_id'");
						if($update_record)
						{
							$updated=1;
?>
							<script type="text/javascript">								
								window.location="<?php echo the_permalink();?>";
							</script>
<?php 
						}				
					}
?>
					<form name="update_record" method="post" action="">	
						<input type="hidden" id="edit_id" name="edit_id" value=""/>
						<table>							
							<tr>
								<td>Description</td>
								<td><textarea name="description"><?php  echo esc_textarea($edit_rating->rating_postdesc); ?></textarea></td>
							</tr>
							<tr>
								<td>Rating Count</td>
								<td><input type="text" name="rating_count" value="<?php  echo esc_attr($edit_rating->rating_count);?>" /></td>
							</tr>							
							<tr>
								<td></td>
								<td><input type="submit" name="update_rating" value="Update Record" /></td>
							</tr>
						</table>
					</form>						
				</div>
			</div>
<?php
	 	} 
?>
	</div>
	<div class="fade-in-div" style="display:none;"></div>
<?php
		if(get_option('rating_author_info')==1){
			echo '<p>Developed By : <a href="http://satvikinfotech.com/" target="_blank">Satvik Infotech</a></p>';
		}
}
?>							
							
<?php 
/*=====================================================================================
							add rating to WP posts 
=====================================================================================*/
	add_filter( 'the_content', 'SIRR_add_shortcode_to_post_type',10,1);
	function SIRR_add_shortcode_to_post_type( $content ) 
	{
	  	global $post;
	  	$get_option=array();
	  	$cur_id=get_the_ID();
	  	$cur_post_type = get_post_type($cur_id);
	  	$get_option=get_option('si_rating_post_types');
	  	if(in_array('post', explode(",",$get_option)))
	  	{
			if(is_single() && $cur_post_type=='post')
			{          
				ob_start();
				$new_content = $content;
				$new_content .= do_shortcode('[si_rating_review]');
				$new_content .=  ob_get_clean();
				return $new_content;
			}
	 	}
	 	if (in_array($cur_post_type, explode(",",$get_option)))
	 	{
			if( is_single()){          
				ob_start();
				$new_content = $content;
				$new_content .= do_shortcode('[si_rating_review]');
				$new_content .=  ob_get_clean();
				return $new_content;
			}
	 	}  
	 	if(in_array('page', explode(",",$get_option)))
	 	{
			if(is_page()) {
				ob_start();
				$new_content = $content;
				$new_content .= do_shortcode('[si_rating_review]');
				$new_content .=  ob_get_clean();
				return $new_content;
			} 
	 	} 
	  	return $content;
	}
?>