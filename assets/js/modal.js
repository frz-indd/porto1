// Modal System
class Modal {
    constructor(id, title, message, type = 'info', buttons = []) {
        this.id = id;
        this.title = title;
        this.message = message;
        this.type = type; // 'info', 'success', 'danger', 'warning', 'confirm'
        this.buttons = buttons;
        this.element = null;
    }

    create() {
        const modal = document.createElement('div');
        modal.id = this.id;
        modal.className = 'modal';
        
        let buttonsHTML = '';
        if (this.buttons.length === 0) {
            // Default OK button
            buttonsHTML = `<button type="button" class="btn btn-primary" onclick="closeModal('${this.id}')">OK</button>`;
        } else {
            buttonsHTML = this.buttons.map(btn => 
                `<button type="button" class="btn btn-${btn.type || 'primary'}" onclick="${btn.onclick}">${btn.text}</button>`
            ).join(' ');
        }

        let iconClass = {
            'success': '✓',
            'danger': '!',
            'warning': '!',
            'confirm': '?',
            'info': 'i'
        }[this.type] || 'i';

        modal.innerHTML = `
            <div class="modal-content modal-${this.type}">
                <div class="modal-header">
                    <div class="modal-icon">${iconClass}</div>
                    <h2 class="modal-title">${this.title}</h2>
                    <button type="button" class="modal-close" onclick="closeModal('${this.id}')">&times;</button>
                </div>
                <div class="modal-body">
                    ${this.message}
                </div>
                <div class="modal-footer">
                    ${buttonsHTML}
                </div>
            </div>
        `;

        document.body.appendChild(modal);
        this.element = modal;
        return modal;
    }

    show() {
        if (!this.element) {
            this.create();
        }
        this.element.classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    hide() {
        if (this.element) {
            this.element.classList.remove('active');
            document.body.style.overflow = 'auto';
        }
    }

    remove() {
        if (this.element) {
            this.element.remove();
            this.element = null;
        }
        document.body.style.overflow = 'auto';
    }
}

// Global modal functions
function showModal(title, message, type = 'info', buttons = []) {
    const modalId = 'modal-' + Date.now();
    const modal = new Modal(modalId, title, message, type, buttons);
    modal.show();
    return modalId;
}

function showSuccessModal(title, message, buttons = []) {
    if (buttons.length === 0) {
        buttons = [{ text: 'OK', type: 'primary', onclick: `closeModal()` }];
    }
    return showModal(title, message, 'success', buttons);
}

function showDangerModal(title, message, buttons = []) {
    if (buttons.length === 0) {
        buttons = [{ text: 'OK', type: 'danger', onclick: `closeModal()` }];
    }
    return showModal(title, message, 'danger', buttons);
}

function showWarningModal(title, message, buttons = []) {
    if (buttons.length === 0) {
        buttons = [{ text: 'OK', type: 'primary', onclick: `closeModal()` }];
    }
    return showModal(title, message, 'warning', buttons);
}

function showConfirmModal(title, message, onConfirm, onCancel = null) {
    const modalId = showModal(title, message, 'confirm', [
        { 
            text: 'Ya, Lanjutkan', 
            type: 'primary', 
            onclick: onConfirm 
        },
        { 
            text: 'Batal', 
            type: 'secondary', 
            onclick: `closeModal('${modalId}'); ${onCancel || ''}` 
        }
    ]);
    return modalId;
}

function closeModal(modalId = null) {
    if (modalId) {
        const modal = document.getElementById(modalId);
        if (modal && modal.classList.contains('active')) {
            modal.classList.remove('active');
            document.body.style.overflow = 'auto';
            setTimeout(() => modal.remove(), 300);
        }
    } else {
        // Close last active modal
        const modals = document.querySelectorAll('.modal.active');
        if (modals.length > 0) {
            const lastModal = modals[modals.length - 1];
            lastModal.classList.remove('active');
            document.body.style.overflow = 'auto';
            setTimeout(() => lastModal.remove(), 300);
        }
    }
}

// Close modal on backdrop click
document.addEventListener('click', function(event) {
    if (event.target.classList.contains('modal') && event.target.classList.contains('active')) {
        closeModal(event.target.id);
    }
});

// Close with Escape key
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        closeModal();
    }
});
