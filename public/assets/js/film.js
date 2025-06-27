const details = document.querySelector(".film-details");
const detailsCtn = document.querySelector(".details-ctn");

function fetchdata(videoId) {
  fetch(`/films/details/${videoId}`)
    .then((response) => {
      if (!response.ok) {
        throw new Error(`HTTP error: ${response.status}`);
      }
      return response.json();
    })
    .then((data) => {
      detailsCtn.innerHTML = `
                <img src='${data.cover_image}' />
                    <div class='film-info'>
                        <div class='film-info-ctn'>
                            <p class='film-title'>${data.title}</p>
                            <a class='primary-btn' href='/films/watch/${data.token}'>regarder le film</a>
                        </div>
                        <div class='film-info-ctn'>
                            <b>Description</b>
                            <p class='description'>${data.description}</p>
                        </div>
                    </div>
                    <button class="film-card-close-btn" onclick = 'hideFilmCard()'>
                        <svg width="24" height="24" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                          <path d="M20.3536 4.35355C20.5488 4.15829 20.5488 3.84171 20.3536 3.64645C20.1583 3.45118 19.8417 3.45118 19.6464 3.64645L12 11.2929L4.35355 3.64645C4.15829 3.45118 3.84171 3.45118 3.64645 3.64645C3.45118 3.84171 3.45118 4.15829 3.64645 4.35355L11.2929 12L3.64645 19.6464C3.45118 19.8417 3.45118 20.1583 3.64645 20.3536C3.84171 20.5488 4.15829 20.5488 4.35355 20.3536L12 12.7071L19.6464 20.3536C19.8417 20.5488 20.1583 20.5488 20.3536 20.3536C20.5488 20.1583 20.5488 19.8417 20.3536 19.6464L12.7071 12L20.3536 4.35355Z"></path>
                        </svg>
                    </button>
               
            `;

      details.classList.add("show");

      document.body.classList.add("no-overflow");

      setTimeout(() => {
        detailsCtn.classList.add("show");
      }, 100);
    })
    .catch(() => console.log);
}

function hideFilmCard() {
  detailsCtn.classList.remove("show");

  setTimeout(() => {
    details.classList.remove("show");
    document.body.classList.remove("no-overflow");
  }, 300);
}
