<?php get_header(); global $user_ID, $wp_rewrite;  ?>
<?php
$user_info = get_user_by('id', $wp_query->query_vars['author']);

?>
<div class="container-fluid">
	<div id="user-wrapper-outer" class="row">
		<div class="container">
			<div class="row">
				<div class="user-wrapper text-center">						
					<h1><?php echo $user_info->display_name; ?></h1>

					<div class="user-avatar text-center">
						<div class="user-avatar-inner">
							<?php echo get_avatar($user_info->ID, '96'); ?>
						</div>

					
					</div>
					
					<p><?php echo $user_info->description; ?></p>
				</div>

				<div class="user-profile-icons text-center">
					

				</div>
			</div>
		</div>
	</div>
	
	<?php 
get_footer();
?>
