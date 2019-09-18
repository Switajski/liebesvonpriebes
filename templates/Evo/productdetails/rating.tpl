{if $stars > 0}
    {assign var=filename1 value='rate'}
    {assign var=filename3 value='.png'}
    {if isset($total) && $total > 1}
        {lang key='averageProductRating' section='product rating' assign='ratingLabelText'}
    {else}
        {lang key='productRating' section='product rating' assign='ratingLabelText'}
    {/if}
    {block name="productdetails-rating"}
    <span class="rating" title="{$ratingLabelText}: {$stars}/5">
    {strip}
        {if $stars >= 5}
            <i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i>
        {elseif $stars >= 4}
            <i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i>
            {if $stars > 4}
                <i class="fa fa-star-half-o"></i>
            {else}
                <i class="fa fa-star-o"></i>
            {/if}
        {elseif $stars >= 3}
            <i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i>
            {if $stars > 3}
                <i class="fa fa-star-half-o"></i><i class="fa fa-star-o"></i>
            {else}
                <i class="fa fa-star-o"></i><i class="fa fa-star-o"></i>
            {/if}
        {elseif $stars >= 2}
            <i class="fa fa-star"></i><i class="fa fa-star"></i>
            {if $stars > 2}
                <i class="fa fa-star-half-o"></i><i class="fa fa-star-o"></i><i class="fa fa-star-o"></i>
            {else}
                <i class="fa fa-star-o"></i><i class="fa fa-star-o"></i><i class="fa fa-star-o"></i>
            {/if}
        {elseif $stars >= 1}
            <i class="fa fa-star"></i>
            {if $stars > 1}
                <i class="fa fa-star-half-o"></i><i class="fa fa-star-o"></i><i class="fa fa-star-o"></i><i class="fa fa-star-o"></i>
            {else}
                <i class="fa fa-star-o"></i><i class="fa fa-star-o"></i><i class="fa fa-star-o"></i><i class="fa fa-star-o"></i>
            {/if}
        {elseif $stars > 0}
            <i class="fa fa-star-half-o"></i><i class="fa fa-star-o"></i><i class="fa fa-star-o"></i><i class="fa fa-star-o"></i><i class="fa fa-star-o"></i>
        {/if}
    {/strip}
    </span>
    {/block}
{/if}