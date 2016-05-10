<?php
//Theme options
if (!function_exists( 'optionsframework_init')) {
	require_once(get_template_directory() . '/inc/options-framework.php');
}


//Set content width
if (!isset($content_width)) {
	$content_width = 700;
}
	

//Action: after_setup_theme
function ipin_after_setup_theme() {
	load_theme_textdomain('ipin', get_template_directory() . '/languages');
	
	register_nav_menus(array('top_nav' => 'Top Navigation'));
	
	add_theme_support('automatic-feed-links');
	add_theme_support('post-thumbnails');
	add_theme_support('custom-background', array('default-color' => 'f2f2f2'));
	add_editor_style();
	
	show_admin_bar(false);

	//Clean up wp head
	remove_action('wp_head', 'adjacent_posts_rel_link_wp_head', 10, 0);
	remove_action('wp_head', 'wp_generator');
	remove_action('wp_head', 'wp_shortlink_wp_head', 10, 0);
}
add_action('after_setup_theme', 'ipin_after_setup_theme');


//Action: widgets_init
function ipin_widgets_init() {
	register_sidebar(array('id' => 'sidebar-r-t', 'name' => 'Right Sidebar for Single Pins Only (Above Boards)', 'before_widget' => '<div class="sidebar-wrapper"><div class="sidebar-inner">', 'after_widget' => '</div></div>', 'before_title' => '<h4>', 'after_title' => '</h4>'));
	register_sidebar(array('id' => 'sidebar-r', 'name' => 'Right Sidebar for Single Pins Only (Below Boards)', 'before_widget' => '<div class="sidebar-wrapper"><div class="sidebar-inner">', 'after_widget' => '</div></div>', 'before_title' => '<h4>', 'after_title' => '</h4>'));
	register_sidebar(array('id' => 'sidebar-others', 'name' => 'Right Sidebar for Other Pages & Sideblog', 'before_widget' => '<div class="sidebar-wrapper"><div class="sidebar-inner">', 'after_widget' => '</div></div>', 'before_title' => '<h4>', 'after_title' => '</h4>'));
}
add_action('widgets_init', 'ipin_widgets_init');


//Action: wp_head
function ipin_head() {
	//Opengraph
	if (is_single()) {
		global $post;
		setup_postdata($post);
		$output = '<meta property="og:type" content="article" />' . "\n";
		$output .= '<meta property="og:title" content="' . preg_replace('/[\n\r]/', ' ', mb_strimwidth(the_title_attribute('echo=0'), 0, 255, ' ...')) . '" />' . "\n";
		$output .= '<meta property="og:url" content="' . get_permalink() . '" />' . "\n";
		
		if ($post->post_content == '') {
			$meta_categories = get_the_category($post->ID);
		
			foreach ($meta_categories as $meta_category) {
				$meta_category_name = $meta_category->name;
			}
	
			if (ipin_get_post_board()) {
				$meta_board_name = ipin_get_post_board()->name;
			} else {
				$meta_board_name = __('Untitled', ipin);
			}

			$output .= '<meta property="og:description" content="' . esc_attr(__('Pinned onto', 'ipin') . ' ' . $meta_board_name . ' ' . __('Board in', 'ipin') . ' ' . $meta_category_name . ' ' . __('Category', 'ipin')) . '" />' . "\n";
		} else {
			$output .= '<meta property="og:description" content="' . esc_attr(get_the_excerpt()) . '" />' . "\n";
		}
		

		if (has_post_thumbnail()) {
			$imgsrc = wp_get_attachment_image_src(get_post_thumbnail_id($post->ID), 'full');
			$output .= '<meta property="og:image" content="' . $imgsrc[0] . '" />' . "\n";
		}
		if (get_option('wsl_settings_Facebook_app_id')) {
			$output .= '<meta property="fb:app_id" content="' . get_option('wsl_settings_Facebook_app_id') . '" />' . "\n";
		}
		echo $output;
	}
	
	if (is_tax('board')) {
		global $post, $wp_query, $wp_taxonomies;
		setup_postdata($post);		
		$output = '<meta property="og:type" content="article" />' . "\n";
		$output .= '<meta property="og:title" content="' . esc_attr($wp_query->queried_object->name) . '" />' . "\n";
		$output .= '<meta property="og:url" content="' . home_url('/' . $wp_taxonomies["board"]->rewrite['slug'] . '/' . sanitize_title($wp_query->queried_object->name, '_') . '/') . $wp_query->queried_object->term_id . '/" />' . "\n";
		$output .= '<meta property="og:description" content="" />' . "\n";
		if (has_post_thumbnail()) {
			$imgsrc = wp_get_attachment_image_src(get_post_thumbnail_id($post->ID), 'full');
			$output .= '<meta property="og:image" content="' . $imgsrc[0] . '" />' . "\n";
		}
		echo $output;
	}
	
	if (is_author()) {
		global $wp_query, $wp_rewrite;
		$user_info = get_user_by('id', $wp_query->query_vars['author']);
		$output = '<meta property="og:type" content="article" />' . "\n";
		$output .= '<meta property="og:title" content="' . esc_attr($user_info->display_name) . ' (' . $user_info->user_nicename . ')" />' . "\n";
		$output .= '<meta property="og:url" content="' . home_url('/') . $wp_rewrite->author_base . '/' . $user_info->user_nicename . '/" />' . "\n";
		$output .= '<meta property="og:description" content="' . esc_attr($user_info->description) . '" />' . "\n";
		$avatar_id = get_user_meta($user_info->ID, 'ipin_user_avatar', true);
		if ($avatar_id != '' && $avatar_id != 'deleted') {
			$user_avatar = wp_get_attachment_image_src($avatar_id, 'full');
			$output .= '<meta property="og:image" content="' . $user_avatar[0] . '" />' . "\n";
		}
		echo $output;
	}
}
add_action('wp_head', 'ipin_head');


//Remove hentry from post class
function ipin_post_class($classes) {
	$classes = array_diff($classes, array('hentry'));
	return $classes;
}
add_filter('post_class','ipin_post_class');


//Filter: query_vars
function ipin_query_vars($aVars) {
	//Rewrite source page template slug from /source/?domain=google.com to /source/google.com/
	$aVars[] = 'domain';
	$aVars[] = 'sort';
	$aVars[] = 'minprice';
	$aVars[] = 'maxprice';
	return $aVars;
}
add_filter('query_vars', 'ipin_query_vars');


//Filter: rewrite_rules_array
function ipin_rewrite_rules_array($aRules) {
	//Rewrite source page template slug from /source/?domain=google.com to /source/google.com/
	$aNewRules = array('source/([^/]+)/?$' => 'index.php?pagename=source&domain=$matches[1]');
	$aRules = $aNewRules + $aRules;
	return $aRules;
}
add_filter('rewrite_rules_array', 'ipin_rewrite_rules_array');


//Remove canonical links for source page
function ipin_wp() {
	if (is_page('source'))
		remove_action( 'wp_head', 'rel_canonical');
}
add_action('wp', 'ipin_wp', 0);


//Remove canonical links for source page for Yoast SEO
if (function_exists('wpseo_init')) {
	function ipin_wpseo_canonical ($canonical) {
		if (is_page_template('page_source.php')) {
			return false;
		} else {
			return $canonical;
		}
	}
	add_filter('wpseo_canonical', 'ipin_wpseo_canonical');
}


//Rewrite titles
function ipin_wp_title( $title, $sep ) {
	if (is_tax('board')) {
		global $post;
		$user_info = get_user_by('id', $post->post_author);
		return str_replace(' Boards', '' ,$title) . ' ' . __('Board by', 'ipin') . ' ' . $user_info->display_name;
	}
	
	if (is_page('source')) {
		global $wp_query;
		return __('Pins from', 'ipin') . ' ' . $wp_query->query_vars['domain'] . str_replace('Source ', ' ', $title);
	}
	
	if (is_single()) {
		if (mb_strlen($title) > 70) {
			$title = mb_strimwidth($title, 0, 70, ' ...');
		}
	}
	
	if (is_author()) {
		global $wp_query;
		$title = $title . '(' . $wp_query->queried_object->data->user_nicename . ')';
	}
	
	if (is_tag()) {
		$title = __('Tag:', 'ipin') . ' ' .$title;
	}
	
	if (is_category()) {
		$title = __('Category:', 'ipin') . ' ' .$title;
	}
	
	if (is_search()) {
		return __('Search results for', 'ipin') . ' ' . get_search_query();
	}
	
	return $title;
}
add_filter('wp_title', 'ipin_wp_title', 10, 2);


//Disable xmlrpc for authors and below
if (!current_user_can('administrator') && !current_user_can('editor')) {
	add_filter( 'xmlrpc_enabled', '__return_false' );
}


//Action: admin_init
function ipin_admin_init() {
	//Restrict /wp-admin/ to administrators & editors
	if ((!defined('DOING_AJAX') || !DOING_AJAX) && !current_user_can('administrator') && !current_user_can('editor')) {
		wp_redirect(home_url());
		exit;
    }
}
add_action('admin_init', 'ipin_admin_init', 1);


//Action: login_init
function ipin_login_init() {
	//Restrict access to wp-login.php
	if (!isset($_REQUEST) || empty($_REQUEST) || $_GET['action'] == 'register') {
		wp_redirect(home_url());
		exit;
    }
}
add_action('login_init', 'ipin_login_init', 1);


//Redirect login page from wp-login.php to /login/
function ipin_login_url($login_url, $redirect){
	$login_url = home_url('/login/');

	if (!empty($redirect)) {
		//prevent duplicate redirect_to parameters
		$duplicate_redirect = substr_count($redirect, 'redirect_to');
		if ($duplicate_redirect >= 1) {
			$redirect = substr($redirect, 0, (strrpos($redirect, '?')));
		}
		
		$login_url = add_query_arg('redirect_to', rawurlencode($redirect), $login_url);
	} else {
		$login_url = add_query_arg('redirect_to', rawurlencode(home_url('/')), $login_url);
	}

	return $login_url;
}
add_filter('login_url', 'ipin_login_url', 10, 2);


//Action: wp_login_failed
function ipin_login_failed($username) {
	//Redirect login page if login failed
	$referrer = $_SERVER['HTTP_REFERER'];
	
	if ($referrer == home_url() . '/login/') $referrer = $referrer . '?redirect_to=' . home_url(); // in rare case where user access /login/ page directly
	
	if (!empty($referrer) && !strstr($referrer,'wp-login') && !strstr($referrer,'wp-admin') && (!defined('DOING_AJAX') || !DOING_AJAX)) {
		//notify unverified users to activate their account
		$userdata = get_user_by('login', $username);
		$verify = get_user_meta($userdata->ID, '_Verify Email', true);
		//user with verified email do not have this usermeta field
		if ($verify != '') {
			$verify = '&email=unverified';
		}

		if (strpos($referrer, '&login=failed')) {
			wp_safe_redirect($referrer . $verify);
		} else {
			wp_safe_redirect($referrer . $verify . '&login=failed');
		}
		exit;
	}
}
add_action('wp_login_failed', 'ipin_login_failed');


//Ajax login
function ipin_ajax_login() {
	$nonce = $_POST['nonce'];

	if (!wp_verify_nonce($nonce, 'ajax-nonce'))
		die();
		
    //allow login using email
	if (is_email($_POST['log'])) {
        $user = get_user_by_email($_POST['log']);
        if ($user) $_POST['log'] = $user->user_login;
    }
	
    $valid_user = wp_authenticate(sanitize_text_field($_POST['log']), sanitize_text_field($_POST['pwd']));
	
    if (is_wp_error($valid_user) ){
        echo 'error';
    } else {
        wp_set_auth_cookie($valid_user->ID, true);
    }
	
	exit;
}
add_action('wp_ajax_nopriv_ipin-ajax-login', 'ipin_ajax_login');


//Filter: wp_authenticate_user
function ipin_wp_authenticate_user($userdata) {
	//Check whether user verified their email
	$verify = get_user_meta($userdata->ID, '_Verify Email', true);
	//user with verified email do not have this usermeta field
	if ($verify != '') {
		return new WP_Error('email_unverified', __('Email not verified. Please check your email for verification link.', 'ipin'));
	}
	
	//check if captcha is correct
	if ($_POST['formname'] == 'ipin_loginform' && of_get_option('captcha_public') != '' && of_get_option('captcha_private') != '') {
		require_once(get_template_directory() . '/recaptchalib.php');
		
		$privatekey = of_get_option('captcha_private');
		$reCaptcha = new ReCaptcha($privatekey);

		if ($_POST["g-recaptcha-response"]) {
			$resp = $reCaptcha->verifyResponse(
				$_SERVER["REMOTE_ADDR"],
				$_POST["g-recaptcha-response"]
			);
		}
		
		if (!$resp->success) {
			return new WP_Error('incorrect_captcha', __('<strong>ERROR</strong>: Incorrect Captcha.', 'ipin'));
		}
	}
	return $userdata;
}
add_filter('wp_authenticate_user', 'ipin_wp_authenticate_user', 1);


//Filter: authenticate
function ipin_authenticate($user, $username, $password) {
	//Allow login using email
    if (is_email($username)) {
        $user = get_user_by_email($username);
        if ($user) $username = $user->user_login;
	    return wp_authenticate_username_password(null, $username, $password);
    }
	
	return $user;
}
add_filter('authenticate', 'ipin_authenticate', 20, 3);


//Add user data after successful registration
function ipin_user_register($user_id) {
	$user_info = get_userdata($user_id);
	
	//create a parent board
	$board_id = wp_insert_term (
		$user_id,
		'board'
	);
	update_user_meta($user_id, '_Board Parent ID', $board_id['term_id']);
	
	//auto create boards
	if (of_get_option('auto_create_boards_name')) {
		$boards_name = explode(',', of_get_option('auto_create_boards_name'));
		$category_id = explode(',', of_get_option('auto_create_boards_cat'));
		
		$count = 0;
		foreach($boards_name as $board_name) {
			$board_name = sanitize_text_field($board_name);
			wp_insert_term (
				$board_name,
				'board',
				array(
					'description' => sanitize_text_field($category_id[$count]),
					'parent' => $board_id['term_id'],
					'slug' => $board_name . '__ipinboard'
				)
			);
			$count++;
		}
		
		delete_option("board_children");
	}
	
	//auto add follows
	/* if (of_get_option('auto_default_follows')) {
		$default_follows = explode(',', of_get_option('auto_default_follows'));	
		$user_ID = $user_id;
		$board_parent_id = '0';
		
		foreach ($default_follows as $default_follow) {
			$author_id = intval($default_follow);
			$board_id = get_user_meta($author_id, '_Board Parent ID', true);

			//if ($_POST['ipin_follow'] == 'follow') {		
				//update usermeta following for current user
				$usermeta_following_count = get_user_meta($user_ID, '_Following Count', true);
				$usermeta_following_user_id = get_user_meta($user_ID, '_Following User ID');
				$following_user_id = $usermeta_following_user_id[0];
				$usermeta_following_board_id = get_user_meta($user_ID, '_Following Board ID');
				$following_board_id = $usermeta_following_board_id[0];
		
				if (!is_array($following_user_id))
					$following_user_id = array();
		
				if (!is_array($following_board_id))
					$following_board_id = array();
		
				if ($board_parent_id == '0') {
					//insert all sub-boards from author
					$author_boards = get_term_children($board_id, 'board');
					
					foreach ($author_boards as $author_board) {
						if (!in_array($author_board, $following_board_id)) {
							array_unshift($following_board_id, $author_board);
						}
					}
		
					//track followers who fully follow user to update them when user create a new board
					$usermeta_followers_id_allboards = get_user_meta($author_id, '_Followers User ID All Boards');
					$followers_id_allboards = $usermeta_followers_id_allboards[0];
		
					if (!is_array($followers_id_allboards))
						$followers_id_allboards = array();
		
					if (!in_array($user_ID, $followers_id_allboards)) {
						array_unshift($followers_id_allboards, $user_ID);
						update_user_meta($author_id, '_Followers User ID All Boards', $followers_id_allboards);
					}
				}
				array_unshift($following_board_id, $board_id);
				update_user_meta($user_ID, '_Following Board ID', $following_board_id);
		
				if (!in_array($author_id, $following_user_id)) {
					array_unshift($following_user_id, $author_id);
					update_user_meta($user_ID, '_Following User ID', $following_user_id);
					update_user_meta($user_ID, '_Following Count', ++$usermeta_following_count);
				}
		
				//update usermeta followers for author
				$usermeta_followers_count = get_user_meta($author_id, '_Followers Count', true);
				$usermeta_followers_id = get_user_meta($author_id, '_Followers User ID');
				$followers_id = $usermeta_followers_id[0];
		
				if (!is_array($followers_id))
					$followers_id = array();
		
				if (!in_array($user_ID, $followers_id)) {
					array_unshift($followers_id, $user_ID);
					update_user_meta($author_id, '_Followers User ID', $followers_id);
					update_user_meta($author_id, '_Followers Count', ++$usermeta_followers_count);
				}
			//}
		}
	} */
	
	//set email notifications
	if (stripos($user_info->user_email, '@example.com') === false) {
		update_user_meta($user_id, 'ipin_user_notify_likes', '1');
		update_user_meta($user_id, 'ipin_user_notify_repins', '1');
		update_user_meta($user_id, 'ipin_user_notify_follows', '1');
		update_user_meta($user_id, 'ipin_user_notify_comments', '1');
	} else {
		update_user_meta($user_id, 'ipin_user_notify_likes', '0');
		update_user_meta($user_id, 'ipin_user_notify_repins', '0');
		update_user_meta($user_id, 'ipin_user_notify_follows', '0');
		update_user_meta($user_id, 'ipin_user_notify_comments', '0');
	}
}
add_action('user_register', 'ipin_user_register');


//Process social login user on first login
if (function_exists('wsl_activate')) :
function ipin_wsl_hook_process_login_after_wp_insert_user($user_id, $provider, $hybridauth_user_profile) {	
	//remove url
	wp_update_user(array('ID' => $user_id, 'user_url' => ''));
}
add_action('wsl_hook_process_login_after_wp_insert_user', 'ipin_wsl_hook_process_login_after_wp_insert_user', 10, 3);
endif;


//Change social login icons
if (function_exists('wsl_activate')) :
function ipin_wsl_render_auth_widget_alter_assets_base_url($url) {
	return get_template_directory_uri() . '/img/social/';
}
add_filter('wsl_render_auth_widget_alter_assets_base_url', 'ipin_wsl_render_auth_widget_alter_assets_base_url');
endif;


//Check and add parent board upon login (in case user did not register through ipin pro theme register page)
function ipin_wp_login($user_login, $user) {
	if (!$user) {
		$user = get_user_by('login', $user_login); 	
	}

	$board_parent_id = get_user_meta($user->ID, '_Board Parent ID', true);
	//create a parent board if not exists
	if ($board_parent_id == '') {
		$board_id = wp_insert_term (
			$user->ID,
			'board'
		);
		update_user_meta($user->ID, '_Board Parent ID', $board_id['term_id']);
	}
}
add_action('wp_login', 'ipin_wp_login', 10, 2);


