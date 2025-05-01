document.addEventListener("DOMContentLoaded", () => {
    const searchButton = document.querySelector(".search-bar button");
    const searchInput = document.querySelector(".search-bar input");

    searchButton.addEventListener("click", () => {
        const query = searchInput.value.toLowerCase();
        const products = document.querySelectorAll(".product");

        products.forEach(product => {
            const productName = product.querySelector("h3").textContent.toLowerCase();
            if (productName.includes(query)) {
                product.style.display = "block";
            } else {
                product.style.display = "none";
            }
        });
    });

    searchInput.addEventListener("keyup", (e) => {
        if (e.key === "Enter") {
            searchButton.click();
        }
    });
});
