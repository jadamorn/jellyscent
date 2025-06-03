document.addEventListener('DOMContentLoaded', () => {
  loadProducts();
  setupPopupControls();
  setupUserProfile();
});

async function loadProducts() {
  const container = document.querySelector('.product-list');
  try {
    const res = await fetch('php/get_products.php');
    const products = await res.json();

    container.innerHTML = '';

    products.forEach(product => {
      const card = document.createElement('div');
      card.className = 'product-card';
      card.dataset.productId = product.product_id;

      card.innerHTML = `
        <div class="product-image">
          <img src="php/uploads/${product.image}" alt="${product.name}">
        </div>
        <h3 class="product-name">${product.name}</h3>
        <p class="product-price">${product.sizes[0].price.toFixed()}</p>
      `;

      card.addEventListener('click', () => openProductPopup(product));
      container.appendChild(card);
    });
  } catch (e) {
    console.error('Error loading products:', e);
  }
}

function openProductPopup(product) {
  const overlay = document.querySelector('.product-popup-overlay');
  overlay.classList.add('active');

  const img = document.querySelector('.popup-image img');
  const name = document.querySelector('.popup-product-name');
  const desc = document.querySelector('.popup-product-description');
  const sizeSelect = document.getElementById('product-size');
  const priceEl = document.querySelector('.popup-product-price');
  const quantity = document.getElementById('product-quantity');

  img.src = `php/uploads/${product.image}`;
  img.alt = product.name;
  name.textContent = product.name;
  desc.textContent = product.description;
  sizeSelect.innerHTML = '';
  quantity.value = 1;

  product.sizes.forEach(size => {
    const opt = document.createElement('option');
    opt.value = size.size_id;
    opt.textContent = size.size;
    opt.dataset.price = size.price;
    sizeSelect.appendChild(opt);
  });

  priceEl.textContent = `${product.sizes[0].price.toFixed()}`;

  const newSizeSelect = sizeSelect.cloneNode(true);
  sizeSelect.parentNode.replaceChild(newSizeSelect, sizeSelect);

  newSizeSelect.addEventListener('change', () => {
    const selected = newSizeSelect.options[newSizeSelect.selectedIndex];
    priceEl.textContent = `${parseFloat(selected.dataset.price).toFixed(2)}`;
  });

  document.querySelector('.add-to-cart-btn').onclick = async () => {
    const selected = newSizeSelect.options[newSizeSelect.selectedIndex];
    const sizeText = selected.textContent;
    const price = parseFloat(selected.dataset.price);
    const qty = parseInt(quantity.value);
    const total = price * qty;

    try {
      const res = await fetch('php/cart_add.php?action=add', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify({
          product_id: product.product_id,
          product_name: product.name,
          size: sizeText,
          price: price,
          quantity: qty,
          total_price: total
        })
      });

      const text = await res.text();
      console.log('Raw server response:', text); // Debugging output

      let result;
      try {
        result = JSON.parse(text);
      } catch (parseError) {
        console.error("Add to cart JSON parse error:", parseError);
        alert("Server returned invalid response. Please check console.");
        return;
      }

      if (result.success) {
        alert("Product added to cart successfully!");
        overlay.classList.remove('active');
      } else {
        alert("Add to cart failed: " + result.message);
      }
    } catch (e) {
      alert("Failed to add to cart. Check console for details.");
      console.error('Add to cart error:', e);
    }
  };
}

function setupPopupControls() {
  document.querySelector('.close-popup').addEventListener('click', () => {
    document.querySelector('.product-popup-overlay').classList.remove('active');
  });

  document.querySelector('.quantity-increase').addEventListener('click', () => {
    const qty = document.getElementById('product-quantity');
    if (parseInt(qty.value) < 10) qty.value = parseInt(qty.value) + 1;
  });

  document.querySelector('.quantity-decrease').addEventListener('click', () => {
    const qty = document.getElementById('product-quantity');
    if (parseInt(qty.value) > 1) qty.value = parseInt(qty.value) - 1;
  });
}

function setupUserProfile() {
  const userLink = document.getElementById('userLink');
  if (!userLink) {
    console.error('User link not found');
    return;
  }
  checkSession();
}

async function checkSession() {
  try {
    const response = await fetch('php/check_session.php');
    const data = await response.json();
    updateUserInterface(data.success === true);
  } catch (error) {
    console.error('Error checking session:', error);
    updateUserInterface(false);
  }
}

window.handleUserAction = async function () {
  try {
    const response = await fetch('php/check_session.php');
    const data = await response.json();
    if (data.success === true) {
      window.location.href = './buyer/profile-user.html';
    } else {
      window.location.href = 'login.html';
    }
  } catch (error) {
    console.error('Error handling user action:', error);
    window.location.href = 'login.html';
  }
}

function updateUserInterface(isLoggedIn) {
  const userLink = document.getElementById('userLink');
  if (userLink) {
    userLink.title = isLoggedIn ? 'View Profile' : 'Click to login';
  }
}
