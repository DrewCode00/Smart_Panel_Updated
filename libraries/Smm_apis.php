<?php 
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Include class
 */
class Smm_apis {
	function __construct(){
		$apis_list = apis_list();
		foreach ($apis_list as $key => $api_name) {
			if(file_exists(APPPATH."../app/libraries/smms/".$key.".php")){
		        require_once 'smms/'.$key.'.php';
		    }
		}
	}

	function services($api_type, $api_key, $api_url){
		switch ($api_type) {
			case 'instasmm':
				$api 	  = new smm_instasmm($api_key, $api_url);
				$result_tmp = $api->services();
				$result_tmp = (array)$result_tmp;
				$result = NULL;
				if (!empty($result_tmp)  && is_array($result_tmp)){
					$i = 0;
					foreach ($result_tmp as $key => $row) {
						$result[$i] = (object)array();
						$result[$i]->service       = $row->service_id;
						$result[$i]->name          = $row->name;
						$result[$i]->category      = $row->category;
						$result[$i]->rate          = $row->price;
						$result[$i]->type          = 'default';
						$result[$i]->min           = $row->min_amount;
						$result[$i]->max           = $row->max_amount;
						$result[$i]->desc          = $row->description;
						$result[$i]->type          = 'default';
						$result[$i]->dripfeed      = 0;
						$i++;
					}
				}
				break;

			case 'indusrabbit':
				$api 	  = new smm_indusrabbit($api_key, $api_url);
				$result = $api->packages();
				if (!empty($result)  && is_array($result)){
					foreach ($result as $key => $row) {
						$result[$key]->category = $row->service;
						$result[$key]->service  = $row->id;

						if ($row->type == "custom_data") {
							$result[$key]->type = "custom_comments";
						}
						unset($result[$key]->id);
						unset($result[$key]->service_id);
					}
				}
				break;

			case 'yoyomedia':
				$api 	  = new smm_yoyomedia($api_key, $api_url);
				$result   = $api->services();
				if (!empty($result)  && is_array($result)){
					foreach ($result as $key => $row) {
						$result[$key]->service       = $row->type;
						$result[$key]->rate          = $row->price_per_1000;
						$result[$key]->min           = $row->order_min;
						$result[$key]->max           = $row->order_max;
						$result[$key]->desc          = $row->description;
						$result[$key]->type          = 'default';
						$result[$key]->dripfeed      = 0;
						
						unset($result[$key]->price_per_1000);
						unset($result[$key]->order_min);
						unset($result[$key]->order_max);
						unset($result[$key]->description);
					}
				}
				break;

			default:
				$api      = new smm_standard($api_key, $api_url);
				$result   = $api->services();
				break;
		}

		return $result;
	}

	function order($api_type, $api_key, $api_url, $data_post){
		switch ($api_type) {

			case 'instasmm':
				$api 	  	= new smm_instasmm($api_key, $api_url);
				$result     = $api->addOrder($data_post);
				if (isset($result->orderID)) {
					$result->order = $result->orderID;
					unset($result->orderID);
				}
				break;

			case 'indusrabbit':
				$api 	  = new smm_indusrabbit($api_key, $api_url);
				$result   = $api->order($data_post);
				if (isset($result->errors)) {
					$result->error = $result->errors[0];
					unset($result->errors);
				}

				break;

			case 'yoyomedia':
				$api 	  = new smm_yoyomedia($api_key, $api_url);
				$result   = $api->order($data_post);

				if (isset($result->status)) {
					switch ($result->status) {
						case 'ok':
							unset($result->status);
							unset($result->message);
							break;	

						case 'fail':
							$result->error = $result->message;
							unset($result->status);
							unset($result->message);
							break;
					}
				}

				break;

			default:
				$api      = new smm_standard($api_key, $api_url);
				$result   = $api->order($data_post);
				break;
		}

		return $result;
	}


	/**
	 * Respone data Standard
	 * {
		    "charge": "0.27819",
		    "start_count": "3572",
		    "status": "Partial",
		    "remains": "157",
		    "currency": "USD"
		}
	 *
	 */
	
