<?php
defined('BASEPATH') OR exit('No direct script access allowed');
 
class users extends MX_Controller {
	public $tb_users;
	public $tb_users_price;
	public $tb_user_mail_logs;
	public $tb_payments;
	public $tb_categories;
	public $tb_transaction_logs;
	public $tb_services;
	public $columns;
	public $module_name;
	public $module;
	public $module_icon;

	public function __construct(){
		parent::__construct();
		$this->load->model(get_class($this).'_model', 'model');
		//Config Module
		$this->tb_users                 = USERS;
		$this->tb_users_price           = USERS_PRICE;
		$this->tb_payments              = PAYMENTS_METHOD;
		$this->tb_categories            = CATEGORIES;
		$this->tb_services              = SERVICES;
		$this->tb_transaction_logs      = TRANSACTION_LOGS;
		$this->tb_user_mail_logs        = USER_MAIL_LOGS;
		$this->module_name              = 'Users';
		$this->module                   = get_class($this);
		$this->module_icon              = "fa ft-users";
		$this->columns = array(
			"name"           => lang("User_profile"),
			"balance"        => lang('Funds'),
			"custom_rate"    => lang("custom_rate"),
			"desc"           => lang('Description'),
			"created"        => lang("Created"),
			"status"         => lang('Status'),
		);
	}

	public function index(){

		$page        = (int)get("p");
		$page        = ($page > 0) ? ($page - 1) : 0;
		$limit_per_page = get_option("default_limit_per_page", 10);
		$query = array();
		$query_string = "";
		if(!empty($query)){
			$query_string = "?".http_build_query($query);
		}
		$config = array(
			'base_url'           => cn(get_class($this).$query_string),
			'total_rows'         => $this->model->get_users_list(true),
			'per_page'           => $limit_per_page,
			'use_page_numbers'   => true,
			'prev_link'          => '<i class="fe fe-chevron-left"></i>',
			'first_link'         => '<i class="fe fe-chevrons-left"></i>',
			'next_link'          => '<i class="fe fe-chevron-right"></i>',
			'last_link'          => '<i class="fe fe-chevrons-right"></i>',
		);
		$this->pagination->initialize($config);
		$links = $this->pagination->create_links();

		$users = $this->model->get_users_list(false, "all", $limit_per_page, $page * $limit_per_page);
		$data = array(
			"module"       => get_class($this),
			"columns"      => $this->columns,
			"users"        => $users,
			"links"        => $links,
		);

		$this->template->build('index', $data);
	}

	public function update($ids = ""){
		$user            = $this->model->get("*", $this->tb_users, "ids = '{$ids}' ");
		$payments_defaut = $this->model->fetch('id, type, name, status', $this->tb_payments, ['status' => 1]);

		$settings = json_decode($user->settings);
        if (isset($settings->limit_payments)) {
          $limit_payments = (array)$settings->limit_payments;
        }else{
        	foreach ($payments_defaut as $key => $payment) {
        		$limit_payments[$payment->type] = 1;
        	}
        }

		$data = array(
			"module"                => get_class($this),
			"user" 		            => $user,
			"payments_defaut" 		=> $payments_defaut,
			"limit_payments" 		=> $limit_payments,
		);
		$this->template->build('update', $data);
	}

	public function mail($ids = ""){
		$user    = $this->model->get("ids, first_name, last_name, email", $this->tb_users, "ids = '{$ids}' ");

		$data = array(
			"module"    => get_class($this),
			"user" 		=> $user,
		);
		$this->load->view('mail_to_user', $data);
	}

	public function add_funds_manual($ids = ""){
		$user    = $this->model->get("ids, id, first_name, last_name, email", $this->tb_users, "ids = '{$ids}' ");
		$payments_defaut = $this->model->fetch('type, name', $this->tb_payments, ['status' => 1]);
		$data = array(
			"module"                => get_class($this),
			"user" 		            => $user,
			"payments_defaut" 		=> $payments_defaut,
		);
		$this->load->view('add_funds_manual', $data);
	}

