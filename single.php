<?php
if (in_category(ipin_blog_cats())) {
	get_template_part('single', 'blog');
} else {
	get_template_part('single', 'pin');
}
?>