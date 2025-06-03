// Handle size checkbox changes to add/remove price and stock inputs dynamically
document.querySelectorAll('input[name="sizes[]"]').forEach((checkbox) => {
  checkbox.addEventListener("change", function () {
    const container = document.getElementById("sizesDetailsContainer");
    const size = this.value;

    if (this.checked) {
      if (!document.getElementById("details-" + size)) {
        const div = document.createElement("div");
        div.className = "size-price-stock";
        div.id = "details-" + size;
        div.innerHTML = `
          <strong>${size} Details:</strong><br/>
          <label for="price-${size}">Price (₱):</label>
          <input type="number" name="price_${size}" id="price-${size}" min="0" step="0.01" required />
          <label for="stock-${size}">Stock:</label>
          <input type="number" name="stock_${size}" id="stock-${size}" min="0" step="1" required />
        `;
        container.appendChild(div);
      }
    } else {
      const toRemove = document.getElementById("details-" + size);
      if (toRemove) container.removeChild(toRemove);
    }
  });
});

// Toggle select all checkboxes
function toggleSelectAll() {
  const selectAll = document.getElementById("selectAll");
  const checkboxes = document.querySelectorAll(".product-checkbox");
  checkboxes.forEach(cb => cb.checked = selectAll.checked);
  updateDeleteButton();
}

// Show or hide delete button based on selected products
function updateDeleteButton() {
  const checkboxes = document.querySelectorAll(".product-checkbox");
  const btn = document.getElementById("deleteSelectedBtn");
  const anyChecked = Array.from(checkboxes).some(cb => cb.checked);
  btn.style.display = anyChecked ? "inline-block" : "none";
}

// Delete multiple selected products
function deleteSelected() {
  if (!confirm('Are you sure you want to delete selected products?')) return;

  const selectedCheckboxes = Array.from(document.querySelectorAll(".product-checkbox:checked"));
  if (selectedCheckboxes.length === 0) {
    alert("No products selected.");
    return;
  }

  const idsToDelete = selectedCheckboxes.map(cb => cb.closest('.product-item').getAttribute('data-id'));

  fetch('/JELLYSCENT/php/delete_product.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ product_ids: idsToDelete })
  })
  .then(res => res.json())
  .then(data => {
    if (data.success) {
      selectedCheckboxes.forEach(cb => cb.closest('.product-item').remove());
      alert('Selected products deleted successfully.');
      updateDeleteButton();
    } else {
      alert('Failed to delete products: ' + (data.error || 'Unknown error'));
    }
  })
  .catch(() => alert('Error deleting selected products'));
}

// Edit a single product
function editProduct(btn) {
  const productItem = btn.closest('.product-item');
  const productId = productItem.getAttribute('data-id');
  const currentName = productItem.querySelector('h3').innerText;
  const currentDesc = productItem.querySelector('.product-description').innerText;
  const sizeInfoText = productItem.querySelector('.size-info').innerText;

  console.log("Original size info text:", sizeInfoText);

  const sizeStocks = {};
  sizeInfoText.split('|').forEach(part => {
    console.log("Processing size part:", part);
    const match = part.trim().match(/(\w+):₱([\d.]+) \(Stock:(\d+)\)/);
    if (match) {
      console.log("Matched size:", match[1], "stock:", match[3]);
      sizeStocks[match[1].trim()] = parseInt(match[3], 10);
    } else {
      console.log("Failed to match size pattern in:", part);
    }
  });

  if (Object.keys(sizeStocks).length === 0) {
    console.error("No valid size information found");
    alert("Error: Could not parse product size information");
    return;
  }

  console.log("Parsed size stocks:", sizeStocks);

  const newName = prompt("Edit product name:", currentName);
  if (newName === null || newName.trim() === "") {
    console.log("Edit cancelled or empty name");
    return;
  }

  const newDesc = prompt("Edit product description:", currentDesc);
  if (newDesc === null) {
    console.log("Edit cancelled at description");
    return;
  }

  const newStocks = {};
  for (const size in sizeStocks) {
    console.log("Processing stock for size:", size);
    const newStock = prompt(`Edit stock for ${size}:`, sizeStocks[size]);
    if (newStock === null) {
      console.log("Edit cancelled at stock input");
      return;
    }
    const stockNum = parseInt(newStock.trim(), 10);
    if (isNaN(stockNum) || stockNum < 0) {
      alert(`Invalid stock value for ${size}. Please enter a positive number.`);
      console.log("Invalid stock value entered:", newStock);
      return;
    }
    newStocks[size] = stockNum;
  }

  const updateData = {
    product_id: parseInt(productId, 10),
    name: newName.trim(),
    description: newDesc.trim(),
    stocks: newStocks
  };

  console.log("Sending update data:", updateData);

  fetch('/JELLYSCENT/php/update_product.php', {
    method: 'POST',
    headers: { 
      'Content-Type': 'application/json'
    },
    body: JSON.stringify(updateData)
  })
  .then(response => {
    console.log("Response status:", response.status);
    return response.text().then(text => {
      console.log("Raw response text:", text);
      try {
        return JSON.parse(text);
      } catch (e) {
        console.error("Failed to parse response as JSON:", text);
        throw new Error("Server returned invalid JSON");
      }
    });
  })
  .then(data => {
    console.log("Parsed response data:", data);
    if (data && data.success) {
      alert('Product updated successfully!');
      loadProducts(); // Refresh the product list
    } else {
      throw new Error(data && data.error ? data.error : 'Unknown error occurred');
    }
  })
  .catch(error => {
    console.error("Update error:", error);
    alert(`Error updating product: ${error.message}`);
  });
}

