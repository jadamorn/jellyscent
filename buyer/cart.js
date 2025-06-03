document.addEventListener('DOMContentLoaded', function () {
    loadCart();

    // Select All checkbox listener
    const selectAllCheckbox = document.getElementById('select-all');
    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', async function () {
            const checked = this.checked;
            document.querySelectorAll('.item-checkbox').forEach(cb => {
                cb.checked = checked;
            });
            await updateSelectionStatusForAll(checked ? 1 : 0); // Update all items in DB
            updateCheckedTotalPrice();
        });
    }

    // Update total price when individual checkbox changes
    document.addEventListener('change', async function (e) {
        if (e.target.classList.contains('item-checkbox')) {
            const id = e.target.closest('tr').querySelector('.remove-btn').getAttribute('data-id');
            const isSelected = e.target.checked ? 1 : 0;
            await updateSelectionStatus(id, isSelected); // Update the item in DB
            updateCheckedTotalPrice();
            
            // Update "Select All" checkbox state
            const allCheckboxes = document.querySelectorAll('.item-checkbox');
            const allChecked = [...allCheckboxes].every(cb => cb.checked);
            if (selectAllCheckbox) selectAllCheckbox.checked = allChecked;
        }
    });
});

async function loadCart() {
    const response = await fetch('../php/cart.php?action=get');
    const data = await response.json();

    const cartItemsContainer = document.getElementById('cart-items');
    const totalPriceElement = document.getElementById('total-price');

    cartItemsContainer.innerHTML = '';
    let total = 0;

    data.forEach((item, index) => {
        const price = Number(item.price);
        const quantity = Number(item.quantity);
        const totalPrice = Number(item.total_price);

        const itemTotal = !isNaN(totalPrice) && totalPrice > 0
            ? totalPrice
            : (!isNaN(price * quantity) ? price * quantity : 0);

        total += itemTotal;

        const row = document.createElement('tr');

        // Checkbox cell - FIRST column
        const checkboxCell = document.createElement('td');
        const checkbox = document.createElement('input');
        checkbox.type = 'checkbox';
        checkbox.classList.add('item-checkbox');
        checkbox.checked = item.is_selected == 1; // Set initial checkbox state based on DB value
        checkboxCell.appendChild(checkbox);
        row.appendChild(checkboxCell);

        // Number column (No.) - SECOND column
        const numberCell = document.createElement('td');
        numberCell.textContent = (index + 1);
        row.appendChild(numberCell);

        // Product name
        const nameCell = document.createElement('td');
        nameCell.textContent = item.name || 'Product';
        row.appendChild(nameCell);

        // Size
        const sizeCell = document.createElement('td');
        sizeCell.textContent = item.size || '-';
        row.appendChild(sizeCell);

        // Price
        const priceCell = document.createElement('td');
        priceCell.textContent = '₱' + price.toFixed(2);
        row.appendChild(priceCell);

        // Quantity
        const quantityCell = document.createElement('td');
        quantityCell.textContent = quantity;
        row.appendChild(quantityCell);

        // Total price
        const totalPriceCell = document.createElement('td');
        totalPriceCell.classList.add('item-total-price');
        totalPriceCell.textContent = '₱' + itemTotal.toFixed(2);
        row.appendChild(totalPriceCell);

        // Remove button
        const removeCell = document.createElement('td');
        const removeBtn = document.createElement('button');
        removeBtn.textContent = 'Remove';
        removeBtn.classList.add('remove-btn');
        removeBtn.setAttribute('data-id', item.id);
        removeCell.appendChild(removeBtn);
        row.appendChild(removeCell);

        cartItemsContainer.appendChild(row);
    });

    totalPriceElement.textContent = '₱' + total.toFixed(2);

    attachEventListeners();
    updateCheckedTotalPrice();
}

function attachEventListeners() {
    document.querySelectorAll('.remove-btn').forEach(button => {
        button.addEventListener('click', handleRemove);
    });
}

async function handleRemove(event) {
    const id = event.target.getAttribute('data-id');

    const response = await fetch('../php/cart.php?action=delete', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `id=${id}`
    });

    const data = await response.json();

    if (data.status === 'success') {
        await loadCart();
    } else {
        alert('Failed to remove item');
    }
}

// Update the `is_selected` status in DB for a single item
async function updateSelectionStatus(id, isSelected) {
    console.log(`Updating selection for item ${id} to ${isSelected}`);
    const response = await fetch('../php/cart.php?action=update_selection', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `id=${id}&is_selected=${isSelected}`
    });

    const data = await response.json();
    console.log(data);  // Check the response object to ensure the backend is responding with the updated values

    if (data.status !== 'success') {
        alert('Failed to update selection');
    }
}

// Update the `is_selected` status in DB for all items
async function updateSelectionStatusForAll(isSelected) {
    const selectedItems = [...document.querySelectorAll('.item-checkbox')];
    const response = await fetch('../php/cart.php?action=update_selection_all', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `is_selected=${isSelected}`
    });

    const data = await response.json();
    console.log(data);  // Check the response object to ensure the backend is responding with the updated values

    if (data.status !== 'success') {
        alert('Failed to update selection for all items');
    }
}

// Update the total price for checked items
function updateCheckedTotalPrice() {
    let total = 0;
    document.querySelectorAll('.item-checkbox:checked').forEach((checkbox) => {
        const row = checkbox.closest('tr');
        const itemTotal = parseFloat(row.querySelector('.item-total-price').textContent.replace('₱', ''));
        if (!isNaN(itemTotal)) {
            total += itemTotal;
        }
    });
    document.getElementById('total-price').textContent = '₱' + total.toFixed(2);
}
