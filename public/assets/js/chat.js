function fetchData(topic) {
    fetch(`/chat/api/${topic}`)
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            // Clear existing messages and build new content
            msgsDisplayCtn.innerHTML = buildMessagesHtml(data.messages);
            
            // Scroll to bottom
            msgsDisplayCtn.scroll({ top: msgsDisplayCtn.scrollHeight, behavior: 'smooth' });

            // Update current topic label
            currentTopic = topic;
            document.querySelector(".current-topic").innerText = currentTopic;

            // Attach event listeners for options visibility
            attachHoverEffectForOptions();
            attachClickToggleForOptions();
        })
        .catch(err => console.error(`Fetch problem: ${err.message}`));
}

// Builds HTML content for messages
function buildMessagesHtml(messages) {
    return messages.map(chat => `
        <div class="msg-ctn">
            <div class="msg-img"></div>
            <div class="msg-info-ctn">
                <div class="msg-pseudo-date-ctn">
                    <p class="msg-pseudo">${chat.pseudo}</p>
                    <p class="msg-date">${chat.date}</p>
                </div>
                <p>${chat.message}</p>
            </div>
            ${chat.options}
        </div>
    `).join(''); // Join to make it a single string instead of multiple concatenations
}

// Attach mouseover and mouseout events to show options
function attachHoverEffectForOptions() {
    const optionsCtn = document.getElementsByClassName("options-ctn");
    
    Array.from(optionsCtn).forEach(el => {
        const parent = el.parentNode;
        
        parent.addEventListener("mouseover", () => el.classList.add("show"));
        parent.addEventListener("mouseout", () => el.classList.remove("show"));
    });
}

// Attach click event to toggle options visibility
function attachClickToggleForOptions() {
    const optionTab = document.getElementsByClassName("options-btn");
    
    Array.from(optionTab).forEach(el => {
        el.addEventListener("click", (ev) => {
            const options = el.parentNode.children[0];
            options.classList.toggle("show");
            
            // Close options when clicking outside
            window.addEventListener("click", (evOutside) => {
                if (evOutside.target !== options && evOutside.target !== el && options.classList.contains("show")) {
                    options.classList.remove("show");
                }
            });
        });
    });
}
