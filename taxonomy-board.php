<?php
global $user_ID, $wp_rewrite, $wp_taxonomies;

$board_info = $wp_query->get_queried_object();
if ($board_info->parent == 0) {
	wp_redirect(get_author_posts_url(intval($board_info->name)), 301);
}

get_header(); 
?>

<div class="container-fluid">
	<?php
	$board_user_array = get_term_by('id', $board_info->parent, 'board');
	$board_user = intval($board_user_array->name);
	$category = get_category($board_info->description);
	
	if (!isset($board_user)) {
		$board_user = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT user_id FROM $wpdb->usermeta
				WHERE meta_key ='_Board Parent ID'
				AND meta_value = %d LIMIT 1
				"
				, $board_info->parent
			)
		);
	}
	$user_info = get_user_by('id', $board_user);
	?>
	<div class="subpage-title">
		<h1>
		<?php echo $board_info->name; ?>
		</h1>
	</div>
	
	<div id="userbar" class="row">
		<ul class="nav">
			<li>
				<a href="<?php echo home_url('/' . $wp_rewrite->author_base . '/') . $user_info->user_nicename; ?>/">
				<?php echo get_avatar($user_info->ID, '19'); ?><br />
				<strong><?php echo $user_info->display_name; ?></strong>
				</a>
			</li>
			<li><a href="<?php echo get_category_link($category->cat_ID); ?>"><?php _e('Category', 'ipin'); ?><br /><strong><?php echo $category->name; ?></strong></a></li>
			<li><?php _e('Pins', 'ipin'); ?><br /><strong><?php echo $board_info->count; ?></strong></li>
			<li>
				<?php if ($board_user != $user_ID) { ?>
				<span class="undisable_buttons">
					<button class="btn btn-success btn-sm follow ipin-follow<?php if ($followed = ipin_followed($board_info->term_id)) { echo ' disabled'; } ?>" data-author_id="<?php echo $board_user; ?>" data-board_id="<?php echo $board_info->term_id;  ?>" data-board_parent_id="<?php echo $board_info->parent; ?>" type="button"><?php if (!$followed) { _e('Follow Board', 'ipin'); } else { _e('Unfollow Board', 'ipin'); } ?></button>
				</span>
				<?php } ?>

				<?php $imgsrc = wp_get_attachment_image_src(get_post_thumbnail_id($post->ID), 'large'); ?>
				<div class="ipin-share btn-group">
					<button type="button" class="btn btn-success btn-sm follow dropdown-toggle" data-toggle="dropdown">
						<i class="fa fa-share-alt"></i> <span class="caret"></span>
					</button>
					
					<ul class="dropdown-menu">
						<li><a href="" onclick="window.open('https://www.facebook.com/sharer/sharer.php?u=<?php echo rawurlencode(home_url('/' . $wp_taxonomies["board"]->rewrite['slug'] . '/' . sanitize_title($board_info->name, '_') . '/') . $wp_query->query['board']. '/'); ?>', 'facebook-share-dialog', 'width=626,height=500'); return false;"><i class="fa fa-facebook-square fa-lg fa-fw text-info"></i> <?php _e('Share on Facebook', 'ipin'); ?></a></li>
						<li><a href="" onclick="window.open('https://twitter.com/share?url=<?php echo home_url('/' . $wp_taxonomies["board"]->rewrite['slug'] . '/' . sanitize_title($board_info->name, '_') . '/') . $wp_query->query['board'] . '/'; ?>&amp;text=<?php echo rawurlencode($board_info->name); ?>', 'twitter-share-dialog', 'width=626,height=500'); return false;"><i class="fa fa-twitter-square fa-lg fa-fw text-primary"></i> <?php _e('Share on Twitter', 'ipin'); ?></a></li>						
						<li><a href="" onclick="window.open('http://www.reddit.com/submit?url=<?php echo rawurlencode(home_url('/' . $wp_taxonomies["board"]->rewrite['slug'] . '/' . sanitize_title($board_info->name, '_') . '/') . $wp_query->query['board']. '/'); ?>&amp;title=<?php echo rawurlencode($board_info->name); ?>', 'reddit-share-dialog', 'width=880,height=500,scrollbars=1'); return false;"><i class="fa fa-reddit-square fa-lg fa-fw text-primary"></i> <?php _e('Share on Reddit', 'ipin'); ?></a></li>
						<li><a href="" onclick="window.open('https://plus.google.com/share?url=<?php echo home_url('/' . $wp_taxonomies["board"]->rewrite['slug'] . '/' . sanitize_title($board_info->name, '_') . '/') . $wp_query->query['board'] . '/'; ?>', 'gplus-share-dialog', 'width=626,height=500'); return false;"><i class="fa fa-google-plus-square fa-lg fa-fw text-danger"></i> <?php _e('Share on Google+', 'ipin'); ?></a></li>	
						<li><a href="" onclick="window.open('http://pinterest.com/pin/create/button/?url=<?php echo rawurlencode(home_url('/' . $wp_taxonomies["board"]->rewrite['slug'] . '/' . sanitize_title($board_info->name, '_') . '/') . $wp_query->query['board']. '/'); ?>&amp;media=<?php echo rawurlencode($imgsrc[0]); ?>&amp;description=<?php echo rawurlencode($board_info->name); ?>', 'pinterest-share-dialog', 'width=626,height=500'); return false;"><i class="fa fa-pinterest-square fa-lg fa-fw text-danger"></i> <?php _e('Share on Pinterest', 'ipin'); ?></a></li>
					</ul>
				</div>
					
				<?php if ($board_info->parent && ($board_user == $user_ID || current_user_can('edit_others_posts'))) { ?>
				<button class="btn btn-success btn-sm edit-board follow" onclick="window.location='<?php echo home_url('/grp-settings/?i=') . $board_info->term_id; ?>'" type="button"><?php _e('Edit Board' , 'ipin'); ?></button>
				<?php } ?>
			</li>
		</ul>
	</div>
	
	<div class="clearfix"><br /></div>
<?php 
get_template_part('index', 'masonry');
get_footer();
?>