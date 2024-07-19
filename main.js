/* This file is loaded when Organizr is loaded */
// Load once Organizr loads
$('body').arrive('#activeInfo', {onceOnly: true}, function() {
	autoOrganizrPluginLaunch();
});
// FUNCTIONS
function autoOrganizrPluginLaunch(){
	organizrAPI2('GET','api/v2/plugins/autoOrganizr/launch').success(function(data) {
		try {
			var menuList = `<li><a href="javascript:void(0)" onclick="toggleAutoOrganizrPlugin();"><i class="fa fa-tv fa-fw"></i> <span lang="en">autoOrganizr</span></a></li>`;
			$('.append-menu').after(menuList);
		}catch(e) {
			organizrCatchError(e,data);
		}
	}).fail(function(xhr) {
		OrganizrApiError(xhr);
	});
}
function toggleAutoOrganizrPlugin(){
	let div = `
		<div class="panel bg-org panel-info" id="autoOrganizr-area">
			<div class="panel-heading">
				<span lang="en">autoOrganizr</span>
			</div>
		</div>
		`;
	swal({
		content: createElementFromHTML(div),
		button: false,
		className: 'orgAlertTransparent',
	});
}
// EVENTS and LISTENERS
