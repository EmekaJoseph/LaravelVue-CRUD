<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller as BaseController;
use App\Models\Task1;
use Illuminate\Http\Request;
use Carbon\Carbon;

class Admin extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function index()
    {
        $all = Task1::all();

        if (sizeof($all) > 0) {
            foreach ($all as $task) {
                $task->created = (Carbon::parse($task->created_at))->diffForHumans();
                $task->expiryAt = (Carbon::parse($task->expiry))->diffForHumans();
            }
        }

        return response()->json($all);
    }

    public function store(Request $request)
    {
        $task = new Task1();
        $task->title = $request->title;
        $task->isDone = false;
        $task->expiry = $request->expiry;
        return $task->save();
    }


    public function show($id)
    {
        $task = Task1::find($id);
        if ($task !== null) {
            $task->posted_date = (Carbon::parse($task->created_at))->diffForHumans();
        }
        return response()->json($task);
    }



    public function destroy($id)
    {
        return Task1::where('id', $id)->delete();
    }

    public function update($id, Request $request)
    {
        $task = Task1::find($id);
        $task->title = $request->title;
        $task->expiry = $request->expiry;
        return $task->save();
    }
}
