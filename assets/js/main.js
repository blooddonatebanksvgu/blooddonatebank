/**
 * Blood Bank Management System - Main JavaScript
 */

document.addEventListener("DOMContentLoaded", function () {
  // Initialize components
  initSidebar();
  initDropdowns();
  initModals();
  initForms();
  initAlerts();
  initDeleteConfirmation();
  initDynamicSelects();
});

/**
 * Sidebar Toggle
 */
function initSidebar() {
  const toggleBtn = document.querySelector(".toggle-sidebar");
  const sidebar = document.querySelector(".sidebar");
  const mainContent = document.querySelector(".main-content");

  if (toggleBtn && sidebar) {
    toggleBtn.addEventListener("click", function () {
      sidebar.classList.toggle("collapsed");
      sidebar.classList.toggle("show");
      if (mainContent) {
        mainContent.classList.toggle("expanded");
      }
      // Save state to localStorage
      localStorage.setItem(
        "sidebarCollapsed",
        sidebar.classList.contains("collapsed"),
      );
    });

    // Restore state from localStorage
    if (localStorage.getItem("sidebarCollapsed") === "true") {
      sidebar.classList.add("collapsed");
      if (mainContent) {
        mainContent.classList.add("expanded");
      }
    }
  }

  // Close sidebar on mobile when clicking outside
  document.addEventListener("click", function (e) {
    if (window.innerWidth <= 992) {
      if (
        sidebar &&
        !sidebar.contains(e.target) &&
        toggleBtn &&
        !toggleBtn.contains(e.target)
      ) {
        sidebar.classList.remove("show");
      }
    }
  });
}

/**
 * Dropdown Menus
 */
function initDropdowns() {
  const dropdownToggles = document.querySelectorAll(".dropdown-toggle");

  dropdownToggles.forEach((toggle) => {
    toggle.addEventListener("click", function (e) {
      e.stopPropagation();
      const dropdown = this.closest(".user-dropdown");
      const menu = dropdown.querySelector(".dropdown-menu");

      // Close other dropdowns
      document.querySelectorAll(".dropdown-menu.show").forEach((m) => {
        if (m !== menu) m.classList.remove("show");
      });

      menu.classList.toggle("show");
    });
  });

  // Close dropdown when clicking outside
  document.addEventListener("click", function () {
    document.querySelectorAll(".dropdown-menu.show").forEach((menu) => {
      menu.classList.remove("show");
    });
  });
}

/**
 * Modal Handling
 */
function initModals() {
  // Open modal
  document.querySelectorAll("[data-modal-open]").forEach((btn) => {
    btn.addEventListener("click", function () {
      const modalId = this.getAttribute("data-modal-open");
      const modal = document.getElementById(modalId);
      if (modal) {
        openModal(modal);
      }
    });
  });

  // Close modal
  document
    .querySelectorAll(".modal-close, [data-modal-close]")
    .forEach((btn) => {
      btn.addEventListener("click", function () {
        const modal = this.closest(".modal-overlay");
        if (modal) {
          closeModal(modal);
        }
      });
    });

  // Close modal on overlay click
  document.querySelectorAll(".modal-overlay").forEach((overlay) => {
    overlay.addEventListener("click", function (e) {
      if (e.target === this) {
        closeModal(this);
      }
    });
  });

  // Close modal on ESC key
  document.addEventListener("keydown", function (e) {
    if (e.key === "Escape") {
      const openModal = document.querySelector(".modal-overlay.show");
      if (openModal) {
        closeModal(openModal);
      }
    }
  });
}

function openModal(modal) {
  modal.classList.add("show");
  document.body.style.overflow = "hidden";
}

function closeModal(modal) {
  modal.classList.remove("show");
  document.body.style.overflow = "";
}

/**
 * Form Validation
 */
