<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::rename('portfolios',           'briefs');
        Schema::rename('portfolio_likes',      'brief_likes');
        Schema::rename('portfolio_comments',   'brief_comments');
        Schema::rename('portfolio_favorites',  'brief_favorites');
        Schema::rename('portfolio_categories', 'brief_categories');
    }

    public function down(): void
    {
        Schema::rename('briefs',           'portfolios');
        Schema::rename('brief_likes',      'portfolio_likes');
        Schema::rename('brief_comments',   'portfolio_comments');
        Schema::rename('brief_favorites',  'portfolio_favorites');
        Schema::rename('brief_categories', 'portfolio_categories');
    }
};
