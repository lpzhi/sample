<?php

use Illuminate\Database\Seeder;
use App\Models\User;

class FollowersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        $users = User::all();
        $user = User::find(31);

        $user_id= $user->id;

        //剔除掉id 为 51 的
        $followers = $users->slice(1,30);

        $follower_ids = $followers->pluck('id')->toArray();

        $user->follow($follower_ids);

        foreach ($followers as $follower) {
            $follower->follow($user_id);
        }

    }
}
