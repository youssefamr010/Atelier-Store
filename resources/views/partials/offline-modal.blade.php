{{-- Offline Pixel Jellyfish Screen & Mini-Game --}}
@php
    $isArabicStore = ($settings['storefront_lang'] ?? 'en') === 'ar';
@endphp

<div id="atelier-offline-overlay" 
     class="fixed inset-0 z-[99999] flex items-center justify-center bg-black/90 backdrop-blur-xl transition-all duration-500 opacity-0 pointer-events-none"
     dir="{{ $isArabicStore ? 'rtl' : 'ltr' }}"
     role="dialog" 
     aria-modal="true"
     aria-hidden="true">

    {{-- Background Ambient Pixel Stars / Dust Canvas --}}
    <canvas id="offline-starfield" class="absolute inset-0 w-full h-full pointer-events-none opacity-40"></canvas>

    {{-- Glassmorphic Card Container --}}
    <div class="relative z-10 w-[92%] max-w-lg mx-auto bg-[#0d0d12]/95 border border-white/15 rounded-3xl p-6 sm:p-8 shadow-[0_25px_70px_rgba(0,0,0,0.8)] text-center text-white overflow-hidden backdrop-blur-2xl">
        
        {{-- Ambient Glow Behind Jellyfish --}}
        <div class="absolute -top-24 left-1/2 -translate-x-1/2 w-64 h-64 bg-cyan-500/20 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute -bottom-24 right-10 w-48 h-48 bg-purple-500/20 rounded-full blur-3xl pointer-events-none"></div>

        {{-- Top Status Pill --}}
        <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-red-500/10 border border-red-500/30 text-red-400 text-[11px] font-mono uppercase tracking-widest mb-4">
            <span class="w-2 h-2 rounded-full bg-red-500 animate-ping"></span>
            <span id="offline-status-pill">{{ $isArabicStore ? 'انقطع اتصال الإنترنت' : 'No Internet Connection' }}</span>
        </div>

        {{-- Pixel Art Jellyfish Stage --}}
        <div class="relative my-2 py-4 flex flex-col items-center justify-center select-none cursor-pointer group" 
             id="pixel-jellyfish-stage"
             title="{{ $isArabicStore ? 'اضغط على القنديل للعب معه!' : 'Click the jellyfish to play!' }}">
            
            {{-- Floating Pixel Clouds in Background --}}
            <div class="absolute inset-0 overflow-hidden pointer-events-none">
                <div class="pixel-cloud cloud-1"></div>
                <div class="pixel-cloud cloud-2"></div>
            </div>

            {{-- Pixel Jellyfish Canvas / Sprite --}}
            <div id="jellyfish-wrapper" class="relative transition-transform duration-300 transform group-hover:scale-110 active:scale-95">
                <canvas id="pixel-jelly-canvas" width="160" height="160" class="image-rendering-pixelated drop-shadow-[0_0_25px_rgba(56,189,248,0.5)]"></canvas>
                
                {{-- Heart / Bubble burst container --}}
                <div id="jelly-bubbles-container" class="absolute inset-0 pointer-events-none"></div>
            </div>

            {{-- Interactive Tap Indicator --}}
            <p class="text-[11px] font-mono text-cyan-300/80 mt-2 flex items-center gap-1 group-hover:text-cyan-200 transition-colors">
                <svg class="w-3.5 h-3.5 inline-block animate-bounce" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                <span id="jelly-interactive-hint">{{ $isArabicStore ? 'انقر على القنديل لجعله يقفز ويتوهج!' : 'Tap the jellyfish to make it jump & glow!' }}</span>
                <span id="jelly-score-badge" class="hidden ml-2 px-2 py-0.5 rounded-full bg-cyan-500/20 text-cyan-300 font-bold border border-cyan-500/30">0</span>
            </p>
        </div>

        {{-- Heading & Description --}}
        <h2 class="text-xl sm:text-2xl font-bold font-sans tracking-tight mb-2 text-white">
            {{ $isArabicStore ? 'أنت تسبح بدون اتصال بالإنترنت!' : 'Lost in the Deep Pixel Waters' }}
        </h2>
        <p class="text-xs sm:text-sm text-gray-400 max-w-sm mx-auto leading-relaxed mb-6 font-sans">
            {{ $isArabicStore 
                ? 'يبدو أن شبكة الإنترنت قد انقطعت. يمكنك اللعب مع قنديل البكسل بينما نحاول إعادة الاتصال تلقائياً.' 
                : 'Your connection seems to have drifted away. Chill with the pixel jellyfish while we automatically watch for signal.' }}
        </p>

        {{-- Mini-Game Toggle or Mini Arcade Button --}}
        <div class="mb-5 flex flex-wrap items-center justify-center gap-2">
            <button type="button" 
                    id="btn-toggle-minigame"
                    class="px-3.5 py-1.5 rounded-xl bg-white/5 hover:bg-white/10 border border-white/10 text-xs font-mono text-cyan-300 transition-all active:scale-95 flex items-center gap-1.5">
                <svg class="w-4 h-4 inline-block text-cyan-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0-4h2m-2 0h-2m-3 7H6a2 2 0 01-2-2V9a2 2 0 012-2h12a2 2 0 012 2v6a2 2 0 01-2 2h-3l-2 2h-2l-2-2z"/></svg>
                <span id="minigame-btn-text">{{ $isArabicStore ? 'بدء لعبة جمع الإشارات' : 'Play Bubble Catch Mini-Game' }}</span>
            </button>
        </div>

        {{-- Mini-Game Canvas Container (Collapsible) --}}
        <div id="minigame-container" class="hidden mb-5 bg-[#08080c] border border-cyan-500/30 rounded-2xl p-2 relative overflow-hidden">
            <div class="flex justify-between items-center px-2 py-1 text-[11px] font-mono text-gray-400">
                <span id="game-score">{{ $isArabicStore ? 'النقاط: 0' : 'Score: 0' }}</span>
                <span class="text-cyan-400">{{ $isArabicStore ? 'حرك الماوس أو المس الشاشة' : 'Move Mouse / Touch' }}</span>
            </div>
            <canvas id="game-canvas" width="340" height="150" class="w-full bg-[#030308] rounded-xl cursor-crosshair"></canvas>
        </div>

        {{-- Action Buttons --}}
        <div class="flex flex-col sm:flex-row items-center justify-center gap-3">
            <button type="button" 
                    id="btn-retry-connection"
                    class="w-full sm:w-auto px-6 py-3 rounded-xl bg-gradient-to-r from-cyan-500 to-blue-600 hover:from-cyan-400 hover:to-blue-500 text-black font-bold text-xs uppercase tracking-widest transition-all transform active:scale-95 shadow-[0_0_20px_rgba(6,182,212,0.4)] flex items-center justify-center gap-2">
                <svg id="retry-spinner" class="w-4 h-4 animate-spin hidden" viewBox="0 0 24 24" fill="none">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                </svg>
                <span id="retry-btn-text">{{ $isArabicStore ? 'إعادة فحص الاتصال' : 'Retry Connection' }}</span>
            </button>

            <button type="button" 
                    id="btn-dismiss-offline"
                    class="w-full sm:w-auto px-4 py-3 rounded-xl bg-white/5 hover:bg-white/10 border border-white/10 text-xs text-gray-300 transition-all font-mono">
                {{ $isArabicStore ? 'متابعة التصفح المحلي' : 'Continue Cached' }}
            </button>
        </div>

        {{-- Auto-checking pulse bar --}}
        <div class="mt-5 flex items-center justify-center gap-2 text-[10px] text-gray-500 font-mono">
            <span class="w-1.5 h-1.5 rounded-full bg-cyan-400 animate-ping"></span>
            <span>{{ $isArabicStore ? 'نراقب عودة الاتصال في الخلفية...' : 'Watching for signal auto-recovery...' }}</span>
        </div>
    </div>
