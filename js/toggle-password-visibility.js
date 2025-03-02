
function togglePasswordVisibility() {
    const inp = document.getElementById("password");
    const img = document.getElementById("toggle");
    if (inp.type === "password") {
        inp.type = "text";
        img.src = "img/eye-closed.png";
    } else {
        inp.type = "password";
        img.src = "img/eye-open.png";
    }
}
