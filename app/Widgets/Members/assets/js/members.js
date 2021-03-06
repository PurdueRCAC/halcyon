
jQuery(document).ready(function ($) {
	var mod_members_charts = [];

	$('.mod_members-chart').each(function(i, el){
		var data = $('#' + $(el).attr('data-datasets')).html();
		var datasets = JSON.parse(data);
		var mod_members_chart = $.plot(
			$(el),
			datasets.datasets,
			{
				legend: {
					show: false
				},
				series: {
					pie: {
						innerRadius: 0.5,
						show: true,
						label: {
							show: false
						},
						stroke: {
							color: '#efefef'
						}
					}
				},
				grid: {
					hoverable: false
				}
			}
		);

		mod_members_charts.push(mod_members_chart);
	});
});