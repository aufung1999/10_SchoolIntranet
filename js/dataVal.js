

function dataVal(event) {
	//console.log("Validating");
    var form = document.getElementById('formAuth');

	let warns = form.querySelectorAll(".warn");
	for (var i = 0; i < warns.length; ++i) {
		warns[i].classList.remove("warn");
	}

	var flag = true;
	if (form.id.value.trim().length <= 0) { // Check login ID is not empty
		form.id.classList.add("warn");
		form.id.parentNode.parentNode.querySelector(".form-msg").innerText = "Please fill in your login ID.";
		flag = false;
	} else if (!/^[A-Za-z0-9_-]{1,}$/i.test(form.id.value)) { // Check login ID is valid
		form.id.parentNode.parentNode.querySelector(".form-msg").innerText = "Please just use alphanumeric characters (A-Z, a-z, 0-9), dash (-), or underscore (_).";
		form.id.classList.add("warn");
		flag = false;
	}
	
	if (form.email.value.trim().length > 0 && !/^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/.test(form.email.value)) { // Check email is valid
		console.log("Email is invalid");
		form.email.classList.add("warn");
		form.email.parentNode.parentNode.querySelector(".form-msg").innerText = "Please input a valid email.";
		flag = false;
	}
	if (form.password.value.trim().length <= 0) {
		form.password.classList.add("warn");
		form.password.parentNode.parentNode.querySelector(".form-msg").innerText = "Please fill in your password.";
		flag = false;
	}
	if (form.type.value == "Please select...") {
		form.type.classList.add("warn");
		form.type.parentNode.parentNode.querySelector(".form-msg").innerText = "Please choose your account type.";
		flag = false;
	} else if (form.type.value == "teacher") {
        if (form.course_code.value.trim().length <= 0) { // Check course code is not empty
            form.course_code.classList.add("warn");
            form.course_code.parentNode.parentNode.querySelector(".form-msg").innerText = "Please fill in your course code.";
            flag = false;
        } else if (!/^[A-Za-z0-9]{1,}$/i.test(form.course_code.value)) { // Check course code is valid
            form.course_code.parentNode.parentNode.querySelector(".form-msg").innerText = "Please just use alphanumeric characters (A-Z, a-z, 0-9).";
            form.course_code.classList.add("warn");
            flag = false;
        }
        if (form.course_title.value.trim().length <= 0) { // Check course title is not empty
            form.course_title.classList.add("warn");
            form.course_title.parentNode.parentNode.querySelector(".form-msg").innerText = "Please fill in your course title.";
            flag = false;
        } else if (form.course_title.value.includes("-")) { // Check course title is valid
            form.course_title.parentNode.parentNode.querySelector(".form-msg").innerText = "Please do not use dash/hyphen (-).";
            form.course_title.classList.add("warn");
            flag = false;
        }
	}
	if (form.elements.avatar.files.length > 0) {
		if (form.elements.avatar.files.length > 1) {
			form.elements.avatar.classList.add("warn");
			form.elements.avatar.parentNode.parentNode.querySelector(".form-msg").innerText = "Please upload single image.";
			flag = false;
		}
		if (!['image/gif', 'image/png', 'image/jpeg', 'image/bmp', 'image/webp'].includes(form.elements.avatar.files.type)) {
			form.elements.avatar.classList.add("warn");
			form.elements.avatar.parentNode.parentNode.querySelector(".form-msg").innerText = "Sorry, only JPEG, PNG, GIF, BMP and WEBP files are allowed.";
			flag = false;
		}
	}

	if (!flag) {
		event.preventDefault();
	}
	return flag;
}