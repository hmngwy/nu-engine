<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN"
"http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>

<title>Nu-Engine Application</title>
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
</head>

<body id="home">
	<div id="content">
		<h1 id="welcome">Nu-Engine Application</h1>
		<p>You're seeing this because your installation went well.</p>
		<p><?php echo $data['message']; ?></p>
	</div>
</body>

</html>