window.addEventListener('DOMContentLoaded', () => {
  // Fetch data from the checkout.php PHP script
  fetch('../php/checkout.php')
    .then(res => res.json())
    .then(data => {
      // Handle errors if any
      if (data.error) {
        alert(data.error);
        return;
      }

      // Extract user data
      const user = data.user || {};
      const cart = data.cart || [];

      // Fill user info into the checkout form
      document.getElementById('customer-email').value = user.email || '';
      document.querySelector('input[placeholder="First name"]').value = user.first_name || '';
      document.querySelector('input[placeholder="Last name"]').value = user.last_name || '';
      document.querySelector('input[placeholder="Complete Address"]').value = user.comp_address || '';
      document.querySelector('input[placeholder="Barangay"]').value = user.brgy || '';
      document.querySelector('input[placeholder="0000"]').value = user.zipcode || '';
      document.querySelector('input[placeholder="City"]').value = user.city || '';
      document.querySelector('input[placeholder="Region"]').value = user.region || '';
      document.querySelector('input[placeholder="Mobile Number"]').value = user.phone_no || '';

      // Render cart products dynamically
      const productsList = document.getElementById('checkout-products-list');
      const subtotalEl = document.getElementById('checkout-subtotal');
      const shippingEl = document.getElementById('checkout-shipping-fee');
      const totalEl = document.getElementById('checkout-total');

      productsList.innerHTML = '';  // Clear previous list
      let subtotal = 0;

      // Loop through each cart item and display it
      cart.forEach(item => {
        const price = parseFloat(item.price); // Convert price to number
        const quantity = parseInt(item.quantity, 10); // Convert quantity to number
        const totalPrice = parseFloat(item.total_price); // Convert total price to number
        subtotal += totalPrice; // Add total price to subtotal

        // Construct the image URL (assuming images are stored in the 'uploads' directory)
        const imageUrl = item.image ? `../php/uploads/${item.image}` : '';  // Default image path
        const imageHtml = imageUrl ? `<img src="${imageUrl}" alt="${item.name}">` : '';

        // Add cart item to the product list
        productsList.innerHTML += `
          <li class="checkout-products-list-item">
            ${imageHtml}
            <span class="product-name">${item.name}</span>
            <span class="product-size">Size: ${item.size}</span>
            <span class="product-quantity">Qty: ${quantity}</span>
            <span class="product-price">${price.toFixed(2)}</span>
          </li>
        `;
      });

      // Calculate shipping (fixed fee in this case)
      const shipping = cart.length > 0 ? 50 : 0; // Example shipping fee
      const total = subtotal + shipping; // Total including shipping

      // Update the UI with the subtotal, shipping, and total
      subtotalEl.textContent = '₱' + subtotal.toFixed(2);
      shippingEl.textContent = '₱' + shipping.toFixed(2);
      totalEl.textContent = '₱' + total.toFixed(2);
    })
    .catch(() => alert('Failed to load checkout data.'));
});

// Handle "Pay Now" form submit
document.getElementById('checkout-form').addEventListener('submit', async (e) => {
  e.preventDefault(); // Prevent default form submission

  // Check if payment method is selected
  const selectedPayment = document.querySelector('.payment-card.selected');
  if (!selectedPayment) {
    alert('Please select a payment method.');
    return;
  }

  // Get form data
  const formData = {
    email: document.getElementById('customer-email').value,
    first_name: document.getElementById('first-name').value,
    last_name: document.getElementById('last-name').value,
    address: document.getElementById('complete-address').value,
    barangay: document.getElementById('barangay').value,
    zipcode: document.getElementById('zip-code').value,
    city: document.getElementById('city').value,
    region: document.getElementById('region').value,
    phone_no: document.getElementById('mobile-number').value,
    payment_method: selectedPayment.dataset.method
  };

  try {
    // Disable the submit button to prevent double submission
    const submitButton = document.getElementById('pay-now-btn');
    submitButton.disabled = true;
    submitButton.textContent = 'Processing...';

    // Send checkout request
    const response = await fetch('../php/cart.php?action=checkout', {
      method: 'POST',
      headers: { 
        'Content-Type': 'application/json'
      },
      body: JSON.stringify(formData)
    });

    const data = await response.json();

    if (data.status === 'success') {
      alert('Order placed successfully!');
      // Redirect to order page with order details
      window.location.href = `order.html?order_number=${data.order.order_number}`;
    } else {
      throw new Error(data.error || 'Failed to place order');
    }
  } catch (error) {
    alert(error.message || 'An error occurred while placing your order');
    // Re-enable the submit button on error
    const submitButton = document.getElementById('pay-now-btn');
    submitButton.disabled = false;
    submitButton.textContent = 'PAY NOW';
  }
});

// Handle payment method selection
document.querySelectorAll('.payment-card').forEach(card => {
  card.addEventListener('click', () => {
    // Remove selected class from all payment cards
    document.querySelectorAll('.payment-card').forEach(item => item.classList.remove('selected'));
    // Add the selected class to the clicked payment card
    card.classList.add('selected');
  });
});