function initForms() {
  const forms = document.querySelectorAll("form[data-validate]");

  forms.forEach((form) => {
    form.addEventListener("submit", function (e) {
      let isValid = true;

      // Remove previous error states
      form.querySelectorAll(".is-invalid").forEach((el) => {
        el.classList.remove("is-invalid");
      });
      form.querySelectorAll(".invalid-feedback").forEach((el) => {
        el.remove();
      });

      // Validate required fields
      form.querySelectorAll("[required]").forEach((field) => {
        if (!field.value.trim()) {
          isValid = false;
          showFieldError(field, "This field is required");
        }
      });

      // Validate email fields
      form.querySelectorAll('[type="email"]').forEach((field) => {
        if (field.value && !isValidEmail(field.value)) {
          isValid = false;
          showFieldError(field, "Please enter a valid email address");
        }
      });

      // Validate phone fields
      form.querySelectorAll("[data-validate-phone]").forEach((field) => {
        if (field.value && !isValidPhone(field.value)) {
          isValid = false;
          showFieldError(field, "Please enter a valid 10-digit phone number");
        }
      });

      // Validate password match
      const password = form.querySelector('[name="password"]');
      const confirmPassword = form.querySelector('[name="confirm_password"]');
      if (
        password &&
        confirmPassword &&
        password.value !== confirmPassword.value
      ) {
        isValid = false;
        showFieldError(confirmPassword, "Passwords do not match");
      }

      // Validate minimum password length
      if (password && password.value && password.value.length < 6) {
        isValid = false;
        showFieldError(password, "Password must be at least 6 characters");
      }

      if (!isValid) {
        e.preventDefault();
      }
    });

    // Real-time validation on input
    form.querySelectorAll("input, select, textarea").forEach((field) => {
      field.addEventListener("blur", function () {
        validateField(this);
      });

      field.addEventListener("input", function () {
        if (this.classList.contains("is-invalid")) {
          validateField(this);
        }
      });
    });
  });
}

function validateField(field) {
  // Remove previous error state
  field.classList.remove("is-invalid");
  const existingFeedback = field.parentNode.querySelector(".invalid-feedback");
  if (existingFeedback) existingFeedback.remove();

  // Required validation
  if (field.required && !field.value.trim()) {
    showFieldError(field, "This field is required");
    return false;
  }

  // Email validation
  if (field.type === "email" && field.value && !isValidEmail(field.value)) {
    showFieldError(field, "Please enter a valid email address");
    return false;
  }

  // Phone validation
  if (
    field.dataset.validatePhone &&
    field.value &&
    !isValidPhone(field.value)
  ) {
    showFieldError(field, "Please enter a valid 10-digit phone number");
    return false;
  }

  return true;
}

function showFieldError(field, message) {
  field.classList.add("is-invalid");
  const feedback = document.createElement("div");
  feedback.className = "invalid-feedback";
  feedback.textContent = message;
  field.parentNode.appendChild(feedback);
}

function isValidEmail(email) {
  return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
}

function isValidPhone(phone) {
  return /^[0-9]{10}$/.test(phone);
}

/**
 * Auto-hide Alerts
 */
function initAlerts() {
  const alerts = document.querySelectorAll(".alert[data-auto-hide]");

  alerts.forEach((alert) => {
    setTimeout(() => {
      alert.style.opacity = "0";
      alert.style.transform = "translateY(-10px)";
      setTimeout(() => {
        alert.remove();
      }, 300);
    }, 5000);
  });
}

/**
 * Delete Confirmation
 */
function initDeleteConfirmation() {
  document.querySelectorAll("[data-confirm-delete]").forEach((btn) => {
    btn.addEventListener("click", function (e) {
      const message =
        this.dataset.confirmDelete ||
        "Are you sure you want to delete this item?";
      if (!confirm(message)) {
        e.preventDefault();
      }
    });
  });
}

/**
 * Dynamic Select Fields (State -> City -> Location)
 */
function initDynamicSelects() {
  const stateSelect = document.querySelector('select[name="state_id"]');
  const citySelect = document.querySelector('select[name="city_id"]');
  const locationSelect = document.querySelector('select[name="location_id"]');

  if (stateSelect && citySelect) {
    stateSelect.addEventListener("change", function () {
      const stateId = this.value;
      citySelect.innerHTML = '<option value="">Select City</option>';
      if (locationSelect) {
        locationSelect.innerHTML = '<option value="">Select Location</option>';
      }

      if (stateId) {
        fetchCities(stateId);
      }
    });
  }

  if (citySelect && locationSelect) {
    citySelect.addEventListener("change", function () {
      const cityId = this.value;
      locationSelect.innerHTML = '<option value="">Select Location</option>';

      if (cityId) {
        fetchLocations(cityId);
      }
    });
  }
}

function fetchCities(stateId) {
  fetch(`/blood_bank/ajax/get_cities.php?state_id=${stateId}`)
    .then((response) => response.json())
    .then((data) => {
      const citySelect = document.querySelector('select[name="city_id"]');
      data.forEach((city) => {
        const option = document.createElement("option");
        option.value = city.id;
        option.textContent = city.city_name;
        citySelect.appendChild(option);
      });
    })
    .catch((error) => console.error("Error fetching cities:", error));
}

function fetchLocations(cityId) {
  fetch(`/blood_bank/ajax/get_locations.php?city_id=${cityId}`)
    .then((response) => response.json())
    .then((data) => {
      const locationSelect = document.querySelector(
        'select[name="location_id"]',
      );
      data.forEach((location) => {
        const option = document.createElement("option");
        option.value = location.id;
        option.textContent = location.location_name;
        locationSelect.appendChild(option);
      });
    })
    .catch((error) => console.error("Error fetching locations:", error));
}

