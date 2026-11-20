{{-- Content Section Editor — Dark Theme matching mockup --}}
@php
    $typeColors = [
        'hero' => '#c084fc', 'image_text' => '#4d8dff', 'video' => '#f5a623',
        'stats' => '#4ade80', 'gallery' => '#f472b6', 'testimonial' => '#22d3ee',
    ];
    $typeLabels = [
        'hero' => 'Hero slider', 'image_text' => 'Imagen + texto', 'video' => 'Video',
        'stats' => 'Cifras', 'gallery' => 'Galería', 'testimonial' => 'Testimonios',
    ];
@endphp

<style>
.dce{--bg:#0b0b0c;--panel:#17181a;--panel-2:#1d1e21;--border:#2a2b2e;--border-s:#3a3b3f;--text:#f2f2f0;--text2:#a8a8ac;--muted:#6b6b70;--accent:#f5a623;--accent-dk:#402d0c;--accent-tx:#ffcf7a;--r:10px;font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,sans-serif;color:var(--text);background:var(--bg);margin:-1.5rem -1.5rem 0;padding:1.5rem 1.5rem 3rem;}
.dce *{box-sizing:border-box;}
.dce-in{width:100%;background:var(--panel-2);border:1px solid var(--border-s);color:var(--text);padding:9px 11px;border-radius:7px;font-size:13px;font-family:inherit;}
.dce-in:focus{outline:none;border-color:var(--accent);}
.dce-ta{width:100%;background:var(--panel-2);border:1px solid var(--border-s);color:var(--text);padding:9px 11px;border-radius:7px;font-size:13px;font-family:inherit;resize:vertical;min-height:60px;}
.dce-ta:focus{outline:none;border-color:var(--accent);}
.dce-sel{background:var(--panel-2);border:1px solid var(--border-s);color:var(--text);padding:9px 11px;border-radius:7px;font-size:13px;}
.dce-sel:focus{outline:none;border-color:var(--accent);}
.dce-btn{border:1px solid var(--border-s);background:var(--panel-2);color:var(--text);padding:8px 14px;border-radius:8px;font-size:13px;cursor:pointer;display:inline-flex;align-items:center;gap:6px;}
.dce-btn:hover{background:#242528;}
.dce-p{background:var(--accent);border-color:var(--accent);color:#1a1200;font-weight:600;}
.dce-p:hover{background:#ffb840;}
.dce-g{background:transparent;border-color:transparent;color:var(--text2);}
.dce-g:hover{background:var(--panel-2);color:var(--text);}
.dce-d{background:transparent;border-color:transparent;color:#e0625f;}
.dce-d:hover{background:#2a1414;}
.dce-sm{padding:6px 10px;font-size:12px;}
.dce-hdr{display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:20px;flex-wrap:wrap;gap:12px;}
.dce-hdr h1{font-size:20px;margin:0 0 4px;font-weight:600;}
.dce-hdr p{margin:0;color:var(--muted);font-size:13px;}
.dce-acts{display:flex;gap:10px;}
.dce-cards{display:flex;flex-direction:column;gap:10px;}
.dce-card{background:var(--panel);border:1px solid var(--border);border-radius:var(--r);overflow:hidden;}
.dce-card.open{border-color:var(--border-s);}
.dce-card-h{display:flex;align-items:center;gap:10px;padding:12px 14px;cursor:pointer;user-select:none;}
.dce-drag{color:var(--muted);cursor:grab;font-size:14px;letter-spacing:-2px;line-height:1;}
.dce-badge{display:flex;align-items:center;gap:6px;background:var(--panel-2);border:1px solid var(--border-s);padding:4px 9px;border-radius:6px;font-size:11.5px;color:var(--text2);white-space:nowrap;}
.dce-dot{width:6px;height:6px;border-radius:50%;}
.dce-mt{flex:1;min-width:0;}
.dce-mt .nm{font-size:13.5px;font-weight:500;}
.dce-mt .sb{font-size:11.5px;color:var(--muted);margin-top:1px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;}
.dce-ord{font-size:11px;color:var(--muted);white-space:nowrap;}
.dce-chv{color:var(--muted);font-size:12px;transition:transform .15s;}
.dce-card.open .dce-chv{transform:rotate(180deg);}
.dce-card-b{border-top:1px solid var(--border);padding:16px;display:none;}
.dce-card.open .dce-card-b{display:block;}
.dce-fl{margin-bottom:12px;}
.dce-fl label{display:block;font-size:11.5px;color:var(--muted);margin-bottom:5px;}
.dce-code{color:var(--text2);font-family:monospace;font-size:11px;background:var(--panel-2);padding:1px 5px;border-radius:4px;margin-left:6px;}
.dce-loc{display:flex;gap:8px;margin-bottom:8px;}
.dce-loc-tab{padding:4px 10px;font-size:11px;border-radius:5px;cursor:pointer;border:1px solid var(--border-s);background:var(--panel-2);color:var(--text2);}
.dce-loc-tab.active{background:var(--accent-dk);color:var(--accent-tx);border-color:var(--accent);}
.dce-dz{border:1.5px dashed var(--border-s);border-radius:8px;height:100px;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:4px;color:var(--muted);font-size:12px;background:var(--panel-2);cursor:pointer;}
.dce-dz.has{padding:0;border-style:solid;}
.dce-dz.has img{width:100%;height:100%;object-fit:cover;}
.dce-card-f{display:flex;align-items:center;justify-content:space-between;margin-top:14px;padding-top:12px;border-top:1px solid var(--border);}
.dce-st{font-size:11.5px;color:var(--muted);}
.dce-add{display:flex;gap:10px;align-items:center;background:var(--panel);border:1px dashed var(--border-s);border-radius:var(--r);padding:12px 14px;margin-top:10px;}
.dce-add .sp{flex:1;}
.dce-loc-badge{font-size:11px;color:var(--text2);border:1px solid var(--border-s);border-radius:6px;padding:3px 8px;}
</style>

<div class="dce" x-data="{ activeLang: {}, toggleLang(s,k,v){ if(!this.activeLang[s+k]) this.activeLang[s+k]=v; else if(this.activeLang[s+k]===v) this.activeLang[s+k]=v==='es'?'en':'es'; else this.activeLang[s+k]=v; } }">

    {{-- Page Selector --}}
    <div style="display:flex;flex-wrap:wrap;gap:6px;margin-bottom:14px;">
        @foreach($pages as $p)
            <button wire:click="mount({{ $p->id }}, '{{ $p->slug }}')"
                    class="dce-btn dce-sm {{ $pageId === $p->id ? 'dce-p' : '' }}">
                {{ $p->title }}
            </button>
        @endforeach
    </div>

    @if($pageId)
        {{-- Header --}}
        <div class="dce-hdr">
            <div>
                <h1>Editar contenido: {{ $pageTitle }}</h1>
                <p>{{ count($sections) }} sección{{ count($sections) !== 1 ? 'es' : '' }} · {{ $pageSlug }}</p>
            </div>
            <div class="dce-acts">
                <a href="{{ $this->getPreviewUrl() }}" target="_blank" class="dce-btn">↗ Vista previa</a>
                <button wire:click="saveAll" class="dce-btn dce-p">Publicar cambios</button>
            </div>
        </div>

        {{-- Section Cards --}}
        <div class="dce-cards">
            @forelse($sections as $sKey => $section)
                @php
                    $isOpen = in_array($sKey, $openSections);
                    $detectedType = 'hero';
                    if (!empty($section['fields'])) {
                        $firstKey = array_key_first($section['fields']);
                        if (in_array('video_url', array_keys($section['fields']))) $detectedType = 'video';
                        elseif (in_array('stat_1_value', array_keys($section['fields']))) $detectedType = 'stats';
                        elseif (in_array('image', array_keys($section['fields'])) || in_array('slide_1_image', array_keys($section['fields']))) $detectedType = 'image_text';
                    }
                    $dotColor = $typeColors[$detectedType] ?? '#6b6b70';
                    $typeLabel = $typeLabels[$detectedType] ?? 'Custom';
                @endphp
                <div class="dce-card {{ $isOpen ? 'open' : '' }}">
                    <div class="dce-card-h" wire:click="toggle('{{ $sKey }}')">
                        <span class="dce-drag">⠿</span>
                        <span class="dce-badge"><span class="dce-dot" style="background:{{ $dotColor }};"></span> {{ $typeLabel }}</span>
                        <div class="dce-mt">
                            <div class="nm">{{ ucwords(str_replace('_', ' ', $sKey)) }}</div>
                            <div class="sb">{{ implode(', ', array_keys($section['fields'])) }}</div>
                        </div>
                        <span class="dce-ord">orden {{ $loop->iteration }}</span>
                        <div style="display:flex;align-items:center;gap:2px;" @click.stop>
                            <button wire:click="moveUp('{{ $sKey }}')" class="dce-btn dce-sm dce-g" title="Subir">↑</button>
                            <button wire:click="moveDown('{{ $sKey }}')" class="dce-btn dce-sm dce-g" title="Bajar">↓</button>
                            <button wire:click="confirmDelete('{{ $sKey }}')" class="dce-btn dce-sm dce-d" title="Eliminar">✕</button>
                        </div>
                        <span class="dce-chv">▾</span>
                    </div>

                    @if($isOpen)
                        <div class="dce-card-b">
                            @foreach($section['fields'] as $fKey => $field)
                                @php
                                    $fieldId = $sKey . '_' . $fKey;
                                    $activeLang = $activeLang[$fieldId] ?? 'es';
                                @endphp
                                <div class="dce-fl">
                                    <label>
                                        {{ ucwords(str_replace('_', ' ', $fKey)) }}
                                        <span class="dce-code">{{ $fKey }}</span>
                                    </label>

                                    {{-- Type selector --}}
                                    <div style="display:flex;gap:6px;margin-bottom:6px;">
                                        <select class="dce-sel" style="max-width:160px;font-size:11px;"
                                                wire:change="setFieldType('{{ $sKey }}', '{{ $fKey }}', $event.target.value)">
                                            <option value="text" {{ $field['type'] === 'text' ? 'selected' : '' }}>📝 Texto</option>
                                            <option value="richtext" {{ $field['type'] === 'richtext' ? 'selected' : '' }}>📄 Enriquecido</option>
                                            <option value="image" {{ $field['type'] === 'image' ? 'selected' : '' }}>🖼️ Imagen</option>
                                            <option value="video" {{ $field['type'] === 'video' ? 'selected' : '' }}>🎬 Video</option>
                                        </select>
                                        <button wire:click="removeField('{{ $sKey }}', '{{ $fKey }}')"
                                                class="dce-btn dce-sm dce-d" onclick="return confirm('¿Eliminar este campo?')">✕</button>
                                    </div>

                                    {{-- Language tabs --}}
                                    <div class="dce-loc">
                                        <div class="dce-loc-tab {{ $activeLang === 'es' ? 'active' : '' }}"
                                             x-on:click="toggleLang('{{ $sKey }}', '{{ $fKey }}', 'es')">🇪🇸 ES</div>
                                        <div class="dce-loc-tab {{ $activeLang === 'en' ? 'active' : '' }}"
                                             x-on:click="toggleLang('{{ $sKey }}', '{{ $fKey }}', 'en')">🇬🇧 EN</div>
                                    </div>

                                    {{-- Input based on type + active language --}}
                                    @if($field['type'] === 'richtext')
                                        @if($activeLang === 'es')
                                            <textarea class="dce-ta" rows="3" wire:change="updateField('{{ $sKey }}', '{{ $fKey }}', 'es', $event.target.value)">{{ $field['es'] }}</textarea>
                                        @else
                                            <textarea class="dce-ta" rows="3" wire:change="updateField('{{ $sKey }}', '{{ $fKey }}', 'en', $event.target.value)">{{ $field['en'] }}</textarea>
                                        @endif
                                    @elseif($field['type'] === 'image')
                                        @php $imgVal = $activeLang === 'es' ? ($field['es'] ?? '') : ($field['en'] ?? ''); @endphp
                                        @if($imgVal && !str_contains((string)$imgVal, 'livewire-file'))
                                            <div class="dce-dz has"><img src="{{ upload_url($imgVal) }}" alt=""></div>
                                        @else
                                            <div class="dce-dz"><span style="font-size:22px;">⤒</span><span>Arrastra imagen o haz clic</span></div>
                                        @endif
                                        <input type="text" class="dce-in" style="margin-top:4px;" value="{{ $imgVal }}"
                                               wire:change="updateField('{{ $sKey }}', '{{ $fKey }}', '{{ $activeLang }}', $event.target.value)"
                                               placeholder="Ruta de imagen">
                                    @else
                                        @if($activeLang === 'es')
                                            <input type="text" class="dce-in" value="{{ $field['es'] }}"
                                                   wire:change="updateField('{{ $sKey }}', '{{ $fKey }}', 'es', $event.target.value)">
                                        @else
                                            <input type="text" class="dce-in" value="{{ $field['en'] }}"
                                                   wire:change="updateField('{{ $sKey }}', '{{ $fKey }}', 'en', $event.target.value)">
                                        @endif
                                    @endif
                                </div>
                            @endforeach

                            {{-- Section footer --}}
                            <div class="dce-card-f">
                                <button wire:click="addField('{{ $sKey }}')" class="dce-btn dce-sm dce-g">+ Agregar campo</button>
                                <div style="display:flex;align-items:center;gap:14px;">
                                    <span class="dce-st">Guardado ✓</span>
                                    <button wire:click="saveSection('{{ $sKey }}')" class="dce-btn dce-sm dce-p">Guardar sección</button>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            @empty
                <div style="background:var(--panel);border:1px dashed var(--border-s);border-radius:var(--r);padding:40px;text-align:center;color:var(--muted);">
                    No hay secciones. Agrega la primera abajo.
                </div>
            @endforelse
        </div>

        {{-- Add Section --}}
        <div class="dce-add">
            <input type="text" class="dce-in" wire:model="newSectionKey" placeholder="Nombre de la sección (ej. nuestros_valores)" style="max-width:280px;">
            <div class="sp"></div>
            <button wire:click="openNewSection" class="dce-btn dce-sm dce-g" style="display:none;">cancelar</button>
            <button wire:click="createSection" class="dce-btn dce-sm dce-p">+ Agregar sección</button>
        </div>
    @else
        <div style="text-align:center;padding:60px;color:var(--muted);">Selecciona una página arriba</div>
    @endif

    {{-- Delete Confirmation Modal --}}
    @if($deleteSectionKey)
        <div style="position:fixed;inset:0;z-index:9999;display:flex;align-items:center;justify-content:center;background:rgba(0,0,0,0.7);">
            <div style="background:var(--panel);border:1px solid var(--border-s);border-radius:12px;padding:24px;max-width:400px;width:90%;">
                <h3 style="margin:0 0 8px;font-size:16px;color:#e0625f;">Eliminar sección</h3>
                <p style="margin:0 0 20px;font-size:13px;color:var(--text2);">
                    Se eliminará <strong>"{{ $deleteSectionKey }}"</strong> y todos sus campos en todos los idiomas.
                </p>
                <div style="display:flex;justify-content:flex-end;gap:8px;">
                    <button wire:click="$set('deleteSectionKey', null)" class="dce-btn dce-sm">Cancelar</button>
                    <button wire:click="deleteSection('{{ $deleteSectionKey }}')" class="dce-btn dce-sm" style="background:#e0625f;color:#fff;border-color:#e0625f;">Eliminar</button>
                </div>
            </div>
        </div>
    @endif
</div>
