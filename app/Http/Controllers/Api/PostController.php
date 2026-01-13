<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Posts\StoreRequest;
use App\Http\Requests\Posts\UpdateRequest;
use App\Http\Resources\PostResource;
use App\Models\Post;
use App\Services\PostService;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

#[OA\Tag(name: 'Posts', description: 'API для работы с постами (видео и новости)')]
class PostController extends Controller
{
    public function __construct(
        private readonly PostService $postService
    ) {
    }

    #[OA\Get(
        path: '/api/posts',
        summary: 'Получить список постов',
        tags: ['Posts'],
        parameters: [
            new OA\Parameter(
                name: 'type',
                in: 'query',
                description: 'Тип поста (video или news)',
                required: false,
                schema: new OA\Schema(type: 'string', enum: ['video', 'news'])
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Список постов',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/Post')),
                    ]
                )
            ),
        ]
    )]
    public function index(Request $request)
    {
        $posts = $this->postService->getAll($request->query('type'));

        return PostResource::collection($posts);
    }

    #[OA\Get(
        path: '/api/posts/{id}',
        summary: 'Получить пост по ID',
        tags: ['Posts'],
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
                description: 'Данные поста',
                content: new OA\JsonContent(ref: '#/components/schemas/Post')
            ),
            new OA\Response(response: 404, description: 'Пост не найден'),
        ]
    )]
    public function show(Post $post)
    {
        $post = $this->postService->getById($post->id);

        return new PostResource($post);
    }

    #[OA\Post(
        path: '/api/posts',
        summary: 'Создать новый пост',
        tags: ['Posts'],
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['type', 'title'],
                properties: [
                    new OA\Property(property: 'type', type: 'string', enum: ['video', 'news'], example: 'video'),
                    new OA\Property(property: 'title', type: 'string', example: 'Название поста'),
                    new OA\Property(property: 'description', type: 'string', nullable: true, example: 'Описание поста'),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Пост создан',
                content: new OA\JsonContent(ref: '#/components/schemas/Post')
            ),
            new OA\Response(response: 401, description: 'Не авторизован'),
            new OA\Response(response: 422, description: 'Ошибка валидации'),
        ]
    )]
    public function store(StoreRequest $request)
    {
        $post = $this->postService->create($request->validated());

        return new PostResource($post);
    }

    #[OA\Put(
        path: '/api/posts/{id}',
        summary: 'Обновить пост',
        tags: ['Posts'],
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
                properties: [
                    new OA\Property(property: 'type', type: 'string', enum: ['video', 'news']),
                    new OA\Property(property: 'title', type: 'string'),
                    new OA\Property(property: 'description', type: 'string', nullable: true),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Пост обновлен',
                content: new OA\JsonContent(ref: '#/components/schemas/Post')
            ),
            new OA\Response(response: 401, description: 'Не авторизован'),
            new OA\Response(response: 404, description: 'Пост не найден'),
        ]
    )]
    public function update(UpdateRequest $request, Post $post)
    {
        $post = $this->postService->update($post, $request->validated());

        return new PostResource($post);
    }

    #[OA\Delete(
        path: '/api/posts/{id}',
        summary: 'Удалить пост',
        tags: ['Posts'],
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
            new OA\Response(response: 204, description: 'Пост удален'),
            new OA\Response(response: 401, description: 'Не авторизован'),
            new OA\Response(response: 404, description: 'Пост не найден'),
        ]
    )]
    public function destroy(Post $post)
    {
        $this->postService->delete($post);

        return response()->noContent();
    }
}
