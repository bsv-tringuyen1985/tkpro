<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\ProductCategory;

class AdminProductTest extends FeatureTestCase
{
    public function test_admin_tao_san_pham_thanh_cong(): void
    {
        $admin = $this->makeUser('super-admin');
        $category = ProductCategory::factory()->create();

        $response = $this->postJson('/api/v1/admin/products', [
            'category_id' => $category->id,
            'name' => 'Camera WiFi 2K trong nhà',
            'sku' => 'CAM-WIFI-2K',
            'price' => 890000,
            'status' => 'published',
            'featured' => true,
        ], $this->authHeaders($admin));

        $response->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.slug', 'camera-wifi-2k-trong-nha')
            ->assertJsonPath('data.price_display', 'Giá tham khảo: 890.000₫');

        $this->assertDatabaseHas('products', ['slug' => 'camera-wifi-2k-trong-nha']);
    }

    public function test_admin_cap_nhat_va_xoa_san_pham(): void
    {
        $admin = $this->makeUser('admin');
        $product = Product::factory()->create();

        $this->putJson("/api/v1/admin/products/{$product->id}", [
            'name' => 'Tên mới',
            'price' => 1000000,
        ], $this->authHeaders($admin))
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.slug', 'ten-moi');

        $this->deleteJson("/api/v1/admin/products/{$product->id}", [], $this->authHeaders($admin))
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseMissing('products', ['id' => $product->id]);
    }

    public function test_editor_khong_duoc_xoa_san_pham(): void
    {
        $editor = $this->makeUser('editor');
        $product = Product::factory()->create();

        // Editor co quyen create nhung KHONG co quyen delete.
        $this->deleteJson("/api/v1/admin/products/{$product->id}", [], $this->authHeaders($editor))
            ->assertForbidden()
            ->assertJsonPath('success', false);

        $this->assertDatabaseHas('products', ['id' => $product->id]);
    }

    public function test_editor_duoc_tao_san_pham(): void
    {
        $editor = $this->makeUser('editor');
        $category = ProductCategory::factory()->create();

        $this->postJson('/api/v1/admin/products', [
            'category_id' => $category->id,
            'name' => 'Sản phẩm của editor',
            'status' => 'draft',
        ], $this->authHeaders($editor))->assertCreated();
    }

    public function test_chua_dang_nhap_khong_truy_cap_duoc_admin(): void
    {
        $this->getJson('/api/v1/admin/products')->assertUnauthorized();
        $this->postJson('/api/v1/admin/products', [])->assertUnauthorized();
    }

    public function test_tao_san_pham_thieu_du_lieu_tra_ve_422(): void
    {
        $admin = $this->makeUser('super-admin');

        $response = $this->postJson('/api/v1/admin/products', [
            'status' => 'published',
        ], $this->authHeaders($admin));

        $response->assertStatus(422)
            ->assertJsonPath('success', false)
            ->assertJsonStructure(['errors' => ['category_id', 'name']]);
    }

    public function test_slug_tu_sinh_khi_trung_ten(): void
    {
        $admin = $this->makeUser('super-admin');
        $category = ProductCategory::factory()->create();
        Product::factory()->create(['name' => 'Camera WiFi', 'slug' => 'camera-wifi', 'category_id' => $category->id]);

        $response = $this->postJson('/api/v1/admin/products', [
            'category_id' => $category->id,
            'name' => 'Camera WiFi',
            'status' => 'draft',
        ], $this->authHeaders($admin));

        $response->assertCreated()->assertJsonPath('data.slug', 'camera-wifi-2');
    }
}
