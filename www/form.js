var forms_ajax = document.querySelectorAll('form.ajax');

for (var i = 0; i < forms_ajax.length; i++) {
	var form_ajax = forms_ajax.item(i);

	form_ajax.onsubmit = function(e) {
		e.preventDefault();
		e.stopPropagation();

		form_submit(this);
	};
}

var form_submit = function(form_element) {
    let xhr = new XMLHttpRequest();
    xhr.addEventListener("load", r => {
			console.log(xhr.response);
    });
    xhr.open("POST", form_element.attributes.action.value);
    xhr.send(new FormData(form_element));
};

