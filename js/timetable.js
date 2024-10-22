$(document).ready(function() {
	

	var dataTables = [];
	$(".tblFlights").each(function() {
		var tbl = $(this).dataTable({
			"responsive": true,
			"pageLength": 50,
			"language": {
				"info":           "Showing _START_ to _END_ of _TOTAL_ flights",
				"infoEmpty":      "Showing 0 to 0 of 0 flights",
				"infoFiltered":   "(filtered from _MAX_ total flights)",
				"lengthMenu":     "Show _MENU_ flights",
				"zeroRecords":    "No matching flights found",
			},
			"order": [[ 4, "asc" ]]
		});
		dataTables.push(tbl);
	});

});