//Process social login user on login
if (function_exists('wsl_activate')) :
function ipin_wsl_hook_process_login_before_wp_safe_redirect($user_id, $provider, $hybridauth_profile) {
	//Check and add parent board upon login (in case user did not register through ipin pro theme register page) (for social login)
	$board_parent_id = get_user_meta($user_id, '_Board Parent ID', true);

	if ($board_parent_id == '') {
		$board_id = wp_insert_term (
			$user_id,
			'board'
		);
		update_user_meta($user_id, '_Board Parent ID', $board_id['term_id']);
	}
	
	//fetch wsl avatar
	if (get_user_meta($user_id, 'ipin_user_avatar', true) == '' && get_user_meta($user_id, 'ipin_user_avatar', true) != 'deleted' && $imgsrc = get_user_meta($user_id, 'wsl_current_user_image', true)) {
		//if twitter fetch larger thumb
		if (strpos($imgsrc, '_normal') !==  false) {
			$imgsrc = str_replace('_normal', '', $imgsrc);
		}
			
		if (function_exists("curl_init")) {
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $imgsrc);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
			$image = curl_exec($ch);
			curl_close($ch);
		} elseif (ini_get("allow_url_fopen")) {
			$image = file_get_contents($imgsrc, false, $context);
		}
	
		if (!$image) {
			$error = 'error';
		}
		
		$filename = time() . substr(str_shuffle("genki02468"), 0, 5);
		$file_array['tmp_name'] = WP_CONTENT_DIR . "/" . $filename . '.tmp';
		$filetmp = file_put_contents($file_array['tmp_name'], $image);
		
		if (!$filetmp) {
			@unlink($file_array['tmp_name']);
			$error = 'error';
		}
	
		if (!$error) {
			require_once(ABSPATH . 'wp-admin/includes/image.php');
			require_once(ABSPATH . 'wp-admin/includes/file.php');
			require_once(ABSPATH . 'wp-admin/includes/media.php');
			
			$imageTypes = array (
				1, //IMAGETYPE_GIF
				2, //IMAGETYPE_JPEG
				3 //IMAGETYPE_PNG
			);
		
			$imageinfo = getimagesize($file_array['tmp_name']);
			$width = @$imageinfo[0];
			$height = @$imageinfo[1];
			$type = @$imageinfo[2];
			$mime = @$imageinfo['mime'];
		
			if (!in_array ( $type, $imageTypes)) {
				@unlink($file_array['tmp_name']);
				$error = 'error';
			}
		
			if ($width <= 1 && $height <= 1) {
				@unlink($file_array['tmp_name']);
				$error = 'error';
			}
		
			if($mime != 'image/gif' && $mime != 'image/jpeg' && $mime != 'image/png') {
				@unlink($file_array['tmp_name']);
				$error = 'error';
			}
			
			switch($type) {
				case 1:
					$ext = '.gif';		
					break;
				case 2:
					$ext = '.jpg';
					break;
				case 3:
					$ext = '.png';
					break;
			}
			$file_array['name'] = 'avatar-' . $filename . $ext;
			
			add_image_size('avatar48', 48, 48, true);
			$attach_id = media_handle_sideload($file_array, 'none', '', array('post_author' => $user_id, 'post_title' => 'Avatar for UserID ' . $user_id)); //use none for $post_id so that image is uploaded to current month/year directory. Else $post_id = this pins page id, which will point to older month/year directory
				
			if (is_wp_error($attach_id)) {
				@unlink($file_array['tmp_name']);
				$error = 'error';
			}
		}
		
		if ($error != 'error') {
			update_user_meta($user_id, 'ipin_user_avatar', $attach_id);

			//attach the avatar to the user settings page so that it's not orphaned in the media library
			$settings_page = get_page_by_path('settings');
		
			global $wpdb;
			$wpdb->query(
				"
				UPDATE $wpdb->posts 
				SET post_parent = $settings_page->ID
				WHERE ID = $attach_id
				"
			);
		}
	}
}
add_action('wsl_hook_process_login_before_wp_safe_redirect', 'ipin_wsl_hook_process_login_before_wp_safe_redirect', 10, 3);
endif;


//Password form
function ipin_password_form($output) {
	$post = get_post( $post );
	$label = 'pwbox-' . ( empty($post->ID) ? rand() : $post->ID );
	$output = 
	'<form action="' . esc_url( site_url( 'wp-login.php?action=postpass', 'login_post' ) ) . '" class="post-password-form" method="post">
	<p>' . __( 'This content is password protected. To view it please enter your password below:', 'ipin' ) . '</p>' . 
	'<div class="form-group">' .  
		'<input class="form-control" type="password" name="post_password" id="' . $label . '" value="" />' . 
	'</div>' .
	'<input class="btn btn-success btn-ipin-custom" type="submit" name="Submit" value="' . esc_attr__( 'Submit', 'ipin' ) . '" />' .
	'</form>
	';
	return $output;
}
add_filter('the_password_form', 'ipin_password_form');


//Exclude blog entries from homepage
function ipin_pre_get_posts($query) {
	if (!is_admin()) {
		//exclude posts from blog
		if (of_get_option('blog_cat_id') && !$query->is_category(ipin_blog_cats()) && !is_feed()) {
			$query->set('cat', '-' . implode(' -', ipin_blog_cats()));
		}
		
		//exclude pages from search
		if ($query->is_search && is_main_query()) {
			$query->set('post_type', 'post');	
		}
		
		if ($query->is_author) {
			$query->set('post_status', array('publish', 'pending'));
		}
	}
	return $query;
}
add_action('pre_get_posts', 'ipin_pre_get_posts');


//Force second order by ID for meta_value_num
if (!function_exists('ipin_meta_value_num_orderby')) :
function ipin_meta_value_num_orderby($orderby) {
	global $wpdb;

	if (stripos($orderby, 'desc') !== false)
		$order = ' DESC';
	else
		$order = ' ASC';

	return " {$wpdb->postmeta}.meta_value+0 $order, {$wpdb->posts}.ID DESC";
}
endif;


//Force second order by ID for most comments
if (!function_exists('ipin_comments_orderby')) :
function ipin_comments_orderby($orderby) {
	global $wpdb;
	return " {$wpdb->posts}.comment_count+0 DESC, {$wpdb->posts}.ID DESC";
}
endif;


//Pre User Query
function ipin_pre_user_query($query) {
	$meta_key = $query->get('meta_key');
	$orderby = $query->get('orderby');
	//resolve numeric sorting for followers count in top users
	if ($meta_key == '_Followers Count' && $orderby == 'meta_value') {
		global $wpdb;
		$query->query_orderby = ' ORDER BY ' . $wpdb->usermeta  . '.meta_value+0 ' . $query->get('order');
	}
}
add_action('pre_user_query', 'ipin_pre_user_query');


//Search in display name
function ipin_user_search_columns($search_columns, $search, $this){
	if (!in_array('display_name', $search_columns)){
		$search_columns[] = 'display_name';
	}
	return $search_columns;
}
add_filter('user_search_columns', 'ipin_user_search_columns' , 10, 3);


//Check if user is top user by followers
if (!function_exists('ipin_top_user_by_followers')) :
function ipin_top_user_by_followers($user_id) {
	$user_id = (int)$user_id;
	$args = array(
		'order' => 'desc',
		'orderby' => 'meta_value',
		'meta_key' => '_Followers Count',
		'meta_query' => array(
			array(
			'key' => '_Followers Count',
			'compare' => '>',
			'value' => '0',
			'type' => 'numeric',
			)
		),
		'number' => '20',
		'fields' => 'ID'
	 );
	
	$top_user_follower_query = new WP_User_Query($args);
	$most_followers_pos = array_search($user_id, $top_user_follower_query->results);

	if ($most_followers_pos !== false)
		return $most_followers_pos + 1; //return the position e.g 3
	else
		return false;
}
endif;


//Check if user is top user by pins count
if (!function_exists('ipin_top_user_by_pins')) :
function ipin_top_user_by_pins($user_id) {
	$user_id = (int)$user_id;
	$blog_cat_id = of_get_option('blog_cat_id');
	if ($blog_cat_id) {
		global $wpdb;
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
				, $blog_cat_id, $user_id
			)
		);
	}
	$pins_count = count_user_posts($user_id) - $blog_post_count;
	
	$args = array(
		'order' => 'desc',
		'orderby' => 'post_count',
		'number' => '20'
		//'fields' => 'ID' -> cannot use fields ID as the results doesn't match the top-users page.
	 );
	
	$top_user_postcount_query = new WP_User_Query($args);
	$top_user_postcount_array = array();
	foreach ($top_user_postcount_query->results as $top_user_postcount) {
		array_push($top_user_postcount_array, $top_user_postcount->ID);
	}

	$most_pins_pos = array_search($user_id, $top_user_postcount_array);

	if ($most_pins_pos !== false && $pins_count > 0)
		return $most_pins_pos + 1; //return the position e.g 3
	else
		return false;
}
endif;


//Get array of blog categories
if (!function_exists('ipin_blog_cats')) :
function ipin_blog_cats() {
	$blog_cat_id = of_get_option('blog_cat_id');
	$blog_cats = array();
	
	if ($blog_cat_id) {
		$blog_cats = array($blog_cat_id);
	
		if (get_option('ipin_blog_subcats')) {
			$blog_cats = array_merge($blog_cats, get_option('ipin_blog_subcats'));
		}
	}
	
	return $blog_cats;
}
endif;


//Save/cache blog sub-categories to options
function ipin_blog_subcats($term_id, $tt_id, $taxonomy) {
	if ($taxonomy == 'category') {
		$blog_cat_id = of_get_option('blog_cat_id');
		
		if ($blog_cat_id) {
			$blog_subcategories = get_categories('hide_empty=0&child_of=' . $blog_cat_id);
			$blog_subcats= array();
			foreach ($blog_subcategories as $blog_subcategory) {
				array_push($blog_subcats, $blog_subcategory->cat_ID);
			}
			
			if (!empty($blog_subcats)) {
				update_option('ipin_blog_subcats', $blog_subcats);
			} else {
				update_option('ipin_blog_subcats', '');
			}
		}
	}
}
add_action("created_term", 'ipin_blog_subcats', 10, 3);
add_action("delete_term", 'ipin_blog_subcats', 10, 3);


//Action: init
function ipin_init() {
	//Add boards taxonomy
	register_taxonomy('board', 'post', array(
		'hierarchical' => true,
		'public' => true,
		'show_ui' => false,
		'show_in_nav_menus' => false,
		'labels' => array(
			'name' => 'Boards',
			'singular_name' => 'Board',
			'search_items' =>  'Search Boards',
			'all_items' => 'All Boards',
			'parent_item' => 'Parent Board',
			'parent_item_colon' => 'Parent Board:',
			'edit_item' => 'Edit Board',
			'update_item' => 'Update Board',
			'add_new_item' => 'Add New Board',
			'new_item_name' => 'New Board Name',
			'menu_name' => 'Boards'
		),
		'rewrite' => array(
			'slug' => 'board',
			'with_front' => false,
			'hierarchical' => true
		)
	));
	
	//Rewrite slug from /author/ to /user/
	global $wp_rewrite;
	$wp_rewrite->author_base = "user";
    $wp_rewrite->author_structure = "/" . $wp_rewrite->author_base . "/%author%/";
}
add_action('init', 'ipin_init', 0);


function ipin_term_link($termlink, $term, $taxonomy) {
	//set board link
	global $wp_taxonomies;
	if ($taxonomy == 'board')
		return home_url('/' . $wp_taxonomies["board"]->rewrite['slug'] . '/' . sanitize_title($term->name, '_') . '/') . $term->term_id . '/';
	return $termlink;
}
add_filter('term_link', 'ipin_term_link', 10, 3);


function ipin_parse_query($query) {
	//set board query
	if(isset($query->query_vars['board'])):
		if ($board = get_term_by('id', $query->query_vars['board'], 'board'))
			$query->query_vars['board'] = $board->slug;
	endif;
}
add_action('parse_query', 'ipin_parse_query');


function ipin_parse_request($wp) {
    //redirect /board/123/ to /board/board-title/123/
	if(isset($wp->query_vars['board'])) {
		preg_match('/board\/(.*?)\/[0-9]/', $_SERVER['REQUEST_URI'], $match);
		if(empty($match)) {
			global $wp_taxonomies;
			$board_info = get_term_by('id', $wp->query_vars['board'], 'board');
			$link = home_url('/' . $wp_taxonomies["board"]->rewrite['slug'] . '/' . sanitize_title($board_info->name, '_') . '/' . $board_info->term_id . '/');
			wp_redirect($link , 301);
			exit;
		}
	}
}
add_action('parse_request', 'ipin_parse_request');


//Javascripts
function ipin_enqueue_scripts() {
	wp_enqueue_style('ipin-bootstrap', get_template_directory_uri() . '/css/bootstrap.css');
	wp_enqueue_style('ipin-fontawesome', get_template_directory_uri() . '/css/font-awesome.min.css');
	wp_enqueue_style('ipin-style', get_stylesheet_directory_uri() . '/style.css', array('ipin-bootstrap'));
	
	if (of_get_option('color_scheme') == 'dark') {
		wp_enqueue_style('ipin-style-dark', get_template_directory_uri() . '/style-dark.css', array('ipin-style'));
	}

	global $current_user, $wp_rewrite;

	get_currentuserinfo();
	
	if (is_singular() && comments_open() && get_option('thread_comments') && is_user_logged_in()) {
		wp_enqueue_script('comment-reply');
	}

	if (is_page_template('page_cp_pins.php')) {
		wp_enqueue_script('suggest');
	}
	
	wp_enqueue_script('ipin_library', get_template_directory_uri() . '/js/ipin.library.js', array('jquery'), null, true);
	wp_enqueue_script('ipin_custom', get_template_directory_uri() . '/js/ipin.custom.js', array('jquery'), null, true);

	//for infinite scroll
	if (function_exists('wp_pagenavi')) {
		$nextSelector = '#navigation a:nth-child(3)';
	} else {
		$nextSelector = '#navigation #navigation-next a';
	}

	$tags_html = '';
	$price_html= '';
	$minWidth = 2;
	$minHeight = 2;
	
	$minWidth = apply_filters('ipin_minwidth', $minWidth);
	$minHeight = apply_filters('ipin_minheight', $minHeight);
	
	if (is_user_logged_in() && !is_page_template('page_cp_boards.php') && !is_page_template('page_cp_boards.php') && !is_page_template('page_cp_login.php') && !is_page_template('page_cp_login_lpw.php') && !is_page_template('page_cp_notifications.php') && !is_page_template('page_cp_pins.php') && !is_page_template('page_cp_register.php') && !is_page_template('page_cp_settings.php') && !is_page_template('page_top_users.php') && !is_404()) {
		if (of_get_option('form_title_desc') != 'separate') {
			if (of_get_option('htmltags') == 'enable') {
				$description_fields = ipin_wp_editor('pin-title');
			} else {
				$description_fields = '<textarea class="form-control" id="pin-title" placeholder="' . __('Describe your pin...', 'ipin') .'"></textarea>';
			}
		} else {
			if (of_get_option('htmltags') == 'enable') {
				$description_fields = '<textarea class="form-control" id="pin-title" placeholder="' . __('Title...', 'ipin') . '"></textarea><p></p>' . $description_fields = ipin_wp_editor('pin-content');
			} else {
				$description_fields = '<textarea class="form-control" id="pin-title" placeholder="' . __('Title...', 'ipin') . '"></textarea><p></p><textarea id="pin-content" class="form-control" placeholder="' . __('Description...', 'ipin') . '"></textarea>';
			}
		}
		
		if (of_get_option('posttags') == 'enable') {
			$tags_html = '<div class="input-group"><span class="input-group-addon"><i class="fa fa-tags"></i></span><input class="form-control" type="text" name="tags" id="tags" value="" placeholder="' . __('Tags e.g. comma, separated', 'ipin') . '" /></div>';
		}
		
		if (of_get_option('price_currency') != '') {
			if (of_get_option('price_currency_position') == 'right') {
				$price_html = '<div class="input-group"><input class="form-control text-right" type="text" name="price" id="price" value="" placeholder="' . __('Price e.g. 23.45', 'ipin') . '" /><span class="input-group-addon">' . of_get_option('price_currency') . '</span></div>';
			} else {
				$price_html = '<div class="input-group"><span class="input-group-addon">' . of_get_option('price_currency') . '</span><input class="form-control" type="text" name="price" id="price" value="" placeholder="' . __('Price e.g. 23.45', 'ipin') . '" /></div>';
				}
		}
		
		$dropdown_categories = ipin_dropdown_categories(__('Category for New Board', 'ipin'), 'board-add-new-category');
	} else {
		$description_fields = '';
		$tags_html = '';
		$price_html = '';
		$dropdown_categories = '';
	}
	
	$translation_array = array(
		'__allitemsloaded' => __('All items loaded', 'ipin'),
		'__addanotherpin' => __('Add Another Pin', 'ipin'),
		'__addnewboard' => __('Add new board...', 'ipin'),
		'__boardalreadyexists' => __('Board already exists. Please try another title.', 'ipin'),
		'__errorpleasetryagain' => __('Error. Please try again.', 'ipin'),
		'__cancel' => __('Cancel', 'ipin'),
		'__close' => __('Close', 'ipin'),
		'__comment' => __('comment', 'ipin'),
		'__comments' => __('comments', 'ipin'),
		'__enternewboardtitle' => __('Enter new board title', 'ipin'),
		'__Follow' => __('Follow', 'ipin'),
		'__FollowBoard' => __('Follow Board', 'ipin'),
		'__Forgot' => __('Forgot?', 'ipin'),
		'__imagetoosmall' => sprintf(__('Image is too small (min size: %d x %dpx)', 'ipin'), $minWidth, $minHeight),
		'__incorrectusernamepassword' => __('Incorrect Username/Password', 'ipin'),
		'__invalidimagefile' => __('Invalid image file. Please choose a JPG/GIF/PNG file.', 'ipin'),
		'__Likes' => __('Likes', 'ipin'),
		'__loading' => __('Loading...', 'ipin'),
		'__Login' => __('Login', 'ipin'),
		'__NotificationsLatest30' => __('Notifications (Latest 30)', 'ipin'),
		'__onto' => __('onto', 'ipin'),
		'__Pleasecreateanewboard' => __('Please create a new board', 'ipin'),
		'__Pleaseentertitle' => __('Please enter title', 'ipin'),
		'__Pleaseloginorregisterhere' => __('Please login or register here', 'ipin'),
		'__Pleasetypeacomment' => __('Please type a comment', 'ipin'),
		'__or' => __('or', 'ipin'),
		'__Password' => __('Password', 'ipin'),
		'__pinnedto' => __('Pinned to', 'ipin'),
		'__pleaseenterbothusernameandpassword' => __('Please enter both username and password.', 'ipin'),
		'__pleaseenterurl' => __('Please enter url', 'ipin'),
		'__Repin' => __('Repin', 'ipin'),
		'__Repins' => __('Repins', 'ipin'),
		'__repinnedto' => __('Repinned to', 'ipin'),
		'__seethispin' => __('See This Pin', 'ipin'),
		'__SeeAll' => __('See All', 'ipin'),
		'__shareitwithyourfriends' => __('Share it with your friends', 'ipin'),
		'__SignUp' => __('Sign Up', 'ipin'),
		'__sorryunbaletofindanypinnableitems' => __('Sorry, unable to find any pinnable items.', 'ipin'),
		'__Unfollow' => __('Unfollow', 'ipin'),
		'__UnfollowBoard' => __('Unfollow Board', 'ipin'),
		'__Username' => __('Username or Email', 'ipin'),
		'__Video' => __('Video', 'ipin'),
		'__Welcome' => __('Welcome', 'ipin'),
		'__yourpinispendingreview' => __('Your pin is pending review', 'ipin'),

		'ajaxurl' => admin_url('admin-ajax.php'),
		'avatar30' => get_avatar($current_user->ID, '30'),
		'avatar48' => get_avatar($current_user->ID, '48'),
		'blogname' => get_bloginfo('name'),
		'categories' => $dropdown_categories,
		'current_date' => date('j M Y g:ia', current_time('timestamp')),
		'description_fields' => $description_fields,
		'home_url' => home_url(),
		'infinitescroll' => of_get_option('infinitescroll'),
		'lightbox' => of_get_option('lightbox'),
		'login_url' => wp_login_url($_SERVER['REQUEST_URI']),
		'nextselector' => $nextSelector,
		'nonce' => wp_create_nonce('ajax-nonce'),
		'price_html' => $price_html,
		'site_url' => site_url(),
		'stylesheet_directory_uri' => get_template_directory_uri(),
		'stylesheet_directory_uri_child' => get_stylesheet_directory_uri(),
		'tags_html' => $tags_html,
		'u' => $current_user->ID,
		'ui' => $current_user->display_name,
		'ul' => $current_user->user_nicename,
		'user_rewrite' => $wp_rewrite->author_base
	);
	
	wp_localize_script('ipin_custom', 'obj_ipin', $translation_array);
}
add_action('wp_enqueue_scripts', 'ipin_enqueue_scripts');


