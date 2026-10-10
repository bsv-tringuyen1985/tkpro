<?php

namespace Tests\Feature;

class ContactMessageTest extends FeatureTestCase
{
    public function test_gui_lien_he_thanh_cong(): void
    {
        $response = $this->postJson('/api/v1/contact-messages', [
            'name' => 'Nguyễn Văn A',
            'phone' => '0901234567',
            'email' => 'a@example.com',
            'subject' => 'Tư vấn camera',
            'message' => 'Tôi cần tư vấn lắp 4 camera cho nhà phố.',
        ]);

        $response->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.is_read', false);

        $this->assertDatabaseHas('contact_messages', [
            'phone' => '0901234567',
            'is_read' => false,
        ]);
    }

    public function test_gui_lien_he_thieu_phone_tra_ve_422(): void
    {
        $response = $this->postJson('/api/v1/contact-messages', [
            'name' => 'Nguyễn Văn A',
            'message' => 'Thiếu số điện thoại.',
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('success', false)
            ->assertJsonStructure(['errors' => ['phone']]);
    }

    public function test_gui_lien_he_bi_gioi_han_10_phut(): void
    {
        $payload = [
            'name' => 'Nguyễn Văn A',
            'phone' => '0901234567',
            'message' => 'Tin nhắn kiểm tra rate limit.',
        ];

        for ($i = 0; $i < 10; $i++) {
            $this->postJson('/api/v1/contact-messages', $payload)->assertCreated();
        }

        // Request thu 11 trong cung 1 phut bi chan.
        $this->postJson('/api/v1/contact-messages', $payload)
            ->assertStatus(429)
            ->assertJsonPath('success', false);
    }

    public function test_admin_danh_dau_da_doc_tin_nhan(): void
    {
        $admin = $this->makeUser('admin');
        $message = \App\Models\ContactMessage::factory()->create(['is_read' => false]);

        $this->putJson("/api/v1/admin/contact-messages/{$message->id}", [
            'is_read' => true,
        ], $this->authHeaders($admin))
            ->assertOk()
            ->assertJsonPath('data.is_read', true);

        $this->assertDatabaseHas('contact_messages', ['id' => $message->id, 'is_read' => true]);
    }

    public function test_editor_khong_xoa_duoc_tin_nhan_lien_he(): void
    {
        // Editor chi co contact_messages.view, khong co quyen delete.
        $editor = $this->makeUser('editor');
        $message = \App\Models\ContactMessage::factory()->create();

        $this->deleteJson("/api/v1/admin/contact-messages/{$message->id}", [], $this->authHeaders($editor))
            ->assertForbidden();
    }
}
