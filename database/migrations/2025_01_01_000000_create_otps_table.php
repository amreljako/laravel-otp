<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('otps', function (Blueprint $t) {
            $t->id();
            $t->nullableMorphs('otpable');
            $t->string('channel', 32);
            $t->string('destination');
            $t->string('purpose', 64);
            $t->string('code_hash');
            $t->unsignedTinyInteger('digits')->default(6);
            $t->unsignedSmallInteger('ttl_seconds')->default(300);
            $t->timestamp('expires_at');
            $t->timestamp('consumed_at')->nullable();
            $t->unsignedTinyInteger('attempts')->default(0);
            $t->unsignedTinyInteger('max_attempts')->default(5);
            $t->string('signature')->nullable();
            $t->json('meta')->nullable();
            $t->timestamps();

            $t->index(['destination', 'purpose', 'channel']);
            $t->index('expires_at');
        });
    }
    public function down(): void { Schema::dropIfExists('otps'); }
};
