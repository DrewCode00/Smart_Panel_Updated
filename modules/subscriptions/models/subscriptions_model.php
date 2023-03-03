<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class subscriptions_model extends MY_Model {
	public $tb_users;
	public $tb_order;
	public $tb_categories;
	public $tb_services;
	public $tb_api_providers;
	public function __construct(){
		$this->tb_categories          = CATEGORIES;
		$this->tb_order               = ORDER;
		$this->tb_users               = USERS;
		$this->tb_services            = SERVICES;
		$this->tb_api_providers   	  = API_PROVIDERS;
		parent::__construct();
	}
	function get_order_logs_list($total_rows = false, $status = "", $limit = "", $start = ""){
		$data  = array();
		if (get_role("user")) {
			$this->db->where("o.uid", session("uid"));
		}

		if ($limit != "" && $start >= 0) {
			$this->db->limit($limit, $start);
		}

		$this->db->select('o.*, u.email as user_email, s.name as service_name, api.name as api_name');
		$this->db->from($this->tb_order." o");
		$this->db->join($this->tb_users." u", "u.id = o.uid", 'left');
		$this->db->join($this->tb_services." s", "s.id = o.service_id", 'left');
		$this->db->join($this->tb_api_providers." api", "api.id = o.api_provider_id", 'left');
		if($status != "all" && !empty($status)){
			$this->db->where("o.sub_status", $status);
		}
		$this->db->where("o.service_type", "subscriptions");
		$this->db->order_by("o.id", 'DESC');

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
	function get_orders_logs_by_search($k){
		$k = trim(htmlspecialchars($k));
		if (get_role("user")) {
			$this->db->select('o.*, u.email as user_email, s.name as service_name');
			$this->db->from($this->tb_order." o");
			$this->db->join($this->tb_users." u", "u.id = o.uid", 'left');
			$this->db->join($this->tb_services." s", "s.id = o.service_id", 'left');

			if ($k != "" && strlen($k) >= 2) {
				$this->db->where("(`o`.`id` LIKE '%".$k."%' ESCAPE '!' OR `o`.`username` LIKE '%".$k."%' ESCAPE '!' OR `o`.`sub_status` LIKE '%".$k."%' ESCAPE '!' OR  `s`.`name` LIKE '%".$k."%' ESCAPE '!')");
			}
			$this->db->where("o.service_type ", "subscriptions");
			$this->db->where("u.id", session("uid"));
			$query = $this->db->get();
			$result = $query->result();

		}else{
			$this->db->select('o.*, u.email as user_email, s.name as service_name, api.name as api_name');
			$this->db->from($this->tb_order." o");
			$this->db->join($this->tb_users." u", "u.id = o.uid", 'left');
			$this->db->join($this->tb_services." s", "s.id = o.service_id", 'left');
			$this->db->join($this->tb_api_providers." api", "api.id = o.api_provider_id", 'left');

			if ($k != "" && strlen($k) >= 2) {
				$this->db->where("(`o`.`api_order_id` LIKE '%".$k."%' ESCAPE '!' OR `o`.`username` LIKE '%".$k."%' ESCAPE '!' OR `o`.`id` LIKE '%".$k."%' ESCAPE '!' OR `o`.`sub_status` LIKE '%".$k."%' ESCAPE '!' OR  `u`.`email` LIKE '%".$k."%' ESCAPE '!'OR  `s`.`name` LIKE '%".$k."%' ESCAPE '!')");
			}
			$this->db->where("o.service_type ", "subscriptions");
			$query = $this->db->get();
			$result = $query->result();
		}
		return $result;
	}
	
	// Get Count of orders by Search query
	public function get_count_orders_by_search($search = []){
		$k = trim($search['k']);
		$where_like = "";
		if (get_role("user")) {
			$this->db->where("o.uid", session("uid"));
			$where_like = "(`o`.`id` LIKE '%".$k."%' ESCAPE '!' OR `o`.`username` LIKE '%".$k."%' ESCAPE '!')";
		}else{
			switch ($search['type']) {
				case 1:
					#order id
					$where_like = "`o`.`id` LIKE '%".$k."%' ESCAPE '!'";
					break;
				case 2:
					# API order id
					$where_like = "`o`.`api_order_id` LIKE '%".$k."%' ESCAPE '!'";
					break;

				case 3:
					# Username
					$where_like = "`o`.`username` LIKE '%".$k."%' ESCAPE '!'";
					break;

				case 4:
					# User Email
					$where_like = "`u`.`email` LIKE '%".$k."%' ESCAPE '!'";
					break;
			}
		}

		$this->db->select('o.id');
		$this->db->from($this->tb_order." o");
		$this->db->join($this->tb_users." u", "u.id = o.uid", 'left');
		if ($where_like) $this->db->where($where_like);

		$this->db->where("o.service_type ", "subscriptions");
		$query = $this->db->get();
		$number_row = $query->num_rows();
		return $number_row;
	}

	// Search Logs by keywork and search type
	public function search_logs_by_get_method($search, $limit = "", $start = ""){
		$k = trim($search['k']);
		$where_like = "";
		if (get_role("user")) {
			$this->db->select('o.*, u.email as user_email, s.name as service_name');
			$this->db->from($this->tb_order." o");
			$this->db->join($this->tb_users." u", "u.id = o.uid", 'left');
			$this->db->join($this->tb_services." s", "s.id = o.service_id", 'left');

			$this->db->where("(`o`.`id` LIKE '%".$k."%' ESCAPE '!' OR `o`.`username` LIKE '%".$k."%' ESCAPE '!')");

			$this->db->where("o.service_type ", "subscriptions");
			$this->db->where("o.uid", session("uid"));
			$this->db->order_by("o.id", 'DESC');
			$this->db->limit($limit, $start);
			$query = $this->db->get();
			$result = $query->result();

		}else{
			switch ($search['type']) {
				case 1:
					#order id
					$where_like = "`o`.`id` LIKE '%".$k."%' ESCAPE '!'";
					break;
				case 2:
					# API order id
					$where_like = "`o`.`api_order_id` LIKE '%".$k."%' ESCAPE '!'";
					break;

				case 3:
					# Username
					$where_like = "`o`.`username` LIKE '%".$k."%' ESCAPE '!'";
					break;

				case 4:
					# User Email
					$where_like = "`u`.`email` LIKE '%".$k."%' ESCAPE '!'";
					break;
			}
			$this->db->select('o.*, u.email as user_email, s.name as service_name, api.name as api_name');
			$this->db->from($this->tb_order." o");
			$this->db->join($this->tb_users." u", "u.id = o.uid", 'left');
			$this->db->join($this->tb_services." s", "s.id = o.service_id", 'left');
			$this->db->join($this->tb_api_providers." api", "api.id = o.api_provider_id", 'left');

			if ($where_like) $this->db->where($where_like);
			$this->db->where("o.service_type ", "subscriptions");
			$this->db->order_by("o.id", 'DESC');
			$this->db->limit($limit, $start);
			$query = $this->db->get();
			$result = $query->result();
		}
		return $result;
	}
}

