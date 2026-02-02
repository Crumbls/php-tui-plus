<?php

declare(strict_types=1);

use Crumbls\Tui\Math\VectorUtil;

test('max with empty array', function (): void {
    expect(VectorUtil::max([]))->toBeNull();
});

test('max with single int', function (): void {
    expect(VectorUtil::max([1]))->toBe(1);
});

test('max with single float', function (): void {
    expect(VectorUtil::max([1.2]))->toBe(1.2);
});

test('max with two floats', function (): void {
    expect(VectorUtil::max([1.2, 3.4]))->toBe(3.4);
});

test('max with multiple ints', function (): void {
    expect(VectorUtil::max([6, 1, 3]))->toBe(6);
});

test('min with empty array', function (): void {
    expect(VectorUtil::min([]))->toBeNull();
});

test('min with single int', function (): void {
    expect(VectorUtil::min([1]))->toBe(1);
});

test('min with single float', function (): void {
    expect(VectorUtil::min([1.2]))->toBe(1.2);
});

test('min with two floats', function (): void {
    expect(VectorUtil::min([1.2, 3.4]))->toBe(1.2);
});

test('min with multiple ints', function (): void {
    expect(VectorUtil::min([6, 1, 3]))->toBe(1);
});
