//for the login validation
function validateLogin() {
    let email = document.getElementById("email").value.trim();
    let password = document.getElementById("password").value.trim();

    if (!email || !password) {
        alert("Please fill all fields!");
        return false;
    }
    return true;
}

//for the register validation
function validateRegister() {
    let name = document.getElementById("full_name").value.trim();
    let email = document.getElementById("email").value.trim();
    let password = document.getElementById("password").value.trim();
    let role = document.getElementById("role").value;

    if (!name || !email || !password || !role) {
        alert("Please fill all fields!");
        return false;
    }

    if (password.length < 6) {
        alert("Password must be at least 6 characters!");
        return false;
    }

    return true;
}