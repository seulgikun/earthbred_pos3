document.addEventListener('DOMContentLoaded', () => {
    // Password visibility toggle
    const passwordInput = document.getElementById('password');
    const passwordToggle = document.querySelector('.password-toggle');

    if (passwordToggle && passwordInput) {
        passwordToggle.addEventListener('click', () => {
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                passwordToggle.classList.remove('fa-eye');
                passwordToggle.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                passwordToggle.classList.remove('fa-eye-slash');
                passwordToggle.classList.add('fa-eye');
            }
        });
    }

    // Login Form Submit Handling
    const loginForm = document.querySelector('.login-form');
    
    if (loginForm) {
        loginForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;
            const loginBtn = document.querySelector('.login-btn');

            try {
                loginBtn.textContent = 'Logging in...';
                loginBtn.disabled = true;

                // Example: Connect to Laravel Backend
                // const API_URL = 'http://localhost:8000/api/login'; 
                // const response = await fetch(API_URL, {
                //     method: 'POST',
                //     headers: {
                //         'Content-Type': 'application/json',
                //         'Accept': 'application/json'
                //     },
                //     body: JSON.stringify({ email, password })
                // });
                // const data = await response.json();

                // Simulate API call for now
                setTimeout(() => {
                    // Redirect to the Point of Sale dashboard
                    window.location.href = '/pos';
                }, 800);
                
            } catch (error) {
                console.error('Error logging in:', error);
                loginBtn.textContent = 'Log In';
                loginBtn.disabled = false;
            }
        });
    }
});
