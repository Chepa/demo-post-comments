<?php

namespace App\Http\Controllers;

use OpenApi\Attributes as OA;

#[OA\Info(
    version: '1.0.0',
    title: 'Demo Post Comments API',
    description: 'API для работы с постами (видео и новости) и комментариями',
)]
#[OA\Server(
    url: '/',
    description: 'Основной сервер'
)]
#[OA\SecurityScheme(
    securityScheme: 'sanctum',
    type: 'http',
    scheme: 'bearer',
    bearerFormat: 'JWT',
    description: 'Используйте токен, полученный при регистрации или авторизации'
)]
#[OA\Schema(
    schema: 'Post',
    type: 'object',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'type', type: 'string', enum: ['video', 'news'], example: 'video'),
        new OA\Property(property: 'title', type: 'string', example: 'Название поста'),
        new OA\Property(property: 'description', type: 'string', nullable: true, example: 'Описание поста'),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
        new OA\Property(property: 'updated_at', type: 'string', format: 'date-time'),
        new OA\Property(
            property: 'comments',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/Comment'),
            nullable: true
        ),
    ]
)]
#[OA\Schema(
    schema: 'Comment',
    type: 'object',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'body', type: 'string', example: 'Текст комментария'),
        new OA\Property(property: 'user_id', type: 'integer', example: 1),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
        new OA\Property(property: 'updated_at', type: 'string', format: 'date-time'),
    ]
)]
abstract class Controller
{
    //
}
