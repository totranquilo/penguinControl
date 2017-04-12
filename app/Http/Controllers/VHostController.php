<?php

namespace App\Http\Controllers;

use App\Alert;
use App\Models\Log;
use App\Models\SystemTask;
use App\Models\Vhost;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;

class VHostController extends Controller
{
	public function index ()
	{
		$user = Auth::user ();
		$userInfo = $user->userInfo;
		$vhosts = Vhost::where ('uid', $user->uid)->get ();
		
		$apacheReloadInterval = SystemTask::where ('type', SystemTask::TYPE_APACHE_RELOAD)
			->where
			(
				function ($query)
				{
					$query->where ('end', '>', time ())
						->orWhereNull ('end');
				}
			)->min ('interval');
		$apacheReloadInterval = SystemTask::friendlyInterval ($apacheReloadInterval);
		
		return view ('website.vhost.index', compact ('user', 'userInfo', 'vhosts', 'apacheReloadInterval'));
	}
	
	public function create ()
	{
		$user = Auth::user ();
		$userInfo = $user->userInfo;
		
		if (! Vhost::allowNew ($user))
			return Redirect::to ('/website/vhost')->with ('alerts', array (new Alert ('You are only allowed to create ' . Vhost::getLimit ($user) . ' vHosts.', Alert::TYPE_ALERT)));
		
		return view ('website.vhost.create', compact ('user', 'userInfo'));
	}

	public function store ()
	{
		$user = Auth::user ();
		
		if (! Vhost::allowNew ($user))
			return Redirect::to ('/website/vhost/create')->withInput ()->with ('alerts', array (new Alert ('You are only allowed to create ' . Vhost::getLimit ($user) . ' vHosts.', Alert::TYPE_ALERT)));
		
		$servername = @strtolower (Input::get ('servername'));
		$docroot = @trailing_slash (Input::get ('docroot'));
		
		$validator = Validator::make
		(
			array
			(
				'Host' => $servername,
				//'Beheerder' => Input::get ('serveradmin'),
				'Alias' => Input::get ('serveralias'),
				'Document root' => $docroot,
				'Protocol' => Input::get ('ssl'),
				'CGI' => Input::get ('cgi'),
				'Install Wordpress' => Input::get ('installWordpress')
			),
			array
			(
				'Host' => array ('required', 'unique:vhost,servername', 'unique:vhost,serveralias', 'regex:/^[a-zA-Z0-9\.\_\-]+\.[a-zA-Z0-9\.\_\-]+$/'),
				//'Beheerder' => array ('required', 'email'),
				'Alias' => array ('nullable', 'different:Host', 'unique:vhost,servername', 'unique:vhost,serveralias', 'regex:/^[a-zA-Z0-9\.\_\-]+\.[a-zA-Z0-9\.\_\-]+(\s[a-zA-Z0-9\.\_\-]+\.[a-zA-Z0-9\.\_\-]+)*$/'),
				'Document root' => array ('regex:/^([a-zA-Z0-9\_\.\-\/]+)?$/'),
				'Protocol' => array ('required', 'in:0,1,2'),
				'CGI' => array ('required', 'in:0,1'),
				'Install Wordpress' => array ('nullable', 'sometimes')
			)
		);
		
		if ($validator->fails ())
			return Redirect::to ('/website/vhost/create')->withInput ()->withErrors ($validator);
		
		$serveralias = 'www.' . $servername;
		if (Input::get ('serveralias'))
			$serveralias .= ' ' . Input::get ('serveralias');
		
		$vhost = new Vhost ();
		$vhost->uid = $user->uid;
		$vhost->docroot = $user->homedir . '/' . $docroot;
		$vhost->servername = $servername;
		$vhost->serveralias = $serveralias;
		$vhost->serveradmin = $user->userInfo->username . '@' . $servername;
		$vhost->ssl = (int) Input::get ('ssl');
		$vhost->cgi = (bool) Input::get ('cgi');
		
		$vhost->save ();
		
		Log::log ('vHost created', $user->id, $vhost);
		
		$task = new SystemTask ();
		$task->type = SystemTask::TYPE_CREATE_VHOST_DOCROOT;
		$task->data = json_encode (['vhostId' => $vhost->id]);
		$task->save ();
		
		if (Input::get ('installWordpress'))
		{
			$wpTask = new SystemTask ();
			$wpTask->type = SystemTask::TYPE_VHOST_INSTALL_WORDPRESS;
			$wpTask->data = json_encode (['vhostId' => $vhost->id]);
			$wpTask->save ();
			
			return Redirect::to ('/system/systemtask/' . $wpTask->id . '/show')->with ('alerts', array (new Alert ('vHost created', Alert::TYPE_SUCCESS), new Alert ('Wordpress installation pending. This page will show more information once the installation attempt has completed.', Alert::TYPE_INFO)));
		}
		
		return Redirect::to ('/website/vhost')->with ('alerts', array (new Alert ('vHost created', Alert::TYPE_SUCCESS)));
	}
	
