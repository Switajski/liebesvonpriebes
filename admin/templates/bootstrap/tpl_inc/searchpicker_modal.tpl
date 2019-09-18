{*
    Parameters:
        searchPickerName - page unique id for the search picker instance (e.g. 'customer', 'product')
        modalTitle - the modal dialogs title
        searchInputLabel - the caption for the search input field
*}
<div class="modal fade" id="{$searchPickerName}-modal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">
                    <i class="fa fa-times"></i>
                </button>
                <h4 class="modal-title">{$modalTitle}</h4>
            </div>
            <div class="modal-body">
                <div class="input-group">
                    <label for="{$searchPickerName}-search-input" class="sr-only">
                        {$searchInputLabel}:
                    </label>
                    <input type="text" class="form-control" id="{$searchPickerName}-search-input" placeholder="Suche"
                           autocomplete="off">
                    <span class="input-group-btn">
                        <button type="button" class="btn btn-default" id="{$searchPickerName}-reset-btn"
                                title="Eingabe l&ouml;schen">
                            <i class="fa fa-eraser"></i>
                        </button>
                    </span>
                </div>
                <h5 id="{$searchPickerName}-list-title"></h5>
                <div class="list-group" id="{$searchPickerName}-result-list" style="max-height:500px;overflow:auto;">
                </div>
                <div class="btn-group">
                    <button type="button" class="btn btn-xs btn-primary" id="{$searchPickerName}-select-all-btn">
                        <i class="fa fa-check-square-o"></i>
                        {#searchpickerSelectAllShown#}
                    </button>
                    <button type="button" class="btn btn-xs btn-danger" id="{$searchPickerName}-unselect-all-btn">
                        <i class="fa fa-square-o"></i>
                        {#searchpickerUnselectAllShown#}
                    </button>
                </div>
            </div>
            <div class="modal-footer">
                <div class="btn-group">
                    <button type="button" class="btn btn-danger" data-dismiss="modal" id="{$searchPickerName}-cancel-btn">
                        <i class="fa fa-times"></i>
                        {#cancel#}
                    </button>
                    <button type="button" class="btn btn-primary" data-dismiss="modal" id="{$searchPickerName}-apply-btn">
                        <i class="fa fa-save"></i>
                        {#apply#}
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>