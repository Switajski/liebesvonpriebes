var nSelectedSuggest = -1;
var nAnzahlSuggests = 0;

/*  
*	Params:
*	elem 			=> Objekt des Suchfelds (input)
*	e 				=> Event des Suchfelds
*	suggestID		=> DIV an dem die Suchvorschläge angegeben werden
*	submitID		=> Form die abgeschickt werden soll
*/
function checkKeys(elem, e, suggestID, submitID) {
	if(e) {
		if(elem.value.length >= 3) {
			if(e.keyCode == 38 || e.keyCode == 40) {
				for(i=0; i<=nAnzahlSuggests; i++) {
					document.getElementById(suggestID + i).className = 'suggestions';
				}
			}
			
			// up
			if(e.keyCode == 40 && (nSelectedSuggest < nAnzahlSuggests)) {
				nSelectedSuggest++;
				document.getElementById(suggestID + nSelectedSuggest).className = 'suggestions active';
			}	
				
			// down
			else if(e.keyCode == 38 && nSelectedSuggest > 0) {
				nSelectedSuggest--;	
				document.getElementById(suggestID + nSelectedSuggest).className = 'suggestions active';
			}
					
			// search
			else if(e.keyCode != 40 && e.keyCode != 38 && e.keyCode != 13) {
				nSelectedSuggest = -1;				
				xajax_suchVorschlag(elem.value, e.keyCode, elem.id, suggestID, submitID);
			}
			
			else
				document.getElementById(suggestID + nSelectedSuggest).className = 'suggestions active';	
		}
		else {
			if(nAnzahlSuggests != 0)
				for(i=0; i<=nAnzahlSuggests; i++)
					document.getElementById(suggestID + i).style.display = "none";
		}
	}
}

function checkEnter(elem, e, suggestID) {
	if(e.keyCode == 13)
		elem.value = document.getElementById(suggestID + 'value' + nSelectedSuggest).value;
}

function releaseSearch(elemSuggestID) {
   window.setTimeout(function() {
      var elemHeader = document.getElementById(elemSuggestID);
      elemHeader.style.display = "none";
   }, 200);
}

function resizeContainer(elemSearchID, elemSuggestID) {
   var elemSearch = document.getElementById(elemSearchID);
   var elemHeader = document.getElementById(elemSuggestID);
   elemHeader.style.zIndex = 101;
   elemHeader.style.position = 'absolute';
   elemHeader.style.left =  elemSearch.offsetLeft + 'px';
   elemHeader.style.top = (elemSearch.offsetTop + elemSearch.offsetHeight) + 'px';
   elemHeader.style.width = elemSearch.offsetWidth + 'px';
   elemHeader.style.display = 'block';
   elemHeader.className = 'search_wrapper';
}