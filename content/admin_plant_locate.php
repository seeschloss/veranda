<?php // vim: ft=html:et:sw=2:sts=2:ts=2
?>
<div id="photo">
  <?= $plant->place()->photo()->html() ?>
</div>
<div id="controls">
  <button id="plant-locate-dirt" title="Select an area at the base of the plant that can be used to evaluate the dirt's humidity">Designate dirt</button>
  <button id="plant-locate" title="Select an area which shows the whole plant">Designate whole plant</button>
</div>
<script>
  let div = document.querySelector('#photo');
  let img = document.querySelector('#photo img');
  let plant = <?= json_encode($plant) ?>;

  var ready = function() {
    svg = document.createElementNS("http://www.w3.org/2000/svg", 'svg');
    svg.id = "plants-svg";
    svg.style.position = "absolute";
    svg.style.left = img.offsetLeft + "px";
    svg.style.top = img.offsetTop + "px";
    svg.setAttributeNS(null, "version", "1.1");
    svg.setAttributeNS(null, "width", img.clientWidth);
    svg.setAttributeNS(null, "height", img.clientHeight);
    console.log(div);
    div.appendChild(svg);

    var drawRect = function(svg, x, y, width, height, cl, title) {
      let g = document.createElementNS("http://www.w3.org/2000/svg", "g");
      g.setAttributeNS(null, "class", cl);

      let rect = document.createElementNS("http://www.w3.org/2000/svg", "rect");

      let x_ratio = img.naturalWidth / img.clientWidth;
      let y_ratio = img.naturalHeight / img.clientHeight;

      rect.setAttributeNS(null, "x", x / x_ratio);
      rect.setAttributeNS(null, "y", y / y_ratio);
      rect.setAttributeNS(null, "width", width / x_ratio);
      rect.setAttributeNS(null, "height", height / y_ratio);
      rect.setAttributeNS(null, "rx", 3);
      rect.setAttributeNS(null, "ry", 3);
      g.appendChild(rect);

      if (title) {
        let text = document.createElementNS("http://www.w3.org/2000/svg", "text");
        text.setAttributeNS(null, "x", x / x_ratio);
        text.setAttributeNS(null, "y", y / y_ratio - 2);
        text.textContent = title;
        g.appendChild(text);
      }

      svg.appendChild(g);
      return g;
    };

    let rect_box = drawRect(svg, plant.box_x, plant.box_y, plant.box_width, plant.box_height, "box", "Plant");
    let rect_dirt = drawRect(svg, plant.dirt_x, plant.dirt_y, plant.dirt_width, plant.dirt_height, "dirt", "Dirt");

    let button_dirt = document.querySelector('#plant-locate-dirt');
    let button_plant = document.querySelector('#plant-locate');

    button_dirt.addEventListener('click', e => {
      getRectangle(img, rect => {
        var formData = new FormData();
        formData.append('plant[id]', <?= $plant->id ?>);
        formData.append('plant[dirt_x]', rect.x);
        formData.append('plant[dirt_y]', rect.y);
        formData.append('plant[dirt_width]', rect.width);
        formData.append('plant[dirt_height]', rect.height);

        formData.append('action', 'update');

        let xhr = new XMLHttpRequest();
        xhr.addEventListener("load", r => {
            rect_dirt.remove();
            rect_dirt = drawRect(svg, rect.x, rect.y, rect.width, rect.height, "dirt", "Dirt");
        });
        xhr.open("POST", "/admin/plant/<?= $plant->id ?>");
        xhr.setRequestHeader("Accept", "application/json");
        xhr.send(formData);
      });
    })

    button_plant.addEventListener('click', e => {
      getRectangle(img, rect => {
        var formData = new FormData();
        formData.append('plant[id]', <?= $plant->id ?>);
        formData.append('plant[box_x]', rect.x);
        formData.append('plant[box_y]', rect.y);
        formData.append('plant[box_width]', rect.width);
        formData.append('plant[box_height]', rect.height);

        formData.append('action', 'update');

        let xhr = new XMLHttpRequest();
        xhr.addEventListener("load", r => {
            rect_box.remove();
            rect_box = drawRect(svg, rect.x, rect.y, rect.width, rect.height, "box", "Plant");
        });
        xhr.open("POST", "/admin/plant/<?= $plant->id ?>");
        xhr.setRequestHeader("Accept", "application/json");
        xhr.send(formData);
      });
    })
  };

  if (img.complete) {
    ready();
  } else {
    img.addEventListener('load', e => { ready() })
  }
</script>
