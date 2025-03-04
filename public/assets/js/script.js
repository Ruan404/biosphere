const burger = document.querySelector('.nav-btn')
const burgerLines = burger.children
const navLinks = document.querySelector('.nav-links')

const burgerL1 = burgerLines[0]
const burgerL2 = burgerLines[1]
const burgerL3 = burgerLines[2]

burger.addEventListener("click", toggleNav)


function toggleNav() {
    navLinks.classList.toggle('show')
    burgerL1.classList.toggle('nav-btn-active-l1')
    burgerL2.classList.toggle('hide')
    burgerL3.classList.toggle('nav-btn-active-l3')
    document.body.classList.toggle('no-overflow')
}