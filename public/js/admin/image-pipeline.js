/**
 * ATELIER — Smart Image Pipeline
 * ═══════════════════════════════════════════════════════════════════
 * Pure client-side image processing pipeline:
 *   Part 1: Universal capture (paste + drag + browse)
 *   Part 2: Auto-crop with Canvas bounding-box detection
 *   Part 3: Background whitening + edge feathering
 *   Part 4: Dominant color extraction → auto-fill hex
 *   Part 5: Smart file naming + alt text
 *   Part 6: pHash near-duplicate detection (within + cross-catalog)
 *   Part 7: Batch multi-color quick-add
 *
 * No paid APIs. No external libraries. Canvas API only.
 * ═══════════════════════════════════════════════════════════════════
 */

const AtelierImagePipeline = (() => {

    // ── CONFIG ──────────────────────────────────────────────────────
    const CFG = {
        autocrop: {
            bgThreshold: 30,      // color distance to count as background
            minSubjectRatio: 0.20, // skip crop if subject < 20% of image
            padding: 0.07,        // 7% padding around subject
        },
        whitening: {
            defaultTolerance: 32,
            featherRadius: 1,
        },
        colorExtract: {
            sampleStep: 8,       // sample every Nth pixel
            whiteThreshold: 230, // skip pixels brighter than this
            minConfidence: 0.60, // 60% of non-bg pixels must agree
        },
        quality: {
            minShortSide: 600,   // warn if shorter side < this
            maxAspectDeviation: 2.0, // warn if aspect > 2x typical
        },
        pHash: {
            size: 8,             // 8×8 DCT hash → 64-bit
        },
    };

    // ── STATE ────────────────────────────────────────────────────────
    let activeZoneId = null;           // last-focused drop zone
    let pipelineQueue = [];            // images pending review
    let batchRows = [];                // batch add rows
    let productSlug = '';
    let basePrice = 0;

    // ── PART 1: UNIVERSAL CAPTURE ────────────────────────────────────

    function init(slug, price) {
        productSlug = slug || '';
        basePrice = price || 0;
        _bindGlobalPaste();
        _bindAllDropZones();
        _bindBatchZone();
    }

    function _bindGlobalPaste() {
        document.addEventListener('paste', (e) => {
            const items = e.clipboardData?.items;
            if (!items) return;
            const files = [];
            for (const item of items) {
                if (item.kind === 'file' && item.type.startsWith('image/')) {
                    const f = item.getAsFile();
                    if (f) files.push(f);
                }
            }
            if (!files.length) return;
            e.preventDefault();
            const zoneId = activeZoneId || _getFirstZoneId();
            if (zoneId) capture(files, zoneId);
        });
    }

    function _bindAllDropZones() {
        // Existing zones: .smart-dropzone
        document.querySelectorAll('.smart-dropzone').forEach(zone => {
            zone.addEventListener('focusin', () => activeZoneId = zone.id);
            zone.addEventListener('mouseenter', () => activeZoneId = zone.id);
            zone.addEventListener('dragover', (e) => {
                e.preventDefault();
                activeZoneId = zone.id;
                zone.classList.add('border-black', 'bg-gray-50');
            });
            zone.addEventListener('dragleave', () => {
                zone.classList.remove('border-black', 'bg-gray-50');
            });
            zone.addEventListener('drop', (e) => {
                e.preventDefault();
                zone.classList.remove('border-black', 'bg-gray-50');
                const files = Array.from(e.dataTransfer?.files || [])
                    .filter(f => f.type.startsWith('image/'));
                if (files.length) capture(files, zone.id);
            });
        });
    }

    function _bindBatchZone() {
        const bz = document.getElementById('atl-batch-dropzone');
        if (!bz) return;
        bz.addEventListener('dragover', (e) => { e.preventDefault(); bz.classList.add('border-black','bg-gray-50'); });
        bz.addEventListener('dragleave', () => bz.classList.remove('border-black','bg-gray-50'));
        bz.addEventListener('drop', (e) => {
            e.preventDefault();
            bz.classList.remove('border-black','bg-gray-50');
            const files = Array.from(e.dataTransfer?.files || []).filter(f => f.type.startsWith('image/'));
            if (files.length) captureBatch(files);
        });
        bz.addEventListener('paste', (e) => {
            const files = [];
            for (const item of (e.clipboardData?.items || [])) {
                if (item.kind === 'file' && item.type.startsWith('image/')) files.push(item.getAsFile());
            }
            if (files.length) { e.preventDefault(); captureBatch(files); }
        });
    }

    function _getFirstZoneId() {
        return document.querySelector('.smart-dropzone')?.id || null;
    }

    // ── PUBLIC ENTRY POINTS ──────────────────────────────────────────

    async function capture(files, zoneId) {
        for (const file of files) {
            await _processSingle(file, zoneId);
        }
    }

    async function captureBatch(files) {
        const rows = [];
        for (const file of files) {
            const result = await _runPipeline(file);
            rows.push(result);
        }
        _showBatchReview(rows);
    }

    // ── PART 2+3+4+5: FULL PIPELINE FOR ONE IMAGE ───────────────────

    async function _processSingle(file, zoneId) {
        const result = await _runPipeline(file);
        _showSingleReview(result, zoneId);
    }

    async function _runPipeline(file) {
        const result = {
            file,
            originalDataUrl: null,
            croppedDataUrl: null,
            whiteDataUrl: null,
            finalDataUrl: null,
            cropBox: null,
            autoCropConfident: true,
            bgConfident: true,
            dominantHex: null,
            suggestedName: null,
            altText: null,
            warnings: [],
            phash: null,
        };

        // Load image
        const img = await _loadImage(file);
        const w = img.naturalWidth, h = img.naturalHeight;

        // Quality checks (Part 6)
        _qualityCheck(w, h, result);

        // Draw to canvas
        const canvas = document.createElement('canvas');
        canvas.width = w; canvas.height = h;
        const ctx = canvas.getContext('2d', { willReadFrequently: true });
        ctx.drawImage(img, 0, 0);
        result.originalDataUrl = canvas.toDataURL('image/jpeg', 0.92);

        // Part 2: Auto-crop
        const bgColor = _detectBackgroundColor(ctx, w, h);
        const box = _findBoundingBox(ctx, w, h, bgColor);
        if (box) {
            const subjectRatio = (box.w * box.h) / (w * h);
            if (subjectRatio < CFG.autocrop.minSubjectRatio) {
                result.autoCropConfident = false;
                result.warnings.push('Auto-crop skipped — subject region too small or background unclear.');
                result.croppedDataUrl = result.originalDataUrl;
            } else {
                const padX = Math.floor(box.w * CFG.autocrop.padding);
                const padY = Math.floor(box.h * CFG.autocrop.padding);
                result.cropBox = {
                    x: Math.max(0, box.x - padX),
                    y: Math.max(0, box.y - padY),
                    w: Math.min(w - Math.max(0, box.x - padX), box.w + padX * 2),
                    h: Math.min(h - Math.max(0, box.y - padY), box.h + padY * 2),
                };
                result.croppedDataUrl = _cropCanvas(ctx, result.cropBox);
            }
        } else {
            result.autoCropConfident = false;
            result.croppedDataUrl = result.originalDataUrl;
            result.warnings.push('Could not auto-crop confidently — background not uniform.');
        }

        // Part 3: Background whitening
        const cropCtx = await _canvasFromDataUrl(result.croppedDataUrl);
        const whitened = _whitenBackground(cropCtx.ctx, cropCtx.w, cropCtx.h, bgColor, CFG.whitening.defaultTolerance);
        if (whitened) {
            result.whiteDataUrl = whitened;
            result.finalDataUrl = whitened;
        } else {
            result.bgConfident = false;
            result.whiteDataUrl = result.croppedDataUrl;
            result.finalDataUrl = result.croppedDataUrl;
            result.warnings.push('Background not uniform enough for whitening — showing original.');
        }

        // Part 4: Dominant color extraction
        const colorCtx = await _canvasFromDataUrl(result.finalDataUrl);
        result.dominantHex = _extractDominantColor(colorCtx.ctx, colorCtx.w, colorCtx.h);

        // Part 5: Smart naming
        const ext = file.type === 'image/png' ? 'png' : 'jpg';
        const colorSlug = result.dominantHex ? result.dominantHex.replace('#','').toLowerCase() : 'color';
        result.suggestedName = `${productSlug || 'product'}-${colorSlug}.${ext}`;
        result.altText = `${document.title.split('—')[0].trim()} — ${colorSlug}`;

        // Part 6: pHash
        result.phash = _computePhash(colorCtx.ctx, colorCtx.w, colorCtx.h);

        return result;
    }

    // ── PART 2: AUTO-CROP FUNCTIONS ──────────────────────────────────

    function _detectBackgroundColor(ctx, w, h) {
        const size = 5;
        const samples = [];
        const corners = [
            [0, 0], [w - size, 0], [0, h - size], [w - size, h - size],
        ];
        for (const [cx, cy] of corners) {
            const data = ctx.getImageData(cx, cy, size, size).data;
            let r = 0, g = 0, b = 0;
            for (let i = 0; i < data.length; i += 4) { r += data[i]; g += data[i+1]; b += data[i+2]; }
            const n = (size * size);
            samples.push({ r: r/n, g: g/n, b: b/n });
        }
        // Average of corner samples
        const avg = samples.reduce((a, s) => ({ r: a.r+s.r, g: a.g+s.g, b: a.b+s.b }), {r:0,g:0,b:0});
        return { r: avg.r / samples.length, g: avg.g / samples.length, b: avg.b / samples.length };
    }

    function _colorDist(a, b) {
        return Math.sqrt((a.r-b.r)**2 + (a.g-b.g)**2 + (a.b-b.b)**2);
    }

    function _findBoundingBox(ctx, w, h, bgColor) {
        const threshold = CFG.autocrop.bgThreshold;

        function isBackground(x, y) {
            const d = ctx.getImageData(x, y, 1, 1).data;
            return _colorDist({r:d[0],g:d[1],b:d[2]}, bgColor) < threshold;
        }

        let top = 0, bottom = h - 1, left = 0, right = w - 1;

        // Scan from top
        outer: for (let y = 0; y < h; y++) {
            for (let x = 0; x < w; x += 4) {
                if (!isBackground(x, y)) { top = y; break outer; }
            }
        }
        // Scan from bottom
        outer: for (let y = h - 1; y >= top; y--) {
            for (let x = 0; x < w; x += 4) {
                if (!isBackground(x, y)) { bottom = y; break outer; }
            }
        }
        // Scan from left
        outer: for (let x = 0; x < w; x++) {
            for (let y = top; y <= bottom; y += 4) {
                if (!isBackground(x, y)) { left = x; break outer; }
            }
        }
        // Scan from right
        outer: for (let x = w - 1; x >= left; x--) {
            for (let y = top; y <= bottom; y += 4) {
                if (!isBackground(x, y)) { right = x; break outer; }
            }
        }

        if (right <= left || bottom <= top) return null;
        return { x: left, y: top, w: right - left, h: bottom - top };
    }

    function _cropCanvas(ctx, box) {
        const cropped = document.createElement('canvas');
        cropped.width = box.w; cropped.height = box.h;
        const cCtx = cropped.getContext('2d');
        cCtx.putImageData(ctx.getImageData(box.x, box.y, box.w, box.h), 0, 0);
        return cropped.toDataURL('image/jpeg', 0.92);
    }

    // ── PART 3: BACKGROUND WHITENING + FEATHERING ───────────────────

    function _whitenBackground(ctx, w, h, bgColor, tolerance) {
        // Sample corners for confidence check
        const corners = [
            ctx.getImageData(0, 0, 5, 5),
            ctx.getImageData(w-5, 0, 5, 5),
            ctx.getImageData(0, h-5, 5, 5),
            ctx.getImageData(w-5, h-5, 5, 5),
        ];
        let bgPixelCount = 0, totalCornerPixels = 0;
        for (const c of corners) {
            for (let i = 0; i < c.data.length; i += 4) {
                totalCornerPixels++;
                const dist = _colorDist({r:c.data[i],g:c.data[i+1],b:c.data[i+2]}, bgColor);
                if (dist < tolerance * 1.5) bgPixelCount++;
            }
        }
        const bgConfidence = bgPixelCount / totalCornerPixels;
        if (bgConfidence < 0.60) return null; // not confident enough

        const imageData = ctx.getImageData(0, 0, w, h);
        const data = imageData.data;
        const mask = new Uint8Array(w * h); // 0=keep, 1=whiten

        // First pass: mark background pixels
        for (let i = 0; i < data.length; i += 4) {
            const px = i / 4;
            const dist = _colorDist({r:data[i],g:data[i+1],b:data[i+2]}, bgColor);
            mask[px] = dist < tolerance ? 1 : 0;
        }

        // Feathering: for pixels adjacent to boundary, blend softly
        const feathered = new Float32Array(w * h); // 0=subject, 1=bg, 0-1=blend
        for (let y = 0; y < h; y++) {
            for (let x = 0; x < w; x++) {
                const px = y * w + x;
                if (!mask[px]) { feathered[px] = 0; continue; }
                // Check if near edge of mask (1px radius)
                let nearSubject = false;
                for (let dy = -1; dy <= 1; dy++) {
                    for (let dx = -1; dx <= 1; dx++) {
                        const nx = x + dx, ny = y + dy;
                        if (nx < 0 || nx >= w || ny < 0 || ny >= h) continue;
                        if (!mask[ny * w + nx]) { nearSubject = true; break; }
                    }
                    if (nearSubject) break;
                }
                feathered[px] = nearSubject ? 0.5 : 1.0; // 50% blend at boundary
            }
        }

        // Apply: whiten bg pixels, blend boundary pixels
        for (let i = 0; i < data.length; i += 4) {
            const px = i / 4;
            const alpha = feathered[px];
            if (alpha === 0) continue; // keep subject pixel as-is
            // Blend toward white
            data[i]   = Math.round(data[i]   + (255 - data[i])   * alpha);
            data[i+1] = Math.round(data[i+1] + (255 - data[i+1]) * alpha);
            data[i+2] = Math.round(data[i+2] + (255 - data[i+2]) * alpha);
        }

        ctx.putImageData(imageData, 0, 0);
        return ctx.canvas.toDataURL('image/jpeg', 0.92);
    }

    // ── PART 4: DOMINANT COLOR EXTRACTION ───────────────────────────

    function _extractDominantColor(ctx, w, h) {
        const data = ctx.getImageData(0, 0, w, h).data;
        const buckets = {};
        let totalSampled = 0;

        for (let i = 0; i < data.length; i += 4 * CFG.colorExtract.sampleStep) {
            const r = data[i], g = data[i+1], b = data[i+2];
            if (r === undefined) continue;
            // Skip near-white (whitened background)
            if (r > CFG.colorExtract.whiteThreshold && g > CFG.colorExtract.whiteThreshold && b > CFG.colorExtract.whiteThreshold) continue;
            totalSampled++;
            // Quantize to 32-level buckets for clustering
            const qr = Math.round(r / 32) * 32;
            const qg = Math.round(g / 32) * 32;
            const qb = Math.round(b / 32) * 32;
            const key = `${qr},${qg},${qb}`;
            buckets[key] = (buckets[key] || {count:0,r:0,g:0,b:0});
            buckets[key].count++;
            buckets[key].r += r;
            buckets[key].g += g;
            buckets[key].b += b;
        }

        if (!totalSampled) return null;

        // Sort by count, pick top bucket
        const sorted = Object.values(buckets).sort((a,b) => b.count - a.count);
        if (!sorted.length) return null;

        const top = sorted[0];
        const confidence = top.count / totalSampled;
        if (confidence < CFG.colorExtract.minConfidence) return null; // too varied

        // Get actual average color in top bucket
        const avgR = Math.round(top.r / top.count);
        const avgG = Math.round(top.g / top.count);
        const avgB = Math.round(top.b / top.count);

        return '#' + [avgR, avgG, avgB].map(v => v.toString(16).padStart(2,'0')).join('').toUpperCase();
    }

    // ── PART 6: PHASH (perceptual hash) ─────────────────────────────

    function _computePhash(ctx, w, h) {
        const SIZE = CFG.pHash.size;
        const tmp = document.createElement('canvas');
        tmp.width = SIZE * 4; tmp.height = SIZE * 4; // 32×32 for DCT approx
        const tCtx = tmp.getContext('2d');
        // Scale down and grayscale
        tCtx.filter = 'grayscale(1)';
        tCtx.drawImage(ctx.canvas, 0, 0, SIZE * 4, SIZE * 4);
        const data = tCtx.getImageData(0, 0, SIZE * 4, SIZE * 4).data;

        // Build grayscale matrix (32×32 flattened)
        const gray = [];
        for (let i = 0; i < data.length; i += 4) gray.push(data[i]);

        // Simplified DCT (only compute top-left SIZE×SIZE of DCT)
        const dctBlock = [];
        const N = SIZE * 4;
        for (let u = 0; u < SIZE; u++) {
            for (let v = 0; v < SIZE; v++) {
                let sum = 0;
                for (let x = 0; x < N; x++) {
                    for (let y = 0; y < N; y++) {
                        sum += gray[x * N + y] *
                            Math.cos(((2*x+1)*u*Math.PI)/(2*N)) *
                            Math.cos(((2*y+1)*v*Math.PI)/(2*N));
                    }
                }
                dctBlock.push(sum);
            }
        }

        // Skip DC coefficient (0,0), use next SIZE²-1 values
        const acCoeffs = dctBlock.slice(1, SIZE * SIZE);
        const mean = acCoeffs.reduce((a,b) => a+b, 0) / acCoeffs.length;
        const bits = acCoeffs.map(v => v >= mean ? 1 : 0);
        // Convert to hex string
        let hash = '';
        for (let i = 0; i < bits.length; i += 4) {
            hash += parseInt(bits.slice(i, i+4).join(''), 2).toString(16);
        }
        return hash;
    }

    function _hammingDistance(a, b) {
        if (!a || !b || a.length !== b.length) return 999;
        let dist = 0;
        for (let i = 0; i < a.length; i++) {
            const xor = parseInt(a[i], 16) ^ parseInt(b[i], 16);
            dist += xor.toString(2).split('').filter(c=>c==='1').length;
        }
        return dist;
    }

    // Check cross-catalog hash via AJAX
    async function checkHashCatalog(hash) {
        try {
            const r = await fetch(`/admin/api/image-hash-check?hash=${encodeURIComponent(hash)}`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            if (!r.ok) return null;
            return await r.json();
        } catch { return null; }
    }

    // ── QUALITY CHECKS ───────────────────────────────────────────────

    function _qualityCheck(w, h, result) {
        const shorter = Math.min(w, h);
        if (shorter < CFG.quality.minShortSide) {
            result.warnings.push(`Low resolution (${w}×${h}). Minimum recommended: ${CFG.quality.minShortSide}px on shorter side.`);
        }
        const ratio = w / h;
        if (ratio > CFG.quality.maxAspectDeviation || ratio < 1/CFG.quality.maxAspectDeviation) {
            result.warnings.push(`Unusual aspect ratio (${w}×${h}). Typical product photos are between 1:2 and 2:1.`);
        }
    }

    // ── REVIEW UI: SINGLE IMAGE ──────────────────────────────────────

    function _showSingleReview(result, zoneId) {
        const modal = document.getElementById('atl-pipeline-review-modal');
        if (!modal) return;

        // Populate modal fields
        modal.querySelector('#pipeline-preview-final').src = result.finalDataUrl;
        modal.querySelector('#pipeline-preview-original').src = result.originalDataUrl;
        modal.querySelector('#pipeline-warnings').innerHTML = result.warnings.length
            ? result.warnings.map(w => `<p class="text-amber-900 text-[10px] font-bold bg-amber-50 border border-amber-300 px-2 py-1">⚠ ${w}</p>`).join('')
            : '<p class="text-emerald-800 text-[10px] font-bold">✓ Image processed successfully.</p>';
        modal.querySelector('#pipeline-hex-input').value = result.dominantHex || '';
        modal.querySelector('#pipeline-alt-input').value = result.altText || '';
        modal.querySelector('#pipeline-filename-input').value = result.suggestedName || '';

        // Show/hide auto-crop badge
        const cropBadge = modal.querySelector('#pipeline-crop-badge');
        if (cropBadge) cropBadge.style.display = result.autoCropConfident ? 'inline-flex' : 'none';

        modal.dataset.zoneId = zoneId;
        modal.dataset.result = JSON.stringify({
            finalDataUrl: result.finalDataUrl,
            originalDataUrl: result.originalDataUrl,
            dominantHex: result.dominantHex,
            phash: result.phash,
        });
        modal.classList.remove('hidden');

        // Cross-catalog hash check
        if (result.phash) {
            checkHashCatalog(result.phash).then(match => {
                const dupEl = modal.querySelector('#pipeline-catalog-dup');
                if (dupEl && match?.match) {
                    dupEl.textContent = `⚠ This image looks identical to one already used for: ${match.product} — ${match.variant}`;
                    dupEl.classList.remove('hidden');
                }
            });
        }
    }

    function _pipelineConfirm() {
        const modal = document.getElementById('atl-pipeline-review-modal');
        if (!modal) return;
        const zoneId = modal.dataset.zoneId;
        const state = JSON.parse(modal.dataset.result || '{}');
        const hex = modal.querySelector('#pipeline-hex-input').value;
        const alt = modal.querySelector('#pipeline-alt-input').value;

        // Convert final dataUrl to File
        const finalDataUrl = state.finalDataUrl;
        fetch(finalDataUrl)
            .then(r => r.blob())
            .then(blob => {
                const fname = modal.querySelector('#pipeline-filename-input').value || 'processed.jpg';
                const file = new File([blob], fname, { type: 'image/jpeg' });

                // Auto-fill hex on the originating zone's variant
                if (hex) {
                    const varId = zoneId?.replace('var-dropzone-','')?.replace('new-var-dropzone','new');
                    if (varId && varId !== 'new') {
                        const hexInput = document.getElementById(`var-color-hex-${varId}`);
                        const colorPicker = document.getElementById(`var-color-picker-${varId}`);
                        if (hexInput) { hexInput.value = hex; hexInput.dispatchEvent(new Event('input')); }
                        if (colorPicker) colorPicker.value = hex;
                        // Show auto-suggest badge
                        const badge = document.getElementById(`color-suggest-badge-${varId}`);
                        if (badge) {
                            badge.querySelector('.suggest-text').textContent = hex;
                            badge.classList.remove('hidden');
                        }
                    }
                }

                // Route file into original handler
                if (zoneId === 'new-var-dropzone') {
                    if (typeof handleSmartVariantFiles === 'function') {
                        const dt = new DataTransfer(); dt.items.add(file);
                        handleSmartVariantFiles({ files: dt.files }, 'new');
                    }
                } else {
                    const varId = zoneId?.replace('var-dropzone-','');
                    if (varId && typeof handleSmartVariantFiles === 'function') {
                        const dt = new DataTransfer(); dt.items.add(file);
                        handleSmartVariantFiles({ files: dt.files }, varId);
                    }
                }

                modal.classList.add('hidden');
            });
    }

    function _pipelineCancel() {
        const modal = document.getElementById('atl-pipeline-review-modal');
        if (modal) modal.classList.add('hidden');
    }

    // Toggle before/after in review modal
    function _toggleBeforeAfter(showOriginal) {
        const modal = document.getElementById('atl-pipeline-review-modal');
        if (!modal) return;
        const state = JSON.parse(modal.dataset.result || '{}');
        modal.querySelector('#pipeline-preview-final').src = showOriginal
            ? state.originalDataUrl
            : state.finalDataUrl;
    }

    // ── REVIEW UI: BATCH ─────────────────────────────────────────────

    function _showBatchReview(rows) {
        const container = document.getElementById('atl-batch-review-rows');
        const section = document.getElementById('atl-batch-review');
        if (!container || !section) return;

        batchRows = rows;
        container.innerHTML = '';

        rows.forEach((result, idx) => {
            const row = document.createElement('div');
            row.className = 'grid grid-cols-[80px_1fr_1fr_100px_100px] gap-3 items-start border border-gray-200 p-3 bg-white';
            row.innerHTML = `
                <div>
                    <img src="${result.finalDataUrl}" class="w-20 h-20 object-contain border border-gray-200 bg-gray-50">
                    ${result.warnings.length ? `<p class="text-[9px] text-amber-700 mt-1">⚠ ${result.warnings.length} warning(s)</p>` : ''}
                </div>
                <div class="space-y-1">
                    <label class="text-[9px] font-bold uppercase text-gray-600">Color Hex</label>
                    <div class="flex items-center gap-1">
                        <input type="color" class="w-7 h-7 border border-gray-300 cursor-pointer" value="${result.dominantHex||'#000000'}"
                            oninput="document.getElementById('batch-hex-${idx}').value=this.value">
                        <input type="text" id="batch-hex-${idx}" value="${result.dominantHex||''}"
                            placeholder="Auto-detected"
                            class="flex-1 border border-gray-300 p-1.5 text-xs font-mono uppercase">
                    </div>
                    <label class="text-[9px] font-bold uppercase text-gray-600">Color Name</label>
                    <input type="text" id="batch-name-${idx}" placeholder="e.g. Chalk Pink" class="w-full border border-gray-300 p-1.5 text-xs">
                    <label class="text-[9px] font-bold uppercase text-gray-600">Alt Text</label>
                    <input type="text" id="batch-alt-${idx}" value="${result.altText||''}" class="w-full border border-gray-300 p-1.5 text-[10px]">
                </div>
                <div class="space-y-1">
                    <label class="text-[9px] font-bold uppercase text-gray-600">Price Override (EGP)</label>
                    <input type="number" id="batch-price-${idx}" placeholder="Base price" class="w-full border border-gray-300 p-1.5 text-xs font-mono">
                    <label class="text-[9px] font-bold uppercase text-gray-600">Stock</label>
                    <input type="number" id="batch-stock-${idx}" value="10" class="w-full border border-gray-300 p-1.5 text-xs font-mono">
                </div>
                <div class="text-center">
                    ${result.warnings.map(w=>`<p class="text-[9px] text-amber-700 mb-1">⚠ ${w}</p>`).join('')}
                </div>
                <div class="text-center">
                    <button type="button" onclick="AtelierImagePipeline.removeBatchRow(${idx})"
                        class="text-red-500 hover:text-red-700 text-xs font-bold uppercase border border-red-300 px-2 py-1">
                        Remove
                    </button>
                </div>
            `;
            container.appendChild(row);
        });

        section.classList.remove('hidden');
        section.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }

    function removeBatchRow(idx) {
        batchRows.splice(idx, 1);
        _showBatchReview(batchRows);
    }

    async function batchCreateAll(productId, csrfToken) {
        const btn = document.getElementById('atl-batch-create-btn');
        const progress = document.getElementById('atl-batch-progress');
        if (btn) btn.disabled = true;

        let successCount = 0;
        for (let idx = 0; idx < batchRows.length; idx++) {
            if (progress) progress.textContent = `Creating variant ${idx+1} of ${batchRows.length}...`;

            const result = batchRows[idx];
            const colorName = document.getElementById(`batch-name-${idx}`)?.value?.trim();
            const colorHex  = document.getElementById(`batch-hex-${idx}`)?.value?.trim();
            const price     = document.getElementById(`batch-price-${idx}`)?.value?.trim();
            const stock     = document.getElementById(`batch-stock-${idx}`)?.value?.trim() || '10';
            const alt       = document.getElementById(`batch-alt-${idx}`)?.value?.trim();

            if (!colorName) { alert(`Row ${idx+1}: Color name is required.`); break; }

            try {
                // Convert dataUrl to blob
                const resp = await fetch(result.finalDataUrl);
                const blob = await resp.blob();
                const fname = `${productSlug || 'product'}-${colorName.toLowerCase().replace(/\s+/g,'-')}.jpg`;
                const file = new File([blob], fname, { type: 'image/jpeg' });

                const fd = new FormData();
                fd.append('_token', csrfToken);
                fd.append('title', colorName);
                fd.append('attribute_name', 'Color');
                fd.append('color_hex', colorHex || '');
                fd.append('price_override', price || '');
                fd.append('inventory', stock);
                fd.append('status', 'draft');
                fd.append('image_file', file, fname);
                if (alt) fd.append('alt_text', alt);

                const r = await fetch(`/admin/products/${productId}/variants`, {
                    method: 'POST', body: fd,
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                if (r.ok) {
                    successCount++;
                } else {
                    const err = await r.text();
                    alert(`Row ${idx+1} failed: ${err}`);
                }
            } catch (e) {
                alert(`Row ${idx+1} error: ${e.message}`);
            }
        }

        if (progress) progress.textContent = `✓ Created ${successCount} of ${batchRows.length} variants. Refreshing...`;
        if (successCount > 0) setTimeout(() => location.reload(), 1200);
        if (btn) btn.disabled = false;
    }

    // ── UTILITY ──────────────────────────────────────────────────────

    function _loadImage(file) {
        return new Promise((resolve, reject) => {
            const img = new Image();
            img.onload = () => resolve(img);
            img.onerror = reject;
            img.src = URL.createObjectURL(file);
        });
    }

    async function _canvasFromDataUrl(dataUrl) {
        const img = await new Promise((res, rej) => {
            const i = new Image(); i.onload=()=>res(i); i.onerror=rej; i.src=dataUrl;
        });
        const c = document.createElement('canvas');
        c.width = img.width; c.height = img.height;
        const ctx = c.getContext('2d', { willReadFrequently: true });
        ctx.drawImage(img, 0, 0);
        return { ctx, w: c.width, h: c.height };
    }

    // ── PUBLIC API ───────────────────────────────────────────────────
    return {
        init,
        capture,
        captureBatch,
        confirm: _pipelineConfirm,
        cancel: _pipelineCancel,
        toggleBeforeAfter: _toggleBeforeAfter,
        removeBatchRow,
        batchCreateAll,
    };
})();
