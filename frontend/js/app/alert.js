export function showAlert(message, type = "success") {
    let alertSection = document.getElementById('alert_section');
    if (!alertSection) {
        alertSection = document.createElement('section');
        alertSection.id = 'alert_section';
        alertSection.classList.add('alert_section', 'hide_ele');
        document.body.appendChild(alertSection);
    }
    alertSection.innerHTML = `
        <div id="action_alert" class="alert alert-${type} fw-bold shadow mb-0 text-center" role="alert">
            ${message}
        </div>
    `;
    alertSection.classList.remove('hide_ele');
    alertSection.classList.add('show_ele');
    window.scrollTo({ top: 0, behavior: 'smooth' });
    setTimeout(() => {
        alertSection.classList.remove('show_ele');
        alertSection.classList.add('hide_ele');
    }, 2000)
}

export function showConfirm(message, onConfirm) {
    document.getElementById('confirmModalMessage').textContent = message;

    const modal = new bootstrap.Modal(document.getElementById('confirmModal'));
    modal.show();

    const okBtn = document.getElementById('confirmModalOkBtn');
    okBtn.onclick = null;
    okBtn.onclick = function() {
        modal.hide();
        if (typeof onConfirm === 'function') onConfirm();
    };
}