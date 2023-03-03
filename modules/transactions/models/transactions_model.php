<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class transactions_model extends MY_Model {
	public $tb_users;
	public $tb_categories;
	public $tb_services;
	public $tb_transaction_logs;

	public function __construct(){
		$this->tb_users 		     = USERS;
		$this->tb_categories 		 = CATEGORIES;
		$this->tb_services   		 = SERVICES;
		$this->tb_transaction_logs   = TRANSACTION_LOGS;
		parent::__construct();
	}

	function get_transaction_list($total_rows = false, $status = "", $limit = "", $start = ""){
		$data  = array();
		if (get_role("user")) {
			$this->db->where("tl.uid", session('uid'));
			$this->db->where("tl.status", 1);
		}
		if ($limit != "" && $start >= 0) {
			$this->db->limit($limit, $start);
		}
		$this->db->select("tl.*, u.email");
		$this->db->from($this->tb_transaction_logs." tl");
		$this->db->join($this->tb_users." u", "u.id = tl.uid", 'left');
		$this->db->order_by("tl.id", 'DESC');
		$query = $this->db->get();
		if ($total_rows) {
			$result = $query->num_rows();
			return $result;
		}else{
			$result = $query->result();
			return $result;
		}
		return false;
	}

	function get_transactions_by_search($k){
		$k = trim(htmlspecialchars($k));
		if (get_role("user")) {
			$this->db->select("tl.*, u.email");
			$this->db->from($this->tb_transaction_logs." tl");
			$this->db->join($this->tb_users." u", "u.id = tl.uid", 'left');

			if ($k != "" && strlen($k) >= 2) {
				$this->db->where("(`tl`.`transaction_id` LIKE '%".$k."%' ESCAPE '!' OR `tl`.`type` LIKE '%".$k."%' ESCAPE '!')");
			}
			$this->db->where("u.id", session("uid"));
			$this->db->where("tl.status", 1);
			$this->db->order_by("tl.id", 'DESC');
			$query = $this->db->get();
			$result = $query->result();
		}else{
			$this->db->select("tl.*, u.email");
			$this->db->from($this->tb_transaction_logs." tl");
			$this->db->join($this->tb_users." u", "u.id = tl.uid", 'left');

			if ($k != "" && strlen($k) >= 2) {
				$this->db->where("(`tl`.`transaction_id` LIKE '%".$k."%' ESCAPE '!' OR `tl`.`type` LIKE '%".$k."%' ESCAPE '!' OR `u`.`email` LIKE '%".$k."%' ESCAPE '!')");
			}
			$this->db->order_by("tl.id", 'DESC');
			$query = $this->db->get();
			$result = $query->result();
		}

		return $result;
	}

	function delete_unpaid_payment($day = ""){
		if ($day == "") {
			$day = 7;
		}
		$SQL   = "DELETE FROM ".$this->tb_transaction_logs." WHERE `status` != 1 AND created < NOW() - INTERVAL ".$day." DAY";
		$query = $this->db->query($SQL);
		return $query;
	}

	// Get Count of orders by Search query
	public function get_count_items_by_search($search = []){
		$k = trim($search['k']);
		$where_like = "";
		switch ($search['type']) {
			case 1:
				#User Email
				$where_like = "`u`.`email` LIKE '%".$k."%' ESCAPE '!'";
				break;
			case 2:
				# Transaction ID
				$where_like = "`tl`.`transaction_id` LIKE '%".$k."%' ESCAPE '!'";
				break;
		}

		$this->db->select("tl.*, u.email");
		$this->db->from($this->tb_transaction_logs." tl");
		$this->db->join($this->tb_users." u", "u.id = tl.uid", 'left');

		if ($where_like) $this->db->where($where_like);
		$this->db->order_by("tl.id", 'DESC');
		$query = $this->db->get();
		$number_row = $query->num_rows();
		return $number_row;
	}

	// Search Logs by keywork and search type
	public function search_items_by_get_method($search, $limit = "", $start = ""){
		$k = trim($search['k']);
		$where_like = "";
		switch ($search['type']) {
			case 1:
				#User Email
				$where_like = "`u`.`email` LIKE '%".$k."%' ESCAPE '!'";
				break;
			case 2:
				# Transaction ID
				$where_like = "`tl`.`transaction_id` LIKE '%".$k."%' ESCAPE '!'";
				break;
		}

		$this->db->select("tl.*, u.email");
		$this->db->from($this->tb_transaction_logs." tl");
		$this->db->join($this->tb_users." u", "u.id = tl.uid", 'left');

		if ($where_like) $this->db->where($where_like);
		
		$this->db->order_by("tl.id", 'DESC');
		$this->db->limit($limit, $start);
		$query = $this->db->get();
		$result = $query->result();
		return $result;
	}
}
