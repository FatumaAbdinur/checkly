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