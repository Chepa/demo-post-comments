<?php

namespace Tests\Feature\Api;

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommentTest extends TestCase
{
    use RefreshDatabase;

    private function getAuthToken(User $user): string
    {
        return $user->createToken('api')->plainTextToken;
    }

    public function test_user_can_get_single_comment(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->create();
        $comment = Comment::factory()->create([
            'user_id' => $user->id,
            'commentable_id' => $post->id,
            'commentable_type' => Post::class,
            'body' => 'Test comment',
        ]);

        $token = $this->getAuthToken($user);

        $response = $this->withHeaders([
            'Authorization' => "Bearer {$token}",
        ])->getJson("/api/comments/{$comment->id}");

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'id' => $comment->id,
                    'body' => 'Test comment',
                    'user_id' => $user->id,
                ]
            ]);
    }

    public function test_user_cannot_get_comment_without_authentication(): void
    {
        $comment = Comment::factory()->create();

        $response = $this->getJson("/api/comments/{$comment->id}");

        $response->assertStatus(401);
    }

    public function test_user_can_create_comment_to_post(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->create();
        $token = $this->getAuthToken($user);

        $response = $this->withHeaders([
            'Authorization' => "Bearer {$token}",
        ])->postJson('/api/comments', [
            'body' => 'Great post!',
            'target_type' => 'post',
            'target_id' => $post->id,
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'data' => [
                    'body' => 'Great post!',
                    'user_id' => $user->id,
                ]
            ]);

        $this->assertDatabaseHas('comments', [
            'user_id' => $user->id,
            'commentable_id' => $post->id,
            'commentable_type' => Post::class,
            'body' => 'Great post!',
        ]);
    }

    public function test_user_can_create_comment_to_comment(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->create();
        $parentComment = Comment::factory()->create([
            'user_id' => User::factory()->create()->id,
            'commentable_id' => $post->id,
            'commentable_type' => Post::class,
        ]);

        $token = $this->getAuthToken($user);

        $response = $this->withHeaders([
            'Authorization' => "Bearer {$token}",
        ])->postJson('/api/comments', [
            'body' => 'Reply to comment',
            'target_type' => 'comment',
            'target_id' => $parentComment->id,
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'data' => [
                    'body' => 'Reply to comment',
                    'user_id' => $user->id,
                ]
            ]);

        $this->assertDatabaseHas('comments', [
            'user_id' => $user->id,
            'commentable_id' => $parentComment->id,
            'commentable_type' => Comment::class,
            'body' => 'Reply to comment',
        ]);
    }

    public function test_user_cannot_create_comment_without_authentication(): void
    {
        $post = Post::factory()->create();

        $response = $this->postJson('/api/comments', [
            'body' => 'Test comment',
            'target_type' => 'post',
            'target_id' => $post->id,
        ]);

        $response->assertStatus(401);
    }

    public function test_user_cannot_create_comment_with_invalid_data(): void
    {
        $user = User::factory()->create();
        $token = $this->getAuthToken($user);

        $response = $this->withHeaders([
            'Authorization' => "Bearer $token",
        ])->postJson('/api/comments', [
            'body' => '',
            'target_type' => 'invalid',
            'target_id' => 999,
        ]);

        $response->assertStatus(422);
    }

    public function test_user_can_update_own_comment(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->create();
        $comment = Comment::factory()->create([
            'user_id' => $user->id,
            'commentable_id' => $post->id,
            'commentable_type' => Post::class,
            'body' => 'Original comment',
        ]);

        $token = $this->getAuthToken($user);

        $response = $this->withHeaders([
            'Authorization' => "Bearer {$token}",
        ])->putJson("/api/comments/{$comment->id}", [
            'body' => 'Updated comment',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'body' => 'Updated comment',
                ]
            ]);

        $this->assertDatabaseHas('comments', [
            'id' => $comment->id,
            'body' => 'Updated comment',
        ]);
    }

    public function test_user_cannot_update_other_user_comment(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $post = Post::factory()->create();
        $comment = Comment::factory()->create([
            'user_id' => $owner->id,
            'commentable_id' => $post->id,
            'commentable_type' => Post::class,
        ]);

        $token = $this->getAuthToken($otherUser);

        $response = $this->withHeaders([
            'Authorization' => "Bearer {$token}",
        ])->putJson("/api/comments/{$comment->id}", [
            'body' => 'Hacked comment',
        ]);

        $response->assertStatus(403);
    }

    public function test_user_cannot_update_comment_without_authentication(): void
    {
        $comment = Comment::factory()->create();

        $response = $this->putJson("/api/comments/{$comment->id}", [
            'body' => 'Updated comment',
        ]);

        $response->assertStatus(401);
    }

    public function test_user_can_delete_own_comment(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->create();
        $comment = Comment::factory()->create([
            'user_id' => $user->id,
            'commentable_id' => $post->id,
            'commentable_type' => Post::class,
        ]);

        $token = $this->getAuthToken($user);

        $response = $this->withHeaders([
            'Authorization' => "Bearer {$token}",
        ])->deleteJson("/api/comments/{$comment->id}");

        $response->assertStatus(204);

        $this->assertDatabaseMissing('comments', [
            'id' => $comment->id,
        ]);
    }

    public function test_user_cannot_delete_other_user_comment(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $post = Post::factory()->create();
        $comment = Comment::factory()->create([
            'user_id' => $owner->id,
            'commentable_id' => $post->id,
            'commentable_type' => Post::class,
        ]);

        $token = $this->getAuthToken($otherUser);

        $response = $this->withHeaders([
            'Authorization' => "Bearer {$token}",
        ])->deleteJson("/api/comments/{$comment->id}");

        $response->assertStatus(403);
    }

    public function test_user_cannot_delete_comment_without_authentication(): void
    {
        $comment = Comment::factory()->create();

        $response = $this->deleteJson("/api/comments/{$comment->id}");

        $response->assertStatus(401);
    }
}
