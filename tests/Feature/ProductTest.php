<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected string $token;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create();
        $this->token = $this->user->createToken('auth_token')->plainTextToken;
    }

    public function test_guest_cannot_access_products(): void
    {
        $response = $this->getJson('/api/products');
        $response->assertStatus(401);
    }

    public function test_authenticated_user_can_list_products(): void
    {
        Product::factory()->count(5)->create();

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson('/api/products');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'name', 'category', 'price', 'rating', 'image'],
                ],
                'links',
            ]);
    }

    public function test_can_search_products_by_name(): void
    {
        Product::factory()->create(['name' => 'Unique Product Name']);
        Product::factory()->create(['name' => 'Another Product']);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson('/api/products?search=Unique');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data');
    }

    public function test_can_filter_products_by_category(): void
    {
        Product::factory()->create(['category' => 'Electronics']);
        Product::factory()->create(['category' => 'Books']);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson('/api/products?category=Electronics');

        $response->assertStatus(200);
        $this->assertEquals('Electronics', $response->json('data.0.category'));
    }

    public function test_can_filter_products_by_price_range(): void
    {
        Product::factory()->create(['price' => 50]);
        Product::factory()->create(['price' => 150]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson('/api/products?min_price=100');

        $response->assertStatus(200);
    }

    public function test_can_sort_products(): void
    {
        Product::factory()->create(['price' => 100]);
        Product::factory()->create(['price' => 50]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson('/api/products?sort_by=price&sort_order=asc');

        $response->assertStatus(200);
    }

    public function test_can_create_product(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson('/api/products', [
                'name' => 'Test Product',
                'category' => 'Electronics',
                'price' => 99.99,
                'rating' => 4.5,
                'description' => 'Test description',
            ]);

        $response->assertStatus(201)
            ->assertJsonFragment(['name' => 'Test Product']);

        $this->assertDatabaseHas('products', ['name' => 'Test Product']);
    }

    public function test_cannot_create_product_with_duplicate_name(): void
    {
        Product::factory()->create(['name' => 'Existing Product']);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson('/api/products', [
                'name' => 'Existing Product',
                'category' => 'Electronics',
                'price' => 99.99,
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('name');
    }

    public function test_can_view_single_product(): void
    {
        $product = Product::factory()->create();

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson('/api/products/' . $product->id);

        $response->assertStatus(200)
            ->assertJsonFragment(['id' => $product->id]);
    }

    public function test_can_update_product(): void
    {
        $product = Product::factory()->create(['name' => 'Old Name']);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->putJson('/api/products/' . $product->id, [
                'name' => 'New Name',
                'category' => 'Updated Category',
                'price' => 150,
            ]);

        $response->assertStatus(200)
            ->assertJsonFragment(['name' => 'New Name']);

        $this->assertDatabaseHas('products', ['name' => 'New Name']);
    }

    public function test_can_delete_product(): void
    {
        $product = Product::factory()->create();

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->deleteJson('/api/products/' . $product->id);

        $response->assertStatus(200)
            ->assertJson(['message' => 'Product deleted successfully']);

        $this->assertDatabaseMissing('products', ['id' => $product->id]);
    }

    public function test_can_upload_product_image(): void
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->image('product.jpg');

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson('/api/products', [
                'name' => 'Product with Image',
                'category' => 'Electronics',
                'price' => 99.99,
                'image' => $file,
            ]);

        $response->assertStatus(201);
        Storage::disk('public')->assertExists($response->json('image'));
    }
}
