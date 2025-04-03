<<<<<<< HEAD
function validateUsername() {
  const username = document.getElementById('username').value;
  const errorMessage = document.getElementById('username-error');
  
  if (!username) {
    errorMessage.textContent = 'Username is required.';
  } else {
    errorMessage.textContent = '';
  }
}

function validatePassword() {
  const password = document.getElementById('password').value;
  const errorMessage = document.getElementById('password-error');
  
  if (!password) {
    errorMessage.textContent = 'Password is required.';
  } else {
    errorMessage.textContent = '';
  }
}

const togglePasswordIcons = document.querySelectorAll('.toggle-password');
togglePasswordIcons.forEach(icon => {
  icon.addEventListener('click', function() {
    const input = this.previousElementSibling;
    if (input.type === 'password') {
      input.type = 'text';
      this.textContent = '👁‍🗨';
    } else {
      input.type = 'password';
      this.textContent = '👁‍🗨';
    }
  });
});

function validateForm() {
  validateUsername();
  validatePassword();

  // Ensure no errors exist before submitting
  const errors = document.querySelectorAll('.error-message');
  for (let error of errors) {
    if (error.textContent) {
      return false; // Prevent form submission if there are errors
    }
  }

  return true; // Allow form submission
}
=======
function validateUsername() {
  const username = document.getElementById('username').value;
  const usernameRegex = /^[A-Za-z]+$/;
  const errorMessage = document.getElementById('username-error');
  
  if (!username) {
    errorMessage.textContent = 'Username is required.';
  } else if (!usernameRegex.test(username)) {
    errorMessage.textContent = 'Username should only contain alphabetic characters.';
  } else {
    errorMessage.textContent = '';
  }
}

function validateEmail() {
  const email = document.getElementById('email').value;
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/; // Basic email format
  const errorMessage = document.getElementById('email-error');
  
  if (!email) {
    errorMessage.textContent = 'Email is required.';
  } else if (!emailRegex.test(email)) {
    errorMessage.textContent = 'Please enter a valid email address (e.g., example@example.com).';
  } else {
    errorMessage.textContent = '';
  }
}


function validatePassword() {
  const password = document.getElementById('password').value;
  const passwordRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/;
  const errorMessage = document.getElementById('password-error');
  
  if (!password) {
    errorMessage.textContent = 'Password is required.';
  } else if (!passwordRegex.test(password)) {
    errorMessage.textContent = 'Password must have at least 8 characters, including uppercase, lowercase, numbers, and special characters.';
  } else {
    errorMessage.textContent = '';
  }
}

function validateConfirmPassword() {
  const password = document.getElementById('password').value;
  const confirmPassword = document.getElementById('confirm_password').value;
  const errorMessage = document.getElementById('confirm-password-error');
  
  if (!confirmPassword) {
    errorMessage.textContent = 'Please confirm your password.';
  } else if (password !== confirmPassword) {
    errorMessage.textContent = 'Passwords do not match.';
  } else {
    errorMessage.textContent = '';
  }
}
const togglePasswordIcons = document.querySelectorAll('.toggle-password');
togglePasswordIcons.forEach(icon => {
  icon.addEventListener('click', function() {
    const input = this.previousElementSibling;
    if (input.type === 'password') {
      input.type = 'text';
      this.textContent = '👁‍🗨';
    } else {
      input.type = 'password';
      this.textContent = '👁‍🗨';
    }
  });
});

function validateForm() {
  validateUsername();
  validateEmail();
  validatePassword();
  validateConfirmPassword();

  // Ensure no errors exist and all fields are filled before submitting
  const errors = document.querySelectorAll('.error-message');
  for (let error of errors) {
    if (error.textContent) {
      return false; // If any error is present, prevent form submission
    }
  }

  // Ensure that all required fields are filled
  const requiredFields = ['username', 'email', 'password', 'confirm_password'];
  for (let fieldId of requiredFields) {
    const field = document.getElementById(fieldId);
    if (!field.value) {
      alert(`${fieldId.charAt(0).toUpperCase() + fieldId.slice(1)} is required.`);
      return false;
    }
  }

  // Display success message
  const username = document.getElementById('username').value;
  const email = document.getElementById('email').value;
  alert(`Congratulations ${username}! You have successfully signed up with the email: ${email}.`);

  // Redirect to the Homepage (Optional)
  // window.location.href = 'Homepage.html'; 

  return true;
}
>>>>>>> afd23aa (Checkly files)
