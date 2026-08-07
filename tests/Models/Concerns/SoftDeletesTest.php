<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Pin\Models\Concerns\SoftDeletes;
use Pin\Models\Model;

beforeEach(function () {
    createSchema();
});

it('soft deletes model', function () {
    $model = SoftDelete::find(1);

    expect($model->deleted_at)->toBe(0);

    $model->delete();
    expect($model->deleted_at)->not->toBe(0)
        ->and(SoftDelete::find(1))->toBeNull();

    $model = SoftDelete::withTrashed()->find(1);
    expect($model->deleted_at)->not->toBe(0);

    $model->restore();
    $model = SoftDelete::find(1);
    expect($model->deleted_at)->toBe(0);

    $model->forceDelete();
    $model = SoftDelete::withTrashed()->find(1);
    expect($model)->toBeNull();
});

it('soft deletes model with null deleted_at', function () {
    $model = SoftDeleteNull::find(1);
    expect($model->deleted_at)->toBeNull();

    $model->delete();
    expect($model->deleted_at)->not->toBeNull()
        ->and(SoftDeleteNull::find(1))->toBeNull();

    $model = SoftDeleteNull::withTrashed()->find(1);
    expect($model->deleted_at)->not->toBeNull();

    $model->restore();
    $model = SoftDeleteNull::find(1);
    expect($model->deleted_at)->toBeNull();
});

it('soft deletes model and updates values', function () {
    $model = SoftDeleteValue::find(1);

    expect($model->deleted_at)->toBe(0)
        ->and($model->name)->toBe('foo');

    $model->delete();
    expect($model->deleted_at)
        ->not->toBe(0)
        ->and($model->name)->toBe('foo_deleted')
        ->and(SoftDeleteValue::find(1))->toBeNull();

    $model->update(); // delete cache
    expect(SoftDeleteValue::onlyTrashed()->where('name', 'foo_deleted')->restore())
        ->toBe(1);

    $model = SoftDeleteValue::find(1);
    expect($model->deleted_at)->toBe(0)
        ->and($model->name)->toBe('foo');
});

function createSchema(): void
{
    schema()->create('soft_deletes', function (Blueprint $table) {
        $table->increments('id');
        $table->string('name');
        $table->timestamps();
        $table->unsignedInteger('deleted_at')->default(0);
        $table->unique(['name', 'deleted_at']);
    });

    schema()->create('soft_delete_nulls', function (Blueprint $table) {
        $table->increments('id');
        $table->string('name');
        $table->timestamps();
        $table->softDeletes();
    });

    schema()->create('soft_delete_values', function (Blueprint $table) {
        $table->increments('id');
        $table->string('name');
        $table->timestamps();
        $table->unsignedInteger('deleted_at')->default(0);
        $table->unique(['name', 'deleted_at']);
    });

    SoftDelete::create(['id' => 1, 'name' => 'foo']);
    SoftDeleteNull::create(['id' => 1, 'name' => 'foo']);
    SoftDeleteValue::create(['id' => 1, 'name' => 'foo']);
}

function schema()
{
    return DB::connection()->getSchemaBuilder();
}

class SoftDelete extends Model
{
    use SoftDeletes;
}

class SoftDeleteNull extends Model
{
    use SoftDeletes;

    public function softDeletedAtValue(bool $deleted): ?string
    {
        return $deleted ? $this->freshTimestampString() : null;
    }
}

class SoftDeleteValue extends Model
{
    use SoftDeletes;

    public function softDeletedValuesForUpdate(bool $deleted): array
    {
        return [
            'deleted_at' => $this->softDeletedAtValue($deleted),
            'name' => $deleted ? $this->name.'_deleted' : 'foo',
        ];
    }
}
