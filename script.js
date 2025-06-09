// Wait for the DOM to be fully loaded
document.addEventListener('DOMContentLoaded', function() {
    // Form validation for report form
    const reportForm = document.getElementById('reportForm');
    if (reportForm) {
        reportForm.addEventListener('submit', function(event) {
            if (!validateReportForm()) {
                event.preventDefault();
            }
        });
    }

    // Form validation for contact form
    const contactForm = document.getElementById('contactForm');
    if (contactForm) {
        contactForm.addEventListener('submit', function(event) {
            if (!validateContactForm()) {
                event.preventDefault();
            }
        });
    }

    // Form validation for admin login form
    const loginForm = document.getElementById('loginForm');
    if (loginForm) {
        loginForm.addEventListener('submit', function(event) {
            if (!validateLoginForm()) {
                event.preventDefault();
            }
        });
    }

    // Initialize date pickers
    const datePickers = document.querySelectorAll('.datepicker');
    if (datePickers.length > 0) {
        datePickers.forEach(function(picker) {
            // If you're using a date picker library, initialize it here
            // For example, with Bootstrap Datepicker:
            // $(picker).datepicker({ format: 'yyyy-mm-dd' });
        });
    }

    // Search functionality
    const searchForm = document.getElementById('searchForm');
    if (searchForm) {
        const searchResults = document.getElementById('searchResults');
        const loadingSpinner = document.getElementById('loadingSpinner');
        
        searchForm.addEventListener('submit', function(event) {
            event.preventDefault();
            
            if (loadingSpinner) {
                loadingSpinner.classList.remove('d-none');
            }
            
            // Simulate search delay
            setTimeout(function() {
                if (loadingSpinner) {
                    loadingSpinner.classList.add('d-none');
                }
                
                if (searchResults) {
                    searchResults.classList.remove('d-none');
                }
            }, 1000);
        });
    }
});

// Validate report form
function validateReportForm() {
    let isValid = true;
    
    // Get form fields
    const fullName = document.getElementById('fullName');
    const email = document.getElementById('email');
    const phone = document.getElementById('phone');
    const busCompany = document.getElementById('busCompany');
    const routeTraveled = document.getElementById('routeTraveled');
    const travelDate = document.getElementById('travelDate');
    const itemDescription = document.getElementById('itemDescription');
    const identifyingFeatures = document.getElementById('identifyingFeatures');
    
    // Reset error messages
    resetErrors();
    
    // Validate full name
    if (!fullName.value.trim()) {
        displayError(fullName, 'Full name is required');
        isValid = false;
    } else if (fullName.value.trim().length < 2) {
        displayError(fullName, 'Full name must be at least 2 characters');
        isValid = false;
    }
    
    // Validate email
    if (!email.value.trim()) {
        displayError(email, 'Email is required');
        isValid = false;
    } else if (!isValidEmail(email.value.trim())) {
        displayError(email, 'Please enter a valid email address');
        isValid = false;
    }
    
    // Validate phone
    if (!phone.value.trim()) {
        displayError(phone, 'Phone number is required');
        isValid = false;
    } else if (phone.value.trim().length < 10) {
        displayError(phone, 'Phone number must be at least 10 digits');
        isValid = false;
    }
    
    // Validate bus company
    if (!busCompany.value) {
        displayError(busCompany, 'Please select a bus company');
        isValid = false;
    }
    
    // Validate route traveled
    if (!routeTraveled.value.trim()) {
        displayError(routeTraveled, 'Route traveled is required');
        isValid = false;
    } else if (routeTraveled.value.trim().length < 5) {
        displayError(routeTraveled, 'Route must be at least 5 characters');
        isValid = false;
    }
    
    // Validate travel date
    if (!travelDate.value.trim()) {
        displayError(travelDate, 'Travel date is required');
        isValid = false;
    }
    
    // Validate item description
    if (!itemDescription.value.trim()) {
        displayError(itemDescription, 'Item description is required');
        isValid = false;
    } else if (itemDescription.value.trim().length < 10) {
        displayError(itemDescription, 'Description must be at least 10 characters');
        isValid = false;
    }
    
    // Validate identifying features
    if (!identifyingFeatures.value.trim()) {
        displayError(identifyingFeatures, 'Identifying features are required');
        isValid = false;
    } else if (identifyingFeatures.value.trim().length < 5) {
        displayError(identifyingFeatures, 'Identifying features must be at least 5 characters');
        isValid = false;
    }
    
    return isValid;
}

