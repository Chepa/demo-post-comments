<?php

namespace Tests\Feature\Api;

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostTest extends TestCase
{
    use RefreshDatabase;

    private function getAuthToken(User $user): string
    {
        return $user->createToken('api')->plainTextToken;
    }

    public function test_user_can_get_list_of_posts(): void
    {
        Post::factory()->count(3)->create();

        $response = $this->getJson('/api/posts');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'type', 'title', 'description', 'created_at', 'updated_at'],
                ],
            ]);

        $this->assertCount(3, $response->json('data'));
    }

    public function test_user_can_filter_posts_by_type(): void
    {
        Post::factory()->create(['type' => Post::TYPE_VIDEO]);
        Post::factory()->create(['type' => Post::TYPE_NEWS]);
        Post::factory()->create(['type' => Post::TYPE_VIDEO]);

        $response = $this->getJson('/api/posts?type=video');

        $response->assertStatus(200);
        $this->assertCount(2, $response->json('data'));
        $response->assertJsonFragment(['type' => Post::TYPE_VIDEO]);
    }

    public function test_user_can_get_single_post(): void
    {
        $post = Post::factory()->create([
            'type' => Post::TYPE_VIDEO,
            'title' => 'Test Video',
            'description' => 'Test Description',
        ]);

        $response = $this->getJson("/api/posts/{$post->id}");

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'id' => $post->id,
                    'type' => Post::TYPE_VIDEO,
                    'title' => 'Test Video',
                    'description' => 'Test Description',
                ]
            ]);
    }

    public function test_user_can_create_video_post(): void
    {
        $user = User::factory()->create();
        $token = $this->getAuthToken($user);

        $response = $this->withHeaders([
            'Authorization' => "Bearer {$token}",
        ])->postJson('/api/posts', [
            'type' => Post::TYPE_VIDEO,
            'title' => 'New Video Post',
            'description' => 'Video description',
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'data' => [
                    'type' => Post::TYPE_VIDEO,
                    'title' => 'New Video Post',
                    'description' => 'Video description',
                ]
            ]);

        $this->assertDatabaseHas('posts', [
            'type' => Post::TYPE_VIDEO,
            'title' => 'New Video Post',
        ]);
    }

    public function test_user_can_create_news_post(): void
    {
        $user = User::factory()->create();
        $token = $this->getAuthToken($user);

        $response = $this->withHeaders([
            'Authorization' => "Bearer {$token}",
        ])->postJson('/api/posts', [
            'type' => Post::TYPE_NEWS,
            'title' => 'New News Post',
            'description' => 'News description',
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'data' => [
                    'type' => Post::TYPE_NEWS,
                    'title' => 'New News Post',
                ]
            ]);

        $this->assertDatabaseHas('posts', [
            'type' => Post::TYPE_NEWS,
            'title' => 'New News Post',
        ]);
    }

    public function test_user_cannot_create_post_with_invalid_data(): void
    {
        $user = User::factory()->create();
        $token = $this->getAuthToken($user);

        $response = $this->withHeaders([
            'Authorization' => "Bearer {$token}",
        ])->postJson('/api/posts', [
            'type' => 'invalid-type',
            'title' => '',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['type', 'title']);
    }

    public function test_user_can_update_post(): void
    {
        $user = User::factory()->create();
        $token = $this->getAuthToken($user);
        $post = Post::factory()->create();

        $response = $this->withHeaders([
            'Authorization' => "Bearer {$token}",
        ])->putJson("/api/posts/{$post->id}", [
            'title' => 'Updated Title',
            'description' => 'Updated Description',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'title' => 'Updated Title',
                    'description' => 'Updated Description',
                ]
            ]);

        $this->assertDatabaseHas('posts', [
            'id' => $post->id,
            'title' => 'Updated Title',
        ]);
    }

    public function test_user_can_partially_update_post(): void
    {
        $user = User::factory()->create();
        $token = $this->getAuthToken($user);
        $post = Post::factory()->create([
            'title' => 'Original Title',
            'description' => 'Original Description',
        ]);

        $response = $this->withHeaders([
            'Authorization' => "Bearer {$token}",
        ])->patchJson("/api/posts/{$post->id}", [
            'title' => 'Updated Title',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'title' => 'Updated Title',
                ]
            ]);

        $this->assertDatabaseHas('posts', [
            'id' => $post->id,
            'title' => 'Updated Title',
            'description' => 'Original Description',
        ]);
    }

    public function test_user_can_delete_post(): void
    {
        $user = User::factory()->create();
        $token = $this->getAuthToken($user);
        $post = Post::factory()->create();

        $response = $this->withHeaders([
            'Authorization' => "Bearer {$token}",
        ])->deleteJson("/api/posts/{$post->id}");

        $response->assertStatus(204);

        $this->assertDatabaseMissing('posts', [
            'id' => $post->id,
        ]);
    }

    public function test_user_can_get_post_with_comments(): void
    {
        $post = Post::factory()->create();
        $user = User::factory()->create();

        $post->comments()->create([
            'user_id' => $user->id,
            'body' => 'Test comment',
        ]);

        $response = $this->getJson("/api/posts/$post->id");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'comments' => [
                        '*' => ['id', 'body', 'user_id'],
                    ],
                ]
            ]);

        $this->assertCount(1, $response->json('data.comments'));
        $this->assertEquals('Test comment', $response->json('data.comments.0.body'));
    }
}
