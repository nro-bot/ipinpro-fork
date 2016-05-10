<?php
	if (post_password_required())
		return;
?>
<style>
#author, #email, #url{/* css 3 */
 border-radius:5px;
 /* mozilla */
 -moz-border-radius:5px;
 /* webkit */
 -webkit-border-radius:5px;
 box-shadow: none;
border: none;
background: #fafafa;
 }

</style>

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
		<?php endif;?><!-- #comment-nav-above -->

	<?php
	elseif (!comments_open() && '0' != get_comments_number() && post_type_supports(get_post_type(), 'comments')) :
	endif;
	
	if (comments_open()) { //if open for comments, display form
		global $user_ID;

		comment_form(array(
		'title_reply' => '',
		'title_reply_to' => '',
		'cancel_reply_link' => __('X Cancel reply', 'ipin'),
		'comment_notes_before' => '<hr/><p class="comment-notes">' . __( 'Your email address will not be published.' ) . ( $req ? $required_text : '' ) . '</p>',
		//'comment_notes_after' => '',
		//'logged_in_as' => '',
		'label_submit' => __('Post Comment', 'ipin'),
		'comment_field' => '<div class="pull-left">' . get_avatar($user_ID, '48') . '</div>' . '<div class="textarea-wrapper"><textarea class="form-control" placeholder="' . __('Add a comment...', 'ipin') . '" id="comment" name="comment" aria-required="true"></textarea></div><br/>'
		)

		);
	} else {
	?>
			<p class="no-comments"><?php _e( 'Comments are closed.', 'ipin' ); ?></p>
	<?php
	}
	?>
</div>
