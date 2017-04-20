(function($) {
	$(document).ready(function() {
		$('.data-table').DataTable({
			paging:   true,
			ordering: true,
			stateSave: true,
			responsive: true,
			columnDefs: [ {
				"targets": 'no-sort',
				"orderable": false
			} ]
		});
	});

	$(document).on('click', '.delete-item', function(e) {
		e.preventDefault();
		var identifier = $(this).data('identifier'),
			redirect = $(this).data('redirect');

		if ( '' === redirect ) {
			return false;
		}

		if ( confirm( 'Are you sure you want to remove this item?' ) ) {
			$.ajax({
				url: identifier,
				type: 'DELETE',
				dataType: 'json',
				success: function(result){
					if ( true === result.success ) {
						window.location.href=redirect;
					} else {
						alert(result.message);
					}
				}
			});
		}
	});

	$(document).on('click', '.apply', function() {
		var post_id = $(this).data('post-id'),
			user_id = $(this).data('user-id'),
			me = $(this),
			result_span = $('.result');

		$.ajax({
			url: '/apply',
			type: 'POST',
			data: {
				user_id: user_id,
				post_id: post_id
			},
			dataType: 'json',
			success: function(result){
				if ( true === result.success ) {
					me.hide();
					result_span.html('<strong>You applied for this.</strong>');
				} else {
					alert( result.message );
				}

			}
		});

	});

})(jQuery);