	function status($api_type, $api_key, $api_url, $order_id){
		switch ($api_type) {

			case 'instasmm':
				/**
				 *
				 * {
					"orderQuantity": 10000,
					"startCount": "55",
					"remaining_amount": "1000.0000000000",
					"refunded_amount": "1000.0000000000",
					"orderStatus": "Partially Completed",
					"orderDate": "2016-10-15 12:47:53",
					"orderPrice": "2.0700000000"
				}
				 *
				 */

				$api 	  = new smm_instasmm($api_key, $api_url);
				$result = $api->orderStatus($order_id);
				if (isset($result->orderQuantity)) {
					$result->charge       = $result->orderPrice;
					$result->remains      = $result->orderStatus;
					$result->start_count  = $result->startCount;
					$result->status       = $result->orderStatus;
					if (strrpos($result->status, 'Partially')) {
						$result->status       = 'Partial';
					}

					unset($result->orderQuantity);
					unset($result->startCount);
					unset($result->remaining_amount);
					unset($result->refunded_amount);
					unset($result->orderStatus);
					unset($result->orderDate);
					unset($result->orderPrice);
				}

				break;

			case 'indusrabbit':
				/**
				 *
				 * {
					  "status": "Completed",
					  "start_counter": "600",
					  "remains": "600"
					}
				 *
				 */

				$api 	  = new smm_indusrabbit($api_key, $api_url);
				$result = $api->status($order_id);
				if (isset($result->errors)) {
					$result->error = $result->errors[0];
					unset($result->errors);
				}

				if (isset($result->status)) {
					$result->start_count = $result->start_counter;
					unset($result->start_counter);
					if ($result->status == "Cancelled") {
						$result->status = "Canceled";
					}
				}
				break;

			case 'yoyomedia':

				/**
				 *
				 * {
					    "status": "ok",
					    "type": "instagram_real_active_s1",
					    "time": "1503642755",
					    "amount": 1000,
					    "price": "0.9000",
					    "link": "instagram",
					    "order_status": "Processing",
					    "count_start": "100",
					    "count_current": "100",
					    "count_remain": "100",
					    "count_finish": "200"
					}
				 *
				 */
				
				$api 	  = new smm_yoyomedia($api_key, $api_url);
				$result   = $api->status($order_id);

				if (isset($result->status)) {
					switch ($result->status) {
						case 'ok':
							$result->status      = $result->order_status;
							$result->charge      = $result->price;
							$result->start_count = $result->count_start;
							$result->remains     = $result->count_remain;
							
							unset($result->type);
							unset($result->time);
							unset($result->amount);
							unset($result->price);
							unset($result->link);
							unset($result->order_status);
							unset($result->count_start);
							unset($result->count_remain);
							unset($result->count_finish);
							break;	

						case 'fail':
							$result->error = $result->message;
							unset($result->status);
							unset($result->message);
							break;
					}
				}

				break;

			default:

				/**
				 *
				 * {
					    "charge": "0.27819",
					    "start_count": "3572",
					    "status": "Partial",
					    "remains": "157",
					    "currency": "USD"
					}
				 *
				 */
					
				$api      = new smm_standard($api_key, $api_url);
				$result   = $api->status($order_id);
				break;
		}

		return $result;
	}



	function balance($api_type, $api_key, $api_url){
		switch ($api_type) {

			case 'instasmm':
				$api 	  = new smm_instasmm($api_key, $api_url);
				$result = $api->balance();
				break;

			case 'indusrabbit':
				$api 	  = new smm_indusrabbit($api_key, $api_url);
				$result   = $api->balance();
				// pr($result, 1);
				break;

			case 'yoyomedia':
				$api 	  = new smm_yoyomedia($api_key, $api_url);
				$result   = $api->balance();
				if (isset($result->balance)) {
					$result->currency = "USD";
				}
				break;

			default:
				$api      = new smm_standard($api_key, $api_url);
				$result   = $api->balance();
				break;
		}
		return $result;
	}

}
