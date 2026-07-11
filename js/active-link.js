const links = document.querySelectorAll(".sidebar ul li a");
links.forEach(link => {
  if (link.href === window.location.href) {
    link.classList.add("active");
  }
});