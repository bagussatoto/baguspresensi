<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class StaticScheduleUploadTest extends TestCase
{
    private string $testUploadDir;

    protected function setUp(): void
    {
        parent::setUp();
        $this->testUploadDir = public_path('uploads/jadwal');
        if (! File::exists($this->testUploadDir)) {
            File::makeDirectory($this->testUploadDir, 0777, true);
        }
    }

    public function test_can_upload_static_schedule_via_warnet_endpoint_without_database(): void
    {
        $file = UploadedFile::fake()->image('jadwal_baru.jpg', 600, 400);

        $response = $this->post(route('warnet.schedule.upload-static'), [
            'image' => $file,
        ]);

        $response->assertRedirect();

        $targetFile = public_path('uploads/jadwal/jadwal-shift-current.jpg');
        $this->assertTrue(File::exists($targetFile));

        $infoFile = public_path('uploads/jadwal/info.json');
        $this->assertTrue(File::exists($infoFile));

        $info = json_decode(File::get($infoFile), true);
        $this->assertEquals('jadwal-shift-current.jpg', $info['filename']);
        $this->assertArrayHasKey('updated_at', $info);
    }

    public function test_can_upload_static_schedule_via_admin_endpoint_without_database(): void
    {
        $file = UploadedFile::fake()->image('jadwal_admin.png', 800, 600);

        $response = $this->post(route('admin.jadwal.upload-static'), [
            'image' => $file,
        ]);

        $response->assertRedirect(route('admin.jadwal'));

        $targetFile = public_path('uploads/jadwal/jadwal-shift-current.jpg');
        $this->assertTrue(File::exists($targetFile));
    }

    public function test_rejects_non_image_files(): void
    {
        $file = UploadedFile::fake()->create('document.pdf', 100);

        $response = $this->post(route('warnet.schedule.upload-static'), [
            'image' => $file,
        ]);

        $response->assertSessionHasErrors('image');
    }
}
