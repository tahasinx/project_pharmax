<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('users')) {
            Schema::create('users', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('email')->unique();
                $table->timestamp('email_verified_at')->nullable();
                $table->string('password');
                $table->boolean('is_platform_admin')->default(false);
                $table->rememberToken();
                $table->timestamps();
            });
        } elseif (! Schema::hasColumn('users', 'is_platform_admin')) {
            Schema::table('users', function (Blueprint $table) {
                $table->boolean('is_platform_admin')->default(false)->after('password');
            });
        }

        Schema::table('companies', function (Blueprint $table) {
            if (! Schema::hasColumn('companies', 'email')) {
                $table->string('email')->nullable()->after('name');
            }
            if (! Schema::hasColumn('companies', 'admin_email')) {
                $table->string('admin_email')->nullable()->after('email');
            }
            if (! Schema::hasColumn('companies', 'phone')) {
                $table->string('phone', 64)->nullable();
            }
            if (! Schema::hasColumn('companies', 'address')) {
                $table->text('address')->nullable();
            }
            if (! Schema::hasColumn('companies', 'provision_status')) {
                $table->string('provision_status', 20)->default('active');
            }
            if (! Schema::hasColumn('companies', 'provision_error')) {
                $table->text('provision_error')->nullable();
            }
            if (! Schema::hasColumn('companies', 'provision_log')) {
                $table->json('provision_log')->nullable();
            }
            if (! Schema::hasColumn('companies', 'provisioned_at')) {
                $table->timestamp('provisioned_at')->nullable();
            }
        });

        Schema::create('platform_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });

        Schema::create('platform_plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->decimal('monthly_amount', 12, 2)->default(0);
            $table->string('currency', 8)->default('BDT');
            $table->string('status', 20)->default('active');
            $table->timestamps();
        });

        Schema::create('platform_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('platform_plan_id')->constrained('platform_plans')->restrictOnDelete();
            $table->string('status', 20)->default('active');
            $table->date('starts_on');
            $table->date('ends_on')->nullable();
            $table->decimal('amount', 12, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('platform_invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('platform_subscription_id')->nullable()->constrained('platform_subscriptions')->nullOnDelete();
            $table->string('number')->unique();
            $table->decimal('amount', 12, 2);
            $table->string('currency', 8)->default('BDT');
            $table->string('status', 20)->default('unpaid');
            $table->date('issued_on');
            $table->timestamp('paid_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('platform_invoices');
        Schema::dropIfExists('platform_subscriptions');
        Schema::dropIfExists('platform_plans');
        Schema::dropIfExists('platform_settings');
    }
};
