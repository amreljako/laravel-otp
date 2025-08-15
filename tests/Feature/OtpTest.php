<?php
it('can send and verify otp', function () {
    // load the package migration
    $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');

    $resp = app('otp.manager')->send([
        'destination' => 'user@example.com',
        'purpose' => 'login',
        'channel' => 'mail',
        'ttl' => 60,
    ]);
    expect($resp)->toHaveKey('id');

    $row = \DB::table('otps')->find($resp['id']);
    \DB::table('otps')->where('id', $row->id)->update(['code_hash' => \Hash::make('123456')]);

    $ok = app('otp.manager')->verify('user@example.com','login','123456');
    expect($ok)->toBeTrue();
});
