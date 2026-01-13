<?php

namespace App\Services;

use App\Models\Post;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class PostService
{
    public function getAll(?string $type = null): LengthAwarePaginator
    {
        $query = Post::query();

        if ($type) {
            $query->where('type', $type);
        }

        return $query->latest()->paginate(15);
    }

    public function getById(int $id): Post
    {
        $post = Post::with('comments')->findOrFail($id);

        return $post;
    }

    public function create(array $data): Post
    {
        return Post::create($data);
    }

    public function update(Post $post, array $data): Post
    {
        $post->update($data);

        return $post->fresh();
    }

    public function delete(Post $post): bool
    {
        return $post->delete();
    }
}
