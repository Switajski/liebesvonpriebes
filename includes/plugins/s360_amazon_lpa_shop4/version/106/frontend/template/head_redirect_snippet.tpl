<script type="text/javascript">
    
    var lpa_shop_basepath = '{$lpa_shop_base_path}';
    
    {literal}
        /*
         * This script catches oAuth-answers with the token in the fragment part (#) of a URL.
         * It redirects to the same location, but turns the fragment into a get parameter (?).
         */
        function getURLParameter(name, source) {
            return decodeURIComponent((new RegExp('[?|&|#]' + name + '=' + '([^&;]+?)(&|#|;|$)').exec(source) || [, ""])[1].replace(/\+/g, '%20')) || null;
        }

        var accessToken = getURLParameter("access_token", location.hash);
        var fromHash = true;

        if (typeof accessToken !== 'string' || !accessToken.match(/^Atza/)) {
            accessToken = getURLParameter("access_token", location.search);
            fromHash = false;
        }

        if (typeof accessToken === 'string' && accessToken.match(/^Atza/)) {
            
            document.cookie = "lpa_address_consent_token=" + accessToken + ";secure";
            document.cookie = "amazon_Login_accessToken=" + accessToken + ";secure";
        }
        if (typeof accessToken === 'string' && accessToken.match(/^Atza/) && fromHash) {
            if (location.pathname === (lpa_shop_basepath + '/lpacheckout')) {
                location.replace('//' + location.host + lpa_shop_basepath + '/lpalogin' + "?access_token=" + encodeURIComponent(accessToken));
            } else if (location.pathname === (lpa_shop_basepath + '/lpacheckout-en')) {
                location.replace('//' + location.host + lpa_shop_basepath + '/lpalogin-en' + "?access_token=" + encodeURIComponent(accessToken));
            } else {
                location.replace('//' + location.host + location.pathname + "?access_token=" + encodeURIComponent(accessToken));
            }
        }
    {/literal}
</script>