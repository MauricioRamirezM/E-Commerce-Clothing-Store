export function validateFormFields(form) {
    if (!form) return false;
    let isFormValid = true;
    const requiredInputs = form.querySelectorAll('input[required], textarea[required], select[required]');
    requiredInputs.forEach(input => {
        let isValid = true;
        if (!input.value.trim()) {
            isValid = false;
            input.classList.add('is-invalid');
            input.setCustomValidity('This field is required');
        } else if (input.hasAttribute('pattern')) {
            const pattern = input.getAttribute('pattern');
            const regex = new RegExp(pattern);
            if (!regex.test(input.value)) {
                isValid = false;
                input.classList.add('is-invalid');
                input.setCustomValidity(input.title || 'Invalid input');
            } else {
                input.classList.remove('is-invalid');
                input.setCustomValidity('');
            }
        } else {
            input.classList.remove('is-invalid');
            input.setCustomValidity('');
        }
        if (!isValid) isFormValid = false;
    });
    return isFormValid;
}
export function showAlert(bool, text) {
    const section_alert = document.getElementById('alert_section');
    const action_alert = document.getElementById('action_alert');
    action_alert.textContent = text;
    action_alert.classList.remove('alert-success', 'alert-danger');
    if (bool === true) {
        action_alert.classList.add('alert-success');
    } else {
        action_alert.classList.add('alert-danger');
    }
    section_alert.classList.remove('hide_ele');
    section_alert.classList.add('show_ele');
    setTimeout(() => {
        section_alert.classList.remove('show_ele');
        section_alert.classList.add('hide_ele');
    }, 3000)

}

