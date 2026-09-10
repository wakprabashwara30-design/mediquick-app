/**
 * MediQuick Pharmacy - University 1st-Year Web Application Scripts
 * Minimal, standard Vanilla JavaScript for clean UI interactions.
 */

document.addEventListener('DOMContentLoaded', function() {
  // 1. Auto-dismiss Bootstrap alerts after 4 seconds
  const alerts = document.querySelectorAll('.alert-dismissible');
  alerts.forEach(function(alert) {
    setTimeout(function() {
      const bsAlert = new bootstrap.Alert(alert);
      bsAlert.close();
    }, 4000);
  });
});

/**
 * Confirm before deleting an item (Products, Orders, Cart)
 */
function confirmAction(message) {
  return confirm(message || 'Are you sure you want to proceed with this action?');
}

/**
 * Preview uploaded prescription / product image before form submission
 */
function previewImage(input, previewElementId) {
  const preview = document.getElementById(previewElementId);
  if (!preview) return;

  if (input.files && input.files[0]) {
    const reader = new FileReader();
    reader.onload = function(e) {
      preview.src = e.target.result;
      preview.classList.remove('d-none');
    };
    reader.readAsDataURL(input.files[0]);
  }
}
