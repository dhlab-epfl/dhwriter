function showRowEditForm(table, row) {
	table.children('tbody').children('tr').removeClass('editing');
	if (row!==null) {
		row.addClass('editing');
		row.next('tr.form').addClass('editing');
	}
}
function toggleDetails(id) {
	if ($("#details"+id+":visible").length) {
		$("#btn"+id).attr("src","i/+.png");
		$("#details"+id).slideUp();
	}
	else {
		$("#btn"+id).attr("src","i/-.png");
		$("#details"+id).slideDown();
	}
}
function initTables() {
	$('table.edit>tbody>tr.row>td').children('a.edit,.btn.edit').click(function(/*e*/){ showRowEditForm($(this).closest('table.edit'), $(this).closest('tr.row'));} );
	$('table.edit>tbody>tr.form .close').click(function(/*e*/){ showRowEditForm($(this).closest('table.edit'), null);} );

	$('table.collapsable a.more').click(function(e){
		e.preventDefault();
		toggleDetails($(this).data('more'));
	});
}

$(document).ready(function(){
	initTables();
});