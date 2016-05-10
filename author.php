<?php get_header(); global $user_ID, $wp_rewrite;  ?>
<?php
$user_info = get_user_by('id', $wp_query->query_vars['author']);

$blog_cat_id = of_get_option('blog_cat_id');
if ($blog_cat_id) {
	$blog_post_count = $wpdb->get_var(
		$wpdb->prepare(
			"SELECT COUNT(*) FROM $wpdb->posts
			LEFT JOIN $wpdb->term_relationships ON($wpdb->posts.ID = $wpdb->term_relationships.object_id)
			LEFT JOIN $wpdb->term_taxonomy ON($wpdb->term_relationships.term_taxonomy_id = $wpdb->term_taxonomy.term_taxonomy_id)
			WHERE $wpdb->term_taxonomy.term_id = %d
			AND $wpdb->term_taxonomy.taxonomy = 'category'
			AND $wpdb->posts.post_status = 'publish'
			AND post_author = %d
			"
			, $blog_cat_id, $user_info->ID
		)
	);
}

$pins_count = count_user_posts($user_info->ID) - $blog_post_count;
$parent_board_id = get_user_meta($user_info->ID, '_Board Parent ID', true);
$parent_board = get_term_by('id', $parent_board_id, 'board', ARRAY_A);
if ($parent_board_id == '') {
	$boards_count = 0;
} else {
	$boards = get_terms('board', array('parent' => $parent_board_id, 'hide_empty' => false, 'orderby' => 'id', 'order' => 'DESC'));
	$boards_count = count($boards);
}

$likes_count = get_user_meta($user_info->ID, '_Likes Count', true);
$likes_count = $likes_count ? $likes_count : 0;
$followers_count = get_user_meta($user_info->ID, '_Followers Count', true);
$followers_count = $followers_count ? $followers_count : 0;
$following_count = get_user_meta($user_info->ID, '_Following Count', true);
$following_count = $following_count ? $following_count : 0;

