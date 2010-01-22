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


function go_virtual_domain(){ 
	var xhr = getXhr(); 

	xhr.onreadystatechange = function(){ 
		if(xhr.readyState == 4 && xhr.status == 200){ 
			response = clean(xhr.responseXML.documentElement);
			var items = response.getElementsByTagName("virtual");
			//						var html  = '<select name="fVirtual" id="fVirtual" onChange="go_next()">\n';
			var html  = '<option value="-1"> --\n';
			for ( i=0;i<items.length;i++)
				{
					name = items[i].getElementsByTagName("name")[0].firstChild.nodeValue;
					id = items[i].getElementsByTagName("id")[0].firstChild.nodeValue;
					if ( name != ' ' ){
						name = name+'.';
					}
					html += '<option value="'+id+'">'+name+domain+'\n';
				}
			html  += '</select>\n';
			document.getElementById('fVirtualid').innerHTML = html;
			
		} 
	} 

	xhr.open("POST","../ajax/domain_info.php",true); 
	xhr.setRequestHeader('Content-Type','application/x-www-form-urlencoded'); 
	sel = document.getElementById('fDomain');
	domain = sel.options[sel.selectedIndex].value; 
	xhr.send("fDomain="+domain+"&fType=virtual_list"); 

} 



function check_ftpaccount(){ 
		var xhr = getXhr(); 
		var vButton = document.getElementById("submit");
		xhr.onreadystatechange = function(){ 
				if(xhr.readyState == 4 && xhr.status == 200){ 
						response = clean(xhr.responseXML.documentElement);
						var items = response.getElementsByTagName("virtual");
						exist = items[0].getElementsByTagName("exist")[0].firstChild.nodeValue;
						if ( exist != 0 ){
						  var html  = 'KO <?php print "already exist;"?>';
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

		xhr.open("POST","../ajax/domain_info.php",true); 
		xhr.setRequestHeader('Content-Type','application/x-www-form-urlencoded'); 
		account = document.getElementById('fLogin').value;
		sel = document.getElementById('fDomain');
		domain = sel.options[sel.selectedIndex].value;
		xhr.send("fDomain="+domain+"&fType=account_exist&fAccount="+account); 
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
