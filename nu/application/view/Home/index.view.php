<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN"
"http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>

<title>NU-Engine Installation</title>
<style type="text/css" media="screen">
	body
	{
		padding:30px;
		margin:0;
		font-family:Arial, sans-serif;
	}
	h1#welcome
	{
		margin:0;
	}
	code
	{
		font-size:16px;
		margin-left:20px;
	}
</style>
<script type="text/javascript" charset="utf-8">
	function badRewriteBase()
	{
		URI = "<?php echo str_replace('?'.$_SERVER["QUERY_STRING"], '', $_SERVER["REQUEST_URI"]) ?>";
		HTACCESS = "<?php echo $_SERVER["DOCUMENT_ROOT"] ?>"+URI+'.htaccess';
		
		header = document.getElementById('welcome');
		header.innerHTML = 'NU-Engine Installation Configuration Required';
		
		notes = document.getElementById('notes');
		message  = '<p>Oops, you must change the <b>RewriteBase</b> line in <b>'+HTACCESS+'</b> to:</p>';
		message += '<code>RewriteBase '+URI+'</code>';
		
		notes.innerHTML = message;
	}
	
	function installationSuccess()
	{
		header = document.getElementById('welcome');
		header.innerHTML = 'NU-Engine Installation Successful';
		
		notes = document.getElementById('notes');
	}
	
	function loadXMLDoc(url)
	{
		xmlhttp=null;
		if (window.XMLHttpRequest)
		{// code for all new browsers
			xmlhttp=new XMLHttpRequest();
		}
		else if (window.ActiveXObject)
		{// code for IE5 and IE6
			xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
		}
		if (xmlhttp!=null)
		{
			xmlhttp.onreadystatechange=state_Change;
			xmlhttp.open("GET",url,true);
			xmlhttp.send(null);
		}
		else
		{
			alert("Your browser does not support XMLHTTP.");
		}
	}
	
	function state_Change()
	{
		if (xmlhttp.readyState==4)
		{
			if (xmlhttp.status==200)
			{// 200 = OK
				if(xmlhttp.responseText != "testSuccess")
				{
					badRewriteBase();
				}
				else
				{
					installationSuccess();
				}
			}
			else
			{
				badRewriteBase();
			}
		}
	}
	
	loadXMLDoc("home/test/");
</script>
</head>

<body id="home">
	<div id="content">
		<h1 id="welcome"></h1>
		<div id="body">
			<div id="notes">
				
				<p>Your Installation is successful.</p><p>Start by editting this file at :</p><code>./nu/application/view/Home/index.view.php</code>
			
			</div>
		</div>
	</div>
</body>

</html>