//Remove style version
function ipin_style_loader_src($src) {
  global $wp_version;

  $version_str = '?ver='.$wp_version;
  $version_str_offset = strlen($src) - strlen($version_str);

  if(substr($src, $version_str_offset) == $version_str)
    return substr($src, 0, $version_str_offset);
  else
    return $src;
}
add_filter('style_loader_src', 'ipin_style_loader_src');


/**
 * From Roots Theme http://roots.io
 * Cleaner walker for wp_nav_menu()
 *
 * Walker_Nav_Menu (WordPress default) example output:
 *   <li id="menu-item-8" class="menu-item menu-item-type-post_type menu-item-object-page menu-item-8"><a href="/">Home</a></li>
 *   <li id="menu-item-9" class="menu-item menu-item-type-post_type menu-item-object-page menu-item-9"><a href="/sample-page/">Sample Page</a></l
 *
 * Roots_Nav_Walker example output:
 *   <li class="menu-home"><a href="/">Home</a></li>
 *   <li class="menu-sample-page"><a href="/sample-page/">Sample Page</a></li>
 */
function is_element_empty($element) {
  $element = trim($element);
  return !empty($element);
}
 
class Roots_Nav_Walker extends Walker_Nav_Menu {
  function check_current($classes) {
    return preg_match('/(current[-_])|active|dropdown/', $classes);
  }

  function start_lvl(&$output, $depth = 0, $args = array()) {
    $output .= "\n<ul class=\"dropdown-menu\">\n";
  }

  function start_el(&$output, $item, $depth = 0, $args = array(), $id = 0) {
    $item_html = '';
    parent::start_el($item_html, $item, $depth, $args);

    if ($item->is_dropdown && ($depth === 0)) {
      $item_html = str_replace('<a', '<a class="dropdown-toggle" data-toggle="dropdown" data-target="#"', $item_html);
      $item_html = str_replace('</a>', ' <b class="caret"></b></a>', $item_html);
    }
    elseif (stristr($item_html, 'li class="divider')) {
      $item_html = preg_replace('/<a[^>]*>.*?<\/a>/iU', '', $item_html);
    }
    elseif (stristr($item_html, 'li class="dropdown-header')) {
      $item_html = preg_replace('/<a[^>]*>(.*)<\/a>/iU', '$1', $item_html);
    }

    $item_html = apply_filters('roots/wp_nav_menu_item', $item_html);
    $output .= $item_html;
  }

  function display_element($element, &$children_elements, $max_depth, $depth = 0, $args, &$output) {
    $element->is_dropdown = ((!empty($children_elements[$element->ID]) && (($depth + 1) < $max_depth || ($max_depth === 0))));

    if ($element->is_dropdown) {
      $element->classes[] = 'dropdown';
    }

    parent::display_element($element, $children_elements, $max_depth, $depth, $args, $output);
  }
}

/**
 * Remove the id="" on nav menu items
 * Return 'menu-slug' for nav menu classes
 */
function roots_nav_menu_css_class($classes, $item) {
  $slug = sanitize_title($item->title);
  $classes = preg_replace('/(current(-menu-|[-_]page[-_])(item|parent|ancestor))/', 'active', $classes);
  $classes = preg_replace('/^((menu|page)[-_\w+]+)+/', '', $classes);

  $classes[] = 'menu-' . $slug;

  $classes = array_unique($classes);

  return array_filter($classes, 'is_element_empty');
}
add_filter('nav_menu_css_class', 'roots_nav_menu_css_class', 10, 2);
add_filter('nav_menu_item_id', '__return_null');

/**
 * Clean up wp_nav_menu_args
 *
 * Remove the container
 * Use Roots_Nav_Walker() by default
 */
function roots_nav_menu_args($args = '') {
  $roots_nav_menu_args['container'] = false;

  if (!$args['items_wrap']) {
    $roots_nav_menu_args['items_wrap'] = '<ul class="%2$s">%3$s</ul>';
  }

  if (!$args['depth']) {
    $roots_nav_menu_args['depth'] = 2;
  }

  if (!$args['walker']) {
    $roots_nav_menu_args['walker'] = new Roots_Nav_Walker();
  }

  return array_merge($args, $roots_nav_menu_args);
}
add_filter('wp_nav_menu_args', 'roots_nav_menu_args');


//Relative date modified from wp-includes/formatting.php
if (!function_exists('ipin_human_time_diff')) :
function ipin_human_time_diff( $from, $to = '' ) {
	if ( empty( $to ) ) {
		$to = time();
	}
	
	$diff = (int) abs($to - $from);
	
	if ($diff <= 3600) {
		$mins = round($diff / 60);
		if ($mins <= 1) {
			$mins = 1;
		}

		if ($mins == 1) {
			$since = sprintf(__('%s min ago', 'ipin'), $mins);
		} else {
			$since = sprintf(__('%s mins ago', 'ipin'), $mins);
		}
	} else if (($diff <= 86400) && ($diff > 3600)) {
		$hours = round($diff / 3600);
		if ($hours <= 1) {
			$hours = 1;
		}
		
		if ($hours == 1) {
			$since = sprintf(__('%s hour ago', 'ipin'), $hours);
		} else {
			$since = sprintf(__('%s hours ago', 'ipin'), $hours);
		}
	} else if ($diff >= 86400 && $diff <= 31536000) {
		$days = round($diff / 86400);
		if ($days <= 1) {
			$days = 1;
		}

		if ($days == 1) {
			$since = sprintf(__('%s day ago', 'ipin'), $days);
		} else {
			$since = sprintf(__('%s days ago', 'ipin'), $days);
		}
	} else {
		$since = get_the_date();
	}
	
	return $since;
}
endif;


//Convert big numbers to K(thousands), M(illions)
function big_number_format($number) {
	if ($number > 5000 & $number < 1000000 ) {
		$number = number_format($number / 1000, 1) . 'K';
	} else if ($number >= 1000000) {
		$number = number_format($number / 1000000, 2) . 'M';
	}
	
	return $number;
}


//Feed content for pins
function ipin_feed_content($content) {
	global $post;
	
	$imgsrc = wp_get_attachment_image_src(get_post_thumbnail_id($post->ID), 'medium');
	if ($imgsrc[0] != '') {
		$content_before = '<p><a href="' . get_permalink($post->ID) . '"><img src="' . $imgsrc[0] . '" alt="" /></a></p>';
	}

	if (ipin_get_post_board()) {
		global $wp_taxonomies;
		$board_link = home_url('/' . $wp_taxonomies["board"]->rewrite['slug'] . '/' . sanitize_title(ipin_get_post_board()->name, '_') . '/' . ipin_get_post_board()->term_id . '/');
		$content_before .= '<p>' . __('Pinned onto', 'ipin') . ' <a href="' . $board_link . '">' . ipin_get_post_board()->name . '</a></p>';
	}
	
	return ($content_before . $content);
}
add_filter('the_excerpt_rss', 'ipin_feed_content');
add_filter('the_content_feed', 'ipin_feed_content');


//Nofollow links
//http://stackoverflow.com/questions/9571210/how-to-set-nofollow-rel-attribute-to-all-outbound-links-in-wordpress-any-plugin
if (!function_exists('ipin_nofollow_callback')) :
function ipin_nofollow_callback( $matches ) {
	$link = $matches[0];
	$exclude = '('. home_url() .')';
	if ( preg_match( '#href=\S('. $exclude .')#i', $link ) )
		return $link;

	if ( strpos( $link, 'rel=' ) === false ) {
		$link = preg_replace( '/(?<=<a\s)/', 'rel="nofollow" ', $link );
	} elseif ( preg_match( '#rel=\S(?!nofollow)#i', $link ) ) {
		$link = preg_replace( '#(?<=rel=.)#', 'nofollow ', $link );
	}
	
	//open in new window
	$link = str_replace('<a', '<a target="_blank" ', $link);
	
	return $link;   
}
endif;


//Comments
if (!function_exists('ipin_list_comments')) :
function ipin_list_comments($comment, $args, $depth) {
	global $wp_rewrite;
	$GLOBALS['comment'] = $comment;
	?>
	<li <?php comment_class(); ?> id="comment-<?php comment_ID(); ?>">

		<?php $comment_author = get_user_by('id', $comment->user_id); ?>
		<div class="comment-avatar">
			<?php if ($comment_author) { ?>
			<a href="<?php echo home_url('/' . $wp_rewrite->author_base . '/') . $comment_author->user_nicename; ?>/">
			<?php } ?>
				<?php echo get_avatar($comment->user_id, '48'); ?>
			<?php if ($comment_author) { ?>
			</a>
			<?php } ?>
		</div>

		<div class="pull-right<?php if (!is_user_logged_in()) echo ' hide'; ?>"><?php comment_reply_link(array('reply_text' => __('Reply', 'ipin'), 'login_text' => __('Reply', 'ipin'), 'depth' => $depth, 'max_depth'=> $args['max_depth'])); ?></div>

		<div class="comment-content">

			<strong><span <?php comment_class(); ?>>
			<?php if ($comment_author) { ?>
			<a class="url" href="<?php echo home_url('/' . $wp_rewrite->author_base . '/') . $comment_author->user_nicename; ?>/">
			<?php } ?>
				<?php echo $comment->comment_author; ?>
			<?php if ($comment_author) { ?>
			</a>
			<?php } ?>
			</span></strong> 
			<span class="text-muted">&#8226; <?php echo ipin_human_time_diff(mysql2date('U', get_gmt_from_date(get_comment_date('Y-m-d H:i:s')))); ?></span> <a href="#comment-<?php comment_ID() ?>" title="<?php esc_attr_e('Comment Permalink', 'ipin'); ?>">#</a> <?php edit_comment_link('e','',''); ?>
			<?php if ($comment->comment_approved == '0') : ?>
			<br /><em><?php _e('Your comment is awaiting moderation.', 'ipin'); ?></em>
			<?php endif; ?>
			
			<?php comment_text(); ?>
		</div>
	<?php
}
endif;


//Get pin price
if (!function_exists('ipin_get_post_price')) :
function ipin_get_post_price($show_symbol = true, $post_id = null) {
	if (empty($post_id) && isset( $GLOBALS['post'])) {
		global $post;
		$post_id = $post->ID;
	}
	
	$post_id = (int)$post_id;
	$post_price = get_post_meta($post_id, '_Price', true);
	
	if ($post_price != '' && $show_symbol != false) {
		if (of_get_option('price_currency_position') == 'left') {
			$post_price = of_get_option('price_currency') . $post_price;
		} else {
			$post_price = $post_price . of_get_option('price_currency');
		}
	}
	
	return apply_filters('ipin_get_post_price', $post_price);
}
endif;


//Get pin board info
if (!function_exists('ipin_get_post_board')) :
function ipin_get_post_board($post_id = null) {
	if (empty($post_id) && isset( $GLOBALS['post'])) {
		global $post;
		$post_id = $post->ID;
	}
	
	$post_id = (int)$post_id;
	$boards = get_the_terms($post_id, 'board');
	
	$board = '';
	if ($boards) {
		foreach ($boards as $board) {
			$board = $board;
		}
	}
	
	return $board;
}
endif;


//Get post video embed code
if (!function_exists('ipin_get_post_video')) :
function ipin_get_post_video($url = '') {
	if ($url == '') {
		global $post;
		$url = get_post_meta($post->ID, "_Photo Source", true);	
	}

	$embed_code = '';
	
	if (preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i', str_replace('&#038;', '&', $url), $videomatch)) {
		if (strpos($url, 'list=') !== FALSE) {
			parse_str(html_entity_decode($url), $youtube_query);
			$embed_code = '<iframe id="video-embed" src="//www.youtube.com/embed/' . $videomatch[1] . '?list=' . $youtube_query['list'] . '&rel=0&autoplay=1&wmode=opaque" width="700" height="393" frameborder="0" allowfullscreen></iframe>';
		} else {
			$embed_code = '<iframe id="video-embed" src="//www.youtube.com/embed/' . $videomatch[1] . '?rel=0&autoplay=1&wmode=opaque" width="700" height="393" frameborder="0" allowfullscreen></iframe>';			
		}
	} else if (strpos($url, 'youtube.com/playlist?list=') !== FALSE && sscanf(parse_url($url, PHP_URL_QUERY), 'list=%s', $video_id)){
		$embed_code = '<iframe id="video-embed" src="//www.youtube.com/embed/videoseries?list=' . $video_id . '&rel=0&autoplay=1&wmode=opaque" width="700" height="393" frameborder="0" allowfullscreen></iframe>';
	} else if (strpos(parse_url($url, PHP_URL_HOST), 'vimeo.com') !== FALSE && sscanf(parse_url($url, PHP_URL_PATH), '/%d', $video_id)){
		$embed_code = '<iframe id="video-embed" src="http://player.vimeo.com/video/' . $video_id . '?title=0&amp;byline=0&amp;portrait=0&amp;badge=0&amp;color=ffffff&amp;autoplay=1" width="700" height="393" webkitAllowFullScreen mozallowfullscreen allowFullScreen style="border:none;"></iframe>';
	} else if (strpos(parse_url($url, PHP_URL_HOST), 'soundcloud.com') !== FALSE){
		$embed_code = wp_oembed_get($url, array('width'=>700, 'height'=>393));
		if ($embed_code)
			$embed_code = str_replace('<iframe', '<iframe id="video-embed"', $embed_code);
			$embed_code = str_replace('"></iframe>', '&amp;auto_play=true&amp;hide_related=true"></iframe>', $embed_code);
	} else {
		$embed_code = apply_filters('ipin_get_post_video', $embed_code, $url);
	}

	return $embed_code;
}
endif;


//Repins
function ipin_repin() {
	$nonce = $_POST['nonce'];

	if (!wp_verify_nonce($nonce, 'ajax-nonce'))
		die();

	do_action('ipin_before_repin', $_POST);

	global $wpdb, $user_ID, $user_identity;
	$original_id  = $_POST['repin_post_id'];
	$duplicate = get_post($original_id, 'ARRAY_A');
	$original_post_author = $duplicate['post_author']; //store original author for use later
	$duplicate['post_author'] = $user_ID;

	$allowed_html = array(
		'a' => array(
			'href' => true
		),
		'em' => array(),
		'blockquote' => array(),
		'p' => array(),
		'li' => array(),
		'ol' => array(),
		'strong' => array(),
		'ul' => array(),
	);
	
	if (of_get_option('htmltags') != 'enable') {
		unset($allowed_html);
		$allowed_html = array();
	}
	
	if (of_get_option('form_title_desc') != 'separate') {
		$duplicate['post_title'] = balanceTags(wp_kses($_POST['repin_title'], $allowed_html), true);
	} else {
		$duplicate['post_title'] = sanitize_text_field($_POST['repin_title']);
	}

	$duplicate['post_content'] = balanceTags(wp_kses($_POST['repin_content'], $allowed_html), true);

	unset($duplicate['ID']);
	unset($duplicate['post_date']);
	unset($duplicate['post_date_gmt']);
	unset($duplicate['post_modified']);
	unset($duplicate['post_modified_gmt']);
	unset($duplicate['post_name']);
	unset($duplicate['guid']);
	unset($duplicate['comment_count']);

	remove_action('save_post', 'ipin_save_post', 50, 2);
	$duplicate_id = wp_insert_post($duplicate);

	//set board
	$board_add_new = sanitize_text_field($_POST['repin_board_add_new']);
	$board_add_new_category = $_POST['repin_board_add_new_category'];
	$board_parent_id = get_user_meta($user_ID, '_Board Parent ID', true);
	if ($board_add_new !== '') {
		$board_children = get_term_children($board_parent_id, 'board');
		$found = '0';

		foreach ($board_children as $board_child) {
			$board_child_term = get_term_by('id', $board_child, 'board');
			if (stripslashes(htmlspecialchars($board_add_new, ENT_NOQUOTES, 'UTF-8')) == $board_child_term->name) {
				$found = '1';
				$found_board_id = $board_child_term->term_id;
				break;
			}
		}

		if ($found == '0') {
			$slug = wp_unique_term_slug($board_add_new . '__ipinboard', 'board'); //append __ipinboard to solve slug conflict with category and 0 in title
			if ($board_add_new_category == '-1')
				$board_add_new_category = '1';

			$new_board_id = wp_insert_term (
				$board_add_new,
				'board',
				array(
					'description' => $board_add_new_category,
					'parent' => $board_parent_id,
					'slug' => $slug
				)
			);
			
			$repin_board = $new_board_id['term_id'];
		} else {
			$repin_board = $found_board_id;
		}
	} else {
		$repin_board = $_POST['repin_board'];		
	}
	wp_set_post_terms($duplicate_id, array($repin_board), 'board');
	update_user_meta($user_ID, 'ipin_last_board', $repin_board);

	//set category
	$category_id = get_term_by('id', $repin_board, 'board');
	wp_set_post_terms($duplicate_id, array($category_id->description), 'category');

	//update postmeta for new post
	if ('' == $repin_of_repin = get_post_meta($original_id, '_Original Post ID', true)) { //check if is a simple repin or a repin of a repin
		add_post_meta($duplicate_id, '_Original Post ID', $original_id);
	} else {
		add_post_meta($duplicate_id, '_Original Post ID', $original_id);
		add_post_meta($duplicate_id, '_Earliest Post ID', $repin_of_repin); //the very first post/pin		
	}
	add_post_meta($duplicate_id, '_Photo Source', get_post_meta($original_id, '_Photo Source', true));
	add_post_meta($duplicate_id, '_Photo Source Domain', get_post_meta($original_id, '_Photo Source Domain', true));
	add_post_meta($duplicate_id, '_thumbnail_id', get_post_meta($original_id, '_thumbnail_id', true));
	
	//add tags
	wp_set_post_tags($duplicate_id, sanitize_text_field($_POST['repin_tags']));

	//add price	
	if ($_POST['repin_price']) {
		if (strpos($_POST['repin_price'], '.') !== false) {
			$_POST['repin_price'] = number_format($_POST['repin_price'], 2);
		}
		
		add_post_meta($duplicate_id, '_Price',sanitize_text_field($_POST['repin_price']));
	}

	//update postmeta for original post
	$postmeta_repin_count = get_post_meta($original_id, '_Repin Count', true);
	$postmeta_repin_post_id = get_post_meta($original_id, '_Repin Post ID');
	$repin_post_id = $postmeta_repin_post_id[0];

	if (!is_array($repin_post_id))
		$repin_post_id = array();

	array_push($repin_post_id, $duplicate_id);
	update_post_meta($original_id, '_Repin Post ID', $repin_post_id);
	update_post_meta($original_id, '_Repin Count', ++$postmeta_repin_count);
	
	//add to notification center
	if ($user_ID != $original_post_author) {
		$wpdb->query(
			$wpdb->prepare(
				"INSERT INTO " . $wpdb->prefix . "ipin_notifications (user_id, notification_date, notification_type, notification_from, notification_post_id)
				VALUES (%d, %s, %s, %d, %d)
				"
				, $original_post_author, current_time('mysql'), 'repin', $user_ID, $original_id
			)
		);
		$ipin_user_notifications_count = get_user_meta($original_post_author, 'ipin_user_notifications_count', true);
		update_user_meta($original_post_author, 'ipin_user_notifications_count', ++$ipin_user_notifications_count);
	}
	
	//email author
	if (get_user_meta($original_post_author, 'ipin_user_notify_repins', true) != '' && $user_ID != $original_post_author) {
		$blogname = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);
		$message = sprintf(__('%s repinned your "%s" pin at %s', 'ipin'), $user_identity, preg_replace('/[\n\r]/', ' ', html_entity_decode(sanitize_text_field(get_the_title($original_id)), ENT_QUOTES, 'UTF-8')), get_permalink($duplicate_id)) . "\r\n\r\n";
		$message .= "-------------------------------------------\r\n";
		$message .= sprintf(__('To change your notification settings, visit %s', 'ipin'), home_url('/settings/'));
		wp_mail(get_the_author_meta('user_email', $original_post_author), sprintf(__('[%s] Someone repinned your pin', 'ipin'), $blogname), $message);
	}
	
	//add new board to followers who fully follow user
	if ($new_board_id && !is_wp_error($new_board_id)) {
		$usermeta_followers_id_allboards = get_user_meta($user_ID, '_Followers User ID All Boards');
		$followers_id_allboards = $usermeta_followers_id_allboards[0];
		
		if (!empty($followers_id_allboards)) {
			foreach ($followers_id_allboards as $followers_id_allboard) {
				$usermeta_following_board_id = get_user_meta($followers_id_allboard, '_Following Board ID');
				$following_board_id = $usermeta_following_board_id[0];
				array_unshift($following_board_id, $new_board_id['term_id']);
				update_user_meta($followers_id_allboard, '_Following Board ID', $following_board_id);
			}
		}
	}
	
	do_action('ipin_after_repin', $duplicate_id);
	
	echo get_permalink($duplicate_id);

	exit;
}
add_action('wp_ajax_ipin-repin', 'ipin_repin');

