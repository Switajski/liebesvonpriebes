{function test_result}
    {if $test->getResult() == 0}
        <span class="hidden-xs">
            <h4 class="label-wrap"><span class="label label-success">
                {if $test->getCurrentState()|@count_characters > 0}
                    {$test->getCurrentState()}
                {else}
                    <i class="fa fa-check" aria-hidden="true"></i>
                {/if}
            </span></h4>
        </span>
        <span class="visible-xs">
            <h4 class="label-wrap"><span class="label label-success">
                <i class="fa fa-check" aria-hidden="true"></i>
            </span></h4>
        </span>
    {elseif $test->getResult() == 1}
        {if $test->getIsOptional()}
        <span class="hidden-xs">
            {if $test->getIsRecommended()}
                <h4 class="label-wrap"><span class="label label-warning">
                    {if $test->getCurrentState()|@count_characters > 0}
                        {$test->getCurrentState()}
                    {else}
                        <i class="fa fa-exclamation-triangle" aria-hidden="true"></i>
                    {/if}
                </span></h4>
            {else}
                <h4 class="label-wrap"><span class="label label-primary">
                    {if $test->getCurrentState()|@count_characters > 0}
                        {$test->getCurrentState()}
                    {else}
                        <i class="fa fa-times" aria-hidden="true"></i>
                    {/if}
                </span></h4>
            {/if}
        </span>
        <span class="visible-xs">
            {if $test->getIsRecommended()}
                <h4 class="label-wrap"><span class="label label-warning">
                    <i class="fa fa-exclamation-triangle" aria-hidden="true"></i>
                </span></h4>
            {else}
                <h4 class="label-wrap"><span class="label label-primary">
                    <i class="fa fa-times" aria-hidden="true"></i>
                </span></h4>
            {/if}
        </span>
        {else}
        <span class="hidden-xs">
            <h4 class="label-wrap"><span class="label label-danger">
                {if $test->getCurrentState()|@count_characters > 0}
                    {$test->getCurrentState()}
                {else}
                    <i class="fa fa-times" aria-hidden="true"></i>
                {/if}
            </span></h4>
        </span>
        <span class="visible-xs">
            <h4 class="label-wrap"><span class="label label-danger">
                <i class="fa fa-times" aria-hidden="true"></i>
            </span></h4>
        </span>
        {/if}
    {elseif $test->getResult() == 2}
    {/if}
{/function}