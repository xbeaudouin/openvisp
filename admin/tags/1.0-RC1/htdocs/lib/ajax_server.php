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

srv_fqdn_available = 0;
srv_name_available = 0;
//Do not check IP
srv_prv_ip = 1;
srv_pub_ip = 1;

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


function check_modelserver(){
		var xhr = getXhr(); 
		var vButton = document.getElementById("submit");
		xhr.onreadystatechange = function(){ 
				if(xhr.readyState == 4 && xhr.status == 200){ 
						response = clean(xhr.responseXML.documentElement);
						var items = response.getElementsByTagName("role_name");
						exist = items[0].getElementsByTagName("available")[0].firstChild.nodeValue;
						//var exist = items[0].firstChild.nodeValue;

						//						items[i].getElementsByTagName("name").firstChild.nodeValue;
						if ( exist != 1 ){
						  var html  = '<?php print $PALANG['pServerModel_exist'];?>';
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
						document.getElementById('model_status').innerHTML = html;

				} 
		} 

		xhr.open("POST","../ajax/server_model.php",true); 
		xhr.setRequestHeader('Content-Type','application/x-www-form-urlencoded'); 
		role_name = document.getElementById('fRole_name').value;
		xhr.send("fRole_name="+role_name+"&fType=model"); 

}



function check_server_pub_ip2(){

		var xhr = getXhr(); 
		var vButton = document.getElementById("submit");
		xhr.onreadystatechange = function(){ 
				if(xhr.readyState == 4 && xhr.status == 200){ 
						response = clean(xhr.responseXML.documentElement);
						var items = response.getElementsByTagName("server_name");
						srv_pub_ip = items[0].getElementsByTagName("available")[0].firstChild.nodeValue;
						//var exist = items[0].firstChild.nodeValue;

						//						items[i].getElementsByTagName("name").firstChild.nodeValue;
						if ( srv_pub_ip != 1 ){
						  var html  = '<?php print $PALANG['pServer_exist'];?>';

						}
						else{
						  var html  = 'OK';
						}
						check_all();
						document.getElementById('server_status').innerHTML = html;

				} 
		} 

		xhr.open("POST","../ajax/server_info.php",true); 
		xhr.setRequestHeader('Content-Type','application/x-www-form-urlencoded'); 
		server_name = document.getElementById('fServer_name').value;
		xhr.send("fServer_name="+server_name+"&fType=server"); 


}


function check_server_prv_ip(){

		var xhr = getXhr(); 
		var vButton = document.getElementById("submit");
		xhr.onreadystatechange = function(){ 
				if(xhr.readyState == 4 && xhr.status == 200){ 
						response = clean(xhr.responseXML.documentElement);
						var items = response.getElementsByTagName("server_prv_ip");
						srv_prv_ip = items[0].getElementsByTagName("available")[0].firstChild.nodeValue;

						if ( srv_prv_ip != 1 ){
						  var html  = '<?php print $PALANG['pServer_ip_prv_used'];?>';
						}
						else{
						  var html  = 'OK';
						}
						// Actually we do not check ip
						srv_prv_ip = 1;
						check_all();
						document.getElementById('server_prv_ip_status').innerHTML = html;

				} 
		} 

		xhr.open("POST","../ajax/server_info.php",true); 
		xhr.setRequestHeader('Content-Type','application/x-www-form-urlencoded'); 
		server_prv_ip = document.getElementById('fServer_prv_ip').value;
		server_form_name = document.getElementById('formname').value;
		server_id = document.getElementById('fServer_id').value;
		server_form_name = document.getElementById('formname').value;
		server_id = document.getElementById('fServer_id').value;

		//		var regexp = /\b(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]­?)\b/ ;	a
		var regexp = /\b((25[0-5]|2[0-4][0-9]|1[0-9]{1,2}|[0-9]{1,2})\.){3}(25[0-5]|2[0-4][0-9]|1([0-9]{1,2})|[1-9][0-9]|[1-9])\b/;

		if ( server_prv_ip.match(regexp)  && server_prv_ip != "" ){

			if ( (( server_prv_ip >= "10.0.0.1") && ( server_prv_ip <= "10.255.255.254")) ||
					 (( server_prv_ip >= "192.168.0.1") && ( server_prv_ip <= "192.168.255.254")) ||
					 (( server_prv_ip >= "172.16.0.1") && ( server_prv_ip <= "172.31.255.254")) ||
					 ( server_prv_ip == "127.0.0.1" )
					 ) {
				xhr.send("fServer_prv_ip="+server_prv_ip+"&fType=server&fServer_id="+server_id+"&fServer_form="+server_form_name+"&fServer_form="+server_form_name+"&fServer_id="+server_id); 
			}
			else{
				document.getElementById('server_prv_ip_status').innerHTML = '<?php print $PALANG['pServer_ip_notprv']; ?>';
			}

		}
		else{
			vButton.style.border = "1px solid #aaa";
			vButton.style.color = "#ffffff";
			vButton.style.background = "#f0f5fa";
			vButton.disabled = true;
			document.getElementById('server_prv_ip_status').innerHTML = '<?php print $PALANG['pServer_ip_notprv']; ?>';
		}

}

function check_server_pub_ip(){

		var xhr = getXhr(); 
		var vButton = document.getElementById("submit");
		xhr.onreadystatechange = function(){ 
				if(xhr.readyState == 4 && xhr.status == 200){ 
						response = clean(xhr.responseXML.documentElement);
						var items = response.getElementsByTagName("server_pub_ip");
						srv_pub_ip = items[0].getElementsByTagName("available")[0].firstChild.nodeValue;

						if ( srv_pub_ip != 1 ){
						  var html  = '<?php print $PALANG['pServer_ip_pub_used'];?>';
						}
						else{
						  var html  = 'OK';
						}
						// Do not check IP
						srv_pub_ip = 1;
						check_all();
						document.getElementById('server_pub_ip_status').innerHTML = html;

				} 
		} 

		xhr.open("POST","../ajax/server_info.php",true); 
		xhr.setRequestHeader('Content-Type','application/x-www-form-urlencoded'); 
		server_pub_ip = document.getElementById('fServer_pub_ip').value;
		server_prv_ip = document.getElementById('fServer_prv_ip').value;
		server_form_name = document.getElementById('formname').value;
		server_id = document.getElementById('fServer_id').value;

		//var regexp = /\b(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\b/ ;	
		var regexp = /((25[0-5]|2[0-4][0-9]|1[0-9]{1,2}|[0-9]{1,2})\.){3}(25[0-5]|2[0-4][0-9]|1([0-9][0-9])|[0-9]{1,2})/;
		//var regexp = /\b(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]­?)\b/ ;	

		if ( server_pub_ip.match(regexp) && server_pub_ip != "" ){

			if ( (( server_pub_ip >= "10.0.0.0") && ( server_pub_ip <= "10.255.255.255")) ||
					 (( server_pub_ip >= "192.168.0.0") && ( server_pub_ip <= "192.168.255.255")) ||
					 (( server_pub_ip >= "172.16.0.0") && ( server_pub_ip <= "172.31.255.255")) ) {

				document.getElementById('server_pub_ip_status').innerHTML = '<?php print $PALANG['pServer_ip_notpub']; ?>';
			}
			else{
					xhr.send("fServer_pub_ip="+server_pub_ip+"&fType=server&fServer_prv_ip="+server_prv_ip+"&fServer_form="+server_form_name+"&fServer_id="+server_id);
			}

		}
		else{
			vButton.style.border = "1px solid #aaa";
			vButton.style.color = "#ffffff";
			vButton.style.background = "#f0f5fa";
			vButton.disabled = true;
			document.getElementById('server_pub_ip_status').innerHTML = '';
		}

}


function check_all(){	

	var vButton = document.getElementById("submit");
	//	document.getElementById('fServer_desc').innerHTML = "NG : "+srv_name_available+" -- "+srv_fqdn_available + "||"+srv_pub_ip+"##"+srv_prv_ip;

	if ( srv_name_available == 1 &&
		 	 srv_fqdn_available == 1 &&
			 srv_prv_ip == 1 &&
			 srv_pub_ip == 1
			 )
		{
			vButton.style.border = "1px solid #bcd0ed";
			vButton.style.color = "#000000";
			vButton.style.background = "#f0f5fa";
			vButton.disabled = false;
			
		}
	else{
		vButton.style.border = "1px solid #aaa";
		vButton.style.color = "#ffffff";
		vButton.style.background = "#f0f5fa";
		vButton.disabled = true;
	}
}

function check_servername(){
		var xhr = getXhr(); 
		var vButton = document.getElementById("submit");
		xhr.onreadystatechange = function(){ 
				if(xhr.readyState == 4 && xhr.status == 200){ 
						response = clean(xhr.responseXML.documentElement);
						var items = response.getElementsByTagName("server_name");
						srv_name_available = items[0].getElementsByTagName("available")[0].firstChild.nodeValue;
						//var exist = items[0].firstChild.nodeValue;

						//						items[i].getElementsByTagName("name").firstChild.nodeValue;
						if ( srv_name_available != 1 ){
						  var html  = '<?php print $PALANG['pServer_exist'];?>';
							//							vButton.disabled = true;
						}
						else{
						  var html  = 'OK';
							//							vButton.disabled = false;
						}
						document.getElementById('server_name_status').innerHTML = html;
						check_all();
				} 
		} 

		xhr.open("POST","../ajax/server_info.php",true); 
		xhr.setRequestHeader('Content-Type','application/x-www-form-urlencoded'); 
		server_name = document.getElementById('fServer_name').value;
		server_form_name = document.getElementById('formname').value;
		server_id = document.getElementById('fServer_id').value;

		if ( server_name != "" ){
			xhr.send("fServer_name="+server_name+"&fType=server&fServer_form="+server_form_name+"&fServer_id="+server_id);
		}
		else{
			vButton.style.border = "1px solid #aaa";
			vButton.style.color = "#ffffff";
			vButton.style.background = "#f0f5fa";
			vButton.disabled = true;
			document.getElementById('server_name_status').innerHTML = '';
		}

}


function check_server_fqdn(){
		var xhr = getXhr(); 
		var vButton = document.getElementById("submit");
		xhr.onreadystatechange = function(){ 
				if(xhr.readyState == 4 && xhr.status == 200){ 
						response = clean(xhr.responseXML.documentElement);
						var items = response.getElementsByTagName("server_fqdn");
						srv_fqdn_available = items[0].getElementsByTagName("available")[0].firstChild.nodeValue;
						//var exist = items[0].firstChild.nodeValue;

						//						items[i].getElementsByTagName("name").firstChild.nodeValue;
						if ( srv_fqdn_available != 1 ){
						  var html  = '<?php print $PALANG['pServer_exist'];?>';
							vButton.style.border = "1px solid #aaa";
							vButton.style.color = "#ffffff";
							vButton.style.background = "#f0f5fa";
							//							vButton.disabled = true;
						}
						else{
						  var html  = 'OK';
							vButton.style.border = "1px solid #bcd0ed";
							vButton.style.color = "#000000";
							vButton.style.background = "#f0f5fa";
							// vButton.disabled = false;

						}
						document.getElementById('server_fqdn_status').innerHTML = html;
						check_all();
				} 
		} 

		xhr.open("POST","../ajax/server_info.php",true); 
		xhr.setRequestHeader('Content-Type','application/x-www-form-urlencoded'); 
		server_fqdn = document.getElementById('fServer_fqdn').value;
		server_form_name = document.getElementById('formname').value;
		server_id = document.getElementById('fServer_id').value;
		if ( server_fqdn != "" ){
			xhr.send("fServer_fqdn="+server_fqdn+"&fType=server&fServer_form="+server_form_name+"&fServer_id="+server_id); 
		}
		else{
			vButton.style.border = "1px solid #aaa";
			vButton.style.color = "#ffffff";
			vButton.style.background = "#f0f5fa";
			vButton.disabled = true;
			document.getElementById('server_fqdn_status').innerHTML = '';
		}

}


function display_role_app(tab_role){

	tab_role_name = 'role_app-' + tab_role;
	app_list = document.getElementById(tab_role_name);
	app_list.innerHTML = '';
	var new_table = ''
	var xhr = getXhr(); 
		
	xhr.onreadystatechange = function(){ 
		if(xhr.readyState == 4 && xhr.status == 200){ 
			response = clean(xhr.responseXML.documentElement);
			var items = response.getElementsByTagName("application");

			if ( items.length != null ){

				new_table = '<table border="3" class="subauto">';

				for (var i = 0; i < items.length; i++){
					app_id = items[i].getElementsByTagName("id")[0].firstChild.nodeValue;
					app_name = items[i].getElementsByTagName("name")[0].firstChild.nodeValue;
					app_version = items[i].getElementsByTagName("version")[0].firstChild.nodeValue;
					new_table += '<tr>';
					new_table += '<td>';
					new_table += '<input type="checkbox" name="role-'+tab_role+'_app-'+app_id+'" onclick="display_hidden(\'login_info-'+app_id+'\')">'+app_name+" "+app_version+"<br/ >\n\n";
					new_table += '<div id="login_info-'+app_id+'" style="display:none">'
					new_table += '<?php print $PALANG['pApplication_login'];?><input type="text" name="login_app-'+app_id+'" id="login_app-'+app_id+'" value=""><br/ >\n\n';
					new_table += '<?php print $PALANG['pApplication_password'];?><input type="text" name="pass_app-'+app_id+'" id="pass_app-'+app_id+'" value=""><br/ >\n\n';
					new_table += '</div>';
					//					new_table += app_id+" = "+app_name;
					new_table += '</td>';
					new_table += '</tr>';
				}

			}
			new_table += '</table>';
			app_list.innerHTML = new_table;
		}
	}

	xhr.open("POST","../ajax/approle_info.php",true); 
	xhr.setRequestHeader('Content-Type','application/x-www-form-urlencoded'); 
	xhr.send("fRole_id="+tab_role); 	

	if ( app_list.style.display == "none" ){
		app_list.style.display = "inline";
	}
	else{
		app_list.style.display = "none";
	}
}


function display_role_app2(){

	appmodel_id = document.getElementById('role_id').value;
	form_app_id = document.getElementById('form_app_id');
	form_app_id.innerHTML = '';
	var new_table = ''
	var xhr = getXhr();

	xhr.onreadystatechange = function(){ 
		if(xhr.readyState == 4 && xhr.status == 200){ 
			response = clean(xhr.responseXML.documentElement);
			var items = response.getElementsByTagName("application");

			if ( items.length != null ){

				new_table = '<select name="app_id">';

				for (var i = 0; i < items.length; i++){
					app_id = items[i].getElementsByTagName("id")[0].firstChild.nodeValue;
					app_name = items[i].getElementsByTagName("name")[0].firstChild.nodeValue;
					app_version = items[i].getElementsByTagName("version")[0].firstChild.nodeValue;

					new_table += '<option value="' + app_id + '">' + app_name + ' ' + app_version+ '</option>\n\n';
				}

			}
			new_table += '</select>\n</td>';
			form_app_id.innerHTML = new_table;
		}
	}

	xhr.open("POST","../ajax/approle_info.php",true); 
	xhr.setRequestHeader('Content-Type','application/x-www-form-urlencoded'); 
	xhr.send("fAppmodel_id="+appmodel_id); 	



	if ( app_list.style.display == "none" ){
		app_list.style.display = "inline";
	}
	else{
		app_list.style.display = "none";
	}
}


function display_hidden(element_name){

	var element  = document.getElementById(element_name);
	if ( element.style.display == "none" ){
		element.style.display = "inline";
	}
	else{
		element.style.display = "none";
	}
	

}

function check_application(){
		var xhr = getXhr(); 
		var vButton = document.getElementById("submit");
		xhr.onreadystatechange = function(){ 
				if(xhr.readyState == 4 && xhr.status == 200){ 
						response = clean(xhr.responseXML.documentElement);
						var items = response.getElementsByTagName("app_name");
						exist = items[0].getElementsByTagName("available")[0].firstChild.nodeValue;
						//var exist = items[0].firstChild.nodeValue;

						//						items[i].getElementsByTagName("name").firstChild.nodeValue;
						if ( exist != 1 ){
						  var html  = '<?php print $PALANG['pServerModel_exist'];?>';
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
						document.getElementById('app_status').innerHTML = html;

				} 
		} 

		xhr.open("POST","../ajax/server_app.php",true); 
		xhr.setRequestHeader('Content-Type','application/x-www-form-urlencoded'); 
		app_name = document.getElementById('fApp_name').value;
		app_version = document.getElementById('fApp_version').value;
		xhr.send("fApp_name="+app_name+"&fApp_version="+app_version); 

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
