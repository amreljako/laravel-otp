<?php
namespace Amreljako\Otp\Facades;
use Illuminate\Support\Facades\Facade;

class Otp extends Facade {
    protected static function getFacadeAccessor() { return 'otp.manager'; }
}
