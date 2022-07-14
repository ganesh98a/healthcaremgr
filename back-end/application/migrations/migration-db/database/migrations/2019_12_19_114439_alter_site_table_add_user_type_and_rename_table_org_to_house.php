<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterSiteTableAddUserTypeAndRenameTableOrgToHouse extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {

        Schema::dropIfExists('tbl_house_docs');
        Schema::dropIfExists('tbl_house_all_contact');
        Schema::dropIfExists('tbl_house_email');
        Schema::dropIfExists('tbl_house_phone');
        Schema::dropIfExists('tbl_house_requirements');
        Schema::dropIfExists('tbl_house_resident');

        if (Schema::hasTable('tbl_organisation_site_email') && !Schema::hasTable('tbl_house_and_site_email')) {
            Schema::rename('tbl_organisation_site_email', 'tbl_house_and_site_email');
        }

        if (Schema::hasTable('tbl_organisation_site_phone') && !Schema::hasTable('tbl_house_and_site_phone')) {
            Schema::rename('tbl_organisation_site_phone', 'tbl_house_and_site_phone');
        }

        if (Schema::hasTable('tbl_organisation_site_key_contact') && !Schema::hasTable('tbl_house_and_site_key_contact')) {
            Schema::rename('tbl_organisation_site_key_contact', 'tbl_house_and_site_key_contact');
        }

        if (Schema::hasTable('tbl_organisation_site_key_contact_email') && !Schema::hasTable('tbl_house_and_site_key_contact_email')) {
            Schema::rename('tbl_organisation_site_key_contact_email', 'tbl_house_and_site_key_contact_email');
        }

        if (Schema::hasTable('tbl_organisation_site_key_contact_phone') && !Schema::hasTable('tbl_house_and_site_key_contact_phone')) {
            Schema::rename('tbl_organisation_site_key_contact_phone', 'tbl_house_and_site_key_contact_phone');
        }

        Schema::table('tbl_house_and_site_email', function (Blueprint $table) {
            if (!Schema::hasColumn('tbl_house_and_site_email', 'user_type')) {
                $table->unsignedTinyInteger('user_type')->comment("1- Site/ 2- House")->default(1)->after('id');
            }
        });

        Schema::table('tbl_house_and_site_phone', function (Blueprint $table) {
            if (!Schema::hasColumn('tbl_house_and_site_phone', 'user_type')) {
                $table->unsignedTinyInteger('user_type')->comment("1- Site/ 2- House")->default(1)->after('id');
            }
        });

        Schema::table('tbl_house_and_site_key_contact', function (Blueprint $table) {
            if (!Schema::hasColumn('tbl_house_and_site_key_contact', 'user_type')) {
                $table->unsignedTinyInteger('user_type')->comment("1- Site/ 2- House")->default(1)->after('id');
            }
        });

        Schema::table('tbl_house_and_site_key_contact_email', function (Blueprint $table) {
            if (!Schema::hasColumn('tbl_house_and_site_key_contact_email', 'user_type')) {
                $table->unsignedTinyInteger('user_type')->comment("1- Site/ 2- House")->default(1)->after('id');
            }
        });

        Schema::table('tbl_house_and_site_key_contact_phone', function (Blueprint $table) {
            if (!Schema::hasColumn('tbl_house_and_site_key_contact_phone', 'user_type')) {
                $table->unsignedTinyInteger('user_type')->comment("1- Site/ 2- House")->default(1)->after('id');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {

        Schema::table('tbl_house_and_site_email', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_house_and_site_email', 'user_type')) {
                $table->dropColumn('user_type');
            }
        });

        Schema::table('tbl_house_and_site_phone', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_house_and_site_phone', 'user_type')) {
                $table->dropColumn('user_type');
            }
        });

        Schema::table('tbl_house_and_site_key_contact', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_house_and_site_key_contact', 'user_type')) {
                $table->dropColumn('user_type');
            }
        });

        Schema::table('tbl_house_and_site_key_contact_email', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_house_and_site_key_contact_email', 'user_type')) {
                $table->dropColumn('user_type');
            }
        });

        Schema::table('tbl_house_and_site_key_contact_phone', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_house_and_site_key_contact_phone', 'user_type')) {
                $table->dropColumn('user_type');
            }
        });

        if (!Schema::hasTable('tbl_organisation_site_email') && Schema::hasTable('tbl_house_and_site_email')) {
            Schema::rename('tbl_house_and_site_email', 'tbl_organisation_site_email');
        }

        if (!Schema::hasTable('tbl_organisation_site_phone') && Schema::hasTable('tbl_house_and_site_phone')) {
            Schema::rename('tbl_house_and_site_phone', 'tbl_organisation_site_phone');
        }

        if (!Schema::hasTable('tbl_organisation_site_key_contact') && Schema::hasTable('tbl_house_and_site_key_contact')) {
            Schema::rename('tbl_house_and_site_key_contact', 'tbl_organisation_site_key_contact');
        }

        if (!Schema::hasTable('tbl_organisation_site_key_contact_email') && Schema::hasTable('tbl_house_and_site_key_contact_email')) {
            Schema::rename('tbl_house_and_site_key_contact_email', 'tbl_organisation_site_key_contact_email');
        }

        if (!Schema::hasTable('tbl_organisation_site_key_contact_phone') && Schema::hasTable('tbl_house_and_site_key_contact_phone')) {
            Schema::rename('tbl_house_and_site_key_contact_phone', 'tbl_organisation_site_key_contact_phone');
        }
    }

}
