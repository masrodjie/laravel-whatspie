<?php

use MasRodjie\LaravelWhatspie\Messaging\Result;

beforeEach(function () {
    $this->successData = [
        'code' => 200,
        'message' => 'Success',
        'data' => [
            'id' => 'msg_123',
            'status' => 'pending',
        ],
    ];
});

test('successful result returns true', function () {
    $result = new Result(200, $this->successData);

    expect($result->successful())->toBeTrue();
    expect($result->failed())->toBeFalse();
});

test('failed result returns false', function () {
    $result = new Result(400, [
        'code' => 400,
        'message' => 'Invalid request',
        'error' => 'Phone number format invalid',
    ]);

    expect($result->successful())->toBeFalse();
    expect($result->failed())->toBeTrue();
});

test('result returns message id when successful', function () {
    $result = new Result(200, $this->successData);

    expect($result->id())->toBe('msg_123');
});

test('result returns null id when failed', function () {
    $result = new Result(400, [
        'code' => 400,
        'message' => 'Invalid request',
    ]);

    expect($result->id())->toBeNull();
});

test('result returns error message when failed', function () {
    $result = new Result(400, [
        'code' => 400,
        'message' => 'Invalid request',
        'error' => 'Phone number format invalid',
    ]);

    expect($result->error())->toBe('Phone number format invalid');
    expect($result->errorCode())->toBe(400);
});

test('result returns full data array', function () {
    $result = new Result(200, $this->successData);

    expect($result->data())->toBe($this->successData);
});
