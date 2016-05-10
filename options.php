<?php
function optionsframework_option_name() {
	$themename = get_option( 'stylesheet' );
	$themename = preg_replace("/\W/", "_", strtolower($themename) );

	$optionsframework_settings = get_option( 'optionsframework' );
	$optionsframework_settings['id'] = $themename;
	update_option( 'optionsframework', $optionsframework_settings );
}

add_action('admin_init', 'optionscheck_change_santiziation', 100);
  
function optionscheck_change_santiziation() {
    remove_filter('of_sanitize_textarea', 'of_sanitize_textarea');
	remove_filter('of_sanitize_text', 'sanitize_text_field');
    add_filter('of_sanitize_textarea', 'custom_sanitize_input');
    add_filter('of_sanitize_text', 'custom_sanitize_input');
}
  
function custom_sanitize_input($input) {
    return $input;
}

if (isset($_GET['settings-updated']) && of_get_option('default_avatar') != '') {
	require_once(ABSPATH . 'wp-admin/includes/file.php');
	$image = wp_get_image_editor(get_home_path() . str_replace(home_url('/'), '', of_get_option('default_avatar')));
	$ext = explode('.', of_get_option('default_avatar'));
	$ext = strtolower(array_pop($ext));
	$upload_dir = wp_upload_dir();
	
	if (!is_wp_error($image)) {
		$image->resize(96, 96, true);
		$image->save($upload_dir['basedir'] . '/avatar-96x96.' . $ext);
		update_option('ipin_avatar_96', $upload_dir['baseurl'] . '/avatar-96x96.' . $ext);
		$image->resize(48, 48, true);
		$image->save($upload_dir['basedir'] . '/avatar-48x48.' . $ext);
		update_option('ipin_avatar_48', $upload_dir['baseurl'] . '/avatar-48x48.' . $ext);
	}
} else if (isset($_GET['settings-updated']) && of_get_option('default_avatar') == '') {
	$ext = explode('.', get_option('ipin_avatar_48'));
	$ext = array_pop($ext);
	$upload_dir = wp_upload_dir();

	delete_option('ipin_avatar_48');
	delete_option('ipin_avatar_96');

	if (file_exists($upload_dir['basedir'] . '/avatar-48x48.' . $ext))
		unlink($upload_dir['basedir'] . '/avatar-48x48.' . $ext);

	if (file_exists($upload_dir['basedir'] . '/avatar-96x96.' . $ext))
		unlink($upload_dir['basedir'] . '/avatar-96x96.' . $ext);
}

