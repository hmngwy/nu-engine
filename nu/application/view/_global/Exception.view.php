<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN"
"http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>

<title><?php echo $data['title']; ?></title>
<style type="text/css" media="screen">
	body
	{
		margin:0;
		padding:0;
		padding:30px 25px 0px 25px;
		font-family:Arial, sans-serif;
		
	}
	
	div#message
	{
		background:#F4F4F4;
		border:1px solid #CCC;
		padding:20px;
	}
	
	div#message h1#subject
	{
		margin:0;
		padding:0;
		font-size:25px;
	}
	
	div#message p#text
	{
		font-size:14px;
		color:#666;
	}
</style>
</head>

<body>

	<div id="message" class="block">
		<h1 id="subject"><?php echo $data['subject']; ?></h1>
		<p id="text"><?php echo $data['body']; ?></p>
	</div>
	
	<?php if($data['debug']): ?>
	<div id="debug" class="block">
		<pre><?php print_r($data['registry']); ?></pre>
	</div>
	<?php endif;?>
	
</body>

</html>