$(document).ready(function(){
	$('body').on('click', '.list-group .btn-sm.btn-outline-info', function(){
		// Viewing a package's description on a modal dialog when requested.
		var composerPackage = $(this).closest('.list-group-item');
		var name = composerPackage.find('.name').text();
		var version = composerPackage.find('.version').text();
		var description = composerPackage.find('.description').text();
		if(description == ''){
			description = '<i class="material-icons loader">rotate_right</i>';
			var modal = showPackageDetails(name, version, description);
			getPackageDetails(name, function(data){
				modal.find('.bootbox-body').text(data.description);
				composerPackage.find('.description').text(data.description);
			});
		} else
			showPackageDetails(name, version, description);
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
					var version = (data.packages[i].version === null)? '' : data.packages[i].version;
					var options = (data.packages[i].installed)?
						'<div class="btn btn-sm btn-outline-success" style="margin-right:4px"><i class="material-icons">update</i></div>\
						<div class="btn btn-sm btn-outline-danger" style="margin-right:4px"><i class="material-icons">delete_forever</i></div>' :
						'<div class="btn btn-sm btn-outline-success install" style="margin-right:4px"><i class="material-icons">file_download</i></div>';
					var description = (data.packages[i].description === null || data.packages[i].description == '')? '' :
						data.packages[i].description;
					packages.append('<div class="list-group-item list-group-item-action">\
						<span class="name">'+data.packages[i].name+'</span>\
					<div class="float-xs-right">\
						<span class="version text-muted">'+version+'</span>'+options+
						'<div class="btn btn-sm btn-outline-info"><i class="material-icons">info_outline</i></div>\
						</div>\
						<div class="hidden-xl-up hidden-xl-down description">'+description+'</div>\
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

	function showPackageDetails(name, version, description){
		return bootbox.dialog({
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
	}

	function getPackageDetails(name, callback){
		$.ajax({
			type: 'POST',
			url: urls.refreshPackage,
			data: { name: name },
			success: callback,
			error: onErrorResponse
		});
	}

	function onErrorResponse(xhr){
		bootbox.alert('Something wrong has happened please check the developer console for more details.');
		console.log(xhr.responseText);
	}
});