<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\User;
use App\UserAdtnlInfo;
use Response;
use DB;
use App\AuthLogs;
use Auth;
use Session;
use App\Mail\WelcomeMail;
use Illuminate\Support\Facades\Mail;
use App\UserActivity;
use App\Repositories\UserRepositoryInterface;   
use App\Repositories\UserAdtnlInfoRepositoryInterface;
use App\Repositories\UserActivityRepositoryInterface;
class UserController extends Controller
{
    
    protected $user_obj;
    public function __construct(UserRepositoryInterface $user_obj,UserAdtnlInfoRepositoryInterface $useradtnl_obj,UserActivityRepositoryInterface $useractivity_obj)
    {
         $this->user_obj = $user_obj;
         $this->useradtnl_obj = $useradtnl_obj;
         $this->useractivity_obj = $useractivity_obj;
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
       
        // $users=User::select('users.id','users.name','users.email','users.created_at')
        // ->sortable()->paginate(10);
        // return view('users.index',compact('users'));

        $users=$this->user_obj->index();
        return view('users.index',compact('users'));
    }
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('users.create');
    }
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'username' => 'required',
            'email' => 'required|unique:users|email',
            'password' => 'required',
            'confirm_password' => 'required',
            'firstname' => 'required',
            'lastname' => 'required',
            'address' => 'required',
            'house_number' => 'required',
            'postal_code' => 'required',
            'city' => 'required',
            'telephone_number' => 'required|max:10',
            'status' => 'required'
        
        ]);
        if($request->password !== $request->confirm_password){
            return "Passwords doesnt Match";
        }else{
          
            $org_pas=$request->password;
            $user=$this->user_obj->store();
            $user->name=$request->username;
            $user->email=$request->email;
            $user->password=bcrypt($request->password);
            $user->save();
            $useradtnlinfo=$this->useradtnl_obj->store();
            $useradtnlinfo->id=$user->id;
            $useradtnlinfo->first_name=$request->firstname;
            $useradtnlinfo->last_name=$request->lastname;
            $useradtnlinfo->address=$request->address;
            $useradtnlinfo->house_number=$request->house_number;
            $useradtnlinfo->postal_code=$request->postal_code;
            $useradtnlinfo->city=$request->city;
            $useradtnlinfo->telephone_number=$request->telephone_number;
            $useradtnlinfo->status=$request->status;
            $useradtnlinfo->save();
            $user['org_pas']=$org_pas;

            Mail::to($user->email)->send(new WelcomeMail($user));

            $user_activity=$this->useractivity_obj->store();
            $user_activity->id=Auth::id();
            $user_activity->by=User::find(Auth::id())->name;
            $user_activity->activity_on_id=$user->id;
            $user_activity->ip_address=$request->getClientIp();
            $user_activity->activity='Create';
            $user_activity->save(); 
            return redirect('/homelisting');

                        
        }
    }
    public function completeRegistration(Request $request)
    {
       
        $request->merge(session('registration_data'));

        return $this->registration($request);
    }
    public function registration(Request $request)
    {
        $update=array();
        $model=array();
        $field=array();
        $old=array();
        $new=array();

    $user=User::find($request->id);
    if($user->name!=$request->username){
       
        $update['2']=0;
        $model['2']="User";
        $field['2']="username";
        $old['2']=$user->name;
        $new['2']=$request->username;
        $user->name=$request->username;
    }else{
        $update['2']=0;$model['2']=$field['2']=$old['2']=$new['2']="";
    }
    if($user->email!=$request->email){
       
        $update['3']=0;
        $model['3']="UserAdtnlInfo";
        $field['3']="email";
        $old['3']=$user->email;
        $new['3']=$request->email;
        $user->email=$request->email;
    }else{
        $update['3']=0;$model['3']=$field['3']=$old['3']=$new['3']="";
    }
    if($user->google_auth!=$request->google_auth){
       
        $update['4']=0;
        $model['4']="User";
        $field['4']="google_auth_change";
        $old['4']=$user->google_auth;
        $new['4']=$request->google_auth;
        $user->google_auth=1;
    }else{
        $update['4']=0;$model['4']=$field['4']=$old['4']=$new['4']="";
    }

    $user->google2fa_secret=$request->google2fa_secret;
    $user->save();
    $user_adtnl_info=UserAdtnlInfo::find($request->id);
    if($user_adtnl_info->first_name!=$request->firstname){
       
        $update['0']=0;
        $model['0']="UserAdtnlInfo";
        $field['0']="first_name";
        $old['0']=$user_adtnl_info->firstname;
        $new['0']=$request->firstname;
        $user_adtnl_info->first_name=$request->firstname;
    }else{
        $update['0']=0;$model['0']=$field['0']=$old['0']=$new['0']="";
    }
    
    if($user_adtnl_info->last_name!=$request->lastname){
        
        $update['1']=1;
        $model['1']="UserAdtnlInfo";
        $field['1']="last_name";
        $old['1']=$user_adtnl_info->lastname;
        $new['1']=$request->lastname;
        $user_adtnl_info->last_name=$request->lastname;
    }else{
        $update['1']=0;$model['1']=$field['1']=$old['1']=$new['1']="";
    }
      
    $user_adtnl_info->save();

    for($i=0;$i< sizeof($update);$i++){
     
        if($field[$i]!=''){
            $user_activity=new UserActivity;
            $user_activity->id=Auth::id();
            $user_activity->by=User::find(Auth::id())->name;
            $user_activity->activity_on_id=$user->id;
            $user_activity->ip_address=$request->getClientIp();
            $user_activity->activity='Update';
            $user_activity->model_name=$model[$i];
            $user_activity->field_name=$field[$i];
            $user_activity->old_value=$old[$i];
            $user_activity->new_value=$new[$i];
            $user_activity->save();
            
        }
    }


    Session::flash('message', 'User Details Updated!');
    Session::flash('status', 'success');
    return redirect('/home');
    }
    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }
    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $user=User::findOrFail($id);
        $useradtnlinfo=UserAdtnlInfo::find($id);
        return view('users.edit',compact('user','useradtnlinfo'));
    }
    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        // have not implemented changing passord via edit
