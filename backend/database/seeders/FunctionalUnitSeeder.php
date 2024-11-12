<?php

namespace Database\Seeders;

use App\Models\AppDepartements;
use App\Models\AppAwards;
use Illuminate\Database\Seeder;

class CollaborationAwardSeeder extends Seeder
{
    public function run()
    {

        // Functional Departements Seeder
        $str = file_get_contents('database/data/functional-units.json');
        $departements = json_decode($str, true);
        foreach($departements['departments'] as $index => $departement) {
            AppDepartements::updateOrCreate([
                'label' => $departement['unitName']
            ], [
                'is_public' => true,
                'description' => $departement['definition'],
                'responsibilities' => $departement['responsibilities']
            ]);
        }

        // Certificate Types
        $str = file_get_contents('database/data/collaboration-awards.json');
        $types = json_decode($str, true);
        foreach($types['collaborationAwards'] as $index => $type) {
            AppAwards::updateOrCreate([
                'label' => $type['type']
            ], [
                'is_public' => $type['is_public'],
                'description' => $type['description'],
                'credits' => (int) $type['credits'],
                'evaluation' => (int) $type['evaluation'],
                'icon' => $type['icon'],
            ]);
        }
    }
}
