<?php

use App\Models\TVCP\VisitTransferApplication;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tvcp_visit_transfer_applications', function (Blueprint $table) {
            $table->uuid('id')->primary()->default();
            $table->unsignedInteger('account_id');
            $table->enum('type', [VisitTransferApplication::TYPE_TRANSFER, VisitTransferApplication::TYPE_VISIT]);
            $table->enum('status', VisitTransferApplication::STATUSES)->default(VisitTransferApplication::STATUS_SUBMITTED);
            $table->json('questions_data')->nullable();
            $table->json('state_history')->nullable();
            $table->json('verification_steps')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tvcp_visit_transfer_applications');
    }
};
