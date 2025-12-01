<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Rating;
use App\Models\PaketTour;
use Illuminate\Http\Request;

class RatingController extends Controller
{
    public function store(Request $request, PaketTour $paketTour)
    {
        $request->validate([
            'nilai_rating' => 'required|integer|min:1|max:5',
            'ulasan' => 'nullable|string'
        ]);

        Rating::updateOrCreate(
            [
                'user_id' => auth()->id(),
                'paket_id' => $paketTour->id
            ],
            [
                'nilai_rating' => $request->nilai_rating,
                'ulasan' => $request->ulasan
            ]
        );

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Terima kasih atas ulasan Anda!',
                'data' => $rating,
            ]);
        }

        return back()->with('success', 'Terima kasih atas ulasan Anda!');
    }
}
