<?php

namespace App\Http\Controllers\User;

use Exception;
use App\Models\UserEntity;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Middleware\AccessBusinessCockpit;
use App\Http\Middleware\AccessCommunity;
use App\Models\PivotUserEntityShortcuts;
use App\Http\Collections\EntityCollection;
use Illuminate\Validation\ValidationException;

class UserShortcutsController extends Controller
{
    //* Get all active collaboration
    public function getShortcutEntities()
    {
        $entityCollection = [];
        $PivotUserEntityShortcuts = PivotUserEntityShortcuts::where('user_id', Auth::id())->orderBy('created_at', 'desc')->get();

        // Collect entities
        foreach ($PivotUserEntityShortcuts as $collaboration) {
            $entity = $collaboration->belongs_to_entity()->first();
            if(
                $entity->is_community
                && AccessCommunity::entityIsAccessible($entity)
            ) array_push($entityCollection, EntityCollection::render_community_entity($entity));
        };

        return response()->json([
            'entities' => $entityCollection,
            'message' => 'Shortcuts loaded.'
        ], 200);
    }

    //* Connect
    public function connectUserWithEntity(Request $request)
    {
        $data = $request->validate([
            'entity_id' => ['required', 'numeric'],
        ]);

        $entity = UserEntity::find((int) $data['entity_id']);
        if(!AccessCommunity::entityIsAccessible($entity, AccessBusinessCockpit::$accessToken)) {
            throw ValidationException::withMessages([
                'message' => 'Invalid request.',
            ]);
        }

        PivotUserEntityShortcuts::updateOrCreate([
            'user_id' => Auth::id(),
            'entity_id' => $entity->id
        ]);

        return response()->json([
            'message' => 'Shortcut added.'
        ], 200);
    }

    //* Remove Collaboration
    public function removeUserFromEntity($entityID)
    {
        if(!$entityID) throw new Exception('ID is required.');
        PivotUserEntityShortcuts::where([
            'user_id' => Auth::id(),
            'entity_id' => (int) $entityID 
        ])->first()?->delete();

        return response()->json([
            'message' => 'Shortcut removed.'
        ], 200);
    }
}
