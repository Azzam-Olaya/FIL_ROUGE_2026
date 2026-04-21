<?php
// Script de secours pour recréer l'admin si la commande DB:SEED échoue
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

try {
    DB::beginTransaction();

    // S'assurer que les rôles existent
    if (Role::count() === 0) {
        Role::create(['id' => 1, 'name' => 'admin']);
        Role::create(['id' => 2, 'name' => 'client']);
        Role::create(['id' => 3, 'name' => 'freelancer']);
        echo "Rôles créés...<br>";
    }

    // Créer ou mettre à jour l'admin
    $admin = User::updateOrCreate(
        ['email' => 'admin@morlancer.com'],
        [
            'name' => 'Admin User',
            'password' => 'password', // Haché automatiquement par le cast du modèle
            'role_id' => 1,
            'verification_status' => 'verified',
            'balance' => 0
        ]
    );

    DB::commit();
    echo "<b>Succès !</b> L'utilisateur admin a été créé ou mis à jour.<br>";
    echo "Email : <b>admin@morlancer.com</b><br>";
    echo "Pass : <b>password</b><br>";
    echo "<br><a href='/'>Retour à l'accueil</a>";

} catch (\Exception $e) {
    DB::rollBack();
    echo "Erreur : " . $e->getMessage();
}
