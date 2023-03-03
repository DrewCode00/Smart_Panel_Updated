    <div class="sidebar o-auto">

      <div class="list-group list-group-transparent mb-0">
        <h5><span class="icon mr-3"><i class="fe fe-disc"></i></span><?=lang("general_settings")?></h5>
      </div>
      <div class="list-group list-group-transparent mb-0">
        <a href="<?php echo cn($module."/website_setting")?>" class="list-group-item list-group-item-action <?php echo (segment(2) == 'website_setting') ? 'active' : ''?>"><span class="icon mr-3"><i class="fe fe-globe"></i></span><?=lang("website_setting")?></a>

        <a href="<?php echo cn($module."/website_logo")?>" class="list-group-item list-group-item-action <?php echo (segment(2) == 'website_logo') ? 'active' : ''?>"><span class="icon mr-3"><i class="fe fe-image"></i></span><?=lang("Logo")?></a>

        <a href="<?php echo cn($module."/cookie_policy")?>" class="list-group-item list-group-item-action <?php echo (segment(2) == 'cookie_policy') ? 'active' : ''?>"><span class="icon mr-3"><i class="fe fe-bookmark"></i></span><?php echo lang("cookie_policy"); ?></a>

        <a href="<?php echo cn($module."/terms_policy")?>" class="list-group-item list-group-item-action <?php echo (segment(2) == 'terms_policy') ? 'active' : ''?>"><span class="icon mr-3"><i class="fe fe-award"></i></span><?=lang("terms__policy_page")?></a>

        <a href="<?php echo cn($module."/default")?>" class="list-group-item list-group-item-action <?php echo (segment(2) == 'default') ? 'active' : ''?>"><span class="icon mr-3"><i class="fe fe-box"></i></span><?=lang("default_setting")?></a>

        <a href="<?php echo cn($module."/currency")?>" class="list-group-item list-group-item-action <?php echo (segment(2) == 'currency') ? 'active' : ''?>"><span class="icon mr-3"><i class="fe fe-dollar-sign"></i></span><?=lang("currency_setting")?></a>

        <a href="<?php echo cn($module."/other")?>" class="list-group-item list-group-item-action <?php echo (segment(2) == 'other') ? 'active' : ''?>"><span class="icon mr-3"><i class="fe fe-command"></i></span><?=lang("Other")?></a>
      </div>

      <div class="list-group list-group-transparent mb-0 mt-5">
        <h5><span class="icon mr-3"><i class="fe fe-disc"></i></span><?=lang("Email")?></h5>
      </div>
      <div class="list-group list-group-transparent mb-0">
        <a href="<?php echo cn($module."/smtp")?>" class="list-group-item list-group-item-action <?php echo (segment(2) == 'smtp') ? 'active' : ''?>"><span class="icon mr-3"><i class="fe fe-mail"></i></span><?=lang("email_setting")?></a>

        <a href="<?php echo cn($module."/template")?>" class="list-group-item list-group-item-action <?php echo (segment(2) == 'template') ? 'active' : ''?>"><span class="icon mr-3"><i class="fe fe-file-text"></i></span><?=lang("email_template")?></a>
      </div>

      <div class="list-group list-group-transparent mb-0 mt-5">
        <h5><span class="icon mr-3"><i class="fe fe-disc"></i></span><?=lang("integrations")?></h5>
      </div>
      <div class="list-group list-group-transparent mb-0">
        <a href="<?php echo cn($module."/payment")?>" class="list-group-item list-group-item-action <?php echo (segment(2) == 'payment') ? 'active' : ''?>"><span class="icon mr-3"><i class="fe fe-alert-triangle"></i></span><?=lang("Payment")?></a>
      </div>

    </div>
