<?php
namespace App\Repositories;
use App\User;

class UserRepository implements UserRepositoryInterface
{
    /**
     * Get's all posts.
     *
     * @return mixed
     */
    public function index()
    {
        $users=User::select('users.id','users.name','users.email','users.created_at')
        ->sortable()->paginate(10);
        return $users;
    }
   
    public function store()
    {
    	$user=new User;
    	return $user;
    }
}