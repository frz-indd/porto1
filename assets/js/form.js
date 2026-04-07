// Form handler dengan file upload untuk pengaduan
const fileUploadArea = document.getElementById('fileUploadArea');
const buktiInput = document.getElementById('bukti');
const fileList = document.getElementById('fileList');
const buktiFields = document.getElementById('buktiFields');
let selectedFiles = [];

// Initialize file upload
if (fileUploadArea && buktiInput) {
    // Click to upload
    fileUploadArea.addEventListener('click', () => buktiInput.click());

    // Drag and drop
    fileUploadArea.addEventListener('dragover', (e) => {
        e.preventDefault();
        fileUploadArea.style.borderColor = '#764ba2';
        fileUploadArea.style.background = '#f0f5ff';
    });

    fileUploadArea.addEventListener('dragleave', () => {
        fileUploadArea.style.borderColor = '#667eea';
        fileUploadArea.style.background = '';
    });

    fileUploadArea.addEventListener('drop', (e) => {
        e.preventDefault();
        fileUploadArea.style.borderColor = '#667eea';
        fileUploadArea.style.background = '';
        handleFiles(e.dataTransfer.files);
    });

    buktiInput.addEventListener('change', (e) => {
        handleFiles(e.target.files);
    });
}

function handleFiles(files) {
    selectedFiles = Array.from(files);
    updateFileList();
    updateBuktiFields();
}

function updateFileList() {
    fileList.innerHTML = '';
    selectedFiles.forEach((file, index) => {
        const fileItem = document.createElement('div');
        fileItem.className = 'file-item';
        
        // Validate file
        const maxSize = 5 * 1024 * 1024; // 5MB
        const allowedExt = ['jpg', 'jpeg', 'png', 'pdf', 'doc', 'docx', 'xls', 'xlsx'];
        const fileExt = file.name.split('.').pop().toLowerCase();
        const isValid = file.size <= maxSize && allowedExt.includes(fileExt);
        
        const errorClass = !isValid ? 'error' : '';
        const errorMsg = file.size > maxSize ? ' (File terlalu besar)' : 
                        !allowedExt.includes(fileExt) ? ' (Format tidak diizinkan)' : '';
        
        fileItem.innerHTML = `
            <span class="${errorClass}">📄 ${file.name} (${(file.size / 1024).toFixed(2)} KB)${errorMsg}</span>
            <button type="button" class="btn btn-danger btn-small" onclick="removeFile(${index})">Hapus</button>
        `;
        fileList.appendChild(fileItem);
    });
}

function removeFile(index) {
    selectedFiles.splice(index, 1);
    updateFileList();
    updateBuktiFields();
}

function updateBuktiFields() {
    buktiFields.innerHTML = '';
    selectedFiles.forEach((file, index) => {
        const field = document.createElement('div');
        field.style.marginBottom = '1rem';
        field.innerHTML = `
            <label>Keterangan untuk ${file.name}</label>
            <input type="text" name="keterangan_bukti[${index}]" 
                   placeholder="Contoh: Foto bukti kerusakan" 
                   style="width: 100%; padding: 0.75rem; border: 1px solid #ddd; border-radius: 4px;">
        `;
        buktiFields.appendChild(field);
    });
}

// Handle form submission dengan modal
const form = document.querySelector('form');
if (form) {
    form.addEventListener('submit', function(e) {
        // Validate files
        const maxSize = 5 * 1024 * 1024;
        const allowedExt = ['jpg', 'jpeg', 'png', 'pdf', 'doc', 'docx', 'xls', 'xlsx'];
        
        for (let file of selectedFiles) {
            const fileExt = file.name.split('.').pop().toLowerCase();
            
            if (file.size > maxSize) {
                e.preventDefault();
                showDangerModal('File Terlalu Besar', 
                    `File "${file.name}" melebihi ukuran maksimal 5MB.`);
                return false;
            }
            
            if (!allowedExt.includes(fileExt)) {
                e.preventDefault();
                showDangerModal('Format File Tidak Diizinkan', 
                    `File "${file.name}" memiliki format yang tidak diizinkan.<br><br>Format yang diizinkan: JPG, PNG, PDF, DOC, DOCX, XLS, XLSX`);
                return false;
            }
        }
    });
}

// Show success message if redirected after submission
window.addEventListener('load', function() {
    const params = new URLSearchParams(window.location.search);
    if (params.get('success') === '1') {
        showSuccessModal('Pengaduan Berhasil Dikirim', 
            'Nomor laporan Anda telah disimpan di database.');
    }
});
