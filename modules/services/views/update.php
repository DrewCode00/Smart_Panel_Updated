<div id="main-modal-content">
  <div class="modal-right">
    <div class="modal-dialog modal-lg" role="document">
      <div class="modal-content">
        <?php
          $ids = (!empty($service->ids))? $service->ids: '';
          if ($ids != "") {
            $url = cn($module."/ajax_update/$ids");
          }else{
            $url = cn($module."/ajax_update");
          }
        ?>
        <form class="form actionForm" action="<?=$url?>" method="POST">
          <div class="modal-header bg-pantone">
            <h4 class="modal-title"><i class="fa fa-edit"></i> <?=lang("edit_service")?></h4>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            </button>
          </div>
          <div class="modal-body">
            <div class="form-body" id="add_edit_service">
              <div class="row justify-content-md-center">
                <div class="col-md-12 col-sm-12 col-xs-12">
                  <div class="form-group emoji-picker-container">
                    <label ><?=lang("package_name")?></label>
                    <input type="text" data-emojiable="true" class="form-control square" name="name" value="<?=(!empty($service->name))? $service->name: ''?>">
                  </div>
                </div>
                <div class="col-md-12 col-sm-12 col-xs-12">
                  <div class="form-group">
                    <label><?=lang("choose_a_category")?></label>
                    <select  name="category" class="form-control square">
                      <?php if(!empty($categories)){
                        foreach ($categories as $key => $category) {
                      ?>
                      <option value="<?=$category->id?>" <?=(!empty($service->ids) && $category->id == $service->cate_id)? 'selected': ''?> ><?=$category->name?></option>
                     <?php }}?>
                    </select>
                  </div>
                </div>
                
                <div class="col-md-12">
                  <div class="form-group">
                    <div class="form-label"><?php echo lang("Type"); ?></div>
                    <div class="custom-controls-stacked">
                      <label class="custom-control custom-radio custom-control-inline">
                        <input type="radio" class="custom-control-input" name="add_type" value="manual" <?php echo (isset($service->add_type) && $service->add_type == 'manual')? 'checked': ''?>>
                        <span class="custom-control-label"><?php echo lang('Manual'); ?></span>
                      </label>
                      <label class="custom-control custom-radio custom-control-inline">
                        <input type="radio" class="custom-control-input" name="add_type" value="api" <?php echo (isset($service->add_type) && $service->add_type == 'api')? 'checked': ''?>>
                        <span class="custom-control-label"><?php echo lang('API'); ?></span>
                      </label>
                      
                    </div>
                  </div>
                </div>

                <!-- API mode -->
                <div class="col-md-12 service-type <?php echo (isset($service->add_type) && $service->add_type == 'api')? '' : 'd-none'?>">
                  <fieldset class="form-fieldset">
                    <div class="form-group">
                      <label><?php echo lang("api_provider_name"); ?></label>
                      <select name="api_provider_id" class="form-control square ajaxGetServicesFromAPI" data-url="<?php echo cn($module.'/ajax_get_services_from_api/'); ?>">
                        <option value="0"> <?php echo lang('choose_a_api_provider'); ?></option>
                        <?php
                          if (!empty($api_providers)) {
                          foreach ($api_providers as $type => $api_provider) {
                        ?>
                        <option value="<?php echo strip_tags($api_provider->id)?>" <?php echo (isset($service->api_provider_id) && $service->api_provider_id == $api_provider->id)? 'selected': ''?>><?php echo strip_tags($api_provider->name)?></option>
                        <?php }} ?>
                      </select>
                    </div>

                    <div class="form-group result-api-service-lists d-none">
                      <div class="dimmer">
                        <div class="loader"></div>
                        <div class="dimmer-content">
                          <label><?php echo lang('list_of_api_services'); ?></label>
                          <select name="api_service_id" class="form-control square">
                            <option value="0"> <?php echo lang('choose_a_service'); ?></option>
                          </select>
                          <input type="hidden" class="form-control square" name="api_service_id" value="<?php echo (!empty($service->api_service_id))? $service->api_service_id : '' ?>">
                        </div>
                      </div>
                    </div>

                    <div class="row api-service-details d-none">
                      <div class="col-md-12">
                        <div class="form-group">
                          <label> Original Rate per 1000</label>
                          <input type="text" class="form-control square" name="original_price" value="<?php echo (!empty($service->original_price))? $service->original_price : '' ?>" disabled>
                          <input type="hidden" class="form-control square" name="original_price" value="<?php echo (!empty($service->original_price))? $service->original_price : '' ?>">

                          <input type="hidden" class="form-control square" name="api_service_type" value="<?php echo (!empty($service->type))? $service->type : 'default' ?>">

                          <input type="hidden" class="form-control square" name="api_service_dripfeed" value="<?php echo (!empty($service->dripfeed))? $service->dripfeed : '' ?>">
                        </div>
                      </div>
                    </div>
                  </fieldset>
                </div>
                <!-- Manual Mode -->
                <div class="col-md-12 service-manual-type <?php echo (!isset($service->add_type) || (isset($service->add_type) && $service->add_type == 'manual')) ? '' : 'd-none'?>">
                  <fieldset class="form-fieldset">
                    <div class="row">
                      <div class="col-md-6">
                        <div class="form-group">
                          <label>Service type</label>
                          <select name="service_type" class="form-control square ajaxChangeServiceType">
                            <?php
                              $service_type_array = all_services_type();
                              foreach ($service_type_array as $type => $service_type) {
                            ?>
                            <option value="<?=$type?>" <?=(isset($service->type) && $service->type == $type)? 'selected': ''?>><?=$service_type?></option>
                            <?php } ?>
                          </select>
                        </div>
                      </div>
                      <div class="col-md-6">
                        <div class="form-group">
                          <label><?=lang("dripfeed")?></label>
                          <select name="dripfeed" class="form-control square">
                            <option value="0" <?=(isset($service->dripfeed) && $service->dripfeed != 1)? 'selected': ''?>><?=lang("Deactive")?></option>
                            <option value="1" <?=(!empty($service->dripfeed) && $service->dripfeed == 1)? 'selected': ''?>><?=lang("Active")?></option>
                          </select>
                        </div>
                      </div>
                    </div>
                  </fieldset>
                </div>

                <div class="col-md-4 col-sm-6 col-xs-6">
                  <div class="form-group">
                    <label><?=lang("minimum_amount")?></label>
                    <input type="number" class="form-control square" name="min" value="<?=(!empty($service->min))? $service->min :  get_option('default_min_order',"")?>">
                  </div>
                </div>

                <div class="col-md-4 col-sm-6 col-xs-6">
                  <div class="form-group">
                    <label><?=lang("maximum_amount")?></label>
                    <input type="number" class="form-control square" name="max" value="<?=(!empty($service->max))? $service->max : get_option('default_max_order',"")?>">
                  </div>
                </div>

                <div class="col-md-4 col-sm-6 col-xs-6">
                  <div class="form-group">
                    <label><?=lang("rate_per_1000")?></label>
                    <input type="text" class="form-control square" name="price" value="<?=(!empty($service->price))? $service->price: currency_format(get_option('default_price_per_1k',"0.80"),2)?>">
                  </div>
                </div>

                <div class="col-md-12 col-sm-6 col-xs-6">
                  <div class="form-group">
                    <label><?=lang("Status")?></label>
                    <select name="status" class="form-control square">
                      <option value="1" <?=(!empty($service->status) && $service->status == 1)? 'selected': ''?>><?=lang("Active")?></option>
                      <option value="0" <?=(isset($service->status) && $service->status != 1)? 'selected': ''?>><?=lang("Deactive")?></option>
                    </select>
                  </div>
                </div>

                <div class="col-md-12 col-sm-12 col-xs-12">
                  <div class="form-group">
                    <label><?=lang("Description")?></label>
                    <textarea rows="10"  class="form-control square text-emoji" id="1text-emoji" name="desc"><?=(!empty($service->desc))? html_entity_decode($service->desc, ENT_QUOTES): ''?></textarea>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <a href="<?=cn("api_provider/services")?>" class="btn round btn-info btn-min-width mr-1 mb-1"><?=lang("add_new_service_via_api")?></a>
            <button type="submit" class="btn round btn-primary btn-min-width mr-1 mb-1"><?=lang("Submit")?></button>
            <button type="button" class="btn round btn-default btn-min-width mr-1 mb-1" data-dismiss="modal"><?=lang("Cancel")?></button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<script>
  // Check post type
  $(document).on("change","input[type=radio][name=add_type]", function(){
    _that = $(this);
    _type = _that.val();
    if(_type == 'api'){
      $('.service-type').removeClass('d-none');
      $('.service-manual-type').addClass('d-none');
    }else{
      $('.service-manual-type').removeClass('d-none');
      $('.service-type').addClass('d-none');
    }
  });

  /*----------  Load default service with API  ----------*/
  $( document ).ready(function() {
    var service_type = $("input[name='add_type']:checked"). val();
    if (service_type == "api") {

      $('.result-api-service-lists').removeClass('d-none');
      $('.result-api-service-lists .dimmer').addClass('active');
      var _id = $('select[name=api_provider_id]').val();
      if (_id == "" || _id == 0) {
        return;
      }
      var _api_service_id = $('input[name=api_service_id]').val();
      var _action     = "<?php echo cn($module.'/ajax_get_services_from_api/'); ?>" + _api_service_id,
          _token      = '<?php echo strip_tags($this->security->get_csrf_hash()); ?>',
          _data       = $.param({token:_token, api_id:_id});
      $.post( _action, _data,function(_result){
        setTimeout(function () {
          $('.api-service-details').removeClass('d-none');
          $('.result-api-service-lists .dimmer').removeClass('active');
          $(".result-api-service-lists .dimmer-content").html(_result);
          
          var _that = $( ".ajaxGetServiceDetail option:selected"),
              _rate = _that.attr("data-rate");
          $(".api-service-details input[name=original_price]").val(_rate);
        }, 100);
      });
      return false;
    }
  });

  /*----------  Get Services list from API  ----------*/
  $(document).on("change", ".ajaxGetServicesFromAPI" , function(){

    event.preventDefault();
    $('.result-api-service-lists').removeClass('d-none');
    $('.result-api-service-lists .dimmer').addClass('active');
    var _that       = $(this),
        _id         = _that.val();
    if (_id == "" || _id == 0) {
        return;
    }
    var _action     = _that.data("url"),
        _token      = '<?php echo strip_tags($this->security->get_csrf_hash()); ?>',
        _data       = $.param({token:_token, api_id:_id});
    $.post( _action, _data,function(_result){
      setTimeout(function () {
        $('.api-service-details').removeClass('d-none');
        $(".api-service-details input[name=original_price]").val('');
        $(".api-service-details input[name=api_service_type]").val('');
        $(".api-service-details input[name=api_service_dripfeed]").val('');

        $('.result-api-service-lists .dimmer').removeClass('active');
        $(".result-api-service-lists .dimmer-content").html(_result);
      }, 100);
    });
  })  

  /*----------  Choose a service  ----------*/
  $(document).on("change", ".ajaxGetServiceDetail", function(){
    
    $(".api-service-details input[name=original_price]").val('');
    $("#add_edit_service input[name=min]").val('');
    $("#add_edit_service input[name=max]").val('');

    var _that      = $('option:selected', this),
        _name      = _that.attr('data-name'),
        _min       = _that.attr('data-min'),
        _max       = _that.attr("data-max"),
        _rate      = _that.attr("data-rate"),
        _type      = _that.attr("data-type"),
        _dripfeed  = _that.attr("data-dripfeed");

    $(".api-service-details input[name=original_price]").val(_rate);
    $(".api-service-details input[name=api_service_type]").val(_type);
    $(".api-service-details input[name=api_service_dripfeed]").val(_dripfeed);

    $("#add_edit_service input[name=min]").val(_min);
    $("#add_edit_service input[name=max]").val(_max);
    $("#add_edit_service select[name=min]").val('');
  })
</script>

<script>
  $(function() {
    window.emojiPicker = new EmojiPicker({
      emojiable_selector: '[data-emojiable=true]',
      assetsPath: "<?=BASE?>assets/plugins/emoji-picker/lib/img/",
      popupButtonClasses: 'fa fa-smile-o'
    });
    window.emojiPicker.discover();
  });
</script>

<script>
  $(function() {
    window.emojiPicker = new EmojiPicker({
      emojiable_selector: '[data-emojiable=true]',
      assetsPath: "<?=BASE?>assets/plugins/emoji-picker/lib/img/",
      popupButtonClasses: 'fa fa-smile-o'
    });
    window.emojiPicker.discover();
  });
</script>

<script type="text/javascript">
  $(document).ready(function() {
    $(".text-emoji").emojioneArea({
      pickerPosition: "top",
      tonesStyle: "bullet"
    });
  });
</script>
