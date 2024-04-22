<?php

/**
 * @file CustomMetadataSchemaMigration.inc.php
 *
 * Copyright (c) 2014-2020 Simon Fraser University
 * Copyright (c) 2000-2020 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class CustomMetadataSchemaMigration
 * @brief Describe database table structures.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Builder;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Capsule\Manager as Capsule;

class CustomMetadataSchemaMigration extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Capsule::schema()->create('custom_metadata', function (Blueprint $table) {
            $table->bigInteger('custom_metadata_id')->autoIncrement();
            $table->bigInteger('context_id');
            $table->bigInteger('section_id');
            $table->string('type', 255);
            $table->boolean('localized');
            $table->string('name', 255);
            $table->string('label', 255);
            $table->longText('description', 255);
        });
    }

}