{if isset($oSlider) && count($oSlider->oSlide_arr) > 0}
    <div class="slider-wrapper theme-{$oSlider->cTheme}{if $oSlider->bControlNav} control-nav{/if}{if $oSlider->bDirectionNav} direction-nav{/if}{if $oSlider->bThumbnail} thumbnail-nav{/if}">
        <div id="slider-{$oSlider->kSlider}" class="nivoSlider">
            {foreach from=$oSlider->oSlide_arr item=oSlide}
                {assign var="slideTitle" value=$oSlide->cTitel}
                {if !empty($oSlide->cText)}
                    {assign var="slideTitle" value="#slide_caption_{$oSlide->kSlide}"}
                {/if}
                {if !empty($oSlide->cLink)}
                    <a href="{$oSlide->cLink}"{if !empty($oSlide->cText)} title="{$oSlide->cText}"{/if} class="slide">
                {else}
                    <div class="slide">
                {/if}

                <img alt="{$oSlide->cTitel}" title="{$slideTitle}" src="{$oSlide->cBildAbsolut}"{if !empty($oSlide->cThumbnailAbsolut) && $oSlider->bThumbnail == '1'} data-thumb="{$oSlide->cThumbnailAbsolut}"{/if}/>

                {if !empty($oSlide->cLink)}
                    </a>
                {else}
                    </div>
                {/if}
            {/foreach}
        </div>
        {* slide captions outside of .nivoSlider *}
        {foreach from=$oSlider->oSlide_arr item=oSlide}
            {if !empty($oSlide->cText)}
                <div id="slide_caption_{$oSlide->kSlide}" class="htmlcaption hidden">
                    {if isset($oSlide->cTitel)}<strong class="title">{$oSlide->cTitel}</strong>{/if}
                    <p class="desc">{$oSlide->cText}</p>
                </div>
            {/if}
        {/foreach}
    </div>
    <script type="text/javascript">
        {if empty($oSlider->bUseKB)}
            jtl.ready(function () {ldelim}
                var slider = $('#slider-{$oSlider->kSlider}');
                $('a.slide').click(function () {ldelim}
                    if (!this.href.match(new RegExp('^' + location.protocol + '\\/\\/' + location.host))) {ldelim}
                        this.target = '_blank';
                        {rdelim}
                    {rdelim});
                slider.nivoSlider({ldelim}
                    effect: '{$oSlider->cEffects|replace:';':','}',
                    animSpeed: {$oSlider->nAnimationSpeed},
                    pauseTime: {$oSlider->nPauseTime},
                    directionNav: {$oSlider->bDirectionNav},
                    controlNav: {$oSlider->bControlNav},
                    controlNavThumbs: {$oSlider->bThumbnail},
                    pauseOnHover: {$oSlider->bPauseOnHover},
                    prevText: '{lang key="sliderPrev" section="media"}',
                    nextText: '{lang key="sliderNext" section="media"}',
                    randomStart: {$oSlider->bRandomStart},
                    afterLoad: function () {ldelim}
                        slider.addClass('loaded');
                    {rdelim}
                {rdelim});
            {rdelim});
        {else}
            var pauseTime = {$oSlider->nPauseTime};         // pauseTime must be set here
            var animSpeed = {$oSlider->nAnimationSpeed};    // animSpeed must be set here
            var zoomFactor = 30;                            // 30% zoom as default
            var durationFactor = 1.25;                      // firstslide pausetime adjustment factor

            function KBInit () {ldelim}
                $('.nivoSlider img').css('visibility', 'hidden');
                $('.nivoSlider .nivo-nextNav').trigger('click');
                $('.nivoSlider, .nivo-control').css('opacity',1);
                setTimeout (function(){ldelim}
                    $('.nivoSlider, .nivo-control').animate({ldelim}opacity: 1{rdelim},animSpeed);
                {rdelim},0);
                $('.nivo-control').on('click', function() {ldelim}
                    setTimeout (function(){ldelim}
                        $('.nivo-main-image').css('opacity',0);
                    {rdelim},0);
                    durationFactor = 1.25;
                {rdelim});
                $('.nivo-prevNav, .nivo-nextNav').on('click', function() {ldelim}
                    setTimeout (function(){ldelim}
                        $('.nivo-main-image').css('opacity',0);
                    {rdelim},20);
                    durationFactor = 1.25;
                {rdelim});
            {rdelim}

            function NivoKenBurns () {ldelim}
                $('.nivo-main-image').css('opacity',1);
                setTimeout (function(){ldelim}
                    $('.nivoSlider .nivo-slice img').css('width',100+zoomFactor+'%');
                {rdelim},10);
                setTimeout (function(){ldelim}
                    var nivoWidth=$('.nivoSlider').width(), nivoHeight=$('.nivoSlider').height();
                    var xScope=nivoWidth*zoomFactor/100, yScope=nivoHeight*zoomFactor/105;
                    var xStart=-xScope*Math.floor(Math.random()*2);
                    var yStart=-yScope*Math.floor(Math.random()*2);
                    $('.nivoSlider .nivo-slice img').css('left',xStart).css('top',yStart).animate({ldelim}width:'100%', left:0, top:0{rdelim},pauseTime*durationFactor);durationFactor=1.02;
                    $('.nivo-main-image').css('cssText','left:0 !important;top:0 !important;');
                {rdelim},10);
            {rdelim}

            jtl.ready(function () {ldelim}
                var slider = $('#slider-{$oSlider->kSlider}');
                var endSlide=$('.nivoSlider img').length-1;
                $('a.slide').click(function() {ldelim}
                    if (!this.href.match(new RegExp('^'+location.protocol+'\\/\\/'+location.host))) {ldelim}
                        this.target = '_blank';
                    {rdelim}
                {rdelim});
                slider.nivoSlider( {ldelim}
                    effect: 'fade',
                    animSpeed: animSpeed,
                    pauseTime: pauseTime,
                    directionNav: true,
                    controlNav: false,
                    controlNavThumbs: false,
                    pauseOnHover: false,
                    prevText: '{lang key="sliderPrev" section="media"}',
                    nextText: '{lang key="sliderNext" section="media"}',
                    manualAdvance: false,
                    randomStart: false,
                    startSlide: endSlide,
                    slices: 1,
                    beforeChange: function (){ldelim}NivoKenBurns();{rdelim},
                    afterLoad: function (){ldelim}
                        KBInit();
                        slider.addClass('loaded');
                    {rdelim}
                {rdelim});
            {rdelim});
        {/if}
    </script>
{/if}