function ipin_repin_board_populate() {
	global $user_ID;
	echo ipin_dropdown_boards(null, get_user_meta($user_ID, 'ipin_last_board', true));
	exit;
}
add_action('wp_ajax_ipin-repin-board-populate', 'ipin_repin_board_populate');


//Likes
function ipin_like() {
	$nonce = $_POST['nonce'];

	if (!wp_verify_nonce($nonce, 'ajax-nonce'))
		die();
		
	do_action('ipin_before_like', $_POST);

	global $wpdb, $user_ID, $user_identity;
	$post_id = $_POST['post_id'];

	if ($_POST['ipin_like'] == 'like') {
		$postmeta_count = get_post_meta($post_id, '_Likes Count', true);
		$postmeta_user_id = get_post_meta($post_id, '_Likes User ID');
		$likes_user_id = $postmeta_user_id[0];

		if (!is_array($likes_user_id)) {
			$likes_user_id = array();
		} else {
			if (in_array($user_ID, $likes_user_id)) {
				echo $postmeta_count;
				exit;
			}
		}

		//update postmeta
		array_push($likes_user_id, $user_ID);
		update_post_meta($post_id, '_Likes User ID', $likes_user_id);
		update_post_meta($post_id, '_Likes Count', ++$postmeta_count);

		//update usermeta
		$usermeta_count = get_user_meta($user_ID, '_Likes Count', true);
		$usermeta_post_id = get_user_meta($user_ID, '_Likes Post ID');
		$likes_post_id = $usermeta_post_id[0];

		if (!is_array($likes_post_id))
			$likes_post_id = array();

		array_unshift($likes_post_id, $post_id);

		update_user_meta($user_ID, '_Likes Post ID', $likes_post_id);
		update_user_meta($user_ID, '_Likes Count', ++$usermeta_count);

		//add to notification center
		$wpdb->query(
			$wpdb->prepare(
				"INSERT INTO " . $wpdb->prefix . "ipin_notifications (user_id, notification_date, notification_type, notification_from, notification_post_id)
				VALUES (%d, %s, %s, %d, %d)
				"
				, $_POST['post_author'], current_time('mysql'), 'like', $user_ID, $post_id
			)
		);
		$ipin_user_notifications_count = get_user_meta($_POST['post_author'], 'ipin_user_notifications_count', true);
		update_user_meta($_POST['post_author'], 'ipin_user_notifications_count', ++$ipin_user_notifications_count);

		//email author
		if (get_user_meta($_POST['post_author'], 'ipin_user_notify_likes', true) != '') {
			$blogname = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);
			$message = sprintf(__('%s liked your "%s" pin at %s', 'ipin'), $user_identity, preg_replace('/[\n\r]/', ' ', html_entity_decode(sanitize_text_field(get_the_title($post_id)), ENT_QUOTES, 'UTF-8')), get_permalink($post_id)) . "\r\n\r\n";
			$message .= "-------------------------------------------\r\n";
			$message .= sprintf(__('To change your notification settings, visit %s', 'ipin'), home_url('/settings/'));
			wp_mail(get_the_author_meta('user_email', $_POST['post_author']), sprintf(__('[%s] Someone liked your pin', 'ipin'), $blogname), $message);
		}

		echo $postmeta_count;

	} else if ($_POST['ipin_like'] == 'unlike') {
		//update postmeta
		$postmeta_count = get_post_meta($post_id, '_Likes Count', true);
		$postmeta_user_id = get_post_meta($post_id, '_Likes User ID');
		$likes_user_id = $postmeta_user_id[0];
		
		if (!in_array($user_ID, $likes_user_id)) {
			echo $postmeta_count;
			exit;
		}
		
		unset($likes_user_id[array_search($user_ID, $likes_user_id)]);
		$likes_user_id = array_values($likes_user_id);
		update_post_meta($post_id, '_Likes User ID', $likes_user_id);
		update_post_meta($post_id, '_Likes Count', --$postmeta_count);

		//update usermeta
		$usermeta_count = get_user_meta($user_ID, '_Likes Count', true);
		$usermeta_post_id = get_user_meta($user_ID, '_Likes Post ID');
		$likes_post_id = $usermeta_post_id[0];

		unset($likes_post_id[array_search($post_id, $likes_post_id)]);
		$likes_post_id = array_values($likes_post_id);

		update_user_meta($user_ID, '_Likes Post ID', $likes_post_id);
		update_user_meta($user_ID, '_Likes Count', --$usermeta_count);

		echo $postmeta_count;
	}
	
	do_action('ipin_after_like', $post_id, $likes_user_id);
	
	exit;
}
add_action('wp_ajax_ipin-like', 'ipin_like');


if (!function_exists('ipin_liked')) :
function ipin_liked($post_id) {
	global $user_ID;
	$postmeta_user_id = get_post_meta($post_id, '_Likes User ID');
	$likes_user_id = $postmeta_user_id[0];

	if (!is_array($likes_user_id))
		$likes_user_id = array();

	if (in_array($user_ID, $likes_user_id)) {
		return true;
	}
	return false;
}
endif;


//Follows
function ipin_follow() {
	$nonce = $_POST['nonce'];

	if (!wp_verify_nonce($nonce, 'ajax-nonce'))
		die();
	
	do_action('ipin_before_follow', $_POST);
	
	global $wpdb, $user_ID, $user_identity;
	$board_parent_id = $_POST['board_parent_id'];
	$board_id = $_POST['board_id'];
	$author_id = $_POST['author_id'];

	if ($_POST['ipin_follow'] == 'follow') {
		//update usermeta following for current user
		$usermeta_following_count = get_user_meta($user_ID, '_Following Count', true);
		$usermeta_following_user_id = get_user_meta($user_ID, '_Following User ID');
		$following_user_id = $usermeta_following_user_id[0];
		$usermeta_following_board_id = get_user_meta($user_ID, '_Following Board ID');
		$following_board_id = $usermeta_following_board_id[0];

		if (!is_array($following_user_id))
			$following_user_id = array();

		if (!is_array($following_board_id))
			$following_board_id = array();

		if ($board_parent_id == '0') {
			//insert all sub-boards from author
			$author_boards = get_term_children($board_id, 'board');

			foreach ($author_boards as $author_board) {
				if (!in_array($author_board, $following_board_id)) {
					array_unshift($following_board_id, $author_board);
				}
			}

			//track followers who fully follow user to update them when user create a new board
			$usermeta_followers_id_allboards = get_user_meta($author_id, '_Followers User ID All Boards');
			$followers_id_allboards = $usermeta_followers_id_allboards[0];

			if (!is_array($followers_id_allboards))
				$followers_id_allboards = array();

			if (!in_array($user_ID, $followers_id_allboards)) {
				array_unshift($followers_id_allboards, $user_ID);
				update_user_meta($author_id, '_Followers User ID All Boards', $followers_id_allboards);
			}
		}
		array_unshift($following_board_id, $board_id);
		update_user_meta($user_ID, '_Following Board ID', $following_board_id);

		if (!in_array($author_id, $following_user_id)) {
			array_unshift($following_user_id, $author_id);
			update_user_meta($user_ID, '_Following User ID', $following_user_id);
			update_user_meta($user_ID, '_Following Count', ++$usermeta_following_count);
		}

		//update usermeta followers for author
		$usermeta_followers_count = get_user_meta($author_id, '_Followers Count', true);
		$usermeta_followers_id = get_user_meta($author_id, '_Followers User ID');
		$followers_id = $usermeta_followers_id[0];

		if (!is_array($followers_id))
			$followers_id = array();

		if (!in_array($user_ID, $followers_id)) {
			array_unshift($followers_id, $user_ID);
			update_user_meta($author_id, '_Followers User ID', $followers_id);
			update_user_meta($author_id, '_Followers Count', ++$usermeta_followers_count);
		}

		//add to notification center
		$wpdb->query(
			$wpdb->prepare(
				"INSERT INTO " . $wpdb->prefix . "ipin_notifications (user_id, notification_date, notification_type, notification_from)
				VALUES (%d, %s, %s, %d)
				"
				, $author_id, current_time('mysql'), 'following', $user_ID
			)
		);
		$ipin_user_notifications_count = get_user_meta($author_id, 'ipin_user_notifications_count', true);
		update_user_meta($author_id, 'ipin_user_notifications_count', ++$ipin_user_notifications_count);

		//email author
		if (get_user_meta($author_id, 'ipin_user_notify_follows', true) != '') {
			$blogname = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);
			$message = sprintf(__('%s is now following you. View %s\'s profile at %s', 'ipin'), $user_identity, $user_identity, get_author_posts_url($user_ID)) . "\r\n\r\n";
			$message .= "-------------------------------------------\r\n";
			$message .= sprintf(__('To change your notification settings, visit %s', 'ipin'), home_url('/settings/'));
			wp_mail(get_the_author_meta('user_email', $author_id), sprintf(__('[%s] Someone is following you', 'ipin'), $blogname), $message);
		}
	} else if ($_POST['ipin_follow'] == 'unfollow') {		
		//update usermeta following for current user
		$usermeta_following_count = get_user_meta($user_ID, '_Following Count', true);
		$usermeta_following_user_id = get_user_meta($user_ID, '_Following User ID');
		$following_user_id = $usermeta_following_user_id[0];
		$usermeta_following_board_id = get_user_meta($user_ID, '_Following Board ID');
		$following_board_id = $usermeta_following_board_id[0];

		if ($board_parent_id == '0') {
			$author_boards = get_term_children($board_id, 'board');

			//prepare to remove all boards from author
			foreach ($author_boards as $author_board) {
				if (in_array($author_board, $following_board_id)) {
					unset($following_board_id[array_search($author_board, $following_board_id)]);
					$following_board_id = array_values($following_board_id);
				}
			}

			//remove parent board as well
			unset($following_board_id[array_search($board_id, $following_board_id)]);
			$following_board_id = array_values($following_board_id);

			unset($following_user_id[array_search($author_id, $following_user_id)]);
			$following_user_id = array_values($following_user_id);

			update_user_meta($user_ID, '_Following Board ID', $following_board_id);
			update_user_meta($user_ID, '_Following User ID', $following_user_id);
			update_user_meta($user_ID, '_Following Count', --$usermeta_following_count);

			//update usermeta followers for author
			$usermeta_followers_count = get_user_meta($author_id, '_Followers Count', true);

			$usermeta_followers_id = get_user_meta($author_id, '_Followers User ID');
			$followers_id = $usermeta_followers_id[0];
			unset($followers_id[array_search($user_ID, $followers_id)]);
			$followers_id = array_values($followers_id);

			$usermeta_followers_id_allboards = get_user_meta($author_id, '_Followers User ID All Boards');
			$followers_id_allboards = $usermeta_followers_id_allboards[0];
			unset($followers_id_allboards[array_search($user_ID, $followers_id_allboards)]);
			$followers_id_allboards = array_values($followers_id_allboards);

			update_user_meta($author_id, '_Followers User ID', $followers_id);
			update_user_meta($author_id, '_Followers User ID All Boards', $followers_id_allboards);
			update_user_meta($author_id, '_Followers Count', --$usermeta_followers_count);
			
			echo 'unfollow_all';
		} else {
			unset($following_board_id[array_search($board_id, $following_board_id)]);
			$following_board_id = array_values($following_board_id);

			$author_boards = get_term_children($board_parent_id, 'board');
			$board_following_others = 'no';

			//check if current user is following other boards from author
			//if no longer following other boards, also unfollow user
			foreach ($following_board_id as $following_board) {
				if (in_array($following_board, $author_boards)) {
					$board_following_others = 'yes';
					break;
				}
			}

			if ($board_following_others == 'no') {
				//remove parent board
				unset($following_board_id[array_search($board_parent_id, $following_board_id)]);
				$following_board_id = array_values($following_board_id);

				unset($following_user_id[array_search($author_id, $following_user_id)]);
				$following_user_id = array_values($following_user_id);

				update_user_meta($user_ID, '_Following User ID', $following_user_id);
				update_user_meta($user_ID, '_Following Count', --$usermeta_following_count);

				//update usermeta followers for author
				$usermeta_followers_count = get_user_meta($author_id, '_Followers Count', true);

				$usermeta_followers_id = get_user_meta($author_id, '_Followers User ID');
				$followers_id = $usermeta_followers_id[0];
				unset($followers_id[array_search($user_ID, $followers_id)]);
				$followers_id = array_values($followers_id);

				$usermeta_followers_id_allboards = get_user_meta($author_id, '_Followers User ID All Boards');
				$followers_id_allboards = $usermeta_followers_id_allboards[0];
				unset($followers_id_allboards[array_search($user_ID, $followers_id_allboards)]);
				$followers_id_allboards = array_values($followers_id_allboards);

				update_user_meta($author_id, '_Followers User ID', $followers_id);
				update_user_meta($author_id, '_Followers User ID All Boards', $followers_id_allboards);
				update_user_meta($author_id, '_Followers Count', --$usermeta_followers_count);

				echo 'unfollow_all';
			}
			update_user_meta($user_ID, '_Following Board ID', $following_board_id);
		}
	}
	
	do_action('ipin_after_follow', $board_id, $user_ID, $author_id);
	
	exit;
}
add_action('wp_ajax_ipin-follow', 'ipin_follow');


if (!function_exists('ipin_followed')) :
function ipin_followed($board_id) {
	global $user_ID;
	$usermeta_board_id = get_user_meta($user_ID, '_Following Board ID');
	$follow_board_id = $usermeta_board_id[0];

	if (!is_array($follow_board_id))
		$follow_board_id = array();
	
	if (in_array($board_id, $follow_board_id)) {
		return true;
	}
	return false;
}
endif;


//Ajax comments
function ipin_ajaxify_comments($comment_ID, $comment_status) {
	if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
		if ('spam' !== $comment_status) {
			if ('0' == $comment_status) {
				wp_notify_moderator($comment_ID);
			} else if ('1' == $comment_status) {
				//email author
				global $wpdb, $user_ID, $user_identity;
				$commentdata = get_comment($comment_ID, 'ARRAY_A');
				$postdata = get_post($commentdata['comment_post_ID'], 'ARRAY_A');
				$blogname = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);

				//add to notification center
				if ($user_ID != $postdata['post_author']) {
					$wpdb->query(
						$wpdb->prepare(
							"INSERT INTO " . $wpdb->prefix . "ipin_notifications (user_id, notification_date, notification_type, notification_from, notification_post_id)
							VALUES (%d, %s, %s, %d, %d)
							"
							, $postdata['post_author'], current_time('mysql'), 'comment', $user_ID, $postdata['ID']
						)
					);
					$ipin_user_notifications_count = get_user_meta($postdata['post_author'], 'ipin_user_notifications_count', true);
					update_user_meta($postdata['post_author'], 'ipin_user_notifications_count', ++$ipin_user_notifications_count);
				}

				if (get_user_meta($postdata['post_author'], 'ipin_user_notify_comments', true) != '' && $user_ID != $postdata['post_author']) {
					$message = sprintf(__('%s commented on your "%s" pin at %s', 'ipin'), $user_identity, preg_replace('/[\n\r]/', ' ', html_entity_decode(sanitize_text_field($postdata['post_title']), ENT_QUOTES, 'UTF-8')), get_permalink($postdata['ID'])) . "\r\n\r\n";
					$message .= "-------------------------------------------\r\n";
					$message .= sprintf(__('To change your notification settings, visit %s', 'ipin'), home_url('/settings/'));
					wp_mail(get_the_author_meta('user_email', $postdata['post_author']), sprintf(__('[%s] Someone commented on your pin', 'ipin'), $blogname), $message);
				}
				
				$comment_author_domain = @gethostbyaddr($commentdata['comment_author_IP']);
				
				//email admin
				if (get_option('comments_notify') && $user_ID != $postdata['post_author']) {
					$admin_message  = sprintf(__('New comment on the pin "%s"', 'ipin'), preg_replace('/[\n\r]/', ' ', html_entity_decode(sanitize_text_field($postdata['post_title']), ENT_QUOTES, 'UTF-8'))) . "\r\n";
					$admin_message .= sprintf(__('Author : %1$s (IP: %2$s , %3$s)', 'ipin'), $commentdata['comment_author'], $commentdata['comment_author_IP'], $comment_author_domain) . "\r\n";
					$admin_message .= sprintf(__('E-mail : %s', 'ipin'), $commentdata['comment_author_email']) . "\r\n";
					$admin_message .= sprintf(__('URL    : %s', 'ipin'), $commentdata['comment_author_url']) . "\r\n";
					$admin_message .= sprintf(__('Whois  : http://whois.arin.net/rest/ip/%s', 'ipin'), $commentdata['comment_author_IP']) . "\r\n";
					$admin_message .= __('Comment:', 'ipin') . " \r\n" . $commentdata['comment_content'] . "\r\n\r\n";
					$admin_message .= __('You can see all comments on this pin here:', 'ipin') . " \r\n";
					$admin_message .= get_permalink($postdata['ID']) . "#comments\r\n\r\n";
					$admin_message .= sprintf(__('Permalink: %s', 'ipin'), get_permalink($postdata['ID']) . '#comment-' . $comment_ID) . "\r\n";
					$admin_message .= sprintf(__('Delete it: %s', 'ipin'), admin_url("comment.php?action=delete&c=$comment_ID")) . "\r\n";
					$admin_message .= sprintf(__('Spam it: %s', 'ipin'), admin_url("comment.php?action=spam&c=$comment_ID")) . "\r\n";
					$admin_subject = sprintf(__('[%1$s] Comment: "%2$s"', 'ipin'), $blogname, preg_replace('/[\n\r]/', ' ', html_entity_decode(sanitize_text_field($postdata['post_title']), ENT_QUOTES, 'UTF-8')));
					wp_mail(get_option('admin_email'), $admin_subject, $admin_message);
				}

				echo 'success';
			}
		}
		exit;
	}
}
add_action('comment_post', 'ipin_ajaxify_comments', 500, 2);


