// Prototype Method to get the element based on ID
function $(d){
	return document.getElementById(d);
}

// set or get the current display style of the div
function dsp(d,v){
	if(v==undefined){
		return d.style.display;
	}else{
		d.style.display=v;
	}
}

// set or get the height of a div.
function sh(d,v){
	// if you are getting the height then display must be block to return the absolute height
	if(v==undefined){
		if(dsp(d)!='none'&& dsp(d)!=''){
			return d.offsetHeight;
		}
		viz = d.style.visibility;
		d.style.visibility = 'hidden';
		o = dsp(d);
		dsp(d,'block');
		r = parseInt(d.offsetHeight);
		dsp(d,o);
		d.style.visibility = viz;
		return r;
	}else{
		d.style.height=v;
	}
}

s=7;
t=10;
nEnabled = 0;

//Collapse Timer is triggered as a setInterval to reduce the height of the div exponentially.
function ct(d){
	d = $(d);
	if(sh(d)>0){
		v = Math.round(sh(d)/d.s);
		v = (v<1) ? 1 :v ;
		v = (sh(d)-v);
		sh(d,v+'px');
		d.style.opacity = (v/d.maxh);
		d.style.filter= 'alpha(opacity='+(v*100/d.maxh)+');';
	}else{
		sh(d,0);
		dsp(d,'none');
		clearInterval(d.t);
	}
}

// Collapse Initializer
function cl(d){
	if(dsp(d)=='block'){
		clearInterval(d.t);
		d.t=setInterval('ct("'+d.id+'")',t);
	}
}

// Removes Classname from the given div.
function cc(n,v){
	s=n.className.split(/\s+/);
	for(p=0;p<s.length;p++){
		if(s[p]==v+n.tc){
			s.splice(p,1);
			n.className=s.join(' ');
			break;
		}
	}
}

function closeAll(cCurrentId) {	
	l=$('basic-accordian').getElementsByTagName('div');
	//id = id.substr(0, id.indexOf('-'));
	
	for(m=0; m<l.length; m++) {
		h = l[m].id;
		
		if(h.substr(h.indexOf('-')+1, h.length) == 'header') {
			cHeadNeutral = h.substr(0, h.indexOf('-'));			
			oHead = $(h);
			oHead.tc = 'header_highlight';
			oHead.cHead = h;
			oHead.onclick = "";
			
			oContent = $(cHeadNeutral + '-content');
			oContent.style.display = 'none';
			oContent.style.overflow = 'hidden';
			oContent.maxh = sh(oContent);
			oContent.s = (s == undefined) ? 7 : s;
			
			cl($(cHeadNeutral + '-content'));
			$(cHeadNeutral + '-header').className = 'accordion_headings_inactive';
			
			if(Number(cCurrentId) == Number(cHeadNeutral)) {
				oHead.className = 'accordion_headings_inactive' + " " + oHead.tc;
				$(cCurrentId + '-content').style.display = 'block';
				cc(oHead, '__');
			}
		}
	}
}

