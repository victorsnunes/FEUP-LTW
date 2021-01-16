// Get the overlayLogin
var overlayLogin = document.getElementById('login-popup');

// When the user clicks anywhere outside of the overlayLogin, close it
window.onclick = function (event) {
  if (event.target == overlayLogin) {
    overlayLogin.style.display = "none";
  }
}

function displayLoginPopup() {
  document.getElementById('login-popup').style.display = 'block';
}

function displayRegisterPopup() {
  document.getElementById('register-popup').style.display = 'block';
}

function hideErrorDiv(node) {
  // node.parentNode.childNodes[1].className = "error-div hide";
}