// Handle profile image selection
document.querySelector('.select-image-btn').addEventListener('click', function() {
    const input = document.createElement('input');
    input.type = 'file';
    input.accept = '.jpeg,.jpg,.png';
    input.onchange = function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const preview = document.querySelector('.profile-image-preview');
                preview.innerHTML = `<img src="${e.target.result}" alt="Profile Image">`;
            };
            reader.readAsDataURL(file);
        }
    };
    input.click();
});

// Fetch user data function
async function fetchUserData() {
    try {
        console.log('Fetching user data...');
        const response = await fetch('../php/get_user_data.php');
        const data = await response.json();
        console.log('User data response:', data);
        
        if (data.success) {
            const user = data.user;
            console.log('Successfully fetched user data:', user);

            // Fill form fields with user data
            document.getElementById('username').value = user.username || '';
            document.getElementById('first_name').value = user.first_name || '';
            document.getElementById('last_name').value = user.last_name || '';
            document.getElementById('email').value = user.email || '';
            document.getElementById('phone').value = user.phone || '';

            // Set gender radio if exists
            if (user.gender) {
                const genderInput = document.querySelector(`input[name="gender"][value="${user.gender}"]`);
                if (genderInput) genderInput.checked = true;
            }

            // Set birth date selects if birth_date_formatted exists
            if (user.birth_date_formatted) {
                document.getElementById('birth-date').value = parseInt(user.birth_date_formatted.date) || '';
                document.getElementById('birth-month').value = parseInt(user.birth_date_formatted.month) || '';
                document.getElementById('birth-year').value = parseInt(user.birth_date_formatted.year) || '';
            }

            // Update profile username display
            const profileUsername = document.querySelector('.profile-username');
            if (profileUsername) profileUsername.textContent = user.username;

            // Update profile name display if available
            const fullName = [user.first_name, user.last_name].filter(Boolean).join(' ');
            const profileName = document.querySelector('.profile-name');
            if (profileName && fullName) {
                profileName.textContent = fullName;
            }

        } else {
            console.error('Failed to fetch user data:', data);
            if (data.message === 'Not logged in') {
                window.location.href = '../login.html';
            }
        }
    } catch (error) {
        console.error('Error fetching user data:', error);
    }
}

// Handle profile form submission
document.querySelector('.profile-form').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = {
        username: document.getElementById('username').value,
        first_name: document.getElementById('first_name').value,
        last_name: document.getElementById('last_name').value,
        email: document.getElementById('email').value,
        phone: document.getElementById('phone').value,
        gender: document.querySelector('input[name="gender"]:checked')?.value || '',
        birth_date: document.getElementById('birth-date').value,
        birth_month: document.getElementById('birth-month').value,
        birth_year: document.getElementById('birth-year').value
    };

    try {
        const response = await fetch('../php/update_user_data.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify(formData)
        });

        const data = await response.json();

        if (data.success) {
            alert('Profile updated successfully!');
            fetchUserData();
        } else {
            alert('Failed to update profile: ' + (data.message || 'Unknown error'));
        }
    } catch (error) {
        console.error('Error updating profile:', error);
        alert('An error occurred while updating your profile.');
    }
});

// Initialize date selects function (date/month/year dropdowns)
function initializeDateSelects() {
    const dateSelect = document.getElementById('birth-date');
    const monthSelect = document.getElementById('birth-month');
    const yearSelect = document.getElementById('birth-year');

    // Clear existing options
    dateSelect.innerHTML = '<option value="">Date</option>';
    monthSelect.innerHTML = '<option value="">Month</option>';
    yearSelect.innerHTML = '<option value="">Year</option>';

    // Add date options 1-31
    for (let i = 1; i <= 31; i++) {
        const option = document.createElement('option');
        option.value = i;
        option.textContent = i;
        dateSelect.appendChild(option);
    }

    // Add months
    const months = ['January', 'February', 'March', 'April', 'May', 'June',
                    'July', 'August', 'September', 'October', 'November', 'December'];
    months.forEach((month, index) => {
        const option = document.createElement('option');
        option.value = index + 1;
        option.textContent = month;
        monthSelect.appendChild(option);
    });

    // Add years from current year back to 1950
    const currentYear = new Date().getFullYear();
    for (let y = currentYear; y >= 1950; y--) {
        const option = document.createElement('option');
        option.value = y;
        option.textContent = y;
        yearSelect.appendChild(option);
    }
}

// Section navigation, mobile menu toggle, and other UI handlers here (if you want, can include as well)

// On DOM ready, check session, then initialize date selects and fetch user data
document.addEventListener('DOMContentLoaded', () => {
    fetch('../php/check_session.php')
    .then(resp => resp.json())
    .then(data => {
        if (!data.success) {
            window.location.href = '../login.html';
        } else {
            initializeDateSelects();
            fetchUserData();
        }
    })
    .catch(() => window.location.href = '../login.html');
});
