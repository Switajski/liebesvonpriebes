<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
    <title></title>
</head>
<body>
{if isset($oMailObjekt->oLogEntry_arr)}
    <h2>Log entries ({$oMailObjekt->oLogEntry_arr|@count}):</h2>
    {foreach $oMailObjekt->oLogEntry_arr as $oLogEntry}
        <h3>
            [{$oLogEntry->dErstellt|date_format:"%d.%m.%Y %H:%M:%S"}]
            {if $oLogEntry->nLevel == 1}
                <span style="color:#f00;">[Error]</span>
            {elseif $oLogEntry->nLevel == 2}
                <span style="color:#00f;">[Notice]</span>
            {elseif $oLogEntry->nLevel == 4}
                <span style="color:#fa0;">[Debug]</span>
            {/if}
        </h3>
        <pre>{$oLogEntry->cLog}</pre>
    {/foreach}
{/if}
</body>
</html>