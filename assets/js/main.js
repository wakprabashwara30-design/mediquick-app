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
 * Custom Standard MediQuick Confirmation Modal System
 * Replaces ugly browser native confirm() dialogs with a sleek, accessible Bootstrap 5 Modal
 */
let mqConfirmModalInstance = null;

function ensureConfirmModalExists() {
  let modalEl = document.getElementById('mqConfirmModal');
  if (!modalEl) {
    const modalHtml = `
      <div class="modal fade mq-confirm-modal" id="mqConfirmModal" tabindex="-1" aria-hidden="true" style="z-index: 99999;">
        <div class="modal-dialog modal-dialog-centered" style="max-width: 420px;">
          <div class="modal-content border-0 shadow-lg" style="border-radius: 20px; overflow: hidden;">
            <div class="modal-body p-4 text-center">
              <div class="mq-confirm-icon-wrap mx-auto mb-3" id="mqConfirmIconWrap">
                <i class="bi bi-trash3-fill fs-2" id="mqConfirmIcon"></i>
              </div>
              <h5 class="fw-bold font-heading text-dark mb-2" id="mqConfirmTitle">
                Confirm Action
              </h5>
              <p class="text-secondary small mb-4 px-2" id="mqConfirmMessage" style="line-height: 1.55;">
                Are you sure you want to proceed with this action?
              </p>
              <div class="d-flex gap-2 justify-content-center">
                <button type="button" class="btn btn-light border px-4 py-2 rounded-3 fw-semibold flex-fill" data-bs-dismiss="modal">
                  Cancel
                </button>
                <button type="button" id="mqConfirmProceedBtn" class="btn btn-danger px-4 py-2 rounded-3 fw-bold flex-fill shadow-sm d-inline-flex align-items-center justify-content-center gap-1.5">
                  <i class="bi bi-trash3-fill" id="mqConfirmBtnIcon"></i> <span id="mqConfirmProceedText">Yes, Delete</span>
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>`;
    document.body.insertAdjacentHTML('beforeend', modalHtml);
    modalEl = document.getElementById('mqConfirmModal');
  }
  return modalEl;
}

function showModernConfirmModal(options) {
  const modalEl = ensureConfirmModalExists();
  const iconWrap = document.getElementById('mqConfirmIconWrap');
  const iconEl = document.getElementById('mqConfirmIcon');
  const titleEl = document.getElementById('mqConfirmTitle');
  const messageEl = document.getElementById('mqConfirmMessage');
  const proceedBtn = document.getElementById('mqConfirmProceedBtn');
  const proceedText = document.getElementById('mqConfirmProceedText');
  const proceedIcon = document.getElementById('mqConfirmBtnIcon');

  const title = options.title || 'Are you sure?';
  const message = options.message || 'This action cannot be undone.';
  const confirmText = options.confirmText || 'Yes, Delete';
  const type = options.type || 'danger'; // 'danger', 'warning', 'primary'
  const iconClass = options.iconClass || (type === 'danger' ? 'bi-trash3-fill' : (type === 'warning' ? 'bi-exclamation-triangle-fill' : 'bi-check2-circle'));

  if (titleEl) titleEl.textContent = title;
  if (messageEl) messageEl.textContent = message;
  if (proceedText) proceedText.textContent = confirmText;

  if (iconWrap) {
    iconWrap.className = 'mq-confirm-icon-wrap mx-auto mb-3 ' + type;
  }
  if (iconEl) {
    iconEl.className = 'bi ' + iconClass + ' fs-2';
  }
  if (proceedIcon) {
    proceedIcon.className = 'bi ' + iconClass;
  }

  if (proceedBtn) {
    proceedBtn.className = 'btn px-4 py-2 rounded-3 fw-bold flex-fill shadow-sm d-inline-flex align-items-center justify-content-center gap-1.5 ' + 
      (type === 'danger' ? 'btn-danger' : (type === 'warning' ? 'btn-warning text-dark' : 'btn-emerald'));
    
    // Clean old listener
    const newProceedBtn = proceedBtn.cloneNode(true);
    proceedBtn.parentNode.replaceChild(newProceedBtn, proceedBtn);

    newProceedBtn.addEventListener('click', function() {
      if (mqConfirmModalInstance) {
        mqConfirmModalInstance.hide();
      }
      if (typeof options.onConfirm === 'function') {
        options.onConfirm();
      }
    });
  }

  if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
    mqConfirmModalInstance = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
    mqConfirmModalInstance.show();
  } else {
    // Fallback if bootstrap JS is not ready
    if (confirm(message)) {
      if (typeof options.onConfirm === 'function') {
        options.onConfirm();
      }
    }
  }
}

/**
 * Universal confirmAction helper function
 * Intercepts inline onclick="return confirmAction(...)" calls and shows custom modal
 */