$profile_cover_id = get_user_meta($user_info->ID, 'ipin_user_cover', true);
if ($profile_cover_id != '') {
	$profile_cover = wp_get_attachment_image_src($profile_cover_id, 'full');
	$profile_cover_bg = ' style="background-image: url(\'' . $profile_cover[0] . '\');"';
}
?>
<div class="container-fluid">
	<div id="user-wrapper-outer" class="row"<?php echo $profile_cover_bg; ?>>
		<div class="container">
			<div class="row">
				<div class="user-wrapper text-center">						
					<h1><?php echo $user_info->display_name; ?></h1>

					<div class="user-avatar text-center">
						<div class="user-avatar-inner">
							<?php echo get_avatar($user_info->ID, '96'); ?>
						</div>

						<?php if ($top_user_followers_pos = ipin_top_user_by_followers($user_info->ID)) { ?>
							<a id="user-profile-top-follower" href="<?php echo home_url('/top-users/'); ?>"><span class="label label-warning top-user-count-alt1"><?php _e('Most Followers', 'ipin'); ?> #<?php echo $top_user_followers_pos; ?></span></a> 
						<?php } ?>
		
						<?php if ($top_user_pins_pos = ipin_top_user_by_pins($user_info->ID)) { ?>
							<a id="user-profile-top-pin" href="<?php echo home_url('/top-users/'); ?>"><span class="label label-warning top-user-count-alt2"><?php _e('Most Pins', 'ipin'); ?> #<?php echo $top_user_pins_pos; ?></span></a>
						<?php } ?>
					</div>
					
					<p><?php echo $user_info->description; ?></p>
				</div>

				<div class="user-profile-icons text-center">
					<?php if ($user_info->ipin_user_facebook) { ?>
					<a href="http://www.facebook.com/<?php echo esc_attr($user_info->ipin_user_facebook); ?>" target="_blank"><i class="fa fa-facebook-square fa-lg"></i></a> 
					<?php } ?>

					<?php if ($user_info->ipin_user_twitter) { ?>
					<a href="http://twitter.com/<?php echo esc_attr($user_info->ipin_user_twitter); ?>" target="_blank"><i class="fa fa-twitter-square fa-lg"></i></a> 
					<?php } ?>

					<?php if ($user_info->ipin_user_pinterest) { ?>
					<a href="http://pinterest.com/<?php echo esc_attr($user_info->ipin_user_pinterest); ?>" target="_blank"><i class="fa fa-pinterest-square fa-lg"></i></a> 
					<?php } ?>

					<?php if ($user_info->ipin_user_googleplus) { ?>
					<a href="http://plus.google.com/<?php echo esc_attr($user_info->ipin_user_googleplus); ?>" target="_blank"><i class="fa fa-google-plus-square fa-lg"></i></a> 
					<?php } ?>
					
					<?php if ($user_info->user_url) { ?>
					<a href="<?php echo esc_url($user_info->user_url); ?>" target="_blank"><i class="fa fa-globe fa-lg"></i> <?php echo parse_url($user_info->user_url, PHP_URL_HOST); ?></a> 
					<?php } ?>

					<?php if ($user_info->ipin_user_location) { ?>
					<a href="http://maps.google.com/?q=<?php echo rawurlencode($user_info->ipin_user_location); ?>" target="_blank"><i class="fa fa-map-marker fa-lg"></i> <?php echo esc_attr($user_info->ipin_user_location); ?></a> 
					<?php } ?>
				</div>
			</div>
		</div>
	</div>
	
	<div id="userbar" class="row">
		<ul class="nav">
			<li<?php if (!isset($_GET['view'])) { echo ' class="active"'; } ?>><a href="<?php echo get_author_posts_url($user_info->ID); ?>"><?php _e('Boards', 'ipin'); ?><br /><strong><?php echo $boards_count; ?></strong></a></li>
			<li<?php if (isset($_GET['view']) && $_GET['view'] == sanitize_title(__('pins', 'ipin'))) { echo ' class="active"'; } ?>><a href="<?php echo get_author_posts_url($user_info->ID); ?>?view=<?php echo sanitize_title(__('pins', 'ipin')); ?>"><?php _e('Pins', 'ipin'); ?><br /><strong><?php echo $pins_count; ?></strong></a></li>
			<li<?php if (isset($_GET['view']) && $_GET['view'] == sanitize_title(__('likes', 'ipin'))) { echo ' class="active"'; } ?>><a href="<?php echo get_author_posts_url($user_info->ID); ?>?view=<?php echo sanitize_title(__('likes', 'ipin')); ?>"><?php _e('Likes', 'ipin'); ?><br /><strong><?php echo $likes_count; ?></strong></a></li>
			<li<?php if (isset($_GET['view']) && $_GET['view'] == sanitize_title(__('followers', 'ipin'))) { echo ' class="active"'; } ?>><a href="<?php echo get_author_posts_url($user_info->ID); ?>?view=<?php echo sanitize_title(__('followers', 'ipin')); ?>"><?php _e('Followers', 'ipin'); ?><br /><strong id="ajax-follower-count"><?php echo $followers_count; ?></strong></a></li>
			<li style="margin-right:0;"<?php if (isset($_GET['view']) && $_GET['view'] == sanitize_title(__('following', 'ipin'))) { echo ' class="active"'; } ?>><a href="<?php echo get_author_posts_url($user_info->ID); ?>?view=<?php echo sanitize_title(__('following', 'ipin')); ?>"><?php _e('Following', 'ipin'); ?><br /><strong><?php echo $following_count; ?></strong></a></li>
			<li>
			<?php if ($user_info->ID != $user_ID) {	?>
				<span class="undisable_buttons">
				<button class="btn btn-success btn-sm follow ipin-follow<?php if ($followed = ipin_followed($parent_board['term_id'])) { echo ' disabled'; } ?>" data-author_id="<?php echo $user_info->ID ?>" data-board_id="<?php echo $parent_board['term_id'];  ?>" data-board_parent_id="<?php echo $parent_board['parent']; ?>" data-disable_others="no" type="button"><?php if (!$followed) { _e('Follow', 'ipin'); } else { _e('Unfollow', 'ipin'); } ?></button>
				</span>
			<?php } ?>
				<div class="ipin-share btn-group">
					<button type="button" class="btn btn-success btn-sm follow dropdown-toggle" data-toggle="dropdown">
						<i class="fa fa-share-alt"></i> <span class="caret"></span>
					</button>
					
					<ul class="dropdown-menu">
						<li><a href="" onclick="window.open('https://www.facebook.com/sharer/sharer.php?u=<?php echo rawurlencode(home_url('/') . $wp_rewrite->author_base . '/' . $user_info->user_nicename . '/'); ?>', 'facebook-share-dialog', 'width=626,height=500'); return false;"><i class="fa fa-facebook-square fa-lg fa-fw text-info"></i> <?php _e('Share on Facebook', 'ipin'); ?></a></li>
						<li><a href="" onclick="window.open('https://twitter.com/share?url=<?php echo home_url('/') . $wp_rewrite->author_base . '/' . $user_info->user_nicename . '/'; ?>&amp;text=<?php echo rawurlencode($user_info->display_name . ' (' . $user_info->user_nicename . ') | ' . get_bloginfo('name')); ?>', 'twitter-share-dialog', 'width=626,height=500'); return false;"><i class="fa fa-twitter-square fa-lg fa-fw text-primary"></i> <?php _e('Share on Twitter', 'ipin'); ?></a></li>
						<li><a href="" onclick="window.open('http://www.reddit.com/submit?url=<?php echo rawurlencode(home_url('/') . $wp_rewrite->author_base . '/' . $user_info->user_nicename . '/'); ?>&amp;title=<?php echo rawurlencode($user_info->display_name . ' (' . $user_info->user_nicename . ') | ' . get_bloginfo('name')); ?>', 'reddit-share-dialog', 'width=880,height=500,scrollbars=1'); return false;"><i class="fa fa-reddit-square fa-lg fa-fw text-primary"></i> <?php _e('Share on Reddit', 'ipin'); ?></a></li>
						<li><a href="" onclick="window.open('https://plus.google.com/share?url=<?php echo home_url('/') . $wp_rewrite->author_base . '/' . $user_info->user_nicename . '/'; ?>', 'gplus-share-dialog', 'width=626,height=500'); return false;"><i class="fa fa-google-plus-square fa-lg fa-fw text-danger"></i> <?php _e('Share on Google+', 'ipin'); ?></a></li>
						<li><a href="" onclick="window.open('http://pinterest.com/pin/create/button/?url=<?php echo rawurlencode(home_url('/') . $wp_rewrite->author_base . '/' . $user_info->user_nicename . '/'); ?>&amp;media=<?php echo rawurlencode($user_avatar_imgsrc[1]); ?>&amp;description=<?php echo rawurlencode($user_info->display_name . ' (' . $user_info->user_nicename . ') | ' . get_bloginfo('name')); ?>', 'pinterest-share-dialog', 'width=626,height=500'); return false;"><i class="fa fa-pinterest-square fa-lg fa-fw text-danger"></i> <?php _e('Share on Pinterest', 'ipin'); ?></a></li>
					</ul>
				</div>
			<?php if ($user_info->ID == $user_ID) {	?>
				<button class="btn btn-success btn-sm follow" onclick="window.location.href='<?php echo home_url('/settings/'); ?>'" type="button"><?php _e('Edit Profile', 'ipin'); ?></button>
			<?php } ?>
			<?php if ((current_user_can('administrator') || current_user_can('editor')) && $user_info->ID != $user_ID) { ?>
				<?php if (!(user_can($user_info->ID, 'administrator') && !current_user_can('administrator'))) { ?>
					<button class="btn btn-success btn-sm follow" onclick="window.location.href='<?php echo home_url('/settings/?user=') . $user_info->ID ; ?>'" type="button"><?php _e('Edit User', 'ipin'); ?></button>
				<?php } ?>
			<?php } ?>
			</li>
		</ul>
	</div>
	
	<div class="clearfix"><br /></div>

