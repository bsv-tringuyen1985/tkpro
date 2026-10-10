<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Models\Post;
use App\Models\Product;
use App\Models\ProductCategory;
use Database\Seeders\CategorySeeder;

class PublicApiTest extends FeatureTestCase
{
    public function test_danh_sach_san_pham_dung_envelope(): void
    {
        Product::factory()->count(3)->create(['status' => 'published']);

        $this->getJson('/api/v1/products')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'data' => [['id', 'name', 'slug', 'price', 'price_display', 'category']],
                'meta' => ['current_page', 'per_page', 'total', 'last_page'],
            ]);
    }

    public function test_san_pham_draft_khong_hien_thi_public(): void
    {
        $draft = Product::factory()->create(['status' => 'draft']);

        $this->getJson("/api/v1/products/{$draft->slug}")->assertNotFound();
    }

    public function test_chi_tiet_san_pham_kem_lien_quan(): void
    {
        $category = ProductCategory::factory()->create();
        $product = Product::factory()->create(['status' => 'published', 'category_id' => $category->id]);
        Product::factory()->count(2)->create(['status' => 'published', 'category_id' => $category->id]);

        $this->getJson("/api/v1/products/{$product->slug}")
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.product.slug', $product->slug)
            ->assertJsonCount(2, 'data.related');
    }

    public function test_cay_danh_muc_kem_dem_san_pham(): void
    {
        $this->seed(CategorySeeder::class);
        $category = ProductCategory::where('slug', 'camera-giam-sat')->firstOrFail();
        Product::factory()->count(2)->create(['status' => 'published', 'category_id' => $category->id]);

        $this->getJson('/api/v1/categories')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonFragment(['slug' => 'camera-giam-sat', 'products_count' => 2]);
    }

    public function test_tim_kiem_yeu_cau_tu_khoa_toi_thieu_2_ky_tu(): void
    {
        $this->getJson('/api/v1/search?q=a')
            ->assertStatus(422)
            ->assertJsonPath('success', false);
    }

    public function test_tim_kiem_tra_ve_san_pham_va_bai_viet(): void
    {
        Product::factory()->create(['status' => 'published', 'name' => 'Camera WiFi ngoài trời']);
        Post::factory()->create(['status' => 'published', 'title' => 'Hướng dẫn chọn camera']);

        $this->getJson('/api/v1/search?q=camera')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure(['data' => ['products', 'posts']]);
    }

    public function test_sitemap_xml(): void
    {
        Product::factory()->create(['status' => 'published']);
        Page::factory()->create(['status' => 'published', 'slug' => 'gioi-thieu']);

        $response = $this->get('/sitemap.xml');

        $response->assertOk();
        $this->assertStringContainsString('application/xml', $response->headers->get('Content-Type'));
        $this->assertStringContainsString('<urlset', $response->getContent());
    }

    public function test_robots_txt(): void
    {
        $response = $this->get('/robots.txt');

        $response->assertOk();
        $this->assertStringContainsString('User-agent: *', $response->getContent());
    }

    public function test_settings_public_chi_tra_ve_nhom_cho_phep(): void
    {
        \App\Models\Setting::create(['key' => 'site_name', 'value' => 'TKPRO', 'group' => 'general']);
        \App\Models\Setting::create(['key' => 'secret_key', 'value' => 'xxx', 'group' => 'private']);

        $this->getJson('/api/v1/settings')
            ->assertOk()
            ->assertJsonPath('data.site_name', 'TKPRO')
            ->assertJsonMissing(['secret_key' => 'xxx']);
    }
}
