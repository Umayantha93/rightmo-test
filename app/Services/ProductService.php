<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;

class ProductService
{
    /**
     * Get filtered and paginated products
     */
    public function getProducts(Request $request)
    {
        $query = Product::query();

        // Search by product name
        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        // Filter by category
        if ($request->has('category')) {
            $query->where('category', $request->category);
        }

        // Filter by price range
        if ($request->has('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }
        if ($request->has('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }

        // Sort
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        
        if (in_array($sortBy, ['price', 'rating', 'created_at', 'name'])) {
            $query->orderBy($sortBy, $sortOrder);
        }

        // Pagination
        $perPage = $request->get('per_page', 15);
        
        return $query->paginate($perPage);
    }

    /**
     * Create a new product
     */
    public function createProduct(array $data)
    {
        if (isset($data['image']) && is_object($data['image'])) {
            $data['image'] = $data['image']->store('products', 'public');
        }

        return Product::create($data);
    }

    /**
     * Update a product
     */
    public function updateProduct(Product $product, array $data)
    {
        if (isset($data['image']) && is_object($data['image'])) {
            // Delete old image if exists
            if ($product->image) {
                Storage::disk('public')->delete($product->image);
            }
            $data['image'] = $data['image']->store('products', 'public');
        } else {
            // Remove image from data if it's not being updated
            unset($data['image']);
        }

        $product->update($data);

        return $product->fresh();
    }

    /**
     * Delete a product
     */
    public function deleteProduct(Product $product)
    {
        // Delete image if exists
        if ($product->image) {
            Storage::disk('public')->delete($product->image);
        }

        $product->delete();

        return true;
    }
}
