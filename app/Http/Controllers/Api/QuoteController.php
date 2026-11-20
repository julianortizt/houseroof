<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\QuoteResource;
use App\Models\Quote;
use Illuminate\Http\Request;

class QuoteController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $quotes = Quote::query()
            ->when($user, fn ($q) => $q->where('email', $user->email))
            ->latest()
            ->get();

        return QuoteResource::collection($quotes);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'service_type' => 'nullable|string|in:resin,granite,other',
            'area_m2' => 'nullable|numeric',
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:60',
            'company' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:500',
            'message' => 'nullable|string|max:2000',
            'items' => 'nullable|array',
        ]);

        $quote = Quote::create([
            'service_type' => $data['service_type'] ?? 'resin',
            'area_m2' => $data['area_m2'] ?? null,
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'company' => $data['company'] ?? null,
            'address' => $data['address'] ?? null,
            'message' => $data['message'] ?? null,
            'items' => json_encode($data['items'] ?? []),
            'status' => 'new',
        ]);

        return (new QuoteResource($quote))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Request $request, int $id)
    {
        $user = $request->user();

        $quote = Quote::query()
            ->when($user, fn ($q) => $q->where('email', $user->email))
            ->find($id);

        if (! $quote) {
            return response()->json(['message' => 'Cotización no encontrada.'], 404);
        }

        return new QuoteResource($quote);
    }
}
