/**
 * @package  Pages manager
 */

jQuery(document).ready(function ($) {
	var alias = $('#field-alias');
	if (alias.length && !alias.val()) {
		$('#field-title').on('keyup', function (e){
			var val = $(this).val();

			val = val.toLowerCase()
				.replace(/\s+/g, '_')
				.replace(/[^a-z0-9\-_]+/g, '');

			alias.val(val);
		});
	}

	$('#field-parent_id')
		.on('change', function (){
			$('#parent-path').html($(this).children("option:selected").data('path'));
		});

	$('body').on('click', '.delete-row', function (e) {
		e.preventDefault();

		console.log($(this).attr('href'));

		$($(this).attr('href')).remove();
	});

	$('.add-row').on('click', function(e){
		e.preventDefault();

		var tr = $('#' + $(this).data('container')).find('.input-group:last');

		var clone  = tr.clone(true);
			clone.removeClass('d-none');
			clone.find('.btn').removeClass('disabled');

		var cindex = $('#' + $(this).data('container')).find('.input-group').length;
		var inputs = clone.find('input,select');

		clone.attr('id', clone.attr('id').replace(/\-\d+/, '-' + cindex));

		inputs.val('');
		inputs.each(function(i, el){
			$(el).attr('name', $(el).attr('name').replace(/\[\d+\]/, '[' + cindex + ']'));
			$(el).attr('id', $(el).attr('id').replace(/\-\d+/, '-' + cindex));
		});

		clone.find('a').each(function (i, el) {
			$(el).attr('href', $(el).attr('href').replace(/\-\d+/, '-' + cindex));
		});

		tr.after(clone);
	});

	$('.sparkline-chart').each(function (i, el) {
		const ctx = el.getContext('2d');
		const chart = new Chart(ctx, {
			type: 'line',
			data: {
				labels: JSON.parse($(el).attr('data-labels')),
				datasets: [
					{
						fill: false,
						data: JSON.parse($(el).attr('data-values'))
					}
				]
			},
			options: {
				responsive: false,
				animation: {
					duration: 0
				},
				legend: {
					display: false
				},
				elements: {
					line: {
						borderColor: '#0071EB',
						borderWidth: 1
					},
					point: {
						radius: 0
					}
				},
				scales: {
					yAxes: [
						{
							display: false
						}
					],
					xAxes: [
						{
							display: false
						}
					]
				}
			}
		});
	});
});
