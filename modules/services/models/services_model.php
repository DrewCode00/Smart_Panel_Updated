<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class services_model extends MY_Model {
	public $tb_users;
	public $tb_users_price;
	public $tb_categories;
	public $tb_services;
	public $tb_api_providers;

	public function __construct(){
		$this->tb_categories     = CATEGORIES;
		$this->tb_services       = SERVICES;
		$this->tb_api_providers  = API_PROVIDERS;
		$this->tb_users_price    = USERS_PRICE;
		parent::__construct();
	}

	public function get_services_list(){
		if (get_role("user")) {
			$this->db->where("s.status", "1");
			$this->db->select('s.id, s.desc, s.ids, s.name, s.min, s.max, s.price, api.name as api_name, c.name as category_name, c.id as main_cate_id');
		}else{
			$this->db->select('s.*, api.name as api_name, c.name as category_name, c.id as main_cate_id');

		}
		$this->db->from($this->tb_services." s");
		$this->db->join($this->tb_categories." c", "c.id = s.cate_id", 'left');
		$this->db->join($this->tb_api_providers." api", "s.api_provider_id = api.id", 'left');
		$this->db->order_by("c.sort", 'ASC');
		$this->db->order_by("s.price", 'ASC');
		$this->db->order_by("s.name", 'ASC');
		$query = $this->db->get();
		$result = $query->result();
		$category = array();
		if ($result) {
			foreach ($query->result_array() as $row) {
               $category[$row['category_name']][] = (object)$row;
         	}
		}
		return $category;
	}

	public function get_services_list_old(){
		$data  = array();
		// get categories
		if (get_role("user")) {
			$this->db->where("status", "1");
		}

		$this->db->select("id, ids, name");
		$this->db->from($this->tb_categories);
		$this->db->order_by("sort", 'ASC');

		$query = $this->db->get();
		$categories = $query->result();
		if(!empty($categories)){
			$i = 0;
			foreach ($categories as $key => $row) {
				$i++;
				// get services
				if ($i > 0) {
					if (get_role("supporter") || get_role("admin")) {
						$services = $this->model->fetch("id", $this->tb_services, ['cate_id' => $row->id],'price', 'ASC');
					}else{
						$services = $this->model->fetch("id", $this->tb_services, ["status" => 1, 'cate_id' => $row->id], 'price', 'ASC');
					}

					if(!empty($services)){
						$categories[$key]->is_exists_services = 1;
					}else{
						unset($categories[$key]);	
					}

				}else{
					break;
				}
			}
		}
		return $categories;
	}

	public function get_services_by_search($k){
		$k = trim(htmlspecialchars($k));
		if (get_role("supporter") || get_role("admin")) {
			$this->db->select('s.*, api.name as api_name');
			$this->db->from($this->tb_services." s");
			$this->db->join($this->tb_api_providers." api", "s.api_provider_id = api.id", 'left');

			$this->db->where("(`s`.`id` LIKE '%".$k."%' ESCAPE '!' OR `s`.`api_service_id` LIKE '%".$k."%' ESCAPE '!' OR  `s`.`name` LIKE '%".$k."%' ESCAPE '!')");
			
			$this->db->order_by("s.price", 'ASC');
			$query = $this->db->get();
			$result = $query->result();

		}else{
			$this->db->select('s.*, api.name as api_name');
			$this->db->from($this->tb_services." s");
			$this->db->join($this->tb_api_providers." api", "s.api_provider_id = api.id", 'left');

			$this->db->where("(`s`.`id` LIKE '%".$k."%' ESCAPE '!' OR  `s`.`name` LIKE '%".$k."%' ESCAPE '!')");

			$this->db->where("s.status", 1);
			$this->db->order_by("s.price", 'ASC');
			$query = $this->db->get();
			$result = $query->result();
		}
		return $result;
	}

	// Search Items by keywork and search type
	public function search_items_by_get_method($search){
		$k = trim($search['k']);
		$where_like = "";

		if (get_role("user")) {
			$this->db->where("s.status", 1);
			$this->db->where("s.status", 1);
			$where_like = "(`s`.`id` LIKE '%".$k."%' ESCAPE '!' OR `s`.`api_service_id` LIKE '%".$k."%' ESCAPE '!' OR  `s`.`name` LIKE '%".$k."%' ESCAPE '!')";
		}else{
			$where_like = "(`s`.`id` LIKE '%".$k."%' ESCAPE '!' OR `s`.`api_service_id` LIKE '%".$k."%' ESCAPE '!' OR  `s`.`name` LIKE '%".$k."%' ESCAPE '!' OR  `api`.`name` LIKE '%".$k."%' ESCAPE '!')";
		}

		$this->db->select('s.*, api.name as api_name');
		$this->db->from($this->tb_services." s");
		$this->db->join($this->tb_api_providers." api", "s.api_provider_id = api.id", 'left');
		
		if ($where_like) $this->db->where($where_like);

		$this->db->order_by("s.price", 'ASC');
		$query = $this->db->get();
		$result = $query->result();

		return $result;
	}

	public function get_services_by_cate_id($id){
		if (get_role("supporter") || get_role("admin")) {
			$this->db->select('s.*, api.name as api_name');
			$this->db->from($this->tb_services." s");
			$this->db->join($this->tb_api_providers." api", "s.api_provider_id = api.id", 'left');
			$this->db->where("s.cate_id", $id);
			$this->db->order_by("s.price", 'ASC');
			$query = $this->db->get();
			$result = $query->result();
		}else{
			$this->db->select('s.*, api.name as api_name');
			$this->db->from($this->tb_services." s");
			$this->db->join($this->tb_api_providers." api", "s.api_provider_id = api.id", 'left');
			$this->db->where("s.cate_id", $id);
			$this->db->where("s.status", 1);
			$this->db->order_by("s.price", 'ASC');
			$query = $this->db->get();
			$result = $query->result();
		}
		return $result;
	}

	public function get_active_categories(){
		$data  = array();
		// get categories
		if (get_role("user")) {
			$this->db->where("status", "1");
		}
		$this->db->select("id, ids, name");
		$this->db->from($this->tb_categories);
		$this->db->order_by("sort", 'ASC');
		$query = $this->db->get();
		$categories = $query->result();
		if(!empty($categories)){
			$i = 0;
			foreach ($categories as $key => $row) {
				$i++;
				// get services
				if ($i > 0) {
					$query = $this->db->query("SELECT id FROM $this->tb_services WHERE status = 1 AND cate_id = '{$row->id}'");
					if($query->num_rows() > 0){
						$categories[$key]->is_exists_services = 1;
					}else{
						unset($categories[$key]);	
					}

				}else{
					break;
				}
			}
		}
		return $categories;
	}

	public function get_custom_rates(){
		$custom_rates = $this->model->fetch('uid, service_id, service_price',$this->tb_users_price, ['uid' => session('uid')]);
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
