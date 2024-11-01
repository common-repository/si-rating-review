<?php 
add_action( 'admin_enqueue_scripts', 'SIRR_enable_scripts' );
function SIRR_enable_scripts() {    
	wp_enqueue_script( 'dataTables' );    
	wp_enqueue_script( 'dataTables', plugin_dir_url(__FILE__).'js/jquery.dataTables.min.js');
}
add_action( 'admin_enqueue_scripts', 'SIRR_admin_rating_review_display_style' );
function SIRR_admin_rating_review_display_style() {
	wp_enqueue_style('SIRR_admin_rating_review_display_style', plugin_dir_url(__FILE__). 'css/jquery.dataTables.min.css' );
}
function SIRR_rating_list(){
	global $wpdb;
	$table_name=$wpdb->prefix.'si_rating_review';?>  
	<script type="text/javascript">
		jQuery(document).ready(function(){	
			jQuery('#rating-listing').DataTable({
				"pageLength": 15
			});	
			jQuery('#user-listing').DataTable();
			jQuery('#disable').change(function()
			{
			if(jQuery('#disable').is(':checked')) 
				{            
					jQuery('.author_info').val('1');
				}
				else 
				{
					jQuery('.author_info').val('0');
				}
			});		
		});
	</script>
	<style>
		input[type="text"]{ width:211px; }
	</style>
		<?php   //$review=0;
				if(isset($_GET['delete_id']))
				{
					$did=$_GET['delete_id'];												
					$delete_rating=$wpdb->query("DELETE from $table_name where rating_id='".$did."'");
					if($delete_rating)
					{							
						$review=1;
					}
				}
		?>
	<div class="wrap">   
		<h2>SI Rating & Review </h2>  
		<?php 
		if(isset($_POST['update_post_type']))
		{

			if($_POST['objects']=='')
			{		
				update_option('si_rating_post_types','');
			}
			
			else
			{
				$update_post_type=implode(",",$_POST['objects']);	
				update_option('si_rating_post_types',$update_post_type);
			}	
			
		}
		?>       
		<form method="post" name="" action="">                
	        <table class="form-table1">
	            <tbody>
	                <tr>
	                    <td><?php _e('Select Post Types for Rating'); ?></td> 
	                </tr>
	                <tr>
	                    <td>                           
	                        <?php
	                        $post_types = get_post_types(array(
	                            'show_ui' => true,
	                            'show_in_menu' => true,
	                                ), 'objects');

	                        foreach ($post_types as $post_type) {
	                            if ($post_type->name == 'attachment')
	                                continue;
	                            ?>
	                            <label><input type="checkbox" name="objects[]" value="<?php echo esc_attr($post_type->name); ?>" 
	                            <?php
	                                $get_post_types=explode(",",get_option('si_rating_post_types'));
	                                if (in_array($post_type->name,$get_post_types)) 
	                                	{
	                                        echo 'checked="checked"';
	                                    }
	                                
	                                ?>/><?php echo esc_html($post_type->label); ?></label><br>
	                                <?php
	                            }
	                            ?>
	                            <p><strong>Shortcode for display rating in custom template :</strong> [si_rating_review] </p>
	                    </td>
	                </tr>
	            </tbody>
	        </table>       
	        <input type="submit" class="button-primary" name="update_post_type" value="<?php _e('Update'); ?>">    
	    </form>    
    </div>
	<h3 align="center">List of Rating & Review</h3>	
	<?php if($review==1) echo "<h3 align='center' style='color:red'>Rating has been Deleted.</h3>";?>
	<table class="wp-list-table widefat fixed striped users display" id="rating-listing">
		<thead>	
			<th>Sno.</th>	
			<th>Title</th>
			<th>Description</th>
			<th>Rating Count</th>
			<th>IP Address</th>		
			<th>Delete</th>
		</thead>
		<tbody>	
			<?php 
			// global $wpdb;
			$table_name=$wpdb->prefix.'si_rating_review';
			$results=$wpdb->get_results("SELECT * from ".$table_name." order by rating_id DESC");
			// echo "<pre>"; print_r($result);exit;
			$i=1;	
			foreach ($results as $key => $result) {
			?>
				<tr>	
				<td><?php echo $i;?></td>	
				<td><?php echo $result->rating_posttitle;?></td>
				<td><?php echo $result->rating_postdesc;?></td>	
				<td><?php echo $result->rating_count;?></td>	
				<td><?php echo $result->rating_ip;?></td>	
				<td><a href="?page=edit_rating&delete_id=<?php echo $result->rating_id;?>" class="button" onclick="return confirm('Are you sure to Delete ?');">Delete</a></td>
				</tr>
			<?php
			}
			?>
		</tbody>
		<tfoot>
			<th>Sno.</th>		
			<th>Title</th>
			<th>Description</th>
			<th>Rating Count</th>
			<th>IP Address</th>		
			<th>Delete</th>
		</tfoot>
	</table>
	<?php 
		add_option('rating_author_info',0);
		if(isset($_POST['hide_info']))
		{
			update_option('rating_author_info',esc_html(trim($_POST['author_info'])));	
		}
		$query=$wpdb->get_results('SELECT option_value from wp_options where option_name="rating_author_info"');
		$result=count($query);
		// echo "<pre>"; print_r($result);exit;
	?>
	<form name="author_info" action="" method="post">
		<label>
			<input type="checkbox" id="disable" <?php if($result==1){ ?> checked="true" <?php } ?> name="disable">
			Enable Author info 
		</label>	
		<input type="hidden" value="" name="author_info" class="author_info">
		<input type="submit" name="hide_info" value="Update Option" class="button button-primary">
	</form>
	<div class="develop_by" id="first_list_bx" style="position:absolute;right:0px;padding-right:10px;">
		Developed By <a href="http://www.satvikinfotech.com/" target="_blank" style="text-decoration:none;">Satvik Infotech</a>
	</div>

<?php  } ?>