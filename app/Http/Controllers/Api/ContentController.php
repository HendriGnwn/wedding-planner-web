<?php

namespace App\Http\Controllers\Api;

use App\Concept;
use App\Content;
use App\Helpers\FormatConverter;
use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;

class ContentController extends Controller
{
    /**
     * @param type $id = concept_id
     * @param Request $request
     * @return type
     */
    public function index($conteptId, $isCustomConcept, Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();
		if ($user->token != JWTAuth::getToken()) {
			return response()->json([
				'status' => 401,
				'message' => 'Invalid credentials'
			], 401);
		}
        
        $user = User::whereId($user->id)->roleMobileApp()->first();
        if (!$user) {
            return response()->json([
				'status' => 401,
				'message' => 'Invalid credentials'
			], 401);
        }
        
        $userRelationId = $user->userRelation ? $user->userRelation->id : null;
        if (!$userRelationId) {
            return response()->json([
				'status' => 500,
				'message' => 'Something error. Please try again'
			], 500);
        }
        if ($isCustomConcept) {
            $contents = Content::where('user_relation_id', $userRelationId)
                ->where('user_relation_concept_id', $conteptId)
                ->actived()
                ->ordered()
                ->get();
        } else {
            $contents = Content::where('user_relation_id', $userRelationId)
                ->where('concept_id', $conteptId)
                ->actived()
                ->ordered()
                ->get();
        }
        
        
        return response()->json([
            'status' => 200,
            'message' => 'success',
            'concept_id' => $conteptId,
            'data' => $contents
        ], 200);
    }
    
    public function store($conceptId, $isCustomConcept, Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();
		if ($user->token != JWTAuth::getToken()) {
			return response()->json([
				'status' => 401,
				'message' => 'Invalid credentials'
			], 401);
		}
        
        $user = User::whereId($user->id)->roleMobileApp()->first();
        if (!$user) {
            return response()->json([
				'status' => 401,
				'message' => 'Invalid credentials'
			], 401);
        }
        
        $content = new Content();
        if ($isCustomConcept) {
            $concept = \App\UserRelationConcept::whereId($conceptId)->first();
            $content->user_relation_concept_id = $conceptId;
            $content->concept_id = null;
        } else {
            $concept = Concept::whereId($conceptId)->first();
            $content->concept_id = $conceptId;
            $content->user_relation_concept_id = null;
        }
        if (!$concept) {
            return response()->json([
                'status' => 404,
                'message' => 'Gagal, silahkan input kembali',
            ], 404);
        }
        
        $validator = \Validator::make($request->all(), [
            'name' => 'required',
        ]);

        if ($validator->fails()) {
			return response()->json([
				'status' => 400,
				'message' => 'Some Parameters is required',
				'validators' => FormatConverter::parseValidatorErrors($validator),
			], 400);
		}
        
        if ($user->gender == User::GENDER_MALE) {
            $relation = $user->maleUserRelation;
            $relationPartner = $relation->femaleUser;
        } else {
            $relation = $user->femaleUserRelation;
            $relationPartner = $relation->maleUser;
        }
        
        $lastContentOrder = Content::where('user_relation_concept_id', $conceptId)
                ->orWhere('concept_id', $conceptId)
                ->where('user_relation_id', $relation->id)
                ->orderBy('id', 'desc')
                ->first();
        if ($lastContentOrder) {
            $order = $lastContentOrder->order + 1;
        } else {
            $order = 0;
        }
        
        $content->grouping = null;
        $content->user_id = $user->id;
        $content->user_relation_id = $relation->id;
        $content->name = $request->name;
        $content->status = Content::STATUS_ACTIVE;
        $content->is_not_deleted = Content::IS_NOT_DELETED_FALSE;
        $content->order = $order;
        $content->save();
        $content->triggerInsertContentDetails();
        
        if ($isCustomConcept) {
            $contents = Content::where('user_relation_id', $relation->id)
                ->where('user_relation_concept_id', $conceptId)
                ->actived()
                ->ordered()
                ->get();
        } else {
            $contents = Content::where('user_relation_id', $relation->id)
                ->where('concept_id', $conceptId)
                ->actived()
                ->ordered()
                ->get();
        }
        
        return response()->json([
            'status' => 200,
            'message' => 'success',
            'concept_id' => $conceptId,
            'data' => $contents,
        ], 200);
    }
    
    public function update($id, Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();
		if ($user->token != JWTAuth::getToken()) {
			return response()->json([
				'status' => 401,
				'message' => 'Invalid credentials'
			], 401);
		}
        
        $user = User::whereId($user->id)->roleMobileApp()->first();
        if (!$user) {
            return response()->json([
				'status' => 401,
				'message' => 'Invalid credentials'
			], 401);
        }
        
        $content = Content::whereId($id)->first();
        if (!$content) {
            return response()->json([
                'status' => 404,
                'message' => 'Gagal, silahkan input kembali',
            ], 404);
        }
        
        $validator = \Validator::make($request->all(), [
            'name' => 'required',
        ]);

        if ($validator->fails()) {
			return response()->json([
				'status' => 400,
				'message' => 'Some Parameters is required',
				'validators' => FormatConverter::parseValidatorErrors($validator),
			], 400);
		}
        
        $content->name = $request->name;
        $content->status = Content::STATUS_ACTIVE;
        $content->is_not_deleted = Content::IS_NOT_DELETED_FALSE;
        $content->save();
        
        if ($content->user_relation_concept_id) {
            $contents = Content::where('user_relation_id', $content->user_relation_id)
                ->where('user_relation_concept_id', $content->user_relation_concept_id)
                ->actived()
                ->ordered()
                ->get();
        } else {
            $contents = Content::where('user_relation_id', $content->user_relation_id)
                ->where('concept_id', $content->concept_id)
                ->actived()
                ->ordered()
                ->get();
        }
        
        return response()->json([
            'status' => 200,
            'message' => 'success',
            'concept_id' => $content->user_relation_concept_id ? $content->user_relation_concept_id : $content->concept_id,
            'data' => $contents,
        ], 200);
    }
    
    public function delete($id, Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();
		if ($user->token != JWTAuth::getToken()) {
			return response()->json([
				'status' => 401,
				'message' => 'Invalid credentials'
			], 401);
		}
        
        $user = User::whereId($user->id)->roleMobileApp()->first();
        if (!$user) {
            return response()->json([
				'status' => 401,
				'message' => 'Invalid credentials'
			], 401);
        }
        
        $content = Content::whereId($id)
                ->where('is_not_deleted', Content::IS_NOT_DELETED_FALSE)
                ->first();
        if (!$content) {
            return response()->json([
                'status' => 404,
                'message' => 'Data ini tidak dapat di hapus',
            ], 404);
        }
        
        $content->delete();
        
        if ($content->user_relation_concept_id) {
            $contents = Content::where('user_relation_id', $content->user_relation_id)
                ->where('user_relation_concept_id', $content->user_relation_concept_id)
                ->actived()
                ->ordered()
                ->get();
        } else {
            $contents = Content::where('user_relation_id', $content->user_relation_id)
                ->where('concept_id', $content->concept_id)
                ->actived()
                ->ordered()
                ->get();
        }
        
        return response()->json([
            'status' => 200,
            'message' => 'success',
            'concept_id' => $content->user_relation_concept_id ? $content->user_relation_concept_id : $content->concept_id,
            'data' => $contents,
        ], 200);
    }
}
