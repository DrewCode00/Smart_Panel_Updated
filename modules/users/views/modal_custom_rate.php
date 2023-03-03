<div class="modal-dialog modal-lg">
  <div class="modal-content">
    <div class="modal-header">
      <h4 class="modal-title"></i> <?php echo lang("edit_custom_rates"); ?> (<?php echo $user->email; ?>)</h4>
      <button type="button" class="close" data-dismiss="modal"></button>
    </div>
    <form class="actionForm" action="<?php echo cn($module.'/ajax_save_custom_rates/'.$user->id); ?>" method="POST">
      <div class="modal-body">
        <div class="row">
          <div class="col-md-12">
            <div class="form-group">
              <select name="service-id" class="select-service-item" class="form-control custom-select">
                <option value="1"><?php echo lang("add_custom_rate"); ?></option>
                <?php
                  if ($services) {
                    $currency_symbol = get_option('currency_symbol');
                    foreach ($services as $key => $service) {
                      $data_service = [
                        'service_id'          => $service->id,
                        'rate'                => (double)$service->price,
                        'original_rate'       => (double)$service->original_price,
                        'name'                => $service->name,
                      ];
                      $data_service = json_encode($data_service);
                      $service_name = $service->id . " - " . $service->name. " [" .$currency_symbol. (double)$service->price . "]";
                ?>
                <option value='<?php echo $data_service; ?>' data-rate="1"><?php echo $service_name; ?></option>
                <?php }} ?>
              </select>
            </div>
          </div>
        </div>
        
        <div class="o-auto" style="height: 30rem">

          <ul class="list-unstyled list-separated services-group-items">

            <div class="s-items">
              <?php
                if ($user_prices) {
                  foreach ($user_prices as $key => $row) {
              ?>
              <li class="list-separated-item s-item" id="item<?php echo $row->service_id?>">
                <div class="row align-items-center">
                  <div class="col"><strong><?php echo $row->service_id; ?></strong></div>
                  <div class="col-md-7"><?php echo $row->name; ?></div>
                  <div class="col-md-1">
                    <div>
                      <span><?php echo (double)$row->price?></span>
                      <small class="d-block item-except text-sm text-muted h-1x"><?php echo (double)$row->original_price?></small>
                    </div>
                  </div>
                  <div class="col-md-2">
                    <input type="hidden" name="customRates[<?php echo $row->service_id?>][uid]" value="<?php echo $user->id; ?>">
                    <input type="hidden" class="form-control" name="customRates[<?php echo $row->service_id?>][service_id]" value="<?php echo $row->service_id?>">
                    <input type="text" class="form-control" name="customRates[<?php echo $row->service_id?>][service_price]" value="<?php echo $row->service_price?>">
                  </div>
                  <div class="col-md-1">
                    <button class="btn btn-secondary btn-remove-item" type="button"><i class="fe fe-trash-2"></i></button>
                  </div>
                </div>
              </li>
              
              <?php }} ?>

            </div>

            <div class="s-item-more d-none">
              <li class="list-separated-item s-item" id="item__serviceID__">
                <div class="row align-items-center">
                  <div class="col"> <strong>__serviceID__</strong> </div>
                  <div class="col-md-7">__serviceName__ </div>
                  <div class="col-md-1">
                    <div>
                      <span>__serviceRate__</spane>
                      <small class="d-block item-except text-sm text-muted h-1x">__serviceOriginalRate__</small>
                    </div>
                  </div>
                  <div class="col-md-2">
                    <input type="hidden" name="customRates[__serviceID__][uid]" value="<?php echo $user->id; ?>">
                    <input type="hidden" class="form-control" name="customRates[__serviceID__][service_id]" value="__serviceID__">
                    <input type="text" class="form-control" name="customRates[__serviceID__][service_price]" value="__serviceRate__">
                  </div>
                  <div class="col-md-1">
                    <button class="btn btn-secondary btn-remove-item" type="button"><i class="fe fe-trash-2"></i></button>
                  </div>
                </div>
              </li>
            </div>
            
          </ul>
        </div>
       
      </div>
      <div class="card-footer text-right">
        <div class="d-flex">

          <a href="javascript:void(0)" class="btn btn-link btn-remove-items"><?php echo lang("delete_all"); ?></a>
          <button type="submit" class="btn btn-success btn-lg ml-auto mr-2"><?php echo lang("Save"); ?></button>
          <button type="button" class="btn btn-default btn-lg" data-dismiss="modal"><?php echo lang("Close"); ?></button>
        </div>
      </div>
    </form>
  </div>
</div>

<script>
  $(document).ready(function () {
    $('.select-service-item').selectize();

    $(document).on("change", ".select-service-item", function(){
      var _serviceItem = $('select[name=service-id] option').filter(':selected'),
          _serviceData = _serviceItem.val();

      if(_serviceData !== null && _serviceData !== 1) {
        _serviceData = JSON.parse(_serviceData);
        var _itemID = "item" + _serviceData.service_id;
        var _itemIdExists = document.getElementById(_itemID);
        if(!_itemIdExists){
          var extra_html = $(".services-group-items").find(".s-item-more").html();
          extra_html = extra_html.replace(/__serviceID__/gi, _serviceData.service_id);
          extra_html = extra_html.replace(/__serviceName__/gi, _serviceData.name);
          extra_html = extra_html.replace(/__serviceRate__/gi, _serviceData.rate);
          extra_html = extra_html.replace(/__serviceOriginalRate__/gi, _serviceData.original_rate);
          $(".services-group-items").find(".s-items").append(extra_html);
        }
      }
    })

    // Remove item
    $(".services-group-items").each(function() {
      var container = $(this);
      $(this).on('click', '.btn-remove-item', function() {
        $(this).closest(".s-item").remove();
      });
    });

    $(document).on("click", ".btn-remove-items", function(){
      if($('.services-group-items .s-items .s-item').length > 0){
        if(!confirm_notice('deleteItems')){
          return;
        }
        $('.services-group-items .s-items .s-item').remove();
      }
    })

  });
</script>
