@extends('web-composer::master')

@section('styles')
	<link rel="stylesheet" href="{{asset('public/vendor/grey-dev-0/web-composer/css/packages.min.css')}}">
@stop

@section('content')
	<div class="row">
		<div class="col-xs-12">
			<h4 class="float-xs-left">Installed Packages</h4>
			<input type="text" placeholder="Search" id="search" class="form-control float-xs-right card-outline-info">
		</div>
		<div class="col-xs-12">
			<div class="card card-outline-success">
				<div class="list-group">
					@foreach($packages as &$package)
						<div class="list-group-item list-group-item-action">
							<span class="name">{{$package->name}}</span>
							<div class="float-xs-right">
								<span class="version text-muted">{{$package->version}}</span>
								<div class="btn btn-sm btn-outline-success"><i class="material-icons">update</i></div>
								<div class="btn btn-sm btn-outline-danger"><i class="material-icons">delete_forever</i></div>
								<div class="btn btn-sm btn-outline-info"><i class="material-icons">info_outline</i></div>
							</div>
							<div class="hidden-xl-up hidden-xl-down">{{$package->description}}</div>
						</div>
					@endforeach
				</div>
			</div>
		</div>
		<div class="col-xs-12 text-xs-center">
			<ul class="pagination">
				<li class="page-item disabled">
					<a class="page-link" href="#">
						<span><i class="material-icons">chevron_left</i></span>
					</a>
				</li>
				@for($i = 0; $i < ceil($packagesCount/10.0); $i++)
					<li class="page-item @if($i == 0) active @endif" data-page="{{$i+1}}"><a class="page-link" href="#">{{$i+1}}</a></li>
				@endfor
				<li class="page-item">
					<a class="page-link" href="#">
						<span><i class="material-icons">chevron_right</i></span>
					</a>
				</li>
			</ul>
		</div>
	</div>
@stop

@section('scripts')
	<script type="text/javascript">
		var urls = { packagesListing: '{{url(config('web-composer.prefix').'/ajax-installed')}}' }
	</script>
	<script type="text/javascript" src="{{asset('public/vendor/grey-dev-0/web-composer/js/bootbox.min.js')}}"></script>
	<script type="text/javascript" src="{{asset('public/vendor/grey-dev-0/web-composer/js/packages.min.js')}}"></script>
@stop