</div>

{{-- Online Restored Toast Notification --}}
<div id="atelier-online-toast" 
     class="fixed bottom-6 right-6 z-[999999] flex items-center gap-3 bg-emerald-950/95 border border-emerald-500/40 text-emerald-200 px-4 py-3 rounded-2xl shadow-2xl backdrop-blur-lg transform translate-y-24 opacity-0 transition-all duration-400 font-sans"
     dir="{{ $isArabicStore ? 'rtl' : 'ltr' }}">
    <div class="w-8 h-8 rounded-xl bg-emerald-500/20 border border-emerald-500/40 flex items-center justify-center text-emerald-400 text-lg">
        <svg class="w-4 h-4 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
    </div>
    <div>
        <p class="font-bold text-xs text-white">{{ $isArabicStore ? 'عاد الاتصال بنجاح!' : 'Connection Restored!' }}</p>
        <p class="text-[11px] text-emerald-300/80">{{ $isArabicStore ? 'تمت إعادة ربطك بالمتجر بكل سلاسة.' : 'You are back online and ready to shop.' }}</p>
    </div>
</div>

{{-- Inline Pixel Art Styles & Animations --}}
<style>
    .image-rendering-pixelated {
        image-rendering: -moz-crisp-edges;
        image-rendering: -webkit-crisp-edges;
        image-rendering: pixelated;
        image-rendering: crisp-edges;
    }

    /* Pixel Floating Clouds */
    .pixel-cloud {
        position: absolute;
        width: 64px;
        height: 24px;
        background: radial-gradient(circle at 50% 50%, rgba(255,255,255,0.15) 0%, rgba(56,189,248,0.05) 100%);
        border-radius: 4px;
        box-shadow: 
            8px -8px 0 -4px rgba(255,255,255,0.1),
            -12px 4px 0 -2px rgba(255,255,255,0.08),
            14px 6px 0 -2px rgba(56,189,248,0.1);
        filter: blur(0.5px);
    }
    .cloud-1 {
        top: 20%;
        animation: driftCloud 14s linear infinite;
    }
    .cloud-2 {
        top: 65%;
        animation: driftCloud 20s linear infinite reverse;
    }

    @keyframes driftCloud {
        0% { transform: translateX(-120%); opacity: 0; }
        15% { opacity: 0.6; }
        85% { opacity: 0.6; }
        100% { transform: translateX(380%); opacity: 0; }
    }

    /* Floating Bubble Hearts */
    .jelly-burst-pixel {
        position: absolute;
        pointer-events: none;
        animation: bubbleFloat 1.2s ease-out forwards;
    }

    @keyframes bubbleFloat {
        0% { transform: translate(0, 0) scale(0.5); opacity: 1; }
        50% { opacity: 0.9; }
        100% { transform: translate(var(--dx), var(--dy)) scale(1.3); opacity: 0; }
    }
