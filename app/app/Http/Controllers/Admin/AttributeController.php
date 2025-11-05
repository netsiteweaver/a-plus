<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\StoreAttributeRequest;
use App\Http\Requests\Admin\UpdateAttributeRequest;
use App\Http\Resources\Admin\AttributeResource;
use App\Models\Attribute;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class AttributeController extends AdminController
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $request->validate([
            'search' => ['nullable', 'string'],
            'include_values' => ['nullable', 'boolean'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $query = Attribute::query()->orderBy('name');

        if ($request->boolean('include_values')) {
            $query->with('values');
        }

        $query->when($request->filled('search'), function ($q) use ($request) {
            $keyword = $request->input('search');
            $q->where(function ($inner) use ($keyword) {
                $inner->where('name', 'like', "%{$keyword}%")
                    ->orWhere('code', 'like', "%{$keyword}%");
            });
        });

        $attributes = $query->paginate($request->integer('per_page', 25));

        return AttributeResource::collection($attributes);
    }

    public function store(StoreAttributeRequest $request): AttributeResource
    {
        $attribute = Attribute::create($request->validated());

        return AttributeResource::make($attribute);
    }

    public function show(Attribute $attribute): AttributeResource
    {
        $attribute->load('values');

        return AttributeResource::make($attribute);
    }

    public function update(UpdateAttributeRequest $request, Attribute $attribute): AttributeResource
    {
        $attribute->update($request->validated());

        return AttributeResource::make($attribute);
    }

    public function destroy(Attribute $attribute): Response
    {
        $attribute->values()->delete();
        $attribute->delete();

        return response()->noContent();
    }
}
