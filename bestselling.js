// Toggle mobile menu
document.querySelector('.mobile-menu-btn')?.addEventListener('click', () => {
  document.querySelector('.menu')?.classList.toggle('active');
});

console.log('Bestselling script loaded');

let allBestSellingProducts = [];

// Utility: Wait for element
function waitForElement(selector, timeout = 5000) {
  return new Promise((resolve, reject) => {
    const start = Date.now();
    const check = () => {
      const el = document.querySelector(selector);
      if (el) return resolve(el);
      if (Date.now() - start > timeout) return reject(`Timeout: ${selector}`);
      requestAnimationFrame(check);
    };
    check();
  });
}

// Load Best Selling Products
async function loadBestSellingProducts() {
  try {
    const res = await fetch('php/bestselling.php');
    if (!res.ok) throw new Error(`HTTP ${res.status}`);
    const products = await res.json();
    const container = document.querySelector('.best-selling .product-list');
    if (!container) throw new Error('Missing .product-list');
    container.innerHTML = '';

    if (!Array.isArray(products) || products.length === 0) {
      container.innerHTML = '<p class="no-products">No products available at the moment.</p>';
      return;
    }

    allBestSellingProducts = products;

    const frag = document.createDocumentFragment();
    products.forEach(p => {
      const price = p.sizes?.[0]?.price?.replace(/[₱]/g, '') || '0';
      const card = document.createElement('div');
      card.className = 'product-card';
      card.innerHTML = `
        <div class="product-image">
          <img src="php/uploads/${p.image}" alt="${p.name}" loading="lazy">
        </div>
        <h3 class="product-name">${p.name}</h3>
        <p class="product-price">${price}</p>
      `;
      card.addEventListener('click', () => {
        openPopup({
          id: p.id, // Ensure that the product ID is passed
          name: p.name,
          image: `php/uploads/${p.image}`,
          price: price,
          description: p.description,
          sizes: p.sizes
        });
      });
      frag.appendChild(card);
    });
    container.appendChild(frag);
  } catch (err) {
    console.error('Error loading best selling:', err);
    const c = document.querySelector('.best-selling .product-list');
    if (c) {
      c.innerHTML = `<p class="error-message">Failed to load products. <small>${err.message}</small></p>`;
    }
  }
}

// Open popup
function openPopup({ id, name, image, price, description, sizes }) {
  const popup = document.querySelector('.product-popup-overlay');
  const popupName = document.querySelector('.popup-product-name');
  const popupImage = document.querySelector('.popup-image img');
  const popupPrice = document.querySelector('.popup-product-price');
  const productSizeSelect = document.getElementById('product-size');
  const quantityInput = document.getElementById('product-quantity');
  const addToCartBtn = document.getElementById('add-to-cart-btn');

  if (!popup || !popupName || !popupImage || !popupPrice || !productSizeSelect || !quantityInput || !addToCartBtn) {
    console.error('Popup elements missing');
    return;
  }

  popupName.textContent = name;
  popupImage.src = image;
  popupImage.alt = name;
  quantityInput.value = 1;

  // Populate sizes
  productSizeSelect.innerHTML = '';
  if (Array.isArray(sizes)) {
    sizes.forEach(sizeObj => {
      const opt = document.createElement('option');
      opt.value = sizeObj.size;
      opt.textContent = sizeObj.size;
      opt.dataset.price = sizeObj.price;
      productSizeSelect.appendChild(opt);
    });
  }

  popupPrice.textContent = sizes?.[0]?.price || price;

  productSizeSelect.onchange = () => {
    const sel = productSizeSelect.selectedOptions[0];
    popupPrice.textContent = sel.dataset.price;
  };

  // ✅ Add to cart button setup here
  addToCartBtn.onclick = () => {
    const selectedSize = productSizeSelect.value; // Get selected size
    addToCart(id, name, selectedSize, popupPrice, quantityInput, productSizeSelect);
  };

  popup.classList.add('active');
  document.body.style.overflow = 'hidden';
}

// Close popup
function closePopup() {
  const popup = document.querySelector('.product-popup-overlay');
  if (popup) {
    popup.classList.remove('active');
    document.body.style.overflow = '';
  }
}

// Add to cart
async function addToCart(productId, name, size, popupPrice, quantityInput, productSizeSelect) {
  const price = parseFloat(popupPrice?.textContent.replace('₱', '')) || 0;
  const quantity = parseInt(quantityInput?.value) || 1;
  const totalPrice = price * quantity;

  console.log('Adding to cart with data:', {
    product_id: productId, // Ensure product_id is passed here
    product_name: name,
    size,
    price,
    quantity,
    total_price: totalPrice
  });

  try {
    const res = await fetch('php/cart_add.php?action=add', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ 
        product_id: productId, // Ensure that you're sending the correct product_id
        product_name: name,
        size,
        price,
        quantity,
        total_price: totalPrice 
      })
    });

    const data = await res.json();
    if (data.success) {
      alert(`Added to cart: ${quantity} x ${name} (${size})`);
      closePopup();
    } else {
      alert(data.message || 'Failed to add to cart.');
    }
  } catch (err) {
    console.error('Error adding to cart:', err);
    alert('Something went wrong while adding to cart.');
  }
}

// Setup popup controls
function setupPopupEventListeners() {
  document.querySelector('.close-popup')?.addEventListener('click', closePopup);

  document.querySelector('.product-popup-overlay')?.addEventListener('click', e => {
    if (e.target.classList.contains('product-popup-overlay')) closePopup();
  });

  document.addEventListener('keydown', e => {
    if (e.key === 'Escape') closePopup();
  });

  document.querySelector('.quantity-decrease')?.addEventListener('click', () => {
    const input = document.getElementById('product-quantity');
    if (input.value > 1) input.value--;
  });

  document.querySelector('.quantity-increase')?.addEventListener('click', () => {
    const input = document.getElementById('product-quantity');
    if (input.value < 10) input.value++;
  });
}

// --- USER SESSION --- 
function setupUserProfile() {
  checkSession();
}

async function checkSession() {
  try {
    const res = await fetch('php/check_session.php');
    const data = await res.json();
    updateUserInterface(data.success === true);
  } catch (err) {
    console.error('Session check error:', err);
    updateUserInterface(false);
  }
}

function updateUserInterface(isLoggedIn) {
  const userLink = document.getElementById('userLink');
  if (userLink) {
    userLink.title = isLoggedIn ? 'View Profile' : 'Click to login';
  }
}

window.handleUserAction = async function () {
  try {
    const res = await fetch('php/check_session.php');
    const data = await res.json();
    window.location.href = data.success ? './buyer/profile-user.html' : 'login.html';
  } catch {
    window.location.href = 'login.html';
  }
};

// Init on DOM ready
document.addEventListener('DOMContentLoaded', () => {
  loadBestSellingProducts();
  setupPopupEventListeners();
  setupUserProfile();

  const popup = document.querySelector('.product-popup-overlay');
  const popupName = document.querySelector('.popup-product-name');
  const popupImage = document.querySelector('.popup-image img');
  const popupPrice = document.querySelector('.popup-product-price');
  const productSizeSelect = document.getElementById('product-size');
  const quantityInput = document.getElementById('product-quantity');
  const addToCartBtn = document.querySelector('.add-to-cart-btn');

  console.log({
    popup,
    popupName,
    popupImage,
    popupPrice,
    productSizeSelect,
    quantityInput,
    addToCartBtn
  });
});
