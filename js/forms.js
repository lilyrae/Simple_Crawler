//check if form has been filled
function checkform(form) {

	var e = form['email'].value;
	var p = form['password'].value;

	if(e == "", p == "") {
		alert("Please fill out all the fields.");
		return false;
	}

	var pp = document.createElement("input");
	//add the new element to login form page
	form.appendChild(pp);

	pp.name = "p";
	pp.type = "hidden";
	pp.value = hex_sha512(p);

	//make sure plaintext password isn't sent
	form["password"].value = "";

	form.submit();
}