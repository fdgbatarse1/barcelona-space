<?php

namespace App\Http\Controllers;

use App\Models\Place;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Sentry\Breadcrumb;
use Sentry\Laravel\Facades\Sentry;

class PlaceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $page = $request->get('page', 1);
        $places = cache()->remember('places_index_page_' . $page, config('app.cache_ttl'), function () {
            return Place::latest()->paginate(10);
        });

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
            $data['image'] = $request->file('image')->store('places');
        }

        $place = Place::create($data);

        cache()->forget('places_index_page_1');

        $this->addPlaceBreadcrumb('created', $place, $request->user()?->id);

        return redirect()
            ->route('places.show', $place)
            ->with('status', 'Place created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Place $place): View
    {
        $place->load('user');

        $page = request()->get('page', 1);
        $comments = cache()->remember('place_' . $place->id . '_comments_page_' . $page, config('app.cache_ttl'), function () use ($place) {
            return $place->comments()->with('user')->latest()->paginate(5);
        });

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
            $data['image'] = $request->file('image')->store('places');
        }

        $place->update($data);

        cache()->forget('places_index_page_1');

        $this->addPlaceBreadcrumb('updated', $place, $request->user()?->id);

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

        $this->addPlaceBreadcrumb('deleted', $place, Auth::id());
        $place->delete();

        cache()->forget('places_index_page_1');

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

    /**
     * Record a Sentry breadcrumb for key place actions.
     */
    protected function addPlaceBreadcrumb(string $action, Place $place, ?int $userId = null): void
    {
        if (!app()->bound('sentry')) {
            return;
        }

        Sentry::addBreadcrumb(new Breadcrumb(
            Breadcrumb::LEVEL_INFO,
            Breadcrumb::TYPE_DEFAULT,
            "place.{$action}",
            "Place {$action}",
            [
                'place_id' => $place->id,
                'user_id' => $userId,
            ]
        ));
    }
}
