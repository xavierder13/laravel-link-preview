<?php

namespace App\Http\Middleware;

use Closure;
use Auth;

class LinkPreviewMaintenance
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $user = Auth::user();
        $api = 'api/link_preview';
        //Link Preview Record
        if($request->is($api.'/index')){
            if($user->can('link-preview-list')){
                return $next($request); 
            }
        }

        //Link Preview Create
        if($request->is($api.'/create') || $request->is($api.'/store') || $request->is($api.'/img_upload')){
            if($user->can('link-preview-create')){
                return $next($request); 
            }
        }

        //Link Preview Edit
        if($request->is($api.'/edit/*') || $request->is($api.'/update/*') || $request->is($api.'/img_delete')){
            if($user->can('link-preview-edit')){
                return $next($request); 
            }
        }

        //Link Preview Delete
        if($request->is($api.'/delete')){
            if($user->can('link-preview-delete')){
                return $next($request); 
            }
        }
        

        return abort(401, 'Unauthorized');
    }
}
