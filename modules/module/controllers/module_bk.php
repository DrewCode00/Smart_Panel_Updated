<?php
defined('BASEPATH') OR exit('No direct script access allowed');
 
class module extends MX_Controller {
	public $tb_users;
	public $tb_purchase;
	public $module_name;
	public $module_icon;

	public function __construct(){
		parent::__construct();
		$this->load->model(get_class($this).'_model', 'model');
		$this->tb_purchase = PURCHASE;
	}

	public function index(){
		$purchase_code_item = $this->model->get("purchase_code", $this->tb_purchase);
		$scripts = "";
		if (!empty($purchase_code_item)) {
			$purchase_code = $purchase_code_item->purchase_code;
			$domain = base_url();
			$url = "https://smartpanelsmm.com/pc_verify/script_list?purchase_code=".urlencode($purchase_code);
			$result = $this->curl($url);
			if (!empty($result)) {
				$result = json_decode($result);
				if (!empty($result->scripts)) {
					$scripts = $result->scripts;
				}
			}
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
		$purchase_code = post("purchase_code");
		if ($purchase_code == "") {
			ms(array(
				"status" 	=> "error",
				"message"   => "Purchase code is required",
			));
		}

		if (strlen($purchase_code) != 36) {
			ms(array(
				"status" 	=> "error",
				"message"   => "Purchase code invalid",
			));
		}

		$domain = base_url();
		$url = "https://smartpanelsmm.com/pc_verify/install?type=install&main=0&purchase_code=".urlencode($purchase_code)."&domain=".urlencode($domain);
		$output_filename = "install.zip";
		$result = $this->curl($url);

		if ($result != "") {
			$result_object = json_decode($result);
			if (is_object($result_object)) {
				switch ($result_object->status) {
					case 'error':
						ms(array(
							"status" 	=> "error",
							"message"   => $result_object->message,
						));
						break;	
										
					case 'success':
						$result_object = explode("{|}", $result_object->response);
						$purchase_code_exists = $this->model->get("*", $this->tb_purchase, ['purchase_code' => $purchase_code, 'pid' => $result_object[0] ]);

						if (!empty($purchase_code_exists)) {
							ms(array(
								"status" 	=> "error",
								"message"   => "This app has already installed. You can't reinstall it again.",
							));
						}
						$this->__curl(base64_decode($result_object[2]), $output_filename);

						if (filesize($output_filename) == 0) {
							ms(array(
								"status" 	=> "error",
								"message"   => "There was an error processing your request. Please contact me via email: tuyennguyen2906@gmail.com",
							));
						}

						/* Open the Zip file */
						$zip = new ZipArchive;
						if($zip->open($output_filename) != TRUE){
							ms(array(
								"status" 	=> "error",
								"message"   => "Error :- Unable to open the Zip File",
							));
						} 
						/* Extract Zip File */
						$zip->extractTo("./");
						$zip->close();

						/*----------  Install SQL  ----------*/
						if (file_exists("install.sql")) {
							$sql = file_get_contents("install.sql");
							$sqls = explode(';', $sql);
							array_pop($sqls);
							foreach($sqls as $statement){
							    $statment = $statement . ";";
							    $this->db->query($statement);
							}
							@unlink("install.sql");
						}
						@unlink('install.zip');
						/*----------  Insert data  ----------*/
						$item = $this->model->get("*", $this->tb_purchase, ['pid' => $result_object[0] ]);
						$data = array(
							"ids"           => ids(),
							"pid"           => $result_object[0],
							"purchase_code" => $purchase_code,
							"version"       => $result_object[3]
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
						break;
				}
			}else{

				ms(array(
					"status" 	=> "error",
					"message"   => "There was some issue with your purchase code. please contact me via email: tuyennguyen2906@gmail.com",
				));
			}

		}else{
			ms(array(
				"status" 	=> "error",
				"message"   => "There was some issue with your purchase code. please contact me via email: tuyennguyen2906@gmail.com",
			));
		}

	}

	public function ajax_upgrade_module($purchase_code = ""){

		if ($purchase_code == "") {
			ms(array(
				"status" 	=> "error",
				"message"   => "Purchase code is required",
			));
		}

		if (strlen($purchase_code) != 36) {
			ms(array(
				"status" 	=> "error",
				"message"   => "Purchase code invalid",
			));
		}

		$domain = base_url();
		$url = "https://smartpanelsmm.com/pc_verify/install?type=upgrade&purchase_code=".urlencode($purchase_code)."&domain=".urlencode($domain);
		
		$output_filename = "install.zip";
		$result = $this->curl($url);
		
		if ($result != "") {
			$result_object = json_decode($result);
			if (is_object($result_object)) {
				switch ($result_object->status) {
					case 'error':
						ms(array(
							"status" 	=> "error",
							"message"   => $result_object->message,
						));
						break;	
										
					case 'success':
						$result_object = explode("{|}", $result_object->response);
						$purchase_code_exists = $this->model->get("*", $this->tb_purchase, ['purchase_code' => $purchase_code, 'pid' => $result_object[0] ]);

						if (empty($purchase_code_exists)) {
							ms(array(
								"status" 	=> "error",
								"message"   => "This item doesn't exits. You can't upgrade to the last version.",
							));
						}
						
						$this->__curl(base64_decode($result_object[2]), $output_filename);

						if (filesize($output_filename) == 0) {
							ms(array(
								"status" 	=> "error",
								"message"   => "There was an error processing your request. Please contact me via email: tuyennguyen2906@gmail.com",
							));
						}

						/* Open the Zip file */
						$zip = new ZipArchive;
						if($zip->open($output_filename) != TRUE){
							ms(array(
								"status" 	=> "error",
								"message"   => "Error :- Unable to open the Zip File",
							));
						} 
						/* Extract Zip File */
						$zip->extractTo("./");
						$zip->close();

						/*----------  Install SQL  ----------*/
						if (file_exists("install.sql")) {
							$sql = file_get_contents("install.sql");
							$sqls = explode(';', $sql);
							array_pop($sqls);
							foreach($sqls as $statement){
							    $statment = $statement . ";";
							    $this->db->query($statement);
							}
							@unlink("install.sql");
						}
						@unlink('install.zip');
						/*----------  Insert data  ----------*/
						$item = $this->model->get("*", $this->tb_purchase, ['pid' => $result_object[0] ]);
						$data = array(
							"ids"           => ids(),
							"pid"           => $result_object[0],
							"purchase_code" => $purchase_code,
							"version"       => $result_object[3]
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
						break;
				}
			}else{

				ms(array(
					"status" 	=> "error",
					"message"   => "There was some issue with your purchase code. please contact me via email: tuyennguyen2906@gmail.com",
				));
			}

		}else{
			ms(array(
				"status" 	=> "error",
				"message"   => "There was some issue with your purchase code. please contact me via email: tuyennguyen2906@gmail.com",
			));
		}

	}

	private function curl($url){
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

	private function __curl($url, $zipPath = ""){
		$zipResource = fopen($zipPath, "w");
		// Get The Zip File From Server
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

	private function extract_zip_file($output_filename){
		/* Open the Zip file */
		$zip = new ZipArchive;
		$extractPath = $output_filename;
		if($zip->open($zipFile) != "true"){
			ms(array(
				"status" 	=> "error",
				"message"   => "Error :- Unable to open the Zip File",
			));
		} 
		/* Extract Zip File */
		$zip->extractTo($extractPath);
		$zip->close();
	}
}

?>