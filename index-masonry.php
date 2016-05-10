	<?php global $wp_rewrite; ?>

	<div id="ajax-loader-masonry" class="ajax-loader"></div>
	
	<div class="row">
		<div id="masonry">
			<?php $count_ad = 1; if (have_posts()) : while (have_posts()) : the_post(); ?>
			<?php if (isset($_GET['pnum']) && $_GET['pnum'] > 1) { $paged = 2; } //stop ads from repeating in author page - likes section  ?>
					
			<?php if (of_get_option('frontpage1_ad') == $count_ad && of_get_option('frontpage1_ad_code') != '' && ($paged == 0 || $paged == 1 || of_get_option('infinitescroll') == 'disable')) { ?>
			<div class="thumb thumb-ad-wrapper">
				<div class="thumb-ad">
					<?php eval('?>' . of_get_option('frontpage1_ad_code')); ?>
				</div>	 
			</div>
			<?php } ?>
			
			<?php if (of_get_option('frontpage2_ad') == $count_ad && of_get_option('frontpage2_ad_code') != '' && ($paged == 0 || $paged == 1 || of_get_option('infinitescroll') == 'disable')) { ?>
			<div class="thumb thumb-ad-wrapper">
				<div class="thumb-ad">
					<?php eval('?>' . of_get_option('frontpage2_ad_code')); ?>
				</div>	 
			</div>
			<?php } ?>
			
			<?php if (of_get_option('frontpage3_ad') == $count_ad && of_get_option('frontpage3_ad_code') != '' && ($paged == 0 || $paged == 1 || of_get_option('infinitescroll') == 'disable')) { ?>
			<div class="thumb thumb-ad-wrapper">
				<div class="thumb-ad">
					<?php eval('?>' . of_get_option('frontpage3_ad_code')); ?>
				</div>	 
			</div>
			<?php } ?>
			
			<?php if (of_get_option('frontpage4_ad') == $count_ad && of_get_option('frontpage4_ad_code') != '' && ($paged == 0 || $paged == 1 || of_get_option('infinitescroll') == 'disable')) { ?>
			<div class="thumb thumb-ad-wrapper">
				<div class="thumb-ad">
					<?php eval('?>' . of_get_option('frontpage4_ad_code')); ?>
				</div>	 
			</div>
			<?php } ?>
			
			<?php if (of_get_option('frontpage5_ad') == $count_ad && of_get_option('frontpage5_ad_code') != '' && ($paged == 0 || $paged == 1 || of_get_option('infinitescroll') == 'disable')) { ?>
			<div class="thumb thumb-ad-wrapper">
				<div class="thumb-ad">
					<?php eval('?>' . of_get_option('frontpage5_ad_code')); ?>
				</div>	 
			</div>
			<?php } ?>
				
			<?php
			get_template_part('index-masonry-inc');
			$count_ad++;
			endwhile;
			else :
			?>
			<div class="container">
				<div class="row">
					<div class="bigmsg">
						<?php if ($wp->query_vars['pagename'] == 'following') { ?>
						<h4><?php _e('Start following some users or boards to fill this space up', 'ipin'); ?></h4>
						<?php } else { ?>
						<h2><?php _e('Nothing yet.', 'ipin'); ?></h2>
						<?php } ?>
					</div>
				</div>
			</div>
			<?php endif; ?>
		</div>
	</div>

	<?php if(function_exists('wp_pagenavi') && $_GET['view'] != sanitize_title(__('likes', 'ipin'))) { ?>
	<div class="text-center">
		<div id="navigation" class="pagination">
			<?php wp_pagenavi(); ?>
		</div>
	</div>
	<?php } else { ?>
	<div id="navigation">
		<ul class="pager">
			<?php if (isset($_GET['view']) && $_GET['view'] == sanitize_title(__('likes', 'ipin'))) { //from author page - likes section ?>
				<?php
				$username = substr($_SERVER['REQUEST_URI'], strpos($_SERVER['REQUEST_URI'], $wp_rewrite->author_base . '/') + strlen($wp_rewrite->author_base . '/')); //get username from url
				$username = substr($username, 0, strpos($username, '/'));
				$user_info= get_user_by('slug', $username);
				
				$post_likes = get_user_meta($user_info->ID, '_Likes Post ID');
				$pnum = isset($_GET['pnum']) ? intval($_GET['pnum']) : 1;
				$posts_per_page = get_option('posts_per_page');
				$maxpage = ceil(count($post_likes[0])/$posts_per_page);
				?>

				<?php if ($maxpage != 0) { ?>
				<div id="navigation">
					<ul class="pager">
						<?php if ($pnum != 1 && $maxpage >= $pnum) { ?>
						<li id="navigation-previous">
							<a href="<?php echo home_url('/' . $wp_rewrite->author_base . '/' . $username . '/?view=' . sanitize_title(__('likes', 'ipin'))); ?>&amp;pnum=<?php echo $pnum-1; ?>"><?php _e('&laquo; Previous', 'ipin'); ?></a>
						</li>
						<?php } ?>
						
						<?php if ($maxpage != 1 && $maxpage != $pnum) { ?>
						<li id="navigation-next">
							<a href="<?php echo home_url('/' . $wp_rewrite->author_base . '/' . $username . '/?view=' . sanitize_title(__('likes', 'ipin'))); ?>&amp;pnum=<?php echo $pnum+1; ?>"><?php _e('Next &raquo;', 'ipin'); ?></a>
						</li>
						<?php } ?>
					</ul>
				</div>
				<?php } ?>
			<?php } else { ?>
				<li id="navigation-previous"><?php previous_posts_link(__('&laquo; Previous', 'ipin')); ?></li>
				<li id="navigation-next"><?php next_posts_link(__('Next &raquo;', 'ipin')); ?></li>			
			<?php } ?>
		</ul>
	</div>
	<?php } ?>
</div>

<div class="modal" id="post-lightbox" tabindex="-1" aria-hidden="true" role="article"></div>