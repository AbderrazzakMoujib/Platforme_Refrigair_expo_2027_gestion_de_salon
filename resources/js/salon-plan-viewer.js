import * as pdfjsLib from 'pdfjs-dist';
import pdfWorkerUrl from 'pdfjs-dist/build/pdf.worker.min.mjs?url';

pdfjsLib.GlobalWorkerOptions.workerSrc = pdfWorkerUrl;

const initializeViewer = (root) => {
    const stage = root.querySelector('[data-pdf-stage]');
    const canvas = root.querySelector('[data-pdf-canvas]');
    const loading = root.querySelector('[data-pdf-loading]');
    const error = root.querySelector('[data-pdf-error]');
    const percentage = root.querySelector('[data-pdf-zoom-value]');
    const pageCount = root.querySelector('[data-pdf-page-count]');
    const buttons = [...root.querySelectorAll('[data-pdf-action]')];

    if (!stage || !canvas || !root.dataset.pdfUrl) return;

    const context = canvas.getContext('2d', { alpha: false });
    const outputScale = Math.min(window.devicePixelRatio || 1, 2);
    let pdfPage;
    let currentScale = 1;
    let fitMode = 'width';
    let renderTask;
    let renderNumber = 0;

    const setStatus = (message) => {
        if (loading) loading.textContent = message;
    };

    const setZoomLabel = () => {
        if (percentage) percentage.textContent = `${Math.round(currentScale * 100)}%`;
    };

    const fitScale = (mode) => {
        if (!pdfPage) return currentScale;

        const base = pdfPage.getViewport({ scale: 1 });
        const padding = 24;
        const widthScale = Math.max(0.1, (stage.clientWidth - padding) / base.width);

        if (mode === 'width') return widthScale;

        const availableHeight = Math.max(0, stage.clientHeight - padding);
        return Math.min(widthScale, Math.max(0.1, availableHeight / base.height));
    };

    const render = async () => {
        if (!pdfPage || !context) return;

        const thisRender = ++renderNumber;
        renderTask?.cancel();
        currentScale = fitMode === 'custom' ? currentScale : fitScale(fitMode);
        setZoomLabel();

        const viewport = pdfPage.getViewport({ scale: currentScale });
        canvas.width = Math.ceil(viewport.width * outputScale);
        canvas.height = Math.ceil(viewport.height * outputScale);
        canvas.style.width = `${Math.ceil(viewport.width)}px`;
        canvas.style.height = `${Math.ceil(viewport.height)}px`;

        const task = pdfPage.render({
            canvasContext: context,
            viewport,
            transform: outputScale === 1 ? null : [outputScale, 0, 0, outputScale, 0, 0],
            background: '#ffffff',
        });
        renderTask = task;

        try {
            await task.promise;
            if (thisRender === renderNumber) {
                canvas.hidden = false;
                if (loading) loading.hidden = true;
            }
        } catch (renderError) {
            if (renderError?.name !== 'RenderingCancelledException') throw renderError;
        }
    };

    const showError = (message) => {
        if (loading) loading.hidden = true;
        if (error) {
            error.hidden = false;
            error.textContent = message;
        }
        canvas.hidden = true;
    };

    const load = async () => {
        try {
            setStatus('Chargement du plan haute résolution…');
            const document = await pdfjsLib.getDocument({
                url: root.dataset.pdfUrl,
                withCredentials: true,
                isEvalSupported: false,
            }).promise;

            pdfPage = await document.getPage(1);
            if (pageCount) pageCount.textContent = `Page 1 / ${document.numPages}`;
            await render();
            buttons.forEach((button) => { button.disabled = false; });
        } catch (loadError) {
            console.error('Échec du chargement du plan PDF.', loadError?.name || 'PDFError');
            showError('Le plan ne peut pas être affiché pour le moment. Utilisez le bouton de téléchargement pour ouvrir le PDF original.');
        }
    };

    root.addEventListener('click', async (event) => {
        const button = event.target.closest('[data-pdf-action]');
        if (!button || button.disabled) return;

        const action = button.dataset.pdfAction;
        if (action === 'zoom-in' || action === 'zoom-out') {
            fitMode = 'custom';
            currentScale = Math.max(0.15, Math.min(5, currentScale * (action === 'zoom-in' ? 1.2 : 1 / 1.2)));
            await render();
        } else if (action === 'fit-width' || action === 'fit-page') {
            fitMode = action === 'fit-width' ? 'width' : 'page';
            await render();
            stage.scrollTo({ top: 0, left: 0, behavior: 'smooth' });
        } else if (action === 'fullscreen') {
            try {
                if (document.fullscreenElement === root) await document.exitFullscreen();
                else await root.requestFullscreen();
                window.setTimeout(() => { if (fitMode !== 'custom') render(); }, 80);
            } catch {
                setStatus('Le mode plein écran n’est pas disponible dans ce navigateur.');
            }
        }
    });

    let dragStart;
    stage.addEventListener('pointerdown', (event) => {
        if (event.pointerType !== 'mouse' || event.button !== 0 || event.target !== canvas) return;
        dragStart = { x: event.clientX, y: event.clientY, left: stage.scrollLeft, top: stage.scrollTop };
        stage.setPointerCapture(event.pointerId);
        stage.classList.add('cursor-grabbing');
    });
    stage.addEventListener('pointermove', (event) => {
        if (!dragStart) return;
        stage.scrollLeft = dragStart.left - (event.clientX - dragStart.x);
        stage.scrollTop = dragStart.top - (event.clientY - dragStart.y);
    });
    const stopDragging = () => {
        dragStart = undefined;
        stage.classList.remove('cursor-grabbing');
    };
    stage.addEventListener('pointerup', stopDragging);
    stage.addEventListener('pointercancel', stopDragging);
    stage.addEventListener('lostpointercapture', stopDragging);

    const resizeObserver = new ResizeObserver(() => {
        if (fitMode !== 'custom' && pdfPage) render();
    });
    resizeObserver.observe(stage);

    load();
};

document.querySelectorAll('[data-pdf-viewer]').forEach(initializeViewer);
