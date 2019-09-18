{config_load file="$lang.conf" section="dbupdater"}
{config_load file="$lang.conf" section="shopupdate"}
{include file='tpl_inc/header.tpl'}

{include file='tpl_inc/seite_header.tpl' cTitel=#dbupdater# cBeschreibung=#dbupdaterDesc# cDokuURL=#dbupdaterURL#}
<div id="content" class="container-fluid">
    <div class="container-fluid2">
        <div id="update-status">
            {include file='tpl_inc/dbupdater_status.tpl'}
        </div>
    </div>
</div>

<script>
    var adminPath = '{$PFAD_ADMIN}';
    {literal}

    function backup($element)
    {
        disableUpdateControl(true);

        var url = $element.attr('href'),
            download = !!$element.data('download');

        pushEvent('Starte Sicherungskopie');
        ioManagedCall(
            adminPath, 'dbupdaterBackup', [],
            function (result, error) {
                disableUpdateControl(false);

                var message = error
                    ? 'Sicherungskopie konnte nicht erstellt werden'
                    : (download
                        ? 'Sicherungskopie "<strong>' + result.file + '</strong>" wird heruntergeladen'
                        : 'Sicherungskopie "<strong>' + result.file + '</strong>" wurde erfolgreich erstellt');

                showNotify(error ? 'danger' : 'success', 'Sicherungskopie', message);
                pushEvent(message);

                if (!error && download) {
                    ioDownload('dbupdaterDownload', [result.file]);
                }
            }
        );
    }

    function doUpdate(callback)
    {
        ioManagedCall(
            adminPath, 'dbUpdateIO', [],
            function (result, error) {
                if (!error) {
                    callback(result);

                    if (result.availableUpdate) {
                        doUpdate(callback);
                    }
                } else {
                    callback(undefined, error);
                }
            }
        );
    }

    function update($element)
    {
        var url = $element.attr('href');

        disableUpdateControl(true);
        pushEvent('Starte Update');

        doUpdate(function(data, error) {
            var _once = function() {
                var message = error
                        ? 'Update wurde angehalten: ' + error.message
                        : 'Update wurde erfolgreich durchgef&uuml;hrt'

                showNotify(error ? 'danger' : 'success', 'Update', message);
                disableUpdateControl(false);
            };

            if (error) {
                pushEvent('Fehler bei Update: ' + error.message);
                _once();
            }
            else {
                pushEvent('     Update auf ' + formatVersion(data.result) + ' erfolgreich');
                if (!data.availableUpdate) {
                    //pushEvent('Update beendet');
                    updateStatusTpl();
                    _once();
                }
            }
        });
    }

    function updateStatusTpl()
    {
        ioManagedCall(adminPath, 'dbupdaterStatusTpl', [], function(result, error) {
            if (error) {
                pushEvent(error.message);
            } else {
                $('#update-status').html(result.tpl);
                init_bindings();
            }
        });

        // update notifications
        updateNotifyDrop();
    }

    function toggleDirection($element)
    {
        $element.parent()
                .children()
                .attr('disabled', false)
                .toggle();
    }

    /**
     * @deprecated since 4.06
     * @param $element
     */
    function migrate($element)
    {
        var url = $element.attr('href'),
            $ladda = Ladda.create($('#migrate-button')[0]);

        $ladda.start();

        ajaxManagedCall(url, {}, function(result, error) {
            var count = error
                ? 0 : (typeof result.data.migrations === 'object'
                    ? result.data.migrations.length : 0);
            var message = error
                    ? error.message
                    : '<strong>' + count + '</strong> Migrations wurden ausgef&uuml;hrt';

            $ladda.stop();
            updateStatusTpl();
            showNotify(error ? 'danger' : 'success', 'Migration', message);
        });
    }

    function migration($element)
    {
        var id = $element.data('id'),
            url = $element.attr('href'),
            dir = $element.data('dir'),
            params = {dir: dir};

        $element.attr('disabled', true);

        if (id !== undefined) {
            params = $.extend({}, { id: id }, params);
        }

        ioManagedCall(adminPath, 'dbupdaterMigration', [id, null, dir], function(result, error) {
            $element
                .attr('disabled', false)
                .closest('tr')
                .find('.migration-created')
                .fadeOut();

            if (!error) {
                toggleDirection($element);
            }

            var message = error
                ? error.message
                : 'Migration wurde erfolgreich ausgef&uuml;hrt';

            showNotify(error ? 'danger' : 'success', 'Migration', message);

            if (!error) {
                updateStatusTpl();
            }
        });
    }

    /**
     * @deprecated since 4.06
     * @param url
     * @param params
     * @param callback
     */
    function ajaxManagedCall(url, params, callback)
    {
        ajaxCall(url, params, function(result, xhr) {
            if (xhr && xhr.error && xhr.error.code === 401) {
                createNotify({
                    title: 'Sitzung abgelaufen',
                    message: 'Sie werden zur Anmelde-Maske weitergeleitet...',
                    icon: 'fa fa-lock'
                }, {
                    type: 'danger',
                    onClose: function() {
                        window.location.pathname = '/' + adminPath + 'index.php';
                    }
                });
            } else if (typeof callback === 'function') {
                callback(result, result.error);
            }
        });
    }

    function pushEvent(message)
    {
        $('#debug').append($('<div/>').html(message));
    }

    function formatVersion(version)
    {
        var v = parseInt(version);
        if (v >= 300 && v < 500) {
            return v / 100;
        }
        return version;
    }

    function disableUpdateControl(disable)
    {
        var $container = $('#btn-update-group'),
            $buttons = $('#btn-update-group a.btn'),
            $ladda = Ladda.create($('#backup-button')[0]);

        if (!!disable) {
            $ladda.start();
            $buttons.attr('disabled', true);
        } else {
            $ladda.stop();
            $buttons.attr('disabled', false);
        }
    }

    function init_bindings()
    {
        $('[data-callback]').click(function(e) {
            e.preventDefault();
            var $element = $(this);
            if ($element.attr('disabled') !== undefined) {
                return false;
            }
            var callback = $element.data('callback');
            if (!$(e.target).attr('disabled')) {
                window[callback]($element);
            }
        });
    }

    $(function() {
        init_bindings();
    });

    {/literal}
</script>

{include file='tpl_inc/footer.tpl'}
