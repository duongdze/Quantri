// Tour Image Upload Handler
document.addEventListener('DOMContentLoaded', function () {
    const dropZone = document.getElementById('image-drop-zone');
    const fileInput = document.getElementById('file-input-handler');
    const previewContainer = document.getElementById('image-preview-container');
    const galleryImagesInput = document.getElementById('gallery-images-input');

    const modal = document.getElementById('image-viewer-modal');
    const modalImg = document.getElementById('modal-image');
    const closeModal = document.querySelector('.close-viewer');

    let selectedFiles = [];

    // --- Event Listeners ---
    dropZone.addEventListener('click', () => fileInput.click());
    dropZone.addEventListener('dragover', (e) => {
        e.preventDefault();
        dropZone.classList.add('border-primary');
    });
    dropZone.addEventListener('dragleave', (e) => {
        e.preventDefault();
        dropZone.classList.remove('border-primary');
    });
    dropZone.addEventListener('drop', (e) => {
        e.preventDefault();
        dropZone.classList.remove('border-primary');
        const files = Array.from(e.dataTransfer.files).filter(file => file.type.startsWith('image/'));
        handleFiles(files);
    });
    fileInput.addEventListener('change', (e) => {
        const files = Array.from(e.target.files).filter(file => file.type.startsWith('image/'));
        handleFiles(files);
        fileInput.value = '';
    });
    closeModal.addEventListener('click', () => modal.style.display = "none");
    modal.addEventListener('click', (e) => {
        if (e.target === modal) {
            modal.style.display = "none";
        }
    });

    // --- File Handling Functions ---
    function handleFiles(files) {
        const newFiles = files.slice(0, 10 - selectedFiles.length);
        selectedFiles = [...selectedFiles, ...newFiles];
        updatePreviews();
        updateFileInputs();
    }

    function updatePreviews() {
        previewContainer.innerHTML = '';
        selectedFiles.forEach((file, index) => {
            const reader = new FileReader();
            reader.onload = (e) => {
                const imgSrc = e.target.result;
                const previewWrapper = document.createElement('div');
                previewWrapper.className = 'col-6 col-md-4 col-lg-3';

                const card = document.createElement('div');
                card.className = 'card h-100 image-preview-card';

                const img = document.createElement('img');
                img.src = imgSrc;
                img.className = 'card-img-top object-fit-cover';
                img.style.height = '120px';

                // --- Actions Overlay ---
                const overlay = document.createElement('div');
                overlay.className = 'actions-overlay';

                // View button
                const viewBtn = document.createElement('i');
                viewBtn.className = 'fas fa-eye action-btn';
                viewBtn.title = 'Xem ảnh';
                viewBtn.onclick = () => {
                    modalImg.src = imgSrc;
                    modal.style.display = "block";
                };
                overlay.appendChild(viewBtn);

                // Set as primary button (only for non-primary images)
                if (index > 0) {
                    const primaryBtn = document.createElement('i');
                    primaryBtn.className = 'fas fa-star action-btn';
                    primaryBtn.title = 'Chọn làm ảnh đại diện';
                    primaryBtn.onclick = () => setAsPrimary(index);
                    overlay.appendChild(primaryBtn);
                }

                // Delete button
                const removeBtn = document.createElement('i');
                removeBtn.className = 'fas fa-trash-alt action-btn text-danger';
                removeBtn.title = 'Xóa ảnh';
                removeBtn.onclick = () => removeFile(index);
                overlay.appendChild(removeBtn);

                card.appendChild(img);
                card.appendChild(overlay);

                // Add primary image badge
                if (index === 0) {
                    const badge = document.createElement('span');
                    badge.className = 'badge bg-primary position-absolute top-0 start-0 m-1';
                    badge.textContent = 'Ảnh đại diện';
                    card.appendChild(badge);
                }

                previewWrapper.appendChild(card);
                previewContainer.appendChild(previewWrapper);
            };
            reader.readAsDataURL(file);
        });
    }

    function removeFile(indexToRemove) {
        selectedFiles.splice(indexToRemove, 1);
        updatePreviews();
        updateFileInputs();
    }

    function setAsPrimary(indexToMakePrimary) {
        if (indexToMakePrimary > 0 && indexToMakePrimary < selectedFiles.length) {
            const item = selectedFiles.splice(indexToMakePrimary, 1)[0];
            selectedFiles.unshift(item);
            updatePreviews();
            updateFileInputs();
        }
    }

    function updateFileInputs() {
        const dataTransfer = new DataTransfer();

        // Add all selected files to the input (first file will be main image)
        selectedFiles.forEach(file => {
            dataTransfer.items.add(file);
        });

        // Update the actual form input
        galleryImagesInput.files = dataTransfer.files;
    }
});
