<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\TrainingPackage;
use Illuminate\Http\Request;
use App\Models\TrainingSession;
use App\Models\User;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;




class TrainingController extends Controller
{

    #=======================================================================================#
    #			                             index                                         	#
    #=======================================================================================#
    public function index()
    {
        $trainingSessions = TrainingSession::all();
        if (count($trainingSessions) <= 0) {
            return view('empty');
        }
        return view('TrainingSessions.listSessions', ['trainingSessions' => $trainingSessions]);
    }

    #=======================================================================================#
    #			                             create                                        	#
    #=======================================================================================#
    public function create()
    {
        $trainingSessions = TrainingSession::all();
        $users = User::all();
        $packages = TrainingPackage::all();

        foreach ($users as $user) {
            if ($user->hasRole('coach')) {
                $coaches[] = $user;
            }
        }
        foreach ($packages as $package) {
            if ($package->id) {
                $Tpackages[] = $package;
            }
        }
        return view('TrainingSessions.create', [
            'trainingSessions' => $trainingSessions,
            'coaches' => $coaches,
            'Tpackages' =>$Tpackages,
        ]);
    }
    
    #=======================================================================================#
    #			                             store                                         	#
    #=======================================================================================#
    public function store(Request $request)
    {

        $request->validate([
            'name' => ['required', 'string', 'min:2'],
            'day' => ['required', 'date', 'after_or_equal:today'],
            'starts_at' => ['required'],
            'finishes_at' => ['required'],

        ]);

        $validate_old_seesions = TrainingSession::where('day', '=', $request->day)->where("starts_at", "!=", null)->where("finishes_at", "!=", null)->where(function ($q) use ($request) {
            $q->whereRaw("starts_at = '$request->starts_at' and finishes_at ='$request->finishes_at'")
                ->orwhereRaw("starts_at < '$request->starts_at' and finishes_at > '$request->finishes_at'")
                ->orwhereRaw("starts_at > '$request->starts_at' and starts_at < '$request->finishes_at'")
                ->orwhereRaw("finishes_at > '$request->starts_at' and finishes_at < '$request->finishes_at'")
                ->orwhereRaw("'$request->starts_at' > '$request->finishes_at'")
                ->orwhereRaw("'starts_at' > 'finishes_at'")
                ->orwhereRaw("starts_at > '$request->starts_at' and finishes_at < '$request->finishes_at'");
        })->get()->toArray();

        


        if (count($validate_old_seesions) > 0)
            return back()->withErrors("please check your time")->withInput();
        $requestData = request()->all();
        $session = TrainingSession::create($requestData);
        $user_id = $request->input('user_id');
        $id = $session->id;
        $data = array('user_id' => $user_id, "training_session_id" => $id);
        DB::table('training_session_user')->insert($data);

        return redirect(route('showSessions'));
    }
    #=======================================================================================#
    #			                             show                                         	#
    #=======================================================================================#
    public function showSessions(Request $request)
    {
        if ($request->ajax()) {
            $data = TrainingSession::select('*');
            return DataTables::of($data)
                    ->addIndexColumn()

                    ->addColumn('action', function ($row) {
                        $btn = '<a href="/admin/tarning-sessions/'.$row->id.'" class="edit btn btn-primary btn-sm">View</a> ';
                        $btn .= '<a href="/admin/addEditSession/'.$row->id.'" class="edit btn btn-warning btn-sm">Edit</a> ';
                        $btn .= '<a href="/admin/delTaraningSession/'.$row->id.'" class="edit btn btn-danger btn-sm">Delete</a>';
    
                        return $btn;
                    })
                    ->rawColumns(['action'])
                    ->make(true);
        }
        return view('TrainingSessions.listSessions');
    }
    public function show($id)
    {

        
        $trainingSession = TrainingSession::findorfail($id);
        $package = TrainingPackage::findorfail($trainingSession->training_package_id);
        return view('TrainingSessions.show', ['trainingSession' => $trainingSession, 'package' => $package]);
    }

    #=======================================================================================#
    #			                             edit                                         	#
    #=======================================================================================#
    public function edit($id)
    {
        $trainingSessions = TrainingSession::all();

        $trainingSession = TrainingSession::find($id);

        return view('TrainingSessions.edit', ['trainingSession' => $trainingSession, 'trainingSessions' => $trainingSessions]);
    }


    public function editSession(Request $request, $id)
    {
        $request->validate([
            'name' => ['required', 'string'],
            'day' => ['required', 'string'],
            'starts_at' => [
                'required'
            ],
            'finishes_at' => [
                'required'
            ],

        ]);

        $validate_old_seesions = TrainingSession::where('day', '=', $request->day)->where("starts_at", "!=", null)->where("finishes_at", "!=", null)->where(function ($q) use ($request) {
            $q->whereRaw("starts_at = '$request->starts_at' and finishes_at ='$request->finishes_at'")
                ->orwhereRaw("starts_at < '$request->starts_at' and finishes_at > '$request->finishes_at'")
                ->orwhereRaw("starts_at > '$request->starts_at' and starts_at < '$request->finishes_at'")
                ->orwhereRaw("finishes_at > '$request->starts_at' and finishes_at < '$request->finishes_at'")
                ->orwhereRaw("starts_at > '$request->starts_at' and finishes_at < '$request->finishes_at'");
        })->where('id', '!=', $id)->get()->toArray();

        if (count($validate_old_seesions) > 0)
            return back()->withErrors("Time invalid")->withInput();


        if (count(DB::select("select * from training_session_user where training_session_id = $id")) != 0) {
            return back()->withErrors("You can't edit this session because there are users in it!")->withInput();
        }

        TrainingSession::where('id', $id)->update([

            'name' => $request->name,
            'day' => $request->day,
            'starts_at' => $request->starts_at,
            'finishes_at' => $request->finishes_at,



        ]);
        return redirect(route('showSessions'));
    }
    

    #=======================================================================================#
    #			                             delete                                       	#
    #=======================================================================================#
    public function deleteSession($id)
    {

        if (count(DB::select("select * from training_session_user where training_session_id = $id")) == 0) {
            $trainingSession = TrainingSession::findorfail($id);
            $trainingSession->delete();
            return redirect(route('showSessions'));
        }
        else {
            return back()->withErrors("You can't delete this session because there are users in it!")->withInput();
        }
       
    }

}