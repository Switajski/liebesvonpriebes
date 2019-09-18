{include file='tpl_inc/header.tpl'}
{include file='tpl_inc/seite_header.tpl' cTitel="Favoriten" cBeschreibung="Verwalten Sie Ihre Favoriten"}

<script type="text/javascript">
    function addItem() {
        var last = $("#favs tbody tr:last-child");
        var title = last.find('input[name="title[]"]').val();
        var url = last.find('input[name="url[]"]').val();
        if (title.length > 0 || url.length > 0) {
            var next = last.clone();
            next.find('input').val('');
            $("#favs tbody").append(next);
        }
    }

    $(function() {
        $("#favs tbody").sortable({
            placeholder: "ui-state-highlight"
        });

        $("#favs tbody").disableSelection();

        $("body").on('click', "#favs tbody button.btn-remove", function() {
            var cnt = $("#favs tbody tr").length;
            if (cnt > 1) {
                $(this)
                    .closest('tr')
                    .remove();
            }
            else {
                $(this)
                    .closest('tr')
                    .find('input')
                    .val('');
            }
        });

        $("body").on('change keyup', "#favs tbody input", function() {
            addItem();
        });

        addItem();
    });
</script>

{function fav_item title='' url=''}
    <tr class="text-vcenter">
        <td class="text-left">
            <input class="form-control" type="text" name="title[]" value="{$title}">
        </td>
        <td class="text-left">
            <input class="form-control" type="text" name="url[]" value="{$url}">
        </td>
        <th class="text-muted text-center" scope="row">
            <i class="fa fa-arrows-v" aria-hidden="true"></i>
        </th>
        <td class="text-center">
            <button type="button" class="btn btn-xs btn-danger btn-remove"><i class="fa fa-times"></i></button>
        </td>
    </tr>
{/function}

<div id="content" class="container-fluid">
    <form method="POST">
        <div class="panel panel-default">
            <div class="table-responsive">
                <table class="list table table-hover" id="favs">
                    <thead>
                    <tr>
                        <th class="text-left">Titel</th>
                        <th class="text-left">Link</th>
                        <th width="30"></th>
                        <th width="50"></th>
                    </tr>
                    </thead>
                    <tbody>
                        {foreach $favorites as $favorite}
                            {fav_item title=$favorite->cTitel url=$favorite->cUrl}
                        {foreachelse}
                            {fav_item title='' url=''}
                        {/foreach}
                    </tbody>
                </table>
            </div>
            <div class="panel-footer">
                <div class="save btn-group">
                    <button type="submit" class="btn btn-success">Aktualisieren</button>
                </div>
            </div>
        </div>
    </form>
</div>

{include file='tpl_inc/footer.tpl'}