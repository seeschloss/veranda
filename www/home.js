// vim: set et sw=2 st=2 sts=2:

var link_plants = function() {
    let container = document.querySelector('#plants');
    let img = container.querySelector('#photo-boite');

    var setup_links = function(container) {
      let plants = container.querySelectorAll('#plants ul li');
      let img = container.querySelector('#photo-boite');

      let x_ratio = img.naturalWidth / img.clientWidth;
      let y_ratio = img.naturalHeight / img.clientHeight;

      let data = [];
      for (var i = 0; i < plants.length; i++) {
        let plant = plants.item(i);
        data.push({
          id: plant.dataset.id,
          box_x: +plant.dataset.boxX,
          box_y: +plant.dataset.boxY,
          box_width: +plant.dataset.boxWidth,
          box_height: +plant.dataset.boxHeight,
          element: plant
        });
      }

      let svg = document.createElementNS("http://www.w3.org/2000/svg", 'svg');
      svg.id = "plant-links";
      svg.style.position = "absolute";
      svg.style.left = container.offsetLeft + "px";
      svg.style.top = container.offsetTop + "px";
      svg.setAttributeNS(null, "version", "1.1");
      svg.setAttributeNS(null, "width", container.clientWidth);
      svg.setAttributeNS(null, "height", container.clientHeight);
      container.appendChild(svg);

      svg = d3.select('svg#plant-links');

      let link_shadow = d3.linkHorizontal()
        .source(d => { return [d.element.offsetLeft - img.offsetLeft - 10, (d.element.offsetTop + d.element.clientHeight/2) - img.offsetTop]})
        .target(d => [(d.box_x + d.box_width/2) / x_ratio, (d.box_y + d.box_height/2) / y_ratio])
        .x(d => d[0] + 1)
        .y(d => d[1] + 1)

      let link = d3.linkHorizontal()
        .source(d => { return [d.element.offsetLeft - img.offsetLeft - 10, (d.element.offsetTop + d.element.clientHeight/2) - img.offsetTop]})
        .target(d => [(d.box_x + d.box_width/2) / x_ratio, (d.box_y + d.box_height/2) / y_ratio])

      svg.selectAll("#plant-links .plant-link-shadow")
        .data(data)
        .enter().append('path')
          .attr('class', 'plant-link-shadow')
          .attr('d', link_shadow);

      svg.selectAll("#plant-links .plant-link")
        .data(data)
        .enter().append('path')
          .attr('class', 'plant-link')
          .attr('d', link);
    };

    if (img.complete) {
        setup_links(container);
    } else {
        img.addEventListener('load', e => { setup_links(container) })
    }
};

var humidex = function(celsius, humidity) {
   //Calcul pression vapeur eau
   var kelvin = celsius + 273;
   eTs = Math.pow(10,((-2937.4 /kelvin)-4.9283* Math.log(kelvin)/Math.LN10 +23.5471));
   eTd = eTs * humidity /100;

   //Calcul de l'humidex
   var humidex = Math.round(celsius + ((eTd-10)*5/9));
   if (humidex < celsius) {
       humidex = celsius;
   }

   return humidex;
};

