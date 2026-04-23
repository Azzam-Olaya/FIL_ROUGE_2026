<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class PortfolioFavorite extends Model {
    protected $fillable = ['portfolio_id', 'user_id'];
}
