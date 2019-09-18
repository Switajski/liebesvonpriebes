/*
-------------------------------------------------------------------------------
	JTL-Shop 3
	File: expander.js, javascript file
	
	page for JTL-Homepage 
	Admin
	
	Author: daniel.boehmer@jtl-software.de, JTL-Software
	http://www.jtl-software.de
	
	Copyright (c) 2010 JTL-Software
-------------------------------------------------------------------------------
*/

function expand(elemID, picExpandID, picRetractID)
{
	if(elemID.length > 0)
	{
		elem = document.getElementById(elemID);
		
		if(typeof(elem) != "undefined")
		{
	    	elem.style.display = "table-row";
	    	
	    	if(picExpandID.length > 0 && picRetractID.length > 0)
	    	{
		    	document.getElementById(picExpandID).style.display = "none";	
		    	document.getElementById(picRetractID).style.display = "table-row";
	    	}
    	}
	}		
}

function retract(elemID, picExpandID, picRetractID)
{
	if(elemID.length > 0)
	{
		elem = document.getElementById(elemID);
		
		if(typeof(elem) != "undefined")
		{
	    	elem.style.display = "none";
	    	
	    	if(picExpandID.length > 0 && picRetractID.length > 0)
	    	{
		    	document.getElementById(picExpandID).style.display = "table-row";	
		    	document.getElementById(picRetractID).style.display = "none";
	    	}
    	}
	}		
}