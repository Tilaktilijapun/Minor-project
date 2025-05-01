
document.addEventListener("DOMContentLoaded", () => {
    const form = document.querySelector("form");
    const inputs = document.querySelectorAll("input");
    const button = document.querySelector("button");

    form.addEventListener("submit", (e) => {
        e.preventDefault();
        let valid = true;

        inputs.forEach((input) => {
            if (!input.value.trim()) {
                input.style.borderColor = "red";
                valid = false;
            } else {
                input.style.borderColor = "#ccc";
            }
        });

        if (valid) {
            alert("Signup successful!");
            form.reset();
        } else {
            alert("Please fill all fields.");
        }
    });
});


const header = document.querySelector(".header");
window.addEventListener("scroll", () => {
    if (window.scrollY > 50) {
        header.style.backgroundColor = "#003366";
        header.style.boxShadow = "0 4px 6px rgba(0, 0, 0, 0.1)";
    } else {
        header.style.backgroundColor = "transparent";
        header.style.boxShadow = "none";
    }
});


const observer = new IntersectionObserver((entries) => {
    entries.forEach((entry) => {
        if (entry.isIntersecting) {
            entry.target.classList.add("visible");
        } else {
            entry.target.classList.remove("visible");
        }
    });
}, { threshold: 0.1 });

document.querySelectorAll(".main, .footer").forEach((section) => {
    observer.observe(section);
});


const buttons = document.querySelectorAll("button");
buttons.forEach((button) => {
    button.addEventListener("mouseover", () => {
        button.style.transform = "scale(1.1)";
    });
    button.addEventListener("mouseout", () => {
        button.style.transform = "scale(1)";
    });
});


document.querySelectorAll("a").forEach((link) => {
    link.addEventListener("click", (e) => {
        e.preventDefault();
        const target = document.querySelector(e.target.getAttribute("href"));
        if (target) {
            target.scrollIntoView({ behavior: "smooth" });
        }
    });
});

document.querySelector("form").addEventListener("submit", function(e) {
    e.preventDefault(); 
    const formSection = document.querySelector(".form-section");
    const thankYouMessage = document.createElement("div");
    thankYouMessage.classList.add("thank-you-message");
    thankYouMessage.innerHTML = `
        <p>Thank you for signing up!</p>
        <p>Your registration is complete.</p>
    `;
    formSection.innerHTML = ""; 
    formSection.appendChild(thankYouMessage);
});