</style>

{{-- Pixel Jellyfish Rendering & Offline State Engine Script --}}
<script>
(() => {
    const isArabic = {{ $isArabicStore ? 'true' : 'false' }};
    const overlay = document.getElementById('atelier-offline-overlay');
    const toast = document.getElementById('atelier-online-toast');
    const jellyCanvas = document.getElementById('pixel-jelly-canvas');
    const stage = document.getElementById('pixel-jelly-stage');
    const retryBtn = document.getElementById('btn-retry-connection');
    const retrySpinner = document.getElementById('retry-spinner');
    const retryBtnText = document.getElementById('retry-btn-text');
    const dismissBtn = document.getElementById('btn-dismiss-offline');
    const scoreBadge = document.getElementById('jelly-score-badge');
    const toggleGameBtn = document.getElementById('btn-toggle-minigame');
    const minigameContainer = document.getElementById('minigame-container');
    const gameCanvas = document.getElementById('game-canvas');
    const gameScoreLabel = document.getElementById('game-score');

    let isOffline = false;
    let clickScore = 0;
    let jellyAnimationId = null;
    let starfieldId = null;
    let jellyState = {
        pulsePhase: 0,
        eyeBlink: 0,
        glowIntensity: 0.5,
        expression: 'happy', // 'happy', 'blink', 'surprised', 'excited'
        squish: 1,
        yOffset: 0
    };

    // ── 1. STARFIELD CANVAS IN BACKGROUND ─────────────────────────────────────
    const starCanvas = document.getElementById('offline-starfield');
    let starCtx = starCanvas ? starCanvas.getContext('2d') : null;
    let stars = [];

    function initStarfield() {
        if (!starCanvas || !starCtx) return;
        starCanvas.width = window.innerWidth;
        starCanvas.height = window.innerHeight;
        stars = [];
        for (let i = 0; i < 70; i++) {
            stars.push({
                x: Math.random() * starCanvas.width,
                y: Math.random() * starCanvas.height,
                size: Math.random() > 0.8 ? 3 : 2,
                color: Math.random() > 0.5 ? '#38bdf8' : '#a855f7',
                alpha: Math.random(),
                speed: 0.3 + Math.random() * 0.5
            });
        }
    }

    function renderStarfield() {
        if (!starCtx || !overlay.classList.contains('opacity-100')) return;
        starCtx.clearRect(0, 0, starCanvas.width, starCanvas.height);
        stars.forEach(s => {
            s.y -= s.speed;
            if (s.y < 0) { s.y = starCanvas.height; s.x = Math.random() * starCanvas.width; }
            s.alpha += (Math.random() - 0.5) * 0.05;
            if (s.alpha < 0.1) s.alpha = 0.1;
            if (s.alpha > 0.9) s.alpha = 0.9;
            starCtx.fillStyle = s.color;
            starCtx.globalAlpha = s.alpha;
            starCtx.fillRect(Math.floor(s.x), Math.floor(s.y), s.size, s.size);
        });
        starCtx.globalAlpha = 1;
        starfieldId = requestAnimationFrame(renderStarfield);
    }

    // ── 2. HIGH-RES PIXEL JELLYFISH SPRITE RENDERER ───────────────────────────
    const ctx = jellyCanvas ? jellyCanvas.getContext('2d') : null;
    
    // Pixel Matrix for Jellyfish Bell (16x16 Grid)
    // 0 = empty, 1 = outline, 2 = body main (cyan/glow), 3 = body highlight, 4 = blush (pink), 5 = eye (dark)
    const jellyBaseGrid = [
        [0,0,0,0,1,1,1,1,1,1,1,1,0,0,0,0],
        [0,0,1,1,2,2,3,3,2,2,2,2,1,1,0,0],
        [0,1,2,2,3,3,3,3,2,2,2,2,2,2,1,0],
        [1,2,2,3,3,3,3,3,2,2,2,2,2,2,2,1],
        [1,2,3,3,2,2,2,2,2,2,2,2,2,2,2,1],
        [1,2,3,2,5,5,2,2,2,2,5,5,2,2,2,1],
        [1,2,2,2,5,5,2,2,2,2,5,5,2,2,2,1],
        [1,2,4,4,2,2,2,2,2,2,2,2,4,4,2,1],
        [1,2,2,2,2,2,2,2,2,2,2,2,2,2,2,1],
        [1,2,2,2,2,2,1,1,1,1,2,2,2,2,2,1],
        [0,1,2,1,2,1,0,0,0,0,1,2,1,2,1,0],
        [0,0,1,0,1,0,0,0,0,0,0,1,0,1,0,0]
    ];

    function renderPixelJellyfish(time) {
        if (!ctx) return;
        ctx.clearRect(0, 0, 160, 160);

        // Breathing / Swimming undulation
        const swimCycle = Math.sin(time * 0.0035);
        const squishX = 1 + (swimCycle * 0.12 * jellyState.squish);
        const squishY = 1 - (swimCycle * 0.1 * jellyState.squish);
        const floatY = Math.sin(time * 0.002) * 8 + jellyState.yOffset;

        ctx.save();
        ctx.translate(80, 55 + floatY);
        ctx.scale(squishX, squishY);

        const pixelSize = 6;
        const startX = - (16 * pixelSize) / 2;
        const startY = - (12 * pixelSize) / 2;

        // Color palettes dynamically reacting to pulse
        const glowAlpha = 0.5 + Math.sin(time * 0.005) * 0.3;
        const mainColor = '#38bdf8'; // Sky cyan
        const highColor = '#bae6fd'; // Light cyan
        const outlineColor = '#0369a1'; // Deep oceanic blue
        const blushColor = '#f472b6'; // Pink blush
        const eyeColor = '#0c162c';

        // Draw Jellyfish Bell Pixels
        for (let row = 0; row < jellyBaseGrid.length; row++) {
            for (let col = 0; col < jellyBaseGrid[row].length; col++) {
                const val = jellyBaseGrid[row][col];
                if (val === 0) continue;

                let fill = null;
                if (val === 1) fill = outlineColor;
                else if (val === 2) fill = mainColor;
                else if (val === 3) fill = highColor;
                else if (val === 4) fill = blushColor;
                else if (val === 5) {
                    // Expression logic: Blinking or Happy arcs
                    if (jellyState.expression === 'blink' || Math.sin(time * 0.001) > 0.95) {
                        fill = (row === 6) ? outlineColor : null; // Close eye slit
                    } else if (jellyState.expression === 'surprised') {
                        fill = eyeColor;
                    } else {
                        fill = eyeColor;
                    }
                }

                if (fill) {
                    ctx.fillStyle = fill;
                    ctx.fillRect(startX + (col * pixelSize), startY + (row * pixelSize), pixelSize, pixelSize);
                }
            }
        }

        // Draw Animated Pixel Tentacles trailing below
        const tentacleCount = 5;
        const tentacleSpacing = 14;
        const tentacleBaseX = - (tentacleCount - 1) * tentacleSpacing / 2;
        const tentacleTopY = startY + (11 * pixelSize);

        for (let t = 0; t < tentacleCount; t++) {
            const tx = tentacleBaseX + (t * tentacleSpacing);
            const tentacleLength = 8 + (t % 2 === 0 ? 3 : 0);
            
            for (let seg = 0; seg < tentacleLength; seg++) {
                const wave = Math.sin((time * 0.006) + (t * 0.8) + (seg * 0.45)) * (4 + seg * 0.6);
                const py = tentacleTopY + (seg * pixelSize);
                const px = tx + wave;

                ctx.fillStyle = (seg < 2) ? highColor : ((seg % 2 === 0) ? mainColor : '#0284c7');
                ctx.fillRect(px, py, pixelSize - 1, pixelSize - 1);
            }
        }

        ctx.restore();

        // Little floating ambient bubbles around the jellyfish
        for (let b = 0; b < 3; b++) {
            const bx = 80 + Math.sin(time * 0.002 + b * 2) * 45;
            const by = (time * 0.04 + b * 50) % 150;
            ctx.fillStyle = 'rgba(186, 230, 253, 0.6)';
            ctx.fillRect(bx, 150 - by, 3, 3);
        }

        jellyAnimationId = requestAnimationFrame(renderPixelJellyfish);
    }

    // ── 3. TAP / INTERACTIVE SQUISH & SPARKLES ─────────────────────────────────
    if (stage) {
        stage.addEventListener('click', (e) => {
            clickScore++;
            if (scoreBadge) {
                scoreBadge.classList.remove('hidden');
                scoreBadge.textContent = isArabic ? `${clickScore} قفزة` : `${clickScore} jumps`;
            }

            // Trigger squish animation
            jellyState.squish = 2.4;
            jellyState.expression = 'surprised';
            jellyState.yOffset = -18;

            setTimeout(() => {
                jellyState.squish = 1;
                jellyState.expression = 'happy';
                jellyState.yOffset = 0;
            }, 350);

            // Spawn floating pixel hearts / bubbles
            const bubblesWrap = document.getElementById('jelly-bubbles-container');
            if (bubblesWrap) {
                const rect = stage.getBoundingClientRect();
                const count = 5;
                for (let i = 0; i < count; i++) {
                    const bubble = document.createElement('div');
                    bubble.className = 'jelly-burst-pixel';
                    const colors = ['#38bdf8', '#f472b6', '#a855f7', '#34d399', '#fde047'];
                    const color = colors[Math.floor(Math.random() * colors.length)];
                    bubble.style.cssText = `
                        left: ${50 + (Math.random() * 20 - 10)}%;
                        top: ${40 + (Math.random() * 20 - 10)}%;
                        width: 8px;
                        height: 8px;
                        background: ${color};
                        box-shadow: 0 0 8px ${color};
                        --dx: ${(Math.random() - 0.5) * 90}px;
                        --dy: ${- (40 + Math.random() * 70)}px;
                    `;
                    bubblesWrap.appendChild(bubble);
                    setTimeout(() => bubble.remove(), 1200);
                }
            }
        });
    }

    // ── 4. MINI-GAME: BUBBLE CATCH WITH PIXEL JELLY ────────────────────────────
    let gameRunning = false;
    let gameScore = 0;
    let gameJellyX = 170;
    let gameItems = [];
    let gameLoopId = null;

    if (toggleGameBtn && minigameContainer && gameCanvas) {
        const gCtx = gameCanvas.getContext('2d');
        
        toggleGameBtn.addEventListener('click', () => {
            minigameContainer.classList.toggle('hidden');
            if (!minigameContainer.classList.contains('hidden')) {
                startGame();
            } else {
                stopGame();
            }
        });

        function startGame() {
            gameRunning = true;
            gameScore = 0;
            gameItems = [];
            gameJellyX = gameCanvas.width / 2;
            updateScoreUI();
            gameLoop();
        }

        function stopGame() {
            gameRunning = false;
            if (gameLoopId) cancelAnimationFrame(gameLoopId);
        }

        function updateScoreUI() {
            if (gameScoreLabel) {
                gameScoreLabel.textContent = isArabic ? `النقاط: ${gameScore}` : `Score: ${gameScore}`;
            }
        }

        // Mouse & Touch Controls
        const handleMove = (clientX) => {
            const rect = gameCanvas.getBoundingClientRect();
            const scale = gameCanvas.width / rect.width;
            gameJellyX = (clientX - rect.left) * scale;
            if (gameJellyX < 20) gameJellyX = 20;
            if (gameJellyX > gameCanvas.width - 20) gameJellyX = gameCanvas.width - 20;
        };

        gameCanvas.addEventListener('mousemove', (e) => handleMove(e.clientX));
        gameCanvas.addEventListener('touchmove', (e) => {
            if (e.touches[0]) handleMove(e.touches[0].clientX);
        }, { passive: true });

        function gameLoop() {
            if (!gameRunning) return;
            gCtx.fillStyle = '#050711';
            gCtx.fillRect(0, 0, gameCanvas.width, gameCanvas.height);

            // Spawn falling pearls / wifi orbs
            if (Math.random() < 0.05) {
                gameItems.push({
                    x: 20 + Math.random() * (gameCanvas.width - 40),
                    y: -10,
                    speed: 1.5 + Math.random() * 2,
                    isGold: Math.random() > 0.8
                });
            }

            // Update & Draw Items
            for (let i = gameItems.length - 1; i >= 0; i--) {
                const item = gameItems[i];
                item.y += item.speed;

                // Draw Item Pixel
                gCtx.fillStyle = item.isGold ? '#facc15' : '#38bdf8';
                gCtx.fillRect(item.x - 4, item.y - 4, 8, 8);
                gCtx.fillStyle = '#ffffff';
                gCtx.fillRect(item.x - 2, item.y - 2, 4, 4);

                // Collision detection with mini jelly
                const dx = item.x - gameJellyX;
                const dy = item.y - 120;
                if (Math.sqrt(dx*dx + dy*dy) < 18) {
                    gameScore += item.isGold ? 5 : 1;
                    updateScoreUI();
                    gameItems.splice(i, 1);
                    continue;
                }

                // Remove when out of screen
                if (item.y > gameCanvas.height + 10) {
                    gameItems.splice(i, 1);
                }
            }

            // Draw player mini jellyfish
            gCtx.fillStyle = '#38bdf8';
            gCtx.fillRect(gameJellyX - 12, 115, 24, 14);
            gCtx.fillStyle = '#ffffff';
            gCtx.fillRect(gameJellyX - 6, 118, 3, 3);
            gCtx.fillRect(gameJellyX + 3, 118, 3, 3);
            // Tentacles
            for (let t = -8; t <= 8; t += 4) {
                gCtx.fillStyle = '#0284c7';
                gCtx.fillRect(gameJellyX + t, 129, 2, 8 + Math.sin(Date.now() * 0.01 + t) * 3);
            }

            gameLoopId = requestAnimationFrame(gameLoop);
        }
    }

    // ── 5. OFFLINE / ONLINE LIFECYCLE DETECTION ──────────────────────────────
    function showOfflineScreen() {
        if (isOffline) return;
        isOffline = true;
        
        if (overlay) {
            overlay.classList.remove('opacity-0', 'pointer-events-none');
            overlay.classList.add('opacity-100', 'pointer-events-auto');
            overlay.setAttribute('aria-hidden', 'false');
        }

        initStarfield();
        if (!starfieldId) starfieldId = requestAnimationFrame(renderStarfield);
        if (!jellyAnimationId) jellyAnimationId = requestAnimationFrame(renderPixelJellyfish);
    }

    function hideOfflineScreen(showToast = true) {
        if (!isOffline && !overlay.classList.contains('opacity-100')) return;
        isOffline = false;

        if (overlay) {
            overlay.classList.add('opacity-0', 'pointer-events-none');
            overlay.classList.remove('opacity-100', 'pointer-events-auto');
            overlay.setAttribute('aria-hidden', 'true');
        }

        if (starfieldId) { cancelAnimationFrame(starfieldId); starfieldId = null; }
        if (jellyAnimationId) { cancelAnimationFrame(jellyAnimationId); jellyAnimationId = null; }

        if (showToast && toast) {
            toast.classList.remove('translate-y-24', 'opacity-0');
            toast.classList.add('translate-y-0', 'opacity-100');
            setTimeout(() => {
                toast.classList.remove('translate-y-0', 'opacity-100');
                toast.classList.add('translate-y-24', 'opacity-0');
            }, 4000);
        }
    }

    // Manual Recheck Connection Button
    async function checkConnection() {
        if (retrySpinner) retrySpinner.classList.remove('hidden');
        if (retryBtnText) retryBtnText.textContent = isArabic ? 'جارِ الفحص...' : 'Checking...';

        try {
            const controller = new AbortController();
            const timeoutId = setTimeout(() => controller.abort(), 3500);
            const response = await fetch('/favicon.ico?_ping=' + Date.now(), {
                method: 'HEAD',
                cache: 'no-store',
                signal: controller.signal
            });
            clearTimeout(timeoutId);

            if (response.ok || response.type === 'opaque') {
                hideOfflineScreen(true);
            } else {
                throw new Error('Unreachable');
            }
        } catch (e) {
            // Still offline
            if (retryBtnText) retryBtnText.textContent = isArabic ? 'لم نتمكن من الاتصال بعد' : 'Still Offline, Retrying...';
            setTimeout(() => {
                if (retryBtnText) retryBtnText.textContent = isArabic ? 'إعادة فحص الاتصال' : 'Retry Connection';
            }, 1800);
        } finally {
            if (retrySpinner) retrySpinner.classList.add('hidden');
        }
    }

    if (retryBtn) {
        retryBtn.addEventListener('click', checkConnection);
    }

    if (dismissBtn) {
        dismissBtn.addEventListener('click', () => {
            hideOfflineScreen(false);
        });
    }

    // Browser Native Events
    window.addEventListener('offline', () => {
        showOfflineScreen();
    });

    window.addEventListener('online', () => {
        checkConnection();
    });

    // Check initial state on load
    if (!navigator.onLine) {
        showOfflineScreen();
    }

    // Developer Test Shortcut: Press Ctrl + Shift + O to simulate offline popup anytime
    window.addEventListener('keydown', (e) => {
        if (e.ctrlKey && e.shiftKey && (e.key === 'O' || e.key === 'o')) {
            e.preventDefault();
            if (overlay && overlay.classList.contains('opacity-100')) {
                hideOfflineScreen(true);
            } else {
                showOfflineScreen();
            }
        }
    });

    // Expose helper globally for debugging / demonstration
    window.atelierSimulateOffline = () => showOfflineScreen();
    window.atelierSimulateOnline = () => hideOfflineScreen(true);
})();
</script>
