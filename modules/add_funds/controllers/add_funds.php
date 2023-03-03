<?php
defined('BASEPATH') OR exit('No direct script access allowed');
 
class add_funds extends MX_Controller {
	public $tb_users;
	public $tb_transaction_logs;
	public $tb_payments;
	public $tb_payments_bonuses;
	public $module;
	public $module_icon;

	public function __construct(){
		parent::__construct();
		$this->load->model(get_class($this).'_model', 'model');
		$this->module              = get_class($this);
		$this->tb_users            = USERS;
		$this->tb_transaction_logs = TRANSACTION_LOGS;
		$this->tb_payments         = PAYMENTS_METHOD;
		$this->tb_payments_bonuses = PAYMENTS_BONUSES;
	}

	public function index(){
		/*----------  Get Payment Gate Way for user  ----------*/
		$payments = $this->model->fetch('type, name, id, params', $this->tb_payments, ['status' => 1]);
		$user_settings = $this->model->get('settings', $this->tb_users, ['id' => session('uid')])->settings;
		$user_settings = json_decode($user_settings);
		if (isset($user_settings->limit_payments)) {
          $limit_payments = (array)$user_settings->limit_payments;
          foreach ($payments as $key => $payment) {
          	if (isset($limit_payments[$payment->type]) && !$limit_payments[$payment->type]) {
          		unset($payments[$key]);
          	}
          }
        }
		$data = array(
			"module"        	=> get_class($this),
			"payments"      	=> $payments,
			"currency_code"     => get_option("currency_code",'USD'),
			"currency_symbol"   => get_option("currency_symbol",'$'),
		);
		$this->template->build('index', $data);
	}

	public function process(){
		_is_ajax($this->module);
		$payment_id     = (int)post("payment_id");
		$payment_method = post("payment_method");
		$amount         = (double)post("amount");
		$agree          = post("agree");
		if ($amount  == "") {
			ms(array(
				"status"  => "error",
				"message" => lang("amount_is_required"),
			));
		}

		if ($amount  < 0) {
			ms(array(
				"status"  => "error",
				"message" => lang("amount_must_be_greater_than_zero"),
			));
		}

		/*----------  Check payment method  ----------*/
		$payment = $this->model->get('id, type, name, params', $this->tb_payments, ['id' => $payment_id, 'type' => $payment_method]);

		if (!$payment) {
			_validation('error', lang('There_was_an_error_processing_your_request_Please_try_again_later'));
		}

		$min_payment = get_value($payment->params, 'min');
		$max_payment = get_value($payment->params, 'max');

		if ($amount  < $min_payment) {
			_validation('error', lang("minimum_amount_is")." ".$min_payment);
		}

		if ($max_payment > 0 && $amount  > $max_payment) {
			_validation('error', 'Maximal amount is'." ".$max_payment);
		}

		if (!$agree) {
			_validation('error', lang("you_must_confirm_to_the_conditions_before_paying"));
		}
		
		$data_payment = array(
			"module"             => get_class($this),
			"amount"             => $amount,
		);
		require_once $payment_method.'.php';
		$payment_module = new $payment_method($payment);
		$payment_module->create_payment($data_payment);

	}

	public function success(){
		$id = session("transaction_id");
		$transaction = $this->model->get("*", $this->tb_transaction_logs, "id = '{$id}' AND uid ='".session('uid')."'");
		if (!empty($transaction)) {
			$data = array(
				"module"        => get_class($this),
				"transaction"   => $transaction,
			);
			unset_session("transaction_id");
			$this->template->build('payment_successfully', $data);
		}else{
			redirect(cn("add_funds"));
		}
	}

	public function unsuccess(){
		$data = array(
			"module"        => get_class($this),
		);
		$this->template->build('payment_unsuccessfully', $data);
	}

	public function send_mail_payment_notification($data_pm_mail = ""){
		if ($data_pm_mail['user']) {

			$user 		= $data_pm_mail['user'];
			$subject 	= get_option('email_payment_notice_subject', '');
			$message 	= get_option('email_payment_notice_content', '');
			// get Merge Fields
			$merge_fields = [
				'{{user_firstname}}' => $user->first_name
			];
			$template 	= [ 'subject' => $subject, 'message' => $message, 'type' => 'default', 'merge_fields' => $merge_fields];
			$send_message = $this->model->send_mail_template($template, $user->id);

			if($send_message){
				ms(array(
					'status'   => 'error',
					'message'  => $send_message,
				));
			}
			return true;
		}else{
			return false;
		}
	}

	public function add_payment_bonuses($data_pm = ""){
		if (!$data_pm) {
			return false;
		}

		// get payment bonuses
		$payment_bonus = $this->model->get("id, bonus_from, percentage, status", $this->tb_payments_bonuses, ['payment_id' => $data_pm['payment_id'],'status' => 1, 'bonus_from <=' => $data_pm['amount']]);
		if (!$payment_bonus) {
			return false;
		}

		// add bonuses
		$user_info   = $this->model->get('id, role, first_name, last_name, email, balance, timezone', $this->tb_users, ["id" => $data_pm['uid'] ]);
		$user_balance = $user_info->balance;
		$bonus = ($payment_bonus->percentage / 100 )* $data_pm['amount'];
		$user_balance += $bonus;
		$this->db->update($this->tb_users, ["balance" => $user_balance], ["id" => $data_pm['uid']]);

		// insert transaction id: 
		$data_tnx_log = array(
			"ids" 				=> ids(),
			"uid" 				=> $data_pm['uid'],
			"type" 				=> 'Bonus',
			"transaction_id" 	=> "",
			"amount" 	        => $bonus,
			"status" 	        => 1,
			"created" 			=> NOW,
		);
		$transaction_log_id = $this->db->insert($this->tb_transaction_logs, $data_tnx_log);

	}

	// add new funds
	public function update_user_balance($uid, $current_balance, $new_funds){
		$user_balance = $current_balance + $new_funds;
		$this->db->update($this->tb_users, ["balance" => $user_balance], ["id" => $uid]);
	}


	// Add fund, bonus and send email
	public function add_funds_bonus_email($data_tnx, $payment_id = ""){
		if (!$data_tnx) return false;
		
		// Update Balance  and total spent
        $user   = $this->model->get('id, role, first_name, last_name, email, balance, timezone, spent', $this->tb_users, ["id" => $data_tnx->uid]);

        if (!$user) return false;
		$new_funds 		= $data_tnx->amount - $data_tnx->txn_fee;
		$new_balance 	= $user->balance + $new_funds;

		if ($user->spent == "") {
			$total_spent_before = $this->model->sum_results('amount', $this->tb_transaction_logs, ['status' => 1, 'uid' => $data_tnx->uid] );
			$total_spent = (double)round($total_spent_before + $data_tnx->amount, 4);
		}else{
			$total_spent = (double)round($user->spent + $data_tnx->amount, 4);
		}

		$user_update_data = [
			"balance" => $new_balance,
			"spent"   => $total_spent,
		];
		$this->db->update($this->tb_users, $user_update_data, ["id" => $data_tnx->uid]);

		//Add bonus
		if ($payment_id) {
			$data_pm_bonus = [
				'payment_id'    => $payment_id,
				'uid'           => $data_tnx->uid,
				'amount'        => $new_funds,
			];
			$this->add_payment_bonuses($data_pm_bonus);
		}
		
		/*----------  Send payment notification email  ----------*/
		if (get_option("is_payment_notice_email", '')) {
			$this->send_mail_payment_notification(['user' => $user]);
		}

		return true;
	}
}