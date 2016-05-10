<?php
	if (post_password_required())
		return;
?>

<div id="comments">
	<?php if (have_comments()) : ?>

		<ol class="commentlist">
			<?php wp_list_comments(array('callback' => 'ipin_list_comments')); ?>
		</ol>

		<?php if (get_comment_pages_count() > 1 && get_option('page_comments')) : ?>
		<ul class="pager">
			<li class="previous"><?php previous_comments_link(__( '&laquo; Older Comments', 'ipin')); ?></li>
			<li class="next"><?php next_comments_link(__('Newer Comments &raquo;', 'ipin')); ?></li>
		</ul>
		<?php endif;?>

	<?php
	elseif (!comments_open() && '0' != get_comments_number() && post_type_supports(get_post_type(), 'comments')) :
	endif;
	
	if (is_user_logged_in()) {
		global $user_ID;

		comment_form(array(
		'title_reply' => '',
		'title_reply_to' => '',
		'cancel_reply_link' => __('X Cancel reply', 'ipin'),
		'comment_notes_before' => '',
		'comment_notes_after' => '',
		'logged_in_as' => '',
		'label_submit' => __('Post Comment', 'ipin'),
		'comment_field' => '<div class="pull-left">' . get_avatar($user_ID, '48') . '</div>' . '<div class="textarea-wrapper"><textarea class="form-control" placeholder="' . __('Add a comment...', 'ipin') . '" id="comment" name="comment" aria-required="true"></textarea></div>'
		));
	} else if (comments_open()) {
	?>
		<form method="post" id="commentform">
			<div class="pull-left"><?php echo get_avatar('', '48'); ?></div>
			<div class="textarea-wrapper">
				<textarea class="form-control" disabled placeholder="<?php _e('Login to comment...', 'ipin'); ?>"></textarea>
				<button id="submit" class="btn btn-success" type="submit"><?php _e('Post Comment', 'ipin'); ?></button>
			</div>
		</form>
	<?php
	}
	?>
</div>