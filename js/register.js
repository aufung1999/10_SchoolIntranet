function changeUserType() {
	document.getElementById('course_code').parentNode.parentNode.classList.add("hide");
	document.getElementById('course_title').parentNode.parentNode.classList.add("hide");
	document.getElementById('gender').parentNode.parentNode.classList.add("hide");
	document.getElementById('birthday').parentNode.parentNode.classList.add("hide");
	switch (document.getElementById('type').value) {
		case "Student":
			document.getElementById('gender').parentNode.parentNode.classList.remove("hide");
			document.getElementById('birthday').parentNode.parentNode.classList.remove("hide");
			break;
		case "Teacher":
			document.getElementById('course_code').parentNode.parentNode.classList.remove("hide");
			document.getElementById('course_title').parentNode.parentNode.classList.remove("hide");
			break;
	}
}

changeUserType(); //Same as onload when put the script tag at the end of the body

document.getElementById('type').addEventListener("change", changeUserType);
document.getElementById('formAuth').addEventListener("submit", dataVal);