function confirmAction(message, title, actionType, confirmBtnText) {
  const e = window.event;
  let targetHref = null;
  let targetForm = null;

  if (e) {
    e.preventDefault();
    e.stopPropagation();
    const trigger = e.currentTarget || (e.target ? e.target.closest('a, button') : null);
    if (trigger) {
      if (trigger.tagName === 'A' && trigger.getAttribute('href') && trigger.getAttribute('href') !== '#') {
        targetHref = trigger.getAttribute('href');
      } else if (trigger.type === 'submit' && trigger.form) {
        targetForm = trigger.form;
      }
    }
  }

  // Derive intelligent title and action text from message if not provided
  const msgLower = (message || '').toLowerCase();
  let defaultTitle = title || 'Confirm Action';
  let defaultBtnText = confirmBtnText || 'Yes, Proceed';
  let defaultType = actionType || 'danger';

  if (msgLower.includes('delete') || msgLower.includes('remove') || msgLower.includes('clear')) {
    defaultTitle = title || (msgLower.includes('clear') ? 'Clear Items?' : (msgLower.includes('medicine') || msgLower.includes('product') ? 'Delete Medicine?' : 'Confirm Deletion'));
    defaultBtnText = confirmBtnText || (msgLower.includes('clear') ? 'Yes, Clear' : 'Yes, Delete');
    defaultType = actionType || 'danger';
  } else if (msgLower.includes('warning') || msgLower.includes('cancel')) {
    defaultTitle = title || 'Warning';
    defaultBtnText = confirmBtnText || 'Confirm';
    defaultType = actionType || 'warning';
  }

  showModernConfirmModal({
    title: defaultTitle,
    message: message || 'Are you sure you want to proceed?',
    confirmText: defaultBtnText,
    type: defaultType,
    onConfirm: function() {
      if (targetHref) {
        window.location.href = targetHref;
      } else if (targetForm) {
        targetForm.submit();
      }
    }
  });

  return false;
}


/**
 * Handle Prescription file selection, live preview & file info display
 */
function handlePrescriptionFile(input) {
  const prompt = document.getElementById('rxDropzonePrompt');
  const previewCard = document.getElementById('rxPreviewCard');
  const previewImg = document.getElementById('rxPreviewImg');
  const previewPdf = document.getElementById('rxPreviewPdf');
  const fileNameEl = document.getElementById('rxFileName');
  const fileSizeEl = document.getElementById('rxFileSize');

  if (!input.files || !input.files[0]) return;

  const file = input.files[0];
  const fileName = file.name;
  const fileSize = (file.size / (1024 * 1024)).toFixed(2) + ' MB';

  if (fileNameEl) fileNameEl.textContent = fileName;
  if (fileSizeEl) fileSizeEl.textContent = fileSize;

  if (file.type.startsWith('image/')) {
    const reader = new FileReader();
    reader.onload = function(e) {
      if (previewImg) {
        previewImg.src = e.target.result;
        previewImg.classList.remove('d-none');
      }
      if (previewPdf) previewPdf.classList.add('d-none');
    };
    reader.readAsDataURL(file);
  } else if (file.type === 'application/pdf' || fileName.toLowerCase().endsWith('.pdf')) {
    if (previewImg) previewImg.classList.add('d-none');
    if (previewPdf) previewPdf.classList.remove('d-none');
  }

  if (prompt) prompt.classList.add('d-none');
  if (previewCard) previewCard.classList.remove('d-none');
}

/**
 * Reset prescription file input and return dropzone to default state
 */
function resetPrescriptionFile() {
  const input = document.getElementById('rxFile');
  const prompt = document.getElementById('rxDropzonePrompt');
  const previewCard = document.getElementById('rxPreviewCard');
  const previewImg = document.getElementById('rxPreviewImg');
  const previewPdf = document.getElementById('rxPreviewPdf');

  if (input) input.value = '';
  if (previewImg) {
    previewImg.src = '#';
    previewImg.classList.add('d-none');
  }
  if (previewPdf) previewPdf.classList.add('d-none');
  if (previewCard) previewCard.classList.add('d-none');
  if (prompt) prompt.classList.remove('d-none');
}

// Drag & drop support for dropzone
document.addEventListener('DOMContentLoaded', function() {
  const dropzone = document.getElementById('rxDropzone');
  const fileInput = document.getElementById('rxFile');

  if (dropzone && fileInput) {
    ['dragenter', 'dragover'].forEach(eventName => {
      dropzone.addEventListener(eventName, function(e) {
        e.preventDefault();
        e.stopPropagation();
        dropzone.classList.add('dragover');
      }, false);
    });

    ['dragleave', 'drop'].forEach(eventName => {
      dropzone.addEventListener(eventName, function(e) {
        e.preventDefault();
        e.stopPropagation();
        dropzone.classList.remove('dragover');
      }, false);
    });

    dropzone.addEventListener('drop', function(e) {
      const dt = e.dataTransfer;
      const files = dt.files;
      if (files && files.length > 0) {
        fileInput.files = files;
        handlePrescriptionFile(fileInput);
      }
    }, false);
  }

  // Smooth Drag to Scroll for Category Scroll Pills
  const catScroll = document.getElementById('categoryScrollPills');
  if (catScroll) {
    let isDown = false;
    let startX;
    let scrollLeft;

    catScroll.addEventListener('mousedown', (e) => {
      isDown = true;
      startX = e.pageX - catScroll.offsetLeft;
      scrollLeft = catScroll.scrollLeft;
      catScroll.style.cursor = 'grabbing';
    });

    catScroll.addEventListener('mouseleave', () => {
      isDown = false;
      catScroll.style.cursor = 'grab';
    });

    catScroll.addEventListener('mouseup', () => {
      isDown = false;
      catScroll.style.cursor = 'grab';
    });

    catScroll.addEventListener('mousemove', (e) => {
      if (!isDown) return;
      e.preventDefault();
      const x = e.pageX - catScroll.offsetLeft;
      const walk = (x - startX) * 1.5;
      catScroll.scrollLeft = scrollLeft - walk;
    });
  }
});