var show_daily = function(svg, temperatures, series) {
  margin = {top: 20, right: 40, bottom: 50, left: 40},
  width = +svg.node().getBoundingClientRect().width - margin.left - margin.right,
  height = +svg.attr("height") - margin.top - margin.bottom,
  g_daily = svg.append("g").attr("transform", "translate(" + margin.left + "," + margin.top + ")");

  var x = d3.scaleTime().range([0, width]);
  var y = d3.scaleLinear().range([height, 0]);
  var z = d3.scaleOrdinal(d3.schemeCategory10);

  x.domain([d3.min(temperatures, d => d.value.date) - 12*3600*1000, +new Date() + 24*3600*1000]);

  y.domain([
      d3.min(series.map(d => d.key).map(key => d3.min(temperatures, d => d.value[key].min))) - 2,
      d3.max(series.map(d => d.key).map(key => d3.max(temperatures, d => d.value[key].max))) + 2
  ]);

  locale = d3.timeFormatLocale({
    "dateTime": "%A, le %e %B %Y, %X",
    "date": "%d/%m/%Y",
    "time": "%H:%M:%S",
    "periods": ["AM", "PM"],
    "days": ["dimanche", "lundi", "mardi", "mercredi", "jeudi", "vendredi", "samedi"],
    "shortDays": ["dim.", "lun.", "mar.", "mer.", "jeu.", "ven.", "sam."],
    "months": ["janvier", "février", "mars", "avril", "mai", "juin", "juillet", "août", "septembre", "octobre", "novembre", "décembre"],
    "shortMonths": ["janv.", "févr.", "mars", "avr.", "mai", "juin", "juil.", "août", "sept.", "oct.", "nov.", "déc."]
  });

  g_daily.append("g")
      .attr("class", "axis axis--x")
      .attr("transform", "translate(0," + height + ")")
      .call(d3.axisBottom(x)
          .ticks(d3.timeWeek.every(1))
          .tickFormat(d => locale.format("%d %b")(d))
      )
      .selectAll("text")
        .attr("transform", "translate(-6, 0) rotate(-45)")
        .style("text-anchor", "end");

  g_daily.append("g")
      .attr("class", "axis axis--y")
      .attr("transform", "translate(" + width + ", 0)")
      .call(d3.axisRight(y).ticks(26).tickSizeInner(width * -1))
    .append("text")
      .attr("transform", "rotate(90)")
      .attr("y", 6)
      .attr("dy", "0.71em")
      .attr("fill", "#000")
      .text("Température (°C)");

  var bandWidth = width/temperatures.length * 0.75;

  series.forEach((serie, i) => {
    let bar = g_daily.selectAll(".bar." + serie.class)
      .data(temperatures.filter(d => d.value[serie.key].avg > 0))
      .enter().append("rect")
        .attr("class", "bar " + serie.class)
        .attr("fill", serie.fill)
        .attr("x", d => x(d.value.date) - bandWidth*serie.width/2)
        .attr("y", d => y(d.value[serie.key].max))
        .attr("width", bandWidth * serie.width)
        .attr("height", d => d3.max([2, y(d.value[serie.key].min) - y(d.value[serie.key].max)]))

    let avg = g_daily.selectAll(".avg." + serie.class)
      .data(temperatures.filter(d => d.value[serie.key].avg > 0))
      .enter().append("rect")
        .attr("class", "avg " + serie.class)
        .attr("fill", "rgba(255,255,255,0.5)")
        .attr("x", d => x(d.value.date) - bandWidth*serie.width/2)
        .attr("y", d => y(d.value[serie.key].avg))
        .attr("width", bandWidth * serie.width)
        .attr("height", 1)

    let rect = g_daily.append("rect")
        .attr("class", "legend " + serie.class)
        .attr("fill", serie.fill)
        .attr("x", i * 10 + i * 70)
        .attr("y", 0)
        .attr("width", 10)
        .attr("height", 10)

    let label = g_daily.append("text")
        .attr("class", "text " + serie.class)
        .attr("x", 14 + i * 10 + i * 70)
        .attr("y", 5)
        .style("dominant-baseline", "middle")
        .style("font-size", "12px")
        .style("font-family", "sans-serif")
        .text(serie.label)
  })
};