// Validate contact form
function validateContactForm() {
    let isValid = true;
    
    // Get form fields
    const name = document.getElementById('name');
    const email = document.getElementById('email');
    const subject = document.getElementById('subject');
    const message = document.getElementById('message');
    
    // Reset error messages
    resetErrors();
    
    // Validate name
    if (!name.value.trim()) {
        displayError(name, 'Name is required');
        isValid = false;
    } else if (name.value.trim().length < 2) {
        displayError(name, 'Name must be at least 2 characters');
        isValid = false;
    }
    
    // Validate email
    if (!email.value.trim()) {
        displayError(email, 'Email is required');
        isValid = false;
    } else if (!isValidEmail(email.value.trim())) {
        displayError(email, 'Please enter a valid email address');
        isValid = false;
    }
    
    // Validate subject
    if (!subject.value.trim()) {
        displayError(subject, 'Subject is required');
        isValid = false;
    } else if (subject.value.trim().length < 5) {
        displayError(subject, 'Subject must be at least 5 characters');
        isValid = false;
    }
    
    // Validate message
    if (!message.value.trim()) {
        displayError(message, 'Message is required');
        isValid = false;
    } else if (message.value.trim().length < 10) {
        displayError(message, 'Message must be at least 10 characters');
        isValid = false;
    }
    
    return isValid;
}

// Validate login form
function validateLoginForm() {
    let isValid = true;
    
    // Get form fields
    const username = document.getElementById('username');
    const password = document.getElementById('password');
    
    // Reset error messages
    resetErrors();
    
    // Validate username
    if (!username.value.trim()) {
        displayError(username, 'Username is required');
        isValid = false;
    }
    
    // Validate password
    if (!password.value.trim()) {
        displayError(password, 'Password is required');
        isValid = false;
    }
    
    return isValid;
}

// Helper function to display error message
function displayError(input, message) {
    const formGroup = input.closest('.mb-3');
    const errorElement = document.createElement('div');
    errorElement.className = 'invalid-feedback';
    errorElement.textContent = message;
    
    input.classList.add('is-invalid');
    
    // Check if error message already exists
    const existingError = formGroup.querySelector('.invalid-feedback');
    if (!existingError) {
        formGroup.appendChild(errorElement);
    }
}

// Helper function to reset all error messages
function resetErrors() {
    const invalidInputs = document.querySelectorAll('.is-invalid');
    const errorMessages = document.querySelectorAll('.invalid-feedback');
    
    invalidInputs.forEach(function(input) {
        input.classList.remove('is-invalid');
    });
    
    errorMessages.forEach(function(error) {
        error.remove();
    });
}

// Helper function to validate email format
function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

// Toggle password visibility
function togglePasswordVisibility() {
    const passwordInput = document.getElementById('password');
    const toggleIcon = document.getElementById('togglePassword');
    
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        toggleIcon.classList.remove('fa-eye');
        toggleIcon.classList.add('fa-eye-slash');
    } else {
        passwordInput.type = 'password';
        toggleIcon.classList.remove('fa-eye-slash');
        toggleIcon.classList.add('fa-eye');
    }
}

// Confirm delete
function confirmDelete(id, type) {
    return confirm(`Are you sure you want to delete this ${type}? This action cannot be undone.`);
}