//Auto assign board if no board e.g adding post from backend
function ipin_save_post($post_id, $post) {
	if ($post->post_type != 'post' || $post->post_status != 'publish')
		return;
	
	//Exclude blog category
	$boards = get_the_terms($post_id, 'board');
	if (!$boards && !in_category(ipin_blog_cats(), $post_id)) {
		$board_parent_id = get_user_meta($post->post_author, '_Board Parent ID', true);
		$board_children = get_term_children($board_parent_id, 'board');
		$found = '0';
		
		$post_category = get_the_category($post_id);	
		
		foreach ($board_children as $board_child) {
			$board_child_term = get_term_by('id', $board_child, 'board');
			if ($board_child_term->name == $post_category[0]->cat_name) {
				$found = '1';
				$found_board_id = $board_child_term->term_id;
				break;
			}
		}
		
		if ($found == '0') {
			$slug = wp_unique_term_slug($post_category[0]->cat_name . '__ipinboard', 'board'); //append __ipinboard to solve slug conflict with category and 0 in title

			$new_board_id = wp_insert_term (
				$post_category[0]->cat_name,
				'board',
				array(
					'description' => $post_category[0]->cat_ID,
					'parent' => $board_parent_id,
					'slug' => $slug
				)
			);
			$postdata_board = $new_board_id['term_id'];
		} else {
			$postdata_board = $found_board_id;
		}
		
		//set board
		wp_set_post_terms($post_id, array($postdata_board), 'board');
		
		//category ID is stored in the board description field
		$category_id = get_term_by('id', $postdata_board, 'board');
		
		//set category
		wp_set_object_terms($post_id, array(intval($category_id->description)), 'category');
	}
}
add_action('save_post', 'ipin_save_post', 50, 2);


//Clean up when delete post
function ipin_before_delete_post($post_id) {
	global $wpdb;

	$original_id = get_post_meta($post_id, '_Original Post ID', true);

	if ($original_id == '') { //this is an original post
		//remove instances from repinned postmeta
		$wpdb->query(
			$wpdb->prepare(
				"UPDATE $wpdb->postmeta
				SET meta_value = 'deleted'
				WHERE meta_key = '_Original Post ID'
				AND meta_value = %d
				"
				,$post_id
			)
		);

		//remove instances from repinned of repinned postmeta
		$wpdb->query(
			$wpdb->prepare(
				"UPDATE $wpdb->postmeta
				SET meta_value = 'deleted'
				WHERE meta_key = '_Earliest Post ID'
				AND meta_value = %d
				"
				, $post_id
			)
		);

		//remove instances from usermeta
		$postmeta_likes_user_ids = get_post_meta($post_id, '_Likes User ID');
		$likes_user_ids = $postmeta_likes_user_ids[0];

		if (is_array($likes_user_ids)) {
			foreach ($likes_user_ids as $likes_user_id) {
				$usermeta_count = get_user_meta($likes_user_id, '_Likes Count', true);
				$usermeta_post_id = get_user_meta($likes_user_id, '_Likes Post ID');
				$likes_post_id = $usermeta_post_id[0];
		
				unset($likes_post_id[array_search($post_id, $likes_post_id)]);
				$likes_post_id = array_values($likes_post_id);
		
				update_user_meta($likes_user_id, '_Likes Post ID', $likes_post_id);
				update_user_meta($likes_user_id, '_Likes Count', --$usermeta_count);
			}
		}
	} else { //this is a repinned post
		//remove instances from repinned postmeta
		$wpdb->query(
			$wpdb->prepare(
				"UPDATE $wpdb->postmeta
				SET meta_value = 'deleted'
				WHERE meta_key = '_Original Post ID'
				AND meta_value = %d
				"
				, $post_id
			)
		);

		//remove instances from original postmeta
		$postmeta_repin_count = get_post_meta($original_id, '_Repin Count', true);
		$postmeta_repin_post_id = get_post_meta($original_id, '_Repin Post ID');
		$repin_post_id = $postmeta_repin_post_id[0];
		unset($repin_post_id[array_search($post_id, $repin_post_id)]);
		$repin_post_id = array_values($repin_post_id);

		update_post_meta($original_id, '_Repin Post ID', $repin_post_id);
		update_post_meta($original_id, '_Repin Count', --$postmeta_repin_count);

		//remove instances from usermeta
		$postmeta_likes_user_ids = get_post_meta($post_id, '_Likes User ID');
		$likes_user_ids = $postmeta_likes_user_ids[0];

		if (is_array($likes_user_ids)) {
			foreach ($likes_user_ids as $likes_user_id) {
				$usermeta_count = get_user_meta($likes_user_id, '_Likes Count', true);
				$usermeta_post_id = get_user_meta($likes_user_id, '_Likes Post ID');
				$likes_post_id = $usermeta_post_id[0];
		
				unset($likes_post_id[array_search($post_id, $likes_post_id)]);
				$likes_post_id = array_values($likes_post_id);
		
				update_user_meta($likes_user_id, '_Likes Post ID', $likes_post_id);
				update_user_meta($likes_user_id, '_Likes Count', --$usermeta_count);
			}
		}
	}

	//delete or assign attachment
	$boards = get_the_terms($post_id, 'board');
	if ($boards && !is_wp_error($boards)) {
		$thumbnail_id = get_post_meta($post_id, '_thumbnail_id', true);
		
		//look for other posts using the same featured image e.g thru repin
		$post_same_thumbnail = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT post_id FROM $wpdb->postmeta
				WHERE meta_key = '_thumbnail_id'
				AND meta_value = %d
				AND post_id != %d
				LIMIT 1
				"
				, $thumbnail_id, $post_id
			)
		);
	
		if ($post_same_thumbnail) {
			$wpdb->query(
				$wpdb->prepare(
					"UPDATE $wpdb->posts
					SET post_parent = %d
					WHERE ID = %d
					"
					, $post_same_thumbnail, $thumbnail_id
				)
			);
		} else {
			wp_delete_attachment($thumbnail_id, true);
		}
	}
	
	//delete notifications
	$wpdb->query(
		$wpdb->prepare(
			"DELETE FROM " . $wpdb->prefix . "ipin_notifications
			WHERE notification_post_id = %d
			"
			, $post_id
		)
	);
}
add_action('before_delete_post', 'ipin_before_delete_post');


//Delete account
function ipin_delete_account() {
	$nonce = $_POST['nonce'];

	if (!wp_verify_nonce($nonce, 'ajax-nonce'))
		die();
		
	if (current_user_can('administrator')) {
		$return_url = home_url('');
	} else {
		$return_url = home_url('/login/?action=loggedout');
	}
	
	global $user_ID;
	$user_id = $_POST['user_id'];

	if (current_user_can('administrator') || $user_id == $user_ID) {
		wp_delete_user($user_id);
	}

	echo $return_url;
	exit;
}
add_action('wp_ajax_ipin-delete-account', 'ipin_delete_account');


//Clean up usermeta & boards when delete user
function ipin_delete_user($id) {
	global $wpdb;

	//user_id is name of parent board
	$board_parent_id = get_user_meta($id, '_Board Parent ID', true);
	$child_boards = get_term_children($board_parent_id, 'board');
	array_push($child_boards, $board_parent_id);

	//remove likes from postmeta
	$usermeta_likes_post_ids = get_user_meta($id, '_Likes Post ID');

	if (!empty($usermeta_likes_post_ids[0])) {
		foreach ($usermeta_likes_post_ids[0] as $likes_post_id) {
			$postmeta_likes_count = get_post_meta($likes_post_id, '_Likes Count', true);
			$postmeta_likes_user_id = get_post_meta($likes_post_id, '_Likes User ID');
			$likes_user_id = $postmeta_likes_user_id[0];
	
			unset($likes_user_id[array_search($id, $likes_user_id)]);
			$likes_user_id = array_values($likes_user_id);
	
			update_post_meta($likes_post_id, '_Likes User ID', $likes_user_id);
			update_post_meta($likes_post_id, '_Likes Count', --$postmeta_likes_count);
		}
	}

	//remove instances from followers
	$followers = get_user_meta($id, '_Followers User ID');
	
	if(!empty($followers[0])) {
		foreach ($followers[0] as $follower) {
			$usermeta_following_count = get_user_meta($follower, '_Following Count', true);
			$usermeta_following_user_id = get_user_meta($follower, '_Following User ID');
			$following_user_id = $usermeta_following_user_id[0];

			unset($following_user_id[array_search($id, $following_user_id)]);
			$following_user_id = array_values($following_user_id);

			update_user_meta($follower, '_Following User ID', $following_user_id);
			update_user_meta($follower, '_Following Count', --$usermeta_following_count);

			//delete board from followers usermeta
			foreach ($child_boards as $child_board) {
				$usermeta_following_board_id = get_user_meta($follower, '_Following Board ID');
				$following_board_id = $usermeta_following_board_id[0];
				
				unset($following_board_id[array_search($child_board, $following_board_id)]);
				$following_board_id = array_values($following_board_id);
				update_user_meta($follower, '_Following Board ID', $following_board_id);	
			}
		}
	}
	
	//remove instances from following users
	$following = get_user_meta($id, '_Following User ID');
	
	if(!empty($following[0])) {
		foreach ($following[0] as $following) {
			$usermeta_followers_count = get_user_meta($following, '_Followers Count', true);
			$usermeta_followers_user_id = get_user_meta($following, '_Followers User ID');
			$followers_user_id = $usermeta_followers_user_id[0];
			$usermeta_followers_user_id_all_boards = get_user_meta($following, '_Followers User ID All Boards');
			$followers_user_id_all_boards = $usermeta_followers_user_id_all_boards[0];

			unset($followers_user_id[array_search($id, $followers_user_id)]);
			$followers_user_id = array_values($followers_user_id);
			
			unset($followers_user_id_all_boards[array_search($id, $followers_user_id_all_boards)]);
			$followers_user_id_all_boards = array_values($followers_user_id_all_boards);

			update_user_meta($following, '_Followers User ID', $followers_user_id);
			update_user_meta($following, '_Followers Count', --$usermeta_followers_count);
			update_user_meta($following, '_Followers User ID All Boards', $followers_user_id_all_boards);
		}
	}

	//delete notifications
	$wpdb->query(
		$wpdb->prepare(
			"DELETE FROM " . $wpdb->prefix . "ipin_notifications
			WHERE user_id = %d
			OR notification_from = %d
			"
			, $id, $id
		)
	);
	
	//delete avatar
	$user_avatar = get_user_meta($id, 'ipin_user_avatar', true);
	
	if ($user_avatar != '' && $user_avatar != 'deleted') {
		$upload_dir = wp_upload_dir();
		$avatar48_img = wp_get_attachment_image_src($user_avatar, 'avatar48');
		$avatar48_img_path = str_replace($upload_dir['baseurl'], $upload_dir['basedir'], $avatar48_img[0]);
	
		if (file_exists($avatar48_img_path))
			unlink($avatar48_img_path);
		
		wp_delete_attachment($user_avatar, true);
	}
	
	//delete profile cover
	$user_cover = get_user_meta($id, 'ipin_user_cover', true);
	
	if ($user_cover != '') {		
		wp_delete_attachment($user_cover, true);
	}
}
add_action('delete_user', 'ipin_delete_user');


function ipin_deleted_user($id, $reassign) {
	$board_parent = get_term_by('slug', $id, 'board', 'ARRAY_A');
	
	if (isset($board_parent) && $board_parent['parent'] == '0') {
		$child_boards = get_term_children($board_parent['term_id'], 'board');
		
		if ($reassign === null) { //delete the boards
			array_push($child_boards, $board_parent['term_id']); //also delete the parent board
	
			foreach ($child_boards as $child_board) {
				wp_delete_term($child_board, 'board');
			}
		} else { //assign the boards
			$board_parent_reassign_user = get_term_by('slug', $reassign, 'board', 'ARRAY_A');
			foreach ($child_boards as $child_board) {
				$child_board_info = get_term($child_board, 'board', 'ARRAY_A');
				$slug = wp_unique_term_slug($child_board_info['name'] . '-@user' . $id . '__ipinboard', 'board'); //append __ipinboard to solve slug conflict with category and 0 in title
				wp_update_term(
					$child_board,
					'board',
					array(
						'name' => $child_board_info['name'] . ' @user' . $id,
						'slug' => $slug,
						'parent' =>$board_parent_reassign_user['term_id']
					)
				);
			}
			
			wp_delete_term($board_parent['term_id'], 'board'); //delete user parent board
		}
	}
}
add_action('deleted_user', 'ipin_deleted_user', 10, 2);


//Don't delete attachments and posts when deleting users, manual delete by setting status to prune
function ipin_post_types_to_delete_with_user($post_types_to_delete) {
	unset($post_types_to_delete[array_search('attachment', $post_types_to_delete)]);
	return $post_types_to_delete;
}
add_filter('post_types_to_delete_with_user', 'ipin_post_types_to_delete_with_user');


//Prune posts
function ipin_cron_schedules($schedules) {
	$prune_duration = of_get_option('prune_duration') * 60;
	
    $schedules['ipin_prune'] = array(
        'interval' => $prune_duration,
        'display'  => 'Prune Duration'
    );
 
    return $schedules;
}
add_filter('cron_schedules', 'ipin_cron_schedules');


if (!wp_next_scheduled( 'ipin_cron_action')) {
    wp_schedule_event(time(), 'ipin_prune', 'ipin_cron_action');
}
 
function ipin_cron_function() {
	global $wpdb;
	
	$prune_postnumber = of_get_option('prune_postnumber');
	
	$posts = $wpdb->get_results(
		$wpdb->prepare(
			"SELECT ID FROM $wpdb->posts
			WHERE post_status = 'ipin_prune'
			LIMIT %d
			"
			, $prune_postnumber
		)
	);
	
	if ($posts) {
		foreach ($posts as $post) {
			$thumbnail_id = get_post_meta($post->ID, '_thumbnail_id', true);
			
			wp_delete_post($post->ID, true);

			//look for other posts using the same featured image e.g thru repin
			$post_same_thumbnail = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT post_id FROM $wpdb->postmeta
					WHERE meta_key = '_thumbnail_id'
					AND meta_value = %d
					LIMIT 1
					"
					, $thumbnail_id
				)
			);

			if ($post_same_thumbnail) {
				$wpdb->query(
					$wpdb->prepare(
						"UPDATE $wpdb->posts
						SET post_parent = %d
						WHERE ID = %d
						"
						, $post_same_thumbnail, $thumbnail_id
					)
				);
			} else {
				wp_delete_attachment($thumbnail_id, true);
			}
		}
	}
	
	//delete notifications older than 30 days
	$wpdb->query(
		"
		DELETE FROM " . $wpdb->prefix . "ipin_notifications
		WHERE notification_date < (NOW() - INTERVAL 30 DAY)
		ORDER BY notification_id ASC
		LIMIT 15
		"
	);
	
	//delete unattached images
	$orphaned_attachment = $wpdb->get_var(
		"SELECT $wpdb->posts.ID
		FROM $wpdb->posts, $wpdb->postmeta
		WHERE $wpdb->posts.post_date < '" . date('Y-m-d', strtotime('-1 day')) . "'
		AND $wpdb->posts.ID = $wpdb->postmeta.post_id
		AND $wpdb->postmeta.meta_key = 'ipin_unattached'
		ORDER BY $wpdb->posts.ID ASC
		LIMIT 1"
	);
	
	if ($orphaned_attachment) {
		wp_delete_attachment($orphaned_attachment, true);
	}
}
add_action('ipin_cron_action', 'ipin_cron_function');


//Format slug for tags when name is same as board
function ipin_created_term($term_id, $tt_id, $taxonomy) {
	if ($taxonomy == 'post_tag') {
		$term = get_term($term_id, $taxonomy);
		if (strpos($term->slug, '__ipinboard') !== false){
			$slug = str_replace('__ipinboard', '', $term->slug);
			wp_update_term($term_id, $taxonomy, array('slug' => $slug));
		}
	}
}
add_action('created_term', 'ipin_created_term', 10, 3);


//Change default email
function ipin_mail_from($email) {
	if ('' != $outgoing_email = of_get_option('outgoing_email')) {
		return $outgoing_email;
	} else {
		return $email;
	}
}
add_filter('wp_mail_from', 'ipin_mail_from');

function ipin_mail_from_name($name) {
	if ('' != $outgoing_email_name = of_get_option('outgoing_email_name')) {
		return $outgoing_email_name;
	} else {
		return $name;
	}
}
add_filter('wp_mail_from_name', 'ipin_mail_from_name');


//Local avatar
function ipin_get_avatar($avatar, $id_or_email, $size, $default, $alt) {
	if (!is_numeric($id_or_email)) {
		if (is_string($id_or_email)) {
			$user = get_user_by('email', $id_or_email);
			$id_or_email = $user->ID;
		} else if (is_object($id_or_email)) {
			if (!empty($id_or_email->ID)) {
				$id_or_email = $id_or_email->ID;
			}
				
			if (!empty( $id_or_email->comment_author_email)) {
				$user = get_user_by('email', $id_or_email->comment_author_email);
				$id_or_email = $user->ID;
			}
		}
	}

	$avatar_id = get_user_meta($id_or_email, 'ipin_user_avatar', true);

	if ($avatar_id != '' && $avatar_id != 'deleted') {
		if (intval($size) <= 48) {
			$imgsrc = wp_get_attachment_image_src($avatar_id, 'avatar48');
			return '<img alt="avatar" src="' . $imgsrc[0] . '" class="avatar" height="' . $size . '" width="' . $size . '" />';
		} else {
			$imgsrc = wp_get_attachment_image_src($avatar_id, 'thumbnail');
			return '<img alt="avatar" src="' . $imgsrc[0] . '" class="avatar" height="' . $size . '" width="' . $size . '" />';
		}
	} else {
		if (of_get_option('default_avatar') == '') { 
			if ($size <= 64) {
				$default = get_template_directory_uri() . '/img/avatar-48x48.png';
			} else {
				$default = get_template_directory_uri() . '/img/avatar-96x96.png';
			}
		} else {			
			if ($size <= 64) {
				$default = get_option('ipin_avatar_48');
			} else {
				$default = get_option('ipin_avatar_96');
			}
		}
		$avatar = '<img alt="avatar" src="' . $default . '" class="avatar" height="' . $size . '" width="' . $size . '" />';
	}

	return $avatar;
}
add_filter('get_avatar', 'ipin_get_avatar', 10, 5);


