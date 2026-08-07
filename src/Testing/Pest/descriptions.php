<?php

declare(strict_types=1);

use Illuminate\Support\Str;

if (! function_exists('creates')) {
    function creates(string $name): string
    {
        return "creates {$name} successfully";
    }
}

if (! function_exists('failsToCreate')) {
    function failsToCreate(string $name): string
    {
        return "fails to create {$name}";
    }
}

if (! function_exists('updates')) {
    function updates(string $name): string
    {
        return "updates {$name} successfully";
    }
}

if (! function_exists('failsToUpdate')) {
    function failsToUpdate(string $name): string
    {
        return "fails to update {$name}";
    }
}

if (! function_exists('deletes')) {
    function deletes(string $name): string
    {
        return "deletes {$name} successfully";
    }
}

if (! function_exists('failsToDelete')) {
    function failsToDelete(string $name): string
    {
        return "fails to delete {$name}";
    }
}

if (! function_exists('lists')) {
    function lists(string $name): string
    {
        $name = Str::plural($name);

        return "lists {$name} successfully";
    }
}

if (! function_exists('validatesCreatePayload')) {
    function validatesCreatePayload(string $name): string
    {
        return "validates payload for updating {$name}";
    }
}

if (! function_exists('validatesCreateRequired')) {
    function validatesCreateRequired(string $name): string
    {
        return "validates required fields for creating {$name}";
    }
}

if (! function_exists('validatesUpdatePayload')) {
    function validatesUpdatePayload(string $name): string
    {
        return "validates payload for updating {$name}";
    }
}

if (! function_exists('validatesUpdateRequired')) {
    function validatesUpdateRequired(string $name): string
    {
        return "validates required fields for updating {$name}";
    }
}

if (! function_exists('ensuresUnique')) {
    function ensuresUnique(string $name, string $field): string
    {
        return "ensures {$name}'s {$field} is unique";
    }
}

if (! function_exists('runsTestsAutomatically')) {
    function runsTestsAutomatically(string $name): string
    {
        return "runs {$name}'s tests automatically";
    }
}
