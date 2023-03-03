<div class="page-header">
  <h1 class="page-title">
    <a href="<?=cn("$module/update")?>" class=""><span class="add-new" data-toggle="tooltip" data-placement="bottom" title="" data-original-title="<?=lang("add_new")?>"><i class="fa fa-plus-square text-primary" aria-hidden="true"></i></span></a> 
    <?=lang("users")?>
  </h1>
</div>

<div class="row" id="result_ajaxSearch">
  <?php if(!empty($users)){
  ?>
  <div class="col-md-12 col-xl-12">
    <div class="card">
      <div class="card-header">
        <h3 class="card-title"><?=lang("Lists")?></h3>
        <div class="card-options">
          <div class="dropdown">
            <button type="button" class="btn btn-outline-info  dropdown-toggle" data-toggle="dropdown">
               <i class="fe fe-upload mr-2"></i>Export
            </button>
            <div class="dropdown-menu">
              <a class="dropdown-item" href="<?=cn($module.'/export/excel')?>">Excel</a>
              <a class="dropdown-item" href="<?=cn($module.'/export/csv')?>">CSV</a>
            </div>
          </div>
        </div>
      </div>
      <div class="table-responsive">
        <table class="table table-hover table-bordered table-vcenter card-table">
          <thead>
            <tr>
              <th class="text-center w-1"><?=lang("No_")?></th>
              <?php if (!empty($columns)) {
                foreach ($columns as $key => $row) {
              ?>
              <th><?=$row?></th>
              <?php }}?>
              
              <?php
                if (!get_role("user")) {
              ?>
              <th class="text-center"><?=lang('Action')?></th>
              <?php }?>
            </tr>
          </thead>
          <tbody>
            <?php if (!empty($users)) {
              $i = 0;
              $currency_symbol = get_option('currency_symbol', '$');

              switch (get_option('currency_decimal_separator', 'dot')) {
                case 'dot':
                  $decimalpoint = '.';
                  break;
                case 'comma':
                  $decimalpoint = ',';
                  break;
                default:
                  $decimalpoint = '';
                  break;
              } 

              switch (get_option('currency_thousand_separator', 'comma')) {
                case 'dot':
                  $separator = '.';
                  break;
                case 'comma':
                  $separator = ',';
                  break;
                case 'space':
                  $separator = ' ';
                  break;
                default:
                  $separator = '';
                  break;
              }

              foreach ($users as $key => $row) {
              $i++;
            ?>
            <tr class="tr_<?=$row->ids?>">
              <td><?=$i?></td>
              <td>
                <div class="title"><h6><?php _echo($row->first_name) ." "._echo($row->last_name); ?></h6></div>
                <div class="sub"><small><?php _echo($row->email); ?></small></div>
                <div class="sub">
                  <small>
                    <?php
                      switch ($row->role) {
                        case 'admin':
                            echo lang("admin");
                          break;
                        case 'supporter':
                            echo lang("Supporter");
                          break;
                        default:
                          echo lang("regular_user");
                          break;
                      }
                    ?>
                  </small>
                </div>
              </td>
              <td>
                <?=(!empty($row->balance)) ? $currency_symbol." ".currency_format($row->balance, get_option('currency_decimal', 2), $decimalpoint, $separator) : 0?>
              </td>
              <td>
                <button type="button" class="btn btn-square btn-outline-info btn-sm btnEditCustomRate" data-action="<?php echo  cn($module.'/ajax_modal_custom_rates/'.$row->id); ?>"><i class="fe fe-plus mr-2"></i>Custom Rate</button>
              </td>
              <td><?=$row->desc?></td>
              <td><?=convert_timezone($row->created, 'user')?></td>
              
              <td class="w-1">
                <label class="custom-switch">
                  <input type="checkbox" name="item_status" data-id="<?php echo $row->id; ?>" data-action="<?php echo cn($module.'/ajax_toggle_item_status/'); ?>" class="custom-switch-input ajaxToggleItemStatus" <?php if(!empty($row->status) && $row->status == 1) echo 'checked'; ?>>
                  <span class="custom-switch-indicator"></span>
                </label>
              </td>

              <?php
                if (get_role("admin") || get_role('supporter')) {
              ?>
              <td class="text-center">
                <div class="item-action dropdown">
                  <a href="javascript:void(0)" data-toggle="dropdown" class="icon"><i class="fe fe-more-vertical"></i></a>
                  <div class="dropdown-menu">
                    <?php
                      if (get_role("admin")) {
                    ?>
                    <a class="dropdown-item" href="<?=cn("$module/update/".$row->ids)?>"><i class="dropdown-icon fe fe-edit"></i> <?=lang('edit')?>
                    </a>
                    <a class="dropdown-item ajaxViewUser" href="<?=cn("$module/view_user/".$row->ids)?>"><i class="dropdown-icon fe fe-eye"></i> <?=lang('view_user')?>
                    </a>
                    <a class="dropdown-item ajaxDeleteItem" href="<?=cn("$module/ajax_delete_item/".$row->ids)?>"><i class="dropdown-icon fe fe-trash"></i> <?=lang('Delete')?>
                    </a>
                    <?php }?>

                    <a class="dropdown-item ajaxModal" href="<?=cn("$module/mail/".$row->ids)?>">
                      <i class="dropdown-icon fe fe-mail"></i> <?=lang("send_mail")?>
                    </a>
                    

                    <a class="dropdown-item ajaxModal" href="<?=cn("$module/add_funds_manual/".$row->ids)?>">
                      <i class="dropdown-icon fe fe-dollar-sign"></i> <?=lang("Add_Funds")?>
                    </a>
                  </div>
                </div>
              </td>
              <?php }?>
            </tr>
            <?php }}?>
            
          </tbody>
        </table>
      </div>
    </div>
  </div>
  <div class="col-md-12">
    <div class="float-right">
      <?=$links?>
    </div>
  </div>
  <?php }else{
    echo Modules::run("blocks/empty_data");
  }?>
