{include file='tpl_inc/seite_header.tpl' cTitel=#plz_ort_import# cBeschreibung=#plz_ort_importDesc#}
<div id="content">
    <div class="boxWrapper row">
        <div class="boxLeft col-md-12">
            <div class="panel panel-default">
                <form id="importForm" action="/plz_ort_import.php">
                    {$jtl_token}
                    <div class="panel-heading">
                        <h3>{#plz_ort_available#}</h3>
                    </div>
                    <div class="panel-body">
                        {include file='tpl_inc/plz_ort_import_index_list.tpl'}
                    </div>
                    <div class="boxOptionRow panel-footer">
                        <a href="#" class="btn btn-primary" data-callback="plz_ort_import_new"><i class="fa fa-download"></i> {#plz_ort_import_new#}</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<div id="modalWait" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4>{#plz_ort_import_load#} <img src="/admin/templates/bootstrap/gfx/widgets/ajax-loader.gif"></h4>
            </div>
        </div>
    </div>
</div>
<div id="modalTempImport" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header alert-warning">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4><i class="fa fa-warning"></i> {#plz_ort_import#}</h4>
            </div>
            <div class="modal-body">
                {#plz_ort_import_tmp_exists#}
            </div>
            <div class="modal-footer">
                <a href="#" class="btn btn-default" data-dismiss="modal"><i class="fa fa-exclamation"></i> {#plz_ort_import_delete_no#}</a>
                <a href="#" class="btn btn-primary" data-callback="plz_ort_import_delete_temp" data-dismiss="modal"><i class="fa fa-trash"></i> {#plz_ort_import_delete_yes#}</a>
            </div>
        </div>
    </div>
</div>
<div id="modalHelp" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4><i class="fa fa-question-circle"></i> {#plz_ort_import#}</h4>
            </div>
            <div class="modal-body">
                {#plz_ort_import_help#|sprintf:$smarty.const.PLZIMPORT_URL}
            </div>
            <div class="modal-footer">
                <a href="#" class="btn btn-primary" data-dismiss="modal"><i class="fa fa-check"></i> {#plz_ort_import_ok#}</a>
            </div>
        </div>
    </div>
</div>
<script type="application/javascript">{literal}

    var jtlToken  = $('#importForm .jtl_token').val(),
        running   = false,
        notify    = null,
        startTick = null;

    var shortGermanHumanizer = humanizeDuration.humanizer({
        round: true,
        delimiter: ' ',
        units: ['h', 'm', 's'],
        language: 'shortDE',
        languages: {
            shortDE: {
                h: function () {
                    return 'Std'
                },
                m: function () {
                    return 'Min'
                },
                s: function () {
                    return 'Sek'
                }
            }
        }
    });

    function showModalWait() {
        var $modalWait = $("#modalWait");
        $modalWait.modal({backdrop: false});

        return $modalWait;
    }

    function showImportNotify(title, message) {
        return createNotify({
            title: title,
            message: message
        }, {
            allow_dismiss: true,
            showProgressbar: true,
            delay: 0,
            onClose: function () {
                stopImport();
                updateIndex();
            }
        });
    }

    function stopImport() {
        if (running) {
            $('[data-callback]').attr('disabled', false);
            running = false;
        }
    }

    function updateIndex() {
        $('[data-callback]').attr('disabled', true);
        ioCall('plzimportActionUpdateIndex', [], function(result) {
            if (result) {
                $('#importForm .panel-body').html(result.listHTML);
            }
            $('[data-callback]').attr('disabled', false);
        });
    }

    function refreshNotify() {
        if (running) {
            ioCall('plzimportActionCallStatus', [], function(result) {
                if (result && result.running) {
                    var offsetTick = new Date().getTime() - startTick,
                        perItem    = Math.floor(offsetTick / result.step),
                        eta        = Math.max(0, Math.ceil((100 - result.step) * perItem)),
                        readable   = shortGermanHumanizer(eta);

                    notify.update({
                        progress: result.step,
                        message: result.status + ' (' + readable + ' verbleiben)'
                    });

                    window.setTimeout(refreshNotify, 1500);
                } else {
                    window.setTimeout(function(){
                        notify.close();
                    }, 3000);
                }
            });
        }
    }

    function startImport(ref, part) {
        $('[data-callback]').attr('disabled', true);
        part      = part || '';
        running   = true;
        startTick = new Date();
        notify    = showImportNotify('PLZ-Orte Import', 'Import wird gestartet...');

        var callback = function(result) {
            stopImport();
            updateIndex();
            notify.update({
                progress: 100,
                message: '&nbsp;',
                type: result ? result.type : 'danger',
                title: result ? result.message : 'Ups...'
            });
            window.setTimeout(function(){
                notify.close();
            }, 3000);
        };

        window.setTimeout(refreshNotify, 1500);
        ioCall('plzimportActionDoImport', [ref, part], callback, function(result) {
            ioCall('plzimportActionResetImport', ['danger', 'Fehler beim Import... Import abgebrochen!'], callback);
        });
    }

    function startBackup(ref) {
        var $modalWait = showModalWait();
        ioCall('plzimportActionRestoreBackup', [ref], function(result) {
            $modalWait.modal('hide');
            updateIndex();
        });
    }

    function checkRunning() {
        ioCall('plzimportActionCheckStatus', [], function(result) {
            if (result) {
                if (result.running) {
                    $('[data-callback]').attr('disabled', true);
                    running   = true;
                    startTick = new Date();
                    startTick.setTime(result.start);
                    notify = showImportNotify('PLZ-Orte Import', 'Import wird gestartet...');

                    refreshNotify();
                } else if (result.tmp > 0) {
                    plz_ort_import_exists();
                }
            }
        });
    }

    function plz_ort_import_exists() {
        showBackdrop();
        var $modal = $('#modalTempImport');
        $modal.on('hide.bs.modal', function () {
            hideBackdrop();
        });
        $modal.modal({backdrop: false});
    }

    function plz_ort_import_delete_temp() {
        notify = showImportNotify('PLZ-Orte Import', 'Tempor&auml;rer Import wird gel&ouml;scht...');
        ioCall('plzimportActionDelTempImport', [], function(result) {
            notify.update({
                progress: 100,
                message: '&nbsp;',
                type: result ? result.type : 'danger',
                title: result ? result.message : 'Ups...'
            });
            window.setTimeout(function(){
                notify.close();
            }, 3000);
        });
    }

    function plz_ort_import_new($el) {
        showBackdrop();
        var $modal = $('#modalSelect');
        if ($modal.length === 0) {
            var $modalWait = showModalWait();
            ioCall('plzimportActionLoadAvailableDownloads', [], function (result) {
                $modal = $(result.dialogHTML);
                $modal.on('hide.bs.modal', function () {
                    hideBackdrop();
                });
                $modalWait.one('hidden.bs.modal', function () {
                    $modal.modal({backdrop: false});
                }).modal('hide');
            });
        } else {
            $modal.modal({backdrop: false});
        }
    }

    function plz_ort_import($el) {
        var ref = $el.data('ref');
        $('#modalSelect').modal('hide');
        startImport(ref);
    }

    function plz_ort_import_refresh($el) {
        var ref = $el.data('ref');
        startImport(ref, 'import');
    }

    function plz_ort_import_reset($el) {
        var ref = $el.data('ref');
        startBackup(ref);
    }

    $(function () {
        $('#content_wrapper > .content-header p.description').append(
                '<a href="#modalHelp" data-toggle="modal" data-backdrop="false"><i class="fa fa-question-circle"></i></a>'
        );
        $('#modalHelp').on('show.bs.modal', function(){
            showBackdrop();
        }).on('hide.bs.modal', function(){
            hideBackdrop();
        });

        $(document).on('click', '[data-callback]', function (e) {
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

        checkRunning();
    });
</script>{/literal}