function toggleState() {
	
	switch(nEnabled) {
		case 0:
			closeAll('0');
			break;
		case 1:
			closeAll('1');
			$('0-header').onclick = function() { 
				nEnabled = 0; 
				closeAll('0');
				$('div_boxRechnungsadresse').style.display = 'none';
				$('div_boxLieferadresse').style.display = 'none';
				$('div_boxVersandart').style.display = 'none';
				$('div_boxZahlungsart').style.display = 'none';
			}
			$('0-header').className = 'accordion_headings_inactive header_highlight';
			$('div_boxRechnungsadresse').style.display = 'none';
			$('div_boxLieferadresse').style.display = 'none';
			$('div_boxVersandart').style.display = 'none';
			$('div_boxZahlungsart').style.display = 'none';
			break;
		case 2:
			closeAll('2');
			$('1-header').onclick = function() { 
				nEnabled = 1; 
				closeAll('1'); 
				$('div_boxRechnungsadresse').style.display = 'none';
				$('div_boxLieferadresse').style.display = 'none';
				$('div_boxVersandart').style.display = 'none';
				$('div_boxZahlungsart').style.display = 'none';
			}
			$('1-header').className = 'accordion_headings_inactive header_highlight';
			$('div_boxLieferadresse').style.display = 'none';
			$('div_boxVersandart').style.display = 'none';
			$('div_boxZahlungsart').style.display = 'none';
			break;
		case 3:
			closeAll('3');
			$('1-header').onclick = function() { 
				nEnabled = 1; 
				closeAll('1'); 
				$('div_boxRechnungsadresse').style.display = 'none';
				$('div_boxLieferadresse').style.display = 'none';
				$('div_boxVersandart').style.display = 'none';
				$('div_boxZahlungsart').style.display = 'none';
			}
			$('1-header').className = 'accordion_headings_inactive header_highlight';
			$('2-header').onclick = function() { 
				nEnabled = 2; 
				closeAll('2'); 
				$('1-header').onclick = function() { 
					nEnabled = 1; 
					closeAll('1'); 
					$('div_boxRechnungsadresse').style.display = 'none';
					$('div_boxLieferadresse').style.display = 'none';
					$('div_boxVersandart').style.display = 'none';
					$('div_boxZahlungsart').style.display = 'none';
				}
				$('1-header').className = 'accordion_headings_inactive header_highlight';
				$('div_boxLieferadresse').style.display = 'none';
				$('div_boxVersandart').style.display = 'none';
				$('div_boxZahlungsart').style.display = 'none';
			}
			$('2-header').className = 'accordion_headings_inactive header_highlight';
			$('div_boxVersandart').style.display = 'none';
			$('div_boxZahlungsart').style.display = 'none';
			break;
		case 4:
			closeAll('4');
			$('1-header').onclick = function() { 
				nEnabled = 1; 
				closeAll('1'); 
				$('div_boxRechnungsadresse').style.display = 'none';
				$('div_boxLieferadresse').style.display = 'none';
				$('div_boxVersandart').style.display = 'none';
				$('div_boxZahlungsart').style.display = 'none';
			}
			$('1-header').className = 'accordion_headings_inactive header_highlight';
			$('2-header').onclick = function() { 
					nEnabled = 2; 
					closeAll('2'); 
					$('1-header').onclick = function() { 
						nEnabled = 1; 
						closeAll('1'); 
						$('div_boxRechnungsadresse').style.display = 'none';
						$('div_boxLieferadresse').style.display = 'none';
						$('div_boxVersandart').style.display = 'none';
						$('div_boxZahlungsart').style.display = 'none';
					}
					$('1-header').className = 'accordion_headings_inactive header_highlight';
					$('div_boxLieferadresse').style.display = 'none';
					$('div_boxVersandart').style.display = 'none';
					$('div_boxZahlungsart').style.display = 'none';
			}
			$('2-header').className = 'accordion_headings_inactive header_highlight';
			$('3-header').onclick = function() { 
				nEnabled = 4; 
				closeAll('3');
				$('2-header').onclick = function() { 
					nEnabled = 2; 
					closeAll('2'); 
					$('1-header').onclick = function() { 
						nEnabled = 1; 
						closeAll('1'); 
						$('div_boxRechnungsadresse').style.display = 'none';
						$('div_boxLieferadresse').style.display = 'none';
						$('div_boxVersandart').style.display = 'none';
						$('div_boxZahlungsart').style.display = 'none';
					}
					$('1-header').className = 'accordion_headings_inactive header_highlight';
					$('div_boxLieferadresse').style.display = 'none';
					$('div_boxVersandart').style.display = 'none';
					$('div_boxZahlungsart').style.display = 'none';
				}
				$('1-header').onclick = function() { 
					nEnabled = 1; 
					closeAll('1'); 
					$('div_boxRechnungsadresse').style.display = 'none';
					$('div_boxLieferadresse').style.display = 'none';
					$('div_boxVersandart').style.display = 'none';
					$('div_boxZahlungsart').style.display = 'none';
				}
				$('1-header').className = 'accordion_headings_inactive header_highlight';
				$('2-header').className = 'accordion_headings_inactive header_highlight';
				$('div_boxVersandart').style.display = 'none';
				$('div_boxZahlungsart').style.display = 'none';
			}
			$('3-header').className = 'accordion_headings_inactive header_highlight';
			$('div_boxZahlungsart').style.display = 'none';	
			break;
		case 5:			
			closeAll('5');
			$('1-header').onclick = function() { 
				nEnabled = 1; 
				closeAll('1'); 
				$('div_boxRechnungsadresse').style.display = 'none';
				$('div_boxLieferadresse').style.display = 'none';
				$('div_boxVersandart').style.display = 'none';
				$('div_boxZahlungsart').style.display = 'none';
			}
			$('1-header').className = 'accordion_headings_inactive header_highlight';
			$('2-header').onclick = function() { 
				nEnabled = 2; 
				closeAll('2');
				$('1-header').onclick = function() { 
					nEnabled = 1; 
					closeAll('1'); 
					$('div_boxRechnungsadresse').style.display = 'none';
					$('div_boxLieferadresse').style.display = 'none';
					$('div_boxVersandart').style.display = 'none';
					$('div_boxZahlungsart').style.display = 'none';
				}
				$('1-header').className = 'accordion_headings_inactive header_highlight';
				$('div_boxLieferadresse').style.display = 'none';
				$('div_boxVersandart').style.display = 'none';
				$('div_boxZahlungsart').style.display = 'none';
			}
			$('2-header').className = 'accordion_headings_inactive header_highlight';
			$('3-header').onclick = function() { 
				nEnabled = 4; 
				closeAll('3');
				$('1-header').onclick = function() { 
					nEnabled = 1; 
					closeAll('1'); 
					$('div_boxRechnungsadresse').style.display = 'none';
					$('div_boxLieferadresse').style.display = 'none';
					$('div_boxVersandart').style.display = 'none';
					$('div_boxZahlungsart').style.display = 'none';
				}
				$('1-header').className = 'accordion_headings_inactive header_highlight';
				$('2-header').onclick = function() { 
					nEnabled = 2; 
					closeAll('2');
					$('1-header').onclick = function() { 
						nEnabled = 1; 
						closeAll('1'); 
						$('div_boxRechnungsadresse').style.display = 'none';
						$('div_boxLieferadresse').style.display = 'none';
						$('div_boxVersandart').style.display = 'none';
						$('div_boxZahlungsart').style.display = 'none';
					}
					$('1-header').className = 'accordion_headings_inactive header_highlight';
					$('div_boxLieferadresse').style.display = 'none';
					$('div_boxVersandart').style.display = 'none';
					$('div_boxZahlungsart').style.display = 'none';
				}
				$('2-header').className = 'accordion_headings_inactive header_highlight';
				$('div_boxVersandart').style.display = 'none';
				$('div_boxZahlungsart').style.display = 'none';
			}
			$('3-header').className = 'accordion_headings_inactive header_highlight';
			$('4-header').onclick = function() { 
				nEnabled = 4; 
				closeAll('4');
				$('1-header').onclick = function() { 
					nEnabled = 1; 
					closeAll('1'); 
					$('div_boxRechnungsadresse').style.display = 'none';
					$('div_boxLieferadresse').style.display = 'none';
					$('div_boxVersandart').style.display = 'none';
					$('div_boxZahlungsart').style.display = 'none';
				}
				$('1-header').className = 'accordion_headings_inactive header_highlight';
				$('2-header').onclick = function() { 
					nEnabled = 2; 
					closeAll('2');
					$('1-header').onclick = function() { 
						nEnabled = 1; 
						closeAll('1'); 
						$('div_boxRechnungsadresse').style.display = 'none';
						$('div_boxLieferadresse').style.display = 'none';
						$('div_boxVersandart').style.display = 'none';
						$('div_boxZahlungsart').style.display = 'none';
					}
					$('1-header').className = 'accordion_headings_inactive header_highlight';
					$('div_boxLieferadresse').style.display = 'none';
					$('div_boxVersandart').style.display = 'none';
					$('div_boxZahlungsart').style.display = 'none';
				}
				$('2-header').className = 'accordion_headings_inactive header_highlight';
				$('3-header').onclick = function() { 
					nEnabled = 4; 
					closeAll('3');
					$('1-header').onclick = function() { 
						nEnabled = 1; 
						closeAll('1'); 
						$('div_boxRechnungsadresse').style.display = 'none';
						$('div_boxLieferadresse').style.display = 'none';
						$('div_boxVersandart').style.display = 'none';
						$('div_boxZahlungsart').style.display = 'none';
					}
					$('1-header').className = 'accordion_headings_inactive header_highlight';
					$('2-header').onclick = function() { 
						nEnabled = 2; 
						closeAll('2');
						$('1-header').onclick = function() { 
							nEnabled = 1; 
							closeAll('1'); 
							$('div_boxRechnungsadresse').style.display = 'none';
							$('div_boxLieferadresse').style.display = 'none';
							$('div_boxVersandart').style.display = 'none';
							$('div_boxZahlungsart').style.display = 'none';
						}
						$('1-header').className = 'accordion_headings_inactive header_highlight';
						$('div_boxLieferadresse').style.display = 'none';
						$('div_boxVersandart').style.display = 'none';
						$('div_boxZahlungsart').style.display = 'none';
					}
					$('2-header').className = 'accordion_headings_inactive header_highlight';
					$('div_boxVersandart').style.display = 'none';
					$('div_boxZahlungsart').style.display = 'none';
				}
				$('3-header').className = 'accordion_headings_inactive header_highlight';
				$('div_boxZahlungsart').style.display = 'none';
			}
			$('4-header').className = 'accordion_headings_inactive header_highlight';
			break;	
	}
}


