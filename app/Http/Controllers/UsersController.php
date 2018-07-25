<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Mockery\Exception;


class UsersController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth',['except' => ['show','create','store','confirmEmail']]);

        $this->middleware('guest', [
            'only' => ['create','confirmEmail']
        ]);
    }
    //
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new ResetPassword($token));
    }

    public function index()
    {
        $users = User::paginate(10);
//        return view('users.index',compact('users'));
        return view('users.index', compact('users'));
    }
    public function create()
    {
        return view('users.create');
    }

    public function show(User $user)
    {
       $statuses = $user->statuses()
           ->orderBy('created_at','desc')
           ->paginate(30);

    	return view('users.show',compact('user','statuses'));
    }

    public function delete()
    {

    }

    public function confirmEmail($token)
    {

        $user = User::where('activation_token', $token)->firstOrFail();

        $user->activated = true;
        $user->activation_token = null;
        $user->save();

        Auth::login($user);
        session()->flash('success', '恭喜你，激活成功！');
        return redirect()->route('users.show', [$user]);
    }

    public function store(Request $request)
    {
        $this->validate($request,[
            'name'=>'required|max:50',
            'email'=>'required|email|unique:users|max:255',
            'password'=>'required|min:6|confirmed'
        ]);

        $user = User::create([
            'name'=>$request->name,
            'email'=>$request->email,
            'password'=>bcrypt($request->password),
        ]);

        $this->sendEmailConfirmationTo($user);
        session()->flash('success', '验证邮件已发送到你的注册邮箱上，请注意查收。');

//        Auth::login($user);
//        session()->flash('success','欢迎,您将在这开启一段新的旅程~');

        return redirect('/');

//         return redirect()->route('users.show',compact('user'));
//        return redirect()->intended('users.show',compact('user'));
    }


    public function edit(User $user)
    {
        $this->authorize('update',$user);
        return view('users.edit',compact('user'));
    }


    public function update(User $user,Request $request)
    {
        $this->validate($request,[
            'name' => 'required|max:50',
            'password' => 'nullable|confirmed|min:6',
        ]);

        $data = [];

        $data['name'] = $request->name;

        if($request->password) {
            $data['password'] = bcrypt($request->password);
        }

        $user->update($data);


        session()->flash('success','个人资料更新成功!');

        return redirect()->route('users.show',$user->id);
    }

    public function destroy(User $user)
    {
        $user->delete();
        session()->flash('success', '成功删除用户！');
        return back();
    }

    protected function sendEmailConfirmationTo($user)
    {

        $view = 'emails.confirm';
        $data = compact('user');
        $to = $user->email;
        $subject = "感谢注册 Sample 应用！请确认你的邮箱。";
       $rs =  Mail::send($view, $data, function ($message) use ($to, $subject) {
            $message->to($to)->subject($subject);
        });
    }

    public function followings(User $user)
    {
        DB::connection()->enableQueryLog();
          $user->isFollowing($user->id);
//        $users = $user->followings()->paginate(30);
        print_r(DB::getQueryLog());exit;
        $title = "关注的人";
        return view('users.show_follow', compact('users', 'title'));
    }

}