//Ajax upload avatar
function ipin_upload_avatar(){
    check_ajax_referer('upload_avatar', 'ajax-nonce');

	if ($_FILES) {
		require_once(ABSPATH . 'wp-admin/includes/image.php');
		require_once(ABSPATH . 'wp-admin/includes/file.php');
		require_once(ABSPATH . 'wp-admin/includes/media.php');

		foreach ($_FILES as $file => $array) {
			$imageTypes = array (
				1, //IMAGETYPE_GIF
				2, //IMAGETYPE_JPEG
				3 //IMAGETYPE_PNG
			);

			$imageinfo = getimagesize($_FILES[$file]['tmp_name']);
			$width = @$imageinfo[0];
			$height = @$imageinfo[1];
			$type = @$imageinfo[2];
			$mime = @$imageinfo['mime'];

			if (!in_array($type, $imageTypes)) {
				@unlink($_FILES[$file]['tmp_name']);
				echo 'error';
				die();
			}

			if ($width <= 1 && $height <= 1) {
				@unlink($_FILES[$file]['tmp_name']);
				echo 'error';
				die();
			}

			if($mime != 'image/gif' && $mime != 'image/jpeg' && $mime != 'image/png') {
				@unlink($_FILES[$file]['tmp_name']);
				echo 'error';
				die();
			}

			$filename = time() . substr(str_shuffle("genki02468"), 0, 5);
			
			switch($type) {
				case 1:
					$ext = '.gif';											
					break;
				case 2:
					$ext = '.jpg';
					break;
				case 3:
					$ext = '.png';
					break;
			}
			$_FILES[$file]['name'] = 'avatar-' . $filename . $ext;
			
			add_image_size('avatar48', 48, 48, true);
			$attach_id = media_handle_upload($file, 'none', array('post_title' => 'Avatar for UserID ' . $_POST['avatar-userid']));	

			if (is_wp_error($attach_id)) {
				@unlink($_FILES[$file]['tmp_name']);
				echo 'error';
				die();
			} else {
				$user_avatar = get_user_meta($_POST['avatar-userid'], 'ipin_user_avatar', true);
				if ($user_avatar != '' && $user_avatar != 'deleted')
					wp_delete_attachment($user_avatar, true);

				update_user_meta($_POST['avatar-userid'], 'ipin_user_avatar', $attach_id);

				//attach the avatar to the user settings page so that it's not orphaned in the media library
				$settings_page = get_page_by_path('settings');
			
				global $wpdb;
				$wpdb->query(
					$wpdb->prepare(
						"UPDATE $wpdb->posts 
						SET post_parent = %d
						WHERE ID = %d
						"
						, $settings_page->ID, $attach_id
					)
				);
			}
		}
	}
	
	$return = array();

	$thumbnail = wp_get_attachment_image_src($attach_id, 'thumbnail');
	$return['thumbnail'] = $thumbnail[0];
	$return['id'] = $attach_id;
	echo json_encode($return);
		
	exit;
}
add_action('wp_ajax_ipin-upload-avatar', 'ipin_upload_avatar');


//Delete avatar
function ipin_delete_avatar() {
	$nonce = $_POST['nonce'];

	if (!wp_verify_nonce($nonce, 'ajax-nonce'))
		die();

	$user_avatar = get_user_meta($_POST['id'], 'ipin_user_avatar', true);
	
	if ($user_avatar != '' && $user_avatar != 'deleted') {
		$upload_dir = wp_upload_dir();
		$avatar48_img = wp_get_attachment_image_src($user_avatar, 'avatar48');
		$avatar48_img_path = str_replace($upload_dir['baseurl'], $upload_dir['basedir'], $avatar48_img[0]);
	
		if (file_exists($avatar48_img_path))
			unlink($avatar48_img_path);
		
		wp_delete_attachment($user_avatar, true);
		update_user_meta($_POST['id'], 'ipin_user_avatar', 'deleted');
	}
	exit;
}
add_action('wp_ajax_ipin-delete-avatar', 'ipin_delete_avatar');


//Ajax upload cover
function ipin_upload_cover(){
    check_ajax_referer('upload_cover', 'ajax-nonce');

	if ($_FILES) {
		foreach ($_FILES as $file => $array) {				
			require_once(ABSPATH . 'wp-admin/includes/image.php');
			require_once(ABSPATH . 'wp-admin/includes/file.php');
			require_once(ABSPATH . 'wp-admin/includes/media.php');

			$imageTypes = array (
				1, //IMAGETYPE_GIF
				2, //IMAGETYPE_JPEG
				3 //IMAGETYPE_PNG
			);

			$imageinfo = getimagesize($_FILES[$file]['tmp_name']);
			$width = @$imageinfo[0];
			$height = @$imageinfo[1];
			$type = @$imageinfo[2];
			$mime = @$imageinfo['mime'];

			if (!in_array($type, $imageTypes)) {
				@unlink($_FILES[$file]['tmp_name']);
				echo 'error';
				die();
			}

			if ($width <= 1 && $height <= 1) {
				@unlink($_FILES[$file]['tmp_name']);
				echo 'error';
				die();
			}

			if($mime != 'image/gif' && $mime != 'image/jpeg' && $mime != 'image/png') {
				@unlink($_FILES[$file]['tmp_name']);
				echo 'error';
				die();
			}

			$filename = time() . substr(str_shuffle("genki02468"), 0, 5);
			
			switch($type) {
				case 1:
					$ext = '.gif';											
					break;
				case 2:
					$ext = '.jpg';
					break;
				case 3:
					$ext = '.png';
					break;
			}
			$_FILES[$file]['name'] = 'cover-' . $filename . $ext;
			
			$attach_id = media_handle_upload($file, 'none', array('post_title' => 'Cover for UserID ' . $_POST['cover-userid']));	

			if (is_wp_error($attach_id)) {
				@unlink($_FILES[$file]['tmp_name']);
				echo 'error';
				die();
			} else {
				$user_cover = get_user_meta($_POST['cover-userid'], 'ipin_user_cover', true);
				if ($user_cover != '')
					wp_delete_attachment($user_cover, true);

				update_user_meta($_POST['cover-userid'], 'ipin_user_cover', $attach_id);

				//attach the cover to the user settings page so that it's not orphaned in the media library
				$settings_page = get_page_by_path('settings');
			
				global $wpdb;
				$wpdb->query(
					$wpdb->prepare(
						"UPDATE $wpdb->posts 
						SET post_parent = %d
						WHERE ID = %d
						"
						, $settings_page->ID, $attach_id
					)
				);
			}
		}
	}
	
	$return = array();

	$thumbnail = wp_get_attachment_image_src($attach_id, 'thumbnail');
	$return['thumbnail'] = $thumbnail[0];
	$return['id'] = $attach_id;
	echo json_encode($return);
		
	exit;
}
add_action('wp_ajax_ipin-upload-cover', 'ipin_upload_cover');


//Delete cover
function ipin_delete_cover() {
	$nonce = $_POST['nonce'];

	if (!wp_verify_nonce($nonce, 'ajax-nonce'))
		die();

	$user_cover = get_user_meta($_POST['id'], 'ipin_user_cover', true);
	
	wp_delete_attachment($user_cover, true);
	update_user_meta($_POST['id'], 'ipin_user_cover', '');
	exit;
}
add_action('wp_ajax_ipin-delete-cover', 'ipin_delete_cover');


//**User Control Panel**//

//Add Board/Edit Board
function ipin_add_board() {
	$nonce = $_POST['nonce'];

	if (!wp_verify_nonce($nonce, 'ajax-nonce'))
		die();
		
	do_action('ipin_before_add_board', $_POST);

	global $wpdb, $user_ID, $wp_taxonomies;
	$mode = $_POST['mode'];
	$term_id = $_POST['term_id'];
	$board_parent_id = get_user_meta($user_ID, '_Board Parent ID', true);
	$board_title  = sanitize_text_field($_POST['board_title']);
	$category_id  = $_POST['category_id'];
	
	if ($category_id == '-1')
		$category_id = '1';

	if ($mode == 'add') {
		$board_children = get_term_children($board_parent_id, 'board');
		$found = '0';

		foreach ($board_children as $board_child) {
			$board_child_term = get_term_by('id', $board_child, 'board');
			if (stripslashes(htmlspecialchars($board_title, ENT_NOQUOTES, 'UTF-8')) == $board_child_term->name) {
				$found = '1';
				break;
			}
		}
		
		if ($found == '0') {
			$slug = wp_unique_term_slug($board_title . '__ipinboard', 'board'); //append __ipinboard to solve slug conflict with category and 0 in title
			
			$new_board_id = wp_insert_term (
				$board_title,
				'board',
				array(
					'description' => $category_id,
					'parent' => $board_parent_id,
					'slug' => $slug
				)
			);
			echo home_url('/' . $wp_taxonomies["board"]->rewrite['slug'] . '/' . sanitize_title($board_title, '_') . '/' . $new_board_id['term_id'] . '/');
		} else {
			echo 'error';
		}

		//add new board to followers who fully follow user
		$usermeta_followers_id_allboards = get_user_meta($user_ID, '_Followers User ID All Boards');
		$followers_id_allboards = $usermeta_followers_id_allboards[0];

		if (!empty($followers_id_allboards)) {
			foreach ($followers_id_allboards as $followers_id_allboard) {
				$usermeta_following_board_id = get_user_meta($followers_id_allboard, '_Following Board ID');
				$following_board_id = $usermeta_following_board_id[0];
				array_unshift($following_board_id, $new_board_id['term_id']);
				update_user_meta($followers_id_allboard, '_Following Board ID', $following_board_id);
			}
		}
		
		do_action('ipin_after_add_board', $new_board_id);
	} else if ($mode == 'edit') {
		$board_info = get_term_by('id', $term_id, 'board', ARRAY_A);
		
		//user_id is name of parent board
		$board_parent_info = get_term_by('id', $board_info['parent'], 'board');
		$user_id = $board_parent_info->name;
		
		if (!(current_user_can('administrator') || current_user_can('editor') || $user_id == $user_ID)) {
			die();
		}
		
		if (stripslashes(htmlspecialchars($board_title, ENT_NOQUOTES, 'UTF-8')) == $board_info['name']) {
			wp_update_term(
				$term_id,
				'board',
				array(
					'description' => $category_id
				)
			);
			echo home_url('/' . $wp_taxonomies["board"]->rewrite['slug'] . '/' . sanitize_title($board_title, '_') . '/' . $term_id . '/');
		} else {
			$board_children = get_term_children($board_info['parent'], 'board');
			$found = '0';

			foreach ($board_children as $board_child) {
				$board_child_term = get_term_by('id', $board_child, 'board');
				if (stripslashes(htmlspecialchars($board_title, ENT_NOQUOTES, 'UTF-8')) == $board_child_term->name) {
					$found = '1';
					break;
				}
			}

			if ($found == '0') {
				$slug = wp_unique_term_slug($board_title . '__ipinboard', 'board'); //append __ipinboard to solve slug conflict with category and 0 in title
				wp_update_term(
					$term_id,
					'board',
					array(
						'name' => $board_title,
						'slug' => $slug,
						'description' => $category_id
					)
				);
				echo home_url('/' . $wp_taxonomies["board"]->rewrite['slug'] . '/' . sanitize_title($board_title, '_') . '/' . $term_id . '/');
			} else {
				echo 'error';				
			}
		}

		//change the category of all posts in this board only if category is changed in the form
		$original_board_cat_id = get_term_by('id', $board_info['term_id'], 'board');
		if ($category_id != $original_board_cat_id) {		
			$posts = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT object_id FROM $wpdb->term_relationships
					WHERE term_taxonomy_id = %d
					"
					, $board_info['term_taxonomy_id']
				)
			);
			
			if ($posts) {
				foreach ($posts as $post) {
					wp_set_object_terms($post->object_id, array(intval($category_id)), 'category');
				}
			}
		}
		do_action('ipin_after_edit_board', $term_id);
	}
	
	exit;
}
add_action('wp_ajax_ipin-add-board', 'ipin_add_board');


