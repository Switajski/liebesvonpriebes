<button type="button" class="btn btn-primary" data-toggle="modal" data-target="#tpl-configurator-modal" id="tpl-configurator-show">
    {$tpl_config_lang_vars.btn_open}
</button>

<div class="modal fade template-configurator" id="tpl-configurator-modal" tabindex="-1" role="dialog"
     aria-labelledby="tplConfiguratorLabel" data-backdrop="false">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title" id="tplConfiguratorLabel">LiveStyler</h4>
            </div>
            <div class="modal-body">
                <form id="tpl-configurator-edit-form" enctype="multipart/form-data" method="post" action="{$shop_url}/">
                    {$jtl_token}
                    <input type="hidden" value="1" name="tpl_config_save_export"/>
                    <input type="hidden" value="{$template_dir}" name="template_dir"/>
                    <input type="hidden" value="{$theme}" name="theme"/>
                    <div class="settings tabber modern" id="css-options">
                        {foreach from=$less_vars key=optionName item=optionValue}
                            <div class="input-group{if $optionValue.type === 'colorpicker'} colorpicker-element{/if}">
                                <div class="input-group-addon">
                                    <label for="input-{$optionValue.name}">{$optionName}</label>
                                </div>
                                <input class="form-control" id="input-{$optionValue.name}" type="text"
                                       name="input-{$optionValue.name}" value="{$optionValue.value}"/>
                                {if $optionValue.type === 'colorpicker'}
                                    <span class="colorpicker-component input-group-addon"><i></i></span>
                                {/if}
                            </div>
                        {/foreach}
                    </div>
                    <div class="{if $is_admin}btn-group {/if}tpl-save-wrapper">
                        <button type="submit" class="btn btn-primary button add" id="tpl-configurator-submit" value="0">
                            <i class="fa fa-exchange"></i> <span>{$tpl_config_lang_vars.btn_apply}</span>
                        </button>
                        <button type="submit" class="btn btn-default button add{if !$is_admin} disabled{/if}"
                                id="tpl-configurator-export" value="1">
                            <i class="fa fa-save"></i> <span>{$tpl_config_lang_vars.btn_save}</span>
                        </button>
                    </div>
                </form>
                <div id="tpl-config-msg-placeholder"></div>
            </div>
        </div>
    </div>
</div>