// print_r($request->firstname);
// return;
        $update=array();
        $model=array();
        $field=array();
        $old=array();
        $new=array();

        $user=User::find($id);
        if($user->name!=$request->username){
       
            $update['8']=0;
            $model['8']="User";
            $field['8']="username";
            $old['8']=$user->name;
            $new['8']=$request->username;
            $user->name=$request->username;
        }else{
            $update['8']=0;$model['8']=$field['8']=$old['8']=$new['8']="";
        }
        if($user->email!=$request->email){
           
            $update['9']=0;
            $model['9']="UserAdtnlInfo";
            $field['9']="email";
            $old['9']=$user->email;
            $new['9']=$request->email;
            $user->email=$request->email;
        }else{
            $update['9']=0;$model['9']=$field['9']=$old['9']=$new['9']="";
        }
        if($user->google_auth!=$request->google_auth){
           
            $update['4']=0;
            $model['4']="User";
            $field['4']="google_auth_change";
            $old['4']=$user->google_auth;
            $new['4']=$request->google_auth;
            $user->google_auth=0;
        }else{
            $update['4']=0;$model['4']=$field['4']=$old['4']=$new['4']="";
        }
        $user->name=$request->username;
        $user->email=$request->email;
        $user->save();

        $useradtnlinfo=UserAdtnlInfo::find($id);
        if($useradtnlinfo->first_name!=$request->firstname){
            // return "Not same";
            $update['0']=0;
            $model['0']="UserAdtnlInfo";
            $field['0']="first_name";
            $old['0']=$useradtnlinfo->first_name;
            $new['0']=$request->firstname;
            $useradtnlinfo->first_name=$request->firstname;
        }else{
            $update['0']=0;$model['0']=$field['0']=$old['0']=$new['0']="";
        }
        
        if($useradtnlinfo->last_name!=$request->lastname){
            
            $update['1']=1;
            $model['1']="UserAdtnlInfo";
            $field['1']="last_name";
            $old['1']=$useradtnlinfo->last_name;
            $new['1']=$request->lastname;
            $useradtnlinfo->last_name=$request->lastname;
        }else{
            $update['1']=0;$model['1']=$field['1']=$old['1']=$new['1']="";
        }
        
        if($useradtnlinfo->address!=$request->address){
            $update['2']=1;
            $model['2']="UserAdtnlInfo";
            $field['2']="address";
            $old['2']=$useradtnlinfo->address;
            $new['2']=$request->address;
            $useradtnlinfo->address=$request->address;
        }else{
            $update['2']=0;$model['2']=$field['2']=$old['2']=$new['2']="";
        }
        
        if($useradtnlinfo->house_number!=$request->house_number){
            $update['3']=1;
            $model['3']="UserAdtnlInfo";
            $field['3']="house_number";
            $old['3']=$useradtnlinfo->house_number;
            $new['3']=$request->house_number;
            $useradtnlinfo->house_number=$request->house_number;
        }else{
            $update['3']=0;$model['3']=$field['3']=$old['3']=$new['3']="";
        }
        if($useradtnlinfo->postal_code!=$request->postal_code){
            $update['4']=1;
            $model['4']="UserAdtnlInfo";
            $field['4']="postal_code";
            $old['4']=$useradtnlinfo->postal_code;
            $new['4']=$request->postal_code;
            $useradtnlinfo->postal_code=$request->postal_code;
        }else{
            $update['4']=0;$model['4']=$field['4']=$old['4']=$new['4']="";
        }

      
        if($useradtnlinfo->city!=$request->city){
            $update['5']=1;
            $model['5']="UserAdtnlInfo";
            $field['5']="city";
            $old['5']=$useradtnlinfo->city;
            $new['5']=$request->city;
            $useradtnlinfo->city=$request->city;
        }else{
            $update['5']=0;$model['5']=$field['5']=$old['5']=$new['5']="";
        }
        if($useradtnlinfo->telephone_number!=$request->telephone_number){
            $update['6']=1;
            $model['6']="UserAdtnlInfo";
            $field['6']="telephone_number";
            $old['6']=$useradtnlinfo->telephone_number;
            $new['6']=$request->telephone_number;
            $useradtnlinfo->telephone_number=$request->telephone_number;
        }else{
            $update['6']=0;$model['6']=$field['6']=$old['6']=$new['6']="";
        }
        if($useradtnlinfo->status!=$request->status){
            $update['7']=1;
            $model['7']="UserAdtnlInfo";
            $field['7']="status";
            $old['7']=$useradtnlinfo->status;
            $new['7']=$request->status;
            $useradtnlinfo->status=$request->status;
        }else{
            $update['7']=0;$model['7']=$field['7']=$old['7']=$new['7']="";
        }
        
        for($i=0;$i< sizeof($update);$i++){
            if($field[$i]!=''){
                $user_activity=new UserActivity;
                $user_activity->id=Auth::id();
                $user_activity->by=User::find(Auth::id())->name;
                $user_activity->activity_on_id=$user->id;
                $user_activity->ip_address=$request->getClientIp();
                $user_activity->activity='Update';
                $user_activity->model_name=$model[$i];
                $user_activity->field_name=$field[$i];
                $user_activity->old_value=$old[$i];
                $user_activity->new_value=$new[$i];
                $user_activity->save();
                
            }
        }
        $useradtnlinfo->save();
        
        // return;
        Session::flash('message', 'User Details Updated!');
        Session::flash('status', 'success');
        return redirect('/homelisting');
    }
    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
   
    public function export()
    {
       
        $table=User::leftJoin('users_addtnl_info', 'users.id', '=', 'users_addtnl_info.id')
        ->select('users.id','users.name','users_addtnl_info.first_name','users_addtnl_info.last_name','users.email','users_addtnl_info.address','users_addtnl_info.house_number','users_addtnl_info.city','users_addtnl_info.postal_code','users.created_at','users_addtnl_info.status')
        ->get();
         $i=1;
         $j=1;   

        $filename = "users.csv";
        $handle = fopen($filename, 'w+');
        fputcsv($handle, array('Sr No:','User Id', 'User name','First Name','Last Name','Email','Address','House Number','City','Postal Code','Creation Date','Staus'));
        foreach($table as $row) {
        fputcsv($handle, array($i,$row['id'], $row['name'], $row['first_name'], $row['last_name'], $row['email'], $row['address'], $row['house_number'], $row['city'], $row['postal_code'], $row['created_at'], $row['status']));
        $i++;
        fputcsv($handle, array('Sr No','Activity', 'Field Name','Old Value','Modified Value','Modified By'));
            $table2=UserActivity::where('activity_on_id',$row['id'])->get();
            foreach($table2 as $row2) {
            fputcsv($handle, array($j,$row2['activity'], $row2['field_name'], $row2['old_value'], $row2['new_value'], $row2['by']));
            $j++;
            }    
        }
        fclose($handle);
        $headers = array(
        'Content-Type' => 'text/csv',
        );
        return Response::download($filename, 'users.csv', $headers);
    }
   public function show_edit($id){
        $user=User::leftJoin('users_addtnl_info','users.id', '=', 'users_addtnl_info.id')
        ->where('users.id',$id)
        ->select('users.id','users.name','users_addtnl_info.first_name','users_addtnl_info.last_name','users.email','users.google_auth')
        ->first();
        // google2fa section
        $google2fa = app('pragmarx.google2fa');
        $registration_data["google2fa_secret"] = $google2fa->generateSecretKey();
        //  $request->session()->flash('registration_data', $registration_data);
        $QR_Image = $google2fa->getQRCodeInline(
        config('app.name'),
        $user->email,
        $registration_data['google2fa_secret']
        );
        //  return view('google2fa.register', ['QR_Image' => $QR_Image, 'secret' => $registration_data['google2fa_secret']]);
        //end of google_2f section

        $user_activity=new UserActivity;
        $user_activity->id=Auth::id();
        $user_activity->by=User::find(Auth::id())->name;
        $user_activity->activity_on_id=$id;
        $user_activity->ip_address=request()->ip();
        $user_activity->activity='Read';
        $user_activity->save(); 

        return view('users.show_edit',compact('user'),['QR_Image' => $QR_Image, 'secret' => $registration_data['google2fa_secret']]);
    }
   public function show_edit_save(Request $request){
    $update=array();
    $model=array();
    $field=array();
    $old=array();
    $new=array();
    
    if($request->google_auth==0){
    $user=User::find($request->id);

    if($user->name!=$request->username){
       
        $update['2']=0;
        $model['2']="User";
        $field['2']="username";
        $old['2']=$user->name;
        $new['2']=$request->username;
        $user->name=$request->username;
    }else{
        $update['2']=0;$model['2']=$field['2']=$old['2']=$new['2']="";
    }
    if($user->email!=$request->email){
       
        $update['3']=0;
        $model['3']="UserAdtnlInfo";
        $field['3']="email";
        $old['3']=$user->email;
        $new['3']=$request->email;
        $user->email=$request->email;
    }else{
        $update['3']=0;$model['3']=$field['3']=$old['3']=$new['3']="";
    }
    if($user->google_auth!=$request->google_auth){
       
        $update['4']=0;
        $model['4']="User";
        $field['4']="google_auth_change";
        $old['4']=$user->google_auth;
        $new['4']=$request->google_auth;
        $user->google_auth=0;
    }else{
        $update['4']=0;$model['4']=$field['4']=$old['4']=$new['4']="";
    }
       
    $user->save();
    $user_adtnl_info=UserAdtnlInfo::find($request->id);
    if($user_adtnl_info->first_name!=$request->firstname){
       
        $update['0']=0;
        $model['0']="UserAdtnlInfo";
        $field['0']="first_name";
        $old['0']=$user_adtnl_info->firstname;
        $new['0']=$request->firstname;
        $user_adtnl_info->first_name=$request->firstname;
    }else{
        $update['0']=0;$model['0']=$field['0']=$old['0']=$new['0']="";
    }
    
    if($user_adtnl_info->last_name!=$request->lastname){
        
        $update['1']=1;
        $model['1']="UserAdtnlInfo";
        $field['1']="last_name";
        $old['1']=$user_adtnl_info->lastname;
        $new['1']=$request->lastname;
        $user_adtnl_info->last_name=$request->lastname;
    }else{
        $update['1']=0;$model['1']=$field['1']=$old['1']=$new['1']="";
    }
    $user_adtnl_info->save();

    for($i=0;$i< sizeof($update);$i++){
     
        if($field[$i]!=''){
            $user_activity=new UserActivity;
            $user_activity->id=Auth::id();
            $user_activity->by=User::find(Auth::id())->name;
            $user_activity->activity_on_id=$user->id;
            $user_activity->ip_address=$request->getClientIp();
            $user_activity->activity='Update';
            $user_activity->model_name=$model[$i];
            $user_activity->field_name=$field[$i];
            $user_activity->old_value=$old[$i];
            $user_activity->new_value=$new[$i];
            $user_activity->save();
            // print_r($field);
            // return;
        }
    }
    



    Session::flash('message', 'User Details Updated!');
    Session::flash('status', 'success');
    return redirect('/homelisting');
   }elseif($request->google_auth==1)
   {
        $google2fa = app('pragmarx.google2fa');
        $registration_data = $request->all();
        $registration_data["google2fa_secret"] = $google2fa->generateSecretKey();
        $request->session()->flash('registration_data', $registration_data);
        $QR_Image = $google2fa->getQRCodeInline(
            config('app.name'),
            $registration_data['email'],
            $registration_data['google2fa_secret']
        );
        return view('google2fa.register', ['QR_Image' => $QR_Image, 'secret' => $registration_data['google2fa_secret']]);
   }
}   
   public function delete($id){
    // return confirm('clicked');
        UserAdtnlInfo::where('id',$id)->delete();
        AuthLogs::where('id',$id)->delete();
        User::where('id',$id)->delete();
        $user_activity=new UserActivity;
        $user_activity->id=Auth::id();
        $user_activity->by=User::find(Auth::id())->name;
        $user_activity->activity_on_id=$id;
        $user_activity->ip_address=request()->ip();
        $user_activity->activity='Delete';
        $user_activity->save(); 
        return redirect('/homelisting');
   }
}