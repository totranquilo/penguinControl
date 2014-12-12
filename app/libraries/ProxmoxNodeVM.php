<?php

class ProxmoxNodeService extends ProxmoxClass
{
	private $node;
	private $vmId;
	private $vmName;
	
	function __construct ($node, $data)
	{
		$this->node = $node;
		
		$this->id = $data->vmid;
		$this->name = $data->name;
	}
	
	public function getNode ()
	{
		return $this->node;
	}
	
	public function getId ()
	{
		return $this->id;
	}
	
	public function getName ()
	{
		return $this->name;
	}
	
	public function getStatus ()
	{
		return $this->get ('status/current');
	}
	
	public function reset ()
	{
		return $this->get ('status/reset');
	}
	
	public function resume ()
	{
		return $this->get ('status/resume');
	}
	
	public function shutdown ()
	{
		return $this->get ('status/shutdown');
	}
	
	public function start ()
	{
		return $this->get ('status/start');
	}
	
	public function stop ()
	{
		return $this->get ('status/stop');
	}
	
	public function suspend ()
	{
		return $this->get ('suspend');
	}
	
	private function get ($url)
	{
		$url = 'nodes/' . $this->node->getName () . '/qemu/' . $this->id . '/' . $url;
		
		return parent::get ($url);
	}
}