<?php 
if (isset($_GET['view']) && $_GET['view'] == sanitize_title(__('pins', 'ipin'))) {
	if ($user_ID == $user_info->ID || current_user_can('administrator') || current_user_can('editor')) {
		$paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
		$args = array(
			'author' => $user_info->ID,
			'paged' => $paged
		);
		query_posts($args);
	}
	get_template_part('index', 'masonry');


} else if (isset($_GET['view']) && $_GET['view'] == sanitize_title(__('likes', 'ipin'))) {
	$post_likes = get_user_meta($user_info->ID, '_Likes Post ID');

	if (!empty($post_likes[0])) {
		$pnum = isset($_GET['pnum']) ? intval($_GET['pnum']) : 1;
		$posts_per_page = get_option('posts_per_page');
		$maxpage = ceil(count($post_likes[0])/$posts_per_page);
		$post_likes[0] = array_slice($post_likes[0], ($posts_per_page * ($pnum-1)), $posts_per_page);
		if ($post_likes[0]) {
			$args = array(
				'post__in' => $post_likes[0],
				'ignore_sticky_posts' => 1,
				'orderby' => 'post__in'
			);
		} else {
			$args = array(
				'post__in' => array(0)
			);
		}
		
		query_posts($args);
		get_template_part('index', 'masonry');
	} else {
	?>
		<div class="row">
			<div class="bigmsg">
				<h2><?php _e('Nothing yet.', 'ipin'); ?></h2>
			</div>
		</div>
	</div>
	<?php
	}
	
	
} else if (isset($_GET['view']) && ($_GET['view'] == sanitize_title(__('followers', 'ipin')) || $_GET['view'] == sanitize_title(__('following', 'ipin')))) {
	if ($_GET['view'] == sanitize_title(__('followers', 'ipin'))) {
		$followers = get_user_meta($user_info->ID, '_Followers User ID');
	} else if ($_GET['view'] == sanitize_title(__('following', 'ipin'))) {
		$followers = get_user_meta($user_info->ID, '_Following User ID');
	}

	if (!empty($followers[0])) {
		$pnum = isset($_GET['pnum']) ? intval($_GET['pnum']) : 1;
		$followers_per_page = get_option('posts_per_page');
		$maxpage = ceil(count($followers[0])/$followers_per_page);
		$followers[0] = array_slice($followers[0], ($followers_per_page * ($pnum-1)), $followers_per_page);
		echo '<div id="user-profile-follow" class="row">';
		foreach ($followers[0] as $follower) {
			$follower_info = get_user_by('id', $follower);
			if ($follower_info) {
			?>
			<div class="follow-wrapper">
				<a class="follow-user-name" title="<?php echo esc_attr($follower_info->display_name); ?>" href="<?php echo home_url('/' . $wp_rewrite->author_base . '/') . $follower_info->user_nicename; ?>/">
					<h4><?php echo $follower_info->display_name; ?></h4>
					<p class="follow-user-meta"><?php $pins_count = count_user_posts($follower_info->ID); echo $pins_count; ?> <?php if ($pins_count == 1) _e('Pin', 'ipin'); else _e('Pins', 'ipin'); ?> &#8226; <?php if ('' == $followers_count = get_user_meta($follower_info->ID, '_Followers Count', true)) echo '0'; else echo $followers_count; ?> <?php if ($followers_count == 1) _e('Follower', 'ipin'); else _e('Followers', 'ipin'); ?></p>
					<div class="clearfix"></div>
				</a>
			
				<a class="follow-user-name" href="<?php echo home_url('/' . $wp_rewrite->author_base . '/') . $follower_info->user_nicename; ?>/">
					<div class="follow-user-avatar">
						<?php echo get_avatar($follower_info->ID, 105); ?>
					</div>

					<div class="follow-user-posts">
						<?php
						$follower_posts_count = 0;
						
						$follower_posts_thumbnail_ids = $wpdb->get_col($wpdb->prepare(
							"		
							SELECT $wpdb->postmeta.meta_value
							FROM $wpdb->posts, $wpdb->postmeta
							WHERE $wpdb->posts.post_author = %d
							AND $wpdb->posts.post_status = 'publish'
							AND $wpdb->posts.ID = $wpdb->postmeta.post_id
							AND $wpdb->postmeta.meta_key = '_thumbnail_id'
							ORDER BY $wpdb->posts.ID DESC
							LIMIT 0, 4
							",
							$follower_info->ID
						));

						foreach ($follower_posts_thumbnail_ids as $follower_posts_thumbnail_id) {
							$imgsrc = wp_get_attachment_image_src($follower_posts_thumbnail_id, 'thumbnail');
							echo '<div class="follow-user-posts-thumb"><img src="' . $imgsrc[0] . '" alt="" /></div>';
							$follower_posts_count++;						
						}
						
						while ($follower_posts_count < 4):
							echo '<div class="follow-user-posts-thumb follow-user-posts-thumb-blank"><img src="' . get_template_directory_uri() . '/img/blank2.gif" alt="" /></div>';
							$follower_posts_count++;
						endwhile;
						?>
					</div>
					<div class="clearfix"></div>
				</a>

				<?php
				if ($follower != $user_ID) {
				?>
				<span class="undisable_buttons">
					<button class="btn btn-success btn-block follow ipin-follow<?php $parent_board = get_user_meta($follower, '_Board Parent ID', true); if ($followed = ipin_followed($parent_board)) { echo ' disabled'; } ?>" data-author_id="<?php echo $follower; ?>" data-board_id="<?php echo $parent_board; ?>" data-board_parent_id="0" data-disable_others="no" type="button"><?php if (!$followed) { _e('Follow', 'ipin'); } else { _e('Unfollow', 'ipin'); } ?></button>
				</span>
				<?php } else { ?>
				<button class="btn btn-success btn-block follow" disabled="disabled" type="button"><?php _e('Myself!', 'ipin'); ?></button>
				<?php } ?>
			</div>
			<?php
			}
		}
		
		if ($maxpage != 0) { ?>
		<div id="navigation">
			<ul class="pager">				
				<?php if ($pnum != 1 && $maxpage >= $pnum) { ?>
				<li id="navigation-previous">
					<?php if ($_GET['view'] == sanitize_title(__('followers', 'ipin'))) { ?>
					<a href="<?php echo get_author_posts_url($user_info->ID); ?>?view=<?php echo sanitize_title(__('followers', 'ipin')); ?>&amp;pnum=<?php echo $pnum-1; ?>"><?php _e('&laquo; Previous', 'ipin') ?></a>
					<?php } else if ($_GET['view'] == sanitize_title(__('following', 'ipin'))) { ?>
					<a href="<?php echo get_author_posts_url($user_info->ID); ?>?view=<?php echo sanitize_title(__('following', 'ipin')); ?>&amp;pnum=<?php echo $pnum-1; ?>"><?php _e('&laquo; Previous', 'ipin') ?></a>
					<?php } ?>
				</li>
				<?php } ?>
				
				<?php if ($maxpage != 1 && $maxpage != $pnum) { ?>
				<li id="navigation-next">
					<?php if ($_GET['view'] == sanitize_title(__('followers', 'ipin'))) { ?>
					<a href="<?php echo get_author_posts_url($user_info->ID); ?>?view=<?php echo sanitize_title(__('followers', 'ipin')); ?>&amp;pnum=<?php echo $pnum+1; ?>"><?php _e('Next &raquo;', 'ipin') ?></a>
					<?php } else if ($_GET['view'] == sanitize_title(__('following', 'ipin'))) { ?>
					<a href="<?php echo get_author_posts_url($user_info->ID); ?>?view=<?php echo sanitize_title(__('following', 'ipin')); ?>&amp;pnum=<?php echo $pnum+1; ?>"><?php _e('Next &raquo;', 'ipin') ?></a>
					<?php } ?>
				</li>
				<?php } ?>
			</ul>
		</div>
		<?php }
		echo '</div><div class="clearfix"></div></div>';
	} else {
	?>
		<div class="row">
			<div class="bigmsg">
				<?php if ($_GET['view'] == sanitize_title(__('followers', 'ipin'))) { ?>
					<h2><?php _e('No one following yet.', 'ipin'); ?></h2>
				<?php } else if ($_GET['view'] == sanitize_title(__('following', 'ipin'))) { ?>
					<h2><?php _e('Not following anyone yet.', 'ipin'); ?></h2>
				<?php } ?>
			</div>
		</div>
	</div>
	<?php
	}
} else { //default to boards page 
	if ($boards_count > 0) {
	?>
	<div id="user-profile-boards">
	<?php
		global $wp_taxonomies;
		$pnum = isset($_GET['pnum']) ? intval($_GET['pnum']) : 1;
		$boards_per_page = 24;
		$maxpage = ceil($boards_count/$boards_per_page);
		$boards_paginated = get_terms('board', array('parent' => $parent_board_id, 'hide_empty' => false, 'orderby' => 'id', 'order' => 'DESC', 'number' => $boards_per_page, 'offset' => ($pnum - 1) * $boards_per_page));
		
		foreach ($boards_paginated as $board) {
			$board_id = $board->term_id;
			$board_parent_id = $board->parent;
			$board_name = $board->name;
			$board_count = $board->count;
			$board_slug = $board->slug;
			
			$board_thumbnail_ids = $wpdb->get_col($wpdb->prepare(
				"
				SELECT v.meta_value
				FROM $wpdb->postmeta AS v
				INNER JOIN (				
					SELECT object_id
					FROM $wpdb->term_taxonomy, $wpdb->term_relationships, $wpdb->posts
					WHERE $wpdb->term_taxonomy.term_id = %d
					AND $wpdb->term_taxonomy.term_taxonomy_id = $wpdb->term_relationships.term_taxonomy_id
					AND $wpdb->term_taxonomy.taxonomy = 'board'
					AND $wpdb->term_relationships.object_id = $wpdb->posts.ID
					AND $wpdb->posts.post_status = 'publish'
					ORDER BY $wpdb->term_relationships.object_id DESC
					LIMIT 0, 5
					) AS v2 ON v.post_id = v2.object_id
					AND v.meta_key = '_thumbnail_id'
				",
				$board_id
			));
			?>
			<div class="board-mini">
				<a class="board-title" title="<?php echo esc_attr($board_name); ?>" href="<?php echo home_url('/' . $wp_taxonomies["board"]->rewrite['slug'] . '/' . sanitize_title($board_name, '_') . '/' . $board_id . '/'); ?>">
					<h4><?php echo $board_name; ?></h4>
				</a>

				<a href="<?php echo home_url('/' . $wp_taxonomies["board"]->rewrite['slug'] . '/' . sanitize_title($board_name, '_') . '/' . $board_id . '/'); ?>">
					<div class="board-photo-frame">
						<?php
						$count= 1;
						$post_array = array();
						foreach ($board_thumbnail_ids as $board_thumbnail_id) {
							if ($count == 1) {
								$imgsrc = wp_get_attachment_image_src($board_thumbnail_id, 'medium');
								$imgsrc = $imgsrc[0];
								array_unshift($post_array, $imgsrc);
							} else {
								$imgsrc = wp_get_attachment_image_src($board_thumbnail_id, 'thumbnail');
								$imgsrc = $imgsrc[0];
								array_unshift($post_array, $imgsrc);
							}
							$count++;
						}
						
						$count = 1;
				
						$post_array_final = array_fill(0, 5, '');
						
						foreach ($post_array as $post_imgsrc) {
							array_unshift($post_array_final, $post_imgsrc);
							array_pop($post_array_final);
						}
						
						foreach ($post_array_final as $post_final) {
							if ($count == 1) {
								if ($post_final !=='') {
								?>
								<div class="board-main-photo-wrapper">
									<span class="board-pin-count"><?php echo $board_count ?> <?php if ($board_count == 1) { _e('pin', 'ipin'); } else { _e('pins', 'ipin'); } ?></span>
									<img src="<?php echo $post_final; ?>" class="board-main-photo" alt="" />
								</div>
								<?php
								} else {
								?>
								<div class="board-main-photo-wrapper">
									<span class="board-pin-count">0 <?php _e('pins', 'ipin'); ?></span>
								</div>
								<?php 
								}
							} else if ($post_final !=='') {
								?>
								<div class="board-photo-wrapper">
								<img src="<?php echo $post_final; ?>" class="board-photo" alt="" />
								</div>
								<?php
							} else {
								?>
								<div class="board-photo-wrapper">
								</div>
								<?php
							}
							$count++;
						}
						?>
					</div>
				</a>
					
				<?php if ($user_info->ID != $user_ID) { ?>
					<span class="undisable_buttons">
						<button class="btn btn-success btn-sm follow ipin-follow<?php if ($followed = ipin_followed($board_id)) { echo ' disabled'; } ?>" data-author_id="<?php echo $user_info->ID; ?>" data-board_id="<?php echo $board_id;  ?>" data-board_parent_id="<?php echo $board_parent_id; ?>" type="button"><?php if (!$followed) { _e('Follow Board', 'ipin'); } else { _e('Unfollow Board', 'ipin'); } ?></button>
					</span>
				<?php } else { ?>
					<a class="btn btn-success btn-sm edit-board" href="<?php echo home_url('/grp-settings/?i=') . $board_id; ?>"><?php _e('Edit Board', 'ipin'); ?></a>
				<?php } ?>
			</div>
		<?php } //end foreach	?>
		
		<?php if ($maxpage != 0) { ?>
		<div id="navigation">
			<ul class="pager">				
				<?php if ($pnum != 1 && $maxpage >= $pnum) { ?>
				<li id="navigation-previous">
					<a href="<?php echo get_author_posts_url($user_info->ID); ?>?pnum=<?php echo $pnum-1; ?>"><?php _e('&laquo; Previous', 'ipin') ?></a>
				</li>
				<?php } ?>
				
				<?php if ($maxpage != 1 && $maxpage != $pnum) { ?>
				<li id="navigation-next">
					<a href="<?php echo get_author_posts_url($user_info->ID); ?>?pnum=<?php echo $pnum+1; ?>"><?php _e('Next &raquo;', 'ipin') ?></a>
				</li>
				<?php } ?>
			</ul>
		</div>
		<?php } ?>
		</div>
		<div class="clearfix"></div>
		</div>

	<?php } else { ?>
		<div class="row">
			<div class="bigmsg">
				<h2><?php _e('Nothing yet.', 'ipin'); ?></h2>
			</div>
		</div>
	</div>
	<?php }
}
get_footer();
?>
