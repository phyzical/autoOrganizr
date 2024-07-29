/* This file is loaded when Organizr is loaded */
// Load once Organizr loads
$('body').arrive('#activeInfo', { onceOnly: true }, function () {
	autoOrganizrPluginLaunch();
});
// FUNCTIONS
function autoOrganizrPluginLaunch() {
	organizrAPI2('GET', 'api/v2/plugins/autoorganizr/launch').success(function (data) {
		try {
			var menuList = `<li><a href="javascript:void(0)" onclick="toggleAutoOrganizrPlugin();"><i class="fa fa-tv fa-fw"></i> <span lang="en">autoOrganizr</span></a></li>`;
			$('.append-menu').after(menuList);
		} catch (e) {
			organizrCatchError(e, data);
		}
	}).fail(function (xhr) {
		OrganizrApiError(xhr);
	});
}

function toggleAutoOrganizrPlugin() {
	let div = `
		<div class="panel bg-org panel-info" id="autoOrganizr-area">
			<div class="panel-heading">
				<span lang="en">autoOrganizr</span>
			</div>
			<div class="panel-body">
				<div>
					<div class="white-box m-b-0">
						<h2 class="text-center loadingautoOrganizr" lang="en"><i class="fa fa-spin fa-spinner"></i></h2>
						<div class="table-responsive autoOrganizrTableList hidden" id="autoOrganizrTableList">
							<h3 class="text-center" lang="en">Actions</h3>
							<table class="table color-bordered-table purple-bordered-table text-left">
								<tr>
									<th>Name</th>
									<th>Action</th>
								</tr>
							</table>
						</div>
					</div>
				</div>
			</div>
		</div>
		`;
	swal({
		content: createElementFromHTML(div),
		button: false,
		className: 'orgAlertTransparent',
	});
	AutoOrganizrPluginSyncTabs();
}
function AutoOrganizrPluginSyncTabs() {
	organizrAPI2('GET', 'api/v2/plugins/autoorganizr/synctabs').success(function ({ response: { data } }) {
		$('.loadingautoOrganizr').remove();
		const items = data.map(update => `<tr><th>${update.name}</th><th>${update.type}</th></tr>`).join("");
		$('#autoOrganizrTableList table').append(items);
		$('#autoOrganizrTableList').removeClass("hidden")
	}).fail(function (res) {
		const { response: { message, data } } = res.responseJSON;
		console.dir(message)
		console.dir(data)

		$('.loadingautoOrganizr').remove();
		$('#autoOrganizrTableList table').append(`<h3>Error: ${message}</h3>`);
		const items = data.map(update => `<tr><th>${update.name}</th><th>${update.type}</th></tr>`).join("");
		$('#autoOrganizrTableList table').append(items);
		$('#autoOrganizrTableList').removeClass("hidden")
		OrganizrApiError(xhr);
	});
}
// EVENTS and LISTENERS
