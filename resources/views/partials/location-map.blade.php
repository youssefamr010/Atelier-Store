@props([
    'mapId'        => 'atelier-map',
    'latInputId'   => 'input-latitude',
    'lngInputId'   => 'input-longitude',
    'cityInputId'  => null,
    'streetInputId'=> null,
    'initialLat'   => 30.0444,
    'initialLng'   => 31.2357,
    'disabled'     => false,
])
@php
    // Calculate language locally — $mapIsAr lives in layout scope only
    $mapIsAr = (($settings['storefront_lang'] ?? 'en') === 'ar');
@endphp

@once
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<style>
    /* ── ATELIER MAP — Minimal Black/White ─────────────────────────────── */
    .atl-pin-wrap {
        display: flex; align-items: flex-end; justify-content: center;
        width: 32px; height: 40px;
        filter: drop-shadow(0 3px 8px rgba(0,0,0,0.4));
        transition: transform .18s;
    }
    .atl-pin-wrap:hover { transform: translateY(-3px) scale(1.08); }
    .atl-pin-body {
        width: 32px; height: 32px;
        background: #000; border: 2.5px solid #fff;
        clip-path: polygon(50% 100%, 0% 30%, 15% 0%, 85% 0%, 100% 30%);
        display: flex; align-items: center; justify-content: center;
    }
    .atl-pin-dot { width: 9px; height: 9px; background: #fff; border-radius: 50%; margin-top: -4px; }

    /* Leaflet popup */
    .leaflet-popup-content-wrapper {
        border: 2px solid #000 !important; border-radius: 0 !important;
        box-shadow: 3px 3px 0 0 #000 !important;
        background: #fff !important; padding: 0 !important;
    }
    .leaflet-popup-content {
        margin: 0 !important; padding: 8px 12px !important;
        font-size: 11px !important; font-family: 'Inter', sans-serif !important;
        font-weight: 700 !important; letter-spacing: .06em !important;
        text-transform: uppercase !important;
    }
    .leaflet-popup-tip { background: #000 !important; }
    .leaflet-popup-close-button { display: none !important; }

    /* Leaflet zoom controls */
    .leaflet-control-zoom {
        border: 2px solid #000 !important; border-radius: 0 !important;
        box-shadow: 2px 2px 0 0 #000 !important; overflow: hidden;
    }
    .leaflet-control-zoom a {
        border-radius: 0 !important; background: #fff !important;
        color: #000 !important; font-weight: 800 !important;
        width: 28px !important; height: 28px !important;
        line-height: 26px !important; font-size: 15px !important;
        border-bottom: 1px solid #000 !important;
    }
    .leaflet-control-zoom a:last-child { border-bottom: none !important; }
    .leaflet-control-zoom a:hover { background: #000 !important; color: #fff !important; }

    /* Search suggestion */
    .atl-suggestion:hover { background: #F5F5F0; }

    /* Coord bar */
    .atl-coord-bar {
        background: #000; color: #fff;
        font-family: 'Inter', sans-serif; font-size: 10px;
        letter-spacing: .04em; padding: 5px 12px;
        display: flex; align-items: center; justify-content: space-between;
        border-top: 2px solid #000;
    }
</style>
@endonce

<div class="atelier-map-shell" id="mapshell-{{ $mapId }}"
    x-data="{
        searchQ: '',
        results: [],
        searching: false,
        pasteOpen: false,
        pasteVal: '',
        doSearch() {
            if (!this.searchQ || this.searchQ.length < 2) { this.results = []; return; }
            this.searching = true;
            clearTimeout(this._st);
            this._st = setTimeout(() => {
                fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(this.searchQ)}&countrycodes=eg&limit=5&addressdetails=1`, { headers: { 'Accept-Language': 'ar,en' } })
                .then(r => r.json())
                .then(d => { this.searching = false; this.results = d || []; })
                .catch(() => { this.searching = false; this.results = []; });
            }, 360);
        },
        pickResult(item) {
            window['mapFly_{{ str_replace('-','_',$mapId) }}'](+item.lat, +item.lon, item.display_name.split(',')[0]);
            this.results = [];
            this.searchQ = item.display_name.split(',')[0];
        },
        parsePaste() {
            window['mapParsePaste_{{ str_replace('-','_',$mapId) }}'](this.pasteVal);
            this.pasteVal = ''; this.pasteOpen = false;
        }
    }"
>
    {{-- ── SECTION LABEL ──────────────────────── --}}
    <div class="flex items-center justify-between mb-2">
        <div class="flex items-center gap-2">
            <div class="w-1 h-4 bg-black shrink-0"></div>
            <span class="text-[10px] font-bold uppercase tracking-[.18em] text-black">
                @if($mapIsAr ?? false) تحديد موقع التوصيل @else Delivery Location @endif
            </span>
        </div>
        {{-- GPS button only --}}
        @if(!$disabled)
        <button type="button"
            onclick="window['mapGPS_{{ str_replace('-','_',$mapId) }}']()"
            class="inline-flex items-center gap-1.5 text-[10px] font-bold uppercase tracking-wider border-2 border-black px-3 py-1.5 bg-black text-white hover:bg-neutral-800 transition-all shadow-[2px_2px_0_0_rgba(0,0,0,1)] active:shadow-none active:translate-x-0.5 active:translate-y-0.5"
        >
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7.5 3.75H6A2.25 2.25 0 003.75 6v1.5M16.5 3.75H18A2.25 2.25 0 0120.25 6v1.5m0 9V18A2.25 2.25 0 0118 20.25h-1.5m-9 0H6A2.25 2.25 0 013.75 18v-1.5M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            <span id="gpsTxt-{{ $mapId }}">GPS</span>
        </button>
        @endif
    </div>

    {{-- ── LIVE SEARCH ──────────────────────────── --}}
    <div class="relative mb-2">
        <div class="flex items-center border-2 border-black bg-white focus-within:shadow-[3px_3px_0_0_rgba(0,0,0,1)] transition-shadow">
            <span class="pl-3 pr-2 text-black/40 pointer-events-none">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/></svg>
            </span>
            <input
                type="text" x-model="searchQ" @input="doSearch()"
                placeholder="{{ ($mapIsAr ?? false) ? 'ابحث عن المنطقة أو الشارع...' : 'Search area or street...' }}"
                class="flex-1 py-2.5 pr-1 text-xs bg-transparent focus:outline-none placeholder-gray-400"
                autocomplete="off" @keydown.escape="results = []"
            >
            <span x-show="searching" class="pl-3 pr-3 text-[9px] text-gray-400 shrink-0">...</span>
        </div>
        {{-- Dropdown --}}
        <div x-show="results.length > 0" @click.away="results = []" x-cloak
            class="absolute left-0 right-0 top-full z-40 bg-white border-2 border-black border-t-0 shadow-[4px_4px_0_0_rgba(0,0,0,1)] max-h-44 overflow-y-auto">
            <template x-for="r in results" :key="r.place_id">
                <div @click="pickResult(r)"
                    class="atl-suggestion flex items-center gap-2.5 px-3 py-2.5 cursor-pointer border-b border-black/10 last:border-0">
                    <svg class="w-4 h-4 shrink-0 text-black/40" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z"/></svg>
                    <p class="text-xs font-semibold text-black truncate" x-text="r.display_name.split(',')[0]"></p>
                    <span class="ml-auto text-[9px] font-bold uppercase bg-black text-white px-1.5 py-0.5 shrink-0">
                        {{ ($mapIsAr ?? false) ? 'اختيار' : 'Pick' }}
                    </span>
                </div>
            </template>
        </div>
    </div>

    {{-- ── MAP CANVAS ──────────────────────────── --}}
    <div class="relative border-2 border-black overflow-hidden shadow-[4px_4px_0_0_rgba(0,0,0,1)]">
        <div id="{{ $mapId }}" class="w-full {{ $disabled ? 'pointer-events-none opacity-60' : '' }}"
             style="height: 180px; z-index: 10; background: #e8e8e3;"></div>

        {{-- Paste link button --}}
        <button type="button" @click="pasteOpen=!pasteOpen"
            class="absolute top-2 right-2 z-20 bg-white border-2 border-black text-[9px] font-bold uppercase tracking-wider px-2 py-1 shadow-[2px_2px_0_0_rgba(0,0,0,1)] hover:bg-black hover:text-white transition-colors"
            title="{{ ($mapIsAr ?? false) ? 'لصق رابط Google Maps' : 'Paste Google Maps link' }}"
        >
            <svg class="w-3.5 h-3.5 inline-block" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 011.242 7.244l-4.5 4.5a4.5 4.5 0 01-6.364-6.364l1.757-1.757m13.35-.622l1.757-1.757a4.5 4.5 0 00-6.364-6.364l-4.5 4.5a4.5 4.5 0 001.242 7.244"/></svg>
        </button>

        {{-- Loading --}}
        <div id="mapLoading-{{ $mapId }}" class="absolute inset-0 flex items-center justify-center bg-[#F5F5F0] z-30">
            <div class="text-center space-y-2">
                <div class="w-7 h-7 border-2 border-black border-t-transparent mx-auto" style="animation: spin 0.8s linear infinite;"></div>
            </div>
        </div>
    </div>

    {{-- ── PASTE LINK BOX ──────────────────────── --}}
    <div x-show="pasteOpen" x-cloak class="mt-1 bg-[#F5F5F0] border-2 border-black p-3">
        <div class="flex gap-2">
            <input type="text" x-model="pasteVal"
                placeholder="{{ ($mapIsAr ?? false) ? 'https://maps.app.goo.gl/... أو 30.062, 31.222' : 'https://maps.app.goo.gl/... or 30.062, 31.222' }}"
                class="flex-1 border border-black p-2 text-xs bg-white focus:outline-none font-mono min-w-0">
            <button type="button" @click="parsePaste()"
                class="bg-black text-white px-3 py-2 text-[10px] font-bold uppercase tracking-wider hover:bg-gray-800 shrink-0">
                {{ ($mapIsAr ?? false) ? 'تثبيت' : 'Set' }}
            </button>
        </div>
    </div>

    {{-- ── STATUS BAR ──────────────────────────── --}}
    <div class="atl-coord-bar">
        <div class="flex items-center gap-2 min-w-0">
            <span class="w-1.5 h-1.5 rounded-full bg-white/60 shrink-0" id="statusDot-{{ $mapId }}" style="border-radius:50%"></span>
            <span id="statusTxt-{{ $mapId }}" class="truncate">
                {{ ($mapIsAr ?? false) ? 'اضغط على الخريطة لتثبيت موقع التوصيل' : 'Tap map to set delivery location' }}
            </span>
        </div>
        <span id="coordBadge-{{ $mapId }}" class="font-mono text-white/70 shrink-0 text-[9px]"></span>
    </div>

    {{-- ── REVERSE GEOCODE CHIPS ────────────────── --}}
    <div id="addrChips-{{ $mapId }}" class="hidden border-2 border-black border-t-0 bg-[#F9F9F6] px-4 py-2 flex flex-wrap items-center gap-x-4 gap-y-1 text-xs">
        <span class="font-semibold text-black" id="chipGov-{{ $mapId }}"></span>
        <span class="text-black/30">|</span>
        <span class="text-black/60 truncate max-w-xs" id="chipStreet-{{ $mapId }}"></span>
        <span class="ml-auto font-mono text-[10px] text-black/40" id="chipCoords-{{ $mapId }}"></span>
    </div>

    {{-- Hidden inputs --}}
    <input type="hidden" name="latitude"  id="{{ $latInputId }}"  value="{{ $initialLat }}" {{ $disabled ? 'disabled' : '' }}>
    <input type="hidden" name="longitude" id="{{ $lngInputId }}"  value="{{ $initialLng }}" {{ $disabled ? 'disabled' : '' }}>
</div>

@push('scripts')
<script>
(function() {
    const MAP_ID    = '{{ $mapId }}';
    const FN        = '{{ str_replace('-','_',$mapId) }}';
    const LAT_EL    = '{{ $latInputId }}';
    const LNG_EL    = '{{ $lngInputId }}';
    const CITY_EL   = '{{ $cityInputId }}';
    const STREET_EL = '{{ $streetInputId }}';
    const INIT_LAT  = {{ (float)($initialLat ?: 30.0444) }};
    const INIT_LNG  = {{ (float)($initialLng ?: 31.2357) }};
    const IS_AR     = {{ ($mapIsAr ?? false) ? 'true' : 'false' }};

    let map, marker, gpsCircle;

    // CartoDB Positron — free, no API key, clean minimal style
    const tileLayer = L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', {
        attribution: '&copy; <a href="https://carto.com/" target="_blank">CARTO</a> &copy; <a href="https://www.openstreetmap.org/copyright" target="_blank">OpenStreetMap</a>',
        subdomains: 'abcd',
        maxZoom: 20,
    });

    function pinIcon() {
        return L.divIcon({
            className: '',
            html: `<div class="atl-pin-wrap"><div class="atl-pin-body"><div class="atl-pin-dot"></div></div></div>`,
            iconSize: [32, 40], iconAnchor: [16, 40], popupAnchor: [0, -42]
        });
    }

    function setCoords(lat, lng) {
        document.getElementById(LAT_EL).value = lat.toFixed(6);
        document.getElementById(LNG_EL).value = lng.toFixed(6);
        const fmt = `${lat.toFixed(4)}, ${lng.toFixed(4)}`;
        const badge = document.getElementById('coordBadge-' + MAP_ID);
        if (badge) badge.textContent = fmt;
        const chipCoords = document.getElementById('chipCoords-' + MAP_ID);
        if (chipCoords) chipCoords.textContent = fmt;
        const dot = document.getElementById('statusDot-' + MAP_ID);
        if (dot) dot.style.background = '#fff';
    }

    function setStatus(msg) {
        const el = document.getElementById('statusTxt-' + MAP_ID);
        if (el) el.textContent = msg;
    }

    function showChips(show) {
        const el = document.getElementById('addrChips-' + MAP_ID);
        if (el) el.style.display = show ? 'flex' : 'none';
    }

    function reverseGeocode(lat, lng) {
        setStatus(IS_AR ? 'قراءة العنوان...' : 'Reading address...');
        fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&zoom=18&addressdetails=1`, {
            headers: { 'Accept-Language': IS_AR ? 'ar,en' : 'en,ar' }
        })
        .then(r => r.json())
        .then(data => {
            if (!data || !data.address) { setStatus(IS_AR ? 'تم تثبيت الموقع ✓' : 'Location set ✓'); return; }
            const a = data.address;
            const city   = a.city || a.town || a.state || a.county || (IS_AR ? 'القاهرة' : 'Cairo');
            const street = [a.road, a.suburb, a.neighbourhood, a.building].filter(Boolean).join(IS_AR ? '، ' : ', ');

            if (CITY_EL) { const el = document.getElementById(CITY_EL); if (el) { el.value = city; el.setAttribute('data-auto','1'); } }
            if (STREET_EL) { const el = document.getElementById(STREET_EL); if (el && street) { el.value = street; el.setAttribute('data-auto','1'); } }

            const chipGov = document.getElementById('chipGov-' + MAP_ID);
            if (chipGov) chipGov.textContent = city;
            const chipStreet = document.getElementById('chipStreet-' + MAP_ID);
            if (chipStreet) chipStreet.textContent = street || (IS_AR ? 'موقع محدد' : 'Location set');

            setStatus(`✓ ${city}${street ? ' — ' + street.split(IS_AR ? '،' : ',')[0] : ''}`);
            showChips(true);

            if (marker) {
                marker.bindPopup(
                    `<span>${city}</span>`,
                    { maxWidth: 140, closeButton: false }
                ).openPopup();
            }
        })
        .catch(() => setStatus(IS_AR ? 'تم تثبيت الموقع ✓' : 'Location set ✓'));
    }

    function updatePin(lat, lng, reverse = true) {
        setCoords(lat, lng);
        if (reverse) reverseGeocode(lat, lng);
    }

    function init() {
        const el = document.getElementById(MAP_ID);
        if (!el || el._leaflet_id) return;
        const loading = document.getElementById('mapLoading-' + MAP_ID);
        if (loading) loading.remove();

        map = L.map(MAP_ID, {
            center: [INIT_LAT, INIT_LNG],
            zoom: 13,
            zoomControl: true,
            scrollWheelZoom: false,
        });
        tileLayer.addTo(map);

        marker = L.marker([INIT_LAT, INIT_LNG], {
            icon: pinIcon(),
            draggable: !{{ $disabled ? 'true' : 'false' }},
            title: IS_AR ? 'اسحب الدبوس لتغيير موقع التسليم' : 'Drag pin to change delivery location'
        }).addTo(map);

        setCoords(INIT_LAT, INIT_LNG);

        marker.on('dragend', () => { const p = marker.getLatLng(); updatePin(p.lat, p.lng, true); });
        map.on('click', e => { marker.setLatLng(e.latlng); updatePin(e.latlng.lat, e.latlng.lng, true); });

        window['mapInstance_' + FN] = map;
        window['markerInstance_' + FN] = marker;
    }

    // Public API
    window['mapFly_' + FN] = function(lat, lng, label) {
        if (!map) return;
        map.flyTo([lat, lng], 15, { duration: 1.0, easeLinearity: 0.4 });
        marker.setLatLng([lat, lng]);
        updatePin(lat, lng, true);
        const chipStreet = document.getElementById('chipStreet-' + MAP_ID);
        if (chipStreet && label) chipStreet.textContent = label;
        showChips(true);
    };

    window['mapParsePaste_' + FN] = function(input) {
        input = (input || '').trim();
        const m1 = input.match(/(-?\d+\.?\d*)[,\s]+(-?\d+\.?\d*)/);
        if (m1) {
            const lat = +m1[1], lng = +m1[2];
            if (lat >= -90 && lat <= 90 && lng >= -180 && lng <= 180) {
                window['mapFly_' + FN](lat, lng, IS_AR ? 'موقع مُدخل' : 'Custom location');
                return;
            }
        }
        const m2 = input.match(/q=(-?\d+\.?\d*)[,+](-?\d+\.?\d*)/);
        if (m2) { window['mapFly_' + FN](+m2[1], +m2[2], 'Google Maps'); return; }
        const m3 = input.match(/@(-?\d+\.?\d*),(-?\d+\.?\d*)/);
        if (m3) { window['mapFly_' + FN](+m3[1], +m3[2], 'Google Maps'); return; }
        alert(IS_AR ? 'لم يتمكن من استخراج الإحداثيات.' : 'Could not extract coordinates.');
    };

    window['mapGPS_' + FN] = function() {
        if (!navigator.geolocation) { alert(IS_AR ? 'GPS غير مدعوم.' : 'GPS not supported.'); return; }
        const btnTxt = document.getElementById('gpsTxt-' + MAP_ID);
        if (btnTxt) btnTxt.textContent = '...';
        setStatus(IS_AR ? 'جاري تحديد موقعك...' : 'Getting your location...');
        navigator.geolocation.getCurrentPosition(pos => {
            const lat = pos.coords.latitude, lng = pos.coords.longitude, acc = pos.coords.accuracy || 50;
            if (gpsCircle) map.removeLayer(gpsCircle);
            gpsCircle = L.circle([lat, lng], {
                radius: acc, color: '#000', fillColor: '#000', fillOpacity: 0.07, weight: 1.5
            }).addTo(map);
            window['mapFly_' + FN](lat, lng, '');
            if (btnTxt) { btnTxt.textContent = '✓'; setTimeout(() => btnTxt.textContent = 'GPS', 2500); }
        }, () => {
            if (btnTxt) btnTxt.textContent = 'GPS';
            setStatus(IS_AR ? 'تعذر الوصول للـ GPS' : 'GPS unavailable');
        }, { enableHighAccuracy: true, timeout: 12000, maximumAge: 0 });
    };

    window.addEventListener('map-refresh-{{ $mapId }}', () => {
        if (map) setTimeout(() => map.invalidateSize(), 200);
    });

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else { init(); }
})();
</script>

@if(!isset($pushScriptsOnce_map_spin))
@php($pushScriptsOnce_map_spin = true)
<style>
@keyframes spin { to { transform: rotate(360deg); } }
</style>
@endif
@endpush