//Delete board
function ipin_delete_board() {
	$nonce = $_POST['nonce'];

	if (!wp_verify_nonce($nonce, 'ajax-nonce'))
		die();
		
	do_action('ipin_before_delete_board', $_POST);

	global $wpdb, $user_ID;

	$board_id = $_POST['board_id'];
	$board_info = get_term_by('id', $board_id, 'board');

	//user_id is name of parent board
	$board_parent_info = get_term_by('id', $board_info->parent, 'board');
	$user_id = $board_parent_info->name;
	
	if (!(current_user_can('administrator') || current_user_can('editor') || $user_id == $user_ID)) {
		die();
	}

	//get all posts in this board
	$posts = $wpdb->get_results(
		$wpdb->prepare(
			"SELECT object_id FROM $wpdb->term_relationships
			WHERE term_taxonomy_id = %d
			"
			, $board_info->term_taxonomy_id
		)
	);

	if ($posts) {
		$post_ids = array();

		foreach ($posts as $post) {
			array_push($post_ids, $post->object_id);
		}

		$post_ids = implode(',', $post_ids);

		//set status to prune
		$wpdb->query("UPDATE $wpdb->posts
					SET post_status = 'ipin_prune'
					WHERE ID IN ($post_ids)
		");
	}

	//delete board from followers usermeta
	$followers = get_user_meta($user_id, '_Followers User ID');

	if(!empty($followers[0])) {
		foreach ($followers[0] as $follower) {
			$usermeta_following_board_id = get_user_meta($follower, '_Following Board ID');
			$following_board_id = $usermeta_following_board_id[0];

			unset($following_board_id[array_search($board_info->term_id, $following_board_id)]);
			$following_board_id = array_values($following_board_id);
			update_user_meta($follower, '_Following Board ID', $following_board_id);
		}
	}

	wp_delete_term($board_info->term_id, 'board');
	
	do_action('ipin_after_delete_board', $board_info->term_id);

	echo get_author_posts_url($user_id);
	exit;
}
add_action('wp_ajax_ipin-delete-board', 'ipin_delete_board');


//Add pin
function ipin_upload_pin(){
    check_ajax_referer('upload_pin', 'ajax-nonce');
	
	do_action('ipin_before_upload_pin', $_POST);
	
	$minWidth = 2;
	$minHeight = 2;
	
	$minWidth = apply_filters('ipin_minwidth', $minWidth);
	$minHeight = apply_filters('ipin_minheight', $minHeight);

	require_once(ABSPATH . 'wp-admin/includes/image.php');
	require_once(ABSPATH . 'wp-admin/includes/file.php');
	require_once(ABSPATH . 'wp-admin/includes/media.php');

	if ($_POST['mode'] == 'computer') {
		if ($_FILES) {
			foreach ($_FILES as $file => $array) {			
				$imageTypes = array (
					1, //IMAGETYPE_GIF
					2, //IMAGETYPE_JPEG
					3 //IMAGETYPE_PNG
				);

				$imageinfo = getimagesize($_FILES[$file]['tmp_name']);
				$width = @$imageinfo[0];
				$height = @$imageinfo[1];
				$type = @$imageinfo[2];
				$mime = @$imageinfo['mime'];

				if (!in_array($type, $imageTypes)) {
					@unlink($_FILES[$file]['tmp_name']);
					echo 'error';
					die();
				}

				if ($width < $minWidth || $height < $minHeight) {
					@unlink($_FILES[$file]['tmp_name']);
					echo 'errorsize';
					die();
				}

				if($mime != 'image/gif' && $mime != 'image/jpeg' && $mime != 'image/png') {
					@unlink($_FILES[$file]['tmp_name']);
					echo 'error';
					die();
				}
				
				switch($type) {
					case 1:
						$ext = '.gif';
						
						//check if is animated gif
						$frames = 0;
						if(($fh = @fopen($_FILES[$file]['tmp_name'], 'rb')) && $error != 'error') {
							while(!feof($fh) && $frames < 2) {
								$chunk = fread($fh, 1024 * 100); //read 100kb at a time
								$frames += preg_match_all('#\x00\x21\xF9\x04.{4}\x00(\x2C|\x21)#s', $chunk, $matches);
						   }
						}
						fclose($fh);
												
						break;
					case 2:
						$ext = '.jpg';
						break;
					case 3:
						$ext = '.png';
						break;
				}
				$filename = time() . str_shuffle('gnk48');
				$original_filename = preg_replace('/[^(\x20|\x61-\x7A)]*/', '', strtolower(str_ireplace($ext, '', $_FILES[$file]['name']))); //preg_replace('/[^(\x48-\x7A)]*/' strips non-utf character. Ref: http://www.ssec.wisc.edu/~tomw/java/unicode.html#x0000
                $_FILES[$file]['name'] = strtolower(substr($original_filename, 0, 100)) . '-' . $filename . $ext;

				$attach_id = media_handle_upload($file, $post_id);

				if (is_wp_error($attach_id)) {
					@unlink($_FILES[$file]['tmp_name']);
					echo 'error';
					die();
				} else {
					if ($frames > 1) {
						update_post_meta($attach_id, 'a_gif', 'yes');
					}
				}
			}   
		}
		
		update_post_meta($attach_id, 'ipin_unattached', 'yes');
		
		$return = array();

		$thumbnail = wp_get_attachment_image_src($attach_id, 'medium');
		$return['thumbnail'] = $thumbnail[0];
		$return['id'] = $attach_id;
	
		do_action('ipin_after_upload_pin_computer', $attach_id);
		echo json_encode($return);
	} else if ($_POST['mode'] == 'web') {
		$url = esc_url_raw($_POST['pin_upload_web']);
		
		if (function_exists('curl_init')) {
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
			$image = curl_exec($ch);
			curl_close($ch);
		} elseif (ini_get('allow_url_fopen')) {
			$image = file_get_contents($url, false, $context);
		}

		if (!$image) {
			echo 'error';
			die();
		}

		$filename = time() . str_shuffle('gnk48');
		$file_array['tmp_name'] = WP_CONTENT_DIR . "/" . $filename . '.tmp';
		$filetmp = file_put_contents($file_array['tmp_name'], $image);
		
		if (!$filetmp) {
			@unlink($file_array['tmp_name']);
			echo 'error';
			die();
		}

		$imageTypes = array (
			1, //IMAGETYPE_GIF
			2, //IMAGETYPE_JPEG
			3 //IMAGETYPE_PNG
		);

		$imageinfo = getimagesize($file_array['tmp_name']);
		$width = @$imageinfo[0];
		$height = @$imageinfo[1];
		$type = @$imageinfo[2];
		$mime = @$imageinfo['mime'];

		if (!in_array ($type, $imageTypes)) {
			@unlink($file_array['tmp_name']);
			echo 'error';
			die();
		}

		if ($width < $minWidth || $height < $minHeight) {
			@unlink($file_array['tmp_name']);
			echo 'errorsize';
			die();
		}

		if($mime != 'image/gif' && $mime != 'image/jpeg' && $mime != 'image/png') {
			@unlink($file_array['tmp_name']);
			echo 'error';
			die();
		}
		
		switch($type) {
			case 1:
				$ext = '.gif';
				
				//check if is animated gif
				$frame = 0;
				if(($fh = @fopen($file_array['tmp_name'], 'rb')) && $error != 'error') {
					while(!feof($fh) && $frames < 2) {
						$chunk = fread($fh, 1024 * 100); //read 100kb at a time
						$frames += preg_match_all('#\x00\x21\xF9\x04.{4}\x00(\x2C|\x21)#s', $chunk, $matches);
				   }
				}
				fclose($fh);
						
				break;
			case 2:
				$ext = '.jpg';
				break;
			case 3:
				$ext = '.png';
				break;
		}
        $original_filename = preg_replace('/[^(\x20|\x61-\x7A)]*/', '', strtolower(str_ireplace($ext, '', basename($url)))); //preg_replace('/[^(\x48-\x7A)]*/' strips non-utf character. Ref: http://www.ssec.wisc.edu/~tomw/java/unicode.html#x0000
        $file_array['name'] = strtolower(substr($original_filename, 0, 100)) . '-' . $filename . $ext;

		$attach_id = media_handle_sideload($file_array, $post_id);
		
		if (is_wp_error($attach_id)) {
			@unlink($file_array['tmp_name']);
			echo 'error';
			die();
		} else {
			if ($frames > 1) {
				update_post_meta($attach_id, 'a_gif', 'yes');
			}
		}
		
		update_post_meta($attach_id, 'ipin_unattached', 'yes');

		$return = array();
		$thumbnail = wp_get_attachment_image_src($attach_id, 'medium');
		$return['thumbnail'] = $thumbnail[0];
		$return['id'] = $attach_id;
		
		do_action('ipin_after_upload_pin_web', $attach_id);
		echo json_encode($return);
	}
	exit;
}
add_action('wp_ajax_ipin-upload-pin', 'ipin_upload_pin');

//Remove %20 from filenames
function ipin_sanitize_file_name($filename, $filename_raw) {
	$filename = str_replace('%20', '-', $filename);
	return $filename;
}
add_filter('sanitize_file_name', 'ipin_sanitize_file_name', 1, 2);

//Add pin as a wp post
function ipin_postdata() {
	$nonce = $_POST['nonce'];

	if (!wp_verify_nonce($nonce, 'ajax-nonce'))
		die();

	do_action('ipin_before_add_pin', $_POST);

	global $user_ID;

	//get board info
	$board_add_new = sanitize_text_field($_POST['postdata_board_add_new']);
	$board_add_new_category = $_POST['postdata_board_add_new_category'];
	$board_parent_id = get_user_meta($user_ID, '_Board Parent ID', true);
	$board_theme = wp_get_theme();
	if ($board_add_new !== '') {
		$board_children = get_term_children($board_parent_id, 'board');
		$found = '0';

		foreach ($board_children as $board_child) {
			$board_child_term = get_term_by('id', $board_child, 'board');
			if (stripslashes(htmlspecialchars($board_add_new, ENT_NOQUOTES, 'UTF-8')) == $board_child_term->name) {
				$found = '1';
				$found_board_id = $board_child_term->term_id;
				break;
			}
		}

		if ($found == '0') {
			$slug = wp_unique_term_slug($board_add_new . '__ipinboard', 'board'); //append __ipinboard to solve slug conflict with category and 0 in title
			if ($board_add_new_category == '-1')
				$board_add_new_category = '1';

			$new_board_id = wp_insert_term (
				$board_add_new,
				'board',
				array(
					'description' => $board_add_new_category,
					'parent' => $board_parent_id,
					'slug' => $slug
				)
			);
			
			$postdata_board = $new_board_id['term_id'];
		} else {
			$postdata_board = $found_board_id;
		}
	} else {
		$postdata_board = $_POST['postdata_board'];
	}

	//category ID is stored in the board description field
	$category_id = get_term_by('id', $postdata_board, 'board');

	$post_status = 'publish';
	
	if (!current_user_can('publish_posts')) {
		$post_status = 'pending';
	}
	
	$allowed_html = array(
		'a' => array(
			'href' => true
		),
		'em' => array(),
		'blockquote' => array(),
		'p' => array(),
		'li' => array(),
		'ol' => array(),
		'strong' => array(),
		'ul' => array(),
	);
	
	if (of_get_option('htmltags') != 'enable') {
		unset($allowed_html);
		$allowed_html = array();
	}
	
	if (of_get_option('form_title_desc') != 'separate') {
		$post_title = balanceTags(wp_kses($_POST['postdata_title'], $allowed_html), true);
	} else {
		$post_title = sanitize_text_field($_POST['postdata_title']);
	}
	
	$post_content = balanceTags(wp_kses($_POST['postdata_content'], $allowed_html), true);

	$post_array = array(
	  'post_title'    => $post_title,
	  'post_content'    => $post_content,
	  'post_status'   => $post_status,
	  'post_category' => array($category_id->description)
	);
	
	remove_action('save_post', 'ipin_save_post', 50, 2);
	$post_id = wp_insert_post($post_array);
		
	wp_set_post_terms($post_id, array($postdata_board), 'board');
	update_user_meta($user_ID, 'ipin_last_board', $postdata_board);

	//update postmeta for new post
	if ($_POST['postdata_photo_source'] != '') {
		add_post_meta($post_id, '_Photo Source', esc_url($_POST['postdata_photo_source']));
		add_post_meta($post_id, '_Photo Source Domain', parse_url(esc_url($_POST['postdata_photo_source']), PHP_URL_HOST));	
	}
	
	//add tags
	if ($_POST['postdata_tags']) {	
		wp_set_post_tags($post_id, sanitize_text_field($_POST['postdata_tags']));
	}
	
	//add price
	if ($_POST['postdata_price']) {
		if (strpos($_POST['postdata_price'], '.') !== false) {
			$_POST['postdata_price'] = number_format($_POST['postdata_price'], 2);
		}
		
		add_post_meta($post_id, '_Price', sanitize_text_field($_POST['postdata_price']));
	}
	
	//add background color
	if ($_POST['postdata_bgcolor']) {
		add_post_meta($post_id, '_Bg Color', sanitize_text_field($_POST['postdata_bgcolor']));
	}

	$attachment_id = $_POST['postdata_attachment_id'];
	delete_post_meta($attachment_id, 'ipin_unattached');
	add_post_meta($post_id, '_thumbnail_id', $attachment_id);

	global $wpdb;
	$wpdb->query(
		$wpdb->prepare(
			"UPDATE $wpdb->posts 
			SET post_parent = %d
			WHERE ID = %d
			"
			, $post_id, $attachment_id
		)
	);

	//add new board to followers who fully follow user
	if ($new_board_id && !is_wp_error($new_board_id)) {
		$usermeta_followers_id_allboards = get_user_meta($user_ID, '_Followers User ID All Boards');
		$followers_id_allboards = $usermeta_followers_id_allboards[0];
		
		if (!empty($followers_id_allboards)) {
			foreach ($followers_id_allboards as $followers_id_allboard) {
				$usermeta_following_board_id = get_user_meta($followers_id_allboard, '_Following Board ID');
				$following_board_id = $usermeta_following_board_id[0];
				array_unshift($following_board_id, $new_board_id['term_id']);
				update_user_meta($followers_id_allboard, '_Following Board ID', $following_board_id);
			}
		}
	}
	
	do_action('ipin_after_add_pin', $post_id);

	echo get_permalink($post_id);
	exit;
}
add_action('wp_ajax_ipin-postdata', 'ipin_postdata');


//Edit pin
function ipin_edit() {
	$nonce = $_POST['nonce'];

	if (!wp_verify_nonce($nonce, 'ajax-nonce'))
		die();
		
	do_action('ipin_before_edit_pin', $_POST);

	global $user_ID;
	$postinfo = get_post(intval($_POST['postdata_pid']), ARRAY_A);
	$user_id = $postinfo['post_author'];
	
	if (!(current_user_can('administrator') || current_user_can('editor') || $user_id == $user_ID)) {
		die();
	}
		
	//Get board info
	$board_add_new = sanitize_text_field($_POST['postdata_board_add_new']);
	$board_add_new_category = $_POST['postdata_board_add_new_category'];
	$board_parent_id = get_user_meta($user_id, '_Board Parent ID', true);
	if ($board_add_new !== '') {
		$board_children = get_term_children($board_parent_id, 'board');
		$found = '0';

		foreach ($board_children as $board_child) {
			$board_child_term = get_term_by('id', $board_child, 'board');
			if (stripslashes(htmlspecialchars($board_add_new, ENT_NOQUOTES, 'UTF-8')) == $board_child_term->name) {
				$found = '1';
				$found_board_id = $board_child_term->term_id;
				break;
			}
		}

		if ($found == '0') {
			$slug = wp_unique_term_slug($board_add_new . '__ipinboard', 'board'); //append __ipinboard to solve slug conflict with category and 0 in title
			if ($board_add_new_category == '-1')
				$board_add_new_category = '1';

			$new_board_id = wp_insert_term (
				$board_add_new,
				'board',
				array(
					'description' => $board_add_new_category,
					'parent' => $board_parent_id,
					'slug' => $slug
				)
			);
			
			$postdata_board = $new_board_id['term_id'];
		} else {
			$postdata_board = $found_board_id;
		}
	} else {
		$postdata_board = $_POST['postdata_board'];		
	}

	//category ID is stored in the board description field
	$category_id = get_term_by( 'id', $postdata_board, 'board');

	$post_id = intval($_POST['postdata_pid']);
	$edit_post = array();
	$edit_post['ID'] = $post_id;
	$edit_post['post_category'] = array($category_id->description);
	$edit_post['post_name'] = '';
	
	$allowed_html = array(
		'a' => array(
			'href' => true
		),
		'em' => array(),
		'blockquote' => array(),
		'p' => array(),
		'li' => array(),
		'ol' => array(),
		'strong' => array(),
		'ul' => array(),
	);
	
	if (of_get_option('htmltags') != 'enable') {
		unset($allowed_html);
		$allowed_html = array();
	}

	if (of_get_option('form_title_desc') != 'separate') {
		$edit_post['post_title'] = balanceTags(wp_kses($_POST['postdata_title'], $allowed_html), true);
	} else {
		$edit_post['post_title'] = sanitize_text_field($_POST['postdata_title']);
	}
	
	$edit_post['post_content'] = balanceTags(wp_kses($_POST['postdata_content'], $allowed_html), true);

	remove_action('save_post', 'ipin_save_post', 50, 2);
	wp_update_post($edit_post);
	
	wp_set_post_terms($post_id, array($postdata_board), 'board');
	
	//update postmeta for new post
	if ($_POST['postdata_source'] != '') {
		update_post_meta($post_id, '_Photo Source', esc_url($_POST['postdata_source']));
		update_post_meta($post_id, '_Photo Source Domain', parse_url(esc_url($_POST['postdata_source']), PHP_URL_HOST));
	} else {
		delete_post_meta($post_id, '_Photo Source');
		delete_post_meta($post_id, '_Photo Source Domain');
	}
	
	//add tags
	wp_set_post_tags($post_id, sanitize_text_field($_POST['postdata_tags']));
	
	//add price
	if ($_POST['postdata_price']) {
		if (strpos($_POST['postdata_price'], '.') !== false) {
			$_POST['postdata_price'] = number_format($_POST['postdata_price'], 2);
		}
		
		update_post_meta($post_id, '_Price', sanitize_text_field($_POST['postdata_price']));
	}
	else {
		if (get_post_meta($post_id, '_Price', true) !== '') {
			delete_post_meta($post_id, '_Price');
		}
	}
	
	//add new board to followers who fully follow user
	if ($new_board_id && !is_wp_error($new_board_id)) {
		$usermeta_followers_id_allboards = get_user_meta($user_id, '_Followers User ID All Boards');
		$followers_id_allboards = $usermeta_followers_id_allboards[0];
		
		if (!empty($followers_id_allboards)) {
			foreach ($followers_id_allboards as $followers_id_allboard) {
				$usermeta_following_board_id = get_user_meta($followers_id_allboard, '_Following Board ID');
				$following_board_id = $usermeta_following_board_id[0];
				array_unshift($following_board_id, $new_board_id['term_id']);
				update_user_meta($followers_id_allboard, '_Following Board ID', $following_board_id);
			}
		}
	}
	
	do_action('ipin_after_edit_pin', $post_id);
	
	echo get_permalink($post_id);
	exit;
}
add_action('wp_ajax_ipin-pin-edit', 'ipin_edit');


//Output WP Editor
if (!function_exists('ipin_wp_editor')) :
function ipin_wp_editor($editor_id, $post_content = '') {
	$settings = array(
		'textarea_rows' => 2,
		'media_buttons' => false,
		'quicktags' => false,
		'tinymce' => array(
			'toolbar1' => 'bold, italic, blockquote, bullist, numlist, link, unlink',
			'toolbar2' => '',
			'plugins' => 'wordpress, wplink',
			'content_css' => get_stylesheet_directory_uri() . '/editor-style-frontend.css'
		)
	);

	ob_start();
	wp_editor($post_content, $editor_id, $settings);
	$editor_contents = ob_get_clean();

	$editor_contents .= '<div class="placeholder_description">' . __('Description', 'ipin') . '</div>';

	return $editor_contents;
}
endif;


//Replace image
function ipin_replace_image(){
    check_ajax_referer('replace_image', 'ajax-nonce');
	
	global $user_ID;
	$post_id = intval($_POST['post_id']);
	$minWidth = 2;
	$minHeight = 2;
	
	$minWidth = apply_filters('ipin_minwidth', $minWidth);
	$minHeight = apply_filters('ipin_minheight', $minHeight);
	
	$post_info = get_post($post_id);
	$post_author = $post_info->post_author;
	
	if (!(current_user_can('administrator') || current_user_can('editor') || $post_author == $user_ID)) {
		die();
	}

	if ($_FILES) {
		require_once(ABSPATH . 'wp-admin/includes/image.php');
		require_once(ABSPATH . 'wp-admin/includes/file.php');
		require_once(ABSPATH . 'wp-admin/includes/media.php');
	
		foreach ($_FILES as $file => $array) {
			$imageTypes = array (
				1, //IMAGETYPE_GIF
				2, //IMAGETYPE_JPEG
				3 //IMAGETYPE_PNG
			);

			$imageinfo = getimagesize($_FILES[$file]['tmp_name']);
			$width = @$imageinfo[0];
			$height = @$imageinfo[1];
			$type = @$imageinfo[2];
			$mime = @$imageinfo['mime'];

			if (!in_array($type, $imageTypes)) {
				@unlink($_FILES[$file]['tmp_name']);
				echo 'error';
				die();
			}

			if ($width < $minWidth || $height < $minHeight) {
				@unlink($_FILES[$file]['tmp_name']);
				echo 'error';
				die();
			}

			if($mime != 'image/gif' && $mime != 'image/jpeg' && $mime != 'image/png') {
				@unlink($_FILES[$file]['tmp_name']);
				echo 'error';
				die();
			}
			
			switch($type) {
				case 1:
					$ext = '.gif';
					
					//check if is animated gif
					$frames = 0;
					if(($fh = @fopen($_FILES[$file]['tmp_name'], 'rb')) && $error != 'error') {
						while(!feof($fh) && $frames < 2) {
							$chunk = fread($fh, 1024 * 100); //read 100kb at a time
							$frames += preg_match_all('#\x00\x21\xF9\x04.{4}\x00(\x2C|\x21)#s', $chunk, $matches);
					   }
					}
					fclose($fh);
															
					break;
				case 2:
					$ext = '.jpg';
					break;
				case 3:
					$ext = '.png';
					break;
			}
			
			$filename = time() . str_shuffle('gnk48');
			$original_filename = preg_replace('/[^(\x20|\x61-\x7A)]*/', '', strtolower(str_ireplace($ext, '', $_FILES[$file]['name']))); //preg_replace('/[^(\x48-\x7A)]*/' strips non-utf character. Ref: http://www.ssec.wisc.edu/~tomw/java/unicode.html#x0000
			$_FILES[$file]['name'] = strtolower(substr($original_filename, 0, 100)) . '-' . $filename . $ext;
			
			$post_thumbnail_id = get_post_meta($post_id, '_thumbnail_id', true);
			$attach_id = media_handle_upload($file, $post_id);	

			if (is_wp_error($attach_id)) {
				@unlink($_FILES[$file]['tmp_name']);
				echo 'error';
				die();
			} else {
				if ($frames > 1) {
					update_post_meta($attach_id, 'a_gif', 'yes');
				}
				
				//update repins
				global $wpdb;
				$wpdb->query(
					$wpdb->prepare(
						"UPDATE $wpdb->postmeta
						SET meta_value = %d
						WHERE meta_key = '_thumbnail_id'
						AND meta_value = %d
						"
						, $attach_id, $post_thumbnail_id
					)
				);
				
				update_post_meta($post_id, '_thumbnail_id', $attach_id);
				wp_delete_attachment($post_thumbnail_id, true);
			}
		}
	}

	$thumbnail = wp_get_attachment_image_src($attach_id, 'medium');
	echo $thumbnail[0];
	exit;
}
add_action('wp_ajax_ipin-replace-image', 'ipin_replace_image');


//Delete pin
function ipin_delete_pin() {
	$nonce = $_POST['nonce'];

	if (!wp_verify_nonce($nonce, 'ajax-nonce'))
		die();
		
	global $wpdb, $user_ID;
	$post_id = intval($_POST['pin_id']);
	$post_author = intval($_POST['pin_author']);
	
	//set status to prune
	if (current_user_can('administrator') || current_user_can('editor') || $post_author == $user_ID) {
		$wpdb->query(
			$wpdb->prepare(
				"UPDATE $wpdb->posts
				SET post_status = 'ipin_prune'
				WHERE ID = %d
				"
				, $post_id
			)
		);
	}

	echo get_author_posts_url($post_author) . '?view=pins';
	exit;
}
add_action('wp_ajax_ipin-delete-pin', 'ipin_delete_pin');


//Output categories dropdown
if (!function_exists('ipin_dropdown_categories')) :
function ipin_dropdown_categories($show_option_none, $name, $selected = '') {
	if (of_get_option('blog_cat_id')) {
		return apply_filters('ipin_dropdown_categories', wp_dropdown_categories(array('hierarchical' => true, 'show_option_none' => $show_option_none, 'exclude_tree' => of_get_option('blog_cat_id') . ',1', 'hide_empty' => 0, 'name' => $name, 'orderby' => 'name', 'selected' => $selected, 'echo' => 0, 'class' => 'form-control')));
	} else {
		return apply_filters('ipin_dropdown_categories', wp_dropdown_categories(array('hierarchical' => true, 'show_option_none' => $show_option_none, 'exclude' => '1', 'hide_empty' => 0, 'name' => $name, 'orderby' => 'name', 'selected' => $selected, 'echo' => 0, 'class' => 'form-control')));
	}
}
endif;


//Output boards dropdown
if (!function_exists('ipin_dropdown_boards')) :
function ipin_dropdown_boards($user_id = null, $selected = '') {
	if (!$user_id)
		$user_id = get_current_user_id();

	$board_parent_id = get_user_meta($user_id, '_Board Parent ID', true);
	$board_children_count = wp_count_terms('board', array('parent' => $board_parent_id));
	if (is_array($board_children_count) || $board_children_count == 0) {
		return apply_filters('ipin_dropdown_boards', '<span id="noboard">' . 
		wp_dropdown_categories(array('taxonomy' => 'board', 'parent' => $board_parent_id, 'hide_empty' => 0, 'name' => 'board', 'hierarchical' => true, 'echo' => 0, 'selected' => $selected, 'show_option_none' => __('Add a new board first...', 'ipin'), 'class' => 'form-control' )) . 
		'</span>');
	} else {
		return apply_filters('ipin_dropdown_boards', wp_dropdown_categories(array('taxonomy' => 'board', 'parent' => $board_parent_id, 'hide_empty' => 0, 'name' => 'board', 'hierarchical' => true, 'orderby'=> 'name', 'order' => 'ASC', 'echo' => 0, 'selected' => $selected, 'class' => 'form-control')));
	}
}
endif;


//Email friend
function ipin_post_email() {
	$nonce = $_POST['nonce'];

	if (!wp_verify_nonce($nonce, 'ajax-nonce'))
		die();
	
	global $user_ID, $user_identity, $wp_rewrite, $wp_taxonomies;
	$user_info = get_user_by('id', $user_ID);
	$post_id = $_POST['email_post_id'];
	$board_id = $_POST['email_board_id'];
	$recipient_name = sanitize_text_field($_POST['recipient_name']);
	$recipient_email = sanitize_text_field($_POST['recipient_email']);
	$recipient_message = stripslashes(strip_tags($_POST['recipient_message']));
	$blogname = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);
	
	
	if ($post_id) { //from single-pin.php
		$message = sprintf(__('Hi %s', 'ipin'), $recipient_name) . "\r\n\r\n";
		$message .= sprintf(__('%s wants to share "%s" with you.', 'ipin'), $user_identity, preg_replace('/[\n\r]/', ' ', html_entity_decode(sanitize_text_field(get_the_title($post_id)), ENT_QUOTES, 'UTF-8'))) . "\r\n\r\n";
		if ($recipient_message !='') {
			$message .= sprintf(__('%s said, "%s".', 'ipin'), $user_identity, $recipient_message) . "\r\n\r\n";
		}
		$message .= sprintf(__('View pin at %s', 'ipin'), get_permalink($post_id)) . "\r\n\r\n";
		$message .= sprintf(__('View %s\'s profile at %s', 'ipin'), $user_identity, home_url('/') . $wp_rewrite->author_base. '/' . $user_info->user_nicename . '/') . "\r\n\r\n";

		wp_mail($recipient_email, sprintf(__('%s wants to share a pin with you from %s', 'ipin'), $user_identity, $blogname), $message);
	}
	
	if ($board_id) { //from taxonomy-board.php
		$board_info = get_term_by('id', $board_id, 'board');
		$message = sprintf(__('Hi %s', 'ipin'), $recipient_name) . "\r\n\r\n";
		$message .= sprintf(__('%s wants to share "%s" with you.', 'ipin'), $user_identity, sanitize_text_field($board_info->name)) . "\r\n\r\n";
		if ($recipient_message !='') {
			$message .= sprintf(__('%s said, "%s".', 'ipin'), $user_identity, $recipient_message) . "\r\n\r\n";
		}
		$message .= sprintf(__('View board at %s', 'ipin'), home_url('/' . $wp_taxonomies["board"]->rewrite['slug'] . '/' . sanitize_title($board_info->name, '_') . '/') . $board_info->term_id) . '/' . "\r\n\r\n";
		$message .= sprintf(__('View %s\'s profile at %s', 'ipin'), $user_identity, home_url('/') . $wp_rewrite->author_base. '/' . $user_info->user_nicename . '/') . "\r\n\r\n";
		
		wp_mail($recipient_email, sprintf(__('%s wants to share a board with you from %s', 'ipin'), $user_identity, $blogname), $message);
	}
	exit;
}
add_action('wp_ajax_ipin-post-email', 'ipin_post_email');


