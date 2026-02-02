<?php

declare(strict_types=1);

use Crumbls\Tui\Extension\Core\Widget\Block\Padding;

test('all', function (): void {
    $padding = Padding::all(2);

    expect($padding->left)->toBe(2);
    expect($padding->right)->toBe(2);
    expect($padding->top)->toBe(2);
    expect($padding->bottom)->toBe(2);
});

test('horizontal', function (): void {
    $padding = Padding::horizontal(2);

    expect($padding->left)->toBe(2);
    expect($padding->right)->toBe(2);
    expect($padding->top)->toBe(0);
    expect($padding->bottom)->toBe(0);
});

test('vertical', function (): void {
    $padding = Padding::vertical(2);

    expect($padding->left)->toBe(0);
    expect($padding->right)->toBe(0);
    expect($padding->top)->toBe(2);
    expect($padding->bottom)->toBe(2);
});

test('none', function (): void {
    $padding = Padding::none();

    expect($padding->left)->toBe(0);
    expect($padding->right)->toBe(0);
    expect($padding->top)->toBe(0);
    expect($padding->bottom)->toBe(0);
});

test('from scalars', function (): void {
    $padding = Padding::fromScalars(1, 2, 3, 4);

    expect($padding->top)->toBe(3);
    expect($padding->bottom)->toBe(4);
    expect($padding->left)->toBe(1);
    expect($padding->right)->toBe(2);
});

test('left', function (): void {
    $padding = Padding::left(2);

    expect($padding->left)->toBe(2);
    expect($padding->right)->toBe(0);
    expect($padding->top)->toBe(0);
    expect($padding->bottom)->toBe(0);
});

test('right', function (): void {
    $padding = Padding::right(2);

    expect($padding->left)->toBe(0);
    expect($padding->right)->toBe(2);
    expect($padding->top)->toBe(0);
    expect($padding->bottom)->toBe(0);
});

test('top', function (): void {
    $padding = Padding::top(2);

    expect($padding->left)->toBe(0);
    expect($padding->right)->toBe(0);
    expect($padding->top)->toBe(2);
    expect($padding->bottom)->toBe(0);
});

test('bottom', function (): void {
    $padding = Padding::bottom(2);

    expect($padding->left)->toBe(0);
    expect($padding->right)->toBe(0);
    expect($padding->top)->toBe(0);
    expect($padding->bottom)->toBe(2);
});

test('mixed', function (): void {
    $padding = Padding::fromScalars(left: 2, top: 4);

    expect($padding->left)->toBe(2);
    expect($padding->right)->toBe(0);
    expect($padding->top)->toBe(4);
    expect($padding->bottom)->toBe(0);
});
