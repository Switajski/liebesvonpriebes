   <script type="text/javascript">
      if (typeof CKEDITOR != 'undefined')
      {ldelim}
         CKEDITOR.config.startupMode = {if isset($Einstellungen.global.admin_ckeditor_mode) && $Einstellungen.global.admin_ckeditor_mode == 'Q'}'source'{else}'wysiwyg'{/if};
         CKEDITOR.config.htmlEncodeOutput = false;
         CKEDITOR.config.entities = false;
         CKEDITOR.config.entities_latin = false;
         CKEDITOR.config.entities_greek = false;
         CKEDITOR.config.filebrowserBrowseUrl = '{$URL_SHOP}/includes/libs/kcfinder/browse.php?type=Sonstiges';
         CKEDITOR.config.filebrowserImageBrowseUrl = '{$URL_SHOP}/includes/libs/kcfinder/browse.php?type=Bilder';
         CKEDITOR.config.filebrowserFlashBrowseUrl = '{$URL_SHOP}/includes/libs/kcfinder/browse.php?type=Videos';
         CKEDITOR.config.filebrowserUploadUrl = '{$URL_SHOP}/includes/libs/kcfinder/upload.php?type=Sonstiges';
         CKEDITOR.config.filebrowserImageUploadUrl = '{$URL_SHOP}/includes/libs/kcfinder/upload.php?type=Bilder';
         CKEDITOR.config.filebrowserFlashUploadUrl = '{$URL_SHOP}/includes/libs/kcfinder/upload.php?type=Videos';
      {rdelim}
   </script>

   {if $account}
   </div>
   <div id="footer_wrapper">
      <p>Copyright 2006-{$smarty.now|date_format:"%Y"} <a href="http://www.jtl-software.de" target="_blank">JTL-Software GmbH</a> - Alle Rechte vorbehalten.</p>
   </div>
   {/if}
   </body>
</html>
