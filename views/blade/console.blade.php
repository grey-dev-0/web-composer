@section('styles')
	<link rel="stylesheet" href="{{asset('public/vendor/grey-dev-0/web-composer/css/console.min.css')}}">
@append

@section('content')
	<div class="row" id="console">
		<div id="console-tab" class="bg-faded text-xs-right">
			<div class="btn btn-secondary btn-sm" id="open-console">Console</div>
		</div>
		<div id="console-content" class="hidden-xs-up hidden-xs-down"></div>
	</div>
@append