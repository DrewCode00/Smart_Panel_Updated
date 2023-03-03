<?php
defined('BASEPATH') OR exit('No direct script access allowed');
 
class transactions extends MX_Controller {
	public $module;
	public $tb_users;
	public $tb_categories;
	public $tb_services;
	public $tb_transaction_logs;
	public $columns;

	public function __construct(){
		parent::__construct();
		$this->load->model(get_class($this).'_model', 'model');
		//Config Module
		$this->module                = get_class($this);
		$this->tb_users              = USERS;
		$this->tb_categories         = CATEGORIES;
		$this->tb_services           = SERVICES;
		$this->tb_transaction_logs   = TRANSACTION_LOGS;
		$this->columns = array(
			"uid"              => lang('User'),
			"transaction_id"   => lang('Transaction_ID'),
			"type"             => lang('Payment_method'),
			"amount"           => lang('Amount_includes_fee'),
			"txn_fee"          => 'Transaction fee',
			"note"             => 'Note',
			"created"          => lang('Created'),
			"status"           => lang('Status'),
		);

		if (!get_role("admin")) {
			$this->columns = array(
				"type"             => lang('Payment_method'),
				"amount"           => lang('Amount_includes_fee'),
				"txn_fee"          => 'Transaction fee',
				"created"          => lang('Created'),
				"status"           => lang('Status'),
			);
		}
	}

	public function index(){
		// Delete all Unpaid Payment over 2 day
		$this->model->delete_unpaid_payment(2);

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
			'total_rows'         => $this->model->get_transaction_list(true),
			'per_page'           => $limit_per_page,
			'use_page_numbers'   => true,
			'prev_link'          => '<i class="fe fe-chevron-left"></i>',
			'first_link'         => '<i class="fe fe-chevrons-left"></i>',
			'next_link'          => '<i class="fe fe-chevron-right"></i>',
			'last_link'          => '<i class="fe fe-chevrons-right"></i>',
		);
		$this->pagination->initialize($config);
		$links = $this->pagination->create_links();

		$transactions = $this->model->get_transaction_list(false, "all", $limit_per_page, $page * $limit_per_page);
		$data = array(
			"module"         => get_class($this),
			"columns"        => $this->columns,
			"transactions"   => $transactions,
			"links"          => $links,
		);

		$this->template->build('index', $data);
	}
	
	public function update($ids = ""){
		if (!get_role('admin')) {
			redirect(cn());
		}
		$transaction     = $this->model->get("*", $this->tb_transaction_logs, ['ids' => $ids]);
		$data = array(
			"module"   			=> get_class($this),
			"transaction" 	    => $transaction,
		);
		$this->load->view('update', $data);
	}

	public function ajax_update($ids = ""){
		if (!get_role('admin')) {
			redirect(cn());
		}
		$uid 		        = (int)post("uid");
		$ids	            = post("ids");
		$note	            = post("note");
		$transaction_id	    = post("transaction_id");
		$payment_method	    = post("payment_method");
		$status			    = (int)post("status");

		if($uid == ""){
			ms(array(
				"status"  => "error",
				"message" => 'User email is required'
			));
		}
		
		if($transaction_id == ""){
			ms(array(
				"status"  => "error",
				"message" => 'Transaction id is required'
			));
		}	

		if($payment_method == ""){
			ms(array(
				"status"  => "error",
				"message" => 'Payment method is required'
			));
		}

		$check_item = $this->model->get("*", $this->tb_transaction_logs, ['uid' => $uid, 'transaction_id' => $transaction_id, 'type' => $payment_method]);
		if(!empty($check_item)){

			$data = array(
				'note'   => $note,
				'status' => $status
			);
			$this->db->update($this->tb_transaction_logs, $data, ['uid' => $check_item->uid, 'transaction_id' => $check_item->transaction_id, 'type' => $check_item->type]);
			if ($status == 1 && $check_item->status == 0) {
				$user_balance = $this->model->get("balance", $this->tb_users, ['id' => $check_item->uid])->balance;
				$new_balance = $user_balance + ($check_item->amount - $check_item->txn_fee);
				$this->db->update($this->tb_users, ["balance" => $new_balance], ["id" => $check_item->uid]);
			}
			if ($this->db->affected_rows() > 0) {
				ms(array(
					"status"  => "success",
					"message" => lang("Update_successfully")
				));
			}
			

		}else{
			ms(array(
				"status"  => "error",
				"message" => 'Transaction does not exists'
			));
		}
		
	}
	
	public function ajax_delete_item($ids = ""){
		$this->model->delete($this->tb_transaction_logs, $ids, false);
	}

	//Search
	public function search(){
		if (!get_role('admin')) {
			redirect(cn($this->module));
		}
		$k           = get('query');
		$k           = htmlspecialchars($k);
		$search_type = (int)get('search_type');
		$data_search = ['k' => $k, 'type' => $search_type];
		$page        = (int)get("p");
		$page        = ($page > 0) ? ($page - 1) : 0;
		$limit_per_page = get_option("default_limit_per_page", 10);
		$query = ['query' => $k, 'search_type' => $search_type];
		$query_string = "";
		if(!empty($query)){
			$query_string = "?".http_build_query($query);
		}
		$config = array(
			'base_url'           => cn(get_class($this)."/search".$query_string),
			'total_rows'         => $this->model->get_count_items_by_search($data_search),
			'per_page'           => $limit_per_page,
			'use_page_numbers'   => true,
			'prev_link'          => '<i class="fe fe-chevron-left"></i>',
			'first_link'         => '<i class="fe fe-chevrons-left"></i>',
			'next_link'          => '<i class="fe fe-chevron-right"></i>',
			'last_link'          => '<i class="fe fe-chevrons-right"></i>',
		);
		$this->pagination->initialize($config);
		$links = $this->pagination->create_links();
		$transactions = $this->model->search_items_by_get_method($data_search, $limit_per_page, $page * $limit_per_page);
		$data = array(
			"module"         => get_class($this),
			"columns"        => $this->columns,
			"transactions"   => $transactions,
			"links"          => $links,
		);

		$this->template->build('index', $data);
	}

}