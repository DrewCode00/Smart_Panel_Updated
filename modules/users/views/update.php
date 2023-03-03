
<div class="page-header">
  <h1 class="page-title">
    <i class="fe fe-edit-3"></i> <?php echo lang('Edit_user'); ?>
  </h1>
</div>

<?php
  $ids = (!empty($user->ids))? $user->ids: '';
  if ($ids != "") {
    $url = cn($module."/ajax_update/$ids");
  }else{
    $url = cn($module."/ajax_update");
  }
?>

<div class="row">
  <div class="col-md-6">
    <div class="card">
      <div class="card-header">
        <h3 class="card-title"><?php echo lang("basic_information"); ?></h3>
        <div class="card-options">
          <a href="#" class="card-options-collapse" data-toggle="card-collapse"><i class="fe fe-chevron-up"></i></a>
          <a href="#" class="card-options-remove" data-toggle="card-remove"><i class="fe fe-x"></i></a>
        </div>
      </div>
      <div class="card-body">
        <form class="form actionForm" action="<?php echo $url?>" data-redirect="<?php echo cn("$module/update/$ids"); ?>" method="POST">
          <div class="form-body">
            <div class="row">
              <div class="col-md-6 col-sm-6 col-xs-6">
                <div class="form-group">
                  <label for="projectinput5"><?php echo lang("first_name"); ?> <span class="form-required">*</span></label>
                  <input class="form-control square" name="first_name" type="text" value="<?php echo (!empty($user->first_name))? htmlspecialchars($user->first_name) : ''?>">
                </div>
              </div>
              <div class="col-md-6 col-sm-6 col-xs-6">
                  <div class="form-group">
                    <label for="userinput5"><?php echo lang("last_name"); ?> <span class="form-required">*</span></label>
                    <input class="form-control square" name="last_name" type="text" value="<?php echo (!empty($user->last_name))? htmlspecialchars($user->last_name): ''?>">
                  </div>
              </div> 
              <div class="col-md-12">
                <div class="form-group">
                  <label for="projectinput5"><?php echo lang('Email'); ?>  <span class="form-required">*</span></label>
                  <input class="form-control square" name="email" type="email" <?php echo (!empty($user->email))? 'disabled': ''?> value="<?php echo (!empty($user->email))? htmlspecialchars($user->email): ''?>">
                </div>
              </div>

              <div class="col-md-12 col-sm-12 col-xs-12">
                <div class="form-group">
                  <label for="projectinput5"><?php echo lang("account_type"); ?></label>
                  <select  name="role" class="form-control square">

                    <option value="user" <?php echo (!empty($user->role) && $user->role == "user")? 'selected': ''?>><?php echo lang("regular_user"); ?></option>

                    <option value="supporter" <?php echo (!empty($user->role) && $user->role == "supporter")? 'selected': ''?>>Supporter</option>

                    <option value="admin" <?php echo (!empty($user->role) && $user->role == "admin")? 'selected': ''?>><?php echo lang('admin'); ?></option>
                  </select>

                </div>
              </div>

              <div class="col-md-6 col-sm-6 col-xs-6">
                <div class="form-group">
                  <label><?php echo lang('Status'); ?></label>
                  <select name="status" class="form-control square">
                    <option value="1" <?php echo (!empty($user->status) && $user->status == 1)? 'selected': ''?>><?php echo lang('Active'); ?></option>
                    <option value="0" <?php echo (isset($user->status) && $user->status != 1)? 'selected': ''?>><?php echo lang('Deactive'); ?></option>
                  </select>
                </div>
              </div>


              <div class="col-md-6 col-sm-6 col-xs-6">
                <div class="form-group">
                  <label for="projectinput5"><?php echo lang('Timezone'); ?></label>
                  <select  name="timezone" class="form-control square">
                    <?php $time_zones = tz_list();
                      if (!empty($time_zones)) {
                        foreach ($time_zones as $key => $time_zone) {
                    ?>
                    <option value="<?php echo $time_zone['zone']?>" <?php echo (!empty($user->timezone) && $user->timezone == $time_zone["zone"])? 'selected': ''?>><?php echo $time_zone['time']?></option>
                    <?php }}?>
                  </select>
                </div>
              </div>
              
              <div class="col-md-6 col-sm-6 col-xs-6">
                <div class="form-group">
                  <label for="projectinput5"><?php echo lang('Password'); ?> <span class="required">*</span></label>
                  <input class="form-control square" name="password" type="password">
                  <small class="text-primary"><?php echo lang("note_if_you_dont_want_to_change_password_then_leave_these_password_fields_empty"); ?></small>
                </div>
              </div> 

              <div class="col-md-6 col-sm-6 col-xs-6">
                <div class="form-group">
                  <label for="projectinput5"><?php echo lang('Confirm_password'); ?> <span class="required">*</span></label>
                  <input class="form-control square" name="re_password" type="password">
                </div>
              </div>

              <div class="col-md-12 col-sm-12 col-xs-12">
                <div class="form-group">
                  <label for="userinput8"><?php echo lang('Description'); ?></label>
                  <textarea id="editor"  rows="5" class="form-control square plugin_editor" name="desc" placeholder="Description"><?php echo (!empty($user->desc))? $user->desc: ''?></textarea>
                </div>
              </div>

              <div class="col-md-12 col-sm-12 col-xs-12 mb-5">
                <h5 class="text-info"><i class="fe fe-link"></i> <?php echo lang("allowed_payment_methods"); ?></h5>
                <div class="row">
                  <?php
                    
                    foreach ($payments_defaut as $payment_method) {
                  ?>
                  <div class="col-md-4">
                    <div class="form-group">
                      <div class="custom-controls-stacked">
                        <label class="custom-switch mr-5">
                          <input type="hidden" name="settings[limit_payments][<?php echo $payment_method->type; ?>]" value="0">
                          <input type="checkbox" name="settings[limit_payments][<?php echo $payment_method->type; ?>]" class="custom-switch-input" value="1" <?php echo (isset($limit_payments[$payment_method->type]) && $limit_payments[$payment_method->type] == 1) ? 'checked' : '' ;?> >
                          <span class="custom-switch-indicator"></span>
                          <span class="custom-switch-description"> <?php echo ucfirst($payment_method->name); ?></span>
                        </label>
                      </div>
                    </div>
                  </div>
                  <?php }?>
                </div>
              </div>

              <div class="col-md-12 col-sm-12 col-xs-12">
                <button type="submit" class="btn round btn-primary btn-min-width mr-1 mb-1"><?php echo lang('Save'); ?></button>
              </div>
            </div>
          </div>
          <div class="">
          </div>
        </form>
      </div>
    </div>
  </div> 

  <div class="col-md-6">
    <div class="card">
      <div class="card-header">
        <h3 class="card-title"><?php echo lang("more_informations"); ?></h3>
        <div class="card-options">
          <a href="#" class="card-options-collapse" data-toggle="card-collapse"><i class="fe fe-chevron-up"></i></a>
          <a href="#" class="card-options-remove" data-toggle="card-remove"><i class="fe fe-x"></i></a>
        </div>
      </div>
      <div class="card-body">
        <form class="form actionForm" action="<?php echo cn($module."/ajax_update_more_infors/$ids"); ?>" data-redirect="<?php echo cn("$module/update/$ids"); ?>" method="POST">
          <div class="form-body">
            <div class="row">
              <?php
                if (!empty($user->more_information)) {
                  $infors     = $user->more_information;
                  $website    = htmlspecialchars(get_value($infors, "website"));
                  $phone      = htmlspecialchars(get_value($infors, "phone"));
                  $skype_id   = htmlspecialchars(get_value($infors, "skype_id"));
                  $what_asap  = htmlspecialchars(get_value($infors, "what_asap"));
                  $address    = htmlspecialchars(get_value($infors, "address"));
                }
              ?>  
              <div class="col-md-6 col-sm-6 col-xs-6">
                <div class="form-group">
                  <label for="userinput5"><?php echo lang('Website'); ?></label>
                  <input class="form-control square" name="website" type="text" value="<?php echo (!empty($website))? $website: ''?>">
                </div>
              </div> 

              <div class="col-md-6 col-sm-6 col-xs-6">
                <div class="form-group">
                  <label for="projectinput5"><?php echo lang('Phone'); ?></label>
                  <input class="form-control square" name="phone" type="text" value="<?php echo (!empty($phone))? $phone: ''?>">
                </div>
              </div>

              <div class="col-md-6 col-sm-6 col-xs-6">
                <div class="form-group">
                  <label for="projectinput5"><?php echo lang('Skype_id'); ?></label>
                  <input class="form-control square"  name="skype_id"  type="text" value="<?php echo (!empty($skype_id))? $skype_id: ''?>">
                </div>
              </div>

              <div class="col-md-6 col-sm-6 col-xs-6">
                <div class="form-group">
                  <label for="projectinput5"><?php echo lang("whatsapp_number"); ?></label>
                  <input class="form-control square"  name="what_asap"  type="text" value="<?php echo (!empty($what_asap))? $what_asap: ''?>">
                </div>
              </div>

              <div class="col-md-6 col-sm-6 col-xs-6">
                <div class="form-group">
                  <label for="projectinput5"><?php echo lang('Address'); ?></label>
                  <input class="form-control square" name="address" type="text" value="<?php echo (!empty($address))? $address: ''?>">
                  <small class="text-primary"><?php echo lang("note_if_you_dont_want_add_more_information_then_leave_these_informations_fields_empty"); ?></small>
                </div>
              </div>
              
              <div class="col-md-12 col-sm-12 col-xs-12">
                <button type="submit" class="btn round btn-primary btn-min-width mr-1 mb-1"><?php echo lang('Save'); ?></button>
              </div>
            </div>
          </div>
          <div class="">
          </div>
        </form>
      </div>
    </div>
  </div>  
  
  <?php
    if (!empty($ids)) {
  ?>
  <div class="col-md-6">
    <div class="card">
      <div class="card-header">
        <h3 class="card-title"><?php echo lang("Add_Funds"); ?></h3>
        <div class="card-options">
          <a href="#" class="card-options-collapse" data-toggle="card-collapse"><i class="fe fe-chevron-up"></i></a>
          <a href="#" class="card-options-remove" data-toggle="card-remove"><i class="fe fe-x"></i></a>
        </div>
      </div>
      <div class="card-body">
        <form class="form actionForm" action="<?php echo cn($module."/ajax_update_fund/$ids"); ?>" data-redirect="<?php echo cn($module); ?>" method="POST">
          <div class="form-group">
            <label for="projectinput5"><?php echo lang("Funds"); ?></label>
            <input class="form-control square" name="funds" type="text" value="<?php echo (!empty($user->balance))? $user->balance: 0 ?>">
          </div>
          <div class="">
            <button type="submit" class="btn round btn-primary btn-min-width mr-1 mb-1"><?php echo lang("Submit"); ?></button>
          </div>
        </form>
      </div>
    </div>
  </div>
  <?php }?>
</div>
