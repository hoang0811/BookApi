<?php

namespace App\Http\Controllers;

use App\Models\CoPublisher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Http\Resources\CoPublisherResource;
use Illuminate\Support\Facades\Validator;

class CoPublisherController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $coPublishers = CoPublisher::all();
        return new CoPublisherResource(true, 'Data retrieved successfully', $coPublishers);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'  => 'required',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $image = $request->file('image');
        $imagePath = $image->storeAs('co_publishers', $image->hashName(), 'public');

        $coPublisher = CoPublisher::create([
            'name'  => $request->name,
            'image' => basename($imagePath)
        ]);

        return new CoPublisherResource(true, 'The co-publisher has been created successfully!', $coPublisher);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $coPublisher = CoPublisher::findOrFail($id);
        return new CoPublisherResource(true, 'Detail Data CoPublisher!', $coPublisher);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $coPublisher = CoPublisher::findOrFail($id);

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imagePath = $image->storeAs('co_publishers', $image->hashName(), 'public');

            if ($coPublisher->image) {
                Storage::delete('public/co_publishers/' . $coPublisher->image);
            }

            $coPublisher->update([
                'name'  => $request->name,
                'image' => basename($imagePath),
            ]);
        } else {
            $coPublisher->update([
                'name' => $request->name,
            ]);
        }

        return new CoPublisherResource(true, 'CoPublisher data has been updated successfully!', $coPublisher);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $coPublisher = CoPublisher::findOrFail($id);
        if (Storage::exists('public/co_publishers/' . basename($coPublisher->image))) {
            Storage::delete('public/co_publishers/' . basename($coPublisher->image));
        }
    
        $coPublisher->delete();

        return new CoPublisherResource(true, 'CoPublisher has been deleted successfully!', null);
    }
}
