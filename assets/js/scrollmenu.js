document.addEventListener("DOMContentLoaded", () => {
  const leftArrow = document.querySelector(".left-arrow");
  const rightArrow = document.querySelector(".right-arrow");
  const menu = document.getElementById("menu");
  const wrapper = document.getElementById("menu-wrapper");

  menu.style.left = "0px"; // position initiale

  const updateArrows = () => {
    const wrapperWidth = wrapper.offsetWidth;
    const menuWidth = menu.scrollWidth;
    const currentLeft = parseFloat(menu.style.left) || 0;

    leftArrow.classList.toggle("hidden", currentLeft >= 0);
    rightArrow.classList.toggle(
      "hidden",
      Math.abs(currentLeft) + wrapperWidth >= menuWidth
    );
  };

  const scrollMenu = (direction) => {
    const itemWidth =
      document.querySelector(".item").offsetWidth +
      parseInt(getComputedStyle(menu).gap || 0);
    let currentLeft = parseFloat(menu.style.left) || 0;

    currentLeft += direction * itemWidth;
    const maxScroll = wrapper.offsetWidth - menu.scrollWidth;

    if (currentLeft > 0) currentLeft = 0;
    if (currentLeft < maxScroll) currentLeft = maxScroll;

    menu.style.left = currentLeft + "px";
    updateArrows();
  };

  rightArrow.addEventListener("click", () => scrollMenu(-1));
  leftArrow.addEventListener("click", () => scrollMenu(1));

  updateArrows();
  window.addEventListener("resize", updateArrows);
});