	public function edit ($vhost)
	{
		$user = Auth::user ();
		
		if ($vhost->uid !== $user->uid)
			return Redirect::to ('/website/vhost')->with ('alerts', array (new Alert ('You don\'t own this vHost!', Alert::TYPE_ALERT)));
		
		$insideHomedir = substr ($vhost->docroot, 0, strlen ($user->homedir)) == $user->homedir;
		
		if ($vhost->locked)
			return Redirect::to ('/website/vhost')->withInput ()->with ('alerts', array (new Alert ('You are not allowed to edit this vHost.', Alert::TYPE_ALERT)));
		
		return view ('website.vhost.edit', compact ('user', 'vhost', 'insideHomedir'));
	}
	
	public function update ($vhost)
	{
		$user = Auth::user ();
		
		if ($vhost->uid !== $user->uid)
			return Redirect::to ('/website/vhost')->with ('alerts', array (new Alert ('You don\'t own this vHost!', Alert::TYPE_ALERT)));
		
		$insideHomedir = (Input::get ('outsideHomedir') !== 'true');
		$docroot = @trailing_slash (Input::get ('docroot'));
		
		$validator = Validator::make
		(
			array
			(
				'Document root' => $docroot,
				'Aliases' => Input::get ('serveralias'),
				'Protocol' => Input::get ('ssl'),
				'CGI' => Input::get ('cgi')
			),
			array
			(
				'Document root' => array ($insideHomedir ? 'regex:/^([a-zA-Z0-9\_\.\-\/]+)?$/' : 'optional'),
				'Alias' => array ('nullable', 'different:Host', 'unique:vhost,servername', 'unique:vhost,serveralias', 'regex:/^[a-zA-Z0-9\.\_\-]+\.[a-zA-Z0-9\.\_\-]+(\s[a-zA-Z0-9\.\_\-]+\.[a-zA-Z0-9\.\_\-]+)*$/'),
				'Protocol' => array ('required', 'in:0,1,2'),
				'CGI' => array ('required', 'in:0,1')
			)
		);
		
		if ($validator->fails ())
			return Redirect::to ('website/vhost/' . $vhost->id . '/edit')
				->withInput ()
				->withErrors ($validator);
		
		if ($vhost->uid !== $user->uid)
			return Redirect::to ('website/vhost/' . $vhost->id . '/edit')
				->withInput ()
				->with ('alerts', array (new Alert ('You don\'t own this vHost!', Alert::TYPE_ALERT)));
		
		if ($vhost->locked)
			return Redirect::to ('/website/vhost/' . $vhost->id . '/edit')
				->withInput ()
				->with ('alerts', array (new Alert ('You are not allowed to edit this vHost.', Alert::TYPE_ALERT)));
		
		$oldDocroot = $vhost->docroot;
		if ($insideHomedir)
			$vhost->docroot = $user->homedir . '/' . $docroot;
		$vhost->serveralias = Input::get ('serveralias');
		$vhost->ssl = (int) Input::get ('ssl');
		$vhost->cgi = (bool) Input::get ('cgi');
		
		$vhost->save ();
		
		Log::log ('vHost modified', NULL, $vhost);
		
		$task = new SystemTask ();
		if ($insideHomedir && $oldDocroot != $vhost->docroot)
		{
			$task->type = SystemTask::TYPE_CREATE_VHOST_DOCROOT;
			$task->data = json_encode (['vhostId' => $vhost->id]);
		}
		else
		{
			$task->type = SystemTask::TYPE_APACHE_RELOAD;
		}
		$task->save ();
		
		return Redirect::to ('/website/vhost')->with ('alerts', array (new Alert ('vHost changes saved', Alert::TYPE_SUCCESS)));
	}
	
	public function remove ($vhost)
	{
		$user = Auth::user ();
		
		if ($vhost->uid !== $user->uid)
			return Redirect::to ('/website/vhost')->with ('alerts', array (new Alert ('You don\'t own this vHost!', Alert::TYPE_ALERT)));
		
		$vhost->delete ();
		
		Log::log ('vHost removed', NULL, $vhost);
		
		$task = new SystemTask ();
		$task->type = SystemTask::TYPE_APACHE_RELOAD;
		$task->save ();
		
		return Redirect::to ('/website/vhost')->with ('alerts', array (new Alert ('vHost removed', Alert::TYPE_SUCCESS)));
	}

}