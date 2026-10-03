<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('departments')) {
            Schema::create('departments', function (Blueprint $table) {
                $table->id();
                $table->uuid('department_id')->unique();
                $table->string('code', 32)->unique();
                $table->string('name');
                $table->string('status', 16)->default('active');
                $table->text('description')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('employees')) {
            Schema::create('employees', function (Blueprint $table) {
                $table->id();
                $table->uuid('employee_id')->unique();
                $table->string('employee_code', 32)->unique();
                $table->foreignId('user_id')->nullable()->unique()->constrained('users')->nullOnDelete();
                $table->foreignId('department_id')->nullable()->constrained('departments')->nullOnDelete();
                $table->string('full_name');
                $table->string('email')->nullable();
                $table->string('phone', 32)->nullable();
                $table->string('job_title')->nullable();
                $table->string('employment_type', 16)->default('full_time');
                $table->string('status', 16)->default('active');
                $table->date('hire_date')->nullable();
                $table->date('termination_date')->nullable();
                $table->decimal('salary', 12, 2)->nullable();
                $table->text('address')->nullable();
                $table->string('emergency_contact')->nullable();
                $table->timestamps();
                $table->index(['status', 'department_id']);
            });
        }

        if (! Schema::hasTable('payroll_runs')) {
            Schema::create('payroll_runs', function (Blueprint $table) {
                $table->id();
                $table->uuid('run_id')->unique();
                $table->string('run_number', 32)->unique();
                $table->unsignedSmallInteger('year');
                $table->unsignedTinyInteger('month');
                $table->string('period_label', 64);
                $table->string('status', 16)->default('draft');
                $table->decimal('total_gross', 14, 2)->default(0);
                $table->decimal('total_deductions', 14, 2)->default(0);
                $table->decimal('total_net', 14, 2)->default(0);
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('approved_at')->nullable();
                $table->timestamp('paid_at')->nullable();
                $table->timestamps();
                $table->unique(['year', 'month']);
            });
        }

        if (! Schema::hasTable('payslips')) {
            Schema::create('payslips', function (Blueprint $table) {
                $table->id();
                $table->uuid('payslip_id')->unique();
                $table->foreignId('payroll_run_id')->constrained('payroll_runs')->cascadeOnDelete();
                $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
                $table->decimal('base_salary', 12, 2)->default(0);
                $table->decimal('bonus', 12, 2)->default(0);
                $table->decimal('deductions', 12, 2)->default(0);
                $table->decimal('net_pay', 12, 2)->default(0);
                $table->unsignedSmallInteger('days_worked')->default(0);
                $table->text('notes')->nullable();
                $table->timestamps();
                $table->unique(['payroll_run_id', 'employee_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('payslips');
        Schema::dropIfExists('payroll_runs');
        Schema::dropIfExists('employees');
        Schema::dropIfExists('departments');
    }
};
