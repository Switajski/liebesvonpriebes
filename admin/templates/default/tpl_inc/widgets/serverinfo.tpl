<div class="widget-custom-data">
   <ul class="infolist clearall">
      <li class="first">
         <p class="key">Domain <span class="value">{$cShopHost}</span></p>
      </li>
      <li>
         <p class="key">Host <span class="value">{$serverHTTPHost} ({$serverAddress})</span></p>
      </li>
      <li>
         <p class="key">System <span class="value">{$phpOS}</span></p>
      </li>
      <li>
         <p class="key">PHP-Version <span class="value">{$phpVersion}</span></p>
      </li>
      <li class="last">
         <p class="key">MySQL-Version <span class="value{if $mySQLVersion|truncate:1:'' < 5} error{/if}">{$mySQLVersion}</span></p>
      </li>
   </ul>
</div>