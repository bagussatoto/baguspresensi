<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\ShiftScheduleImage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class AdminScheduleTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_schedule_page_can_be_rendered(): void
    {
        $schedule = ShiftScheduleImage::create([
            'title' => 'Jadwal Shift Operator Maya (12 - 18)',
            'image_path' => 'uploads/jadwal/jadwal-shift-current.jpg',
            'period' => '12 - 18 Oktober',
            'is_active' => true,
        ]);

        $response = $this->get(route('admin.jadwal'));

        $response->assertStatus(200);
        $response->assertSee('jadwal shift operator');
        $response->assertSee('Jadwal Shift Operator Maya (12 - 18)');
    }

    public function test_admin_can_upload_new_schedule_image(): void
    {
        $file = UploadedFile::fake()->image('jadwal-baru.jpg', 800, 600);

        $response = $this->post(route('admin.jadwal.store'), [
            'image' => $file,
            'title' => 'Jadwal Minggu Depan',
            'period' => '19 - 25 Oktober',
            'notes' => 'Rotasi 4 shift normal',
        ]);

        $response->assertRedirect(route('admin.jadwal'));

        $this->assertDatabaseHas('shift_schedule_images', [
            'title' => 'Jadwal Minggu Depan',
            'period' => '19 - 25 Oktober',
            'is_active' => true,
        ]);
    }

    public function test_admin_can_set_active_schedule(): void
    {
        $old = ShiftScheduleImage::create([
            'title' => 'Jadwal Lama',
            'image_path' => 'uploads/jadwal/test-old.jpg',
            'is_active' => true,
        ]);

        $new = ShiftScheduleImage::create([
            'title' => 'Jadwal Baru',
            'image_path' => 'uploads/jadwal/test-new.jpg',
            'is_active' => false,
        ]);

        $response = $this->postJson(route('admin.jadwal.set-active', $new));

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->assertFalse($old->fresh()->is_active);
        $this->assertTrue($new->fresh()->is_active);
    }

    public function test_admin_can_delete_schedule(): void
    {
        $schedule = ShiftScheduleImage::create([
            'title' => 'Jadwal Dihapus',
            'image_path' => 'uploads/jadwal/temp.jpg',
            'is_active' => false,
        ]);

        $response = $this->deleteJson(route('admin.jadwal.destroy', $schedule));

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->assertDatabaseMissing('shift_schedule_images', [
            'id' => $schedule->id,
        ]);
    }
}
