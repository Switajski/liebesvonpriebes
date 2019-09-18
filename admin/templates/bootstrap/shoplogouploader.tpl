{config_load file="$lang.conf" section='shoplogouploader'}
{include file='tpl_inc/header.tpl'}
{include file='tpl_inc/seite_header.tpl' cTitel=#shoplogouploader# cBeschreibung=#shoplogouploaderDesc# cDokuURL=#shoplogouploaderURL#}
<div id="content" class="container-fluid">
    <form name="uploader" method="post" action="shoplogouploader.php" enctype="multipart/form-data">
        {$jtl_token}
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">Ihr Logo</h3>
            </div>
            <div class="panel-body">
                <input type="hidden" name="upload" value="1" />
                <div class="col-xs-12">
                    <input name="shopLogo" id="shoplogo-upload" type="file" class="file" accept="image/*">
                    <script>
                        $('#shoplogo-upload').fileinput({ldelim}
                            uploadUrl: '{$shopURL}/{$PFAD_ADMIN}shoplogouploader.php?token={$smarty.session.jtl_token}',
                            allowedFileExtensions : ['jpg', 'jpeg', 'jpe', 'gif', 'png', 'bmp'],
                            overwriteInitial: true,
                            deleteUrl: '{$shopURL}/{$PFAD_ADMIN}shoplogouploader.php?token={$smarty.session.jtl_token}',
                            initialPreviewCount: 1,
                            uploadAsync: false,
                            showPreview: true,
                            language: 'de',
                            maxFileSize: 100000,
                            maxFilesNum: 1{if $ShopLogo|strlen > 0}, initialPreview: [
                                '<img src="{$ShopLogoURL}" class="file-preview-image" alt="Logo" title="Logo" />'
                            ],
                            initialPreviewConfig: [
                                {ldelim}
                                    url: '{$shopURL}/{$PFAD_ADMIN}shoplogouploader.php',
                                    extra: {ldelim}logo: '{$ShopLogo}'{rdelim}
                                {rdelim}
                            ]
                            {/if}
                        {rdelim}).on('fileuploaded', function(event, data) {ldelim}
                            if (data.response.status === 'OK') {ldelim}
                                $('#logo-upload-success').show().removeClass('hidden');
                                $('.kv-upload-progress').addClass('hide');
                            {rdelim} else {ldelim}
                                $('#logo-upload-error').show().removeClass('hidden');
                            {rdelim}
                        {rdelim});
                    </script>
                    <div id="logo-upload-success" class="alert alert-info hidden">Logo erfolgreich hochgeladen.</div>
                    <div id="logo-upload-error" class="alert alert-danger hidden">Logo konnte nicht hochgeladen werden.</div>
                </div>
            </div>
        </div>
    </form>
</div>
{include file='tpl_inc/footer.tpl'}