document.addEventListener("DOMContentLoaded", function () {
    // Fetch about page content
    fetch("/minor project/aboutus_api.php")
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                showError("Error loading content");
                return;
            }

            // Update description
            const descriptionElement = document.getElementById("description");
            descriptionElement.innerHTML = `
                <p>${data.description}</p>
                <div class="about-stats">
                    <div class="stat">
                        <span class="stat-number">${data.stats.customers}+</span>
                        <span class="stat-label">Happy Customers</span>
                    </div>
                    <div class="stat">
                        <span class="stat-number">${data.stats.products}+</span>
                        <span class="stat-label">Products</span>
                    </div>
                    <div class="stat">
                        <span class="stat-number">${data.stats.years}</span>
                        <span class="stat-label">Years of Service</span>
                    </div>
                </div>
            `;

            // Update team section
            const teamContainer = document.getElementById("team");
            teamContainer.innerHTML = data.team.map(member => `
                <div class="team-member">
                    <div class="member-image">
                        <img src="${member.image}" alt="${member.name}">
                    </div>
                    <div class="member-info">
                        <h3>${member.name}</h3>
                        <p class="role">${member.role}</p>
                        <div class="member-social">
                            ${member.social ? `
                                <a href="${member.social.linkedin}" target="_blank"><i class="fab fa-linkedin"></i></a>
                                <a href="${member.social.github}" target="_blank"><i class="fab fa-github"></i></a>
                            ` : ''}
                        </div>
                    </div>
                </div>
            `).join('');
        })
        .catch(error => {
            console.error("Error fetching data:", error);
            showError("Failed to load content");
        });
});

function showError(message) {
    const elements = ['description', 'team'];
    elements.forEach(id => {
        const element = document.getElementById(id);
        if (element) {
            element.innerHTML = `<div class="error-message">${message}</div>`;
        }
    });
}