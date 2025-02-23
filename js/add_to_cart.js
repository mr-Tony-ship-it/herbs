<script>
// Open modal when "Add to Cart" button is clicked
    document.querySelectorAll('.add-to-cart-btn').forEach(button = {
    button.addEventListener('click', function() {
        const productId = button.getAttribute('data-product-id');
        const productName = button.getAttribute('data-product-name');
        const productPrice = button.getAttribute('data-product-price');
        const productImage = button.getAttribute('data-product-image');

        const modal = document.getElementById(`modal-${productId}`);
        const closeButton = modal.querySelector('.close-btn');

        // Populate modal content with product info
        modal.querySelector('.modal-product-image').src = `uploads/${productImage}`;
        modal.querySelector('h4').textContent = productName;
        modal.querySelector('p').textContent = `Price: Rs. ${parseFloat(productPrice).toFixed(2)}`;

        // Display the modal
        modal.style.display = "block";

        // Close the modal when clicking the close button
        closeButton.addEventListener('click', () => {
            modal.style.display = "none";
        });

        // Close the modal when clicking outside of the modal content
        window.onclick = function(event) {
            if (event.target === modal) {
                modal.style.display = "none";
            }
        };
    })
});

// Add to Cart inside Modal
document.querySelectorAll('.add-to-cart-modal-btn').forEach(button = {
    button.addEventListener('click', function() {
        const productId = button.getAttribute('data-product-id');

        // Send AJAX request to add to cart
        fetch('user/add_to_cart.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: 'id=' + encodeURIComponent(productId)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Product added to cart!');
            } else {
                alert('Error adding product to cart');
            }
        })
        .catch(error => {
            alert('An error occurred while adding the product to the cart');
        });
    })
});
</script>
