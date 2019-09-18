<hr>
<div class="panel panel-default">
    <div class="panel-heading">
        <h3 class="panel-title">Revisionen</h3>
    </div>
    <div class="panel-body">
        {if $revisions|count > 0}
            {if !empty($data)}
                {if $secondary === true}
                    {foreach $data as $foreignKey => $localized}
                        {foreach $show as $attribute}
                            <div class="hidden" id="original-{$attribute|escape}-{$foreignKey}">{if isset($localized->$attribute)}{$localized->$attribute|escape}{elseif is_string($localized)}{$localized|escape}{/if}</div>
                        {/foreach}
                    {/foreach}
                {else}
                    {foreach $show as $attribute}
                        <div class="hidden" id="original-{$attribute|escape}" class="original" data-references="{$attribute|escape}">{$data->$attribute|escape}</div>
                    {/foreach}
                {/if}
            {/if}
            <div class="panel-group" id="accordion" role="tablist" aria-multiselectable="true">
                {foreach $revisions as $revision}
                    <div class="panel panel-default">
                        <div class="panel-heading" role="tab" data-idx="{$revision@iteration}" id="heading-revision-{$revision@iteration}">
                            <h4 class="panel-title">
                                <a data-toggle="collapse" data-parent="#accordion" href="#revision-{$revision@iteration}" aria-expanded="true" aria-controls="profile-{$revision@iteration}">
                                    <span class="badge left">{$revision->timestamp}</span> {$revision->author}
                                </a>
                            </h4>
                        </div>
                        <div id="revision-{$revision@iteration}" data-idx="{$revision@iteration}" class="panel-collapse collapse" role="tabpanel" aria-labelledby="heading-revision-{$revision@iteration}">
                            <div class="panel-body">
                                <div class="list-group revision-content">
                                    {if $secondary === true && isset($revision->content->references)}
                                        {foreach $revision->content->references as $secondaryKey => $ref}
                                            {foreach $show as $attribute}
                                                {if isset($ref->$attribute)}
                                                    <h4>{$attribute|escape} ({$secondaryKey}):</h4>
                                                    <div id="diff-{$revision@iteration}-{$attribute|escape}-{$secondaryKey}"></div>
                                                    <div class="hidden" data-references="{$attribute|escape}" data-references-secondary="{$secondaryKey}">{$ref->$attribute|escape|utf8_decode}</div>
                                                {/if}
                                            {/foreach}
                                        {/foreach}
                                    {else}
                                        {foreach $show as $attribute}
                                            {if isset($revision->content->$attribute)}
                                                <h4>{$attribute|escape}</h4>
                                                <div id="diff-{$revision@iteration}-{$attribute|escape}"></div>
                                                <div class="hidden" data-references="{$attribute|escape}" data-references-secondary="">{$revision->content->$attribute|escape|utf8_decode}</div>
                                            {/if}
                                        {/foreach}
                                    {/if}
                                </div>
                            </div>
                            <div class="panel-footer">
                                <form class="restore-revision" method="post">
                                    {$jtl_token}
                                    <input type="hidden" value="{$revision->id}" name="revision-id" />
                                    <input type="hidden" value="{$revision->type}" name="revision-type" />
                                    <input type="hidden" value="{if $secondary === true}1{else}0{/if}" name="revision-secondary" />
                                    <span class="btn-group">
                                        <button type="submit" class="btn btn-primary" name="revision-action" value="restore"><i class="fa fa-refresh"></i> Revision wiederherstellen</button>
                                        <button type="submit" class="btn btn-danger" name="revision-action" value="delete"><i class="fa fa-trash"></i> Revision l&ouml;schen</button>
                                    </span>
                                </form>
                            </div>
                        </div>
                    </div>
                {/foreach}
            </div>
        {else}
            <div class="alert alert-info">Keine Revisionen vorhanden.</div>
        {/if}
    </div>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/diff_match_patch/20121119/diff_match_patch.js"></script>
<script src="{$PFAD_CODEMIRROR}addon/merge/merge.js"></script>
<link rel="stylesheet" type="text/css" href="{$PFAD_CODEMIRROR}addon/merge/merge.css" />

{literal}
<script type="text/javascript">

    /**
     * @param target
     * @param original
     * @param modified
     */
    function initUI(target, original, modified) {
        target.innerHTML = '';
        dv = CodeMirror.MergeView(target, {
            highlightDifferences: true,
            collapseIdentical:    false,
            revertButtons:        false,
            value:                modified,
            origLeft:             null,
            orig:                 original,
            lineNumbers:          true,
            mode:                 'smartymixed',
            connect:              null
        });
    }

    /**
     * @param mergeView
     * @returns {number}
     */
    function mergeViewHeight(mergeView) {
        function editorHeight(editor) {
            if (!editor) {
                return 0;
            }
            return editor.getScrollInfo().height;
        }
        return Math.max(editorHeight(mergeView.leftOriginal()),
                editorHeight(mergeView.editor()),
                editorHeight(mergeView.rightOriginal()));
    }

    /**
     * @param mergeView
     */
    function resize(mergeView) {
        var height = mergeViewHeight(mergeView),
            newHeight;
        for(;;) {
            if (mergeView.leftOriginal()) {
                mergeView.leftOriginal().setSize(null, height);
            }
            mergeView.editor().setSize(null, height);
            if (mergeView.rightOriginal()) {
                mergeView.rightOriginal().setSize(null, height);
            }
            newHeight = mergeViewHeight(mergeView);
            if (newHeight >= height) {
                break;
            } else {
                height = newHeight;
            }
        }
        mergeView.wrap.style.height = height + 'px';
    }

    $(document).ready(function () {
        $('.panel-collapse').on('shown.bs.collapse', function (a,b) {
            var id               = $(this).attr('data-idx'),
                collapsedElement = $('#revision-' + id),
                closed           = collapsedElement.hasClass('in'),
                hasDiff          = false,
                revisionContent  = collapsedElement.find('.revision-content .hidden');
            revisionContent.each(function(idx, elem) {
                var jelem,
                    reference,
                    secondary,
                    selector,
                    target,
                    originalSelector;
                jelem     = $(elem);
                reference = jelem.attr('data-references');
                secondary = jelem.attr('data-references-secondary');
                selector  = (typeof secondary !== 'undefined' && secondary !== '' && secondary !== null)
                    ? ('diff-' + id + '-' + reference + '-' + secondary)
                    : ('diff-' + id + '-' + reference);
                target    = document.getElementById(selector);
                originalSelector = (typeof secondary !== 'undefined' && secondary !== '' && secondary !== null)
                    ? ('#original-' + reference + '-' + secondary)
                    : ('#original-' + reference);
                initUI(target, $(originalSelector).text(), jelem.text());
            })
        });
    });
</script>
{/literal}