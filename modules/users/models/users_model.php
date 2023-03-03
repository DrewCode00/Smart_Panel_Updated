<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class users_model extends MY_Model {
	public $tb_users;
	public $tb_users_price;
	public $tb_categories;
	public $tb_services;

	public function __construct(){
		parent::__construct();
		$this->tb_categories            = CATEGORIES;
		$this->tb_services              = SERVICES;
		$this->tb_users                 = USERS;
		$this->tb_users_price           = USERS_PRICE;
	}

	public function get_users_list($total_rows = false, $status = "", $limit = "", $start = ""){
		$data  = array();
		if ($limit != "" && $start >= 0) {
			$this->db->limit($limit, $start);
		}
		$this->db->select("*");
		$this->db->from($this->tb_users);
		$this->db->order_by("id", 'DESC');
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

	// old V3.2
	public function get_users_by_search($k){
		$k = trim(htmlspecialchars($k));
		$this->db->select('*');
		$this->db->from($this->tb_users);
		if ($k != "" && strlen($k) >= 2) {
			$this->db->where("(`first_name` LIKE '%".$k."%' ESCAPE '!' OR `last_name` LIKE '%".$k."%' ESCAPE '!' OR `email` LIKE '%".$k."%' ESCAPE '!' OR `desc` LIKE '%".$k."%' ESCAPE '!')");
		}
		$this->db->order_by('id', 'DESC');
		$query = $this->db->get();
		$result = $query->result();
		return $result;
	}
	
	// Get Count of orders by Search query
	public function get_count_users_by_search($search = []){
		$k = trim($search['k']);

		$where_like = "(`first_name` LIKE '%".$k."%' ESCAPE '!' OR `last_name` LIKE '%".$k."%' ESCAPE '!' OR `email` LIKE '%".$k."%' ESCAPE '!')";

		$this->db->select('*');
		$this->db->from($this->tb_users);
		if ($where_like) $this->db->where($where_like);
		$this->db->order_by('id', 'DESC');
		$query = $this->db->get();
		$number_row = $query->num_rows();
		return $number_row;
	}

	// Search Logs by keywork and search type
	public function search_logs_by_get_method($search, $limit = "", $start = ""){
		$k = trim($search['k']);
		$where_like = "(`first_name` LIKE '%".$k."%' ESCAPE '!' OR `last_name` LIKE '%".$k."%' ESCAPE '!' OR `email` LIKE '%".$k."%' ESCAPE '!')";

		$this->db->select('*');
		$this->db->from($this->tb_users);
		if ($where_like) $this->db->where($where_like);
		$this->db->order_by('id', 'DESC');
		$this->db->limit($limit, $start);
		$query = $this->db->get();
		$result = $query->result();
		return $result;
	}

	// Get custom rate list by user
	public function get_current_customrate_by($uid){
		$this->db->select('up.*, s.name, s.original_price, s.price');
		$this->db->from($this->tb_services." s");
		$this->db->join($this->tb_users_price." up", "s.id = up.service_id", 'left');
		$this->db->where('up.uid', $uid);
		$this->db->order_by('up.service_id', 'ASC');
		$this->db->order_by('up.id', 'ASC');
		$query = $this->db->get();
		$result = $query->result();
		return $result;
	}

	// Get all user price
	public function get_custom_rates(){
		$custom_rates = $this->model->fetch('uid, service_id, service_price', $this->tb_users_price, ['uid' => session('uid')]);
		$exist_db_custom_rates = [];
		if (!empty($custom_rates)) {
			foreach ($custom_rates as $key => $row) {
				$exist_db_custom_rates[$row->service_id]['uid']           = $row->uid;
				$exist_db_custom_rates[$row->service_id]['service_id']    = $row->service_id;
				$exist_db_custom_rates[$row->service_id]['service_price'] = $row->service_price;
			}
		}
		return $exist_db_custom_rates;
	}
	
}
