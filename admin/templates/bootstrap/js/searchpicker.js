/**
 * @param options.searchPickerName page unique id for the kind of items to be searched for (e.g. 'customer', 'product')
 * @param options.getDataIoFuncName the Ajax function name that fetches the items to be searched for
 * @param options.keyName name of the property that denotes the key column of each item
 * @param options.renderItemCb callback function that gets an item object and returns the html content for its list item
 *      (function (item))
 * @param options.onApplyBefore callback that gets called *before* the selected items are returned through the onApply
 *      callback
 * @param options.onApply callback function that gets called on apply selection click with the current array of selected
 *      keys (function (selectedKeys, items))
 * @param options.selectedKeysInit array of the items keys that are initially selected
 * @constructor
 */
function SearchPicker(options)
{
    var searchPickerName   = options.searchPickerName;
    var getDataIoFuncName  = options.getDataIoFuncName;
    var keyName            = options.keyName;
    var renderItemCb       = options.renderItemCb;
    var onApplyBefore      = options.onApplyBefore || $.noop;
    var onApply            = options.onApply || $.noop;
    var selectedKeysInit   = options.selectedKeysInit || [];
    var self               = this;
    var searchString       = '';
    var lastSearchString   = '';
    var selectedKeys       = selectedKeysInit.slice();
    var backupSelectedKeys = [];
    var foundItems         = [];
    var dataIoFuncName     = getDataIoFuncName;
    var getRenderedItem    = renderItemCb;
    var closeAction        = '';
    var pendingRequest     = null;
    var $searchModal       = $('#' + searchPickerName + '-modal');
    var $searchResultList  = $('#' + searchPickerName + '-result-list');
    var $listTitle         = $('#' + searchPickerName + '-list-title');
    var $searchInput       = $('#' + searchPickerName + '-search-input');
    var $applyButton       = $('#' + searchPickerName + '-apply-btn');
    var $cancelButton      = $('#' + searchPickerName + '-cancel-btn');
    var $resetButton       = $('#' + searchPickerName + '-reset-btn');
    var $selectAllButton   = $('#' + searchPickerName + '-select-all-btn');
    var $unselectAllButton = $('#' + searchPickerName + '-unselect-all-btn');

    $(function () {
        $searchModal.on('show.bs.modal', self.onShow);
        $searchModal.on('hide.bs.modal', self.onHide);
        $searchInput.keyup(self.onChangeSearchInput);
        $applyButton.click(self.onApply);
        $cancelButton.click(self.onCancel);
        $resetButton.click(self.onResetSearchInput);
        $selectAllButton.click(self.selectAllShownItems.bind(self, true));
        $unselectAllButton.click(self.selectAllShownItems.bind(self, false));
        self.init();
    });

    self.init = function () {
        self.onResetSearchInput();
        self.updateItemList();
    };

    self.onShow = function ()
    {
        backupSelectedKeys = selectedKeys.slice();
    };

    self.onHide = function () {
        if (closeAction === 'apply') {
            onApplyBefore();
            ioCall(
                dataIoFuncName, [selectedKeys, 100],
                function (items) {
                    onApply(selectedKeys, items);
                    pendingRequest = null;
                }
            );
            self.init();
        } else if (closeAction === 'cancel') {
            selectedKeys = backupSelectedKeys.slice();
            self.init();
        }

        closeAction = 'cancel';
    };

    self.onApply = function ()
    {
        closeAction = 'apply';
    };

    self.onCancel = function ()
    {
        closeAction = 'cancel';
    };

    self.onResetSearchInput = function ()
    {
        $searchInput.val('');
        self.onChangeSearchInput();
    };

    self.onChangeSearchInput = function ()
    {
        searchString = $searchInput.val();

        if (searchString !== lastSearchString) {
            lastSearchString = searchString;
            self.updateItemList();
        }
    };

    self.selectAllShownItems = function (selected)
    {
        foundItems.forEach(function (item) {
            self.select(item[keyName], selected);
        });
    };

    self.updateItemList = function ()
    {
        $searchResultList.empty();
        $('<span>')
            .addClass('list-group-item')
            .html('<i class="fa fa-spinner fa-pulse"></i>')
            .appendTo($searchResultList);
        $listTitle.html('Suche...');

        if (searchString !== '') {
            if (pendingRequest !== null) {
                pendingRequest.abort();
            }

            pendingRequest = ioCall(dataIoFuncName, [searchString, 100], self.itemsReceived);
        } else if (selectedKeys.length > 0) {
            if (pendingRequest !== null) {
                pendingRequest.abort();
            }

            pendingRequest = ioCall(dataIoFuncName, [selectedKeys, 100, keyName], self.itemsReceived);
        } else {
            $searchResultList.empty();
            foundItems = [];
            self.updateListTitle();
        }
    };

    self.itemsReceived = function (items)
    {
        foundItems = items;
        self.updateListTitle();
        $searchResultList.empty();

        items.forEach(function (item) {
            var key      = item[keyName];
            var cleanKey = key.replace(/[^a-zA-Z0-9]/g, '-');

            $('<a>')
                .addClass('list-group-item' + (self.isSelected(key) ? ' active' : ''))
                .attr('id', searchPickerName + '-' + cleanKey)
                .css('cursor', 'pointer')
                .click(function () { self.select(key, !self.isSelected(key)); })
                .html(getRenderedItem(item))
                .appendTo($searchResultList);
        });

        pendingRequest = null;
    };

    self.updateListTitle = function ()
    {
        if (searchString !== '') {
            $listTitle.html('Gefundene Eintr&auml;ge: ' + foundItems.length);
        } else if (selectedKeys.length > 0) {
            $listTitle.html('Alle ausgew&auml;hlten Eintr&auml;ge: ' + selectedKeys.length);
        } else {
            $listTitle.html('Bisher sind keine Eintr&auml;ge ausgew&auml;hlt. Nutzen Sie die Suche!');
        }
    };

    self.select = function (key, selected)
    {
        var index    = selectedKeys.indexOf(key);
        var cleanKey = key.replace(/[^a-zA-Z0-9]/g, '-');

        if (selected) {
            $('#' + searchPickerName + '-' + cleanKey).addClass('active');

            if (index === -1) {
                selectedKeys.push(key);
            }
        } else {
            $('#' + searchPickerName + '-' + cleanKey).removeClass('active');

            if (index !== -1) {
                selectedKeys.splice(index, 1);
            }
        }

        self.updateListTitle();
    };

    self.isSelected = function (key)
    {
        return selectedKeys.indexOf(key) !== -1;
    };

    self.getSelection = function ()
    {
        return selectedKeys;
    };

    self.setSelection = function (newSelectedKeys)
    {
        selectedKeys = newSelectedKeys;
        return self;
    };

    self.setOnApplyBefore = function (newOnApplyBefore)
    {
        onApplyBefore = newOnApplyBefore;
        return self;
    };

    self.setOnApply = function (newOnApply)
    {
        onApply = newOnApply;
        return self;
    };

    self.show = function () {
        self.init();
        $searchModal.modal('show');
        return self;
    }
}
