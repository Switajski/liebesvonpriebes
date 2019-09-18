	// Ajax Code
	var obXHR;
	try {
		obXHR = new XMLHttpRequest();
	} catch(err) {
		try {
			obXHR = new ActiveXObject("Msxml2.XMLHTTP");
		} catch(err) {
			try {
				obXHR = new ActiveXObject("Microsoft.XMLHTTP");
			} catch(err) { obXHR = false; }
		}
	}
	function loadData(url, obId) {
		var obCon = document.getElementById(obId);
		obXHR.open("GET", url + '&rnd=' + Math.random(), true);
		obXHR.onreadystatechange = function() {
			if (obXHR.readyState == 1) {
				document.getElementById("amountCalc").innerHTML = "<img src='includes/modules/safetypay/gfx/safetypay_loader.gif' border='0'>";
			}
			else if (obXHR.readyState == 4)
			{
				obXML = obXHR.responseXML;
				obTcu = obXML.getElementsByTagName("ToCurrency");
				obCod = obXML.getElementsByTagName("BankCode");
				obDes = obXML.getElementsByTagName("BankName");
				obTam = obXML.getElementsByTagName("ToAmount");
				obRef = obXML.getElementsByTagName("ReferenceNo");
				if (obCod.length > 0)
				{
					obCon.length = obCod.length;
					for (var i=0; i<obCod.length;i++)
					{
						obCon.options[i].value	= obCod[i].firstChild.nodeValue;
						obCon.options[i].text	= obDes[i].firstChild.nodeValue;
					}
				}
				else
				{
					obCon.length = 1;
					obCon.options[0].value 	= '';
					obCon.options[0].text 	= 'Keine Banken gefunden. Bitte andere Währung auswählen.';
				}
				
				var undef = url.split("?");
				var args = undef[1].split("&");
				// Argument 1 ist die übergebene Ausgangswährung
				var arg1 = args[0].split("=");
				var Currency = arg1[1];
				// Argument 2 ist der übergebene Ausgangswert
				var arg2 = args[1].split("=");
				var txtAmount = arg2[1];
				
				if (obTam.length > 0)
				{
					// Argument 3 ist die übergebene Zielwährung
					var arg3 = args[2].split("=");
					var toCurrency = arg3[1];
					document.getElementById("amountCalc").innerHTML = toCurrency + " " + formatNum(obTam[0].firstChild.nodeValue, 2,',','.','','','-','');
					document.getElementById("CalcQuoteReferenceNo").value = obRef[0].firstChild.nodeValue;
				}
				else
				{
					document.getElementById("amountCalc").innerHTML = Currency + " " + txtAmount;
				}
			}
		}
		obXHR.send(null);
	}
	function formatNum(num,dec,thou,pnt,curr1,curr2,n1,n2) {var x = Math.round(num * Math.pow(10,dec));if (x >= 0) n1=n2='';var y = (''+Math.abs(x)).split('');var z = y.length - dec; if (z<0) z--; for(var i = z; i < 0; i++) y.unshift('0'); if (z<0) z = 1; y.splice(z, 0, pnt); if(y[0] == pnt) y.unshift('0'); while (z > 3) {z-=3; y.splice(z,0,thou);}var r = curr1+n1+y.join('')+n2+curr2;return r;}