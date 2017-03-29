<?php

class StaffSystemSystemTaskController extends BaseController
{
	public function index ()
	{
		$tasks = SystemTask::all ();
		
		return View::make ('staff.system.systemtask.index', compact ('tasks'));
	}
	
	public function create ()
	{
		return View::make ('staff.system.systemtask.create');
	}

	public function store ()
	{
		$validator = Validator::make
		(
			array
			(
				'Type' => Input::get ('type'),
				'Start' => Input::get ('start'),
				'Interval' => Input::get ('interval'),
				'Interval-eenheid' => Input::get ('interval_unit'),
				'Einde' => Input::get ('end')
			),
			array
			(
				'Type' => array ('required', 'in:apache_reload,nuke_expired_vhosts,calculate_disk_usage'),
				'Start' => array ('date'),
				'Interval' => array ('numeric', 'required_with:Einde', 'min:1', 'max:113529600000'),
				'Interval-eenheid' => array ('required_with:Interval', 'in:sec,min,hour,day,week'),
				'Einde' => array ('date')
			)
		);
		
		if ($validator->fails ())
			return Redirect::to ('/staff/system/systemtask/create')->withInput ()->withErrors ($validator);
		
		$task = new SystemTask ();
		$task->type = Input::get ('type');
		$task->start = (empty (Input::get ('start')) ? time () : strtotime (Input::get ('start')));
		$factor = 1;
		switch (Input::get ('interval_unit'))
		{
			case 'week': // Falls through //
				$factor *= 7;
			case 'day': // Falls through //
				$factor *= 24;
			case 'hour': // Falls through //
				$factor *= 60;
			case 'min': // Falls through //
				$factor *= 60;
		}
		if (! empty (Input::get ('interval')))
			$task->interval = Input::get ('interval') * $factor;
		$task->end = (empty (Input::get ('end')) ? NULL : strtotime (Input::get ('end')));
		$task->started = 0;
		
		$task->save ();
		
		SinLog::log ('Systeemtaak aangemaakt', NULL, $task);
		
		return Redirect::to ('/staff/system/systemtask')->with ('alerts', array (new Alert ('Opdracht toegevoegd', 'success')));
	}
	
	public function show ($task)
	{
		return View::make ('staff.system.systemtask.show', compact ('task'));
	}
	
	public function remove ($task)
	{
		$task->delete ();
		
		SinLog::log ('Systeemtaak verwijderd', NULL, $task);
		
		return Redirect::to ('/staff/system/systemtask')->with ('alerts', array (new Alert ('Opdracht verwijderd', 'success')));
	}

}