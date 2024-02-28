const toggle = document.getElementById("edit-field-is-decorative-value");
const imageAlt = document.getElementById("edit-field-media-image-0-alt");
const imageAltField = document.querySelector(
  ".form-item--field-media-image-0-alt"
);

const updateAltFieldState = (isChecked) => {
  imageAltField.style.display = isChecked ? "none" : "block";
  imageAlt.toggleAttribute("required", !isChecked);
  imageAlt.toggleAttribute("aria-required", !isChecked);
};

// Set initial states based on the toggle's initial state
updateAltFieldState(toggle.checked);

toggle.addEventListener("change", () => {
  updateAltFieldState(toggle.checked);
});
