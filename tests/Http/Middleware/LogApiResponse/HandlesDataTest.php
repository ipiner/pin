<?php

declare(strict_types=1);

use Illuminate\Http\Request;
use Pin\Http\Middleware\LogApiResponse\HandlesData;

it('handles payload and response data inclusion', function () {
    $object = new class
    {
        use HandlesData;

        public Request $request;

        protected function isSuccess()
        {
            return true;
        }

        public function includePayload()
        {
            return $this->shouldIncludeRequestPayload();
        }

        public function includeData()
        {
            return $this->shouldIncludeData();
        }
    };

    foreach ([false, true] as $i => $case) {
        config(['pin.logging.response.include_request_payload' => $case]);
        expect($object->includePayload())->toBe($case, (string) $i);
    }

    $object->request = Request::create('/test', 'POST', [
        'username' => 'admin',
        'password' => '123456',
    ]);

    foreach ([
        [true, []],
        [true, ['foo']],
        [false, ['test']],
    ] as $i => [$expected, $ignore]) {
        config(['pin.logging.response.ignore_response_data' => $ignore]);
        expect($object->includeData())->toBe($expected, (string) $i);
    }
});
