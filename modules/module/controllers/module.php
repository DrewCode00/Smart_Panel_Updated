<?php
defined('BASEPATH') OR exit('No direct script access allowed');
 
class module extends MX_Controller {
	public $tb_users;
	public $tb_purchase;
	public $module_name;
	public $config;
	public $secret_key;
	public $publish_key;
	public $module_icon;

	public function __construct(){
		parent::__construct();
		$this->load->model('model', 'model');
		$this->config = get_json_content( APPPATH. './hooks/config.json');
		$this->secret_key = $this->config->secret_key;
		$this->publish_key = $this->config->publish_key;
		$this->tb_purchase = PURCHASE;
	}

	public function index(){
		$pc_item = $this->model->get("purchase_code", $this->tb_purchase, ['id' => 1]);
		if (empty($pc_item)) {
			redirect(cn());
		}
		$scripts = "";
		if (!preg_match("/^([a-f0-9]{8})-(([a-f0-9]{4})-){3}([a-f0-9]{12})$/i", $pc_item->purchase_code)) {
			redirect(cn());
		}
		$result = get_json_content(base64_decode($this->publish_key).'/script_list', ['purchase_code' => urlencode($pc_item->purchase_code)]);
		if (!empty($result)) {
			if (!empty($result->scripts)) {
				$scripts = $result->scripts;
			}
		}
		if (empty($scripts)) {
			redirect(cn());
		}
		$purchase_code_lists = $this->model->fetch("*", $this->tb_purchase);
		$data = array(
			"module"                    => get_class($this),
			"scripts"                   => $scripts,
			"purchase_code_lists"       => $purchase_code_lists
		);
		$this->template->build('index', $data);
	}

	public function update(){
		$purchase_code = post("purchase_code");
		$purchase_code = trim($purchase_code);

		if ($purchase_code  == "") {
			ms(array(
				"status"  => "error",
				"message" => "Purchase code is required",
			));
		}
		$this->db->update($this->tb_purchase, ["purchase_code" => $purchase_code], ["pid" => "23595718"]);
		delete_cookie("purchase_code_status");
		ms(array(
			"status" => "success",
			"message" => "Update Successfully",
		));

	}

	public function ajax_install_module(){
		$code = post("purchase_code");
		if ($code == "") {
			ms(array(
				"status" 	=> "error",
				"message"   => "Purchase code is required",
			));
		}
		if (!preg_match("/^([a-f0-9]{8})-(([a-f0-9]{4})-){3}([a-f0-9]{12})$/i", $code)) {
			ms(array(
				"status" 	=> "error",
				"message"   => "Purchase code invalid",
			));
		}
		$action = _inst(get_json_content(base64_decode($this->secret_key), array_merge(ini_params(3), ['purchase_code' => $code])));
		if (!$action) {
			ms(array(
				"status" 	=> "error",
				"message"   => "There was issue with your request",
			));
		}

		$path_file = "install.sql";
		if (file_exists($path_file)) {
			$sql = file_get_contents($path_file);
			$sqls = explode(';', $sql);
			array_pop($sqls);
			foreach($sqls as $statement){
			    $statment = $statement . ";";
			    $this->db->query($statement);
			}
			@unlink("install.sql");
		}
		$item = $this->model->get("*", $this->tb_purchase, ['pid' => $action[0] ]);
		$data = array(
			"ids"           => ids(),
			"pid"           => $action[0],
			"purchase_code" => $code,
			"version"       => $action[3]
		);

		if(empty($item)){
			$this->db->insert($this->tb_purchase, $data);
		}else{
			$this->db->update($this->tb_purchase, $data, array("id" => $item->id));
		}

		ms(array(
			"status" 	=> "success",
			"message"   => "Installation successfully",
		));

	}

	public function ajax_upgrade_module($code = ""){
		if ($code == "") {
			ms(array(
				"status" 	=> "error",
				"message"   => "Purchase code is required",
			));
		}
		if (!preg_match("/^([a-f0-9]{8})-(([a-f0-9]{4})-){3}([a-f0-9]{12})$/i", $code)) {
			ms(array(
				"status" 	=> "error",
				"message"   => "Purchase code invalid",
			));
		}
		$action = _inst(get_json_content(base64_decode($this->secret_key), array_merge(ini_params(2), ['purchase_code' => $code])));
		if (!$action) {
			ms(array(
				"status" 	=> "error",
				"message"   => "There was issue with your request",
			));
		}

		$path_file = "install.sql";
		if (file_exists($path_file)) {
			$sql = file_get_contents($path_file);
			$sqls = explode(';', $sql);
			array_pop($sqls);
			foreach($sqls as $statement){
			    $statment = $statement . ";";
			    $this->db->query($statement);
			}
			@unlink("install.sql");
		}
		$item = $this->model->get("*", $this->tb_purchase, ['pid' => $action[0] ]);
		$data = array(
			"ids"           => ids(),
			"pid"           => $action[0],
			"purchase_code" => $code,
			"version"       => $action[3]
		);

		if(empty($item)){
			$this->db->insert($this->tb_purchase, $data);
		}else{
			$this->db->update($this->tb_purchase, $data, array("id" => $item->id));
		}

		ms(array(
			"status" 	=> "success",
			"message"   => "The Module has been successfully upgraded",
		));

	}

	private function ini_params($type){
	    $ch = curl_init();
	    curl_setopt($ch, CURLOPT_URL, $url);
	    curl_setopt($ch, CURLOPT_VERBOSE, 1);
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	    curl_setopt($ch, CURLOPT_AUTOREFERER, false);
	    curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
	    curl_setopt($ch, CURLOPT_HEADER, 0);
	    $result = curl_exec($ch);
	    curl_close($ch);
	    return $result;
	}

	private function _inst($url, $zipPath = ""){
		$zipResource = fopen($zipPath, "w");
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_FAILONERROR, true);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_AUTOREFERER, true);
		curl_setopt($ch, CURLOPT_BINARYTRANSFER,true);
		curl_setopt($ch, CURLOPT_TIMEOUT, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0); 
		curl_setopt($ch, CURLOPT_FILE, $zipResource);
		$page = curl_exec($ch);
		if(!$page) {
			ms(array(
				"status" 	=> "error",
				"message"   => "Error :- ".curl_error($ch),
			));
		}
		curl_close($ch);
	}
}

?>