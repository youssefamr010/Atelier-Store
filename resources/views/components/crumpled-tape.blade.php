@props([
    'variant' => 'black', // 'black' or 'yellow'
    'text' => 'ATELIER 404 · CONNECTION INTERRUPTED · RETRYING LINK · ATELIER STUDIO EGYPT · SYSTEM STANDBY · '
])

<div class="crumpled-tape-container relative w-full max-w-2xl mx-auto my-6 select-none overflow-hidden {{ $variant === 'yellow' ? 'tape-theme-yellow' : 'tape-theme-black' }}">
    <style>
        .tape-theme-black {
            --tape-bg: #121212;
            --tape-bg-dark: #050505;
            --tape-bg-light: #2c2c2c;
            --tape-text: #F5F5F0;
            --tape-border: rgba(255, 255, 255, 0.15);
        }
        .tape-theme-yellow {
            --tape-bg: #EAB308;
            --tape-bg-dark: #CA8A04;
            --tape-bg-light: #FDE047;
            --tape-text: #000000;
            --tape-border: rgba(0, 0, 0, 0.2);
        }
    </style>

    <svg viewBox="0 0 800 460" class="w-full h-auto drop-shadow-2xl overflow-visible" xmlns="http://www.w3.org/2000/svg">
        <defs>
            <!-- Soft realistic ambient + contact shadows -->
            <filter id="tape-crease-shadow" x="-20%" y="-20%" width="140%" height="140%">
                <feDropShadow dx="0" dy="10" stdDeviation="14" flood-color="#000000" flood-opacity="0.32" />
                <feDropShadow dx="0" dy="3" stdDeviation="4" flood-color="#000000" flood-opacity="0.2" />
            </filter>

            <!-- Organic creased lighting gradients for Strip 1 (Top-Left to Bottom-Right) -->
            <linearGradient id="crease-grad-1" x1="0%" y1="0%" x2="100%" y2="100%">
                <stop offset="0%" stop-color="var(--tape-bg)" />
                <stop offset="14%" stop-color="var(--tape-bg-dark)" />
                <stop offset="18%" stop-color="var(--tape-bg-light)" />
                <stop offset="29%" stop-color="var(--tape-bg)" />
                <stop offset="42%" stop-color="var(--tape-bg-light)" />
                <stop offset="46%" stop-color="var(--tape-bg-dark)" />
                <stop offset="59%" stop-color="var(--tape-bg)" />
                <stop offset="72%" stop-color="var(--tape-bg-light)" />
                <stop offset="76%" stop-color="var(--tape-bg-dark)" />
                <stop offset="88%" stop-color="var(--tape-bg)" />
                <stop offset="94%" stop-color="var(--tape-bg-light)" />
                <stop offset="100%" stop-color="var(--tape-bg)" />
            </linearGradient>

            <!-- Organic creased lighting gradients for Strip 2 (Bottom-Left to Top-Right) -->
            <linearGradient id="crease-grad-2" x1="0%" y1="100%" x2="100%" y2="0%">
                <stop offset="0%" stop-color="var(--tape-bg)" />
                <stop offset="16%" stop-color="var(--tape-bg-light)" />
                <stop offset="21%" stop-color="var(--tape-bg-dark)" />
                <stop offset="34%" stop-color="var(--tape-bg)" />
                <stop offset="48%" stop-color="var(--tape-bg-dark)" />
                <stop offset="52%" stop-color="var(--tape-bg-light)" />
                <stop offset="67%" stop-color="var(--tape-bg)" />
                <stop offset="80%" stop-color="var(--tape-bg-light)" />
                <stop offset="84%" stop-color="var(--tape-bg-dark)" />
                <stop offset="100%" stop-color="var(--tape-bg)" />
            </linearGradient>

            <!-- Gently undulating centerline paths for text warping -->
            <path id="tape-centerline-1" d="M -60 40 Q 180 110 390 225 T 860 410" fill="none" />
            <path id="tape-centerline-2" d="M -60 410 Q 180 340 390 225 T 860 40" fill="none" />
        </defs>

        <!-- Strip 1: Top-Left to Bottom-Right (underneath at intersection) -->
        <g filter="url(#tape-crease-shadow)">
            <!-- Polygonal ribbon body with jagged torn ends & subtle edge wrinkles -->
            <polygon points="-70,10 870,380 850,445 -90,75" fill="url(#crease-grad-1)" />
            
            <!-- Simulated micro crease overlay lines -->
            <line x1="120" y1="90" x2="100" y2="155" stroke="var(--tape-bg-light)" stroke-width="1.5" opacity="0.6" />
            <line x1="122" y1="90" x2="102" y2="155" stroke="var(--tape-bg-dark)" stroke-width="2" opacity="0.7" />
            <line x1="330" y1="180" x2="310" y2="245" stroke="var(--tape-bg-light)" stroke-width="2" opacity="0.6" />
            <line x1="333" y1="180" x2="313" y2="245" stroke="var(--tape-bg-dark)" stroke-width="2" opacity="0.7" />
            <line x1="620" y1="305" x2="600" y2="370" stroke="var(--tape-bg-light)" stroke-width="1.5" opacity="0.5" />
            <line x1="622" y1="305" x2="602" y2="370" stroke="var(--tape-bg-dark)" stroke-width="2" opacity="0.7" />

            <!-- Edge border stitching/gloss line -->
            <path d="M -70 10 L 870 380" stroke="var(--tape-border)" stroke-width="1" fill="none" />
            <path d="M -90 75 L 850 445" stroke="var(--tape-border)" stroke-width="1" fill="none" />

            <!-- Wavy repeating warning text along centerline -->
            <text font-family="'Cinzel', 'Courier New', monospace" font-size="14" font-weight="900" letter-spacing="4px" fill="var(--tape-text)" opacity="0.95" dy="5">
                <textPath href="#tape-centerline-1" startOffset="0%">
                    {{ $text }} {{ $text }}
                </textPath>
            </text>
        </g>

        <!-- Strip 2: Bottom-Left to Top-Right (crossing above Strip 1) -->
        <g filter="url(#tape-crease-shadow)">
            <!-- Polygonal ribbon body with organic wrinkles -->
            <polygon points="-70,440 870,70 850,5 -90,375" fill="url(#crease-grad-2)" />
            
            <!-- Simulated micro crease overlay lines -->
            <line x1="150" y1="370" x2="170" y2="305" stroke="var(--tape-bg-light)" stroke-width="2" opacity="0.6" />
            <line x1="153" y1="370" x2="173" y2="305" stroke="var(--tape-bg-dark)" stroke-width="2" opacity="0.7" />
            <line x1="440" y1="250" x2="460" y2="185" stroke="var(--tape-bg-light)" stroke-width="2" opacity="0.6" />
            <line x1="443" y1="250" x2="463" y2="185" stroke="var(--tape-bg-dark)" stroke-width="2" opacity="0.8" />
            <line x1="670" y1="150" x2="690" y2="85" stroke="var(--tape-bg-light)" stroke-width="1.5" opacity="0.5" />
            <line x1="673" y1="150" x2="693" y2="85" stroke="var(--tape-bg-dark)" stroke-width="2" opacity="0.7" />

            <!-- Edge border stitching/gloss line -->
            <path d="M -70 440 L 870 70" stroke="var(--tape-border)" stroke-width="1" fill="none" />
            <path d="M -90 375 L 850 5" stroke="var(--tape-border)" stroke-width="1" fill="none" />

            <!-- Wavy repeating warning text along centerline -->
            <text font-family="'Cinzel', 'Courier New', monospace" font-size="14" font-weight="900" letter-spacing="4px" fill="var(--tape-text)" opacity="0.95" dy="5">
                <textPath href="#tape-centerline-2" startOffset="0%">
                    {{ $text }} {{ $text }}
                </textPath>
            </text>
        </g>
    </svg>
</div>
