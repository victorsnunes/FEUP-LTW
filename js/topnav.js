function topnavResponsive() {
  let topnavbar = document.getElementById("topnavbar");

  if (topnavbar.className === "topnav") topnavbar.className += " responsive";
  else topnavbar.className = "topnav";
}