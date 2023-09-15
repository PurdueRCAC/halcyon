
document.addEventListener('DOMContentLoaded', function () {
	var mod_members_charts = [];

	document.querySelectorAll('.mod_members-chart').forEach(function(el){
		var data = document.getElementById(el.getAttribute('data-datasets')).innerHTML;
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