//Accordian Initializer
function Accordian(d, s, tc) {

	l=$(d).getElementsByTagName('div');
	cContent_arr = [];
	cHead_arr = [];
	cHead = '';
	oHead = '';
	
	for(m=0; m<l.length; m++) {
		h = l[m].id;
	
		if(h.substr(h.indexOf('-')+1, h.length) == 'content')
			cContent_arr.push(h);
			
		if(h.substr(h.indexOf('-')+1, h.length) == 'header')
			cHead_arr.push(h);
	}
	
	//then search through headers
	for(i=0; i<cHead_arr.length; i++){
		cHead = cHead_arr[i];
		cHeadNeutral = cHead.substr(0, cHead.indexOf('-'));
		oHead = $(cHead);
		
		oContent = $(cHead.substr(0, cHead.indexOf('-')) + '-content');
		oContent.style.display = 'none';
		oContent.style.overflow = 'hidden';
		oContent.maxh = sh(oContent);
		oContent.s = (s == undefined) ? 7 : s;
		
		oHead.tc = tc;
		oHead.cHead = cHead;
		oHead.cHead_arr = cHead_arr;
		
	}
	
	toggleState();
}

function init() {
  // quit if this function has already been called
  if (arguments.callee.done) return;

  // flag this function so we don't do the same thing twice
  arguments.callee.done = true;

  A = new Accordian('basic-accordian',5,'header_highlight');
};