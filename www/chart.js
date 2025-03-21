var chart_min_max_display = function(id, title, raw_data) {
	let svg = d3.select('#' + id);

	var create_chart = function() {
		let margin = {top: 20, right: 60, bottom: 50, left: 20},
			width = +svg.node().getBoundingClientRect().width - margin.left - margin.right,
			height = +svg.node().getBoundingClientRect().height - margin.top - margin.bottom,
			g = svg.append("g").attr("transform", "translate(" + margin.left + "," + margin.top + ")");

		var x = d3.scaleTime().range([0, width]);

		let data = [];
		let sensor_width = 0.7;

		let y_scales = d3.map();
		
		for (var sensor_id in raw_data) {
			if (raw_data.hasOwnProperty(sensor_id)) {
				let sensor_data = raw_data[sensor_id];

				switch(sensor_data.unit) {
					case "A":
					case "V":
						if (Object.entries(sensor_data.values).every(value => value[1] < 1)) {
							sensor_data.unit = "m" + sensor_data.unit;
							Object.keys(sensor_data.values).forEach(function(key, value) {
								sensor_data.values[key] = sensor_data.values[key] * 1000;
							});
						}

						break;
				}

				let all_values = [];
				for (var timestamp in sensor_data.values) {
					if (sensor_data.values.hasOwnProperty(timestamp)) {
						all_values.push({
							date: new Date(+timestamp * 1000),
							value: +sensor_data.values[timestamp]
						});
					}
				}

				var values = d3.nest()
					.key(d => d.date.getUTCFullYear() + '-' + d.date.getUTCMonth() + '-' + d.date.getUTCDate())
					.rollup(d => {
						d[0].date.setHours(0);
						d[0].date.setMinutes(0);
						d[0].date.setSeconds(0);

						return {
							date: d[0].date,
							min: d3.min(d, d => d.value),
							avg: d3.mean(d, d => d.value),
							max: d3.max(d, d => d.value)
						}
					})
					.entries(all_values)
					.map(value => value.value);

				y_scales.set(sensor_data.type, {
					type: sensor_data.type,
					axislabel: sensor_data['axis-label'],
					unit: sensor_data.unit
				});

				data.push({
					id: sensor_id,
					label: sensor_data.label,
					place: sensor_data.place,
					type: sensor_data.type,
					unit: sensor_data.unit,
					color: sensor_data.color,
					width: sensor_width,
					values: values
				});

				sensor_width = sensor_width * 1/2;
			}
		}

		x.domain([
			d3.min(data, sensor => d3.min(sensor.values, point => point.date)) - 12*3600*1000,
			+new Date() + 24*3600*1000
		]);
		var bands = d3.timeDay.every(1).range(
			d3.min(data, sensor => d3.min(sensor.values, point => point.date)) - 12*3600*1000,
			+new Date() + 24*3600*1000
		);

		let legend_offset_x = 0;
		let legend_offset_y = 0;
		data.forEach((sensor, i) => {
			let rect = g.append("rect")
				.attr("class", "legend sensor-" + sensor.id)
				.attr("fill", sensor.color)
				.attr("x", legend_offset_x)
				.attr("y", 0 + legend_offset_y)
				.attr("width", 9)
				.attr("height", 9)

			let label = g.append("text")
				.attr("class", "text sensor-" + sensor.id)
				.attr("x", 12 + legend_offset_x)
				.attr("y", 5 + legend_offset_y)
				.style("dominant-baseline", "middle")
				.style("font-size", "9px")
				.style("font-family", "sans-serif")
				.text(sensor.label)

			legend_offset_x += 12 + label.node().getBBox().width + 8;
			if (legend_offset_x >= width/2) {
				legend_offset_x = 0;
				legend_offset_y += 12;
			}
		});

		margin.top += legend_offset_y;

		let locale = d3.timeFormatLocale({
			"dateTime": "%A, le %e %B %Y, %X",
			"date": "%d/%m/%Y",
			"time": "%H:%M:%S",
			"periods": ["AM", "PM"],
			"days": ["dimanche", "lundi", "mardi", "mercredi", "jeudi", "vendredi", "samedi"],
			"shortDays": ["dim.", "lun.", "mar.", "mer.", "jeu.", "ven.", "sam."],
			"months": ["janvier", "février", "mars", "avril", "mai", "juin", "juillet", "août", "septembre", "octobre", "novembre", "décembre"],
			"shortMonths": ["janv.", "févr.", "mars", "avr.", "mai", "juin", "juil.", "août", "sept.", "oct.", "nov.", "déc."]
		});

		g.append("g")
			.attr("class", "axis axis--x")
			.attr("transform", "translate(0," + height + ")")
			.call(d3.axisBottom(x)
				.tickFormat(d => locale.format("%d %b")(d))
			)
			.selectAll("text")
			.attr("transform", "translate(-6, 0) rotate(-45)")
			.style("text-anchor", "end");

		y_scales.values().forEach((scale, i) => {
			if (scale.type == "humidity") {
				scale.scale = d3.scaleLinear()
					.range([height, margin.top])
					.domain([0, 100]);
			} else {
				scale.scale = d3.scaleLinear()
					.range([height, margin.top])
					.domain([
						d3.min(data.filter(sensor => sensor.type == scale.type), sensor => d3.min(sensor.values, point => point.min)) - 2,
						d3.max(data.filter(sensor => sensor.type == scale.type), sensor => d3.max(sensor.values, point => point.max)) + 2
					]);
			}

			if (i % 2 == 0) {
				g.append("g")
					.attr("class", "axis axis--y axis-" + scale.type)
					.attr("transform", "translate(" + width + ", 0)")
					.call(d3.axisRight(scale.scale))
					.append("text")
						.attr("transform", "translate(0, " + margin.top + ") rotate(90)")
						.attr("y", 6)
						.attr("dy", "0.71em")
						.attr("fill", "#000")
						.text(scale.axislabel + (scale.unit ? " (" + scale.unit + ")" : ""));

				g.append("g")
					.attr("class", "axis axis--y lines axis-" + scale.type)
					.attr("transform", "translate(" + width + ", 0)")
					.call(d3.axisRight(scale.scale).tickSizeInner(width * -1).tickFormat(""))
			} else {
				g.append("g")
					.attr("class", "axis axis--y axis-" + scale.type)
					.attr("transform", "translate(0, 0)")
					.call(d3.axisLeft(scale.scale))
					.append("text")
						.attr("transform", "translate(0, " + margin.top + ") rotate(270)")
						.attr("y", 6)
						.attr("dy", "0.71em")
						.attr("fill", "#000")
						.text(scale.axislabel + (scale.unit ? " (" + scale.unit + ")" : ""));
			}
		})

		var bandWidth = (width/bands.length) * 0.75;

		data.forEach((sensor, i) => {
			let bar = g.selectAll(".bar.sensor-" + sensor.id)
				.data(sensor.values)
				.enter().append("rect")
					.attr("class", "bar sensor-" + sensor.id)
					.attr("fill", sensor.color)
					.attr("x", d => x(d.date) - bandWidth*sensor.width/2)
					.attr("y", d => y_scales.get(sensor.type).scale(d.max))
					.attr("width", d => bandWidth * sensor.width)
					.attr("height", d => d3.max([2, y_scales.get(sensor.type).scale(d.min) - y_scales.get(sensor.type).scale(d.max)]))

			let avg = g.selectAll(".avg.sensor-" + sensor.id)
				.data(sensor.values)
					.enter().append("rect")
					.attr("class", "avg sensor-" + sensor.id)
					.attr("fill", "rgba(255,255,255,0.5)")
					.attr("x", d => x(d.date) - bandWidth*sensor.width/2)
					.attr("y", d => y_scales.get(sensor.type).scale(d.avg))
					.attr("width", d => bandWidth * sensor.width)
					.attr("height", 1)
		})

		return g;
	};

	let g = create_chart();

	let resize_timer = 0;
    window.addEventListener('resize', e => {
		clearTimeout(resize_timer);
		resize_timer = setTimeout(function() {
			g.remove();
			g = create_chart();
		}, 250);
	})
};

