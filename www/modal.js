var modal_links = document.querySelectorAll('.modal');
for (var i = 0; i < modal_links.length; i++) {
	var modal_link = modal_links.item(i);

	modal_link.onclick = function(e) {
		e.preventDefault();
		e.stopPropagation();

		modal_open(modal_link.href);
	};
}

var modal_open = function(url) {
    let xhr = new XMLHttpRequest();
    xhr.addEventListener("load", r => {
		modal_show(xhr.response);
    });
    xhr.open("GET", url);
    xhr.setRequestHeader("X-Modal", "modal");
    xhr.send();
};

var modal_close = function(element) {
	element.remove();
};

var modal_show = function(html) {
	var modal_element = document.createElement('div');
	modal_element.id = 'modal';

	modal_element.onclick = function(e) {
		if (e.target == modal_element) {
			modal_close(modal_element);
		}
	};

	document.body.onkeyup = function(e) {
		if (e.keyCode == 27) {
			modal_close(modal_element);
		}
	};

	var modal_content = document.createElement('div');
	modal_content.id = 'modal-content';
	modal_content.innerHTML = html;

	modal_element.appendChild(modal_content);
	document.body.appendChild(modal_element);

	var scripts = modal_content.querySelectorAll('script');
	for (var i = 0; i < scripts.length; i++) {
		eval(scripts.item(i).innerHTML);
	}
};