function optionsframework_options() {
	// Pull all the parent categories into an array	
	$options_categories = array('');
	$options_categories_obj = get_categories('hide_empty=0&exclude=1');
	foreach ($options_categories_obj as $category) {
		if ($category->category_parent == 0) {
			$options_categories[$category->cat_ID] = $category->cat_name;
		}
	}
	
	// Pull all pages into an array	
	$options_pages = array('');
	$options_pages_obj = get_pages();
	foreach ($options_pages_obj as $page) {
			$options_pages[$page->ID] = $page->post_title;
	}
	
	$options = array();
	
	$options[] = array(
		'name' => __('General', 'options_framework_theme'),
		'type' => 'heading');
	
	$options[] = array(
		'name' => __('Color Scheme', 'options_framework_theme'),
		'id' => 'color_scheme',
		'std' => 'light',
		'type' => 'radio',
		'options' => array('light' => __('Light', 'options_framework_theme'), 'dark' => __('Dark', 'options_framework_theme')));
		
	$options[] = array(
		'name' => __('Show Frontpage\'s Pins Based On', 'options_framework_theme'),
		'id' => 'frontpage_popularity',
		'std' => 'showall',
		'type' => 'radio',
		'options' => array('likes' => __('Most Likes', 'options_framework_theme'), 'repins' => __('Most Repins', 'options_framework_theme'), 'comments' => __('Most Comments', 'options_framework_theme'), 'random' => __('Random', 'options_framework_theme'), 'showall' => __('Show All', 'options_framework_theme')));

	$options[] = array(
		'desc' => __(' Over Last X Days (only for Most Likes/Repins/Comments)', 'options_framework_theme'),
		'id' => 'frontpage_popularity_duration',
		'std' => '180',
		'class' => 'mini',
		'type' => 'text');

	$options[] = array(
		'name' => __('Show Popular Page\'s Pins Based On', 'options_framework_theme'),
		'id' => 'popularity',
		'std' => 'showall',
		'type' => 'radio',
		'options' => array('likes' => __('Most Likes', 'options_framework_theme'), 'repins' => __('Most Repins', 'options_framework_theme'), 'comments' => __('Most Comments', 'options_framework_theme'), 'showall' => __('Show All', 'options_framework_theme')));

	$options[] = array(
		'desc' => __(' Over Last X Days (only for Most Likes/Repins/Comments)', 'options_framework_theme'),
		'id' => 'popularity_duration',
		'std' => '180',
		'class' => 'mini',
		'type' => 'text');

	$options[] = array(
		'desc' => __('When your site is new, select "Show All" so that the frontpage & popular page will not be blank. As the pins get more likes, repins or comments, select as appropriate.', 'options_framework_theme'),
		'type' => 'info');
		
	$options[] = array(
		'name' => __('Header Logo Image', 'options_framework_theme'),
		'desc' => __('Logo height should be 50px. Width is flexible. Leave blank to use site title text.', 'options_framework_theme'),
		'id' => 'logo',
		'type' => 'upload');
		
	$options[] = array(
		'name' => __('Pin It Button Image', 'options_framework_theme'),
		'desc' => __('Leave blank to use text for Pin It Button at <a href="' . home_url('/itm-settings/') . '">Add > Pin</a>.', 'options_framework_theme'),
		'id' => 'pinit_button',
		'type' => 'upload');
		
	$options[] = array(
		'name' => __('Default Avatar', 'options_framework_theme'),
		'desc' => __('Recommended size: 96 x 96px. Leave blank to use <a target="_blank" href="' . get_template_directory_uri() . '/img/avatar-48x48.png">Mystery Man</a>', 'options_framework_theme'),
		'id' => 'default_avatar',
		'type' => 'upload');
	
	$options[] = array(
		'name' => __('Top Header Message for Non Logged-in Users', 'options_framework_theme'),
		'id' => 'top_message',
		'std' => 'Organize and share the things you like.',
		'type' => 'text');

	$options[] = array(
		'name' => __('Social Icon Urls', 'options_framework_theme'),
		'desc' => __('Facebook Url. Leave blank to hide facebook icon in header', 'options_framework_theme'),
		'id' => 'facebook_icon_url',
		'std' => 'http://facebook.com/#',
		'type' => 'text');
		
	$options[] = array(
		'desc' => __('Twitter Url. Leave blank to hide twitter icon in header', 'options_framework_theme'),
		'id' => 'twitter_icon_url',
		'std' => 'http://twitter.com/#',
		'type' => 'text');
		
	$options[] = array(
		'name' => __('Frontpage Comments Number', 'options_framework_theme'),
		'desc' => __('Enter 0 to hide comments on frontpage', 'options_framework_theme'),
		'id' => 'frontpage_comments_number',
		'std' => '2',
		'type' => 'text');
		
	$options[] = array(
		'name' => __('Facebook Comments', 'options_framework_theme'),
		'desc' => __('If enabled, Facebook Comments box will be displayed in single post', 'options_framework_theme'),
		'id' => 'facebook_comments',
		'std' => 'enable',
		'type' => 'radio',
		'options' => array('enable' => __('Enable', 'options_framework_theme'), 'disable' => __('Disable', 'options_framework_theme')));
		
	$options[] = array(
		'name' => __('Show Repins', 'options_framework_theme'),
		'desc' => __('If disabled, repins are hidden from the front, categories and tags page', 'options_framework_theme'),
		'id' => 'show_repins',
		'std' => 'enable',
		'type' => 'radio',
		'options' => array('enable' => __('Enable', 'options_framework_theme'), 'disable' => __('Disable', 'options_framework_theme')));
		
	$options[] = array(
		'name' => __('Infinite Scroll', 'options_framework_theme'),
		'desc' => __('If disabled, the normal pagination links are displayed. The theme is compatible with the <a href="http://wordpress.org/extend/plugins/wp-pagenavi/">WP-PageNavi</a> plugin, but must be deactivated if you re-enable infinite scroll.', 'options_framework_theme'),
		'id' => 'infinitescroll',
		'std' => 'enable',
		'type' => 'radio',
		'options' => array('enable' => __('Enable', 'options_framework_theme'), 'disable' => __('Disable', 'options_framework_theme')));

	$options[] = array(
		'name' => __('Lightbox', 'options_framework_theme'),
		'desc' => __('If disabled, clicking on the frontpage thumbnails will go to the single post instead of opening in a lightbox.', 'options_framework_theme'),
		'id' => 'lightbox',
		'std' => 'enable',
		'type' => 'radio',
		'options' => array('enable' => __('Enable', 'options_framework_theme'), 'disable' => __('Disable', 'options_framework_theme')));
		
	$options[] = array(
		'name' => __('Form Title & Description', 'options_framework_theme'),
		'desc' => __('For use in add/edit pins forms', 'options_framework_theme'),
		'id' => 'form_title_desc',
		'std' => 'single',
		'type' => 'radio',
		'options' => array('single' => __('Show Single Description Field', 'options_framework_theme'), 'separate' => __('Show Separate Title & Description Field', 'options_framework_theme')));

	$options[] = array(
		'name' => __('Allow HTML Code', 'options_framework_theme'),
		'desc' => __('If enabled, the html editor will be displayed on the description field.', 'options_framework_theme'),
		'id' => 'htmltags',
		'std' => 'disable',
		'type' => 'radio',
		'options' => array('enable' => __('Enable', 'options_framework_theme'), 'disable' => __('Disable', 'options_framework_theme')));
		
	$options[] = array(
		'name' => __('Allow Tags Input', 'options_framework_theme'),
		'desc' => __('If enabled, users can add tags to pins.', 'options_framework_theme'),
		'id' => 'posttags',
		'std' => 'disable',
		'type' => 'radio',
		'options' => array('enable' => __('Enable', 'options_framework_theme'), 'disable' => __('Disable', 'options_framework_theme')));
		
	$options[] = array(
		'name' => __('Allow Source Input', 'options_framework_theme'),
		'desc' => __('If enabled, users can add source url to pins.', 'options_framework_theme'),
		'id' => 'source_input',
		'std' => 'disable',
		'type' => 'radio',
		'options' => array('enable' => __('Enable', 'options_framework_theme'), 'disable' => __('Disable', 'options_framework_theme')));

	$options[] = array(
		'name' => __('Allow Price Input/Currency Symbol', 'options_framework_theme'),
		'desc' => __('Default is $. Leave blank to disable price input and price tag shown in top left corner of the pin.', 'options_framework_theme'),
		'id' => 'price_currency',
		'std' => '$',
		'type' => 'text');
		
	$options[] = array(
		'id' => 'price_currency_position',
		'std' => 'left',
		'type' => 'radio',
		'options' => array('left' => __('Currency symbol on the left', 'options_framework_theme'), 'right' => __('Currency symbol on the right', 'options_framework_theme')));

	$options[] = array(
		'name' => __('Allow Users to Delete Own Account', 'options_framework_theme'),
		'desc' => __('If enabled, users can delete their own account in the <a href="' . home_url('/settings/') . '">Settings</a> page. Administrator will always see the Delete Account link.', 'options_framework_theme'),
		'id' => 'delete_account',
		'std' => 'disable',
		'type' => 'radio',
		'options' => array('enable' => __('Enable', 'options_framework_theme'), 'disable' => __('Disable', 'options_framework_theme')));

	$options[] = array(
		'name' => __('Auto Create These Boards for New Users', 'options_framework_theme'),
		'desc' => __('Enter board names seperated by commas e.g My First Board, Gadgets, Humour', 'options_framework_theme'),
		'id' => 'auto_create_boards_name',
		'std' => '',
		'type' => 'text');

	$options[] = array(
		'desc' => __('Enter category ID for each board as above e.g 1, 4, 2. You can find the category ID at <a href="' . admin_url('post-new.php?post_type=page') . '">Posts > Categories</a>', 'options_framework_theme'),
		'id' => 'auto_create_boards_cat',
		'std' => '',
		'type' => 'text');

	/* $options[] = array(
		'name' => __('Auto Follow These Users for New Users', 'options_framework_theme'),
		'desc' => __('Enter user IDs seperated by commas e.g 1, 23, 45', 'options_framework_theme'),
		'id' => 'auto_default_follows',
		'type' => 'text');
	*/

	$options[] = array(
		'name' => __('Outgoing Email Settings', 'options_framework_theme'),
		'desc' => __('Email address', 'options_framework_theme'),
		'id' => 'outgoing_email',
		'std' => 'noreply@' . parse_url(home_url(), PHP_URL_HOST),
		'type' => 'text');
		
	$options[] = array(
		'desc' => __('From whom', 'options_framework_theme'),
		'id' => 'outgoing_email_name',
		'std' => get_bloginfo('name'),
		'type' => 'text');
		
	$options[] = array(
		'desc' => __('Your email "From" field. For user email notifications for likes, follows, comments etc.', 'options_framework_theme'),
		'type' => 'info');

	$options[] = array(
		'name' => __('Prune Schedule', 'options_framework_theme'),
		'desc' => __('posts every', 'options_framework_theme'),
		'id' => 'prune_postnumber',
		'std' => '5',
		'type' => 'text');
		
	$options[] = array(
		'desc' => __('mins', 'options_framework_theme'),
		'id' => 'prune_duration',
		'std' => '5',
		'type' => 'text');

	$options[] = array(
		'desc' => __('When a user delete a pin or a board, the posts are marked as prune for deletion later. Depending on your server load, you can adjust how often the system delete these posts.', 'options_framework_theme'),
		'type' => 'info');
		
	$options[] = array(
		'name' => __('Captcha for Register/Login Form', 'options_framework_theme'),
		'desc' => __('reCAPTCHA Site Key', 'options_framework_theme'),
		'id' => 'captcha_public',
		'std' => '',
		'type' => 'text');
		
	$options[] = array(
		'desc' => __('reCAPTCHA Secret Key', 'options_framework_theme'),
		'id' => 'captcha_private',
		'std' => '',
		'type' => 'text');

	$options[] = array(
		'desc' => __('Sign up for the keys at <a href="http://www.google.com/recaptcha/">Google reCAPTCHA</a>. Leave blank to hide captcha.', 'options_framework_theme'),
		'type' => 'info');
		
	$options[] = array(
		'name' => __('Terms of Service Page for Register Form', 'options_framework_theme'),
		'desc' => __('Go to <a href="' . admin_url('post-new.php?post_type=page') . '">Pages > Add New</a> to create the page first. Leave blank if you do not need users to tick a box to agree to terms of service before registering.', 'options_framework_theme'),
		'id' => 'register_agree',
		'type' => 'select',
		'options' => $options_pages);
		
	$options[] = array(
		'name' => __('Category For Blog', 'options_framework_theme'),
		'desc' => __('Hide blog category from the Add/Edit Board page. Leave blank if you do not need a blog yet.', 'options_framework_theme'),
		'id' => 'blog_cat_id',
		'std' => '0',
		'type' => 'select',
		'options' => $options_categories);
		
	$options[] = array(
		'name' => __('Header Scripts', 'options_framework_theme'),
		'desc' => __('Add scripts before the &lt;/head> tag', 'options_framework_theme'),
		'id' => 'header_scripts',
		'type' => 'textarea');
		
	$options[] = array(
		'name' => __('Footer Scripts', 'options_framework_theme'),
		'desc' => __('Add scripts before the &lt;/body> tag e.g Google Analytics', 'options_framework_theme'),
		'id' => 'footer_scripts',
		'type' => 'textarea');

	/*
	$options[] = array(
		'name' => __('Browser Extension Pack ID', 'options_framework_theme'),
		'desc' => __('If you purchase the optional Browser Extension Pack, enter the ID to activate (<a href="http://ericulous.com/2013/09/18/browser-addonsextensions-pack-for-ipin-pro/" target="_blank">how to get ID</a>).', 'options_framework_theme'),
		'id' => 'browser-extension-id',
		'std' => '',
		'type' => 'text');
	*/
	
	$options[] = array(
		'name' => __('Advertisement', 'options_framework_theme'),
		'type' => 'heading');

	$options[] = array(
		'name' => __('Header Advertisement', 'options_framework_theme'),
		'desc' => __('HTML / PHP / Javascript allowed.', 'options_framework_theme'),
		'id' => 'header_ad',
		'type' => 'textarea');
		
	$options[] = array(
		'name' => __('Single Post - Above Photo', 'options_framework_theme'),
		'desc' => __('Recommended Width: 700px or lower. HTML / PHP / Javascript allowed. Note: Javascript based ads like adsense may not appear in the lightbox, only in single posts.', 'options_framework_theme'),
		'id' => 'single_pin_above_ad',
		'type' => 'textarea');
		
	$options[] = array(
		'name' => __('Single Post - Below Photo', 'options_framework_theme'),
		'desc' => __('Recommended Width: 700px or lower. HTML / PHP / Javascript allowed. Note: Javascript based ads like adsense may not appear in the lightbox, only in single posts', 'options_framework_theme'),
		'id' => 'single_pin_below_ad',
		'type' => 'textarea');

	$options[] = array(
		'name' => __('Frontpage Thumbnail Ad #1', 'options_framework_theme'),
		'desc' => __('Display before X(th) thumbnail', 'options_framework_theme'),
		'id' => 'frontpage1_ad',
		'std' => '1',
		'class' => 'mini',
		'type' => 'text');
		
	$options[] = array(
		'desc' => __('Recommended Width: 200px or lower. HTML / PHP / Javascript allowed.', 'options_framework_theme'),
		'id' => 'frontpage1_ad_code',
		'type' => 'textarea');
		
	$options[] = array(
		'name' => __('Frontpage Thumbnail Ad #2', 'options_framework_theme'),
		'desc' => __('Display at X(th) position', 'options_framework_theme'),
		'id' => 'frontpage2_ad',
		'std' => '2',
		'class' => 'mini',
		'type' => 'text');
		
	$options[] = array(
		'desc' => __('Recommended Width: 200px or lower. HTML / PHP / Javascript allowed.', 'options_framework_theme'),
		'id' => 'frontpage2_ad_code',
		'type' => 'textarea');
		
	$options[] = array(
		'name' => __('Frontpage Thumbnail Ad #3', 'options_framework_theme'),
		'desc' => __('Display at X(th) position', 'options_framework_theme'),
		'id' => 'frontpage3_ad',
		'std' => '3',
		'class' => 'mini',
		'type' => 'text');
		
	$options[] = array(
		'desc' => __('Recommended Width: 200px or lower. HTML / PHP / Javascript allowed.', 'options_framework_theme'),
		'id' => 'frontpage3_ad_code',
		'type' => 'textarea');
		
	$options[] = array(
		'name' => __('Frontpage Thumbnail Ad #4', 'options_framework_theme'),
		'desc' => __('Display at X(th) position', 'options_framework_theme'),
		'id' => 'frontpage4_ad',
		'std' => '4',
		'class' => 'mini',
		'type' => 'text');
		
	$options[] = array(
		'desc' => __('Recommended Width: 200px or lower. HTML / PHP / Javascript allowed.', 'options_framework_theme'),
		'id' => 'frontpage4_ad_code',
		'type' => 'textarea');
		
	$options[] = array(
		'name' => __('Frontpage Thumbnail Ad #5', 'options_framework_theme'),
		'desc' => __('Display at X(th) position', 'options_framework_theme'),
		'id' => 'frontpage5_ad',
		'std' => '5',
		'class' => 'mini',
		'type' => 'text');
		
	$options[] = array(
		'desc' => __('Recommended Width: 200px or lower. HTML / PHP / Javascript allowed.', 'options_framework_theme'),
		'id' => 'frontpage5_ad_code',
		'type' => 'textarea');
		
	$options[] = array(
		'name' => __('Notes', 'options_framework_theme'),
		'type' => 'heading');
		
	$options[] = array(
		'desc' => __('
		<h2>Recommended Plugins</h2>
		<ol>
		<li><a href="http://wordpress.org/extend/plugins/wp-super-cache/" target="_blank">WP Super Cache</a></li>
		<ul>
		<li>- Advanced Tab: "Don\'t cache pages for known users" must be ticked</li>
		</ul>
		<li><a href="http://wordpress.org/plugins/wordpress-social-login/" target="_blank">WordPress Social Login</a> (allow users to login with Facebook or Twitter)</li>
		<ul>
		<li>- Networks Tab: tested only with Facebook, Twitter & Google</li>
		<li>- Bouncer Tab: features not supported</li>
		<li>- Widget Tab:<br />--- Users avatars: Display the default users avatars<br />--- Authentication flow: No popup window</li>
		</ul>
		</ol>

		<hr style="border:none;border-top:1px solid #ccc;color" />
		<h2>Adding Pins</h2>
		<p>All users should add pins from the frontend (top right corner)  > Add > <a href="' . home_url('/itm-settings/') . '" target="_blank">Pin</a>. Notes when adding pins from backend e.g WP-Admin > Posts > Add New</p>
		<ol>
		<li>The "Featured Image" must be set.</li>
		<li>The post will be assigned to the board with the same name as the post category. E.g if a post is created under the Humour category, the post will be assigned to the Humour board. If Humour board does not exist, it will be created automatically.</li>
		<ul>
		</ol>
		
		<hr style="border:none;border-top:1px solid #ccc;color" />
		<h2>Sideblog</h2>
		If you have enabled the sideblog (General tab > Category For Blog), please do not enter tags for the sideblog posts. You can however create sub-categories under the parent blog category.
		
		<hr style="border:none;border-top:1px solid #ccc;color" />
		<h2>Permissions</h2>
		<p>You can change the Settings > General > <a href="' . admin_url('options-general.php') . '" target="_blank">New User Default Role</a> depending on your needs. If unsure, leave it as "Author" which best matched Pinterest.com system. The permissions for each role are as below.</p>
		<p><strong>Administrator</strong>
		- Everything</p>
		<p><strong>Editor</strong>
		- All of Author
		- Access WP-Admin
		- Publish "Pending Review" Pin (backend)
		- Edit/Delete Others Pin (frontend)
		- Edit/Delete Others Board (frontend)
		- Edit Others Profile (frontend)
		</p>
		<p><strong>Author</strong>
		- All of Contributor
		- Add Pin (Post Status: Published)
		- Repin
		</p>
		<p><strong>Contributor</strong>
		- All of Subscriber
		- Add Pin (Post Status: Pending Review)
		</p>
		<p><strong>Subscriber</strong>
		- Comment
		- Follow
		- Like
		</p>

		<hr style="border:none;border-top:1px solid #ccc;color" />
		<h2>Cautions</h2>
		<ol>
		<li>
		WP-Admin > Pages > All Pages<br />Do not change the permalink for these pages (Board Settings, Everything, Following, Login, Lost Your Password, Notifications, Pins Settings, Popular, Register, Settings, Source, Top Users)
		</li>
		<li>
		WP-Admin > Posts > Categories<br />Do not delete categories if there are already posts under them. However you can edit them or create new ones.
		</li>
		</ol>
		', 'options_framework_theme'),
		'type' => 'info');

	return $options;
}
?>