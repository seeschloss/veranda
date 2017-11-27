var link_plants = function(id) {
    let container = document.querySelector('#' + id);
    let img = container.querySelector('img');

    var setup_links = function(container) {
      let plants = container.querySelectorAll('ul li');

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
          element: plant,
		  element_bounds: plant.getBoundingClientRect()
        });
      }

      let svg = document.createElementNS("http://www.w3.org/2000/svg", 'svg');
      svg.style.position = "absolute";
      svg.style.left = container.offsetLeft + "px";
      svg.style.top = container.offsetTop + "px";
      svg.setAttributeNS(null, "version", "1.1");
      svg.setAttributeNS(null, "width", container.clientWidth);
      svg.setAttributeNS(null, "height", container.clientHeight);
      container.appendChild(svg);

	  let svg_bounds = svg.getBoundingClientRect();
	  let img_bounds = img.getBoundingClientRect();

	  let img_offset_x = img_bounds.x - svg_bounds.x;
	  let img_offset_y = img_bounds.y - svg_bounds.y;

      svg = d3.select(svg);

      let link_shadow = d3.linkHorizontal()
        .source(d => [
			d.element_bounds.x - svg_bounds.x - 3,
			d.element_bounds.y - svg_bounds.y + d.element_bounds.height/2
		])
        .target(d => [
			(d.box_x + d.box_width/2) / x_ratio + img_offset_x,
			(d.box_y + d.box_height/2) / y_ratio + img_offset_y
		])
        .x(d => d[0] + 1)
        .y(d => d[1] + 1)

      let link = d3.linkHorizontal()
        .source(d => [
			d.element_bounds.x - svg_bounds.x - 3,
			d.element_bounds.y - svg_bounds.y + d.element_bounds.height/2
		])
        .target(d => [
			(d.box_x + d.box_width/2) / x_ratio + img_offset_x,
			(d.box_y + d.box_height/2) / y_ratio + img_offset_y
		])

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

	  return svg;
    };

	let svg = null;

	setTimeout(function() {
		if (img.complete) {
			svg = setup_links(container);
		} else {
			img.addEventListener('load', e => {
				svg = setup_links(container);
			})
		}
	}, 100);

	let resize_timer = 0;
    window.addEventListener('resize', e => {
		clearTimeout(resize_timer);
		resize_timer = setTimeout(function() {
			svg.remove();
			svg = setup_links(container);
		}, 250);
	})
};
