{getExtensionCategory cat=$extension->kErweiterungKategorie}
<li class="col-xs-12 col-md-6 col-lg-3 item item-extension item-extension-filtered{if $clear === true} clear{/if}">
    <div class="item-inner">
        <div class="item-img-wrapper">
            <a href="{$extension->cUrl}" target="_blank">
                <img src="{if empty($extension->cBildPfadErw)}{$shopURL}/gfx/keinBild.gif{else}https://bilder.jtl-software.de/erweiterungen/{$extension->cBildPfadErw}{/if}" alt="{$extension->cName}" />
            </a>
        </div>
        <div>
            <p class="title">
                <a href="{$extension->cUrl}" target="_blank">{$extension->cName}</a>
            </p>
            <p class="author-meta">
                von <span class="by"><a href="{$extension->cWWW}" title="Webseite von {$extension->cFirma}">{$extension->cFirma}</a></span>
            </p>
            <p class="short-description">
                {$extension->cKurzBeschreibung}<br>
            </p>
        </div>
        <div class="cat-wrapper">           
            <span class="cat-icon">
                {if $catName}
                    <i class="fa" id="cat-{$catName|strtolower|htmlspecialchars|replace:'/':''|replace:' ':''}"></i>
                {else}
                    <i class="fa" id="cat-sonstiges"></i>
                {/if}
            </span>
            <span class="cat-name">{if $catName}{$catName}{else}Sonstiges{/if}</span>
        </div>
    </div>
    {if ($extension->nZertifiziert === '1')}
        <div class="cert-wrapper">
            <img src="https://images.jtl-software.de/servicepartner/cert/jtl_certified_128.png" alt="{$extension->cName}">
        </div>
    {/if}
</li>