	public function ajax_add_funds_manualy($ids = ""){
		_is_ajax($this->module);
		$funds     			= (double)post('funds');
		$payment_method     = post('payment_method');
		$transaction_id      = post('transaction_id');
		$txt_note	        = post("txt_note");
		$txt_fee	        = post("txt_fee");

		$checkUser = $this->model->get('id, ids, email, balance, spent', $this->tb_users, "`ids` = '{$ids}'");
		if ($ids == "" || empty($checkUser)) {
			ms(array(
				'status'  => 'error',
				'message' => lang("the_account_does_not_exists"),
			));
		}
		
		if ($payment_method == '') {
			ms(array(
				'status'  => 'error',
				'message' => 'Please choose payment method!',
			));
		}

		if ($funds == '') {
			ms(array(
				'status'  => 'error',
				'message' => 'Funds is required',
			));
		}	

		if(!is_double($funds)){
			ms(array(
				'status'  => 'error',
				'message' => lang('the_input_value_was_not_a_correct_number'),
			));
		}

		$new_balance = $checkUser->balance + $funds;
		$total_spent_before = $this->model->sum_results('amount', $this->tb_transaction_logs, ['status' => 1, 'uid' => $checkUser->id] );
		$total_spent = (double)round($total_spent_before + $funds, 4);
		

		$data = array(
			"balance" => $new_balance,
			"spent"   => $total_spent,
		);

		if ($transaction_id == "") {
			$transaction_id = 'empty';
		}
		
		$data_transaction = array(
			"ids" 				=> ids(),
			"uid" 				=> $checkUser->id,
			"type" 				=> $payment_method,
			"transaction_id" 	=> $transaction_id,
			"txn_fee" 	        => $txt_fee,
			"note" 	            => $txt_note,
			"amount" 	        => $funds,
			"created" 			=> NOW,
		);

		$this->db->insert($this->tb_transaction_logs, $data_transaction);
		if($this->db->update( $this->tb_users, $data , ['ids' => $ids] )){
			ms(array(
				'status'  => 'success',
				'message' => lang("Update_successfully"),
			));
		}
	}

