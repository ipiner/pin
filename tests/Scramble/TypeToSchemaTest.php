<?php

declare(strict_types=1);

use Dedoc\Scramble\Generator;
use Dedoc\Scramble\Infer;
use Dedoc\Scramble\Infer\Definition\FunctionLikeDefinition;
use Dedoc\Scramble\OpenApiContext;
use Dedoc\Scramble\Scramble;
use Dedoc\Scramble\Support\Generator\OpenApi;
use Dedoc\Scramble\Support\Generator\TypeTransformer;
use Dedoc\Scramble\Support\PhpDoc;
use Dedoc\Scramble\Support\Type\ArrayItemType_;
use Dedoc\Scramble\Support\Type\ArrayType;
use Dedoc\Scramble\Support\Type\FunctionType;
use Dedoc\Scramble\Support\Type\Generic;
use Dedoc\Scramble\Support\Type\IntegerType;
use Dedoc\Scramble\Support\Type\KeyedArrayType;
use Dedoc\Scramble\Support\Type\NullType;
use Dedoc\Scramble\Support\Type\ObjectType;
use Dedoc\Scramble\Support\Type\StringType;
use Dedoc\Scramble\Support\Type\Type;
use Dedoc\Scramble\Support\Type\Union;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Route as RouteFacade;
use Pin\Http\ApiResponse;
use Pin\Pagination\Pagination;
use Pin\Scramble\Created;
use Pin\Scramble\Deleted;
use Pin\Scramble\ScrambleServiceProvider;
use Pin\Scramble\SelectOption;
use Pin\Scramble\TypeToSchemaExtensions\ApiResponseToSchema;
use Pin\Scramble\TypeToSchemaExtensions\PaginationToSchema;
use Pin\Scramble\Updated;

beforeEach(function () {
    $this->app->register(Dedoc\Scramble\ScrambleServiceProvider::class);
    config(['scramble.extensions' => [ApiResponseToSchema::class, PaginationToSchema::class]]);
    $context = new OpenApiContext(new OpenApi('3.1.0'), Scramble::configure());
    $this->transformer = app(TypeTransformer::class, ['context' => $context]);
    $this->extension = new ApiResponseToSchema(
        app(Infer::class),
        $this->transformer,
        $context->openApi->components
    );
});

it('generates JSON responses with concrete data types and field descriptions', function () {
    $response = $this->transformer->toResponse(new Generic(ApiResponse::class, [new StringType()]));
    $schema = $response->toArray()['content']['application/json']['schema'];

    expect($response->code)->toBe(200)
        ->and($schema['properties']['code']['type'])->toBe('integer')
        ->and($schema['properties']['message']['type'])->toBe('string')
        ->and($schema['properties']['data']['type'])->toBe('string')
        ->and($schema['properties']['data']['description'])->toBe('响应数据');
});

it('analyzes documentation resources inside nested types', function (Closure $makeType) {
    Scramble::infer()->config->buildDefinitionsUsingReflectionFor([Created::class]);

    $this->transformer->transform(new Generic(ApiResponse::class, [$makeType()]));
    $schemas = $this->transformer->getComponents()->toArray()['schemas'];

    expect($schemas['Created']['properties']['id']['type'])->toBe('integer');
})->with([
    'resource' => fn () => new ObjectType(Created::class),
    'array' => fn () => new ArrayType(new ObjectType(Created::class)),
    'array shape' => fn () => new KeyedArrayType([
        new ArrayItemType_('result', new ObjectType(Created::class)),
    ]),
    'nullable resource' => fn () => new Union([new ObjectType(Created::class), new NullType()]),
    'pagination' => fn () => new Generic(Pagination::class, [new ObjectType(Created::class)]),
]);

