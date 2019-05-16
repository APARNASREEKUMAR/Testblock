<?php
namespace App\Repositories;
use App\UserActivity;

class UserActivityRepository implements UserActivityRepositoryInterface
{
    /**
     * Get's all posts.
     *
     * @return mixed
     */

    public function store()
    {
    	$useractivity=new UserActivity;
    	return $useractivity;
    }
}