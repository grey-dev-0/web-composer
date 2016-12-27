$(document).ready(function(){
	$('body').on('click', '.list-group .btn-sm.btn-outline-info', function(){
		// Viewing a package's description on a modal dialog when requested.
		var composerPackage = $(this).closest('.list-group-item');
		var name = composerPackage.find('.name').text();
		var version = composerPackage.find('.version').text();
		var description = composerPackage.find('.hidden-xl-up.hidden-xl-down').text();
		bootbox.dialog({
			title: name+' <small class="text-muted">'+version+'</small>',
			message: description,
			backdrop: true,
			onEscape: true,
			buttons: {
				ok: {
					label: 'OK',
					className: 'btn-outline-primary'
				}
			}
		});
	}).on('click', '.page-link', function(e){
		// Changing pages when requested.
		e.preventDefault();
		var pageLink = $(this);
		var pageIndex = pageLink.parent('.page-item').index();
		var linksCount = pageLink.closest('.pagination').find('.page-item').length;
		var oldPageNumber = parseInt(pageLink.closest('.pagination').find('.active').attr('data-page'));
		var pageNumber;
		switch(true){
			case (pageIndex == 0): pageNumber = oldPageNumber - 1; break;
			case (pageIndex == linksCount - 1):
				pageNumber = oldPageNumber + 1; break;
			default: pageNumber = parseInt($(this).text());
		}
		var offset = (pageNumber - 1)*10;
		$.ajax({
			url: urls.packagesListing+'/'+offset+'/10',
			type: 'GET',
			success: function(data){
				var packages = $('.list-group')
				packages.empty();
				for(i in data.packages){
					packages.append('<div class="list-group-item list-group-item-action">\
						<span class="name">'+data.packages[i].name+'</span>\
					<div class="float-xs-right">\
						<span class="version text-muted">'+data.packages[i].version+'</span>\
					<div class="btn btn-sm btn-outline-success"><i class="material-icons">update</i></div>\
						<div class="btn btn-sm btn-outline-danger"><i class="material-icons">delete_forever</i></div>\
						<div class="btn btn-sm btn-outline-info"><i class="material-icons">info_outline</i></div>\
						</div>\
						<div class="hidden-xl-up hidden-xl-down">'+data.packages[i].description+'</div>\
					</div>')
				}
				pageLink.closest('.pagination').find('.page-item').removeClass('active');
				if(pageNumber == 1)
					pageLink.closest('.pagination').find('.page-item:first').addClass('disabled');
				else
					pageLink.closest('.pagination').find('.page-item:first').removeClass('disabled');
				if(pageNumber == linksCount - 2)
					pageLink.closest('.pagination').find('.page-item:last').addClass('disabled');
				else
					pageLink.closest('.pagination').find('.page-item:last').removeClass('disabled');
				$('[data-page="'+pageNumber+'"]').addClass('active');
			},
			error: onErrorResponse
		});
	});

	function onErrorResponse(xhr){
		bootbox.alert('Something wrong has happened please check the developer console for more details.');
		console.log(xhr.responseText);
	}
});