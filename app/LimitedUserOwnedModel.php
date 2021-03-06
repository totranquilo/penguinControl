<?php

namespace App;

use App\Models\User;
use App\Models\UserLimit;

abstract class LimitedUserOwnedModel extends UserOwnedModel
{
	public static function getTableName ()
	{
		return with (new static)->getTable ();
	}
	
	public static function allowNew (User $user)
	{
		$count = self::getCount ($user);
		$allowed = self::getLimit ($user);
		
		return ($count < $allowed);
	}
	
	public static function getCount (User $user)
	{
		return self::where ('uid', $user->uid)->count ();
	}
	
	public static function getLimit (User $user)
	{
		$limit = UserLimit::where ('uid', $user->uid);
		if ($limit->count () < 1)
			$limit = UserLimit::whereNull ('uid');
		$tableName = self::getTableName ();
		
		return $limit->first ()->$tableName;
	}
	
	public static function getGlobalLimit ()
	{
		return UserLimit::whereNull ('uid')->pluck (self::getTableName ());
	}
}