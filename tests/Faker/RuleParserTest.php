<?php

declare(strict_types=1);

use Pin\Faker\FakeRule;
use Pin\Faker\RuleBag;
use Pin\Faker\RuleParser;
use Pin\Validation\Rules\Enum;

it('parses string rules', function () {
    $parser = new RuleParser();
    $rule = new RuleBag('fake:string,16');
    $rule = $parser->parse($rule);

    expect($rule->generator())->toBe('string')
        ->and($rule->parameters())->toBe(['16']);
});

it('parses FakeRule instances', function () {
    $parser = new RuleParser();
    $rule = new RuleBag([new FakeRule('s')]);

    expect($parser->parse($rule)->generator())->toBe('s');
});

it('parses infer rules', function () {
    $parser = new RuleParser();
    $rule = new RuleBag('string');
    $rule = $parser->parse($rule);

    expect($rule->generator())->toBe('string')
        ->and($rule->parameters())->toBe([16]);
});

it('parses enum rule', function () {
    $parser = new RuleParser();
    $rule = new RuleBag([new Enum('s')]);

    expect($parser->parse($rule)->parameter(0))->toBe('s');
});
