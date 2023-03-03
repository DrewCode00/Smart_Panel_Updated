<?php
defined('BASEPATH') OR exit('No direct script access allowed');
 
class services extends MX_Controller {
	public $tb_users;
	public $tb_categories;
	public $tb_services;
	public $tb_api_providers;
	public $columns;
	public $module;
	public $module_name;
	public $module_icon;

	public function __construct(){
		parent::__construct();
		$this->load->model(get_class($this).'_model', 'model');
		//Config Module
		$this->tb_categories      = CATEGORIES;
		$this->tb_services        = SERVICES;
		$this->tb_api_providers   = API_PROVIDERS;
		$this->module_name        = 'Services';
		$this->module             = get_class($this);
		$this->module_icon        = "fa ft-users";

		$this->columns = array(
			"price"            => lang("rate_per_1000")."(".get_option("currency_symbol","").")",
			"min_max"          => lang("min__max_order"),
			"desc"             => lang("Description"),
		);

        if (get_role("admin") || get_role("supporter")) {
			$this->columns = array(
				"provider"         => 'Provider',
				"price"            => lang("rate_per_1000")."(".get_option("currency_symbol","").")",
				"min_max"          => lang("min__max_order"),
				"desc"             => lang("Description"),
				"dripfeed"         => lang("dripfeed"),
				"status"           => lang("Status"),
			);
		}				
	}

	public function index(){

		if (!session('uid') && get_option("enable_service_list_no_login") != 1) {
			redirect(cn());
		}

		$all_services = $this->model->get_services_list();
		$data = array(
			"module"       => get_class($this),
			"columns"      => $this->columns,
			"all_services" => $all_services,
			"categories"   => $all_services,
			"custom_rates" => $this->model->get_custom_rates(),
		);
		
		if (!session('uid')) {
			$this->template->set_layout('general_page');
			$this->template->build("index", $data);
		}
		$this->template->build("index", $data);
	}

	public function update($ids = ""){
		if (!get_role('admin')) _validation('error', "Permission Denied!");

		$service     = $this->model->get("*", $this->tb_services, "ids = '{$ids}' ");
		$categories  = $this->model->fetch("*", $this->tb_categories, "status = 1", 'sort','ASC');
		$api_providers  = $this->model->fetch("*", $this->tb_api_providers, "status = 1", 'id','ASC');
		$data = array(
			"module"   			=> get_class($this),
			"service" 			=> $service,
			"categories" 		=> $categories,
			"api_providers" 	=> $api_providers,
		);
		$this->load->view('update', $data);
	}

	public function desc($ids = ""){
		$service    = $this->model->get("id, ids, name, desc", $this->tb_services, "ids = '{$ids}' ");
		$data = array(
			"module"   		=> get_class($this),
			"service" 		=> $service,
		);
		$this->load->view('descriptions', $data);
	}

	public function ajax_update($ids = ""){
		_is_ajax($this->module);

		if (!get_role('admin')) _validation('error', "Permission Denied!");

		$name 		        = post("name");
		$category	        = post("category");
		$min	            = post("min");
		$max	            = post("max");
		$add_type			= post("add_type");
		$price	            = (double)post("price");
		$status 	        = (int)post("status");
		$desc 	            = $_POST['desc'];

		if($name == ""){
			ms(array(
				"status"  => "error",
				"message" => lang("name_is_required")
			));
		}

		if($category == ""){
			ms(array(
				"status"  => "error",
				"message" => lang("category_is_required")
			));
		}

		if($min == ""){
			ms(array(
				"status"  => "error",
				"message" => lang("min_order_is_required")
			));
		}

		if($max == ""){
			ms(array(
				"status"  => "error",
				"message" => lang("max_order_is_required")
			));
		}

		if($min > $max){
			ms(array(
				"status"  => "error",
				"message" => lang("max_order_must_to_be_greater_than_min_order")
			));
		}

		if($price == ""){
			ms(array(
				"status"  => "error",
				"message" => lang("price_invalid")
			));
		}

		// $decimal_places = get_option("auto_rounding_x_decimal_places", 2);
		// if(strlen(substr(strrchr($price, "."), 1)) > $decimal_places || strlen(substr(strrchr($price, "."), 1)) < 0){
		// 	ms(array(
		// 		"status"  => "error",
		// 		"message" => lang("price_invalid_format")
		// 	));
		// }

		$data = array(
			"uid"             => session('uid'),
			"cate_id"         => $category,
			"name"            => $name,
			"desc"            => $desc,
			"min"             => $min,
			"max"             => $max,
			"price"           => $price,
			"status"          => $status,
		);

		/*----------  Fields for Service API type  ----------*/
		switch ($add_type) {
			case 'api':
				$api_provider_id	         = post("api_provider_id");
				$original_price	             = post("original_price");
				$api_service_id	             = post("api_service_id");
				$api_service_type	         = post("api_service_type");
				$api_service_dripfeed	     = (int)post("api_service_dripfeed");
				
				$api = $this->model->get("ids", $this->tb_api_providers, ['id' => $api_provider_id, 'status' => 1]);
				if (empty($api)) {
					ms(array(
						"status"  => "error",
						"message" => lang("api_provider_does_not_exists")
					));
				}

				if ($api_service_id == "") {
					ms(array(
						"status"  => "error",
						"message" => 'API Service ID invalid format'
					));
				}
				$data['api_provider_id'] = $api_provider_id;
				$data['api_service_id']  = $api_service_id;
				$data['original_price']  = $original_price;
				$data['type']            = $api_service_type;
				$data['dripfeed']        = $api_service_dripfeed;
				break;
			
			default:

				$service_type_array = array('default', 'subscriptions', 'custom_comments', 'custom_comments_package', 'mentions_with_hashtags', 'mentions_custom_list', 'mentions_hashtag', 'mentions_user_followers', 'mentions_media_likers', 'package', 'comment_likes');

				if (!in_array(post("service_type"), $service_type_array)) {
					ms(array(
						"status"  => "error",
						"message" => 'Service Type invalid format'
					));
				}
				$data['api_provider_id'] = "";
				$data['api_service_id']  = "";
				$data['type']            = post("service_type");
				$data['dripfeed']        = (int)post("dripfeed");
				break;
		}
		
		$data['add_type'] = $add_type;

		$check_item = $this->model->get("ids", $this->tb_services, "ids = '{$ids}'");
		
		if(empty($check_item)){
			$data["ids"]     = ids();
			$data["changed"] = NOW;
			$data["created"] = NOW;

			$this->db->insert($this->tb_services, $data);
		}else{
			$data["changed"] = NOW;
			$this->db->update($this->tb_services, $data, array("ids" => $check_item->ids));
		}

		ms(array(
			"status"  => "success",
			"message" => lang("Update_successfully")
		));
	}
	
