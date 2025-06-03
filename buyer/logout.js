// Logout functionality
const logoutBtn = document.getElementById('logoutBtn');
if (logoutBtn) {
    logoutBtn.addEventListener('click', async function(e) {
        e.preventDefault();
        try {
            const response = await fetch('../php/logout.php');
            const data = await response.json();
            
            if (data.success) {
                // Clear any stored user data
                localStorage.removeItem('user_session');
                localStorage.removeItem('jellyscentCart');
                
                // Redirect to index page
                window.location.href = '../index.html';
            } else {
                console.error('Logout failed:', data.message);
                alert('Logout failed. Please try again.');
            }
        } catch (error) {
            console.error('Error during logout:', error);
            alert('An error occurred during logout. Please try again.');
        }
    });
} 