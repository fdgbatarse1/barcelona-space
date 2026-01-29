<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StorePlaceRequest;
use App\Http\Requests\Api\UpdatePlaceRequest;
use App\Http\Resources\PlaceResource;
use App\Models\Place;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PlaceController extends Controller
{
    /**
     * Display a listing of places with filtering, sorting, and pagination.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Place::query()->with('user')->withCount('comments');

        // Search filter (searches in name and description)
        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('address', 'like', "%{$search}%");
            });
        }

        // Filter by user_id
        if ($userId = $request->query('user_id')) {
            $query->where('user_id', $userId);
        }

        // Filter by latitude/longitude bounds (for map views)
        if ($request->has(['lat_min', 'lat_max', 'lng_min', 'lng_max'])) {
            $query->whereBetween('latitude', [$request->query('lat_min'), $request->query('lat_max')])
                ->whereBetween('longitude', [$request->query('lng_min'), $request->query('lng_max')]);
        }

        // Sorting (prefix with - for descending)
        $sortField = $request->query('sort', '-created_at');
        $sortDirection = 'asc';

        if (str_starts_with($sortField, '-')) {
            $sortDirection = 'desc';
            $sortField = substr($sortField, 1);
        }

        // Only allow sorting by specific fields
        $allowedSorts = ['name', 'created_at', 'updated_at'];
        if (in_array($sortField, $allowedSorts)) {
            $query->orderBy($sortField, $sortDirection);
        } else {
            $query->orderBy('created_at', 'desc');
        }

        // Pagination
        $perPage = min((int) $request->query('per_page', 15), 100);

        return PlaceResource::collection($query->paginate($perPage));
    }

    /**
     * Store a newly created place in storage.
     */
    public function store(StorePlaceRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['user_id'] = $request->user()->id;

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('places', 'public');
        }

        $place = Place::create($data);
        $place->load('user');

        return response()->json([
            'message' => 'Place created successfully.',
            'data' => new PlaceResource($place),
        ], 201);
    }

    /**
     * Display the specified place.
     */
    public function show(Place $place): JsonResponse
    {
        $place->load('user')->loadCount('comments');

        return response()->json([
            'data' => new PlaceResource($place),
        ]);
    }

    /**
     * Update the specified place in storage.
     */
    public function update(UpdatePlaceRequest $request, Place $place): JsonResponse
    {
        // Authorization is handled in the form request
        $data = $request->validated();

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('places', 'public');
        }

        $place->update($data);
        $place->load('user')->loadCount('comments');

        return response()->json([
            'message' => 'Place updated successfully.',
            'data' => new PlaceResource($place),
        ]);
    }

    /**
     * Remove the specified place from storage.
     */
    public function destroy(Request $request, Place $place): JsonResponse
    {
        // Check ownership
        if ($place->user_id !== $request->user()->id) {
            return response()->json([
                'message' => 'You are not authorized to delete this place.',
            ], 403);
        }

        $place->delete();

        return response()->json([
            'message' => 'Place deleted successfully.',
        ], 200);
    }
}