it('generates resource and pagination field types', function () {
    $type = new Generic(ApiResponse::class, [
        new Generic(Pagination::class, [new ObjectType(SelectOption::class)]),
    ]);
    $schema = $this->transformer->transform($type)->toArray();
    $pagination = $schema['properties']['data'];
    $schemas = $this->transformer->getComponents()->toArray()['schemas'];

    expect($pagination['properties']['total']['type'])->toBe('integer')
        ->and($pagination['properties']['total_page']['type'])->toBe('integer')
        ->and($pagination['properties']['items']['type'])->toBe('array')
        ->and($schemas['SelectOption']['properties']['label']['type'])->toBe('string')
        ->and($schemas['SelectOption']['properties']['value']['type'])
        ->toBe(['integer', 'string']);

    foreach ([Deleted::class, Updated::class] as $resource) {
        $this->transformer->transform(new Generic(ApiResponse::class, [new ObjectType($resource)]));
    }

    $schemas = $this->transformer->getComponents()->toArray()['schemas'];
    expect($schemas['Deleted']['properties']['deleted']['type'])->toBe('boolean')
        ->and($schemas['Updated']['properties']['updated']['type'])->toBe('boolean')
        ->and($schemas['Updated']['properties']['v']['type'])->toBe(['integer', 'null']);
});

it('preserves field metadata without modifying the inferred return type', function () {
    $item = new ArrayItemType_('data', new IntegerType(), isOptional: true);
    $item->setAttribute('format', 'uuid');
    $doc = PhpDoc::parse("/**\n * @var TData 字段说明\n * @example demo\n */");
    $item->setAttribute('docNode', $doc);
    $returnType = new KeyedArrayType([$item]);
    $type = Mockery::mock(Generic::class, [ApiResponse::class, [new StringType()]])->makePartial();
    $type->shouldReceive('getMethodDefinition')->with('toArray')->andReturn(
        new FunctionLikeDefinition(new FunctionType('toArray', returnType: $returnType))
    );

    $resolved = $this->invoker($this->extension)->resolveReturnType($type);
    $schema = $this->transformer->transform($resolved)->toArray();

    expect($resolved->items[0]->isOptional)->toBeTrue()
        ->and($resolved->items[0]->getAttribute('format'))->toBe('uuid')
        ->and($resolved->items[0]->value)->toBeInstanceOf(StringType::class)
        ->and($returnType->items[0])->toBe($item)
        ->and($item->value)->toBeInstanceOf(IntegerType::class)
        ->and($doc->getVarTagValues())->toHaveCount(1)
        ->and($schema['properties']['data']['type'])->toBe('string')
        ->and($schema['properties']['data']['description'])->toBe('字段说明')
        ->and($schema['properties']['data']['examples'])->toBe(['demo']);
});

it('keeps return types without named fields intact', function () {
    $returnType = new ArrayType(new StringType());
    $type = Mockery::mock(Generic::class, [ApiResponse::class, [new StringType()]])->makePartial();
    $type->shouldReceive('getMethodDefinition')->with('toArray')->andReturn(
        new FunctionLikeDefinition(new FunctionType('toArray', returnType: $returnType))
    );

    $resolved = $this->invoker($this->extension)->resolveReturnType($type);

    expect($resolved)->toBeInstanceOf(ArrayType::class)
        ->and($resolved->value)->toBeInstanceOf(StringType::class);
});

it('only handles matching generic types', function (Type $type, bool $handled) {
    expect($this->extension->shouldHandle($type))->toBe($handled);
})->with([
    [new Generic(ApiResponse::class, [new StringType()]), true],
    [new Generic(ApiResponse::class, [new NullType()]), true],
    [new Generic(ApiResponse::class), false],
    [new ObjectType(ApiResponse::class), false],
    [new Generic(Pagination::class, [new StringType()]), false],
]);

it('generates a complete document from controller annotations', function () {
    new ScrambleServiceProvider($this->app)->boot();
    RouteFacade::get('api/scramble-testing', [ScrambleDocumentationController::class, 'index']);
    Scramble::configure()->routes(fn (Route $route) => $route->uri() === 'api/scramble-testing');

    $document = app(Generator::class)();
    $response = $document['paths']['/scramble-testing']['get']['responses'][200];
    $schema = $response['content']['application/json']['schema'];

    expect($schema['properties']['data']['properties']['items']['type'])->toBe('array')
        ->and($schema['properties']['data']['description'])->toBe('响应数据')
        ->and($document['components']['schemas']['SelectOption']['properties']['value']['type'])
        ->toBe(['integer', 'string'])
        ->and($document['components']['securitySchemes']['bearer'])->toBe([
            'type' => 'http',
            'scheme' => 'bearer',
        ])
        ->and($document['security'])->toContain(['bearer' => []]);
});

class ScrambleDocumentationController
{
    /**
     * @return ApiResponse<Pagination<SelectOption>>
     */
    public function index(): ApiResponse
    {
        return ApiResponse::make();
    }
}
