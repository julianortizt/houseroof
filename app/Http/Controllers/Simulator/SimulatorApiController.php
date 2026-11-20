<?php

namespace App\Http\Controllers\Simulator;

use App\Http\Controllers\Controller;
use App\Models\Simulator\Finish;
use App\Models\Simulator\Quote;
use App\Models\Simulator\RoomTemplate;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class SimulatorApiController extends Controller
{
    public function roomTemplates(): JsonResponse
    {
        $templates = Cache::remember('simulator.room_templates', 600, function () {
            return RoomTemplate::active()
                ->orderBy('name')
                ->get()
                ->map(fn ($t) => [
                    'id' => $t->id,
                    'name' => $t->name,
                    'slug' => $t->slug,
                    'model_url' => $t->model_glb_url,
                    'photo_url' => $t->photo_url,
                    'preview_image_url' => $t->preview_image_url,
                    'configurable_meshes' => $t->configurable_meshes,
                    'default_area_m2' => (float) $t->default_area_m2,
                ]);
        });

        return response()->json(['data' => $templates]);
    }

    public function finishes(): JsonResponse
    {
        $finishes = Cache::remember('simulator.finishes', 600, function () {
            return Finish::active()
                ->with(['activeVariants' => fn ($q) => $q->orderBy('sort_order')])
                ->orderBy('sort_order')
                ->get()
                ->map(fn ($f) => [
                    'id' => $f->id,
                    'name' => $f->name,
                    'type' => $f->type,
                    'price_per_m2' => (float) $f->price_per_m2,
                    'currency' => $f->currency,
                    'variants' => $f->activeVariants->map(fn ($v) => [
                        'id' => $v->id,
                        'name' => $v->name,
                        'base_color_hex' => $v->base_color_hex,
                        'texture_color_url' => $v->texture_color_url,
                        'texture_normal_url' => $v->texture_normal_url,
                        'texture_roughness_url' => $v->texture_roughness_url,
                        'preview_image_url' => $v->preview_image_url,
                        'chip_density' => $v->chip_density,
                    ]),
                ]);
        });

        return response()->json(['data' => $finishes]);
    }

    public function storeQuote(Request $request): JsonResponse
    {
        $data = $request->validate([
            'customer_name' => 'nullable|string|max:120',
            'customer_email' => 'nullable|email|max:150',
            'customer_phone' => 'nullable|string|max:30',
            'room_template_id' => 'required|exists:simulator_room_templates,id',
            'finish_variant_id' => 'required|exists:simulator_finish_variants,id',
            'target_surface' => 'required|in:floor,counter',
            'area_m2' => 'required|numeric|min:0.5|max:2000',
        ]);

        // Get the finish variant and its parent finish for pricing
        $variant = \App\Models\Simulator\FinishVariant::with('finish')->findOrFail($data['finish_variant_id']);

        // Server-side price calculation (never trust client)
        $pricePerM2 = $variant->finish->price_per_m2;
        $area = $data['area_m2'];
        $totalPrice = round($pricePerM2 * $area, 2);

        $quote = Quote::create([
            'customer_name' => $data['customer_name'] ?? null,
            'customer_email' => $data['customer_email'] ?? null,
            'customer_phone' => $data['customer_phone'] ?? null,
            'simulator_room_template_id' => $data['room_template_id'],
            'simulator_finish_variant_id' => $data['finish_variant_id'],
            'target_surface' => $data['target_surface'],
            'area_m2' => $area,
            'price_per_m2_snapshot' => $pricePerM2,
            'total_price' => $totalPrice,
            'status' => 'pending',
        ]);

        // Invalidate cache for quotes count if needed
        Cache::forget('simulator.quotes_count');

        return response()->json([
            'data' => [
                'id' => $quote->id,
                'reference' => 'SIM-' . str_pad((string) $quote->id, 5, '0', STR_PAD_LEFT),
                'total_price' => $totalPrice,
                'price_per_m2' => $pricePerM2,
                'area_m2' => $area,
                'status' => 'pending',
            ],
        ], 201);
    }
}
