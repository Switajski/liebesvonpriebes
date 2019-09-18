function setzeBrutto(elem, targetElemID, fSteuersatz)
{    
   document.getElementById(targetElemID).value = Math.round(Number(elem.value) * ((100 + Number(fSteuersatz)) / 100) * 100) / 100;
}

function setzeNetto(elem, targetElemID, fSteuersatz)
{
   document.getElementById(targetElemID).value = Math.round(Number(elem.value) * (100 / (100 + Number(fSteuersatz))) * 100) / 100;
}

function setzeBruttoAjax(cTargetID, elem, targetElemID, fSteuersatz)
{
   offset = $(elem).offset();
   if ($('#' + cTargetID).length > 0)
      $('#' + cTargetID).fadeIn('fast');
   
	setzeBrutto(elem, targetElemID, fSteuersatz);
	xajax_getCurrencyConversionAjax(parseFloat(elem.value), 0, cTargetID);
   
   $('#' + cTargetID).css({
      position: 'absolute',
      top: offset.top + $(elem).outerHeight(),
      left: offset.left
   }).addClass('pstooltip');
   
   $(elem).attr('autocomplete', 'off');
   $(elem).blur(function() {
      $('#' + cTargetID).fadeOut('fast');
   });
}

function setzeNettoAjax(cTargetID, elem, targetElemID, fSteuersatz)
{   
   offset = $(elem).offset();
   if ($('#' + cTargetID).length > 0)
      $('#' + cTargetID).fadeIn('fast');
   
	setzeNetto(elem, targetElemID, fSteuersatz);
	xajax_getCurrencyConversionAjax(0, parseFloat(elem.value), cTargetID);
   
   $('#' + cTargetID).css({
      position: 'absolute',
      top: offset.top + $(elem).outerHeight(),
      left: offset.left
   }).addClass('pstooltip');
   
   $(elem).attr('autocomplete', 'off');
   $(elem).blur(function() {
      $('#' + cTargetID).fadeOut('fast');
   });
}

function setzePreisAjax(bNetto, cTargetID, elem)
{
	if(bNetto)
		xajax_getCurrencyConversionAjax(parseFloat(elem.value), 0, cTargetID);
	else
		xajax_getCurrencyConversionAjax(0, parseFloat(elem.value), cTargetID);
}

function setzeAufpreisTyp(elem, bruttoElemID, nettoElemID)
{
   if(elem.value == "festpreis")
   {
      document.getElementById(bruttoElemID).style.visibility = 'visible';
      setzeBrutto(document.getElementById(nettoElemID), bruttoElemID);
   }
   else
      document.getElementById(bruttoElemID).style.visibility = 'hidden';             
}