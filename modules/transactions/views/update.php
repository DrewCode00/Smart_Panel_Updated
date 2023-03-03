
<div id="main-modal-content">
  <div class="modal-right">
    <div class="modal-dialog modal-lg" role="document">
      <div class="modal-content">
        <?php
          $ids = (!empty($transaction->ids))? $transaction->ids: '';
        ?>
        <form class="form actionForm" action="<?=cn($module."/ajax_update/$ids")?>" method="POST">
          <div class="modal-header bg-pantone">
            <h4 class="modal-title"><i class="fa fa-edit"></i> Edit Transaction</h4>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            </button>
          </div>
          <div class="modal-body">
            <div class="form-body">
              <div class="row justify-content-md-center">

                <div class="col-md-12 col-sm-12 col-xs-12">
                  <div class="form-group">
                    <label ><?=lang("User")?></label>
                    <input type="hidden" name="uid" value="<?=(!empty($transaction->uid))? $transaction->uid : ''?>">
                    <input type="hidden" name="ids" value="<?=(!empty($transaction->ids))? $transaction->ids : ''?>">
                    <input type="text" class="form-control square" value="<?=(!empty($transaction->uid))? get_field('general_users', ["id" => $transaction->uid], "email"): ''?>">
                  </div>
                </div>

                <div class="col-md-12 col-sm-12 col-xs-12">
                  <div class="form-group">
                    <label ><?=lang("Transaction_ID")?></label>
                    <input type="text" name="transaction_id" class="form-control square" value="<?=(!empty($transaction->transaction_id))? $transaction->transaction_id: ''?>">
                  </div>
                </div>

                <?php
                  $get_payments_method = get_payments_method();
                  $get_payments_method = array_merge($get_payments_method, ['paypal', 'stripe', '2checkout']);
                ?>
                <div class="col-md-12 col-sm-12 col-xs-12">
                  <div class="form-group">
                    <label><?=lang('Payment_method')?></label>
                    <select name="payment_method" class="form-control square">
                      <?php
                        foreach ($get_payments_method as $key => $row) {
                      ?>
                      <option class="text-uppercase" value="<?php echo $row; ?>" <?php echo ($row == $transaction->type) ? "selected" : '' ; ?> > 
                        <?php echo $row; ?>
                      </option>
                      <?php } ?>
                      <option value="manual">Bank/Other (Manual Payment)</option>
                    </select>
                  </div>
                </div>

                <div class="col-md-12 col-sm-12 col-xs-12">
                  <div class="form-group">
                    <label><?=lang('Status')?></label>
                    <select name="status" class="form-control square">
                      <option value="-1"<?php echo (-1 == $transaction->status) ? "selected" : '' ; ?>><?php echo lang('cancelled_timed_out'); ?></option>
                      <option value="0"<?php echo (0 == $transaction->status) ? "selected" : '' ; ?>><?php echo lang('waiting_for_buyer_funds'); ?></option>
                      <option value="1"<?php echo (1 == $transaction->status) ? "selected" : '' ; ?>><?php echo lang('Paid'); ?></option>
                    </select>
                  </div>
                </div>

                <div class="col-md-12 col-sm-12 col-xs-12">
                  <div class="form-group">
                    <label>Note</label>
                    <textarea rows="3"  class="form-control square text-emoji" name="note"><?=(!empty($transaction->note))? $transaction->note : ''?></textarea>
                  </div>
                </div> 


              </div>
            </div>
          </div>
          <div class="modal-footer">
            
            <button type="submit" class="btn round btn-primary btn-min-width mr-1 mb-1"><?=lang("Submit")?></button>
            <button type="button" class="btn round btn-default btn-min-width mr-1 mb-1" data-dismiss="modal"><?=lang("Cancel")?></button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
