{extends file='master.tpl'}

{block name='styles'}
	<link rel="stylesheet" href="{#baseUrl#}/assets/css/packages.min.css">
{/block}

{block name='content'}
	<div class="row">
		<div class="col-xs-12">
			<h4 class="float-xs-left">Installed Packages</h4>
			<div id="search">
				<input type="text" placeholder="Search" class="form-control float-xs-right card-outline-info">
				<div id="search-actions">
					<i class="material-icons" id="search-btn">search</i>
					<i class="material-icons text-muted hidden-xs-up hidden-xs-down" id="search-clear">clear</i>
				</div>
			</div>
		</div>
		<div class="col-xs-12">
			<div class="card card-outline-success">
				<div class="list-group">
					{if $packagesCount > 0}
						{foreach $packages as $package}
							<div class="list-group-item list-group-item-action">
								<span class="name">{$package->name}</span>
								<div class="float-xs-right">
									<span class="version text-muted">{$package->version}</span>
									{if $package->installed}
										<div class="btn btn-sm btn-outline-success update"><i class="material-icons">update</i></div>
										<div class="btn btn-sm btn-outline-danger"><i class="material-icons">delete_forever</i></div>
									{else}
										<div class="btn btn-sm btn-outline-success install"><i class="material-icons">file_download</i></div>
									{/if}
									<div class="btn btn-sm btn-outline-info"><i class="material-icons">info_outline</i></div>
								</div>
								<div class="hidden-xl-up hidden-xl-down">{$package->description}</div>
								<ul class="hidden-xl-up hidden-xl-down versions">
									{foreach $package->available_versions as $version}
										<li>{$version}</li>
									{/foreach}
								</ul>
							</div>
						{/foreach}
					{else}
						<div class="card card-outline-info">
							<div class="card-block">
								<i class="material-icons text-info" style="vertical-align:middle;margin-bottom:4px">info_outline</i> All packages cache is being refreshed, please come back again after a while..
							</div>
						</div>
					{/if}
				</div>
			</div>
		</div>
		{if $packagesCount > 0}
			<div class="col-xs-12 text-xs-center">
				<ul class="pagination">
					<li class="page-item disabled">
						<a class="page-link" href="#">
							<span><i class="material-icons">chevron_left</i></span>
						</a>
					</li>
					{for $i = 0; $i < 10; $i++}
					<li class="page-item {if $i == 0} active {/if}" data-page="{$i+1}"><a class="page-link" href="#">{$i+1}</a></li>
					{/for}
					<li class="page-item" data-page="after"><a class="page-link" href="#">...</a></li>
					<li class="page-item" data-page="{$final = ceil($packagesCount/10.0)}"><a class="page-link" href="#">{$final}</a></li>
					<li class="page-item">
						<a class="page-link" href="#">
							<span><i class="material-icons">chevron_right</i></span>
						</a>
					</li>
				</ul>
			</div>
		{/if}
	</div>
{/block}

{block name='scripts'}
	<script type="text/javascript">
		var urls = {
			consoleOutput: '/{#prefix#}/console',
			packagesListing: '/{#prefix#}/ajax-all',
			removePackage: '/{#prefix#}/remove-package',
			refreshPackage: '/{#prefix#}/refresh-package',
			updatePackage: '/{#prefix#}/upgrade-package',
			searchPackages: '/{#prefix#}/ajax-search/all',
			clearConsole: '/{#prefix#}/clear-console'
		};
	</script>
	<script type="text/javascript" src="{#baseUrl#}/assets/js/bootbox.min.js"></script>
	<script type="text/javascript" src="{#baseUrl#}/assets/js/console.min.js"></script>
	<script type="text/javascript" src="{#baseUrl#}/assets/js/packages.min.js"></script>
{/block}