var show_hourly = function(temperatures) {
  var svg_hourly = d3.select("svg#hourly"),
      margin = {top: 20, right: 40, bottom: 50, left: 40},
      width = +svg_hourly.node().getBoundingClientRect().width - margin.left - margin.right,
      height = +svg_hourly.attr("height") - margin.top - margin.bottom,
      g_hourly = svg_hourly.append("g").attr("transform", "translate(" + margin.left + "," + margin.top + ")");

  var x = d3.scaleTime().range([0, width]);
  var y = d3.scaleLinear().range([height, 0]);
  var z = d3.scaleOrdinal(d3.schemeCategory10);

  var line = d3.line()
    .curve(d3.curveBasis)
    .x(function(d) { return x(d.date); })
    .y(function(d) { return y(d.temperature); });

  x.domain(d3.extent(temperatures, function(d) { return d.value.date; }));
  y.domain([
    d3.min([
      d3.min(temperatures, d => d.value.temperature_meteo),
      d3.min(temperatures, d => d.value.temperature_veranda),
      d3.min(temperatures, d => d.value.temperature_salon),
    ]) - 2,
    d3.max([
      d3.max(temperatures, d => d.value.temperature_meteo),
      d3.max(temperatures, d => d.value.temperature_veranda),
      d3.max(temperatures, d => d.value.temperature_salon),
    ]) + 2
  ]);

  var y_humidity = d3.scaleLinear().range([height, 0]);
  y_humidity.domain([0, 100]);

  var extent = x.domain();

  // interval.range() is [start; stop[ so we have to add an extra day at the end
  var days = d3.timeDay.every(1).range(extent[0], d3.timeDay.offset(extent[1], 1));

  var line_humidity = d3.line()
    .curve(d3.curveBasis)
    .x(function(d) { return x(d.date); })
    .y(function(d) { return y_humidity(d.humidity); });

  locale = d3.timeFormatLocale({
    "dateTime": "%A, le %e %B %Y, %X",
    "date": "%d/%m/%Y",
    "time": "%H:%M:%S",
    "periods": ["AM", "PM"],
    "days": ["dimanche", "lundi", "mardi", "mercredi", "jeudi", "vendredi", "samedi"],
    "shortDays": ["dim.", "lun.", "mar.", "mer.", "jeu.", "ven.", "sam."],
    "months": ["janvier", "février", "mars", "avril", "mai", "juin", "juillet", "août", "septembre", "octobre", "novembre", "décembre"],
    "shortMonths": ["janv.", "févr.", "mars", "avr.", "mai", "juin", "juil.", "août", "sept.", "oct.", "nov.", "déc."]
  });

  g_hourly.append("g")
      .attr("class", "axis axis--x")
      .attr("transform", "translate(0," + height + ")")
      .call(d3.axisBottom(x)
          .tickSize(height * -1)
          .ticks(d3.timeHour.every(4))
          .tickFormat(function(d) { return locale.format("%Hh")(d) })
      );

  g_hourly.append("g")
      .attr("class", "axis axis--x-2")
      .attr("transform", "translate(0," + (height + 16) + ")")
      .call(d3.axisBottom(x)
          .ticks(d3.timeHour.every(12).filter(d => d.getHours() > 0)).tickSize(0)
          .tickFormat(function(d) { return d.getHours() > 0 ? locale.format("%a %d")(d) : "" })
      );

  g_hourly.append("g")
      .attr("class", "axis axis--y")
      .attr("transform", "translate(" + width + ", 0)")
      .call(d3.axisRight(y).ticks(36))
    .append("text")
      .attr("transform", "rotate(90)")
      .attr("y", 6)
      .attr("dy", "0.71em")
      .attr("fill", "#000")
      .text("Température (°C)");

  g_hourly.append("g")
      .attr("class", "axis axis--y-2")
      .call(d3.axisLeft(y_humidity))
    .append("text")
      .attr("transform", "rotate(-90)")
      .attr("y", 6)
      .attr("dy", "0.71em")
      .attr("fill", "#000")
      .text("Humidité (%)");

  let rect_daylight = g_hourly.selectAll(".rect.day")
    .data(days)
    .enter().append("rect")
      .attr("class", "rect day")
      .attr("fill", "yellow")
      .attr("opacity", "0.3")
      .attr("x", d => {
        let sun = SunCalc.getTimes(d, 50.6278, 3.0583);

        d.x = Math.min(Math.max(0, x(sun.sunrise)), width);
        d.width = Math.min(Math.max(0, x(sun.sunset) - d.x), width - d.x);
        return d.x;
      })
      .attr("y", 0)
      .attr("width", d => d.width)
      .attr("height", height)

  line_curve = g_hourly.selectAll(".line.celsius")
    .data([temperatures.map(d => {return {date: d.value.date, temperature: d.value.temperature_veranda}}).filter(d => d.temperature != undefined)])
    .enter().append("path")
      .attr("class", "line celsius")
      .attr("fill", "none")
      .attr("stroke", "orange")
      .attr("stroke-width", "3px")
      .attr("d", d => line(d))

  line_curve = g_hourly.selectAll(".line.celsius.outside")
    .data([temperatures.map(d => {return {date: d.value.date, temperature: d.value.temperature_exterieur}}).filter(d => d.temperature != undefined)])
    .enter().append("path")
      .attr("class", "line celsius")
      .attr("fill", "none")
      .attr("stroke", "green")
      .attr("stroke-width", "2px")
      .attr("d", d => line(d))

  line_curve = g_hourly.selectAll(".line.celsius.meteo")
    .data([temperatures.map(d => {return {date: d.value.date, temperature: d.value.temperature_meteo}}).filter(d => d.temperature != undefined)])
    .enter().append("path")
      .attr("class", "line celsius outside")
      .attr("fill", "none")
      .attr("stroke", "green")
      .attr("stroke-dasharray", "1 8")
      .attr("stroke-width", "0.75px")
      .attr("d", d => line(d))

  line_curve = g_hourly.selectAll(".line.celsius.boite")
    .data([temperatures.map(d => {return {date: d.value.date, temperature: d.value.temperature_boite}}).filter(d => d.temperature != undefined)])
    .enter().append("path")
      .attr("class", "line celsius boite")
      .attr("fill", "none")
      .attr("stroke", "red")
      .attr("stroke-width", "2px")
      .attr("d", d => line(d))

  line_curve = g_hourly.selectAll(".line.celsius.salon")
    .data([temperatures.map(d => {return {date: d.value.date, temperature: d.value.temperature_salon}}).filter(d => d.temperature != undefined)])
    .enter().append("path")
      .attr("class", "line celsius outside")
      .attr("fill", "none")
      .attr("stroke", "grey")
      .attr("stroke-width", "2px")
      .attr("d", d => line(d))

  line_curve = g_hourly.selectAll(".line.humidity")
    .data([temperatures.map(d => {return {date: d.value.date, humidity: d.value.humidity_veranda}}).filter(d => d.humidity != undefined)])
    .enter().append("path")
      .attr("class", "line humidity")
      .attr("fill", "none")
      .attr("stroke", "blue")
      .attr("stroke-dasharray", "4 8")
      .attr("stroke-width", "1px")
      .attr("d", d => line_humidity(d))

  line_curve = g_hourly.selectAll(".line.humidity.salon")
    .data([temperatures.map(d => {return {date: d.value.date, humidity: d.value.humidity_salon}}).filter(d => d.humidity != undefined)])
    .enter().append("path")
      .attr("class", "line humidity")
      .attr("fill", "none")
      .attr("stroke", "grey")
      .attr("stroke-dasharray", "4 8")
      .attr("stroke-width", "1px")
      .attr("d", d => line_humidity(d))

  var legend = g_hourly.append("g")
      .attr("font-family", "sans-serif")
      .attr("font-size", 10)
      .attr("text-anchor", "end")
    .selectAll("g")
    .data([
		{ title: "°C salon", attr: {
            "stroke": "grey",
            "stroke-width": "2px"
          } },
		{ title: "Humidité salon", attr: {
            "stroke": "grey",
            "stroke-dasharray": "4 2",
            "stroke-width": "1px"
          } },
		{ title: "°C véranda", attr: {
            "stroke": "orange",
            "stroke-width": "3px"
          } },
		{ title: "Humidité véranda", attr: {
            "stroke": "blue",
            "stroke-dasharray": "4 2",
            "stroke-width": "1px"
          } },
		{ title: "°C extérieur", attr: {
            "stroke": "green",
            "stroke-width": "2px"
          } },
	])
    .enter().append("g")
      .attr("transform", function(d, i) { return "translate(-30," + (10 + i * 20) + ")"; });

  legend.append("line")
      .attr("x1", width - 19)
      .attr("x2", width -  2)
      .attr("width", 19)
      .attr("height", 19)
      .attr("shape-rendering", "crispEdges")
      .style("stroke", d => d.attr['stroke'])
      .style("stroke-dasharray", d => d.attr['stroke-dasharray'])
      .style("stroke-width", d => d.attr['stroke-width'])

  legend.append("text")
      .attr("x", width - 24)
      .attr("y", 0)
      .attr("dy", "0.32em")
      .text(d => d.title);

};

