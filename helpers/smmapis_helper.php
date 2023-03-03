<?php

if(!function_exists('apis_list')){
	function apis_list($type = ""){
		$apis = array(
			'standard'    => "Standard (JAP, Perfectpanel, Smartpanel)",
			'indusrabbit' => "Type 2 (indusrabbit, Indiansmartpanel)",
			'yoyomedia'   => "Type 3 (Yoyomedia)",
			'instasmm'    => "Type 4 (Instasmm)",
		);
		return $apis;
	}
}

if (!function_exists('all_services_type')) {
	function all_services_type(){
		$all_services_type = array(
	        'default'                 => lang('Default'),
	        'subscriptions'           => lang('Subscriptions'),
	        'custom_comments'         => lang('custom_comments'),
	        'custom_comments_package' => lang('custom_comments_package'),
	        'mentions_with_hashtags'  => lang('mentions_with_hashtags'),
	        'mentions_custom_list'    => lang('mentions_custom_list'),
	        'mentions_hashtag'        => lang('mentions_hashtag'),
	        'mentions_user_followers' => lang('mentions_user_followers'),
	        'mentions_media_likers'   => lang('mentions_media_likers'),
	        'package'                 => lang('package'),
	        'comment_likes'           => lang('comment_likes'),
	  	);

	  	return $all_services_type;
	}
}

if (!function_exists('api_connect')) {
	function api_connect($url, $post = array("")) {
	    $_post = Array();

        if (is_array($post)) {
          foreach ($post as $name => $value) {
            $_post[] = $name.'='.urlencode($value);
          }
        }
        if (is_array($post)) {
          $url_complete = join('&', $_post);
        }
        $url = $url."?".$url_complete;
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_USERAGENT, 'API (compatible; MSIE 5.01; Windows NT 5.0)');
        $result = curl_exec($ch);
        if (curl_errno($ch) != 0 && empty($result)) {
          $result = false;
        }
        curl_close($ch);
        return $result;
	}
}

/*----------  Get user price  ----------*/
if (!function_exists('get_user_price')) {
	function get_user_price($uid, $service) {
	    $CI = &get_instance();
	    if(empty($CI->help_model)){
			$CI->load->model('model', 'help_model');
		}
		$user_price = $CI->help_model->get('service_price', USERS_PRICE, ['uid' => $uid, 'service_id' => $service->id]);
		if (isset($user_price->service_price)) {
			$price = $user_price->service_price;
		}else{
			$price = $service->price;
		}
		return $price;
	}
}