// Remove a single product
function removeProduct(btn) {
  if (!confirm('Are you sure you want to delete this product?')) return;

  const productItem = btn.closest('.product-item');
  const productId = productItem.getAttribute('data-id');

  fetch(`/JELLYSCENT/php/delete_product.php`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ product_id: productId })
  })
    .then(res => res.json())
    .then(data => {
      if (data.success) {
        productItem.remove();
        alert('Product deleted successfully!');
        updateDeleteButton();
      } else {
        alert('Delete failed: ' + (data.error || 'Unknown error'));
      }
    })
    .catch(err => {
      console.error("Delete error:", err);
      alert('Error deleting product');
    });
}

// Load products from PHP and render them
function loadProducts() {
  fetch('/JELLYSCENT/php/fetch_product.php')
    .then(response => response.json())
    .then(products => {
      console.log("Fetched products:", products);

      const container = document.getElementById("productListContainer");
      if (!container) {
        console.error("Product container not found");
        return;
      }
      container.innerHTML = "";

      if (!Array.isArray(products)) {
        if (products.error) {
          container.innerHTML = `<div class="error-message">Error: ${products.error}</div>`;
        } else {
          container.innerHTML = '<div class="error-message">Invalid response from server</div>';
        }
        return;
      }

      if (products.length === 0) {
        container.innerHTML = '<div class="no-products">No products found</div>';
        return;
      }

      products.forEach(product => {
        try {
          // Ensure product has required properties
          if (!product.product_id || !product.name) {
            console.error("Invalid product data:", product);
            return;
          }

          // Format size information
          let sizeInfo = '';
          if (Array.isArray(product.sizes) && product.sizes.length > 0) {
            sizeInfo = product.sizes.map(size => {
              if (!size || !size.size || typeof size.price === 'undefined' || typeof size.stock === 'undefined') {
                console.error("Invalid size data:", size);
                return '';
              }
              return `${size.size}:₱${parseFloat(size.price).toFixed(2)} (Stock:${size.stock})`;
            }).filter(Boolean).join(' | ');
          }

          if (!sizeInfo) {
            sizeInfo = 'No size information available';
          }

          const div = document.createElement("div");
          div.className = "product-item";
          div.setAttribute('data-id', product.product_id);

          // Create image HTML only if image exists and path is valid
          let imageHtml = '';
          if (product.image) {
            // Clean up the image path by removing any instances of the base path
            const cleanPath = product.image
              .replace(/^\/JELLYSCENT\/php\/uploads\//, '') // Remove if at start
              .replace(/\/+/g, '/'); // Replace multiple slashes with single slash
            const imagePath = `/JELLYSCENT/php/uploads/${cleanPath}`;
            imageHtml = `<img src="${imagePath}" alt="${product.name}" style="max-width: 100px; height: auto; margin-right: 15px;" onerror="this.style.display='none'">`;
          }

          div.innerHTML = `
            <div style="display: flex; align-items: center; gap: 10px;">
              <input type="checkbox" class="product-checkbox" onchange="updateDeleteButton()" />
              <div style="display: flex; align-items: start;">
                ${imageHtml}
                <div>
                  <h3>${product.name}</h3> 
                  <h4>
                    <input type="checkbox" class="best-selling-checkbox" data-id="${product.product_id}" ${product.best_selling ? 'checked' : ''}>
                    Best Selling  
                  </h4><br/>
                  <p class="size-info">${sizeInfo}</p>
                  <p class="product-description">${product.description || ''}</p>
                </div>
              </div>
            </div>
            <div style="margin-top: 20px;">
              <button class="btn btn-primary" onclick="editProduct(this)" style="margin-right: 10px;">Edit</button>
              <button class="btn btn-danger" onclick="removeProduct(this)">Remove</button>
            </div>
          `;

          container.appendChild(div);
        } catch (err) {
          console.error("Error processing product:", err, product);
        }
      });

      // Attach best-selling checkbox handlers
      document.querySelectorAll('.best-selling-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', function () {
          const productId = this.getAttribute('data-id');
          const isBestSelling = this.checked;

          console.log("Sending update for best selling:", { productId, isBestSelling });

          fetch('/JELLYSCENT/php/update_bestselling.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ product_id: productId, best_selling: isBestSelling })
          })
          .then(res => {
            if (!res.ok) {
              return res.text().then(text => {
                console.error("Error response:", text);
                throw new Error(`HTTP error! status: ${res.status}`);
              });
            }
            return res.json();
          })
          .then(data => {
            console.log("Server responded with:", data);
            if (!data.success) {
              this.checked = !this.checked; // Revert checkbox if update failed
              throw new Error(data.error || 'Unknown error');
            }
          })
          .catch(err => {
            console.error('Error updating best selling status:', err);
            this.checked = !this.checked; // Revert checkbox on error
            alert('Error updating status: ' + err.message);
          });
        });
      });

      updateDeleteButton();
    })
    .catch(error => {
      console.error("Failed to load products:", error);
      const container = document.getElementById("productListContainer");
      if (container) {
        container.innerHTML = `<div class="error-message">Error loading products: ${error.message}</div>`;
      }
    });
}

// Sign out function
function signOut() {
  window.location.href = '/JELLYSCENT/admin/login.html';
}

document.addEventListener("DOMContentLoaded", () => {
  loadProducts();
});
