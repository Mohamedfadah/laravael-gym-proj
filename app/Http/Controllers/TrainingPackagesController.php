<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TrainingPackage;
use App\Models\Gym;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;


use DataTables;

class TrainingPackagesController extends Controller
{
    #=======================================================================================#
    #			                             index                                         	#
    #=======================================================================================#
    public function index()
    {
        $packages = TrainingPackage::all();
        if (count($packages) <= 0) { //for empty statement
            return view('empty');
        }
        return view('trainingPackeges.listPackeges', ['packages' => $packages]);
    }
    #=======================================================================================#
    #			                             create                                        	#
    #=======================================================================================#
    public function create()
    {
        $packages = TrainingPackage::all();
        $gyms=Gym::all();


        return view('trainingPackeges.create', [
            'packages' => $packages,
            'gyms' => $gyms

        ]);
    }
    #=======================================================================================#
    #			                             store                                         	#
    #=======================================================================================#
    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required'],
            'price' => ['required', 'numeric', 'min:10', 'max:1000'],
            'sessions_number' => ['required', 'numeric', 'min:1', 'max:40'],
            'gym_id' => ['required'],
        ]);

        $requestData = request()->all();
        $user = Auth::user();
        $package = TrainingPackage::create(
            [
                'name' => $requestData['name'],
                'price' => $requestData['price'],
                'sessions_number' => $requestData['sessions_number'],
                'user_id' => $user->id,
            ]
        );

        $id = $package->id;


        $data = array('gym_id' => $request->gym_id, "training_package_id" => $id);
        DB::table('gyms_training_packages')->insert($data);



        return redirect(route('showPackages'));
    }
    #=======================================================================================#
    #			                             show                                         	#
    #=======================================================================================#
    public function showPackages(Request $request)
    {
        if ($request->ajax()) {
            $data = TrainingPackage::select('*');
            return DataTables::of($data)
                    ->addIndexColumn()

                    ->addColumn('action', function ($row) {
                        $btn = '<a href="/admin/tarning-packages/'.$row->id.'" class="edit btn btn-primary btn-sm">View</a> ';
                        $btn .= '<a href="/admin/addEditPackage/'.$row->id.'" class="edit btn btn-warning btn-sm">Edit</a> ';
                        $btn .= '<a href="/admin/delTaraningPackage/'.$row->id.'" class="edit btn btn-danger btn-sm">Delete</a>';
    
                        return $btn;
                    })
                    ->rawColumns(['action'])
                    ->make(true);
        }
        return view('trainingPackeges.list');
        // return view('trainingPackeges.show_training_package', ['package' => $package]);
    }
    public function show($id)
    {
        $package = TrainingPackage::find($id);
 
        return view("trainingPackeges.show", ['package' => $package]);
    }
    
    #=======================================================================================#
    #			                             edit                                         	#
    #=======================================================================================#
    public function edit($id)
    {
        $packages = TrainingPackage::all();

        $package = TrainingPackage::find($id);
        $gyms = Gym::all();

        return view('trainingPackeges.edit', ['package' => $package, 'packages' => $packages, 'gyms' => $gyms]);
    }

    public function editPackage(Request $request, $id)
    {
        $request->validate([
            'name' => ['required'],
            'price' => ['required', 'numeric', 'min:10', 'max:4000'],
            'sessions_number' => ['required', 'numeric', 'min:1', 'max:60']
        ]);

        $packages = TrainingPackage::findorfail($id);

        $packages->name = $request->name;
        $packages->price = $request->price;
        $packages->sessions_number = $request->sessions_number;
        $packages->update();
        return redirect(route('showPackages'));
    }

    #=======================================================================================#
    #			                             destroy                                       	#
    #=======================================================================================#
    public function deletePackage($id)
    {
        $package = TrainingPackage::findorfail($id);
        $package->delete();
        return redirect(route('showPackages'));
    }
}
