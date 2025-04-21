document.getElementById("uploadForm").addEventListener("submit", fileUpload);
const imagePreviewCtn = document.querySelector(".preview-image-ctn");
const videoPreviewCtn = document.querySelector(".preview-video-ctn");

const videoInput = document.getElementById("video");
const coverInput = document.getElementById("cover");
var currentTab = 0;
const prevBtn = document.getElementById("prev-btn");
const nextBtn = document.getElementById("next-btn");
const tabs = document.getElementsByClassName("tab");

const submitBtn = document.getElementById("submit-btn")
const totalSteps = tabs.length;

async function fileUpload(ev) {
  ev.preventDefault();

  const videoFile = document.getElementById("video").files[0];
  const coverFile = document.getElementById("cover").files[0];
  const title = document.getElementById("title").value;
  const description = document.getElementById("description").value;
  const progressBar = document.getElementById("uploadProgress");
  const statusBox = document.getElementById("upload-status");

  document.querySelector(".uploading").style.display = "block";

  const token = crypto.randomUUID();
  const chunkSize = 5 * 1024 * 1024; // 5MB
  const totalChunks = Math.ceil(videoFile.size / chunkSize);

  let start = 0;
  let step = 0;

//   statusBox.innerHTML = ""; // Clear previous messages

  while (start < videoFile.size) {
    const chunk = videoFile.slice(start, start + chunkSize);
    submitBtn.disabled = true
    try {
      const isLastChunk = step === totalChunks - 1;
      const res = await uploadChunk(
        chunk,
        videoFile.name,
        token,
        step,
        totalChunks,
        title,
        description,
        coverFile,
        isLastChunk
      );

      if (res.message) {
        statusBox.innerHTML = res.message;
      } else if (res.error) {
        statusBox.innerHTML = `${res.error}`;
        return;
      }
    } catch (err) {
      statusBox.innerHTML = `échec du téléchargement au chunk #${step}: ${err.message}`;
      return;
    }

    const percentComplete = Math.round(((step + 1) / totalChunks) * 100);
    progressBar.value = percentComplete;

    start += chunkSize;
    step += 1;
  }

  // Reset form and progress only after final chunk, assuming success
  window.setTimeout(() => {
    imagePreviewCtn.classList.remove("show");
    videoPreviewCtn.classList.remove("show");

    document.getElementById("uploadForm").reset();
    currentTab = 0;
    tabs[totalSteps - 1].style.display = "none"
    showTab(currentTab);

    document.querySelector(".uploading").style.display = "none";
    progressBar.value = 0;

    submitBtn.disabled = false;
  }, 2000);
}

async function uploadChunk(
  chunk,
  filename,
  token,
  step,
  totalChunks,
  title,
  description,
  coverFile,
  isLastChunk,
  retries = 3
) {
  const formData = new FormData();
  formData.append("file", chunk);
  formData.append("filename", filename);
  formData.append("token", token);
  formData.append("step", step);
  formData.append("totalChunks", totalChunks);

  if (isLastChunk) {
    formData.append("title", title);
    formData.append("description", description);
    formData.append("cover", coverFile);
  }

  try {
    const response = await fetch("/film/upload", {
      method: "POST",
      body: formData,
    });

    const text = await response.text();
    const jsonStart = text.indexOf("{");
    const data = JSON.parse(text.slice(jsonStart));

    if (!response.ok) {
      throw new Error(data.message || `HTTP ${response.status}`);
    }

    return data;
  } catch (error) {
    if (retries > 0) {
      return uploadChunk(
        chunk,
        filename,
        token,
        step,
        totalChunks,
        title,
        description,
        coverFile,
        isLastChunk,
        retries - 1
      );
    } else {
      throw error;
    }
  }
}

/*===tab ===*/

prevBtn.addEventListener("click", () => {
  tabs[currentTab].style.display = "none";
  showTab(currentTab - 1);
  currentTab -= 1;
});
nextBtn.addEventListener("click", () => {
  let canContinue = validateStep(currentTab);
  if (canContinue) {
    tabs[currentTab].style.display = "none";
    showTab(currentTab + 1);
    currentTab += 1;
  }
});

showTab(0);

function showTab(n) {
  tab = tabs[n];
  tab.style.display = "block";
  document.querySelector(".step").textContent = `${n + 1}/${totalSteps}`;

  if (n === 0) {
    prevBtn.style.display = "none";
  } else {
    prevBtn.style.display = "block";
  }

  if (n === totalSteps - 1) {
    document.querySelector("#submit-btn").style.display = "block";
    nextBtn.style.display = "none";
  } else {
    document.querySelector("#submit-btn").style.display = "none";
    nextBtn.style.display = "block";
  }
}
videoInput.addEventListener("change", (event) => {
  if (event.target.files.length > 1) {
    alert("ajouter un seul fichier");
  }
  event.target.parentNode.classList.remove("invalid");
  let file = event.target.files[0];

  let video = URL.createObjectURL(file);
  videoPreviewCtn.classList.add("show");
  document.querySelector("#video-preview").src = video;
  document.querySelector("#video-preview-file-name").textContent = file.name;
  document.querySelector("#video-preview-file-size").textContent =
    formatFileSize(file.size);
});

coverInput.addEventListener("change", (event) => {
  if (event.target.files.length > 1) {
    alert("ajouter un seul fichier");
  }
  event.target.parentNode.classList.remove("invalid");
  let file = event.target.files[0];

  let img = URL.createObjectURL(file);
  
  imagePreviewCtn.classList.add("show");
  document.querySelector("#image-preview").src = img;
  document.querySelector("#image-preview-file-name").textContent = file.name;
  document.querySelector("#image-preview-file-size").textContent =
    formatFileSize(file.size);
});

function formatFileSize(bytes, decimalPoint = 2) {
  if (bytes == 0) return "0 Bytes";
  var k = 1024,
    dm = decimalPoint || 2,
    sizes = ["Bytes", "KB", "MB", "GB", "TB", "PB", "EB", "ZB", "YB"],
    i = Math.max(0, Math.floor(Math.log(bytes) / Math.log(k)));
  return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + " " + sizes[i];
}

function validateStep(n) {
  const tab = tabs[n];

  const inputs = tab.getElementsByTagName("input");

  for (var i = 0; i < inputs.length; i++) {
    if (inputs[i].value == "") {
      inputs[i].parentNode.classList.add("invalid");
      return false;
    }

    if (i === inputs.length - 1 && inputs[i].value !== "") {
      return true;
    }
  }
}
