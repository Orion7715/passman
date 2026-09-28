function toggleImportModal() {
    const modal = document.getElementById('importModal');
    if (modal.classList.contains('hidden')) {
        modal.classList.remove('hidden');
    } else {
        modal.classList.add('hidden');
    }
}

// وظيفة لتحديد نوع الاستيراد وفتح نافذة اختيار الملف
function triggerFileSelect(type) {
    document.getElementById('importType').value = type;
    document.getElementById('csvFileInput').click();
}

// وظيفة لإرسال الفورم تلقائياً بعد اختيار الملف
function submitImport() {
    const fileInput = document.getElementById('csvFileInput');
    if (fileInput.files.length > 0) {
        document.getElementById('importForm').submit();
    }
}

// إغلاق النافذة عند الضغط خارجها
window.addEventListener('click', function(event) {
    const modal = document.getElementById('importModal');
    if (event.target === modal) {
        toggleImportModal();
    }
});
