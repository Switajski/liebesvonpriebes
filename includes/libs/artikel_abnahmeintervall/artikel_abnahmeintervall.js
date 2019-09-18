function gibAbnahmeIntervall(elem, fAbnahmeintervall)
{    
    if(Number(fAbnahmeintervall) > 0)
    {
        if(typeof(elem) != "undefined")
        {
            elem.value = elem.value.replace(/,/g, ".");
            if(Number(elem.value) < Number(fAbnahmeintervall) && Number(elem.value) != 0)
                elem.value = Number(fAbnahmeintervall);
            else
                elem.value = Math.round((Number(fAbnahmeintervall) * Math.ceil((Number(elem.value) / Number(fAbnahmeintervall)))) * 100) / 100; 
        }
    }
}

function erhoeheArtikelAnzahl(elemID, bIntervall, fAbnahmeintervall)
{
    if(elemID.length > 0)
    {
        if(typeof(bIntervall) == "undefined")
            bIntervall = false;
            
        elem = document.getElementById(elemID);
        
        if(typeof(elem) != "undefined")
        {
            elem.value = elem.value.replace(/,/g, ".");
            if(Number(elem.value) < 0)
                elem.value = 0;
            
            if(bIntervall && Number(fAbnahmeintervall) > 0)
            {
                if(Number(elem.value) < Number(fAbnahmeintervall))
                    elem.value = Number(fAbnahmeintervall);
                else
                {
                    elem.value = Math.round((Number(fAbnahmeintervall) * Math.ceil((Number(elem.value) / Number(fAbnahmeintervall)))) * 100) / 100; 
                    elem.value = Math.round((Number(elem.value) + Number(fAbnahmeintervall)) * 100) / 100;
                }
            }
            else
            {
                elem.value = Number(elem.value) + 1;
            }
        }
        $('#' + elemID).trigger('keyup');
    }   
}

function erniedrigeArtikelAnzahl(elemID, bIntervall, fAbnahmeintervall)
{
    if(elemID.length > 0)
    {
        if(typeof(bIntervall) == "undefined")
            bIntervall = false;
            
        elem = document.getElementById(elemID);
        
        if(typeof(elem) != "undefined")
        {
            elem.value = elem.value.replace(/,/g, ".");
            if(Number(elem.value) < 0)
                elem.value = 0;
            
            if(bIntervall && Number(fAbnahmeintervall) > 0)
            {
                if(Number(elem.value) >= Number(fAbnahmeintervall))
                {
                    elem.value = Math.round((Number(fAbnahmeintervall) * Math.floor((Number(elem.value) / Number(fAbnahmeintervall)))) * 100) / 100;
                    elem.value = Math.round((Number(elem.value) - Number(fAbnahmeintervall)) * 100) / 100; 
                }
            }
            else
            {
                if(Number(elem.value) > 1)
                    elem.value -= 1;
            }
        }
        $('#' + elemID).trigger('keyup');
    }   
}

function number_format(number, decimals, dec_point, thousands_sep)
{
	number = (number + '').replace(/[^0-9+\-Ee.]/g, '');
    var n = !isFinite(+number) ? 0 : +number,
        prec = !isFinite(+decimals) ? 0 : Math.abs(decimals),        
        sep = (typeof thousands_sep === 'undefined') ? ',' : thousands_sep,
        dec = (typeof dec_point === 'undefined') ? '.' : dec_point,
        s = '',
        toFixedFix = function (n, prec) {
            var k = Math.pow(10, prec);            
            return '' + Math.round(n * k) / k;
        };
    // Fix for IE parseFloat(0.55).toFixed(0) = 0;
    s = (prec ? toFixedFix(n, prec) : '' + Math.round(n)).split('.');
    if (s[0].length > 3) {        
    	s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);
    }
    if ((s[1] || '').length < prec) {
        s[1] = s[1] || '';
        s[1] += new Array(prec - s[1].length + 1).join('0');    
    }
    
    return s.join(dec);
}