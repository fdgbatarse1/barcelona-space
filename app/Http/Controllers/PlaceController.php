<?php

namespace App\Http\Controllers;

use App\Models\Place;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class PlaceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $places = Place::simplePaginate(10);

        return view('places.index', compact('places'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        abort_if(!Auth::check(), 403);

        return view('places.create', [
            'place' => new Place(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        abort_if(!Auth::check(), 403);

        $data = $this->validatePlace($request);
        $data['user_id'] = $request->user()->id;

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('places', 'public');
        }

        $place = Place::create($data);

        return redirect()
            ->route('places.show', $place)
            ->with('status', 'Place created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Place $place): View
    {
        $comments = $place->comments()->latest()->simplePaginate(5);
        return view('places.show', compact('place', 'comments'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Place $place): View
    {
        $this->authorizePlace($place);

        return view('places.edit', compact('place'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Place $place): RedirectResponse
    {
        $this->authorizePlace($place);

        $data = $this->validatePlace($request);

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('places', 'public');
        }

        $place->update($data);

        return redirect()
            ->route('places.show', $place)
            ->with('status', 'Place updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Place $place): RedirectResponse
    {
        $this->authorizePlace($place);
        $place->delete();

        return redirect()
            ->route('places.index')
            ->with('status', 'Place deleted successfully.');
    }

    /**
     * Validate a request payload for storing/updating a place.
     */
    protected function validatePlace(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'address' => ['nullable', 'string', 'max:255'],
            'image' => ['nullable', 'image', 'max:2048'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
        ]);
    }

    /**
     * Ensure the authenticated user owns the given place.
     */
    protected function authorizePlace(Place $place): void
    {
        abort_if($place->user_id !== Auth::id(), 403);
    }
}
