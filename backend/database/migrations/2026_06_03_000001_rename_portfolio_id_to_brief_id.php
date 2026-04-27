<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('brief_likes',      fn(Blueprint $t) => $t->renameColumn('portfolio_id', 'brief_id'));
        Schema::table('brief_comments',   fn(Blueprint $t) => $t->renameColumn('portfolio_id', 'brief_id'));
        Schema::table('brief_favorites',  fn(Blueprint $t) => $t->renameColumn('portfolio_id', 'brief_id'));
        Schema::table('brief_categories', fn(Blueprint $t) => $t->renameColumn('portfolio_id', 'brief_id'));
    }

    public function down(): void
    {
        Schema::table('brief_likes',      fn(Blueprint $t) => $t->renameColumn('brief_id', 'portfolio_id'));
        Schema::table('brief_comments',   fn(Blueprint $t) => $t->renameColumn('brief_id', 'portfolio_id'));
        Schema::table('brief_favorites',  fn(Blueprint $t) => $t->renameColumn('brief_id', 'portfolio_id'));
        Schema::table('brief_categories', fn(Blueprint $t) => $t->renameColumn('brief_id', 'portfolio_id'));
    }
};