	public function ajax_search(){
		_is_ajax($this->module);

		$k = post("query");
		$k = htmlspecialchars($k);
		$services = $this->model->get_services_by_search($k);
		$data = array(
			"module"       => get_class($this),
			"columns"      => $this->columns,
			"services"     => $services,
			"custom_rates" => $this->model->get_custom_rates(),
		);
		$this->load->view("ajax_search", $data);
	}
	
	public function ajax_service_sort_by_cate($id){
		$data = array(
			"module"     => get_class($this),
			"columns"    => $this->columns,
			"cate_name"  => get_field($this->tb_categories, ['id' => $id], 'name'),
			"services"   => $this->model->get_services_by_cate_id($id),
		);
		$this->load->view("ajax_search", $data);
	}

	public function ajax_load_services_by_cate($id){
		$data = array(
			"module"     => get_class($this),
			"columns"    => $this->columns,
			"services"   => $this->model->get_services_by_cate_id($id),
			"cate_id"    => $id,
		);
		$this->load->view("ajax_load_services_by_cate", $data);
	}

	public function ajax_delete_item($ids = ""){
		_is_ajax($this->module);
		$this->model->delete($this->tb_services, $ids, false);
	}

	// Change Item Status
	public function ajax_toggle_item_status($id = ""){

		_is_ajax($this->module);
		if (!get_role('admin')) _validation('error', "Permission Denied!");


		$status  = post('status');
		$item  = $this->model->get("id", $this->tb_services, ['id' => $id]);
		if ($item ) {
			$this->db->update($this->tb_services, ['status' => (int)$status], ['id' => $id]);
			_validation('success', lang("Update_successfully"));
		}
	}

	public function ajax_actions_option(){
		_is_ajax($this->module);
		if (!get_role('admin')) _validation('error', "Permission Denied!");
		
		$type = post("type");
		$idss = post("ids");
		if ($type == '') {
			ms(array(
				"status"  => "error",
				"message" => lang('There_was_an_error_processing_your_request_Please_try_again_later')
			));
		}

		if (in_array($type, ['delete', 'deactive', 'active']) && empty($idss)) {
			ms(array(
				"status"  => "error",
				"message" => lang("please_choose_at_least_one_item")
			));
		}
		switch ($type) {
			case 'delete':
				foreach ($idss as $key => $ids) {
					$this->db->delete($this->tb_services, ['ids' => $ids]);
				}
				ms(array(
					"status"  => "success",
					"message" => lang("Deleted_successfully")
				));
				break;
			case 'deactive':
				foreach ($idss as $key => $ids) {
					$this->db->update($this->tb_services, ['status' => 0], ['ids' => $ids]);
				}
				ms(array(
					"status"  => "success",
					"message" => lang("Updated_successfully")
				));
				break;

			case 'active':
				foreach ($idss as $key => $ids) {
					$this->db->update($this->tb_services, ['status' => 1], ['ids' => $ids]);
				}
				ms(array(
					"status"  => "success",
					"message" => lang("Updated_successfully")
				));
				break;


			case 'all_deactive':
				$deactive_services = $this->model->fetch("*", $this->tb_services, ['status' => 0]);
				if (empty($deactive_services)) {
					ms(array(
						"status"  => "error",
						"message" => lang("failed_to_delete_there_are_no_deactivate_service_now")
					));
				}
				$this->db->delete($this->tb_services, ['status' => 0]);
				ms(array(
					"status"  => "success",
					"message" => lang("Deleted_successfully")
				));

				break;
			
			default:
				ms(array(
					"status"  => "error",
					"message" => lang('There_was_an_error_processing_your_request_Please_try_again_later')
				));
				break;
		}

	}

	// Get Services From API for Update page
	public function ajax_get_services_from_api($api_service_id = ""){
		if (!get_role('admin')) _validation('error', "Permission Denied!");
		
		$api_id  = post('api_id');
		$api     = $this->model->get("id, name, type, ids, url, key",  $this->tb_api_providers, ['id' => $api_id, 'status' => 1]);
		if (!$api) {
			redirect(cn());
		}
		$data_post = [
			'key'    => $api->key,
			'action' => 'services',
		];
		$response = api_connect($api->url, $data_post);
		if (!empty($response)) {
			$response = json_decode($response);
			usort($response, function($a, $b) {return $a->service - $b->service;});
		}
		$data = array(
			"module"   		        => get_class($this),
			"services" 		        => $response,
			"api_service_id" 		=> $api_service_id,
		);
		$this->load->view('ajax/get_services_from_api', $data);
	}
}