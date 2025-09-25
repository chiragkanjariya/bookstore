import './bootstrap';

// Global function to open auth modal
window.openAuthModal = function() {
    const authModal = document.getElementById('authModal');
    if (authModal) {
        authModal.classList.remove('hidden');
        // Show login form by default
        const loginForm = document.getElementById('loginForm');
        const registerForm = document.getElementById('registerForm');
        const modalTitle = document.getElementById('modalTitle');
        
        if (loginForm && registerForm && modalTitle) {
            loginForm.classList.remove('hidden');
            registerForm.classList.add('hidden');
            modalTitle.textContent = 'Sign In';
        }
    }
};

// Authentication Modal Functionality
document.addEventListener('DOMContentLoaded', function() {
    // Modal elements
    const authModal = document.getElementById('authModal');
    const openAuthModalBtn = document.getElementById('openAuthModal');
    const closeModalBtn = document.getElementById('closeModal');
    const modalTitle = document.getElementById('modalTitle');
    
    // Form elements
    const loginForm = document.getElementById('loginForm');
    const registerForm = document.getElementById('registerForm');
    const showRegisterFormBtn = document.getElementById('showRegisterForm');
    const showLoginFormBtn = document.getElementById('showLoginForm');
    
    // Form submission elements
    const loginFormElement = document.getElementById('loginFormElement');
    const registerFormElement = document.getElementById('registerFormElement');
    
    // Message elements
    const authMessage = document.getElementById('authMessage');
    const authMessageText = document.getElementById('authMessageText');
    
    // User dropdown functionality
    const userMenuButton = document.getElementById('userMenuButton');
    const userMenu = document.getElementById('userMenu');

    // Open modal
    if (openAuthModalBtn) {
        openAuthModalBtn.addEventListener('click', function() {
            authModal.classList.remove('hidden');
            showLoginForm();
        });
    }

    // Close modal
    if (closeModalBtn) {
        closeModalBtn.addEventListener('click', closeModal);
    }

    // Close modal when clicking outside
    if (authModal) {
        authModal.addEventListener('click', function(e) {
            if (e.target === authModal) {
                closeModal();
            }
        });
    }

    // Switch to register form
    if (showRegisterFormBtn) {
        showRegisterFormBtn.addEventListener('click', function(e) {
            e.preventDefault();
            showRegisterForm();
        });
    }

    // Switch to login form
    if (showLoginFormBtn) {
        showLoginFormBtn.addEventListener('click', function(e) {
            e.preventDefault();
            showLoginForm();
        });
    }

    // User dropdown toggle
    if (userMenuButton && userMenu) {
        userMenuButton.addEventListener('click', function(e) {
            e.preventDefault();
            userMenu.classList.toggle('hidden');
        });

        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            if (!userMenuButton.contains(e.target) && !userMenu.contains(e.target)) {
                userMenu.classList.add('hidden');
            }
        });
    }

    // Login form submission
    if (loginFormElement) {
        loginFormElement.addEventListener('submit', function(e) {
            e.preventDefault();
            submitForm(this, '/login', 'loginSubmitBtn');
        });
    }

    // Register form submission
    if (registerFormElement) {
        registerFormElement.addEventListener('submit', function(e) {
            e.preventDefault();
            submitForm(this, '/register', 'registerSubmitBtn');
        });
    }

    // Phone number validation for register modal
    const registerPhoneInput = document.getElementById('register_phone');
    if (registerPhoneInput) {
        // Allow only numeric input
        registerPhoneInput.addEventListener('input', function(e) {
            // Remove any non-digit characters
            this.value = this.value.replace(/\D/g, '');
            
            // Limit to 10 digits
            if (this.value.length > 10) {
                this.value = this.value.slice(0, 10);
            }
        });
        
        // Prevent non-numeric characters from being typed
        registerPhoneInput.addEventListener('keypress', function(e) {
            // Allow backspace, delete, tab, escape, enter
            if ([8, 9, 27, 13, 46].indexOf(e.keyCode) !== -1 ||
                // Allow Ctrl+A, Ctrl+C, Ctrl+V, Ctrl+X
                (e.keyCode === 65 && e.ctrlKey === true) ||
                (e.keyCode === 67 && e.ctrlKey === true) ||
                (e.keyCode === 86 && e.ctrlKey === true) ||
                (e.keyCode === 88 && e.ctrlKey === true)) {
                return;
            }
            
            // Ensure that it is a number and stop the keypress
            if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105)) {
                e.preventDefault();
            }
        });
    }

    // Functions
    function closeModal() {
        authModal.classList.add('hidden');
        clearErrors();
        clearMessages();
        resetForms();
    }

    function showLoginForm() {
        loginForm.classList.remove('hidden');
        registerForm.classList.add('hidden');
        modalTitle.textContent = 'Sign In';
        clearErrors();
        clearMessages();
    }

    function showRegisterForm() {
        loginForm.classList.add('hidden');
        registerForm.classList.remove('hidden');
        modalTitle.textContent = 'Sign Up';
        clearErrors();
        clearMessages();
    }

    function clearErrors() {
        // Clear all error messages
        const errorElements = document.querySelectorAll('[id$="_error"]');
        errorElements.forEach(element => {
            element.classList.add('hidden');
            element.textContent = '';
        });

        // Remove error styling from inputs
        const inputs = document.querySelectorAll('.auth-form input, .auth-form textarea');
        inputs.forEach(input => {
            input.classList.remove('border-red-500');
        });
    }

    function clearMessages() {
        authMessage.classList.add('hidden');
        authMessage.classList.remove('bg-green-100', 'text-green-700', 'bg-red-100', 'text-red-700');
    }

    function resetForms() {
        if (loginFormElement) loginFormElement.reset();
        if (registerFormElement) registerFormElement.reset();
    }

    function showMessage(message, isError = false) {
        authMessage.classList.remove('hidden');
        if (isError) {
            authMessage.classList.add('bg-red-100', 'text-red-700');
            authMessage.classList.remove('bg-green-100', 'text-green-700');
        } else {
            authMessage.classList.add('bg-green-100', 'text-green-700');
            authMessage.classList.remove('bg-red-100', 'text-red-700');
        }
        authMessageText.textContent = message;
    }

    function showErrors(errors) {
        Object.keys(errors).forEach(field => {
            const errorElement = document.getElementById(field + '_error');
            const inputElement = document.getElementById(field) || document.getElementById('login_' + field) || document.getElementById('register_' + field);
            
            if (errorElement) {
                errorElement.textContent = errors[field][0];
                errorElement.classList.remove('hidden');
            }
            
            if (inputElement) {
                inputElement.classList.add('border-red-500');
            }
        });
    }

    function setSubmitButtonLoading(buttonId, isLoading) {
        const button = document.getElementById(buttonId);
        const submitText = button.querySelector('.submit-text');
        const loadingSpinner = button.querySelector('.loading-spinner');
        
        if (isLoading) {
            button.disabled = true;
            submitText.classList.add('hidden');
            loadingSpinner.classList.remove('hidden');
        } else {
            button.disabled = false;
            submitText.classList.remove('hidden');
            loadingSpinner.classList.add('hidden');
        }
    }

    async function submitForm(form, url, submitButtonId) {
        clearErrors();
        clearMessages();
        setSubmitButtonLoading(submitButtonId, true);

        try {
            const formData = new FormData(form);
            
            const response = await fetch(url, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            });

            const data = await response.json();

            if (data.success) {
                showMessage(data.message);
                setTimeout(() => {
                    window.location.href = data.redirect || '/';
                }, 1500);
            } else {
                if (data.errors) {
                    showErrors(data.errors);
                } else {
                    showMessage(data.message || 'An error occurred. Please try again.', true);
                }
            }
        } catch (error) {
            console.error('Error:', error);
            showMessage('An error occurred. Please try again.', true);
        } finally {
            setSubmitButtonLoading(submitButtonId, false);
        }
    }
});
