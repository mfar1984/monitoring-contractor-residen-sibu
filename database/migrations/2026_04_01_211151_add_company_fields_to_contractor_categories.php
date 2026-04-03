<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('contractor_categories', function (Blueprint $table) {
            // Add new columns after existing fields
            $table->string('office_registration_no')->nullable()->after('registration_number');
            $table->string('email')->nullable()->after('description');
            $table->string('telephone_no')->nullable()->after('email');
            $table->string('mobile_no')->nullable()->after('telephone_no');
            $table->string('fax_no')->nullable()->after('mobile_no');
            $table->string('contact_person')->nullable()->after('fax_no');
            $table->string('contact_no')->nullable()->after('contact_person');
            $table->date('date_established')->nullable()->after('contact_no');
            $table->string('company_type')->nullable()->default('contractor')->after('date_established');
            $table->string('company_category')->nullable()->after('company_type');
            $table->string('registration_category')->nullable()->after('company_category');
            $table->string('division')->nullable()->after('registration_category');
            $table->string('district')->nullable()->after('division');
            $table->boolean('registration_status_valid')->default(0)->after('district');
            $table->boolean('registration_status_expired')->default(0)->after('registration_status_valid');
            $table->string('bumiputera_status')->nullable()->after('registration_status_expired');
            $table->boolean('rescue_contractor')->default(0)->after('bumiputera_status');
            $table->string('authorized_person_name')->nullable()->after('rescue_contractor');
            $table->string('authorized_person_ic')->nullable()->after('authorized_person_name');
            $table->string('upk_license_no')->nullable()->after('authorized_person_ic');
            $table->date('upk_expiry_date')->nullable()->after('upk_license_no');
            $table->string('upkj_class')->nullable()->after('upk_expiry_date');
            $table->string('upkj_head')->nullable()->after('upkj_class');
            $table->text('upkj_subhead')->nullable()->after('upkj_head');
            $table->json('shareholders_data')->nullable()->after('upkj_subhead');
            $table->integer('manpower_sole_proprietor')->default(0)->after('shareholders_data');
            $table->integer('manpower_management')->default(0)->after('manpower_sole_proprietor');
            $table->integer('manpower_professional')->default(0)->after('manpower_management');
            $table->integer('manpower_sub_professional')->default(0)->after('manpower_professional');
            $table->integer('manpower_competent_worker')->default(0)->after('manpower_sub_professional');
            $table->integer('manpower_total')->default(0)->after('manpower_competent_worker');
            $table->text('registered_address')->nullable()->after('manpower_total');
            $table->text('postal_address')->nullable()->after('registered_address');
            $table->text('business_address')->nullable()->after('postal_address');
            $table->string('registered_location')->nullable()->after('business_address');
            $table->string('registered_address_city')->nullable()->after('registered_location');
            $table->string('registered_address_state')->nullable()->after('registered_address_city');
            $table->string('registered_address_postcode')->nullable()->after('registered_address_state');
        });
        
        // Modify status enum to add new values
        DB::statement("ALTER TABLE contractor_categories MODIFY COLUMN status ENUM('Active', 'Inactive', 'Pending', 'Rejected', 'Suspended') DEFAULT 'Active'");
        
        // Set default company_type for existing records
        DB::statement("UPDATE contractor_categories SET company_type = 'contractor' WHERE company_type IS NULL");
        
        // Add indexes for performance
        Schema::table('contractor_categories', function (Blueprint $table) {
            $table->index('company_type');
            $table->index('upkj_class');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contractor_categories', function (Blueprint $table) {
            // Drop indexes
            $table->dropIndex(['company_type']);
            $table->dropIndex(['upkj_class']);
            
            // Remove all new columns
            $table->dropColumn([
                'office_registration_no',
                'email',
                'telephone_no',
                'mobile_no',
                'fax_no',
                'contact_person',
                'contact_no',
                'date_established',
                'company_type',
                'company_category',
                'registration_category',
                'division',
                'district',
                'registration_status_valid',
                'registration_status_expired',
                'bumiputera_status',
                'rescue_contractor',
                'authorized_person_name',
                'authorized_person_ic',
                'upk_license_no',
                'upk_expiry_date',
                'upkj_class',
                'upkj_head',
                'upkj_subhead',
                'shareholders_data',
                'manpower_sole_proprietor',
                'manpower_management',
                'manpower_professional',
                'manpower_sub_professional',
                'manpower_competent_worker',
                'manpower_total',
                'registered_address',
                'postal_address',
                'business_address',
                'registered_location',
                'registered_address_city',
                'registered_address_state',
                'registered_address_postcode',
            ]);
        });
        
        // Revert status enum
        DB::statement("ALTER TABLE contractor_categories MODIFY COLUMN status ENUM('Active', 'Inactive') DEFAULT 'Active'");
    }
};