</div>

<div id="customRate" class="modal fade" role="dialog">
  <div class="modal-dialog modal-lg">
    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title"></i> Edit custom rates (ID: 1)</h4>
        <button type="button" class="close" data-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="row">
          <div class="col-md-12">
            <div class="form-group">
              <select name="service-id" class="select-service-item" class="form-control custom-select">
                <option value='{"service_id": "1189", "rate": "0.53", "name": "Instagram Likes [100 - 3K] [Instant] [Exclusive]"}' data-rate="1" data-data='{"rate": "0.53", "name": "Instagram Likes [100 - 3K] [Instant] [Exclusive]"}'>128 - Instagram Likes [100 - 3K] [Instant] [Exclusive] [$0.18]</option>
                <option value='{"service_id": "123", "rate": "0.78", "name": "Instagram Likes [100 - 3K] [Instant] [Exclusive]"}' data-rate="1" data-data='{"rate": "0.53", "name": "Instagram Likes [100 - 3K] [Instant] [Exclusive]"}'>123 - Instagram Likes [100 - 3K] [Instant] [Exclusive] [$0.18]</option>
              </select>
            </div>
          </div>
        </div>
        
        <div class="o-auto" style="height: 20rem">
          <ul class="list-unstyled list-separated services-group-items">

            <div class="s-items">
              <li class="list-separated-item s-item">
                <div class="row align-items-center">
                  <div class="col">
                    111
                  </div>
                  <div class="col-md-7">
                    Facebook [Real Relevant Comments - Custom Comments]
                  </div>
                  <div class="col-md-1">
                    0.53
                  </div>
                  <div class="col-md-2">
                    <input type="hidden" class="form-control" value="customRates[1123][price]">
                    <input type="text" class="form-control" >
                  </div>
                  <div class="col-md-1">
                    <button class="btn btn-secondary btn-remove-item" type="button"><i class="fe fe-trash-2"></i></button>
                  </div>
                </div>
              </li>
            </div>

            <div class="s-item-more d-none">
              <li class="list-separated-item s-item" id="item__serviceID__">
                <div class="row align-items-center">
                  <div class="col">
                    __serviceID__
                  </div>
                  <div class="col-md-7">
                    __serviceName__
                  </div>
                  <div class="col-md-1">
                    __serviceRate__
                  </div>
                  <div class="col-md-2">
                    <input type="hidden" class="form-control" value="customRates[__serviceID__][rate_id]">
                    <input type="number" class="form-control" value="customRates[__serviceID__][price]">
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
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
      </div>
    </div>

  </div>
</div>