//Report pin
function ipin_post_report() {
	$nonce = $_POST['nonce'];

	if (!wp_verify_nonce($nonce, 'ajax-nonce'))
		die();
	
	global $user_ID, $user_login;
	$post_id = $_POST['report_post_id'];
	$report_message = stripslashes(strip_tags($_POST['report_message']));
	$blogname = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);
	
	if ($user_ID) {
		$message = sprintf(__('User(%s) reported the "%s" pin.', 'ipin'), $user_login, preg_replace('/[\n\r]/', ' ', html_entity_decode(sanitize_text_field(get_the_title($post_id)), ENT_QUOTES, 'UTF-8'))) . "\r\n";
	} else {
		$message = sprintf(__('An unregistered user reported the "%s" pin.', 'ipin'), preg_replace('/[\n\r]/', ' ', html_entity_decode(sanitize_text_field(get_the_title($post_id)), ENT_QUOTES, 'UTF-8'))) . "\r\n";
	}
	$message .= sprintf(__('Message: %s', 'ipin'), $report_message) . "\r\n";
	$message .= sprintf(__('View pin at %s', 'ipin'), get_permalink($post_id)) . "\r\n\r\n";
	wp_mail(get_option('admin_email'), sprintf(__('[%s] Someone reported a pin', 'ipin'), $blogname), $message);
	exit;
}
add_action('wp_ajax_ipin-post-report', 'ipin_post_report');
add_action('wp_ajax_nopriv_ipin-post-report', 'ipin_post_report');


//Custom usermeta - allow admin to override email verification
function ipin_edit_user_profile($user) {
	if ('' != $verify_email = get_the_author_meta( '_Verify Email', $user->ID)) {
	?>
	<table class="form-table">
		<tr>
			<th><label for="emailverify">Email Verification Link</label></th>
			<td>
				<?php $verification_link .= sprintf('%s?email=verify&login=%s&key=%s', home_url('/login/'), rawurlencode($user->user_login), $verify_email); ?>
				<input type="text" name="_Verify_Email" id="_Verify_Email" value="<?php echo $verification_link; ?>" class="regular-text" /><br />
				<span class="description">Leave blank to allow user to login without email verification.</span>
			</td>
		</tr>
	</table>
<?php
	}
}
add_action('edit_user_profile', 'ipin_edit_user_profile');


function ipin_edit_user_profile_update($user_id) {
	if (!$_POST['_Verify_Email']) {
		delete_user_meta($user_id, '_Verify Email');
	}
}
add_action('edit_user_profile_update', 'ipin_edit_user_profile_update');


//Setup theme for first time
function ipin_setup() {
	global $wpdb;
	$ipin_version = get_option('ipin_version');
	if (!$ipin_version) {
		//setup database table
		$sql = "CREATE TABLE IF NOT EXISTS `" . $wpdb->prefix . "ipin_notifications` (
			`notification_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			`user_id` bigint(20) unsigned NOT NULL DEFAULT '0',
			`notification_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
			`notification_type` varchar(255) NOT NULL,
			`notification_from` bigint(20) unsigned NOT NULL DEFAULT '0',
			`notification_post_id` bigint(20) unsigned NOT NULL DEFAULT '0',
			`notification_message` longtext NOT NULL,
			`notification_read` tinyint(1) unsigned NOT NULL DEFAULT '0',
			PRIMARY KEY (`notification_id`),
			KEY user_id (`user_id`)
		) CHARACTER SET utf8 COLLATE utf8_general_ci;";
		
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql);
		
		//setup pages
		$page= array(
			'post_title' => 'Group Settings',
			'post_name' => 'grp-settings',
			'post_author' => 1,
			'post_status' => 'publish',
			'post_type' => 'page',
			'comment_status' => 'closed',
			'ping_status' => 'closed'
		);
		$pageid = wp_insert_post($page);
		add_post_meta($pageid, '_wp_page_template', 'page_cp_boards.php');
		
		$page = array(
			'post_title' => 'Login',
			'post_name' => 'login',
			'post_author' => 1,
			'post_status' => 'publish',
			'post_type' => 'page',
			'comment_status' => 'closed',
			'ping_status' => 'closed'
		);
		$pageid = wp_insert_post($page);
		add_post_meta($pageid, '_wp_page_template', 'page_cp_login.php');
		
		$page = array(
			'post_title' => 'Lost Your Password?',
			'post_name' => 'login-lpw',
			'post_author' => 1,
			'post_status' => 'publish',
			'post_type' => 'page',
			'comment_status' => 'closed',
			'ping_status' => 'closed'
		);
		$pageid = wp_insert_post($page);
		add_post_meta($pageid, '_wp_page_template', 'page_cp_login_lpw.php');
	
		$page = array(
			'post_title' => 'Item Settings',
			'post_name' => 'itm-settings',
			'post_author' => 1,
			'post_status' => 'publish',
			'post_type' => 'page',
			'comment_status' => 'closed',
			'ping_status' => 'closed'
		);
		$pageid = wp_insert_post($page);
		add_post_meta($pageid, '_wp_page_template', 'page_cp_pins.php');
	
		$page = array(
			'post_title' => 'Sign Up',
			'post_name' => 'signup',
			'post_author' => 1,
			'post_status' => 'publish',
			'post_type' => 'page',
			'comment_status' => 'closed',
			'ping_status' => 'closed'
		);
		$pageid = wp_insert_post($page);
		add_post_meta($pageid, '_wp_page_template', 'page_cp_register.php');
		
		$page = array(
			'post_title' => 'Settings',
			'post_name' => 'settings',
			'post_author' => 1,
			'post_status' => 'publish',
			'post_type' => 'page',
			'comment_status' => 'closed',
			'ping_status' => 'closed'
		);
		$pageid = wp_insert_post($page);
		add_post_meta($pageid, '_wp_page_template', 'page_cp_settings.php');
		
		$page = array(
			'post_title' => 'Everything',
			'post_name' => 'everything',
			'post_author' => 1,
			'post_status' => 'publish',
			'post_type' => 'page',
			'comment_status' => 'closed',
			'ping_status' => 'closed'
		);
		$pageid = wp_insert_post($page);
		add_post_meta($pageid, '_wp_page_template', 'page_everything.php');
		
		$page = array(
			'post_title' => 'Following',
			'post_name' => 'following',
			'post_author' => 1,
			'post_status' => 'publish',
			'post_type' => 'page',
			'comment_status' => 'closed',
			'ping_status' => 'closed'
		);
		$pageid = wp_insert_post($page);
		add_post_meta($pageid, '_wp_page_template', 'page_following.php');
	
		$page = array(
			'post_title' => 'Popular',
			'post_name' => 'popular',
			'post_author' => 1,
			'post_status' => 'publish',
			'post_type' => 'page',
			'comment_status' => 'closed',
			'ping_status' => 'closed'
		);
		$pageid = wp_insert_post($page);
		add_post_meta($pageid, '_wp_page_template', 'page_popular.php');
		
		$page = array(
			'post_title' => 'Source',
			'post_name' => 'source',
			'post_author' => 1,
			'post_status' => 'publish',
			'post_type' => 'page',
			'comment_status' => 'closed',
			'ping_status' => 'closed'
		);
		$pageid = wp_insert_post($page);
		add_post_meta($pageid, '_wp_page_template', 'page_source.php');
		add_post_meta($pageid, '_aioseop_disable', 'on'); //disable for aio seo
		
		$page = array(
			'post_title' => 'Notifications',
			'post_name' => 'notifications',
			'post_author' => 1,
			'post_status' => 'publish',
			'post_type' => 'page',
			'comment_status' => 'closed',
			'ping_status' => 'closed'
		);
		$pageid = wp_insert_post($page);
		add_post_meta($pageid, '_wp_page_template', 'page_cp_notifications.php');
		
		$page = array(
			'post_title' => 'Top Users',
			'post_name' => 'top-users',
			'post_author' => 1,
			'post_status' => 'publish',
			'post_type' => 'page',
			'comment_status' => 'closed',
			'ping_status' => 'closed'
		);
		$pageid = wp_insert_post($page);
		add_post_meta($pageid, '_wp_page_template', 'page_top_users.php');

		//setup top menu
		$menuname = 'Top Menu';
		$menulocation = 'top_nav';
		$menu_exists = wp_get_nav_menu_object($menuname);

		if( !$menu_exists){
			$menu_id = wp_create_nav_menu($menuname);
		
			$category_menu_id = wp_update_nav_menu_item($menu_id, 0, array(
				'menu-item-title' => 'Categories',
				'menu-item-url' => '#', 
				'menu-item-status' => 'publish'));
				
			wp_update_nav_menu_item($menu_id, 0, array(
				'menu-item-title' => 'Popular',
				'menu-item-url' => home_url('/popular/'), 
				'menu-item-status' => 'publish',
				'menu-item-parent-id' => $category_menu_id));
		
			wp_update_nav_menu_item($menu_id, 0, array(
				'menu-item-title' => 'Everything',
				'menu-item-url' => home_url('/everything/'), 
				'menu-item-status' => 'publish',
				'menu-item-parent-id' => $category_menu_id));

			if(!has_nav_menu($bpmenulocation)){
				$locations = get_theme_mod('nav_menu_locations');
				$locations[$menulocation] = $menu_id;
				set_theme_mod('nav_menu_locations', $locations);
			}
		}
		
		//remove default sidebar widgets
		update_option('sidebars_widgets', array());

		//setup user accounts
		$ipin_users = get_users('orderby=ID');
		foreach ($ipin_users as $user) {
			$board_parent_id = get_user_meta($user->ID, '_Board Parent ID', true);

			if ($board_parent_id == '') {
				$board_id = wp_insert_term (
					$user->ID,
					'board'
				);
				update_user_meta($user->ID, '_Board Parent ID', $board_id['term_id']);
				update_user_meta($user->ID, 'ipin_user_notify_likes', '1');
				update_user_meta($user->ID, 'ipin_user_notify_repins', '1');
				update_user_meta($user->ID, 'ipin_user_notify_follows', '1');
				update_user_meta($user->ID, 'ipin_user_notify_comments', '1');
			}
		}
	
		update_option('ipin_version', '2.1');
		add_action('admin_notices', 'ipin_admin_notices');
	} else if (floatval($ipin_version) <= 2.0) {
		update_option('ipin_version', '2.1');
	}
}
add_action('admin_init', 'ipin_setup');

if (EMPTY_TRASH_DAYS != 0) {
	add_action('admin_notices', 'ipin_admin_notices');
}

function ipin_admin_notices() {
	echo '<div class="error fade"><p><strong>Important! Please read the <a href="'
		 . admin_url('themes.php?page=theme_installation') . '">'
		 . 'Theme Installation</a> to finish installation.' . '</strong></div>';
}

//Setup Guide
function ipin_setup_guide() {
	if (function_exists('add_options_page'))
		add_theme_page('Theme Installation', 'Theme Installation', 'edit_theme_options', 'theme_installation', 'ipin_setup_guide_page');
}

function ipin_setup_guide_page() {
?>
<style type="text/css">
.wrap ol li { margin-bottom:30px; width: 520px; }
.wrap ul li { margin:3px 0 0 15px;list-style-type:disc; }
.wrap hr { border:none;border-top:1px dashed #aaa;height:0;margin:10px 0 0 0; }
</style>
<div class="wrap">
	<?php screen_icon(); ?>
    <h2>Theme Installation</h2>
	<hr />
    <table class="form-table"><tr><th>
		<div style="background: #fcfcfc; border: 1px solid #eee; padding: 15px; max-width: 550px;">
			<strong>Server Checklist</strong>
			<ul>
				<li>PHP Extension: Curl 
				<?php if (extension_loaded('curl')) { ?>
				<span style="color: green; font-weight: bold; font-style:italic;">enabled</span>
				<?php } else { $error_extension = true; ?>
				<span style="color: red; font-weight: bold; font-style:italic;">not enabled!</span>
				<?php } ?>
				</li>
				
				<li>PHP Extension: Dom 
				<?php if (extension_loaded('dom')) { ?>
				<span style="color: green; font-weight: bold; font-style:italic;">enabled</span>
				<?php } else { $error_extension = true; ?>
				<span style="color: red; font-weight: bold; font-style:italic;">not enabled!</span>
				<?php } ?>
				</li>
				
				<li>PHP Extension: Mbstring 
				<?php if (extension_loaded('mbstring')) { ?>
				<span style="color: green; font-weight: bold; font-style:italic;">enabled</span>
				<?php } else { $error_extension = true; ?>
				<span style="color: red; font-weight: bold; font-style:italic;">not enabled!</span>
				<?php } ?>
				</li>
				
				<li>PHP Extension: GD/Imagemagick 
				<?php if (extension_loaded('gd') || extension_loaded('imagemagick')) { ?>
				<span style="color: green; font-weight: bold; font-style:italic;">enabled</span>
				<?php } else { $error_extension = true; ?>
				<span style="color: red; font-weight: bold; font-style:italic;">not enabled!</span>
				<?php } ?>
				</li>
				
				<li>WP-Content Directory Permission
				<?php if (is_writable(WP_CONTENT_DIR)) { ?>
				<span style="color: green; font-weight: bold; font-style:italic;">writable</span>
				<?php } else { ?>
				<span style="color: red; font-weight: bold; font-style:italic;">not writable</span>
				<?php } ?>
				</li>
				
				<?php if ($error_extension ) { ?>
				<p><span style="color: red; font-weight: bold; font-style:italic;">Alert:</span> Required php extension not enabled. Please check with your host to enable them.</p>
				<?php } ?>
				
				<?php if (!is_writable(WP_CONTENT_DIR)) { ?>
				<p><span style="color: red; font-weight: bold; font-style:italic;">Alert:</span> WP-Content directory (<?php echo WP_CONTENT_DIR; ?>) not writeable. Please change directory permission to 755 or 777. If 777 works, check with your host if it's possible to work with 755, which is safer.</p>
				<?php } ?>
				
				<?php if (!$error_extension && is_writable(WP_CONTENT_DIR)) { ?>
				<p>Server checklist passed. Please proceed below.</p>
				<?php } ?>
			</ul>
		</div>

		<ol>
			<li>
				Go to <strong><a href="<?php echo admin_url('options-general.php'); ?>" target="_blank">Settings > General</a></strong> and set
				<ul>
					<li>Membership = Anyone can register (ticked)</li>
					<li>New User Default Role = Author</li>
				</ul>
			</li>
			
			<li>
				Go to <strong><a href="<?php echo admin_url('options-reading.php'); ?>" target="_blank">Settings > Reading</a></strong> and set
				<ul>
					<li>Blog pages show at most = 20 (or as you like. 20 for a good start)</li>
				</ul>
			</li>
			
			<li>
				Go to <strong><a href="<?php echo admin_url('options-media.php'); ?>" target="_blank">Settings > Media</a></strong> and set
				<ul>
					<li>Medium size: Max Width = 235, Max Height = 4096</li>
					<li>Large size: Max Width = 700, Max Height = 4096</li>
				</ul>
			</li>
			
			<li>
				Go to <strong><a href="<?php echo admin_url('options-permalink.php'); ?>" target="_blank">Settings > Permalinks</a></strong> and set
				<ul>
					<li>Custom Structure = /pin/%post_id%/</li>
				</ul>
			</li>
			
			<li>
				Go to <strong><a href="<?php echo admin_url('edit-tags.php?taxonomy=category'); ?>" target="_blank">Posts > Categories</a></strong>
				<ul>
					<li>Add your categories e.g. Celebrities, Food, Technology</li>
				</ul>
			</li>
			
			<li>
				Go to <strong><a href="<?php echo admin_url('nav-menus.php'); ?>" target="_blank">Appearance > Menus</a></strong>
				<ul>
					<li>From the "Categories" box, select the categories you created earlier and click "Add to Menu". Drag the newly added items slightly to the right, such that they are aligned with the "Everything" menu.</li>
				</ul>
			</li>
			
			<li>
				Go to <strong><a href="<?php echo admin_url('themes.php?page=options-framework'); ?>" target="_blank">Appearance > Theme Options</a></strong>
				<ul>
					<li>Tweak to your liking</li>
					<li>Remember to read the Notes too</li>
				</ul>
			</li>
			
			<li>
				Go to <strong><a href="<?php global $current_user, $wp_rewrite; echo home_url('/' . $wp_rewrite->author_base . '/' . $current_user->data->user_nicename . '/'); ?>" target="_blank"><?php echo home_url('/' . $wp_rewrite->author_base . '/' . $current_user->data->user_nicename . '/'); ?></a></strong>
				<ul>
					<li>If you see a 404 error, go to <a href="<?php echo admin_url('options-permalink.php'); ?>" target="_blank">Settings > Permalinks</a> and simply click "Save Changes" again</li>
				</ul>
			</li>
			
			<li>
				Edit <strong>wp-config.php</strong>
				<ul>
					<li>Open wp-config.php file on your wordpress server directory, look for <em>define('WP_DEBUG', false);</em> and below it add <em>define('EMPTY_TRASH_DAYS', 0);</em></li>
				</ul>
			</li>
			
			<li>
				Install <strong>WP Social Login</strong> plugin (optional - for Facebook & Twitter login)
				<ul>
					<li>See <a href="<?php echo admin_url('themes.php?page=options-framework'); ?>" target="_blank">Appearance > Theme Options</a> > Notes tab > Recommended Plugins</li>
				</ul>
			</li>
			
			<li>
				<strong>Enjoy your theme! Or continue below to setup a sideblog (optional).</strong>
			</li>
			
			<li>
				Go to <strong><a href="<?php echo admin_url('edit-tags.php?taxonomy=category'); ?>" target="_blank">Posts > Categories</a></strong>
				<ul>
					<li>Add a new category e.g Blog</li>
				</ul>
			</li>
			
			<li>
				Go to <strong><a href="<?php echo admin_url('themes.php?page=options-framework'); ?>" target="_blank">Appearance > Theme Options</a></strong>
				<ul>
					<li>Under "Category For Blog", select the blog category you just created</li>
				</ul>
			</li>
			
			<li>
				Go to <strong><a href="<?php echo admin_url('nav-menus.php'); ?>" target="_blank">Appearance > Menus</a></strong>
				<ul>
					<li>From the "Categories" box, select the blog category you just created and click "Add to Menu"</li>
				</ul>
			</li>
			
			<li>
				Go to <strong><a href="<?php echo admin_url('post-new.php'); ?>" target="_blank">Posts > Add New</a></strong>
				<ul>
					<li>Create your post and make sure to select "Blog" under "Categories"</li>
				</ul>
			</li>
		</ol>
    </th></tr></table>
</div>
<?php
}
add_action('admin_menu', 'ipin_setup_guide');
?>