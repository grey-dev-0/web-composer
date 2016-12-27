{config_load file="master.conf"}
<!DOCTYPE html>
<html>
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1">
	<meta http-equiv="X-UA-Compatible" content="ie=edge">
	<title>{#title#}</title>
	<link rel="stylesheet" href="{#baseUrl#}/assets/css/bootstrap.min.css">
	<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
	{block name="styles"}CSS files and style tags{/block}
	<script type="text/javascript" src="{#baseUrl#}/assets/js/jquery-2.1.0.min.js"></script>
	<script type="text/javascript" src="{#baseUrl#}/assets/js/bootstrap.min.js"></script>
</head>
<body>
<nav class="navbar navbar-fixed-top navbar-light bg-faded">
	<div class="container">
		<div class="navbar-brand">Composer</div>
		<ul class="navbar-nav nav">
			<li class="nav-item"><a class="nav-link" href="url(config('web-composer.prefix').'/installed')">Installed Packages</a></li>
			<li class="nav-item"><a class="nav-link" href="url(config('web-composer.prefix').'/all')">All Packages</a></li>
		</ul>
		<a href="url(config('web-composer.app_url'))" class="btn btn-outline-primary float-xs-right">Application Home</a>
	</div>
</nav>
<div id="nav-placeholder"></div>
<div class="container">
	{block name="content"}Content{/block}
</div>
<script type="text/javascript">
	var currentUrl = 'Request::url()'
</script>
<script type="text/javascript" src="{#baseUrl#}/assets/js/master.min.js"></script>
{block name="scripts"}JS files and script tags{/block}
</body>
</html>