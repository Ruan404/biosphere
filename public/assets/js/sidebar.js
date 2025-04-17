var topics = document.querySelector(".sidebar-menu-ctn");

function showTab() {
  topics.classList.add("show");
  document.body.classList.add("black-mask");

  document.body.addEventListener("click", (ev)=>{
    if(ev.target.classList == "black-mask"){
      hideTab();
    }
  })
}
function hideTab() {
  topics.classList.remove("show");
  document.body.classList.remove("black-mask");
}