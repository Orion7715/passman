function toggleExportModal() {
    const modal = document.getElementById('exportModal');
    if (modal.classList.contains('hidden')) {
        modal.classList.remove('hidden');
        // إضافة تأثير بسيط عند الفتح
        modal.style.opacity = "0";
        setTimeout(() => { modal.style.opacity = "1"; }, 10);
    } else {
        modal.classList.add('hidden');
    }
}

// إغلاق النافذة عند الضغط خارجها
window.onclick = function(event) {
    const modal = document.getElementById('exportModal');
    if (event.target == modal) {
        toggleExportModal();
    }
}
