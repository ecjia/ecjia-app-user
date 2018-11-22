// JavaScript Document
;
(function (app, $) {
	app.user_level = {
		init: function () {
			app.user_level.searchForm();
			app.user_level.chart();
		},

		searchForm: function () {
			$('.screen-btn').off('click').on('click', function (e) {
				e.preventDefault();
				var year = $("select[name='year']").val(); //开始时间
				var month = $("select[name='month']").val(); //结束时间
				var url = $("form[name='searchForm']").attr('action'); //请求链接

				if (year == 0 || year == undefined) {
					ecjia.admin.showmessage({
						'state': 'error',
						'message': '请选择年份'
					});
					return false;
				}
				url += '&year=' + year;

				if (month != undefined && month != 0) {
					url += '&month=' + month;
				}
				ecjia.pjax(url);
			});

			$('.search-btn').off('click').on('click', function (e) {
				e.preventDefault();
				var keywords = $("input[name='keywords']").val();
				var url = $("form[name='searchForm']").attr('action'); //请求链接

				if (keywords != '' && keywords != undefined) {
					url += '&keywords=' + keywords;
				}
				ecjia.pjax(url);
			});
		},

		chart: function () {
			var dataset = [];
			var ticks = [];
			if (data.length == 0) {
				$('.row-fluid-stats').css('display', 'none');
			} else {
				$.each(JSON.parse(data), function (key, value) {
					if (key < 30) {
						if (stats == 'order_count') {
							dataset.push(value.order_count);
						} else {
							dataset.push(value.order_money);
						}
						ticks.push(value.user_name);
					}
				});
				var orderStatsChart = echarts.init(document.getElementById('user_level'));
				var option = {
					color: ['#6DCEEE'],
					xAxis: {
						type: 'category',
						data: ticks
					},
					yAxis: {
						type: 'value'
					},
					tooltip: {
						show: "true",
						trigger: 'item',
						backgroundColor: 'rgba(0,0,0,0.7)',
						padding: [8, 10],
						extraCssText: 'box-shadow: 0 0 3px rgba(255, 255, 255, 0.4);',
						formatter: function (params) {
							if (params.seriesName != "") {
								return params.name
							}
						},
					},
					grid: {
						left: '2%',
						right: '2%',
						bottom: '5%',
						top: '5%',
						containLabel: true
					},
					series: [{
						data: dataset,
						type: 'bar',
						barWidth: '32px',
					}]
				};

				orderStatsChart.setOption(option);

				window.addEventListener("resize", function () {
					orderStatsChart.resize();
				});
			}
		},
	};

})(ecjia.admin, jQuery);

// end