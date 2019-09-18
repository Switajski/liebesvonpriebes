/*
-------------------------------------------------------------------------------
	JTL-Shop 3
	File: checkAllMSG.js, javascript file
	
	page for JTL-Shop 3 
	Admin
	
	Author: daniel.boehmer@jtl-software.de, JTL-Software
	http://www.jtl-software.de
	
	Copyright (c) 2009 JTL-Software
-------------------------------------------------------------------------------
*/

function AllMessages(form)
{
	for (var x = 0; x< form.elements.length; x++) {
		var y = form.elements[x];
		if (y.name != 'ALLMSGS') {
			y.checked = form.ALLMSGS.checked;
		}
	}
}

function AllMessagesExcept(form, cID)
{
	for (var x = 0; x< form.elements.length; x++) {
		var y = form.elements[x];
		if (y.name != 'ALLMSGS') {
			if (cID.length > 0)
			{
				if (y.id.indexOf(cID))
					y.checked = form.ALLMSGS.checked;
			}
		}
	}
}