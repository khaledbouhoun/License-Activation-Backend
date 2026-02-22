<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('licenses', function (Blueprint $table) {
            // 1. حذف القيد الفريد القديم (اسم الحقل_unique)
            $table->dropUnique(['device_id']);

            // 2. إضافة القيد المركب الجديد
            $table->unique(['subscription_id', 'device_id']);
        });
    }

    public function down(): void
    {
        Schema::table('licenses', function (Blueprint $table) {
            $table->dropUnique(['subscription_id', 'device_id']);
            $table->unique('device_id');
        });
    }
};