	public function ajax_update($ids = ""){
		$first_name         = post('first_name');
		$last_name          = post('last_name');
		$email              = post('email');
		$password           = post('password');
		$re_password        = post('re_password');
		$status             = (int)post('status');
		$role               = post('role');
		$timezone           = post('timezone');
		$desc               = post('desc');
		$settings           = post('settings');
		if($first_name == '' || $last_name == ''){
			ms(array(
				'status'  => 'error',
				'message' => lang("please_fill_in_the_required_fields"),
			));
		}

		$data = array(
			"first_name"              => $first_name,
			"last_name"               => $last_name,
			"role"                    => $role,
			"status"                  => $status,
			"timezone"                => $timezone,
			"desc"        	          => $desc,
			"changed"                 => NOW,
			"settings"                => json_encode($settings),
			"reset_key"               => ids(),
		);
		
		if($password != ''|| $ids == ''){
			if($password == ''){
				ms(array(
					'status'  => 'error',
					'message' => lang("Password_is_required"),
				));
			}

			if(strlen($password) < 6){
				ms(array(
					'status'  => 'error',
					'message' => lang("Password_must_be_at_least_6_characters_long"),
				));
			}

			if($re_password!= $password){
				ms(array(
					'status'  => 'error',
					'message' => lang("Password_does_not_match_the_confirm_password"),
				));
			}
			$data['password'] = $this->model->app_password_hash($password);
		}
		
		if($ids != ''){
			$checkUser = $this->model->get('id, ids, email', $this->tb_users, "`ids` = '{$ids}'");

			if(empty($checkUser)){
				ms(array(
					'status'  => 'error',
					'message' => lang("There_was_an_error_processing_your_request_Please_try_again_later"),
				));
			}

			// check email
			$checkUserEmail = $this->model->get('email, ids', $this->tb_users,"email='{$email}' AND `ids` != '{$ids}'");
			if(!empty($checkUserEmail)){
				ms(array(
					'status'  => 'error',
					'message' => lang('An_account_for_the_specified_email_address_already_exists_Try_another_email_address'),
				));
			}
			if($this->db->update( $this->tb_users, $data ,"ids = '{$ids}'")){
				ms(array(
					'status'  => 'success',
					'message' => lang("Update_successfully"),
				));
			}
		}else{

			if($email == ''){
				ms(array(
					'status'  => 'error',
					'message' => lang("email_is_required"),
				));
			}

			if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
		      	ms(array(
					'status'  => 'error',
					'message' => lang("invalid_email_format"),
				));
		    }

		    // check email
			$checkUserEmail = $this->model->get('email, ids', $this->tb_users,"email='{$email}'");
			if(!empty($checkUserEmail)){
				ms(array(
					'status'  => 'error',
					'message' => lang('An_account_for_the_specified_email_address_already_exists_Try_another_email_address'),
				));
			}
			$data['ids']         = ids();
			$data['created']     = NOW;
			$data['email']       = $email;
			$data['api_key']     = create_random_string_key(32);

			if($this->db->insert( $this->tb_users,$data)){
				ms(array(
					'status'  => 'success',
					'message' => lang("Update_successfully"),
				));
			}
		}
	}
	
	public function ajax_send_email(){
		_is_ajax(get_class($this));
		$user_email       = post("email_to");
		$subject          = post("subject");
		$email_content    = post("email_content");

		if($subject == ''){
			ms(array(
				'status'  => 'error',
				'message' => lang("subject_is_required"),
			));
		}

		if($email_content == ''){
			ms(array(
				'status'  => 'error',
				'message' => lang("message_is_required"),
			));
		}

		$user = $this->model->get("id, email", $this->tb_users, "email = '{$user_email}'");
		if (!empty($user)) {
			$subject = get_option("website_name", "") ." - ".$subject;
			$check_email_issue = $this->model->send_email($subject, $email_content, $user->id, false);
			if ($check_email_issue) {
				ms(array(
					"status"  => "error",
					"message" => $check_email_issue,
				));
			}

			if ($this->db->table_exists($this->tb_user_mail_logs)) {
				$data = array(
					'ids'                 => ids(),
					'uid'                 => session('uid'),
					'received_uid'        => $user->id,
					'subject'             => post("subject"),
					'content'             => htmlspecialchars(@$email_content, ENT_QUOTES),
					'created'             => NOW,
					'changed'             => NOW,
				);
				$this->db->insert($this->tb_user_mail_logs, $data);
			}

			ms(array(
				"status"  => "success",
				"message" => lang("your_email_has_been_successfully_sent_to_user"),
			));
		}else{
			ms(array(
				"status"  => "error",
				"message" => lang("the_account_does_not_exists"),
			));
		}
	}

	public function ajax_update_more_infors($ids = ''){
		_is_ajax(get_class($this));
		$website            = post('website');
		$phone              = post('phone');
		$skype_id           = post('skype_id');
		$what_asap          = post('what_asap');
		$address            = post('address');

		$more_information = array(
			"website"         => $website,
			"phone"        	  => $phone,
			"what_asap"       => $what_asap,
			"skype_id"        => $skype_id,
			"address"         => $address,
		);

		$data = array(
			"more_information"        => json_encode($more_information),
			"changed"                 => NOW,
		);

		if($ids != ''){
			$checkUser = $this->model->get('id,ids,email', $this->tb_users, "`ids` = '{$ids}'");

			if(empty($checkUser)){
				ms(array(
					'status'  => 'error',
					'message' => lang("There_was_an_error_processing_your_request_Please_try_again_later"),
				));
			}

			if($this->db->update($this->tb_users, $data, "ids ='{$ids}'")){
				ms(array(
					'status'  => 'success',
					'message' => lang("Update_successfully"),
				));
			}
		}else{
			ms(array(
				'status'  => 'error',
				'message' => lang("There_was_an_error_processing_your_request_Please_try_again_later"),
			));
		}
	}

	public function ajax_update_fund($ids = ""){
		_is_ajax(get_class($this));
		$funds     = post('funds');
		$checkUser = $this->model->get('id, ids, email, balance', $this->tb_users, "`ids` = '{$ids}'");
		if ($ids == "" || empty($checkUser)) {
			ms(array(
				'status'  => 'error',
				'message' => lang("the_account_does_not_exists"),
			));
		}
		
		if ($funds == '') {
			ms(array(
				'status'  => 'error',
				'message' => 'Incorrect funds',
			));
		}

		if(!is_numeric($funds) && $funds != 0){
			ms(array(
				'status'  => 'error',
				'message' => lang('the_input_value_was_not_a_correct_number'),
			));
		}

		$data = array(
			"balance" => $funds,
		);

		if($this->db->update( $this->tb_users, $data ,"ids = '{$ids}'")){
			ms(array(
				'status'  => 'success',
				'message' => lang("Update_successfully"),
			));
		}
	}

	//Search
	public function search(){
		$k           = get('query');
		$k           = htmlspecialchars($k);
		$search_type = (int)get('search_type');
		$data_search = ['k' => $k, 'type' => $search_type];
		$page        = (int)get("p");
		$page        = ($page > 0) ? ($page - 1) : 0;
		$limit_per_page = get_option("default_limit_per_page", 10);
		$query = ['query' => $k];
		$query_string = "";
		if(!empty($query)){
			$query_string = "?".http_build_query($query);
		}
		$config = array(
			'base_url'           => cn(get_class($this)."/search".$query_string),
			'total_rows'         => $this->model->get_count_users_by_search($data_search),
			'per_page'           => $limit_per_page,
			'use_page_numbers'   => true,
			'prev_link'          => '<i class="fe fe-chevron-left"></i>',
			'first_link'         => '<i class="fe fe-chevrons-left"></i>',
			'next_link'          => '<i class="fe fe-chevron-right"></i>',
			'last_link'          => '<i class="fe fe-chevrons-right"></i>',
		);
		$this->pagination->initialize($config);
		$links = $this->pagination->create_links();
		$users = $this->model->search_logs_by_get_method($data_search, $limit_per_page, $page * $limit_per_page);
		$data = array(
			"module"       => get_class($this),
			"columns"      => $this->columns,
			"users"        => $users,
			"links"        => $links,
		);

		$this->template->build('index', $data);
	}

	public function ajax_delete_item($ids = ""){
		$this->model->delete($this->tb_users, $ids, false);
	}

	// Change Item Status
	public function ajax_toggle_item_status($id = ""){
		_is_ajax($this->module);
		$status  = post('status');
		$item  = $this->model->get("id", $this->tb_users, ['id' => $id]);
		if ( $item ) {
			$this->db->update($this->tb_users, ['status' => (int)$status], ['id' => $id]);
			_validation('success', lang("Update_successfully"));
		}
	}

	public function view_user($ids = ""){
		$user = $this->model->get("id, ids", $this->tb_users, ['ids' => $ids]);
		if (empty($user)) {
			ms(array(
				'status'  => 'error',
				'message' => lang("There_was_an_error_processing_your_request_Please_try_again_later"),
			));
		}
		set_session('uid_tmp', $user->id);
		unset_session("user_current_info");
		if (session('uid_tmp')) {
			ms(array(
				'status'  => 'success',
				'message' => lang("processing_"),
			));
		}
	}

	public function export($type = ""){

		switch ($type) {

			case 'excel':
				$users = $this->model->fetch('*', $this->tb_users, ['status' => 1]);
				if (!empty($users )) {
					$columns = ['id' ,'first_name', 'last_name', 'email', 'timezone', 'balance', 'status', 'created'];
					$filename = 'List-Users-'.date("d-m-Y", strtotime(NOW)).".xlsx";
					$this->load->library('phpspreadsheet_lib');
					$phpexel = new Phpspreadsheet_lib();
					$phpexel->export_excel($columns, $users, $filename);
				}

				break;

			case 'csv':
				$users = $this->model->fetch('*', $this->tb_users, ['status' => 1]);
				if (!empty($users )) {
					$columns = ['id' ,'first_name', 'last_name', 'email', 'timezone', 'balance', 'status', 'created'];
					$filename = 'List-Users-'.date("d-m-Y", strtotime(NOW)).".csv";
					$this->load->library('phpspreadsheet_lib');
					$phpexel = new Phpspreadsheet_lib();
					$phpexel->export_csv($columns, $users, $filename);
				}

				break;
			
			default:
				$filename = 'List-Users['.date("d-m-Y", strtotime(NOW))."].csv";
				export_csv($filename, $this->tb_users);
				break;
		}
	}

	// ajax_modal_custom_rates
	public function ajax_modal_custom_rates($uid = ""){
		_is_ajax($this->module);
		$uid = (int)$uid;
		$user = $this->model->get('id, ids, email', $this->tb_users, ['status' => 1, 'id' => $uid]);
		if ($user) {
			$user_prices = $this->model->get_current_customrate_by($uid);
			$services    = $this->model->fetch('id, price, name, original_price', $this->tb_services, ['status' => 1]);
			$data_modal = [
				'module'      => $this->module,
				'user'        => $user,
				'user_prices' => $user_prices,
				'services'    => $services,
			];
			$this->load->view('modal_custom_rate', $data_modal);
		}else{
			echo 	'<div class="modal-dialog">
					    <div class="modal-content">
						    <div class="alert  alert-dismissible">
							  <button type="button" class="close" data-dismiss="modal"></button>
							  <h4>Warning!</h4>
							  <p>
							   User is inactive mode, please active this user before adding custom rate!
							  </p>
							  <div class="btn-list">
							    <button class="btn btn-warning btn-sm" type="button" data-dismiss="modal">Okay</button>
							  </div>
							</div>
					    </div>
				 	</div>';
		}
	}

	public function ajax_save_custom_rates($uid = ""){
		_is_ajax($this->module);
		$user = $this->model->get('id, ids, email', $this->tb_users, ['status' => 1, 'id' => $uid]);

		if (!$user) {
			_validation('error', 'There was an error processing your request. Please try again later');
		}
		$custom_rates = post('customRates');
		unset($custom_rates['__serviceID__']);
		if (!empty($custom_rates)) {
			$exist_db_custom_rates = [];
			$exist_items = $this->model->fetch('*', $this->tb_users_price, ['uid' => $user->id]);

			// update the current Items
			if ($exist_items) {
				foreach ($exist_items as $key => $row) {
					$exist_db_custom_rates[$row->service_id]['uid']           = $row->uid;
					$exist_db_custom_rates[$row->service_id]['service_id']    = $row->service_id;
					$exist_db_custom_rates[$row->service_id]['service_price'] = $row->service_price;
					foreach ($custom_rates as $key => $custom_rate) {
						if ($custom_rate['service_id'] == $row->service_id && $row->uid == $custom_rate['uid']) {
							$this->db->update($this->tb_users_price, ['service_price' => $custom_rate['service_price']], ['id' => $row->id ]);
						}
					}
				}	
			}
			/*----------  Compare Custom rates New and exists on Database  ----------*/
			if (!empty($exist_db_custom_rates)) {
				// Get new
				$new_custom_rates = array_udiff($custom_rates, $exist_db_custom_rates,
				  	function ($obj_a, $obj_b) {
					    return $obj_a['service_id'] - $obj_b['service_id'];
				  	}
				);
				// Get Disbale Custom rates
				$disable_custom_rates = array_udiff($exist_db_custom_rates, $custom_rates,
				  	function ($obj_a, $obj_b) {
					    return $obj_a['service_id'] - $obj_b['service_id'];
				  	}
				);

			}else{
				$new_custom_rates = $custom_rates;
			}

			/*----------  Insert New  ----------*/
			if (!empty($new_custom_rates)) {
				$this->db->insert_batch($this->tb_users_price, $new_custom_rates);
			}

			/*----------  Delete non custom rate  ----------*/
			if (!empty($disable_custom_rates)) {
				foreach ($disable_custom_rates as $key => $row) {
					$this->db->delete($this->tb_users_price, [ 'uid' => $row['uid'] , 'service_id' => $row['service_id'] ]);
				}
			}
		}else{
			$this->db->delete($this->tb_users_price, ['uid' => $user->id]);
		}
		_validation( 'success', lang("Update_successfully") );
	}
}