var chart_histogram_display = function(id, title, raw_data) {
	let svg = d3.select('#' + id);

	var create_chart = function() {
		let margin = {top: 20, right: 60, bottom: 50, left: 40},
			width = +svg.node().getBoundingClientRect().width - margin.left - margin.right,
			height = +svg.node().getBoundingClientRect().height - margin.top - margin.bottom,
			g = svg.append("g").attr("transform", "translate(" + margin.left + "," + margin.top + ")");

		var x = d3.scaleTime().range([0, width]);

		let data = [];
		let sensor_width = 0.7;

		let y_scales = d3.map();
		
		for (var sensor_id in raw_data) {
			if (raw_data.hasOwnProperty(sensor_id)) {
				let sensor_data = raw_data[sensor_id];

				switch(sensor_data.unit) {
					case "A":
					case "V":
						if (Object.entries(sensor_data.values).every(value => value[1] < 1)) {
							sensor_data.unit = "m" + sensor_data.unit;
							Object.keys(sensor_data.values).forEach(function(key, value) {
								sensor_data.values[key] = sensor_data.values[key] * 1000;
							});
						}

						break;
				}

				let all_values = [];
				for (var timestamp in sensor_data.values) {
					if (sensor_data.values.hasOwnProperty(timestamp)) {
						all_values.push({
							date: new Date(+timestamp * 1000),
							value: +sensor_data.values[timestamp]
						});
					}
				}

				y_scales.set(sensor_data.type, {
					type: sensor_data.type,
					axislabel: sensor_data['axis-label'],
					unit: sensor_data.unit
				});

				data.push({
					id: sensor_id,
					label: sensor_data.label,
					place: sensor_data.place,
					type: sensor_data.type,
					unit: sensor_data.unit,
					color: sensor_data.color,
					width: sensor_width,
					values: all_values
				});

				sensor_width = sensor_width * 1/2;
			}
		}

		x.domain([
			d3.min(data, sensor => d3.min(sensor.values, point => point.date)),
			+new Date()
		]);
		var bands = d3.timeMinute.every(5).range(
				x.domain()[0], x.domain()[1]
		);

		let legend_offset_x = 0;
		let legend_offset_y = 0;
		data.forEach((sensor, i) => {
			let rect = g.append("rect")
				.attr("class", "legend sensor-" + sensor.id)
				.attr("fill", sensor.color)
				.attr("x", legend_offset_x)
				.attr("y", 0 + legend_offset_y)
				.attr("width", 9)
				.attr("height", 9)

			let label = g.append("text")
				.attr("class", "text sensor-" + sensor.id)
				.attr("x", 12 + legend_offset_x)
				.attr("y", 5 + legend_offset_y)
				.style("dominant-baseline", "middle")
				.style("font-size", "9px")
				.style("font-family", "sans-serif")
				.text(sensor.label)

			legend_offset_x += 12 + label.node().getBBox().width + 8;
			if (legend_offset_x >= width/2) {
				legend_offset_x = 0;
				legend_offset_y += 12;
			}
		});

		margin.top += legend_offset_y;

		let locale = d3.timeFormatLocale({
			"dateTime": "%A, le %e %B %Y, %X",
			"date": "%d/%m/%Y",
			"time": "%H:%M:%S",
			"periods": ["AM", "PM"],
			"days": ["dimanche", "lundi", "mardi", "mercredi", "jeudi", "vendredi", "samedi"],
			"shortDays": ["dim.", "lun.", "mar.", "mer.", "jeu.", "ven.", "sam."],
			"months": ["janvier", "février", "mars", "avril", "mai", "juin", "juillet", "août", "septembre", "octobre", "novembre", "décembre"],
			"shortMonths": ["janv.", "févr.", "mars", "avr.", "mai", "juin", "juil.", "août", "sept.", "oct.", "nov.", "déc."]
		});

		g.append("g")
			.attr("class", "axis axis--x")
			.attr("transform", "translate(0," + height + ")")
			.call(d3.axisBottom(x)
				.tickFormat(d => locale.format("%d/%m %H:%M")(d))
			)
			.selectAll("text")
			.attr("transform", "translate(-6, 0) rotate(-45)")
			.style("text-anchor", "end");

		y_scales.values().forEach((scale, i) => {
			if (scale.type == "humidity") {
				scale.scale = d3.scaleLinear()
					.range([height, margin.top])
					.domain([0, 100]);
			} else {
				scale.scale = d3.scaleLinear()
					.range([height, margin.top])
					.domain([
						0,
						d3.max(data.filter(sensor => sensor.type == scale.type), sensor => d3.max(sensor.values, point => point.value)) + 2
					]);
			}

			if (i % 2 == 0) {
				g.append("g")
					.attr("class", "axis axis--y axis-" + scale.type)
					.attr("transform", "translate(" + width + ", 0)")
					.call(d3.axisRight(scale.scale))
					.append("text")
						.attr("transform", "translate(0, " + margin.top + ") rotate(90)")
						.attr("y", 6)
						.attr("dy", "0.71em")
						.attr("fill", "#000")
						.text(scale.axislabel + (scale.unit ? " (" + scale.unit + ")" : ""));

				g.append("g")
					.attr("class", "axis axis--y lines axis-" + scale.type)
					.attr("transform", "translate(" + width + ", 0)")
					.call(d3.axisRight(scale.scale).tickSizeInner(width * -1).tickFormat(""))
			} else {
				g.append("g")
					.attr("class", "axis axis--y axis-" + scale.type)
					.attr("transform", "translate(0, 0)")
					.call(d3.axisLeft(scale.scale))
					.append("text")
						.attr("transform", "translate(0, " + margin.top + ") rotate(270)")
						.attr("y", 6)
						.attr("dy", "0.71em")
						.attr("fill", "#000")
						.text(scale.axislabel + (scale.unit ? " (" + scale.unit + ")" : ""));
			}
		})

		var bandWidth = (width/bands.length/data.length) * 0.75;

		data.forEach((sensor, i) => {
			let bar = g.selectAll(".bar.sensor-" + sensor.id)
				.data(sensor.values)
				.enter().append("rect")
					.attr("class", "bar sensor-" + sensor.id)
					.attr("fill", sensor.color)
					.attr("x", d => x(d.date) + bandWidth * i)
					.attr("y", d => y_scales.get(sensor.type).scale(d.value))
					.attr("width", d => bandWidth)
					.attr("height", d => y_scales.get(sensor.type).scale(0) - y_scales.get(sensor.type).scale(d.value))
		})

		return g;
	};

	let g = create_chart();

	let resize_timer = 0;
    window.addEventListener('resize', e => {
		clearTimeout(resize_timer);
		resize_timer = setTimeout(function() {
			g.remove();
			g = create_chart();
		}, 250);
	})
};

