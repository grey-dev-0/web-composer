$(document).ready(function(){
	// Initializing composer console output viewer.
	var composerConsole = new ComposerConsole(urls.consoleOutput);
	// Packages list is a search result or a standard output.
	var isSearch = false;
	// Pagination related variables.
	var pageLink, pageNumber, oldPageNumber, linksCount, replacePagination = false;
	// Original pagination data.
	var originalPages = $('.pagination').clone();
	// Pacakge object that can be installed or updated.
	var package = {};
	// Flag that indicates the modification operation is either an update or require.
	var isUpdate;

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
				composerPackage.find('.versions').html(renderVersionsListItems(data.available_versions));
			});
		} else
			showPackageDetails(name, version, description);
	}).on('click', '.list-group .btn-sm.btn-outline-danger', function(){
		// Deleting a package.
		var packageName = $(this).closest('.list-group-item').find('.name').text();
		bootbox.confirm('Are you sure about deleting '+packageName+' from the application?', function(confirm){
			if(confirm){
				$.ajax({
					url: urls.removePackage,
					type: 'POST',
					data: { package: packageName },
					success: function(){ bootbox.alert('The package is being uninstalled, please refer to console for more details.'); },
					error: onErrorResponse
				});
			}
		});
	}).on('click', '.list-group .btn-sm.btn-outline-success', function(){
		// Opening a new update or require package modal.
		var operation = $(this).find('i').text();
		var packageRecord = $(this).closest('.list-group-item');
		package.name = packageRecord.find('.name').text();
		var title;
		if(operation == 'update'){
			package.currentVersion = packageRecord.find('.version').text();
			title = 'Update '+package.name+' <small class="text-muted">'+package.currentVersion+'</small>';
			isUpdate = true;
		} else{
			title = 'Install '+package.name;
			package.currentVersion = null;
			isUpdate = false;
		}
		openUpdateRequireModal({
			title: title,
			message: '<i class="material-icons loader">rotate_right</i>',
			buttons: {
				ok: {
					label: ((isUpdate)? 'Update' : 'Install'),
					className: 'btn-outline-success update',
					callback: updateRequirePackage
				},
				cancel: {
					label: 'Cancel',
					className: 'btn-outline-secondary'
				}
			}
		});
	}).on('click', '.page-link', function(e){
		// Changing pages when requested.
		e.preventDefault();
		pageLink = $(this);
		setPageNumber(pageLink);
		var offset = (pageNumber - 1)*10;
		$.ajax({
			url: ((!isSearch)? urls.packagesListing : urls.searchPackages)+'/'+offset+'/10',
			type: 'POST',
			data: { query: $('#search').find('input').val() },
			success: renderPackages,
			error: onErrorResponse
		});
	}).on('click', '#open-console', function(){
		// Opening console view.
		composerConsole.view();
	}).on('click', '#clear-console', function(){
		// Clearing console output.
		composerConsole.clear();
	}).on('click', '#refresh-console', function(){
		// Refreshing console output.
		composerConsole.autoRefresh();
		$('#refresh-console').find('i').toggleClass('in-sync');
	}).on('click', '#search-btn', search).on('change', '#search input', function(evt){
		// Searching for a package
		if($(this).val() != '')
			search();
		else
			clearSearch();
	})	// Clearing search results.
	.on('click', '#search-clear', clearSearch).on('click', '[data-target="#dependencies-list"]', function(){
		// Toggling dependencies list collapse button icon
		var icon = $(this).find('i');
		icon.text((icon.text() == 'keyboard_arrow_down') ? 'keyboard_arrow_up' : 'keyboard_arrow_down');
	});

	function search(){
		isSearch = true;
		replacePagination = true;
		$('#search-clear').removeClass('hidden-xs-down hidden-xs-up');
		pageNumber = 1;
		$.ajax({
			url: urls.searchPackages+'/0/10',
			type: 'POST',
			data: { query: $('#search').find('input').val() },
			success: renderPackages,
			error: onErrorResponse
		});
	}

	function clearSearch(){
		if(isSearch){
			$('.pagination').before(originalPages);
			$('.pagination:last').remove();
		}
		isSearch = false;
		$('#search-clear').addClass('hidden-xs-down hidden-xs-up');
		$('#search').find('input').val('');
		$('.page-item:eq(1) .page-link').trigger('click');
	}

	function setPageNumber(pageLink){
		var pageIndex = pageLink.parent('.page-item').index();
		linksCount = pageLink.closest('.pagination').find('.page-item').length;
		oldPageNumber = parseInt(pageLink.closest('.pagination').find('.active').attr('data-page'));
		switch(true){
			case (pageIndex == 0): pageNumber = oldPageNumber - 1; break;
			case (pageIndex == linksCount - 1):
				pageNumber = oldPageNumber + 1; break;
			default:
				if(pageLink.text() == '...'){
					var direction = pageLink.parent('.page-item').attr('data-page');
					if(direction == 'after')
						pageNumber = parseInt(pageLink.parent('.page-item').prev().attr('data-page')) + 1;
					else
						pageNumber = parseInt(pageLink.parent('.page-item').next().attr('data-page')) - 1;
				} else
					pageNumber = parseInt(pageLink.text());
		}
	}

	function renderPackages(data){
		var packages = $('.list-group');
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
		if(replacePagination){
			switchPagination(data.packagesCount);
			replacePagination = false;
		} else{
			pageLink.closest('.pagination').find('.page-item').removeClass('active');
			if(pageNumber == 1)
				pageLink.closest('.pagination').find('.page-item:first').addClass('disabled');
			else
				pageLink.closest('.pagination').find('.page-item:first').removeClass('disabled');
			if(pageNumber == linksCount - 2)
				pageLink.closest('.pagination').find('.page-item:last').addClass('disabled');
			else
				pageLink.closest('.pagination').find('.page-item:last').removeClass('disabled');
			var newPageLink = $('[data-page="'+pageNumber+'"]');
			if(newPageLink.length > 0)
				newPageLink.addClass('active');
			else if(pageNumber < oldPageNumber)
				replacePages(pageNumber - 9, pageNumber, linksCount, pageNumber);
			else if(pageNumber > oldPageNumber)
				replacePages(pageNumber, pageNumber + 9, linksCount, pageNumber);
		}
	}

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

	/**
	 * Replace page numbers for overflowing pagination.
	 *
	 * @param start int First page number to be started from.
	 * @param end int Last page number to be ended to.
	 * @param linksCount int Count of links of the pagination.
	 * @param activePageNumber int Current page number.
	 */
	function replacePages(start, end, linksCount, activePageNumber){
		var collection = $();
		$('.page-item').each(function(i, page){
			if($(page).attr('data-page') !== undefined && $(page).index() != linksCount - 2)
				collection = collection.add($(page));
		});
		var lastPage = $('.page-item:eq('+(linksCount-2)+')');
		collection.remove();
		if(start != 1)
			$('.page-item:first').after('<div class="page-item" data-page="before"><a href="#" class="page-link">...</a></div>');
		for(var i = start; i <= end; i++)
			lastPage.before('<div class="page-item" data-page="'+i+'"><a href="#" class="page-link">'+i+'</a></div>');
		if(end != lastPage.attr('data-page'))
			lastPage.before('<div class="page-item" data-page="after"><a href="#" class="page-link">...</a></div>');
		if(activePageNumber !== undefined)
			$('[data-page="'+activePageNumber+'"]').addClass('active');
	}

	/**
	 * Switching pagination from standard output to search results pagination.
	 *
	 * @param packagesCount int Count of resultant packages to compute required number of pages.
	 */
	function switchPagination(packagesCount){
		$('.pagination').before($('<ul class="pagination"/>').append(originalPages.find('.page-item:first, .page-item:last').clone()));
		$('.pagination:last').detach();
		var pages = Math.floor(packagesCount / 10.0);
		for(var i = 1; i <= pages; i++)
			$('.page-item:last').before($('<div class="page-item" data-page="'+i+'"><a href="#" class="page-link">'+i+'</a></div>'));
		$('.page-item:eq(1)').addClass('active');
	}

	function renderVersionsListItems(versions){
		var listItems = '';
		for(var i in versions)
			listItems += '<li>'+versions[i]+'</li>';
		return listItems;
	}

	/**
	 * Opening modal for Updating or Requiring a package.
	 *
	 * @param settings Object Settings of the modal to be opened.
	 */
	function openUpdateRequireModal(settings){
		var modal = bootbox.dialog(settings);
		$.ajax({
			url: urls.refreshPackage,
			type: 'POST',
			data: { name: package.name },
			success: function(data){
				syncUpdateRequireModal(modal, data);
			},
			error: onErrorResponse
		});
	}

	/**
	 * Syncing update and / or require package modal content
	 * after getting the package's data.
	 *
	 * @param modal bootbox Bootbox modal to be synced.
	 * @param data Object Received package's data.
	 */
	function syncUpdateRequireModal(modal, data){
		var html = '<p><strong>Description:</strong><br/>'+data.description +'</p>\
			<div style="cursor:pointer" data-toggle="collapse"\
			data-target="#dependencies-list"><strong>Dependencies:</strong>\
			<i class="float-xs-right material-icons text-muted">keyboard_arrow_down</i></div>\
			<div class="list-group collapse" id="dependencies-list"></div>\
			<strong style="margin-top:1rem;display:inline-block">Version:</strong>\
			<select id="select-version" name="version" class="form-control"></select>';
		modal.find('.bootbox-body').html(html);
		modal.find('#select-version').append(function(){
			var html = '', version;
			for(var i in data.available_versions){
				version = data.available_versions[i];
				html += '<option value="'+version+'">'+version+'</option>'
			}
			return html;
		});
		modal.find('#dependencies-list').append(function(){
			var html = '', dependency;
			if(data.dependencies.length > 0)
				for(var i in data.dependencies){
					dependency = data.dependencies[i];
					html += '<div class="list-group-item list-group-item-action">'+dependency+'</div>';
				}
			else
				html = '<div class="list-group-item list-group-item-action"><span class="text-info">No dependencies found.</span></div>';
			return html;
		});
	}

	/**
	 *  Issuing an update or require task for a specific package.
	 */
	function updateRequirePackage(){
		var modal = $('.bootbox .modal-content');
		var command = (isUpdate)? 'An update' : 'A require';
		$.ajax({
			url: urls.updatePackage,
			type: 'POST',
			data: {
				package: package.name+':'+$('#select-version').val()
			},
			success: function(){
				bootbox.hideAll();
				bootbox.alert(command+' command has been issued, please refer to console for more details.');
			},
			error: onErrorResponse
		});
		return false;
	}

	function onErrorResponse(xhr){
		bootbox.alert('Something wrong has happened please check the developer console for more details.');
		console.log(xhr.responseText);
	}
});