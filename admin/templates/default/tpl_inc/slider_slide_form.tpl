<div class="category">Slides</div>
<div id="slides_outer">
    <div id="slides_container">
        <ul id="slides">
            {foreach name="slide" from=$oSlider->oSlide_arr item=oSlide}
                <li id="{$oSlide->kSlide}" class="slide" context="{$oSlide->kSlider}">
                    <span class="caption item{$oSlide->kSlide}">Slide #<span class="number">{$oSlide->nSort}</span></span>
                    <img src="{$oSlide->cBildAbsolut}" alt="Slidergrafik" />
                    <div class="overlay"></div>
                    <div class="overlay_edit">
                        <div>
                            <form id="slide{$oSlide->kSlide}" enctype="multipart/form-data">
                                <div class="status status_success">
                                    <span class="success">Bild wurde ausgew&auml;hlt</span>
                                </div>
                                <div class="status status_error">
                                    <span class="error">Fehler beim Speichervorgang!</span>
                                </div>
                                <input type="hidden" name="delete" value="0" />
                                <input type="hidden" name="kSlide" value="{$oSlide->kSlide}" />
                                <input type="hidden" name="nSort" value="{$oSlide->nSort}" />
                                <input type="hidden" name="kSlider" value="{$oSlide->kSlider}" />
                                <input type="hidden" name="cBild" value="{$oSlide->cBild}" />
                                <input type="hidden" name="cThumbnail" value="{$oSlide->cThumbnail}" />
                                <fieldset>
                                    <input type="button" class="select_image button add" value="Bild ausw&auml;hlen" />
                                </fieldset>
                                <fieldset>
                                    <label for="cTitel">Titel</label>
                                    <input type="text" name="cTitel" value="{$oSlide->cTitel}" />
                                </fieldset>
                                <fieldset>
                                    <label for="cText">Text</label>
                                    <textarea name="cText">{$oSlide->cText}</textarea>
                                </fieldset>
                                <fieldset>
                                    <label for="cTitel">Link</label>
                                    <input type="text" name="cLink" value="{$oSlide->cLink}" />
                                </fieldset>
                                <p class="ajax_preloader">Wird gespeichert...</p>
                                <div class="right buttons">
                                    <input type="button" class="button blue cancel" value="Abbrechen" /><input type="button" class="button blue slide_delete" value="L&ouml;schen" /><input type="button" class="button blue" name="save" value="Speichern" />
                                </div>
                            </form>
                        </div>
                    </div>
                    <h4>{$oSlide->cTitel}</h4>
                    <p>{$oSlide->cText}</p>
                </li>
            {/foreach}
            <li class="slide append" context="0" id="0">
                <form id="slide0" enctype="multipart/form-data">
                    <input type="hidden" name="kSlider" value="{$oSlider->kSlider}" />
                    <span class="caption">Neuen Slide hinzuf&uuml;gen</span><br>
                    <div class="status status_success">
                        <span class="success">Bild wurde ausgew&auml;hlt</span>
                    </div>
                    <div class="status status_error">
                        <span class="error">Fehler beim Speichervorgang!</span>
                    </div>
                    <fieldset>
                        <input type="button" class="select_image button add" value="Bild ausw&auml;hlen" />
                        <input type="hidden" name="cBild" value="" />
                    </fieldset>
                    <fieldset>
                        <label for="cTitel">Titel</label>
                        <input type="text" name="cTitel" value="" />
                    </fieldset>
                    <fieldset>
                        <label for="cText">Text</label>
                        <textarea name="cText"></textarea>
                    </fieldset>
                    <fieldset>
                        <label for="cTitel">Link</label>
                        <input type="text" name="cLink" value="" />
                    </fieldset>
                    <p class="ajax_preloader">Wird gespeichert...</p>
                    <input type="button" name="append_slide" value="Hinzuf&uuml;gen" class="button blue" />
                </form>
            </li>
        </ul>
    </div>
</div>
<div class="save_wrapper">
    <input type="button" class="button orange" onclick="window.location.href = 'slider.php';" value="Zur&uuml;ck" />
</div>
<div class="upload_info kcfinder_path">{$ShopURL}/{$PFAD_KCFINDER}</div>
<div class="upload_info shop_url">{$ShopURL}</div>
<script type="text/javascript" src="{$currentTemplateDir}js/jquery.uploadify.js"></script>