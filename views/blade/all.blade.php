@extends('web-composer::master')

@section('styles')
	<link rel="stylesheet" href="{{asset('public/vendor/grey-dev-0/web-composer/css/packages.min.css')}}">
@stop

@section('content')
	<div class="row">
		<div class="col-xs-12">
			<h4 class="float-xs-left">All Packages</h4>
			<div id="search">
				<input type="text" placeholder="Search" class="form-control float-xs-right card-outline-info">
				<div id="search-actions">
					<i class="material-icons" id="search-btn">search</i>
					<i class="material-icons text-muted hidden-xs-up hidden-xs-down" id="search-clear">clear</i>
				</div>
			</div>
		</div>
		<div class="col-xs-12">
			@if($packagesCount > 0)
				<div class="card card-outline-success">
					<div class="list-group">
						@foreach($packages as &$package)
							<div class="list-group-item list-group-item-action">
								<span class="name">{{$package->name}}</span>
								<div class="float-xs-right">
									<span class="version text-muted">{{$package->version}}</span>
									@if($package->installed)
										<div class="btn btn-sm btn-outline-success update"><i class="material-icons">update</i></div>
										<div class="btn btn-sm btn-outline-danger"><i class="material-icons">delete_forever</i></div>
									@else
										<div class="btn btn-sm btn-outline-success install"><i class="material-icons">file_download</i></div>
									@endif
									<div class="btn btn-sm btn-outline-info"><i class="material-icons">info_outline</i></div>
								</div>
								<div class="hidden-xl-up hidden-xl-down description">{{$package->description}}</div>
								<ul class="hidden-xl-up hidden-xl-down versions">
									@foreach($package->available_versions as &$version)
										<li>{{$version}}</li>
									@endforeach
								</ul>
							</div>
						@endforeach
					</div>
				</div>
			@else
				<div class="card card-outline-info">
					<div class="card-block">
						<i class="material-icons text-info" style="vertical-align:middle;margin-bottom:4px">info_outline</i> All packages cache is being refreshed, please come back again after a while..
					</div>
				</div>
			@endif
		</div>
		@if($packagesCount > 0)
			<div class="col-xs-12 text-xs-center">
				<ul class="pagination">
					<li class="page-item disabled">
						<a class="page-link" href="#">
							<span><i class="material-icons">chevron_left</i></span>
						</a>
					</li>
					@for($i = 0; $i < 10; $i++)
						<li class="page-item @if($i == 0) active @endif" data-page="{{$i+1}}"><a class="page-link" href="#">{{$i+1}}</a></li>
					@endfor
					<li class="page-item" data-page="after"><a class="page-link" href="#">...</a></li>
					<li class="page-item" data-page="{{$final = ceil($packagesCount/10.0)}}"><a class="page-link" href="#">{{$final}}</a></li>
					<li class="page-item">
						<a class="page-link" href="#">
							<span><i class="material-icons">chevron_right</i></span>
						</a>
					</li>
				</ul>
			</div>
		@endif
	</div>
@stop

@section('scripts')
	<script type="text/javascript">
		var urls = {
			consoleOutput: '{{url(config('web-composer.prefix').'/console')}}',
			packagesListing: '{{url(config('web-composer.prefix').'/ajax-all')}}',
			removePackage: '{{url(config('web-composer.prefix').'/remove-package')}}',
			refreshPackage: '{{url(config('web-composer.prefix').'/refresh-package')}}',
			updatePackage: '{{url(config('web-composer.prefix').'/upgrade-package')}}',
			searchPackages: '{{url(config('web-composer.prefix').'/ajax-search/all')}}'
		}
	</script>
	<script type="text/javascript" src="{{asset('public/vendor/grey-dev-0/web-composer/js/bootbox.min.js')}}"></script>
	<script type="text/javascript" src="{{asset('public/vendor/grey-dev-0/web-composer/js/console.min.js')}}"></script>
	<script type="text/javascript" src="{{asset('public/vendor/grey-dev-0/web-composer/js/packages.min.js')}}"></script>
@stop

@include('web-composer::console')