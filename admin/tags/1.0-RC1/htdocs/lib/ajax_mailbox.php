<?php
//
// File: ajax_mailbox.php
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


function check_mailaccount(){
		var xhr = getXhr(); 
		var vButton = document.getElementById("submit");
		xhr.onreadystatechange = function(){ 
				if(xhr.readyState == 4 && xhr.status == 200){ 
						response = clean(xhr.responseXML.documentElement);
						var items = response.getElementsByTagName("mailbox");
						exist = items[0].getElementsByTagName("exist")[0].firstChild.nodeValue;
						if ( exist != 0 ){
						  var html  = '<?php print $PALANG['pCreate_alias_address_text_error2'];?>';
							vButton.style.border = "1px solid #aaa";
							vButton.style.color = "#ffffff";
							vButton.style.background = "#f0f5fa";
							vButton.disabled = true;
						}
						else{
						  var html  = 'OK';
							vButton.style.border = "1px solid #bcd0ed";
							vButton.style.color = "#000000";
							vButton.style.background = "#f0f5fa";
							vButton.disabled = false;

						}
						document.getElementById('login_status').innerHTML = html;

				} 
		} 

		xhr.open("POST","../ajax/mailbox_info.php",true); 
		xhr.setRequestHeader('Content-Type','application/x-www-form-urlencoded'); 
		account = document.getElementById('fUsername').value;
		domain = document.getElementById('fDomain').value;
		xhr.send("fDomain="+domain+"&fType=mailbox_exist&fUsername="+account); 

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
