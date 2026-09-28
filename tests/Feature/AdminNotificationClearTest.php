<?php

namespace Tests\Feature;

use App\Models\AdminNotification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminNotificationClearTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['app.url' => 'http://localhost']);
        \Illuminate\Support\Facades\URL::forceRootUrl('http://localhost');
    }

    protected function staffUser(): User
    {
        return User::factory()->create([
            'is_staff' => true,
            'is_active' => true,
        ]);
    }

    protected function makeNotification(User $user, string $title = 'New order received'): AdminNotification
    {
        return AdminNotification::create([
            'user_id' => $user->id,
            'type' => 'order_created',
            'title' => $title,
            'message' => 'A customer placed a new order',
            'is_read' => false,
        ]);
    }

    public function test_admin_can_clear_a_single_notification(): void
    {
        $admin = $this->staffUser();
        $keep = $this->makeNotification($admin, 'Keep this');
        $gone = $this->makeNotification($admin, 'Clear this');

        $this->actingAs($admin)
            ->delete('/admin/notifications/'.$gone->id)
            ->assertRedirect();

        $this->assertDatabaseMissing('admin_notifications', ['id' => $gone->id]);
        $this->assertDatabaseHas('admin_notifications', ['id' => $keep->id]);
    }

    public function test_admin_can_clear_all_notifications(): void
    {
        $admin = $this->staffUser();
        $this->makeNotification($admin, 'First');
        $this->makeNotification($admin, 'Second');

        $this->actingAs($admin)
            ->delete('/admin/notifications/clear-all')
            ->assertRedirect();

        $this->assertSame(0, AdminNotification::query()->where('user_id', $admin->id)->count());
    }

    public function test_notification_screens_include_clear_actions(): void
    {
        $admin = $this->staffUser();
        $this->makeNotification($admin, 'Ring order');

        $this->actingAs($admin)
            ->get('/admin/notifications')
            ->assertOk()
            ->assertSee('Clear all', false)
            ->assertSee('Clear this notification?', false)
            ->assertSee('Mark all read', false);
    }

    public function test_admin_cannot_clear_another_admins_notification(): void
    {
        $admin = $this->staffUser();
        $other = $this->staffUser();
        $note = $this->makeNotification($other, 'Other admin');

        $this->actingAs($admin)
            ->delete('/admin/notifications/'.$note->id)
            ->assertForbidden();

        $this->assertDatabaseHas('admin_notifications', ['id' => $note->id]);
    }
}
