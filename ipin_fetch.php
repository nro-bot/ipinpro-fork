<?php
error_reporting(0);
require_once(dirname(dirname(dirname(dirname(__FILE__)))) . '/wp-load.php');
if (!is_user_logged_in() || !wp_verify_nonce($_GET['nonce'], 'ajax-nonce')) { die(); }

$url = esc_url_raw('http' . $_GET['url']);

if ($url != 'http://http') {
	$args = array(
		'user-agent' => 'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)'
	);
	$response = wp_remote_get($url, $args);

	if(is_wp_error($response)) {
	   echo 'error' . $response->get_error_message();
	} else {
		preg_match("/content=\"text\/(.*)>/i", $response['body'], $content_type);

		if (strpos($response['headers']['content-type'], 'text/') !== false && ($response['response']['code'] == '200')) {
			preg_match("/<title>(.*?)<\/title>/is", $response['body'], $title);

			//for multiple languages in title ref: http://php.net/manual/en/function.htmlentities.php
			if (strpos($content_type[0], '8859-1') !== false) {
				$ipintitle = '<ipintitle>' . htmlentities($title[1], ENT_QUOTES, 'ISO-8859-1') . '</ipintitle>';
			} else if (strpos($content_type[0], '8859-5') !== false) {
				$ipintitle = '<ipintitle>' . htmlentities($title[1], ENT_QUOTES, 'ISO-8859-5') . '</ipintitle>';
			} else if (strpos($content_type[0], '8859-15') !== false) {
				$ipintitle = '<ipintitle>' . htmlentities($title[1], ENT_QUOTES, 'ISO-8859-15') . '</ipintitle>';
			} else if (strpos($content_type[0], '866') !== false) {
				$ipintitle = '<ipintitle>' . htmlentities($title[1], ENT_QUOTES, 'cp866') . '</ipintitle>';
			} else if (strpos($content_type[0], '1251') !== false) {
				$ipintitle = '<ipintitle>' . htmlentities($title[1], ENT_QUOTES, 'cp1251') . '</ipintitle>';
			} else if (strpos($content_type[0], '1252') !== false) {
				$ipintitle = '<ipintitle>' . htmlentities($title[1], ENT_QUOTES, 'cp1252') . '</ipintitle>';
			} else if (stripos($content_type[0], 'koi8') !== false) {
				$ipintitle = '<ipintitle>' . htmlentities($title[1], ENT_QUOTES, 'KOI8-R') . '</ipintitle>';
			} else if (stripos($content_type[0], 'hkscs') !== false) {
				$ipintitle = '<ipintitle>' . mb_convert_encoding($title[1], 'UTF-8', 'BIG5-HKSCS') . '</ipintitle>';
			} else if (stripos($content_type[0], 'big5') !== false || strpos($content_type[0], '950') !== false ) {
				$ipintitle = '<ipintitle>' . mb_convert_encoding($title[1], 'UTF-8', 'BIG5') . '</ipintitle>';
			} else if (strpos($content_type[0], '2312') !== false || strpos($content_type[0], '936') !== false ) {
				$ipintitle = '<ipintitle>' . mb_convert_encoding($title[1], 'UTF-8', 'GB2312') . '</ipintitle>';
			} else if (stripos($content_type[0], 'jis') !== false || strpos($content_type[0], '932') !== false ) {
				$ipintitle = '<ipintitle>' . mb_convert_encoding($title[1], 'UTF-8', 'Shift_JIS') . '</ipintitle>';
			} else if (stripos($content_type[0], 'jp') !== false) {
				$ipintitle = '<ipintitle>' . mb_convert_encoding($title[1], 'UTF-8', 'EUC-JP') . '</ipintitle>';
			} else {
				$ipintitle = '<ipintitle>' . htmlentities($title[1], ENT_QUOTES, 'UTF-8') . '</ipintitle>';
			}
			
			if (of_get_option('form_title_desc') != 'separate') {
				$ipindescription = '';
			} else {
				preg_match('/<meta.*?name=("|\')description("|\').*?content=("|\')(.*?)("|\')/i', $response['body'], $description);
							
				//for multiple languages in description ref: http://php.net/manual/en/function.htmlentities.php
				if (strpos($content_type[0], '8859-1') !== false) {
					$ipindescription = '<ipindescription>' . htmlentities($description[4], ENT_QUOTES, 'ISO-8859-1') . '</ipindescription>';
				} else if (strpos($content_type[0], '8859-5') !== false) {
					$ipindescription = '<ipindescription>' . htmlentities($description[4], ENT_QUOTES, 'ISO-8859-5') . '</ipindescription>';
				} else if (strpos($content_type[0], '8859-15') !== false) {
					$ipindescription = '<ipindescription>' . htmlentities($description[4], ENT_QUOTES, 'ISO-8859-15') . '</ipindescription>';
				} else if (strpos($content_type[0], '866') !== false) {
					$ipindescription = '<ipindescription>' . htmlentities($description[4], ENT_QUOTES, 'cp866') . '</ipindescription>';
				} else if (strpos($content_type[0], '1251') !== false) {
					$ipindescription = '<ipindescription>' . htmlentities($description[4], ENT_QUOTES, 'cp1251') . '</ipindescription>';
				} else if (strpos($content_type[0], '1252') !== false) {
					$ipindescription = '<ipindescription>' . htmlentities($description[4], ENT_QUOTES, 'cp1252') . '</ipindescription>';
				} else if (stripos($content_type[0], 'koi8') !== false) {
					$ipindescription = '<ipindescription>' . htmlentities($description[4], ENT_QUOTES, 'KOI8-R') . '</ipindescription>';
				} else if (stripos($content_type[0], 'hkscs') !== false) {
					$ipindescription = '<ipindescription>' . mb_convert_encoding($description[4], 'UTF-8', 'BIG5-HKSCS') . '</ipindescription>';
				} else if (stripos($content_type[0], 'big5') !== false || strpos($content_type[0], '950') !== false ) {
					$ipindescription = '<ipindescription>' . mb_convert_encoding($description[4], 'UTF-8', 'BIG5') . '</ipindescription>';
				} else if (strpos($content_type[0], '2312') !== false || strpos($content_type[0], '936') !== false ) {
					$ipindescription = '<ipindescription>' . mb_convert_encoding($description[4], 'UTF-8', 'GB2312') . '</ipindescription>';
				} else if (stripos($content_type[0], 'jis') !== false || strpos($content_type[0], '932') !== false ) {
					$ipindescription = '<ipindescription>' . mb_convert_encoding($description[4], 'UTF-8', 'Shift_JIS') . '</ipindescription>';
				} else if (stripos($content_type[0], 'jp') !== false) {
					$ipindescription = '<ipindescription>' . mb_convert_encoding($description[4], 'UTF-8', 'EUC-JP') . '</ipindescription>';
				} else {
					$ipindescription = '<ipindescription>' . htmlentities($description[4], ENT_QUOTES, 'UTF-8') . '</ipindescription>';
				}
			}
								
			$body = '<!DOCTYPE html><html><head><title>&nbsp;</title><meta charset="UTF-8" /></head><body>';
			$body .= $ipintitle . $ipindescription;

			$dom = new domDocument;
			libxml_use_internal_errors(true);
			$dom->loadHTML($response['body']);
			$dom->preserveWhiteSpace = false;
			$images = $dom->getElementsByTagName('img');

			foreach ($images as $image) {
				$prepend = '';
				if (substr($image->getAttribute('src'), 0, 2) == '//') {
					$prepend = 'http:';
				}
				$body .= '<a href="#"><img src="' . $prepend . $image->getAttribute('src') .'" /></a>';
			}

			$body .= '</body></html>';
			$body = absolute_url($body, parse_url($url, PHP_URL_SCHEME) . '://' . parse_url($url, PHP_URL_HOST));
			
			echo $body;
		} else {
			echo 'error' . __('Invalid content', 'ipin');
		}
	}
} else {
	echo 'error' . __('Invalid content', 'ipin');
}

//convert relative to absolute path for images
//ref: http://www.howtoforge.com/forums/showthread.php?t=4
function absolute_url($txt, $base_url){
  $needles = array('src="');
  $new_txt = '';
  if(substr($base_url,-1) != '/') $base_url .= '/';
  $new_base_url = $base_url;
  $base_url_parts = parse_url($base_url);

  foreach($needles as $needle){
    while($pos = strpos($txt, $needle)){
      $pos += strlen($needle);
      if(substr($txt,$pos,7) != 'http://' && substr($txt,$pos,8) != 'https://' && substr($txt,$pos,6) != 'ftp://' && substr($txt,$pos,9) != 'mailto://'){
        if(substr($txt,$pos,1) == '/') $new_base_url = $base_url_parts['scheme'].'://'.$base_url_parts['host'];
        $new_txt .= substr($txt,0,$pos).$new_base_url;
      } else {
        $new_txt .= substr($txt,0,$pos);
      }
      $txt = substr($txt,$pos);
    }
    $txt = $new_txt.$txt;
    $new_txt = '';
  }
  return $txt;
}
?>