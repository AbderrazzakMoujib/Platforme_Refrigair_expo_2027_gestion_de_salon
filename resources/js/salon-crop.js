import Cropper from 'cropperjs';
import 'cropperjs/dist/cropper.css';

document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('salon-form');
    const input = document.getElementById('cover_image');
    const modal = document.getElementById('crop-modal');

    if (!form || !input || !modal) return;

    const cropImage = document.getElementById('crop-image');
    const preview = document.getElementById('image-preview');
    const wrapper = document.getElementById('image-preview-wrapper');
    const fileName = document.getElementById('image-file-name');
    const removeFlag = document.getElementById('remove_cover_image');
    const removeButton = document.getElementById('remove-image');
    const uploadZone = document.getElementById('upload-zone');

    const applyButton = document.getElementById('crop-apply');

    const initialPreview = preview?.getAttribute('src') || '';
    const initialName = fileName?.textContent?.trim() || '';

    let cropper = null;
    let sourceUrl = null;
    let previewUrl = null;
    let confirmedFile = null;
    let pendingFile = null;

    function disposeSource() {
        if (cropper) {
            cropper.destroy();
            cropper = null;
        }

        if (sourceUrl) {
            URL.revokeObjectURL(sourceUrl);
            sourceUrl = null;
        }
    }

    function restoreInput() {
        const transfer = new DataTransfer();

        if (confirmedFile) {
            transfer.items.add(confirmedFile);
        }

        input.files = transfer.files;
    }

    function closeModal() {
        disposeSource();

        modal.classList.add('hidden');
        modal.classList.remove('flex');

        document.body.style.overflow = '';
        pendingFile = null;
    }

    function cancelCrop() {
        closeModal();
        restoreInput();
    }

    function showPreview(file) {
        if (previewUrl) {
            URL.revokeObjectURL(previewUrl);
        }

        previewUrl = URL.createObjectURL(file);

        preview.src = previewUrl;
        fileName.textContent = file.name;
        wrapper.classList.remove('hidden');

        removeFlag.value = '0';
    }

    function openCrop(file) {
        if (!file) return;

        const allowed = [
            'image/jpeg',
            'image/png',
            'image/webp',
        ];

        if (!allowed.includes(file.type)) {
            alert('Format accepté : JPG, PNG ou WebP.');
            restoreInput();
            return;
        }

        if (file.size > 5 * 1024 * 1024) {
            alert('Image trop volumineuse. Maximum : 5 Mo.');
            restoreInput();
            return;
        }

        pendingFile = file;

        disposeSource();
        sourceUrl = URL.createObjectURL(file);

        cropImage.src = sourceUrl;

        modal.classList.remove('hidden');
        modal.classList.add('flex');

        document.body.style.overflow = 'hidden';

        cropImage.onload = () => {
            if (!pendingFile) return;

            if (cropper) {
                cropper.destroy();
            }

            cropper = new Cropper(cropImage, {
                viewMode: 1,

                // Cadrage libre
                aspectRatio: NaN,

                dragMode: 'crop',
                autoCrop: true,
                autoCropArea: 0.7,

                cropBoxMovable: true,
                cropBoxResizable: true,

                movable: true,
                zoomable: true,
                zoomOnWheel: true,
                zoomOnTouch: true,

                scalable: false,
                rotatable: false,

                background: false,
                responsive: true,
            });
        };
    }

    input.addEventListener('change', () => {
        const file = input.files?.[0];

        if (file) openCrop(file);
    });

    document.getElementById('crop-close')
        .addEventListener('click', cancelCrop);

    document.getElementById('crop-cancel')
        .addEventListener('click', cancelCrop);

    document.getElementById('crop-zoom-in')
        .addEventListener('click', () => {
            cropper?.zoom(0.1);
        });

    document.getElementById('crop-zoom-out')
        .addEventListener('click', () => {
            cropper?.zoom(-0.1);
        });

    document.getElementById('crop-reset')
        .addEventListener('click', () => {
            cropper?.reset();
        });

    applyButton.addEventListener('click', () => {
        if (!cropper || !pendingFile) return;

        const canvas = cropper.getCroppedCanvas({
            maxWidth: 4096,
            maxHeight: 4096,
            imageSmoothingEnabled: true,
            imageSmoothingQuality: 'high',
        });

        if (!canvas) {
            alert('Impossible de recadrer cette image.');
            return;
        }

        applyButton.disabled = true;
        applyButton.textContent = 'Traitement...';

        canvas.toBlob(blob => {
            applyButton.disabled = false;
            applyButton.textContent = 'Valider le cadrage';

            if (!blob) {
                alert('Erreur lors du traitement.');
                return;
            }

            if (blob.size > 5 * 1024 * 1024) {
                alert('Image recadrée trop volumineuse (max 5 Mo).');
                return;
            }

            const file = new File(
                [blob],
                'salon-cropped-' + Date.now() + '.jpg',
                { type: 'image/jpeg' }
            );

            const transfer = new DataTransfer();
            transfer.items.add(file);

            confirmedFile = file;
            input.files = transfer.files;

            showPreview(file);
            closeModal();

        }, 'image/jpeg', 0.9);
    });

    // Supprimer l'image
    removeButton?.addEventListener('click', () => {
        confirmedFile = null;
        input.value = '';

        if (previewUrl) {
            URL.revokeObjectURL(previewUrl);
            previewUrl = null;
        }

        preview.removeAttribute('src');
        fileName.textContent = '';
        wrapper.classList.add('hidden');

        removeFlag.value = '1';
    });

    // Drag & drop
    ['dragenter', 'dragover'].forEach(name => {
        uploadZone.addEventListener(name, event => {
            event.preventDefault();
            uploadZone.classList.add(
                'border-blue-600',
                'bg-blue-100'
            );
        });
    });

    ['dragleave', 'drop'].forEach(name => {
        uploadZone.addEventListener(name, event => {
            event.preventDefault();
            uploadZone.classList.remove(
                'border-blue-600',
                'bg-blue-100'
            );
        });
    });

    uploadZone.addEventListener('drop', event => {
        const file = event.dataTransfer?.files?.[0];
        if (!file) return;

        openCrop(file);
    });

    // Reset form
    form.addEventListener('reset', () => {
        cancelCrop();

        confirmedFile = null;

        if (previewUrl) {
            URL.revokeObjectURL(previewUrl);
            previewUrl = null;
        }

        window.setTimeout(() => {
            if (initialPreview) {
                preview.src = initialPreview;
                fileName.textContent = initialName;
                wrapper.classList.remove('hidden');
            } else {
                preview.removeAttribute('src');
                fileName.textContent = '';
                wrapper.classList.add('hidden');
            }

            input.value = '';
            removeFlag.value = '0';
        }, 0);
    });

    // Prevent unvalidated original upload
    form.addEventListener('submit', event => {
        if (!modal.classList.contains('hidden')) {
            event.preventDefault();
            alert('Validez d’abord le cadrage de l’image.');
        }
    });

    window.addEventListener('beforeunload', () => {
        disposeSource();

        if (previewUrl) {
            URL.revokeObjectURL(previewUrl);
        }
    });
});