var show_home_charts = (local_temperatures_url, lille_temperatures_url) => {
  d3.queue()
    .defer(d3.text, local_temperatures_url)
    .defer(d3.json, lille_temperatures_url)
    .await((error, text, lille) => {
      if (error) throw error;

      data = d3.dsvFormat(' ').parse(text).filter(function(d) {
        d.date = new Date((parseInt(d.time) + 3600*0) * 1000);

        d.temperature_veranda = d.temp3 != '-' ? parseFloat(d.temp3)
        : parseFloat(d.temp1)         - 6.50;
        d.temperature_exterieur = parseFloat(d.temp2)         * 0.85;
        d.temperature_salon     = parseFloat(d.temp_salon)    * 0.85;

        if (d.time < 1510415401) {
          d.temperature_entree    = parseFloat(d.temp_entree)   * 1.00;
        } else {
          d.temperature_entree    = parseFloat(d.temp_entree0)   * 1.00;
        }
        d.temperature_boite     = parseFloat(d.temp_boite)    * 1.00;

        d.humidity_veranda      = parseFloat(d.hum1);
        d.humidity_exterieur    = parseFloat(d.hum2);
        d.humidity_salon        = parseFloat(d.hum_salon);

        return d.date.getUTCMonth() >= 0;
      });

      var date_limit = new Date(new Date() - (3600*24*15 * 1000));
      temperatures_hourly = d3.nest()
        .key(function(d) {
            return d.date.getUTCFullYear() + '-' + d.date.getUTCMonth() + '-' + d.date.getUTCDate() + '-' + d.date.getUTCHours() + '-' + Math.floor(d.date.getUTCMinutes() / 30);
          })
        .rollup(function(d) {
          var data = {
            date: d[0].date,
            temperature_veranda:   d3.mean(d, d => d.temperature_veranda),
            temperature_exterieur: d3.mean(d, d => d.temperature_exterieur),
            temperature_salon:     d3.mean(d, d => d.temperature_salon),
            temperature_entree:    d3.mean(d, d => d.temperature_entree),
            temperature_boite :    d3.mean(d, d => d.temperature_boite),

            temperature_min_veranda:   d3.min(d, d => d.temperature_veranda),
            temperature_min_exterieur: d3.min(d, d => d.temperature_exterieur),
            temperature_min_salon:     d3.min(d, d => d.temperature_salon),
            temperature_min_entree:    d3.min(d, d => d.temperature_entree),
            temperature_min_boite:     d3.min(d, d => d.temperature_boite),

            temperature_max_veranda:   d3.max(d, d => d.temperature_veranda),
            temperature_max_exterieur: d3.max(d, d => d.temperature_exterieur),
            temperature_max_salon:     d3.max(d, d => d.temperature_salon),
            temperature_max_entree:    d3.max(d, d => d.temperature_entree),
            temperature_max_boite:     d3.max(d, d => d.temperature_boite),

            humidity_veranda:      d3.mean(d, d => d.humidity_veranda),
            humidity_exterieur:    d3.mean(d, d => d.humidity_exterieur),
            humidity_salon:        d3.mean(d, d => d.humidity_salon)
          };
          return data;
        })
        .entries(data);

      temperatures_lille = d3.nest()
        .key(function(d) {
          d.date = new Date(d.time * 1000);
          return d.date.getUTCFullYear() + '-' + d.date.getUTCMonth() + '-' + d.date.getUTCDate() + '-' + d.date.getUTCHours() + '-' + Math.floor(d.date.getUTCMinutes() / 30);
        })
        .rollup(function(d) {
          var data = {
            date: d[0].date,
            temperature_meteo: d3.mean(d, d => parseFloat(d.temperature)),
            humidity_meteo: d3.mean(d, d => parseFloat(d.humidity)),
          };
          return data;
        })
      .entries(lille);
      temperatures_lille = d3.map(temperatures_lille, d => d.key);

      temperatures_hourly.forEach(function(d) {
        if (temperatures_lille.has(d.key)) {
          d.value.temperature_meteo = temperatures_lille.get(d.key).value.temperature_meteo;
          d.value.humidity_meteo = temperatures_lille.get(d.key).value.humidity_meteo;
        }
      });

      show_hourly(temperatures_hourly.filter(d => d.value.date > date_limit));

      var temperatures_daily = d3.nest()
        .key(d => d.date.getUTCFullYear() + '-' + d.date.getUTCMonth() + '-' + d.date.getUTCDate())
        .rollup(d => {
          d[0].date.setHours(0);
          d[0].date.setMinutes(0);
          d[0].date.setSeconds(0);

          return {
            date: d[0].date,
            veranda: {
              min: d3.min(d, d => d.temperature_min_veranda),
              avg: d3.mean(d, d => d.temperature_veranda),
              max: d3.max(d, d => d.temperature_max_veranda)
            },
            exterieur: {
              min: d3.min(d, d => d.temperature_min_exterieur),
              avg: d3.mean(d, d => d.temperature_exterieur),
              max: d3.max(d, d => d.temperature_max_exterieur)
            },
            salon: {
              min: d3.min(d, d => d.temperature_min_salon),
              avg: d3.mean(d, d => d.temperature_salon),
              max: d3.max(d, d => d.temperature_max_salon)
            },
            entree: {
              min: d3.min(d, d => d.temperature_min_entree),
              avg: d3.mean(d, d => d.temperature_entree),
              max: d3.max(d, d => d.temperature_max_entree)
            },
            boite: {
              min: d3.min(d, d => d.temperature_min_boite),
              avg: d3.mean(d, d => d.temperature_boite),
              max: d3.max(d, d => d.temperature_max_boite)
            },
          }
        })
        .entries(temperatures_hourly.map(d => d.value));

    show_daily(d3.select("svg#daily-outside"), temperatures_daily.slice(2), [
      { key: "exterieur",
        class: "exterieur",
        label: "extérieur",
        width: 1,
        fill: "green"
      },
      { key: "veranda",
        class: "veranda",
        label: "véranda",
        width: 0.5,
        fill: "orange"
      },
      { key: "boite",
        class: "boite",
        label: "boite",
        width: 0.2,
        fill: "blue"
      },
    ]);

    show_daily(d3.select("svg#daily-inside"), temperatures_daily, [
      { key: "salon",
        class: "salon",
        label: "salon",
        width: 1,
        fill: "grey"
      },
      { key: "entree",
        class: "entree",
        label: "entrée",
        width: 0.66,
        fill: "teal"
      }
    ]);
  });
};
