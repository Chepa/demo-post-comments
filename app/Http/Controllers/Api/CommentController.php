<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Comments\StoreRequest;
use App\Http\Requests\Comments\UpdateRequest;
use App\Http\Resources\CommentResource;
use App\Models\Comment;
use App\Services\CommentService;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

#[OA\Tag(name: 'Comments', description: 'API для работы с комментариями')]
class CommentController extends Controller
{
    public function __construct(
        private readonly CommentService $commentService
    ) {
    }

    #[OA\Get(
        path: '/api/comments/{id}',
        summary: 'Получить комментарий по ID',
        tags: ['Comments'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer')
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Данные комментария',
                content: new OA\JsonContent(ref: '#/components/schemas/Comment')
            ),
            new OA\Response(response: 401, description: 'Не авторизован'),
            new OA\Response(response: 404, description: 'Комментарий не найден'),
        ]
    )]
    public function show(Comment $comment)
    {
        $comment = $this->commentService->getById($comment->id);

        return new CommentResource($comment);
    }

    #[OA\Post(
        path: '/api/comments',
        summary: 'Создать новый комментарий',
        tags: ['Comments'],
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['body', 'target_type', 'target_id'],
                properties: [
                    new OA\Property(property: 'body', type: 'string', example: 'Текст комментария'),
                    new OA\Property(property: 'target_type', type: 'string', enum: ['post', 'comment'], example: 'post'),
                    new OA\Property(property: 'target_id', type: 'integer', example: 1),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Комментарий создан',
                content: new OA\JsonContent(ref: '#/components/schemas/Comment')
            ),
            new OA\Response(response: 401, description: 'Не авторизован'),
            new OA\Response(response: 422, description: 'Ошибка валидации'),
        ]
    )]
    public function store(StoreRequest $request)
    {
        $comment = $this->commentService->create(
            $request->validated(),
            $request->user()->id
        );

        return new CommentResource($comment);
    }

    #[OA\Put(
        path: '/api/comments/{id}',
        summary: 'Обновить комментарий',
        tags: ['Comments'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer')
            ),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['body'],
                properties: [
                    new OA\Property(property: 'body', type: 'string', example: 'Обновленный текст комментария'),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Комментарий обновлен',
                content: new OA\JsonContent(ref: '#/components/schemas/Comment')
            ),
            new OA\Response(response: 401, description: 'Не авторизован'),
            new OA\Response(response: 403, description: 'Нет прав на редактирование'),
            new OA\Response(response: 404, description: 'Комментарий не найден'),
        ]
    )]
    public function update(UpdateRequest $request, Comment $comment)
    {
        if (! $this->commentService->canModify($comment, $request->user()->id)) {
            abort(403, 'You are not allowed to update this comment.');
        }

        $comment = $this->commentService->update($comment, $request->validated());

        return new CommentResource($comment);
    }

    #[OA\Delete(
        path: '/api/comments/{id}',
        summary: 'Удалить комментарий',
        tags: ['Comments'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer')
            ),
        ],
        responses: [
            new OA\Response(response: 204, description: 'Комментарий удален'),
            new OA\Response(response: 401, description: 'Не авторизован'),
            new OA\Response(response: 403, description: 'Нет прав на удаление'),
            new OA\Response(response: 404, description: 'Комментарий не найден'),
        ]
    )]
    public function destroy(Request $request, Comment $comment)
    {
        if (! $this->commentService->canModify($comment, $request->user()->id)) {
            abort(403, 'You are not allowed to delete this comment.');
        }

        $this->commentService->delete($comment);

        return response()->noContent();
    }
}
