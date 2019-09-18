<div class="modal modal-center fade" id="ppp-modal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-body">
                <h2 id="pp-loading-body"><i class="fa fa-spinner fa-spin fa-fw"></i> Ihre Bestellung wird abgeschlossen</h2>
            </div>
        </div>
    </div>
</div>

<script>
$(function() {
    var submitted = false;
    // $(window).bind('unload', function (e) { console.log('unload'); return false; });
    //$(window).bind('beforeunload', function (e) { console.log('beforeunload', submitted); return submitted; });

    $('#complete_order').on('submit', function() {
        submitted = true;

        $(this).find('input[type="submit"]')
            .addClass('disabled')
            .attr('disabled', true);

        $('#ppp-modal').modal({
            backdrop: 'static'
        });

        $('#ppp-modal').modal('show');

        return true;
    });
});
</script>