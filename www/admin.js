var setup = function(img) {
	showPlants(img);
	setupCanvas(img);
};

var showPlants = function(img) {
  let svg = document.querySelector('svg#plants-svg');
  if (!svg) {
    let img = document.querySelector('#photo-boite');
    svg = document.createElementNS("http://www.w3.org/2000/svg", 'svg');
    svg.id = "plants-svg";
    svg.style.position = "absolute";
    svg.style.left = img.offsetLeft + "px";
    svg.style.top = img.offsetTop + "px";
    svg.setAttributeNS(null, "version", "1.1");
    svg.setAttributeNS(null, "width", img.clientWidth);
    svg.setAttributeNS(null, "height", img.clientHeight);
    document.body.appendChild(svg);
  } else {
    let rects = svg.querySelectorAll('rect');
    for (var i = 0; i < rects.length; i++) {
      rects.item(i).remove();
    }
  }

	plants.forEach((plant, i) => {
		let rect = document.createElementNS("http://www.w3.org/2000/svg", "rect");

		let img = document.querySelector('#photo-boite');
		let x_ratio = img.naturalWidth / img.clientWidth;
		let y_ratio = img.naturalHeight / img.clientHeight;

		rect.setAttributeNS(null, "class", "plant");
		rect.setAttributeNS(null, "x", plant.box_x / x_ratio);
		rect.setAttributeNS(null, "y", plant.box_y / y_ratio);
		rect.setAttributeNS(null, "width", plant.box_width / x_ratio);
		rect.setAttributeNS(null, "height", plant.box_height / y_ratio);
		rect.setAttributeNS(null, "rx", 3);
		rect.setAttributeNS(null, "ry", 3);

		svg.appendChild(rect);

		rect.onmousedown = e => {
			showPlantForm(plant, i);
		}

		rect.onmouseover = e => {
			showPlantPopup(plant, i);
		}

		rect.onmouseout = e => {
			hidePlantPopup(plant, i);
		}
	});
};

var hidePlantForm = function() {
	let form = document.querySelector('div#plant-form');
	form.className = "";
};

var showPlantForm = function(plant, id) {
	let div = document.querySelector('div#plant-form');
	div.className = "displayed";

	let form = document.querySelector('div#plant-form form');

	let buttons = form.querySelectorAll('button[type="submit"]');
	for (var i = 0; i < buttons.length; i++) {
		buttons.item(i).onclick = function(e) {
			form.submitValue = this.value;
		};
	}

  form.querySelector('[name="id"]').value = id;

  for (var key in plant) {
    let input = form.querySelector('[name="' + key + '"]');
    if (input) {
      switch (key) {
        case "planted":
          let date = new Date(plant[key] * 1000);
          input.value = date.toISOString().split('T')[0];
          break;
        default:
          input.value = plant[key];
          break;
      }
    }
  }

  form.onsubmit = (e) => {
    e.preventDefault();
    e.stopPropagation();

    let xhr = new XMLHttpRequest();
    xhr.addEventListener("load", r => {
      plants = JSON.parse(xhr.response);
      hidePlantForm();
      showPlants();
    });
    xhr.open("POST", "/plant");
    xhr.setRequestHeader("Accept", "application/json");
	let data = new FormData(form);
	data.append("submit", form.submitValue);
    xhr.send(data);
  };

  form.querySelector('#area-button').onclick = function(e) {
	let plant_id = document.querySelector('input[name="id"]').value;
	let img = document.querySelector('#photo-boite');

	getRectangle(img, rect => {
		let xhr = new XMLHttpRequest();
		xhr.addEventListener("load", r => {
			plants = JSON.parse(xhr.response);
			showPlants();
		});
		xhr.open("POST", "/plant");
		xhr.setRequestHeader("Accept", "application/json");
		xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
		xhr.send("submit=update&id=" + plant_id + "&coordinates=" + encodeURIComponent(JSON.stringify(rect)));
	});
  };
};

var hidePlantPopup = function(plant, id) {
	let popup = document.querySelector('#plant-popup');
	popup.remove();
};

var showPlantPopup = function(plant, id) {
	let popup = document.createElement('div');
	popup.id = "plant-popup";
	popup.style.left = (plant.box_x + img.offsetLeft) + "px";
	popup.style.top = (plant.box_y + img.offsetTop + plant.box_height) + "px";

	popup.innerHTML = plant.name;

	document.body.appendChild(popup);
};

var getRectangle = function(img, callback) {
	let canvas = document.createElement('canvas');
	canvas.id = "drawing-canvas";
	canvas.style.position = "absolute";
	canvas.style.left = img.offsetLeft + "px";
	canvas.style.top = img.offsetTop + "px";
	canvas.style.cursor = "crosshair";
	canvas.width = img.clientWidth;
	canvas.height = img.clientHeight;
	document.body.appendChild(canvas);

	let ctx = canvas.getContext('2d');

	canvas.onmousedown = function(e) {
		e.preventDefault();
		e.stopPropagation();

		let x = e.offsetX;
		let y = e.offsetY;

		let width = 0;
		let height = 0;

		canvas.onmouseup = function(e) {
			e.preventDefault();
			e.stopPropagation();
		};

		canvas.onmousemove = function(e) {
			e.preventDefault();
			e.stopPropagation();

			width = e.offsetX - x;
			height = e.offsetY - y;

			ctx.clearRect(0, 0, canvas.width, canvas.height);
			ctx.strokeStyle = "green";
			ctx.strokeRect(x, y, width, height);
			ctx.strokeStyle = "white";
			ctx.strokeRect(x + 0.5, y + 0.5, width - 0.5, height - 0.5);

			canvas.onmouseup = function(e) {
				e.preventDefault();
				e.stopPropagation();

				let x_ratio = img.naturalWidth / img.clientWidth;
				let y_ratio = img.naturalHeight / img.clientHeight;
				canvas.remove();

				if (width < 0) {
					width *= -1;
					x = x - width;
				}

				if (height < 0) {
					height *= -1;
					y = y - height;
				}
				
				callback({
					x: Math.floor(x * x_ratio),
					y: Math.floor(y * y_ratio),
					width: Math.floor(width * x_ratio),
					height: Math.floor(height * y_ratio)
				});
			};
		};
	};
}

var setupCanvas = function(img) {
	document.querySelector('#new-plant').onclick = function() {
		let img = document.querySelector('#photo-boite');

		getRectangle(img, rect => {
			let xhr = new XMLHttpRequest();
			xhr.addEventListener("load", r => {
				plants = JSON.parse(xhr.response);
				showPlants();
			});
			xhr.open("POST", "/plant");
			xhr.setRequestHeader("Accept", "application/json");
			xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
			xhr.send("submit=insert&id=0&coordinates=" + encodeURIComponent(JSON.stringify(rect)));
		});
	};
};

