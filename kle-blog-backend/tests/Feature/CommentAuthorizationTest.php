<?php

namespace Tests\Feature;

use App\Models\Comment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommentAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_cannot_update_someone_elses_comment(): void
    {
        $comment = Comment::factory()->create(['content' => 'Orijinal']);
        $intruder = User::factory()->create();

        $this->actingAs($intruder, 'sanctum')
            ->putJson('/api/comments/'.$comment->id, ['content' => 'Ele geçirildi'])
            ->assertForbidden();

        $this->assertDatabaseHas('comments', ['id' => $comment->id, 'content' => 'Orijinal']);
    }

    public function test_user_cannot_delete_someone_elses_comment(): void
    {
        $comment = Comment::factory()->create();
        $intruder = User::factory()->create();

        $this->actingAs($intruder, 'sanctum')
            ->deleteJson('/api/comments/'.$comment->id)
            ->assertForbidden();

        $this->assertModelExists($comment);
    }

    public function test_owner_update_resets_comment_approval(): void
    {
        $owner = User::factory()->create();
        $comment = Comment::factory()->for($owner)->create(['is_approved' => true]);

        $this->actingAs($owner, 'sanctum')
            ->patchJson('/api/comments/'.$comment->id, ['content' => 'Düzenlenmiş yorum'])
            ->assertOk()
            ->assertJsonPath('data.content', 'Düzenlenmiş yorum')
            ->assertJsonPath('data.is_approved', false);
    }

    public function test_owner_can_delete_own_comment(): void
    {
        $owner = User::factory()->create();
        $comment = Comment::factory()->for($owner)->create();

        $this->actingAs($owner, 'sanctum')
            ->deleteJson('/api/comments/'.$comment->id)
            ->assertOk();

        $this->assertModelMissing($comment);
    }

    public function test_admin_can_moderate_any_comment_and_keeps_approval(): void
    {
        $admin = User::factory()->admin()->create();
        $comment = Comment::factory()->create(['is_approved' => true]);

        $this->actingAs($admin, 'sanctum')
            ->putJson('/api/comments/'.$comment->id, ['content' => 'Moderasyon'])
            ->assertOk()
            ->assertJsonPath('data.is_approved', true);

        $this->actingAs($admin, 'sanctum')
            ->deleteJson('/api/comments/'.$comment->id)
            ->assertOk();

        $this->assertModelMissing($comment);
    }

    public function test_comment_update_requires_content(): void
    {
        $owner = User::factory()->create();
        $comment = Comment::factory()->for($owner)->create();

        $this->actingAs($owner, 'sanctum')
            ->putJson('/api/comments/'.$comment->id, ['content' => ''])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['content']);
    }

    public function test_guest_cannot_modify_comments(): void
    {
        $comment = Comment::factory()->create();

        $this->putJson('/api/comments/'.$comment->id, ['content' => 'Misafir'])->assertUnauthorized();
        $this->deleteJson('/api/comments/'.$comment->id)->assertUnauthorized();
    }
}
