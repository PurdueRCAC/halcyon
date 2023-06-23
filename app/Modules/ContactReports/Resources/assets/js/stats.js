document.addEventListener('DOMContentLoaded', function () {
	document.querySelectorAll('.items-toggle').forEach(function (el) {
		el.addEventListener('click', function (e) {
			e.preventDefault();

			document.getElementById(this.getAttribute('href').replace('#', '')).classList.toggle('collapse');
		});
	});

	var charts = new Array;
	document.querySelectorAll('.sparkline-chart').forEach(function (el) {
		const ctx = el.getContext('2d');
		const chart = new Chart(ctx, {
			type: 'line',
			data: {
				labels: JSON.parse(el.getAttribute('data-labels')),
				datasets: [
					{
						fill: true,
						data: JSON.parse(el.getAttribute('data-values'))
					}
				]
			},
			options: {
				bezierCurve: false,
				animation: {
					duration: 0
				},
				legend: {
					display: false
				},
				elements: {
					line: {
						borderColor: 'rgb(54, 162, 235)',
						borderWidth: 1,
						tension: 0
					},
					point: {
						borderColor: 'rgb(54, 162, 235)'
					}
				},
				scales: {
					xAxes: [
						{
							display: false
						}
					]
				}
			}
		});
		charts.push(chart);
	});

	document.querySelectorAll('.pie-chart').forEach(function (el) {
		const ctx = el.getContext('2d');
		const pchart = new Chart(ctx, {
			type: 'doughnut',
			data: {
				labels: JSON.parse(el.getAttribute('data-labels')),
				datasets: [
					{
						data: JSON.parse(el.getAttribute('data-values')),
						backgroundColor: [
							'rgb(255, 99, 132)', // red
							'rgb(54, 162, 235)', // blue
							'rgb(255, 205, 86)', // yellow
							'rgb(201, 203, 207)', // grey
							'rgb(75, 192, 192)', // blue green
							'rgb(255, 159, 64)', // orange
							'rgb(153, 102, 255)' // purple
						],
						borderColor: el.getAttribute('data-border')
					}
				]
			},
			options: {
				animation: {
					duration: 0
				}
			}
		});
		charts.push(pchart);
	});
});