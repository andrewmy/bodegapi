<?php

declare(strict_types=1);

namespace App\Api;

use App\DataFixtures\Users;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class DocumentationDecorator implements NormalizerInterface
{
    /** @var NormalizerInterface */
    private $decorated;

    public function __construct(NormalizerInterface $decorated)
    {
        $this->decorated = $decorated;
    }

    public function normalize($object, $format = null, array $context = [])
    {
        /** @var array $docs */
        $docs = $this->decorated->normalize($object, $format, $context);

        $docs['info']['version'] = '0.0.1';
        $docs['info']['title'] = 'Bodegapi';

        /** @var \ArrayObject $paths */
        $paths = $docs['paths'];
        // prepending
        $docs['paths'] = new \ArrayObject(\array_merge([
            '/api/login' => [
                'post' => [
                    'tags' => ['Login'],
                    'operationId' => 'postLogin',
                    'consumes' => ['application/json'],
                    'produces' => ['application/json'],
                    'summary' => 'Authenticates a user',
                    'parameters' => [[
                        'in' => 'body',
                        'name' => 'credentials',
                        'description' => 'User credentials',
                        'schema' => [
                            'type' => 'object',
                            'properties' => [
                                'username' => [
                                    'type' => 'string',
                                    'example' => Users::API_LOGIN,
                                ],
                                'password' => [
                                    'type' => 'string',
                                    'example' => Users::API_PASSWORD,
                                ],
                            ],
                            'required' => ['username', 'password'],
                        ],
                    ]],
                    'responses' => [
                        Response::HTTP_OK => [
                            'description' => 'OK',
                            'schema' => [
                                'type' => 'object',
                                'properties' => [
                                    'token' => ['type' => 'string'],
                                ],
                            ],
                        ],
                        Response::HTTP_BAD_REQUEST => [
                            'description' => 'Bad request',
                        ],
                        Response::HTTP_UNAUTHORIZED => [
                            'description' => 'Bad credentials',
                        ],
                    ],
                ],
            ],
        ], $paths->getArrayCopy()));

        return $docs;
    }

    public function supportsNormalization($data, $format = null)
    {
        return $this->decorated->supportsNormalization($data, $format);
    }
}
