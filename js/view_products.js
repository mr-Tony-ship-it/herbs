   // Update main image on thumbnail click
        function updateImage(src) {
            document.getElementById('main-image').src = src;
        }

        // Show feedback message
        function showMessage(message, type='') {
            const messageBox = document.getElementById('message-box');
            messageBox.className = 'message-box ' + type; // Add type for error or success
            messageBox.innerHTML = message;
            messageBox.style.display = 'block';
            setTimeout(() => {
                messageBox.style.display = 'none';
            }, 3000);
        }


        // Add to Cart functionality (just show a message for now)
        function addToCart(productId) {
            const qtyInput = document.getElementById('product-quantity');
            const qty = qtyInput.value;

            fetch('user/add_to_cart.php', { // Update this path based on your directory structure
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ product_id: productId, quantity: parseInt(qty, 10) })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showMessage(data.message, "success");
                } else {
                    showMessage(data.message, "error");
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showMessage("An error occurred while adding to cart.", "error");
            });
        }

        // Buy Now functionality (check login and redirect to checkout)
       // Buy Now functionality (clear cart and store the item)
