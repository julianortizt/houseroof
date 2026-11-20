@extends('layouts.app')

@section('title', __('Project Simulator') . ' — ' . config('platform.name'))
@section('meta_description', __('Sube tu foto, define zonas de piso, y aplica acabados reales con iluminación intacta.'))

@section('content')
<style>
  .sim-wrap {
    background: radial-gradient(circle at top left, rgba(217,119,6,0.12), transparent 25%), linear-gradient(180deg, #1a1814 0%, #14110f 100%);
    color: #f5f0e8; font-family: 'Inter', system-ui, sans-serif; min-height: 100vh;
  }
  .sim-wrap canvas { display: block; }
  .sim-wrap input[type=range] { accent-color: #D97706; }
  .sim-mode-toggle { display:flex; gap:6px; margin-bottom:10px; padding:4px; background:#201d1b; border:1px solid #3b3732; border-radius:12px; }
  .sim-mode-toggle button { flex:1; border-radius:10px; border:1px solid #4a433d; background:rgba(41,37,36,0.8); color:#c7bfb6; cursor:pointer; padding:9px 6px; font-size:11px; font-weight:700; letter-spacing:0.02em; transition:all .18s ease; }
  .sim-mode-toggle button:hover { border-color:#6b5b4f; color:#f0e9e2; }
  .sim-mode-toggle button.active { background: linear-gradient(135deg,#D97706,#F59E0B); color:#fff; border-color:#D97706; box-shadow:0 8px 20px rgba(245,158,11,0.28), inset 0 1px 0 rgba(255,255,255,0.24); }
  .sim-swatch-button { background: linear-gradient(180deg, rgba(42,38,34,0.96), rgba(26,23,20,0.96)); border: 2px solid rgba(120,108,96,0.8); box-shadow: inset 0 1px 0 rgba(255,255,255,0.06), 0 0 0 1px rgba(0,0,0,0.2); transition: transform .15s ease, border-color .15s ease, box-shadow .15s ease; }
  .sim-swatch-button:hover { transform: translateY(-1px); border-color: rgba(196,139,58,0.9); }
  .sim-swatch-button.is-active { background: linear-gradient(180deg, rgba(89,58,23,0.96), rgba(42,29,16,0.96)); border-color: rgba(245,158,11,0.96); box-shadow: 0 0 0 2px rgba(245,158,11,0.28), inset 0 1px 0 rgba(255,255,255,0.08); }
  .sim-toolbar button, .sim-tabs button { transition: all .18s ease; }
  .sim-toolbar button:hover, .sim-tabs button:hover { transform: translateY(-1px); filter: brightness(1.03); }
  .sim-slider-handle { position:absolute;top:0;bottom:0;width:3px;background:#D97706;cursor:ew-resize;z-index:10; box-shadow:0 0 8px rgba(217,119,6,0.5); }
  .sim-slider-handle::after { content:'';position:absolute;top:50%;left:50%;transform:translate(-50%,-50%); width:28px;height:28px;border-radius:50%;background:#D97706;border:2px solid #fff; box-shadow:0 2px 8px rgba(0,0,0,0.4); }
  .sim-slider-label { position:absolute;top:8px;padding:3px 8px;border-radius:6px;font-size:10px;font-weight:700;letter-spacing:.04em;z-index:11;pointer-events:none; }
  .sim-modal-overlay { position:fixed;inset:0;background:rgba(0,0,0,0.7);z-index:100;display:flex;align-items:center;justify-content:center; backdrop-filter:blur(4px); }
  .sim-modal-card { background:#1c1917;border:1px solid #44403c;border-radius:16px;padding:28px;max-width:400px;width:90%; box-shadow:0 20px 60px rgba(0,0,0,0.5); }
  .sim-modal-card input { width:100%;background:#292524;color:#f5f0e8;border:1px solid #44403c;border-radius:8px;padding:10px 12px;font-size:13px; margin-bottom:10px;outline:none;transition:border-color .15s; }
  .sim-modal-card input:focus { border-color:#D97706; }
  .sim-modal-card input::placeholder { color:#78716c; }
  .sim-area-badge { position:absolute;bottom:8px;right:8px;background:rgba(0,0,0,0.75);border:1px solid #44403c; border-radius:8px;padding:4px 10px;font-size:11px;color:#D97706;font-weight:600;z-index:5;pointer-events:none; backdrop-filter:blur(4px); }
  @media (max-width: 768px) {
    .sim-sidebar { width: 100% !important; } .sim-main-col { flex-direction: column !important; }
    .sim-room-grid { grid-template-columns: repeat(3, 1fr) !important; } .sim-swatch-grid { grid-template-columns: repeat(3, 1fr) !important; }
    .sim-hero h1 { font-size: 16px !important; } .sim-hero p { font-size: 11px !important; }
    .sim-tabs button { font-size: 11px !important; padding: 8px 6px !important; } .sim-toolbar button { font-size: 11px !important; padding: 6px 10px !important; }
  }
  @media (max-width: 480px) { .sim-room-grid { grid-template-columns: repeat(2, 1fr) !important; } .sim-swatch-grid { grid-template-columns: repeat(2, 1fr) !important; } }
</style>

<div x-data="simulator" x-init="init()" class="sim-wrap">

  <!-- HERO -->
  <div class="sim-hero" style="background:linear-gradient(135deg,#292524,#1c1917);padding:20px 0;border-bottom:1px solid #44403c;">
    <div style="max-width:1200px;margin:0 auto;padding:0 16px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;">
      <div style="min-width:0;">
        <h1 style="font-size:22px;font-weight:700;margin:0 0 6px;white-space:nowrap;"><span style="color:#D97706;">{{ config('platform.name') }}</span> — <span>{{ __('Project Simulator') }}</span></h1>
        <p style="font-size:13px;color:#a8a29e;margin:0;">{{ __('Sube tu foto, define zonas de piso, y aplica acabados reales con iluminación intacta.') }}</p>
      </div>
      <div style="display:flex;gap:8px;flex-wrap:wrap;">
        <button @click="$refs.fileInput.click()" style="background:#D97706;color:#fff;border:none;padding:12px 24px;border-radius:10px;font-size:14px;font-weight:600;cursor:pointer;white-space:nowrap;box-shadow:0 2px 8px rgba(217,119,6,0.3);">📷 {{ __('Subir mi foto') }}</button>
        <button x-show="hasImage" @click="shareWhatsApp()" style="background:#25D366;color:#fff;border:none;padding:12px 24px;border-radius:10px;font-size:14px;font-weight:600;cursor:pointer;white-space:nowrap;box-shadow:0 2px 8px rgba(37,211,102,0.3);">💬 {{ __('Compartir') }}</button>
      </div>
      <input type="file" x-ref="fileInput" @change="handleUpload($event)" accept="image/*" style="display:none;">
    </div>
  </div>

  <!-- MAIN LAYOUT -->
  <div class="sim-main-col" style="max-width:1200px;margin:0 auto;padding:16px;display:flex;gap:16px;">
    <!-- LEFT: canvas area -->
    <div style="flex:1;min-width:0;display:flex;flex-direction:column;gap:12px;">
      <!-- Tabs -->
      <div class="sim-tabs" style="display:flex;gap:6px;">
        <button data-sim-action="setMode" data-sim-value="quad" @click="mode='quad'" :style="mode==='quad'?'background:#D97706;color:#fff;border-color:#D97706;font-weight:600;':'background:#292524;color:#a8a29e;border-color:#44403c;'" style="flex:1;padding:10px;border-radius:10px;font-size:13px;border:1px solid;cursor:pointer;">📐 {{ __('Perspectiva (4 puntos)') }}</button>
        <button data-sim-action="setMode" data-sim-value="mask" @click="mode='mask';curveMode=false" :style="mode==='mask'?'background:#0891b2;color:#fff;border-color:#0891b2;font-weight:600;':'background:#292524;color:#a8a29e;border-color:#44403c;'" style="flex:1;padding:10px;border-radius:10px;font-size:13px;border:1px solid;cursor:pointer;">✂️ {{ __('Contorno (puntos ilimitados)') }}</button>
        <button data-sim-action="setMode" data-sim-value="curve" @click="mode='curve'" :style="mode==='curve'?'background:#059669;color:#fff;border-color:#059669;font-weight:600;':'background:#292524;color:#a8a29e;border-color:#44403c;'" style="flex:1;padding:10px;border-radius:10px;font-size:13px;border:1px solid;cursor:pointer;">〰️ {{ __('Curvo (piscinas)') }}</button>
      </div>
      <!-- Hint -->
      <div style="background:#292524;border:1px solid #44403c;border-radius:10px;padding:12px 14px;font-size:12px;color:#a8a29e;line-height:1.5;">
        <template x-if="!hasImage"><span>📷 <b style="color:#D97706;">{{ __('Selecciona un espacio') }}</b> {{ __('o sube tu foto') }}.</span></template>
        <template x-if="hasImage && selectionMode==='zones'"><span>📐 <b style="color:#D97706;">{{ __('Modo Zonas') }}</b>: {{ __('Arrastra los 4 puntos naranjas') }}.</span></template>
        <template x-if="hasImage && selectionMode==='multioutline' && mode==='quad'"><span>✂️ <b style="color:#0891b2;">{{ __('Modo Múltiples acabados') }}</b>: {{ __('Selecciona una sub-zona y elige un acabado diferente. Arrastra los puntos naranjas para ajustar.') }}</span></template>
        <template x-if="hasImage && selectionMode==='multioutline' && (mode==='mask'||mode==='curve')"><span>✂️ <b style="color:#0891b2;">{{ __('Clic para agregar puntos') }}</b>. <b>{{ __('Doble clic para eliminar') }}</b>. {{ __('Cada sub-zona puede tener un acabado diferente.') }}</span></template>
      </div>
      <!-- CANVAS -->
      <div id="canvasWrap" style="position:relative;background:#0c0a09;border:1px solid #44403c;border-radius:12px;overflow:hidden;min-height:350px;aspect-ratio:16/10;">
        <canvas id="mainCanvas" x-ref="mainCanvas" style="width:100%;height:100%;display:block;"></canvas>
        <canvas id="overlayCanvas" x-ref="overlayCanvas" style="position:absolute;top:0;left:0;width:100%;height:100%;touch-action:none;"></canvas>
        <div class="sim-area-badge" x-show="hasImage" x-text="areaDisplayText"></div>
        <template x-if="hasImage && sliderActive">
          <div>
            <div class="sim-slider-label" :style="'left:'+(sliderPos*100)+'%;transform:translateX(-50%);background:#D97706;color:#fff;'" x-text="{{ __('Después') }}"></div>
            <div class="sim-slider-label" :style="'right:'+(100-sliderPos*100)+'%;transform:translateX(50%);background:#78716c;color:#fff;'" x-text="{{ __('Original') }}"></div>
            <div class="sim-slider-handle" :style="'left:'+(sliderPos*100)+'%'" @mousedown="startSliderDrag($event)" @touchstart.prevent="startSliderDrag($event)"></div>
          </div>
        </template>
        <div x-show="!hasImage" style="position:absolute;inset:0;display:flex;flex-direction:column;align-items:center;justify-content:center;color:#6b6358;pointer-events:none;z-index:2;">
          <div style="font-size:48px;opacity:.4;margin-bottom:12px;">📷</div>
          <p style="font-size:13px;text-align:center;">{{ __('Selecciona un espacio o sube tu foto') }}</p>
        </div>
      </div>
      <!-- Toolbar -->
      <div class="sim-toolbar" style="display:flex;gap:6px;flex-wrap:wrap;">
        <button @click="_undo()" :style="historyIdx<1?'opacity:0.35;pointer-events:none;':''" title="Ctrl+Z" style="background:#292524;border:1px solid #44403c;color:#f5f0e8;padding:8px 12px;border-radius:10px;font-size:14px;cursor:pointer;">↶</button>
        <button @click="_redo()" :style="historyIdx>=history.length-1?'opacity:0.35;pointer-events:none;':''" title="Ctrl+Y" style="background:#292524;border:1px solid #44403c;color:#f5f0e8;padding:8px 12px;border-radius:10px;font-size:14px;cursor:pointer;">↷</button>
        <div style="width:1px;background:#44403c;margin:2px 2px;"></div>
        <button data-sim-action="resetCorners" @click="resetCorners()" style="background:#292524;border:1px solid #44403c;color:#f5f0e8;padding:8px 16px;border-radius:10px;font-size:12px;cursor:pointer;">↺ {{ __('Reiniciar') }}</button>
        <button data-sim-action="resetMask" @click="resetMask()" style="background:#292524;border:1px solid #44403c;color:#f5f0e8;padding:8px 16px;border-radius:10px;font-size:12px;cursor:pointer;">◻ {{ __('Contorno') }}</button>
        <button data-sim-action="undoLastPoint" @click="undoLastPoint()" style="background:#292524;border:1px solid #44403c;color:#f5f0e8;padding:8px 16px;border-radius:10px;font-size:12px;cursor:pointer;">↶ {{ __('Punto') }}</button>
        <button data-sim-action="clearSelection" @click="clearSelection()" style="background:#292524;border:1px solid #44403c;color:#f5f0e8;padding:8px 16px;border-radius:10px;font-size:12px;cursor:pointer;">⌫ {{ __('Limpiar') }}</button>
        <button @click="deleteActiveZone()" x-show="zones.length>0" style="background:#7f1d1d;border:1px solid #991b1b;color:#fca5a5;padding:8px 16px;border-radius:10px;font-size:12px;cursor:pointer;">🗑 {{ __('Eliminar zona') }}</button>
        <button data-sim-action="setMode" data-sim-value="mask" @click="mode='mask'" x-show="mode==='curve'" style="background:#292524;border:1px solid #44403c;color:#f5f0e8;padding:8px 16px;border-radius:10px;font-size:12px;cursor:pointer;">✂️ {{ __('Rectas') }}</button>
        <button data-sim-action="setMode" data-sim-value="curve" @click="mode='curve'" x-show="mode==='mask'" style="background:#292524;border:1px solid #44403c;color:#f5f0e8;padding:8px 16px;border-radius:10px;font-size:12px;cursor:pointer;">〰️ {{ __('Curvas') }}</button>
        <button @click="sliderActive=!sliderActive;render()" :style="sliderActive?'background:#0891b2;color:#fff;border-color:#0891b2;':'background:#292524;border:1px solid #44403c;color:#f5f0e8;'" style="padding:8px 16px;border-radius:10px;font-size:12px;cursor:pointer;"> <=> {{ __('Comparar') }}</button>
        <button @mousedown="showOriginal=true;render()" @mouseup="showOriginal=false;render()" @mouseleave="showOriginal=false;render()" style="background:#292524;border:1px solid #44403c;color:#f5f0e8;padding:8px 16px;border-radius:10px;font-size:12px;cursor:pointer;">👁 {{ __('Original') }}</button>
        <button @click="download()" style="background:#D97706;border:1px solid #D97706;color:#fff;padding:8px 16px;border-radius:10px;font-size:12px;font-weight:600;cursor:pointer;box-shadow:0 2px 6px rgba(217,119,6,0.3);">⬇ {{ __('Descargar HD') }}</button>
      </div>
    </div>

    <!-- RIGHT: sidebar -->
    <div class="sim-sidebar" style="width:310px;flex-shrink:0;display:flex;flex-direction:column;gap:14px;">
      <!-- Rooms -->
      <div style="background:#1c1917;border:1px solid #44403c;border-radius:12px;padding:16px;">
        <div style="font-size:11px;letter-spacing:.06em;text-transform:uppercase;color:#a8a29e;font-weight:600;margin-bottom:12px;display:flex;align-items:center;gap:8px;">{{ __('Espacios') }} <span style="flex:1;height:1px;background:#44403c;"></span></div>
        <div class="sim-room-grid" style="display:grid;grid-template-columns:1fr 1fr;gap:6px;">
          <template x-for="room in rooms" :key="room.id">
            <button @click="loadRoom(room)" :style="currentRoom===room.id?'border-color:#D97706;box-shadow:0 0 0 2px rgba(217,119,6,0.3);':'border-color:#44403c;'" style="border:2px solid;border-radius:10px;overflow:hidden;cursor:pointer;background:#292524;transition:all .15s;">
              <div style="aspect-ratio:1.4;overflow:hidden;position:relative;">
                <img :src="room.photo" :alt="room.name" style="width:100%;height:100%;object-fit:cover;display:block;" loading="lazy" x-on:error="$event.target.style.display='none';$event.target.nextElementSibling.style.display='flex';">
                <div style="display:none;width:100%;height:100%;align-items:center;justify-content:center;background:linear-gradient(135deg,#44403c,#292524);flex-direction:column;gap:4px;"><span style="font-size:20px;">🏠</span><span style="font-size:9px;color:#a8a29e;" x-text="room.name"></span></div>
                <div style="position:absolute;bottom:0;left:0;right:0;padding:4px 6px;background:linear-gradient(transparent,rgba(0,0,0,0.7));"><span style="font-size:10px;font-weight:500;color:#fff;" x-text="room.name"></span></div>
              </div>
            </button>
          </template>
        </div>
      </div>
      <!-- Zones -->
      <div style="background:#1c1917;border:1px solid #44403c;border-radius:12px;padding:16px;">
        <div style="font-size:11px;letter-spacing:.06em;text-transform:uppercase;color:#a8a29e;font-weight:600;margin-bottom:12px;display:flex;align-items:center;gap:8px;">{{ __('Zonas de piso') }} <span style="flex:1;height:1px;background:#44403c;"></span></div>
        <div class="sim-mode-toggle">
          <button data-sim-action="setSelectionMode" data-sim-value="zones" :class="{ active: selectionMode === 'zones' }" @click="setSelectionMode('zones')">Zonas</button>
          <button data-sim-action="setSelectionMode" data-sim-value="multioutline" :class="{ active: selectionMode === 'multioutline' }" @click="setSelectionMode('multioutline')">Múltiples outlines</button>
        </div>
        <div x-show="selectionMode==='zones'" style="display:flex;flex-direction:column;gap:4px;">
          <template x-for="(zone, zi) in zones" :key="zone.id">
            <div @click="activeZoneIdx=zi;render()" :style="zi===activeZoneIdx?'border-color:#D97706;background:#292524;':'border-color:#44403c;'" style="display:flex;align-items:center;gap:8px;padding:8px 10px;border-radius:10px;background:#292524;border:1px solid;cursor:pointer;font-size:12px;transition:all .15s;">
              <div style="width:12px;height:12px;border-radius:3px;flex-shrink:0;" :style="'background:'+zoneColors[zi%zoneColors.length]"></div>
              <span style="flex:1;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;color:#eeeae0;" x-text="zone.name+' · '+zone.swatch.name"></span>
              <button x-show="zones.length>1" @click.stop="removeZone(zi)" style="color:#a39c8a;font-size:13px;padding:0 4px;cursor:pointer;border:none;background:none;">✕</button>
            </div>
          </template>
        </div>
        <div x-show="selectionMode==='multioutline'" style="display:flex;flex-direction:column;gap:4px;">
          <template x-if="zones[activeZoneIdx]">
            <template x-for="(subZone, si) in (zones[activeZoneIdx]?.subZones || [])" :key="subZone.id">
              <div @click="activeSubZoneIdx=si;render()" :style="si===activeSubZoneIdx?'border-color:#0891b2;background:#172a33;':'border-color:#44403c;'" style="display:flex;align-items:center;gap:8px;padding:8px 10px;border-radius:10px;background:#292524;border:1px solid;cursor:pointer;font-size:12px;transition:all .15s;">
                <div style="width:12px;height:12px;border-radius:3px;flex-shrink:0;" :style="'background:'+subZone.swatch.base"></div>
                <span style="flex:1;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;color:#eeeae0;" x-text="subZone.name+' · '+subZone.swatch.name"></span>
                <button @click.stop="removeSubZone(si)" style="color:#a39c8a;font-size:13px;padding:0 4px;cursor:pointer;border:none;background:none;">✕</button>
              </div>
            </template>
          </template>
        </div>
        <div style="display:flex;gap:6px;margin-top:10px;">
          <button data-sim-action="addSelection" @click="selectionMode==='multioutline'?addSubZone():addZone()" style="flex:1;padding:10px;font-size:12px;font-weight:600;color:#D97706;border:1px dashed #92400e;border-radius:10px;cursor:pointer;background:none;">+ {{ __('Nueva selección') }}</button>
          <button data-sim-action="duplicateZone" @click="duplicateActiveZone()" x-show="hasImage" style="flex:1;padding:10px;font-size:12px;font-weight:600;color:#60a5fa;border:1px dashed #1e40af;border-radius:10px;cursor:pointer;background:none;">📋 {{ __('Duplicar') }}</button>
        </div>
      </div>
      <!-- Swatches -->
      <div style="background:#1c1917;border:1px solid #44403c;border-radius:12px;padding:16px;">
        <div style="font-size:11px;letter-spacing:.06em;text-transform:uppercase;color:#a8a29e;font-weight:600;margin-bottom:12px;display:flex;align-items:center;gap:8px;">{{ __('Acabado') }} <span style="flex:1;height:1px;background:#44403c;"></span></div>
        <div class="sim-swatch-grid" style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:6px;">
          <template x-for="sw in catalog" :key="sw.id">
            <button class="sim-swatch-button" :class="{ 'is-active': (selectionMode === 'multioutline' ? (zones[activeZoneIdx]?.subZones?.[activeSubZoneIdx]?.swatch?.id ?? zones[activeZoneIdx]?.swatch?.id) : zones[activeZoneIdx]?.swatch?.id) === sw.id }" @click="setFinish(sw)" style="border-radius:10px;overflow:hidden;cursor:pointer;transition:all .15s;width:100%;padding:0;">
              <canvas :id="'sw-'+sw.id" width="200" height="143" style="display:block;width:100%;border-radius:8px 8px 0 0;background:rgba(15,14,12,0.9);"></canvas>
              <div style="padding:5px 6px 6px;text-align:center;background:rgba(26,24,22,0.95);"><span style="font-size:10px;font-weight:700;color:#f5f0e8;" x-text="sw.name"></span></div>
            </button>
          </template>
        </div>
      </div>
      <!-- Settings -->
      <div style="background:#1c1917;border:1px solid #44403c;border-radius:12px;padding:16px;">
        <div style="font-size:11px;letter-spacing:.06em;text-transform:uppercase;color:#a8a29e;font-weight:600;margin-bottom:12px;display:flex;align-items:center;gap:8px;">{{ __('Ajustes') }} <span style="flex:1;height:1px;background:#44403c;"></span></div>
        <div style="display:flex;justify-content:space-between;align-items:center;margin:8px 0;font-size:12px;"><span style="color:#a8a29e;">{{ __('Tamaño chip') }}</span><span style="color:#D97706;font-weight:600;font-size:11px;" x-text="chipScale+'%'"></span></div>
        <input type="range" min="30" max="250" x-model.number="chipScale" @input="onSettingChange()" style="width:100%;margin-bottom:4px;">
        <div style="display:flex;justify-content:space-between;align-items:center;margin:8px 0;font-size:12px;"><span style="color:#a8a29e;">{{ __('Densidad') }}</span><span style="color:#D97706;font-weight:600;font-size:11px;" x-text="density"></span></div>
        <input type="range" min="15" max="150" x-model.number="density" @input="onSettingChange()" style="width:100%;margin-bottom:4px;">
        <div style="display:flex;justify-content:space-between;align-items:center;margin:8px 0;font-size:12px;"><span style="color:#a8a29e;">{{ __('Intensidad') }}</span><span style="color:#D97706;font-weight:600;font-size:11px;" x-text="intensity+'%'"></span></div>
        <input type="range" min="10" max="100" x-model.number="intensity" @input="onSettingChange()" style="width:100%;margin-bottom:4px;">
        <div style="margin-top:8px;padding-top:8px;border-top:1px solid #44403c;">
          <div style="display:flex;justify-content:space-between;align-items:center;margin:8px 0;font-size:12px;"><span style="color:#a8a29e;">{{ __('Escala (m por px)') }}</span><span style="color:#D97706;font-weight:600;font-size:11px;" x-text="metersPerPixel.toFixed(4)"></span></div>
          <input type="range" min="0.001" max="0.1" step="0.001" x-model.number="metersPerPixel" @input="render()" style="width:100%;">
          <p style="font-size:10px;color:#78716c;margin:4px 0 0;">{{ __('Ajusta hasta que el área mostrada coincida con la real') }}</p>
        </div>
        <div style="margin-top:10px;padding-top:10px;border-top:1px solid #44403c;">
          <button @click="showAdvanced=!showAdvanced" style="font-size:12px;color:#a8a29e;text-decoration:underline;cursor:pointer;background:none;border:none;">⚙ {{ __('Opciones avanzadas') }}</button>
          <div x-show="showAdvanced" style="margin-top:8px;">
            <label style="font-size:12px;color:#a8a29e;display:block;margin-bottom:4px;">{{ __('Modo de mezcla') }}</label>
            <select x-model="blendMode" @change="render()" style="width:100%;background:#292524;color:#f5f0e8;border:1px solid #44403c;border-radius:8px;padding:6px 8px;font-size:12px;">
              <option value="color">{{ __('Conserva luz real') }}</option>
              <option value="hue">{{ __('Tono') }}</option>
              <option value="soft-light">{{ __('Luz suave') }}</option>
              <option value="overlay">{{ __('Superposición') }}</option>
              <option value="multiply">{{ __('Multiplicar') }}</option>
            </select>
            <div style="display:flex;justify-content:space-between;align-items:center;margin:8px 0;font-size:12px;"><span style="color:#a8a29e;">{{ __('Malla') }}</span><span style="color:#D97706;font-weight:600;font-size:11px;" x-text="meshDiv"></span></div>
            <input type="range" min="12" max="50" x-model.number="meshDiv" @input="render()" style="width:100%;">
          </div>
        </div>
      </div>
      <!-- Quote -->
      <div style="background:#1c1917;border:1px solid #44403c;border-radius:12px;padding:16px;">
        <div style="font-size:11px;letter-spacing:.06em;text-transform:uppercase;color:#a8a29e;font-weight:600;margin-bottom:12px;display:flex;align-items:center;gap:8px;">{{ __('Cotización') }} <span style="flex:1;height:1px;background:#44403c;"></span></div>
        <div style="display:flex;gap:8px;margin-bottom:10px;">
          <div style="flex:1;"><label style="font-size:11px;color:#a8a29e;display:block;margin-bottom:3px;">{{ __('Área (m²)') }}</label><input type="number" min="1" max="9999" x-model.number="areaM2" @input="updateQuote()" style="width:100%;background:#292524;color:#f5f0e8;border:1px solid #44403c;border-radius:8px;padding:6px 8px;font-size:12px;"></div>
          <div style="flex:1;"><label style="font-size:11px;color:#a8a29e;display:block;margin-bottom:3px;">{{ __('Mano obra /m² (€)') }}</label><input type="number" min="0" max="200" x-model.number="laborRate" @input="updateQuote()" style="width:100%;background:#292524;color:#f5f0e8;border:1px solid #44403c;border-radius:8px;padding:6px 8px;font-size:12px;"></div>
        </div>
        <template x-for="(zone, zi) in zones" :key="zone.id+'-q'">
          <div>
            <div style="display:flex;justify-content:space-between;font-size:11px;color:#a8a29e;margin:4px 0;">
              <span x-text="zone.name+(selectionMode==='multioutline'&&zone.subZones&&zone.subZones.length>1?'':' ('+zone.swatch.name+')')"></span>
              <span style="font-weight:600;color:#f5f0e8;" x-text="'€'+Math.round(_zoneQuote(zone))"></span>
            </div>
            <template x-if="selectionMode==='multioutline'&&zone.subZones&&zone.subZones.length>1">
              <div style="padding-left:10px;">
                <template x-for="(sz,sii) in zone.subZones" :key="sz.id+'-sq'">
                  <div style="display:flex;justify-content:space-between;font-size:10px;color:#78716c;margin:2px 0;">
                    <span x-text="'  · '+sz.name+' ('+sz.swatch.name+')'"></span>
                    <span style="color:#a8a29e;" x-text="'€'+Math.round((sz.swatch.pricePerM2||50)*areaM2+laborRate*areaM2)"></span>
                  </div>
                </template>
              </div>
            </template>
          </div>
        </template>
        <div style="margin-top:10px;padding-top:10px;border-top:1px solid #44403c;display:flex;justify-content:space-between;align-items:center;">
          <span style="font-size:13px;font-weight:700;color:#f5f0e8;">{{ __('Total') }}</span>
          <span style="font-size:16px;font-weight:800;color:#D97706;" x-text="'€'+totalQuote"></span>
        </div>
        <a href="{{ route('quote.create') }}" style="display:block;margin-top:12px;padding:12px;text-align:center;background:#D97706;color:#fff;border-radius:10px;font-size:13px;font-weight:600;text-decoration:none;box-shadow:0 2px 8px rgba(217,119,6,0.3);">{{ __('Solicitar cotización') }} →</a>
      </div>
    </div>
  </div>

  <!-- LEAD CAPTURE MODAL -->
  <div x-show="showLeadModal" x-cloak class="sim-modal-overlay" @click.self="showLeadModal=false">
    <div class="sim-modal-card">
      <h3 style="font-size:18px;font-weight:700;margin:0 0 6px;color:#f5f0e8;">📸 {{ __('Descargar en alta resolución') }}</h3>
      <p style="font-size:12px;color:#a8a29e;margin:0 0 16px;">{{ __('Déjanos tus datos para enviarte la imagen y una cotización personalizada.') }}</p>
      <input type="text" x-model="leadName" placeholder="{{ __('Nombre completo') }}">
      <input type="email" x-model="leadEmail" placeholder="{{ __('Email') }}">
      <input type="tel" x-model="leadPhone" placeholder="{{ __('Teléfono (opcional)') }}">
      <div style="display:flex;gap:8px;margin-top:6px;">
        <button @click="showLeadModal=false" style="flex:1;padding:10px;font-size:13px;border-radius:8px;border:1px solid #44403c;background:#292524;color:#a8a29e;cursor:pointer;">{{ __('Cancelar') }}</button>
        <button @click="submitLeadAndDownload()" :style="(!leadName||!leadEmail||leadSubmitting)?'opacity:0.5;pointer-events:none;':''" style="flex:2;padding:10px;font-size:13px;font-weight:600;border-radius:8px;border:none;background:#D97706;color:#fff;cursor:pointer;box-shadow:0 2px 8px rgba(217,119,6,0.3);">{{ __('Descargar gratis') }} ⬇</button>
      </div>
      <p x-show="leadError" style="color:#ef4444;font-size:11px;margin:8px 0 0;" x-text="leadError"></p>
    </div>
  </div>

</div>
@endsection

@section('scripts')
<script>
function simulator() {
  return {
    hasImage: false, currentRoom: null, currentRoomApiId: null, mode: 'quad', curveMode: false, showOriginal: false, showAdvanced: false,
    selectionMode: 'zones', activeZoneIdx: 0, activeSubZoneIdx: 0, zones: [], texCache: {}, dragIdx: -1, W: 0, H: 0, baseImage: null,
    chipScale: 100, density: 65, intensity: 92, blendMode: 'color', meshDiv: 28,
    areaM2: 20, laborRate: 35, totalQuote: 0,
    zoneColors: ['#d4952e','#4a9aa8','#a07060','#609060','#9060a0','#6080b0'],
    history: [], historyIdx: -1, _maxHistory: 60,
    sliderActive: false, sliderPos: 0.5, _sliderDragging: false, _originalCanvas: null,
    metersPerPixel: 0.02, areaDisplayText: '',
    showLeadModal: false, leadName: '', leadEmail: '', leadPhone: '', leadError: '', leadSubmitting: false, leadSubmitted: false,
    baseUrl: '{{ asset("") }}',
    textureAssets: {}, _textureKeys: {concrete:'simulator-textures/concrete_color.jpg',granite:'simulator-textures/granite_color.jpg',metal:'simulator-textures/metal_color.jpg'},
    loadedTextures: {},
    imageCache: {},
    roomFileMap: {
      'simulator-rooms/cocina.jpg':'simulator-rooms/cocina.jpg','simulator-rooms/cocina-2.jpg':'simulator-rooms/cocina-2.jpg',
      'simulator-rooms/sala-de-estar.jpg':'simulator-rooms/SALA DE ESTAR.jpg','simulator-rooms/comedor.jpg':'simulator-rooms/comedor.jpg',
      'simulator-rooms/piscina.jpg':'simulator-rooms/piscina.jpg','simulator-rooms/entrada.jpg':'simulator-rooms/entrada.jpg',
      'simulator-rooms/habitacion.jpg':'simulator-rooms/HABITACION.jpg','simulator-rooms/terraza.jpg':'simulator-rooms/terraza.jpg',
      'simulator-rooms/garaje.jpg':'simulator-rooms/garaje.jpg','simulator-rooms/bano.jpg':'simulator-rooms/BAÑO.jpg',
      'simulator-rooms/oficina.jpg':'simulator-rooms/oficina.jpg','simulator-rooms/patio.jpg':'simulator-rooms/patio.jpg',
      'simulator-rooms/sotano.jpg':'simulator-rooms/sotano.jpg','simulator-rooms/area-comercial.jpg':'simulator-rooms/area-comercial.jpg',
      'simulator-rooms/BAÑO.jpg':'simulator-rooms/BAÑO.jpg','simulator-rooms/HABITACION.jpg':'simulator-rooms/HABITACION.jpg',
      'simulator-rooms/SALA DE ESTAR.jpg':'simulator-rooms/SALA DE ESTAR.jpg','simulator-rooms/COCINA 2.jpg':'simulator-rooms/COCINA 2.jpg',
    },

    normalizeAssetUrl(path) {
      if (!path) return path;
      const url = String(path).trim();
      if (/^https?:\/\//i.test(url)) return url;
      const relative = url.replace(/\\/g, '/').replace(/^\/+/, '').replace(/^\.?\//, '');
      return `${this.baseUrl.replace(/\/+$/, '')}/${relative}`;
    },
    resolveAssetPath(path) {
      if (!path) return path;
      const clean = String(path).replace(/\\/g, '/').replace(/^\/+/, '');
      const direct = this.roomFileMap[clean] || clean;
      const lowered = direct.toLowerCase();
      const matched = Object.keys(this.roomFileMap).find((key) => key.toLowerCase() === lowered);
      return matched ? this.roomFileMap[matched] : direct;
    },

    // ── Fallback catalog (used if API fails) ──
    _fallbackCatalog: [
      {id:'c1',name:'Gris Acero',base:'#3a3d40',chips:['#c7c9cb','#8a8d90','#1f2123','#e8e6df'],density:1.0,chipShape:'angular',textureType:'granite',tintIntensity:0.55,pricePerM2:52},
      {id:'c2',name:'Arena Beige',base:'#3f372a',chips:['#e8d9b8','#a9906a','#5c4a34','#f4ecd9'],density:0.9,chipShape:'angular',textureType:'granite',tintIntensity:0.50,pricePerM2:52},
      {id:'c3',name:'Carbón Multicolor',base:'#1b1b1c',chips:['#d64545','#3d7dcf','#e8c93a','#f2f2ef','#4a4a4a'],density:1.1,chipShape:'angular',textureType:'granite',tintIntensity:0.45,pricePerM2:58},
      {id:'c4',name:'Cuarzo Champagne',base:'#4a3f2e',chips:['#e9cfa0','#b99b6b','#f6e9cf','#8a6f45'],density:0.85,chipShape:'round',textureType:'concrete',tintIntensity:0.50,pricePerM2:55},
      {id:'c5',name:'Bronce Metálico',base:'#2b2016',chips:['#b5772f','#e0a95c','#8a5a22','#f0c98a'],metallic:true,density:0.6,chipShape:'flake',textureType:'metal',tintIntensity:0.65,pricePerM2:68},
      {id:'c6',name:'Blanco Ostra',base:'#e7e3d8',chips:['#c9c2ae','#a89f89','#f6f4ee','#dcd6c6'],density:0.7,chipShape:'round',textureType:'concrete',tintIntensity:0.40,pricePerM2:52},
      {id:'c7',name:'Marino Grafito',base:'#111820',chips:['#3c5872','#7d97ab','#1a232c','#c4d2dc'],density:0.95,chipShape:'angular',textureType:'granite',tintIntensity:0.60,pricePerM2:55},
      {id:'c8',name:'Terracota Rústico',base:'#3a2018',chips:['#c96a3e','#8f4526','#e3a878','#f1d4b0'],density:1.2,chipShape:'angular',textureType:'granite',tintIntensity:0.55,pricePerM2:58},
      {id:'c9',name:'Blanco Perla',base:'#d9d5c9',chips:['#c0bbaa','#efece2','#a39880','#d3ccb8'],density:0.65,chipShape:'round',textureType:'concrete',tintIntensity:0.35,pricePerM2:52},
      {id:'c10',name:'Verde Musgo',base:'#1e2b1c',chips:['#4c6b3f','#8fae6a','#26361f','#c9d9a0'],density:0.9,chipShape:'angular',textureType:'granite',tintIntensity:0.55,pricePerM2:55},
      {id:'c11',name:'Vino Tinto',base:'#2c1017',chips:['#6b1f2b','#a83b46','#3d0d14','#d98a91'],density:0.85,chipShape:'angular',textureType:'granite',tintIntensity:0.55,pricePerM2:58},
      {id:'c12',name:'Turquesa Caribe',base:'#0c2c2e',chips:['#1f8f8a','#7fd8cf','#0a4547','#c7f0e8'],density:0.95,chipShape:'round',textureType:'concrete',tintIntensity:0.50,pricePerM2:55},
      {id:'c13',name:'Negro Galaxia',base:'#0a0a0c',chips:['#3a3d52','#6f74a3','#c9cbe0','#1c1c22'],metallic:true,density:1.0,chipShape:'flake',textureType:'metal',tintIntensity:0.60,pricePerM2:68},
      {id:'c14',name:'Gris Perla Claro',base:'#66625a',chips:['#c9c5ba','#3f3c37','#8f8b80','#e7e4db'],density:0.8,chipShape:'round',textureType:'concrete',tintIntensity:0.45,pricePerM2:52},
      {id:'c15',name:'Cobre Envejecido',base:'#21140c',chips:['#8a5228','#c98247','#4a2c17','#e0b183'],metallic:true,density:0.7,chipShape:'flake',textureType:'metal',tintIntensity:0.60,pricePerM2:68},
      {id:'c16',name:'Multicolor Fiesta',base:'#17171a',chips:['#e0483f','#f2b705','#2f8f4e','#3a6fd8','#e0e0e0'],density:1.3,chipShape:'angular',textureType:'granite',tintIntensity:0.40,pricePerM2:62},
    ],
    catalog: [],

    _fallbackRooms: [
      {id:'cocina',name:'Cocina',photo:'simulator-rooms/cocina.jpg'},
      {id:'cocina2',name:'Cocina 2',photo:'simulator-rooms/cocina-2.jpg'},
      {id:'sala',name:'Sala de estar',photo:'simulator-rooms/sala-de-estar.jpg'},
      {id:'comedor',name:'Comedor',photo:'simulator-rooms/comedor.jpg'},
      {id:'piscina',name:'Piscina',photo:'simulator-rooms/piscina.jpg'},
      {id:'entrada',name:'Entrada',photo:'simulator-rooms/entrada.jpg'},
      {id:'habitacion',name:'Habitación',photo:'simulator-rooms/habitacion.jpg'},
      {id:'terraza',name:'Terraza',photo:'simulator-rooms/terraza.jpg'},
      {id:'garaje',name:'Garaje',photo:'simulator-rooms/garaje.jpg'},
      {id:'bano',name:'Baño',photo:'simulator-rooms/bano.jpg'},
      {id:'oficina',name:'Oficina',photo:'simulator-rooms/oficina.jpg'},
      {id:'patio',name:'Patio',photo:'simulator-rooms/patio.jpg'},
      {id:'sotano',name:'Sótano',photo:'simulator-rooms/sotano.jpg'},
      {id:'areacomercial',name:'Área Comercial',photo:'simulator-rooms/area-comercial.jpg'},
    ],
    rooms: [],

    // ── INIT ──
    init() {
      Object.entries(this._textureKeys).forEach(([k,p])=>{this.textureAssets[k]=this.normalizeAssetUrl(this.resolveAssetPath(p));});
      // Load fallback rooms and normalize URLs
      this.rooms = this._fallbackRooms.map(r=>({...r, photo:this.normalizeAssetUrl(this.resolveAssetPath(r.photo))}));
      this.catalog = [...this._fallbackCatalog];
      // Try to fetch from API (finishes + rooms)
      this._fetchApiData();
      // Load textures
      Promise.all(Object.entries(this.textureAssets).map(([key,url])=>new Promise(resolve=>{
        const img=new Image();img.onload=()=>{const c=document.createElement('canvas');c.width=img.naturalWidth;c.height=img.naturalHeight;c.getContext('2d').drawImage(img,0,0);this.loadedTextures[key]=c;resolve();};img.onerror=()=>{this.loadedTextures[key]=null;resolve();};img.src=url;
      }))).then(()=>this.drawAllSwatches());
      this.$nextTick(()=>{
        this.$refs.overlayCanvas.addEventListener('pointerdown',e=>this.onPointerDown(e));
        this.$refs.overlayCanvas.addEventListener('pointermove',e=>this.onPointerMove(e));
        window.addEventListener('pointerup',()=>{this.dragIdx=-1;this._sliderDragging=false;});
        window.addEventListener('keydown',(event)=>this.handleKeydown(event));
        this.$refs.overlayCanvas.addEventListener('dblclick',e=>this.onDblClick(e));
      });
    },

    // ── FETCH FROM API (finishes + rooms from admin) ──
    async _fetchApiData() {
      try {
        const [finRes, roomRes] = await Promise.all([
          fetch('/api/simulador/finishes').then(r=>r.ok?r.json():null),
          fetch('/api/simulador/room-templates').then(r=>r.ok?r.json():null)
        ]);
        // Map API finishes to internal catalog format
        if (finRes && finRes.data && finRes.data.length) {
          const typeMap = {chips:'granite',solid:'concrete',metallic:'metal'};
          const shapeMap = {chips:'angular',solid:'round',metallic:'flake'};
          const apiCatalog = [];
          finRes.data.forEach(f => {
            (f.variants && f.variants.length ? f.variants : [{id:f.id+'_v',name:f.name,base_color_hex:'#888888',chip_density:40}]).forEach(v => {
              const hex = v.base_color_hex || '#888888';
              // Generate chip colors from the base hex
              const chips = this._generateChipColors(hex, f.type);
              apiCatalog.push({
                id: 'api_'+v.id,
                name: v.name || f.name,
                base: hex,
                chips: chips,
                density: (v.chip_density||40)/40,
                chipShape: shapeMap[f.type]||'angular',
                textureType: typeMap[f.type]||'granite',
                tintIntensity: 0.50,
                pricePerM2: f.price_per_m2 || 50,
                textureUrl: v.texture_color_url || '',
                previewUrl: v.preview_image_url || '',
                _apiFinishId: f.id,
                _apiVariantId: v.id,
              });
            });
          });
          if (apiCatalog.length) this.catalog = apiCatalog;
          this._loadCatalogImages();
        }
        // Map API room templates
        if (roomRes && roomRes.data && roomRes.data.length) {
          this.rooms = roomRes.data.map(t => ({
            id: 'room_'+t.id,
            name: t.name,
            photo: t.photo_url ? this.normalizeAssetUrl(t.photo_url) : '',
            _apiId: t.id,
          })).filter(r => r.photo);
        }
        this.$nextTick(()=>this.drawAllSwatches());
      } catch(e) { /* API unavailable, keep fallback data */ }
    },

    _loadCatalogImages(){
      const pending = this.catalog
        .filter(sw => sw.textureUrl && !this.imageCache[sw.id])
        .map(sw => new Promise(resolve => {
          const url = this.normalizeAssetUrl(sw.textureUrl);
          const img = new Image();
          img.crossOrigin = 'anonymous';
          img.onload = () => {
            this.imageCache[sw.id] = img;
            resolve(true);
          };
          img.onerror = () => resolve(false);
          img.src = url;
        }));
      if (!pending.length) { this.drawAllSwatches(); return; }
      Promise.all(pending).then(() => { this.texCache = {}; this.drawAllSwatches(); this.render(); });
    },

    // ── Generate chip colors from a base hex ──
    _generateChipColors(hex, type) {
      const r = parseInt(hex.slice(1,3),16), g = parseInt(hex.slice(3,5),16), b = parseInt(hex.slice(5,7),16);
      const lighten = (amt) => '#'+[r,g,b].map(c=>Math.min(255,c+amt).toString(16).padStart(2,'0')).join('');
      const darken = (amt) => '#'+[r,g,b].map(c=>Math.max(0,c-amt).toString(16).padStart(2,'0')).join('');
      if (type==='metallic') return [lighten(60),lighten(30),darken(40),lighten(90)];
      if (type==='solid') return [lighten(20),darken(20),lighten(40),darken(40)];
      return [lighten(50),lighten(20),darken(30),darken(60),lighten(80)];
    },

    // ── UNDO / REDO ──
    _serializeZones(){return JSON.parse(JSON.stringify(this.zones));},
    _pushHistory(){
      const snap={zones:this._serializeZones(),activeZoneIdx:this.activeZoneIdx,activeSubZoneIdx:this.activeSubZoneIdx};
      if(this.historyIdx<this.history.length-1){this.history=this.history.slice(0,this.historyIdx+1);}
      this.history.push(snap);
      if(this.history.length>this._maxHistory){this.history.shift();}
      this.historyIdx=this.history.length-1;
    },
    _undo(){if(this.historyIdx<1)return;this.historyIdx--;this._restoreHistory();},
    _redo(){if(this.historyIdx>=this.history.length-1)return;this.historyIdx++;this._restoreHistory();},
    _restoreHistory(){
      const snap=this.history[this.historyIdx];if(!snap)return;
      this.zones=JSON.parse(JSON.stringify(snap.zones));
      this.activeZoneIdx=snap.activeZoneIdx;this.activeSubZoneIdx=snap.activeSubZoneIdx;
      this.texCache={};this.render();this.updateQuote();
    },

    // ── HOMOGRAPHY ──
    computeHomography(q){const[p0,p1,p2,p3]=q;const dx1=p1.x-p2.x,dx2=p3.x-p2.x,dx3=p0.x-p1.x+p2.x-p3.x;const dy1=p1.y-p2.y,dy2=p3.y-p2.y,dy3=p0.y-p1.y+p2.y-p3.y;let g=0,h=0;if(!(Math.abs(dx3)<1e-9&&Math.abs(dy3)<1e-9)){const d=dx1*dy2-dx2*dy1;g=(dx3*dy2-dx2*dy3)/d;h=(dx1*dy3-dx3*dy1)/d;}return{a:p1.x-p0.x+g*p1.x,b:p3.x-p0.x+h*p3.x,c:p0.x,d:p1.y-p0.y+g*p1.y,e:p3.y-p0.y+h*p3.y,f:p0.y,g,h};},
    mapUV(H,u,v){const d=H.g*u+H.h*v+1;return{x:(H.a*u+H.b*v+H.c)/d,y:(H.d*u+H.e*v+H.f)/d};},
    getAffine(s0,s1,s2,d0,d1,d2){const denom=s0.x*(s1.y-s2.y)+s1.x*(s2.y-s0.y)+s2.x*(s0.y-s1.y);if(Math.abs(denom)<1e-9)return null;return{a:(d0.x*(s1.y-s2.y)+d1.x*(s2.y-s0.y)+d2.x*(s0.y-s1.y))/denom,c:(d0.x*(s2.x-s1.x)+d1.x*(s0.x-s2.x)+d2.x*(s1.x-s0.x))/denom,e:(d0.x*(s1.x*s2.y-s2.x*s1.y)+d1.x*(s2.x*s0.y-s0.x*s2.y)+d2.x*(s0.x*s1.y-s1.x*s0.y))/denom,b:(d0.y*(s1.y-s2.y)+d1.y*(s2.y-s0.y)+d2.y*(s0.y-s1.y))/denom,d:(d0.y*(s2.x-s1.x)+d1.y*(s0.x-s2.x)+d2.y*(s1.x-s0.x))/denom,f:(d0.y*(s1.x*s2.y-s2.x*s1.y)+d1.y*(s2.x*s0.y-s0.x*s2.y)+d2.y*(s0.x*s1.y-s1.x*s0.y))/denom};},
    drawTri(ctx,img,s0,s1,s2,d0,d1,d2){const t=this.getAffine(s0,s1,s2,d0,d1,d2);if(!t)return;ctx.save();ctx.beginPath();ctx.moveTo(d0.x,d0.y);ctx.lineTo(d1.x,d1.y);ctx.lineTo(d2.x,d2.y);ctx.closePath();ctx.clip();ctx.setTransform(t.a,t.b,t.c,t.d,t.e,t.f);ctx.drawImage(img,0,0);ctx.restore();},

    // ── IMPROVED TEXTURE GENERATION ──
    generateTexture(sw,resPx){
      const c=document.createElement('canvas');c.width=c.height=resPx;const ctx=c.getContext('2d');
      // Prefer real product circle photo (Teseris) as floor material
      const productImg=this.imageCache[sw.id];
      if(productImg){
        const iw=productImg.naturalWidth||productImg.width||512;
        const ih=productImg.naturalHeight||productImg.height||512;
        const tile=Math.max(180,Math.min(resPx,Math.round(resPx*0.45)));
        const scale=tile/Math.max(iw,ih);
        const dw=Math.max(1,Math.round(iw*scale)), dh=Math.max(1,Math.round(ih*scale));
        ctx.imageSmoothingEnabled=true; ctx.imageSmoothingQuality='high';
        for(let y=0;y<resPx;y+=dh){ for(let x=0;x<resPx;x+=dw){ ctx.drawImage(productImg,x,y,dw,dh); } }
        // light grain so it doesn’t look flat under lighting
        ctx.globalAlpha=0.04;
        for(let n=0;n<resPx*resPx*0.004;n++){ ctx.fillStyle=Math.random()>0.5?'#fff':'#000'; ctx.fillRect(Math.random()*resPx,Math.random()*resPx,1+Math.random(),1+Math.random()); }
        ctx.globalAlpha=1;
        return c;
      }
      const realTex=this.loadedTextures[sw.textureType];
      // Base texture with real material or procedural
      if(realTex){
        for(let y=0;y<resPx;y+=realTex.height)for(let x=0;x<resPx;x+=realTex.width)ctx.drawImage(realTex,x,y,realTex.width,realTex.height);
        ctx.globalCompositeOperation='multiply';ctx.globalAlpha=sw.tintIntensity||0.5;ctx.fillStyle=sw.base;ctx.fillRect(0,0,resPx,resPx);
        ctx.globalCompositeOperation='soft-light';ctx.globalAlpha=(sw.tintIntensity||0.5)*0.6;ctx.fillStyle=sw.base;ctx.fillRect(0,0,resPx,resPx);
        ctx.globalCompositeOperation='source-over';ctx.globalAlpha=1;
      } else {
        // Procedural base with Perlin-like noise
        ctx.fillStyle=sw.base;ctx.fillRect(0,0,resPx,resPx);
        const imgData=ctx.getImageData(0,0,resPx,resPx);const d=imgData.data;
        const br=parseInt(sw.base.slice(1,3),16),bg=parseInt(sw.base.slice(3,5),16),bb=parseInt(sw.base.slice(5,7),16);
        for(let y=0;y<resPx;y+=2){for(let x=0;x<resPx;x+=2){
          const n=(Math.sin(x*0.05)*Math.cos(y*0.07)+Math.sin(x*0.12+y*0.09))*15;
          const idx=(y*resPx+x)*4;d[idx]=Math.max(0,Math.min(255,br+n));d[idx+1]=Math.max(0,Math.min(255,bg+n));d[idx+2]=Math.max(0,Math.min(255,bb+n));
          if(x+1<resPx){const i2=(y*resPx+x+1)*4;d[i2]=d[idx];d[i2+1]=d[idx+1];d[i2+2]=d[idx+2];}
          if(y+1<resPx){const i3=((y+1)*resPx+x)*4;d[i3]=d[idx];d[i3+1]=d[idx+1];d[i3+2]=d[idx+2];}
          if(x+1<resPx&&y+1<resPx){const i4=((y+1)*resPx+x+1)*4;d[i4]=d[idx];d[i4+1]=d[idx+1];d[i4+2]=d[idx+2];}
        }}
        ctx.putImageData(imgData,0,0);
      }
      // Chips with improved rendering
      const count=Math.floor(this.density*(resPx/400)*(sw.density||1));
      const bs=3.5*(this.chipScale/100);
      for(let i=0;i<count;i++){
        const cx=Math.random()*resPx,cy=Math.random()*resPx;
        const chColor=sw.chips[Math.floor(Math.random()*sw.chips.length)];
        const sz=bs*(0.5+Math.random()*1.4);
        ctx.save();ctx.translate(cx,cy);ctx.rotate(Math.random()*Math.PI*2);
        if(sw.chipShape==='round'){
          ctx.fillStyle=chColor;ctx.beginPath();ctx.ellipse(0,0,sz*(0.7+Math.random()*0.5),sz*(0.5+Math.random()*0.4),0,0,Math.PI*2);ctx.fill();
          // Highlight
          ctx.fillStyle='rgba(255,255,255,0.15)';ctx.beginPath();ctx.ellipse(-sz*0.12,-sz*0.12,sz*0.25,sz*0.18,0,0,Math.PI*2);ctx.fill();
          // Shadow
          ctx.fillStyle='rgba(0,0,0,0.12)';ctx.beginPath();ctx.ellipse(sz*0.08,sz*0.08,sz*0.35,sz*0.22,0,0,Math.PI*2);ctx.fill();
          // Edge
          ctx.strokeStyle='rgba(0,0,0,0.08)';ctx.lineWidth=0.5;ctx.beginPath();ctx.ellipse(0,0,sz*(0.7+Math.random()*0.5),sz*(0.5+Math.random()*0.4),0,0,Math.PI*2);ctx.stroke();
        } else if(sw.chipShape==='flake'){
          const pts=5+Math.floor(Math.random()*4);ctx.fillStyle=chColor;ctx.beginPath();
          for(let p=0;p<pts;p++){const a=(p/pts)*Math.PI*2,r=sz*(0.4+Math.random()*0.9);p===0?ctx.moveTo(Math.cos(a)*r,Math.sin(a)*r*0.35):ctx.lineTo(Math.cos(a)*r,Math.sin(a)*r*0.35);}
          ctx.closePath();ctx.fill();
          // Metallic highlight
          ctx.fillStyle=`rgba(255,255,255,${0.15+Math.random()*0.35})`;ctx.beginPath();ctx.ellipse(0,0,sz*0.18,sz*0.06,Math.random(),0,Math.PI*2);ctx.fill();
          ctx.strokeStyle='rgba(0,0,0,0.1)';ctx.lineWidth=0.3;ctx.stroke();
        } else {
          const pts=5+Math.floor(Math.random()*4);ctx.fillStyle=chColor;ctx.beginPath();
          for(let p=0;p<pts;p++){const a=(p/pts)*Math.PI*2+(Math.random()-0.5)*0.3,r=sz*(0.45+Math.random()*0.65);p===0?ctx.moveTo(Math.cos(a)*r,Math.sin(a)*r*0.8):ctx.lineTo(Math.cos(a)*r,Math.sin(a)*r*0.8);}
          ctx.closePath();ctx.fill();
          ctx.fillStyle='rgba(255,255,255,0.06)';ctx.beginPath();ctx.ellipse(-sz*0.05,-sz*0.08,sz*0.2,sz*0.12,0,0,Math.PI*2);ctx.fill();
          ctx.strokeStyle='rgba(255,255,255,0.06)';ctx.lineWidth=0.5;ctx.stroke();
        }
        ctx.restore();
      }
      // Surface micro-texture (grain)
      const grainCtx=ctx;grainCtx.globalAlpha=0.03;
      for(let n=0;n<resPx*resPx*0.008;n++){
        grainCtx.fillStyle=Math.random()>0.5?'#ffffff':'#000000';
        grainCtx.fillRect(Math.random()*resPx,Math.random()*resPx,1+Math.random(),1+Math.random());
      }
      grainCtx.globalAlpha=1;
      return c;
    },

    getTexture(sw,quad){const rp=Math.max(400,Math.min(1200,Math.round(Math.sqrt(quad.reduce((a,p)=>a+p.x*p.x+p.y*p.y,0)/4)/50)*50));const k=sw.id+'|'+this.density+'|'+this.chipScale+'|'+rp;if(this.texCache[k])return this.texCache[k];const t=this.generateTexture(sw,rp);this.texCache[k]=t;return t;},

    // ── BEZIER CURVE HELPERS ──
    getSmoothedPath(pts,tension){
      if(pts.length<3)return pts;
      const smooth=[];const n=pts.length;const t=tension||0.3;
      for(let i=0;i<n;i++){const p0=pts[(i-1+n)%n],p1=pts[i],p2=pts[(i+1)%n],p3=pts[(i+2)%n];
      const steps=24;for(let s=0;s<steps;s++){const st=s/steps;const st2=st*st,st3=st2*st;
      const x=(2*st3-3*st2+1)*p1.x+(-2*st3+3*st2)*p2.x+(st3-2*st2+st)*t*(p2.x-p0.x)+(-st3+st2)*t*(p3.x-p1.x);
      const y=(2*st3-3*st2+1)*p1.y+(-2*st3+3*st2)*p2.y+(st3-2*st2+st)*t*(p2.y-p0.y)+(-st3+st2)*t*(p3.y-p1.y);
      smooth.push({x,y});}}
      return smooth;
    },
    drawSmoothPath(ctx,pts,close){
      if(pts.length<3){ctx.beginPath();pts.forEach((p,i)=>i===0?ctx.moveTo(p.x,p.y):ctx.lineTo(p.x,p.y));if(close)ctx.closePath();return;}
      const smooth=this.getSmoothedPath(pts);
      ctx.beginPath();ctx.moveTo(smooth[0].x,smooth[0].y);
      for(let i=1;i<smooth.length;i++)ctx.lineTo(smooth[i].x,smooth[i].y);
      if(close)ctx.closePath();
    },
    drawCurvedPath(ctx,pts,close){
      if(pts.length<3){ctx.beginPath();pts.forEach((p,i)=>i===0?ctx.moveTo(p.x,p.y):ctx.lineTo(p.x,p.y));if(close)ctx.closePath();return;}
      const smooth=this.getSmoothedPath(pts);
      ctx.beginPath();ctx.moveTo(smooth[0].x,smooth[0].y);
      for(let i=1;i<smooth.length-1;i+=2){const mx=(smooth[i].x+smooth[i+1].x)/2,my=(smooth[i].y+smooth[i+1].y)/2;ctx.quadraticCurveTo(smooth[i].x,smooth[i].y,mx,my);}
      if(smooth.length%2===0)ctx.lineTo(smooth[smooth.length-1].x,smooth[smooth.length-1].y);
      if(close)ctx.closePath();
    },

    // ── AREA MEASUREMENT ──
    _polygonArea(pts){let a=0;const n=pts.length;for(let i=0;i<n;i++){const j=(i+1)%n;a+=pts[i].x*pts[j].y;a-=pts[j].x*pts[i].y;}return Math.abs(a)/2;},
    _computeAreaDisplay(){
      if(!this.hasImage){this.areaDisplayText='';return;}
      const z=this.zones[this.activeZoneIdx];if(!z){this.areaDisplayText='';return;}
      const pts=this.selectionMode==='multioutline'?(this.getActiveShape()?.mask||z.mask):z.mask;
      const pxArea=this._polygonArea(pts);
      const m2=pxArea*(this.metersPerPixel*this.metersPerPixel);
      this.areaDisplayText='📐 ~'+m2.toFixed(1)+' m²';
      if(m2>0.5){this.areaM2=Math.round(m2*10)/10;}
    },

    // ── BEFORE/AFTER SLIDER ──
    startSliderDrag(e){
      this._sliderDragging=true;
      const onMove=(ev)=>{
        if(!this._sliderDragging)return;
        const r=this.$refs.overlayCanvas.getBoundingClientRect();
        const clientX=ev.touches?ev.touches[0].clientX:ev.clientX;
        this.sliderPos=Math.max(0.05,Math.min(0.95,(clientX-r.left)/r.width));
        this.render();
      };
      const onUp=()=>{this._sliderDragging=false;window.removeEventListener('mousemove',onMove);window.removeEventListener('mouseup',onUp);window.removeEventListener('touchmove',onMove);window.removeEventListener('touchend',onUp);};
      window.addEventListener('mousemove',onMove);window.addEventListener('mouseup',onUp);
      window.addEventListener('touchmove',onMove);window.addEventListener('touchend',onUp);
    },
    _cacheOriginalImage(){if(!this.baseImage)return;const c=document.createElement('canvas');c.width=this.W;c.height=this.H;c.getContext('2d').drawImage(this.baseImage,0,0,this.W,this.H);this._originalCanvas=c;},

    // ── RENDER ──
    _renderZoneOrSubZone(ctx,sw,mask,quad,isCurve,N){
      const tex=this.getTexture(sw,quad);const tw=tex.width,th=tex.height;const Hm=this.computeHomography(quad);
      ctx.save();
      if(isCurve&&mask.length>=3){this.drawCurvedPath(ctx,mask,true);ctx.clip();}
      else{ctx.beginPath();mask.forEach((p,i)=>i===0?ctx.moveTo(p.x,p.y):ctx.lineTo(p.x,p.y));ctx.closePath();ctx.clip();}
      ctx.globalCompositeOperation='multiply';ctx.globalAlpha=(this.intensity/100)*0.85;ctx.fillStyle=sw.base;ctx.fillRect(0,0,this.W,this.H);ctx.restore();
      ctx.save();
      if(isCurve&&mask.length>=3){this.drawCurvedPath(ctx,mask,true);ctx.clip();}
      else{ctx.beginPath();mask.forEach((p,i)=>i===0?ctx.moveTo(p.x,p.y):ctx.lineTo(p.x,p.y));ctx.closePath();ctx.clip();}
      ctx.globalCompositeOperation='overlay';ctx.globalAlpha=(this.intensity/100)*0.95;
      for(let j=0;j<N;j++){const v0=j/N,v1=(j+1)/N;for(let i=0;i<N;i++){const u0=i/N,u1=(i+1)/N;const d00=this.mapUV(Hm,u0,v0),d10=this.mapUV(Hm,u1,v0),d11=this.mapUV(Hm,u1,v1),d01=this.mapUV(Hm,u0,v1);const s00={x:u0*tw,y:v0*th},s10={x:u1*tw,y:v0*th},s11={x:u1*tw,y:v1*th},s01={x:u0*tw,y:v1*th};this.drawTri(ctx,tex,s00,s10,s11,d00,d10,d11);this.drawTri(ctx,tex,s00,s11,s01,d00,d11,d01);}}
      ctx.setTransform(1,0,0,1,0,0);ctx.restore();
    },
    render(){
      if(!this.hasImage)return;
      const ctx=this.$refs.mainCanvas.getContext('2d');ctx.setTransform(1,0,0,1,0,0);ctx.clearRect(0,0,this.W,this.H);
      if(this.sliderActive&&this._originalCanvas){
        ctx.save();ctx.beginPath();ctx.rect(0,0,this.W*this.sliderPos,this.H);ctx.clip();ctx.drawImage(this._originalCanvas,0,0,this.W,this.H);ctx.restore();
        ctx.save();ctx.beginPath();ctx.rect(this.W*this.sliderPos,0,this.W*(1-this.sliderPos),this.H);ctx.clip();
        ctx.drawImage(this.baseImage,0,0,this.W,this.H);
        const isCurve=this.mode==='curve';const N=this.meshDiv;
        this.zones.forEach(zone=>{if(this.selectionMode==='multioutline'&&zone.subZones&&zone.subZones.length){zone.subZones.forEach(sz=>{this._renderZoneOrSubZone(ctx,sz.swatch,sz.mask,zone.quad,isCurve,N);});}else{this._renderZoneOrSubZone(ctx,zone.swatch,zone.mask,zone.quad,isCurve,N);}});
        ctx.restore();
      } else {
        ctx.drawImage(this.baseImage,0,0,this.W,this.H);
        const isCurve=this.mode==='curve';
        if(!this.showOriginal){const N=this.meshDiv;
        this.zones.forEach(zone=>{if(this.selectionMode==='multioutline'&&zone.subZones&&zone.subZones.length){zone.subZones.forEach(sz=>{this._renderZoneOrSubZone(ctx,sz.swatch,sz.mask,zone.quad,isCurve,N);});}else{this._renderZoneOrSubZone(ctx,zone.swatch,zone.mask,zone.quad,isCurve,N);}});}
      }
      const oc=this.$refs.overlayCanvas.getContext('2d');oc.clearRect(0,0,this.W,this.H);if(this.showOriginal||!this.hasImage)return;
      this.zones.forEach((zone,zi)=>{const active=zi===this.activeZoneIdx;if(active)return;
        const col=this.zoneColors[zi%this.zoneColors.length];oc.strokeStyle=col+'88';oc.lineWidth=Math.max(1,this.W*0.0015);
        if(this.mode==='quad'){oc.beginPath();zone.quad.forEach((p,i)=>i===0?oc.moveTo(p.x,p.y):oc.lineTo(p.x,p.y));oc.closePath();oc.stroke();}
        else{oc.beginPath();zone.mask.forEach((p,i)=>i===0?oc.moveTo(p.x,p.y):oc.lineTo(p.x,p.y));oc.closePath();oc.stroke();}
        const cx=zone.quad.reduce((a,p)=>a+p.x,0)/4,cy=zone.quad.reduce((a,p)=>a+p.y,0)/4;
        oc.font='bold '+(Math.max(11,this.W*0.012))+'px Inter,system-ui,sans-serif';oc.textAlign='center';oc.textBaseline='middle';
        oc.fillStyle='rgba(0,0,0,0.5)';oc.fillText(zone.name,cx+1,cy+1);oc.fillStyle=col;oc.fillText(zone.name,cx,cy);
      });
      const zone=this.zones[this.activeZoneIdx];if(!zone)return;
      if(this.mode==='quad'){
        oc.strokeStyle='rgba(217,119,6,0.9)';oc.lineWidth=Math.max(1.5,this.W*0.002);oc.beginPath();zone.quad.forEach((p,i)=>i===0?oc.moveTo(p.x,p.y):oc.lineTo(p.x,p.y));oc.closePath();oc.stroke();
        const r=Math.max(6,this.W*0.009);zone.quad.forEach(p=>{oc.beginPath();oc.arc(p.x,p.y,r,0,Math.PI*2);oc.fillStyle='rgba(217,119,6,0.95)';oc.fill();oc.lineWidth=2;oc.strokeStyle='#1c1917';oc.stroke();});
      } else if(this.mode==='curve'){
        const r=Math.max(5,this.W*0.007);zone.mask.forEach((p,i)=>{oc.beginPath();oc.arc(p.x,p.y,r,0,Math.PI*2);oc.fillStyle='rgba(5,150,105,0.95)';oc.fill();oc.lineWidth=1.5;oc.strokeStyle='#0c0a09';oc.stroke();
        if(zone.mask.length>=3){const prev=zone.mask[(i-1+zone.mask.length)%zone.mask.length];const next=zone.mask[(i+1)%zone.mask.length];const mx=(prev.x+next.x)/2,my=(prev.y+next.y)/2;oc.beginPath();oc.setLineDash([3,3]);oc.strokeStyle='rgba(5,150,105,0.4)';oc.lineWidth=1;oc.moveTo(p.x,p.y);oc.lineTo(mx,my);oc.stroke();oc.setLineDash([]);}});
      } else {
        const r=Math.max(5,this.W*0.007);zone.mask.forEach(p=>{oc.beginPath();oc.arc(p.x,p.y,r,0,Math.PI*2);oc.fillStyle='rgba(8,145,178,0.95)';oc.fill();oc.lineWidth=1.5;oc.strokeStyle='#0c0a09';oc.stroke();});
      }
      if(this.selectionMode==='multioutline'&&zone.subZones&&zone.subZones.length>1){
        zone.subZones.forEach((sz,si)=>{if(si===this.activeSubZoneIdx)return;
          oc.strokeStyle='rgba(8,145,178,0.45)';oc.lineWidth=Math.max(1,this.W*0.0015);
          oc.beginPath();sz.mask.forEach((p,i)=>i===0?oc.moveTo(p.x,p.y):oc.lineTo(p.x,p.y));oc.closePath();oc.stroke();});
      }
      this._computeAreaDisplay();
    },

    // ── INTERACTION ──
    getPos(e){const r=this.$refs.overlayCanvas.getBoundingClientRect();return{x:(e.clientX-r.left)*(this.W/r.width),y:(e.clientY-r.top)*(this.H/r.height)};},
    getActiveShape(){const z=this.zones[this.activeZoneIdx];if(!z)return null;if(this.selectionMode==='multioutline'){if(!z.subZones||!z.subZones.length){z.subZones=[this.makeZoneSelection(z,'Selección 1',z.swatch)];this.activeSubZoneIdx=0;}const current=z.subZones[this.activeSubZoneIdx]||z.subZones[0];if(current&&current.quad&&z.quad!==current.quad){z.quad=current.quad.map(p=>({...p}));}if(current&&current.mask&&z.mask!==current.mask){z.mask=current.mask.map(p=>({...p}));}return current;}return z;},
    makeZoneSelection(zone,name,swatch){return{id:'s'+Math.random().toString(36).slice(2,8),name,swatch:JSON.parse(JSON.stringify(swatch)),quad:zone.quad.map(p=>({...p})),mask:zone.mask.map(p=>({...p}))};},
    hitTest(pos,pts){const r=Math.max(14,this.W*0.022);let best=-1,bestD=Infinity;pts.forEach((p,i)=>{const d=Math.hypot(p.x-pos.x,p.y-pos.y);if(d<r&&d<bestD){bestD=d;best=i;}});return best;},
    onPointerDown(e){
      if(this.sliderActive)return;
      const z=this.zones[this.activeZoneIdx];if(!z)return;const current=this.getActiveShape();const pos=this.getPos(e);const isMaskLike=this.mode==='mask'||this.mode==='curve';const pts=this.mode==='quad'?(current?.quad||z.quad):(current?.mask||z.mask);const hit=this.hitTest(pos,pts);if(hit>=0){this.dragIdx=hit;this.$refs.overlayCanvas.setPointerCapture(e.pointerId);this._pushHistory();}
      else if(isMaskLike){let bestE=0,bestD=Infinity;const targetMask=current?.mask||z.mask;for(let i=0;i<targetMask.length;i++){const a=targetMask[i],b=targetMask[(i+1)%targetMask.length];const d=this.distSeg(pos,a,b);if(d<bestD){bestD=d;bestE=i;}}this._pushHistory();targetMask.splice(bestE+1,0,pos);if(this.selectionMode==='multioutline'&&current){current.mask=targetMask;current.quad=current.quad||z.quad.map(p=>({...p}));}else{z.mask=targetMask;}this.render();}
    },
    onPointerMove(e){if(this.dragIdx<0)return;const z=this.zones[this.activeZoneIdx];if(!z)return;const current=this.getActiveShape();const pos=this.getPos(e);const cl={x:Math.max(0,Math.min(this.W,pos.x)),y:Math.max(0,Math.min(this.H,pos.y))};if(this.mode==='quad'){if(this.selectionMode==='multioutline'&&current){current.quad[this.dragIdx]=cl;}else{z.quad[this.dragIdx]=cl;}}else{if(this.selectionMode==='multioutline'&&current){current.mask[this.dragIdx]=cl;}else{z.mask[this.dragIdx]=cl;}}this.texCache={};this.render();},
    onDblClick(e){if(this.mode!=='mask'&&this.mode!=='curve')return;const z=this.zones[this.activeZoneIdx];if(!z)return;const current=this.getActiveShape();const targetMask=current?.mask||z.mask;if(!targetMask||targetMask.length<=3)return;const hit=this.hitTest(this.getPos(e),targetMask);if(hit>=0){this._pushHistory();targetMask.splice(hit,1);this.render();}},
    distSeg(p,a,b){const l2=(b.x-a.x)**2+(b.y-a.y)**2;if(l2===0)return Math.hypot(p.x-a.x,p.y-a.y);let t=Math.max(0,Math.min(1,((p.x-a.x)*(b.x-a.x)+(p.y-a.y)*(b.y-a.y))/l2));return Math.hypot(p.x-(a.x+t*(b.x-a.x)),p.y-(a.y+t*(b.y-a.y)));},

    // ── ACTIONS ──
    loadRoom(room){this.currentRoom=room.id;this.currentRoomApiId=room._apiId??null;this.loadImageFromUrl(room.photo);},
    handleUpload(e){const f=e.target.files[0];if(!f)return;this.currentRoom=null;this.currentRoomApiId=null;const r=new FileReader();r.onload=ev=>this.loadImageFromUrl(ev.target.result);r.readAsDataURL(f);},
    setSelectionMode(mode){this.selectionMode=mode;if(this.selectionMode==='multioutline'&&this.zones[this.activeZoneIdx]){const z=this.zones[this.activeZoneIdx];if(!z.subZones||!z.subZones.length)z.subZones=[this.makeZoneSelection(z,'Selección 1',z.swatch)];this.activeSubZoneIdx=0;}this.$nextTick(()=>this.render());},
    loadImageFromUrl(url){const img=new Image();img.onload=()=>{this.baseImage=img;this.W=img.naturalWidth;this.H=img.naturalHeight;this.$refs.mainCanvas.width=this.W;this.$refs.mainCanvas.height=this.H;this.$refs.overlayCanvas.width=this.W;this.$refs.overlayCanvas.height=this.H;document.getElementById('canvasWrap').style.aspectRatio=this.W+'/'+this.H;this.zones=[this.makeZone('Zona 1',this.catalog[0])];this.zones[0].subZones=[this.makeZoneSelection(this.zones[0],'Selección 1',this.catalog[0])];this.activeZoneIdx=0;this.activeSubZoneIdx=0;this.texCache={};this.hasImage=true;this.history=[];this.historyIdx=-1;this._pushHistory();this._cacheOriginalImage();this.$nextTick(()=>{this.drawAllSwatches();this.render();this.updateQuote();});};img.onerror=()=>{this._showImageError(url);};img.src=url;},
    _showImageError(url){const wrap=document.getElementById('canvasWrap');if(!wrap)return;let err=wrap.querySelector('.sim-error-msg');if(!err){err=document.createElement('div');err.className='sim-error-msg';err.style.cssText='position:absolute;inset:0;display:flex;flex-direction:column;align-items:center;justify-content:center;color:#ef4444;pointer-events:none;z-index:3;gap:8px;';err.innerHTML='<div style="font-size:40px;">⚠️</div><p style="font-size:13px;text-align:center;max-width:300px;">Error loading image.<br><small style="color:#a8a29e;">Try another photo or select a predefined space.</small></p>';wrap.appendChild(err);}err.style.display='flex';},
    defaultQuad(){return[{x:Math.min(this.W-2,Math.max(2,0.06*this.W)),y:Math.min(this.H-2,Math.max(2,0.92*this.H))},{x:Math.min(this.W-2,Math.max(2,0.92*this.W)),y:Math.min(this.H-2,Math.max(2,0.92*this.H))},{x:Math.min(this.W-2,Math.max(2,0.64*this.W)),y:Math.min(this.H-2,Math.max(2,0.55*this.H))},{x:Math.min(this.W-2,Math.max(2,0.32*this.W)),y:Math.min(this.H-2,Math.max(2,0.55*this.H))}]},
    makeZone(name,swatch){const q=this.defaultQuad();return{id:'z'+Math.random().toString(36).slice(2,8),name,swatch:JSON.parse(JSON.stringify(swatch)),quad:q,mask:q.map(p=>({...p})),subZones:[]};},
    setFinish(sw){const zi=this.activeZoneIdx;if(zi<0||zi>=this.zones.length)return;this._pushHistory();if(this.selectionMode==='multioutline'){const z=this.zones[zi];if(!z.subZones||!z.subZones.length){z.subZones=[this.makeZoneSelection(z,'Selección 1',z.swatch)];this.activeSubZoneIdx=0;}z.subZones[this.activeSubZoneIdx].swatch=JSON.parse(JSON.stringify(sw));}else{this.zones[zi].swatch=JSON.parse(JSON.stringify(sw));}this.texCache={};this.zones=this.zones.slice();this.$nextTick(()=>{this.render();this.updateQuote();});},
    addZone(){if(!this.hasImage)return;this._pushHistory();const n=this.zones.length;const zone=this.makeZone('Zona '+(n+1),this.catalog[n%this.catalog.length]);zone.subZones=[this.makeZoneSelection(zone,'Selección 1',zone.swatch)];this.zones.push(zone);this.activeZoneIdx=this.zones.length-1;this.activeSubZoneIdx=0;this.texCache={};this.render();this.updateQuote();},
    addSubZone(){if(!this.hasImage)return;this._pushHistory();const z=this.zones[this.activeZoneIdx];if(!z)return;const base=z.subZones&&z.subZones.length?z.subZones[z.subZones.length-1].swatch:z.swatch;const item=this.makeZoneSelection(z,'Selección '+(z.subZones.length+1),base);z.subZones.push(item);this.activeSubZoneIdx=z.subZones.length-1;this.texCache={};this.render();this.updateQuote();},
    deleteActiveZone(){const zi=this.activeZoneIdx;if(zi<0||zi>=this.zones.length)return;if(this.zones.length<=1){this.clearSelection();return;}this.removeZone(zi);},
    removeZone(i){this._pushHistory();this.zones.splice(i,1);if(this.activeZoneIdx>=this.zones.length)this.activeZoneIdx=this.zones.length-1;this.activeSubZoneIdx=0;this.texCache={};this.render();this.updateQuote();},
    removeSubZone(si){const z=this.zones[this.activeZoneIdx];if(!z||!z.subZones||!z.subZones.length)return;this._pushHistory();z.subZones.splice(si,1);if(this.activeSubZoneIdx>=z.subZones.length)this.activeSubZoneIdx=Math.max(0,z.subZones.length-1);if(!z.subZones.length){z.subZones=[this.makeZoneSelection(z,'Selección 1',z.swatch)];this.activeSubZoneIdx=0;}this.texCache={};this.render();this.updateQuote();},
    duplicateActiveZone(){if(!this.hasImage)return;const src=this.zones[this.activeZoneIdx];if(!src)return;this._pushHistory();const dup={id:'z'+Math.random().toString(36).slice(2,8),name:src.name+' (copia)',swatch:JSON.parse(JSON.stringify(src.swatch)),quad:src.quad.map(p=>({...p})),mask:src.mask.map(p=>({...p})),subZones:src.subZones?src.subZones.map(sz=>this.makeZoneSelection(sz,sz.name,sz.swatch)):[]};this.zones.push(dup);this.activeZoneIdx=this.zones.length-1;this.activeSubZoneIdx=0;this.texCache={};this.render();this.updateQuote();},
    resetCorners(){const z=this.zones[this.activeZoneIdx];if(!z)return;this._pushHistory();const target=this.selectionMode==='multioutline'?(this.getActiveShape()||z):z;if(!target||!target.quad)return;target.quad=this.defaultQuad();this.texCache={};this.render();},
    resetMask(){const z=this.zones[this.activeZoneIdx];if(!z)return;this._pushHistory();const target=this.selectionMode==='multioutline'?(this.getActiveShape()||z):z;if(!target)return;target.mask=(target.quad||this.defaultQuad()).map(p=>({...p}));this.texCache={};this.render();},
    undoLastPoint(){const z=this.zones[this.activeZoneIdx];if(!z)return;this._pushHistory();const target=this.selectionMode==='multioutline'?(this.getActiveShape()||z):z;if(!target)return;const points=this.mode==='quad'?target.quad:target.mask;if(!points||points.length<=1){this.clearSelection();return;}points.pop();if(this.mode!=='quad'&&points.length<3){target.mask=(target.quad||this.defaultQuad()).map(p=>({...p}));}this.texCache={};this.render();},
    clearSelection(){const z=this.zones[this.activeZoneIdx];if(!z)return;this._pushHistory();const target=this.selectionMode==='multioutline'?(this.getActiveShape()||z):z;if(!target)return;if(this.mode==='quad'){target.quad=this.defaultQuad();}else{target.mask=(target.quad||this.defaultQuad()).map(p=>({...p}));}this.texCache={};this.render();},
    handleKeydown(event){
      if((event.ctrlKey||event.metaKey)&&event.key==='z'&&!event.shiftKey){event.preventDefault();this._undo();return;}
      if((event.ctrlKey||event.metaKey)&&(event.key==='y'||(event.key==='z'&&event.shiftKey))){event.preventDefault();this._redo();return;}
      if(!['Backspace','Delete','Escape'].includes(event.key))return;
      if(event.target&&['INPUT','SELECT','TEXTAREA'].includes(event.target.tagName))return;
      event.preventDefault();
      if(event.key==='Escape'){this.clearSelection();return;}
      this.undoLastPoint();
    },
    toggleCurve(){this.curveMode=!this.curveMode;this.render();},
    onSettingChange(){this.texCache={};this.render();},
    _zoneQuote(zone){if(this.selectionMode==='multioutline'&&zone.subZones&&zone.subZones.length>1){return zone.subZones.reduce((s,sz)=>s+(sz.swatch.pricePerM2||50)*this.areaM2+this.laborRate*this.areaM2,0);}return(zone.swatch.pricePerM2||50)*this.areaM2+this.laborRate*this.areaM2;},
    updateQuote(){let t=0;this.zones.forEach(z=>{t+=Math.round(this._zoneQuote(z));});this.totalQuote=t;},
    shareWhatsApp(){
      const z=this.zones[this.activeZoneIdx];const finishName=z?z.swatch.name:'';
      const text=`🔬 *{{ config('platform.name') }}*\n\n{{ __('Project preview') }}:\n🏠 {{ __('Space') }}: ${this.currentRoom||'{{ __('Custom photo') }}'}\n🎨 {{ __('Finish') }}: ${finishName}\n📐 {{ __('Area') }}: ~${this.areaM2} m²\n💰 {{ __('Estimated total') }}: *€${this.totalQuote}*\n\n{{ __('Please send me more information.') }}`;
      window.open(`https://wa.me/?text=${encodeURIComponent(text)}`,'_blank');
    },
    // The download is gated on a recorded lead: never hand out the file (and
    // never lose the contact) because a silent failure was swallowed.
    download(){if(!this.leadSubmitted){this.showLeadModal=true;return;}this._doDownload();},
    submitLeadAndDownload(){
      if(!this.leadName||!this.leadEmail){this.leadError='Por favor completa nombre y email';return;}
      this.leadError='';this.leadSubmitting=true;
      const sw=this.zones[this.activeZoneIdx]?.swatch||{};
      fetch('/api/simulador/quotes',{method:'POST',headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name=csrf-token]')?.content||''},body:JSON.stringify({customer_name:this.leadName,customer_email:this.leadEmail,customer_phone:this.leadPhone||null,room_template_id:this.currentRoomApiId,finish_variant_id:sw._apiVariantId??null,target_surface:'floor',area_m2:this.areaM2})})
        .then(r=>r.json().catch(()=>({})).then(d=>({ok:r.ok,d:d})))
        .then(res=>{
          this.leadSubmitting=false;
          if(!res.ok){
            const msg=(res.d&&(res.d.message||(res.d.errors&&Object.values(res.d.errors)[0]&&Object.values(res.d.errors)[0][0])))||'';
            this.leadError='No se pudo registrar tu solicitud.'+(msg?' ('+msg+')':'')+' Inténtalo de nuevo o escríbenos por WhatsApp.';
            return;
          }
          this.leadSubmitted=true;
          this.showLeadModal=false;
          this._doDownload();
        })
        .catch(()=>{this.leadSubmitting=false;this.leadError='Sin conexión. Inténtalo de nuevo o escríbenos por WhatsApp.';});
    },
    _doDownload(){const w=this.showOriginal;this.showOriginal=false;this.render();const l=document.createElement('a');l.download='costa-resin-simulacion.jpg';l.href=this.$refs.mainCanvas.toDataURL('image/jpeg',0.95);l.click();this.showOriginal=w;this.render();},
    drawAllSwatches(){this.catalog.forEach(sw=>{const el=document.getElementById('sw-'+sw.id);if(!el)return;const ctx=el.getContext('2d');el.width=240;el.height=170;const productImg=this.imageCache[sw.id];if(productImg){ctx.fillStyle=sw.base||'#888888';ctx.fillRect(0,0,240,170);const iw=productImg.naturalWidth||productImg.width||1;const ih=productImg.naturalHeight||productImg.height||1;const scale=Math.max(240/iw,170/ih);const dw=iw*scale,dh=ih*scale;ctx.imageSmoothingEnabled=true;ctx.imageSmoothingQuality='high';ctx.drawImage(productImg,(240-dw)/2,(170-dh)/2,dw,dh);}else{ctx.drawImage(this.generateTexture(sw,240),0,0,240,170);}});},
  };
}

window.simulator = window.simulator || simulator;
function ensureSimulatorCompat(api,root){if(!api||!root)return api;api.$el=root;api.$refs=api.$refs||{mainCanvas:root.querySelector('#mainCanvas'),overlayCanvas:root.querySelector('#overlayCanvas'),fileInput:root.querySelector('input[type=file]')};api.$nextTick=api.$nextTick||function(cb){if(typeof cb==='function')cb();};return api;}
function bindSimulatorFallbackControls(){const root=document.querySelector('.sim-wrap');if(!root||root.dataset.simulatorBound==='true')return;root.dataset.simulatorBound='true';const api=ensureSimulatorCompat(simulator(),root);window.__simulatorApi=api;api.init();root.addEventListener('click',(event)=>{const trigger=event.target.closest('[data-sim-action]');if(!trigger)return;const action=trigger.dataset.simAction;if(action==='setMode'){api.mode=trigger.dataset.simValue;if(api.mode==='mask')api.curveMode=false;api.render();return;}if(action==='setSelectionMode'){api.setSelectionMode(trigger.dataset.simValue);return;}if(action==='addSelection'){if(api.selectionMode==='multioutline')api.addSubZone();else api.addZone();return;}if(typeof api[action]==='function'){api[action]();}});const fileInput=root.querySelector('input[type=file]');if(fileInput&&!fileInput.dataset.simBound){fileInput.dataset.simBound='true';fileInput.addEventListener('change',(event)=>api.handleUpload(event));}}
function registerSimulatorAlpine(){if(!window.Alpine)return;window.Alpine.data('simulator',simulator);const root=document.querySelector('.sim-wrap');if(root&&!root.__x){window.Alpine.initTree(root);}bindSimulatorFallbackControls();}
document.addEventListener('alpine:init',registerSimulatorAlpine);
if(document.readyState==='loading'){document.addEventListener('DOMContentLoaded',registerSimulatorAlpine);}else{registerSimulatorAlpine();}
window.addEventListener('load',registerSimulatorAlpine);
setTimeout(registerSimulatorAlpine,150);
</script>
@endsection
