{include file='tpl_inc/header.tpl'}
{include file='tpl_inc/einstellungen_bearbeiten.tpl'}
{if $createIndex !== false}
    <script type="text/javascript">
        var createIndex = '{$createIndex}';
        var createCount = 0;
    </script>
    <script type="text/javascript">
        function showIndexNotification(pResult) {
            var type = 'info';
            var msg  = '';

            if (pResult && pResult.error) {
                type = 'danger';
                msg  = pResult.error.message;
            } else if (pResult && pResult.hinweis) {
                msg  = pResult.hinweis;
                createCount++;
            } else {
                return null;
            }

            createNotify({
                title: 'Volltextsuche verwenden',
                message: msg
            }, {
                type: type
            });

            if (createCount >= 2) {
                $('.alert.alert-danger').hide(300);
                updateNotifyDrop();
                ioCall('clearSearchCache', [], showCacheNotification, showCacheNotification);
            }
        }

        function showCacheNotification(pResult) {
            var isError = pResult && pResult.error;
            createNotify({
                title: 'Sucheinstellungen &auml;ndern',
                message: isError ? pResult.error.message : pResult.hinweis
            }, {
                type: isError ? 'danger' : 'info'
            });
        }

        ioCall('createSearchIndex', ['tartikel', createIndex], showIndexNotification, showIndexNotification);
        ioCall('createSearchIndex', ['tartikelsprache', createIndex], showIndexNotification, showIndexNotification);
    </script>
{/if}
{include file='tpl_inc/footer.tpl'}