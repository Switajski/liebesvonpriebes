<script type="text/javascript">
    _tsRatingConfig = {ldelim}
        tsid: '{$ts_id_review_pre}',
        variant: 'skyscraper_horizontal',
        /* valid values: skyscraper_vertical, skyscraper_horizontal, vertical         */
        theme: 'light',
        reviews: 10,
        /* default = 10 */
        borderColor: '#aabbcc',
        /* optional - override the border */
        className: 'ts_features_review_wrapper',
        /* optional - override the whole sticker style with your own css
         class */
        richSnippets: 'off',
        /* valid values: on, off */
        introtext: 'What our customers say about us:'
        /* optional, not used in skyscraper variants */
    {rdelim};
    var scripts = document.getElementsByTagName('SCRIPT'),
            me = scripts[scripts.length - 1];
    var _ts = document.createElement('SCRIPT');
    _ts.type = 'text/javascript';
    _ts.async = true;
    _ts.charset = 'utf-8';
    _ts.src ='//widgets.trustedshops.com/reviews/tsSticker/tsSticker.js';
    me.parentNode.insertBefore(_ts, me);
    _tsRatingConfig.script = _ts;
</script>