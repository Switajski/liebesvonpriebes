{include file='tpl_inc/header.tpl'}
{config_load file="$lang.conf" section="shopzuruecksetzen"}
{include file='tpl_inc/seite_header.tpl' cTitel=#shopReset# cBeschreibung=#shopResetDesc# cDokuURL=#shopResetURL#}
<div id="content" class="container-fluid settings">
    <form name="login" method="post" action="shopzuruecksetzen.php">
        {$jtl_token}
        <input type="hidden" name="zuruecksetzen" value="1" />

        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">Shopinhalte</h3>
            </div>
            <div class="panel-body">
                <div class="item">
                    <input type="checkbox" name="cOption_arr[]" value="artikel" tabindex="3" id="Artikel" />
                    <label for="Artikel">Artikel, Kategorien, Merkmale l&ouml;schen (Komplett&uuml;bertragung aus JTL-Wawi f&uuml;llt diese Daten wieder auf)</label>
                </div>
                <div class="item">
                    <input type="checkbox" name="cOption_arr[]" value="news" tabindex="4" id="News" />
                    <label for="News">News l&ouml;schen</label>
                </div>
                <div class="item">
                    <input type="checkbox" name="cOption_arr[]" value="bestseller" tabindex="5" id="Bestseller" />
                    <label for="Bestseller">Bestseller l&ouml;schen</label>
                </div>
                <div class="item">
                    <input type="checkbox" name="cOption_arr[]" value="besucherstatistiken" tabindex="6" id="Besucherstatistiken" />
                    <label for="Besucherstatistiken">Besucherstatistiken l&ouml;schen</label>
                </div>
                <div class="item">
                    <input type="checkbox" name="cOption_arr[]" value="preisverlaeufe" tabindex="8" id="Preisverlaufe" />
                    <label for="Preisverlaufe">Preisverl&auml;ufe l&ouml;schen</label>
                </div>
                <div class="item">
                    <input type="checkbox" name="cOption_arr[]" value="umfragen" tabindex="9" id="Umfragen" />
                    <label for="Umfragen">Umfragen l&ouml;schen</label>
                </div>
                <div class="item">
                    <input type="checkbox" name="cOption_arr[]" value="verfuegbarkeitsbenachrichtigungen" tabindex="10" id="Verfugbarkeitsbenachrichtigungen" />
                    <label for="Verfugbarkeitsbenachrichtigungen">Verf&uuml;gbarkeitsbenachrichtigungen l&ouml;schen</label>
                </div>
                <div class="item">
                    <input type="checkbox" name="cOption_arr[]" value="revisions" tabindex="11" id="Revisions" />
                    <label for="Revisions">Revisionen l&ouml;schen</label>
                </div>
            </div>
            <div class="panel-footer">
                <input type="checkbox" id="ALLMSGS1" class="Boxen" />
                <label for="ALLMSGS1">Alle ausw&auml;hlen</label>
            </div>
        </div>

        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">Benutzergenerierte Inhalte</h3>
            </div>
            <div class="panel-body">
                <div class="item">
                    <input type="checkbox" name="cOption_arr[]" value="suchanfragen" tabindex="11" id="Suchanfragen" />
                    <label for="Suchanfragen">Suchanfragen l&ouml;schen</label>
                </div>
                <div class="item">
                    <input type="checkbox" name="cOption_arr[]" value="tags" tabindex="12" id="Tags" />
                    <label for="Tags">Tags l&ouml;schen</label>
                </div>
                <div class="item">
                    <input type="checkbox" name="cOption_arr[]" value="bewertungen" tabindex="13" id="Bewertungen" />
                    <label for="Bewertungen">Bewertungen l&ouml;schen</label>
                </div>
            </div>
            <div class="panel-footer">
                <input type="checkbox" id="ALLMSGS2" class="Boxen" />
                <label for="ALLMSGS2">Alle ausw&auml;hlen</label>
            </div>
        </div>
        
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">Shopkunden & Bestellungen & Kupons</h3>
            </div>
            <div class="panel-body">
                <div class="item">
                    <input type="checkbox" name="cOption_arr[]" value="shopkunden" tabindex="14" id="Shopkunden" />
                    <label for="Shopkunden">Shopkunden l&ouml;schen</label>
                </div>
                <div class="item">
                    <input type="checkbox" name="cOption_arr[]" value="kwerbenk" tabindex="14" id="KwerbenK" />
                    <label for="KwerbenK">Kunden werben Kunden l&ouml;schen</label>
                </div>
                <div class="item">
                    <input type="checkbox" name="cOption_arr[]" value="bestellungen" tabindex="15" id="Bestellungen" />
                    <label for="Bestellungen">Bestellungen l&ouml;schen</label>
                </div>
                <div class="item">
                    <input type="checkbox" name="cOption_arr[]" value="kupons" tabindex="15" id="Kupons" />
                    <label for="Kupons">Kupons l&ouml;schen</label>
                </div>
            </div>
            <div class="panel-footer">
                <input type="checkbox" id="ALLMSGS3" class="Boxen" />
                <label for="ALLMSGS3">Alle ausw&auml;hlen</label>
            </div>
        </div>
        <div class="save_wrapper">
            <button type="submit" value="{#shopReset#}" class="btn btn-danger"><i class="fa fa-exclamation-triangle"></i> {#shopReset#}</button>
        </div>
    </form>
</div>
{include file='tpl_inc/footer.tpl'}