var chart_line_display = function(id, title, raw_data, daylight) {
	if (daylight === undefined) {
		daylight = true;
	}

	let svg = d3.select('#' + id);

	var create_chart = function() {
		let margin = {top: 20, right: 60, bottom: 50, left: 30},
			width = +svg.node().getBoundingClientRect().width - margin.left - margin.right,
			height = +svg.node().getBoundingClientRect().height - margin.top - margin.bottom,
			g = svg.append("g").attr("transform", "translate(" + margin.left + "," + margin.top + ")");

		var x = d3.scaleTime().range([0, width]);

		let events = [];
		let data = [];
		let y_scales = d3.map();

		if (raw_data.events) for (var event_id in raw_data.events) {
			if (raw_data.events.hasOwnProperty(event_id)) {
				let e = raw_data.events[event_id];

				events.push({
					id: event_id,
					timestamp: e.timestamp,
					title: e.title,
					details: e.details,
				});
			}
		}
		
		for (var sensor_id in raw_data) {
			if (raw_data.hasOwnProperty(sensor_id)) {
				let sensor_data = raw_data[sensor_id];

				switch(sensor_data.unit) {
					case "A":
					case "V":
						if (Object.entries(sensor_data.values).every(value => value[1] < 1)) {
							sensor_data.unit = "m" + sensor_data.unit;
							Object.keys(sensor_data.values).forEach(function(key, value) {
								sensor_data.values[key] = sensor_data.values[key] * 1000;
							});
						}

						break;
				}

				let all_values = [];
				for (var timestamp in sensor_data.values) {
					if (sensor_data.values.hasOwnProperty(timestamp)) {
						all_values.push({
							date: new Date(+timestamp * 1000),
							value: +sensor_data.values[timestamp]
						});
					}
				}

				y_scales.set(sensor_data.type, {
					type: sensor_data.type,
					axislabel: sensor_data['axis-label'],
					unit: sensor_data.unit
				});

				data.push({
					id: sensor_id,
					label: sensor_data.label,
					place: sensor_data.place,
					type: sensor_data.type,
					unit: sensor_data.unit,
					color: sensor_data.color,
					values: all_values
				});
			}
		}

		x.domain([
			d3.min(data, sensor => d3.min(sensor.values, point => point.date)),
			new Date()
		]);
		var bands = d3.timeDay.every(1).range(
			d3.min(data, sensor => d3.min(sensor.values, point => point.date)) - 12*3600*1000,
			+new Date() + 24*3600*1000
		);
		var bandWidth = width/bands.length;

		let legend_offset_x = 0;
		let legend_offset_y = 0;
		data.forEach((sensor, i) => {
			let rect = g.append("rect")
				.attr("class", "legend sensor-" + sensor.id)
				.attr("fill", sensor.color)
				.attr("x", legend_offset_x)
				.attr("y", 0 + legend_offset_y)
				.attr("width", 9)
				.attr("height", 9)

			let label = g.append("text")
				.attr("class", "text sensor-" + sensor.id)
				.attr("x", 12 + legend_offset_x)
				.attr("y", 5 + legend_offset_y)
				.style("dominant-baseline", "middle")
				.style("font-size", "9px")
				.style("font-family", "sans-serif")
				.text(sensor.label)

			legend_offset_x += 12 + label.node().getBBox().width + 8;
			if (legend_offset_x >= width/2) {
				legend_offset_x = 0;
				legend_offset_y += 12;
			}
		});

		margin.top += legend_offset_y;

		let locale = d3.timeFormatLocale({
			"dateTime": "%A, le %e %B %Y, %X",
			"date": "%d/%m/%Y",
			"time": "%H:%M:%S",
			"periods": ["AM", "PM"],
			"days": ["dimanche", "lundi", "mardi", "mercredi", "jeudi", "vendredi", "samedi"],
			"shortDays": ["dim.", "lun.", "mar.", "mer.", "jeu.", "ven.", "sam."],
			"months": ["janvier", "février", "mars", "avril", "mai", "juin", "juillet", "août", "septembre", "octobre", "novembre", "décembre"],
			"shortMonths": ["janv.", "févr.", "mars", "avr.", "mai", "juin", "juil.", "août", "sept.", "oct.", "nov.", "déc."]
		});

		if (x.domain()[1] - x.domain()[0] < 3600 * 24 * 2 * 1000) {
			g.append("g")
				.attr("class", "axis axis--x labels hours")
				.attr("transform", "translate(0," + height + ")")
				.call(d3.axisBottom(x)
					.tickFormat(function(d) { return locale.format("%Hh%M")(d) })
				 );

			g.append("g")
				.attr("class", "axis axis--x lines")
				.attr("transform", "translate(0," + height + ")")
				.call(d3.axisBottom(x)
					.tickSize((height - margin.top) * -1)
					.ticks(d3.timeMinute.every(5))
					.tickFormat("")
				 );
		} else if (bandWidth > 20) {
			g.append("g")
				.attr("class", "axis axis--x labels hours")
				.attr("transform", "translate(0," + height + ")")
				.call(d3.axisBottom(x)
					.ticks(d3.timeHour.every(6))
					.tickFormat(function(d) { return locale.format("%Hh")(d) })
				 );
			g.append("g")
				.attr("class", "axis axis--x labels days")
				.attr("transform", "translate(0," + (height + 16) + ")")
				.call(d3.axisBottom(x)
					.ticks(d3.timeHour.every(12).filter(d => d.getHours() > 0)).tickSize(0)
					.tickFormat(function(d) { return d.getHours() > 0 ? locale.format("%a %d")(d) : "" })
				 );

			g.append("g")
				.attr("class", "axis axis--x lines")
				.attr("transform", "translate(0," + height + ")")
				.call(d3.axisBottom(x)
					.tickSize((height - margin.top) * -1)
					.ticks(d3.timeHour.every(4))
					.tickFormat("")
				 );
		} else {
			g.append("g")
				.attr("class", "axis axis--x labels hours")
				.attr("transform", "translate(0," + height + ")")
				.call(d3.axisBottom(x)
					.tickFormat(d => locale.format("%d %b")(d))
				)
				.selectAll("text")
				.attr("transform", "translate(-6, 0) rotate(-45)")
				.style("text-anchor", "end");
		}


		y_scales.values().forEach((scale, i) => {
			if (scale.unit === undefined) {
				scale.scale = d3.scaleLinear()
					.range([height*0.8, margin.top + height*0.2])
					.domain([0, 1]);

				scale.line = d3.line()
					.curve(d3.curveStepAfter)
					.x(d => x(d.date))
					.y(d => scale.scale(d.value));
			} else if (scale.type == "humidity") {
				scale.scale = d3.scaleLinear()
					.range([height, margin.top])
					.domain([0, 100]);

				scale.line = d3.line()
					.curve(d3.curveMonotoneX)
					.x(d => x(d.date))
					.y(d => scale.scale(d.value));
			} else {
				scale.scale = d3.scaleLinear()
					.range([height, margin.top])
					.domain([
						d3.min(data.filter(sensor => sensor.type == scale.type), sensor => d3.min(sensor.values, point => point.value)) - 2,
						d3.max(data.filter(sensor => sensor.type == scale.type), sensor => d3.max(sensor.values, point => point.value)) + 2
					]);

				scale.line = d3.line()
					.curve(d3.curveMonotoneX)
					.x(d => x(d.date))
					.y(d => scale.scale(d.value));
			}

			if (i % 2 == 0) {
				g.append("g")
					.attr("class", "axis axis--y axis-" + scale.type)
					.attr("transform", "translate(" + width + ", 0)")
					.call(d3.axisRight(scale.scale))
					.append("text")
						.attr("transform", "translate(0, " + margin.top + ") rotate(90)")
						.attr("y", 6)
						.attr("dy", "0.71em")
						.attr("fill", "#000")
						.text(scale.axislabel + (scale.unit ? " (" + scale.unit + ")" : ""));

				g.append("g")
					.attr("class", "axis axis--y lines axis-" + scale.type)
					.attr("transform", "translate(" + width + ", 0)")
					.call(d3.axisRight(scale.scale).tickSizeInner(width * -1).tickFormat(""))
			} else {
				g.append("g")
					.attr("class", "axis axis--y axis-" + scale.type)
					.attr("transform", "translate(0, 0)")
					.call(d3.axisLeft(scale.scale))
					.append("text")
						.attr("transform", "translate(0, " + margin.top + ") rotate(270)")
						.attr("y", 6)
						.attr("dy", "0.71em")
						.attr("fill", "#000")
						.text(scale.axislabel + (scale.unit ? " (" + scale.unit + ")" : ""));
			}
		})

		if (daylight) {
			// interval.range() is [start; stop[ so we have to add an extra day at the end
			var days = d3.timeDay.every(1).range(x.domain()[0], d3.timeDay.offset(x.domain()[1], 1));
			let rect_daylight = g.selectAll("rect.daylight")
				.data(days)
				.enter().append("rect")
					.attr("class", "daylight")
					.attr("fill", "yellow")
					.attr("opacity", "0.3")
					.attr("x", d => {
						let sun = SunCalc.getTimes(d, 50.6278, 3.0583);

						d.x = Math.min(Math.max(0, x(sun.sunrise)), width);
						d.width = Math.min(Math.max(0, x(sun.sunset) - d.x), width - d.x);
						return d.x;
						})
					.attr("y", margin.top)
					.attr("width", d => d.width)
					.attr("height", height - margin.top - 1)
		}

		events.forEach((e, i) => {
			let element_event = g.selectAll("rect.event")
				.data(days)
				.enter().append("rect")
					.attr("class", "event")
					.attr("fill", "red")
					.attr("opacity", "0.3")
					.attr("x", d => {
						var time = new Date(e.timestamp * 1000);

						d.x = Math.min(Math.max(0, x(time)), width);
						d.width = 2;
						return d.x;
						})
					.attr("y", margin.top)
					.attr("width", d => d.width)
					.attr("height", height - margin.top - 1)
		});
		data.forEach((sensor, i) => {
			let path = g.selectAll("line.sensor-" + sensor.id)
				.data([sensor.values])
				.enter().append("path")
					.attr("class", "sensor sensor-" + sensor.id)
					.attr("fill", "none")
					.attr("stroke", sensor.color)
					.attr("stroke-width", "2px")
					.attr("d", d => y_scales.get(sensor.type).line(d))
		})

		return g;
	};

	let g = create_chart();

	let resize_timer = 0;
    window.addEventListener('resize', e => {
		clearTimeout(resize_timer);
		resize_timer = setTimeout(function() {
			g.remove();
			g = create_chart();
		}, 250);
	})
};

