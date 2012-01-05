/* $Id: view.js 40 2008-02-11 12:11:44Z pedroix $ $URL: https://web2project.svn.sourceforge.net/svnroot/web2project/tags/version2.4/modules/resources/view.js $ */
var delete_msg = '';
var can_delete = false;

function delIt() {
if (can_delete) {
if (confirm(delete_msg)) {
document.frmDelete.submit();
}
} else {
alert('Function not permitted');
}
}