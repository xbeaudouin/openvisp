<?php
//
// File: ajax_db.php
//
require ("../variables.inc.php");
require ("../config.inc.php");
require ("../lib/functions.inc.php");
include ("../languages/" . check_language () . ".lang");

$SESSID_USERNAME = check_user_session();


?>
function getXhr(){ 
	var xhr = null; 
	if(window.XMLHttpRequest) // Firefox et autres 
		xhr = new XMLHttpRequest(); 
	else if(window.ActiveXObject){ // Internet Explorer 
		try { 
			xhr = new ActiveXObject("Msxml2.XMLHTTP"); 
		} catch (e) { 
			xhr = new ActiveXObject("Microsoft.XMLHTTP"); 
		} 
	} 
	else { // XMLHttpRequest non supporté par le navigateur 
		alert("Votre navigateur ne supporte pas les objets XMLHTTPRequest..."); 
		xhr = false; 
	} 
	return xhr; 
} 


function check_db_available(){ 
	var xhr = getXhr(); 
	var vButton = document.getElementById("submit");


	xhr.onreadystatechange = function(){ 
		if(xhr.readyState == 4 && xhr.status == 200){ 
			response = clean(xhr.responseXML.documentElement);
			items = response.getElementsByTagName("db");
			var allowed = items[0].getElementsByTagName("available")[0].firstChild.nodeValue - 0;
			var dbnum = document.getElementById('fDBnum').value - 0;
			if ( dbnum == "" ){
				var html  = '';
				vButton.style.border = "1px solid #aaa";
				vButton.style.color = "#ffffff";
				vButton.style.background = "#f0f5fa";
				vButton.disabled = true;
				
			}
			else if ( dbnum <= allowed ){
				var html  = 'OK';
				vButton.style.border = "1px solid #bcd0ed";
				vButton.style.color = "#000000";
				vButton.style.background = "#f0f5fa";
				vButton.disabled = false;

			}
			else{
				var html  = "<?php print $PALANG['pDBCreate_db_overquota'];?> :"+dbnum+":"+allowed;
				vButton.style.border = "1px solid #aaa";
				vButton.style.color = "#ffffff";
				vButton.style.background = "#f0f5fa";
				vButton.disabled = true;

			}
			document.getElementById('fMessage').innerHTML = html;

		} 
	} 
	
	xhr.open("POST","../ajax/domain_info.php",true); 
	xhr.setRequestHeader('Content-Type','application/x-www-form-urlencoded'); 
	dbnum = document.getElementById('fDBnum').value;
	sel = document.getElementById('fDomain');
	domain = sel.options[sel.selectedIndex].value;
	xhr.send("fDomain="+domain+"&fType=db_check_available&add="+dbnum); 

} 


function check_dbuser_available(){ 
	var xhr = getXhr(); 
	var vButton = document.getElementById("submit");


	xhr.onreadystatechange = function(){ 
		if(xhr.readyState == 4 && xhr.status == 200){ 
			response = clean(xhr.responseXML.documentElement);
			items = response.getElementsByTagName("dbuser");
			var allowed = items[0].getElementsByTagName("available")[0].firstChild.nodeValue - 0;
			var dbnum = document.getElementById('fUsernum').value - 0;
			if ( dbnum == "" ){
				var html  = '';
				vButton.style.border = "1px solid #aaa";
				vButton.style.color = "#ffffff";
				vButton.style.background = "#f0f5fa";
				vButton.disabled = true;
				
			}
			else if ( dbnum <= allowed ){
				var html  = 'OK';
				vButton.style.border = "1px solid #bcd0ed";
				vButton.style.color = "#000000";
				vButton.style.background = "#f0f5fa";
				vButton.disabled = false;

			}
			else{
				var html  = "<?php print $PALANG['pDBCreate_db_overquota'];?> :"+dbnum+":"+allowed;
				vButton.style.border = "1px solid #aaa";
				vButton.style.color = "#ffffff";
				vButton.style.background = "#f0f5fa";
				vButton.disabled = true;

			}
			document.getElementById('fMessage').innerHTML = html;

		} 
	} 
	
	xhr.open("POST","../ajax/domain_info.php",true); 
	xhr.setRequestHeader('Content-Type','application/x-www-form-urlencoded'); 
	sel = document.getElementById('fDomain');
	domain = sel.options[sel.selectedIndex].value;
	xhr.send("fDomain="+domain+"&fType=db_info"); 

} 


function go(c){ 
	if(!c.data.replace(/\s/g,'')) 
		c.parentNode.removeChild(c); 
} 

function clean(d){ 
	var bal=d.getElementsByTagName('*'); 
	for(i=0;i<bal.length;i++){ 
		a=bal[i].previousSibling; 
		if(a && a.nodeType==3) 
			go(a); 
		b=bal[i].nextSibling; 
		if(b && b.nodeType==3) 
			go(b); 
	} 
	return d; 
} 
