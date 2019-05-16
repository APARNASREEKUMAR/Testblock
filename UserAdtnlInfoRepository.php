<?php
namespace App\Repositories;
use App\UserAdtnlInfo;

class UserAdtnlInfoRepository implements UserAdtnlInfoRepositoryInterface
{
    /**
     * Get's all posts.
     *
     * @return mixed
     */
  
    public function store()
    {
    	$useradtnlinfo=new UserAdtnlInfo;
    	return $useradtnlinfo;
    }
}