/**
 * Role Selection in Registration
 */
function initRoleSelection() {
  const roleOptions = document.querySelectorAll(".role-option");

  roleOptions.forEach((option) => {
    option.addEventListener("click", function () {
      roleOptions.forEach((opt) => opt.classList.remove("selected"));
      this.classList.add("selected");
      this.querySelector('input[type="radio"]').checked = true;
    });
  });
}

// Initialize role selection if on registration page
if (document.querySelector(".role-option")) {
  initRoleSelection();
}

/**
 * Print Function
 */
function printContent(elementId) {
  const content = document.getElementById(elementId);
  if (content) {
    const printWindow = window.open("", "_blank");
    printWindow.document.write(`
            <!DOCTYPE html>
            <html>
            <head>
                <title>Print</title>
                <link rel="stylesheet" href="/blood_bank/assets/css/style.css">
                <style>
                    body { background: white; padding: 20px; }
                    .no-print { display: none !important; }
                </style>
            </head>
            <body>
                ${content.innerHTML}
            </body>
            </html>
        `);
    printWindow.document.close();
    printWindow.onload = function () {
      printWindow.print();
      printWindow.close();
    };
  }
}

/**
 * Export to CSV
 */
function exportTableToCSV(tableId, filename) {
  const table = document.getElementById(tableId);
  if (!table) return;

  let csv = [];
  const rows = table.querySelectorAll("tr");

  rows.forEach((row) => {
    const cols = row.querySelectorAll("td, th");
    const rowData = [];

    cols.forEach((col) => {
      // Skip action columns
      if (!col.classList.contains("action-btns")) {
        let text = col.innerText.replace(/"/g, '""');
        rowData.push(`"${text}"`);
      }
    });

    csv.push(rowData.join(","));
  });

  const csvContent = csv.join("\n");
  const blob = new Blob([csvContent], { type: "text/csv;charset=utf-8;" });
  const link = document.createElement("a");

  link.href = URL.createObjectURL(blob);
  link.download = filename || "export.csv";
  link.click();
}

/**
 * Toast Notifications
 */
function showToast(message, type = "info") {
  const toast = document.createElement("div");
  toast.className = `alert alert-${type}`;
  toast.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 9999;
        min-width: 300px;
        animation: slideIn 0.3s ease;
    `;

  const icon = {
    success: "fa-check-circle",
    danger: "fa-exclamation-circle",
    warning: "fa-exclamation-triangle",
    info: "fa-info-circle",
  };

  toast.innerHTML = `
        <i class="fas ${icon[type] || icon.info}"></i>
        <span>${message}</span>
    `;

  document.body.appendChild(toast);

  setTimeout(() => {
    toast.style.animation = "slideOut 0.3s ease";
    setTimeout(() => toast.remove(), 300);
  }, 4000);
}

// Add animation keyframes
const style = document.createElement("style");
style.textContent = `
    @keyframes slideIn {
        from { transform: translateX(100%); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }
    @keyframes slideOut {
        from { transform: translateX(0); opacity: 1; }
        to { transform: translateX(100%); opacity: 0; }
    }
`;
document.head.appendChild(style);

/**
 * Quantity Buttons (Blood Stock)
 */
function initQuantityButtons() {
  document.querySelectorAll(".quantity-btn").forEach((btn) => {
    btn.addEventListener("click", function () {
      const input = this.parentNode.querySelector('input[type="number"]');
      const action = this.dataset.action;
      let value = parseInt(input.value) || 0;

      if (action === "increase") {
        value += 50;
      } else if (action === "decrease") {
        value = Math.max(0, value - 50);
      }

      input.value = value;
    });
  });
}

// Initialize quantity buttons if present
if (document.querySelector(".quantity-btn")) {
  initQuantityButtons();
}

/**
 * Search Filter for Tables
 */
function initTableSearch() {
  const searchInput = document.querySelector("[data-table-search]");

  if (searchInput) {
    const tableId = searchInput.dataset.tableSearch;
    const table = document.getElementById(tableId);

    if (table) {
      searchInput.addEventListener("input", function () {
        const searchTerm = this.value.toLowerCase();
        const rows = table.querySelectorAll("tbody tr");

        rows.forEach((row) => {
          const text = row.textContent.toLowerCase();
          row.style.display = text.includes(searchTerm) ? "" : "none";
        });
      });
    }
  }
}

// Initialize table search if present
if (document.querySelector("[data-table-search]")) {
  initTableSearch();
}
