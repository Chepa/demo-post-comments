<?php

namespace App\Services;

use App\Models\Comment;
use App\Models\Post;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

class CommentService
{
    public function getById(int $id): Model
    {
        return Comment::findOrFail($id);
    }

    public function create(array $data, int $userId): Comment
    {
        $commentable = $this->getCommentableType($data['target_type']);

        return Comment::create([
            'user_id' => $userId,
            'commentable_id' => $data['target_id'],
            'commentable_type' => $commentable,
            'body' => $data['body'],
        ]);
    }

    private function getCommentableType(string $targetType): string
    {
        return match ($targetType) {
            'post' => Post::class,
            'comment' => Comment::class,
            default => throw new InvalidArgumentException("Unknown target type: $targetType"),
        };
    }

    public function update(Comment $comment, array $data): Comment
    {
        $comment->update($data);

        return $comment->fresh();
    }

    public function delete(Comment $comment): bool
    {
        return $comment->delete();
    }

    public function canModify(Comment $comment, int $userId): bool
    {
        return $comment->user_id === $userId;
    }
}
